<?php
/**
 * Firefighter Stats — Remiza.pl Reporter.
 *
 * Sends a JSON payload to Remiza.pl's REST API each time a new
 * firefighter_stats post is published, so the portal can aggregate
 * national firefighter activity.
 *
 * Enabled by default. Users can opt out via the Settings page or the
 * consent notice that appears on first use.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Reporter' ) ) {

	class Firefighter_Stats_Reporter {

		const OPTION_ENABLED    = 'firefighter_stats_reporting_enabled';
		const OPTION_TOKEN      = 'firefighter_stats_reporting_token';
		const OPTION_REGISTERED = 'firefighter_stats_reporting_registered';

		// ---------------------------------------------------------------
		// Static entry points
		// ---------------------------------------------------------------

		/**
		 * Instantiate and register all hooks. Called on the `init` action.
		 */
		public static function init(): void {
			new self();
		}

		/**
		 * Generate (or retrieve) the site token on plugin activation.
		 */
		public static function generate_token_on_activation(): void {
			( new self() )->ensure_token();
		}

		// ---------------------------------------------------------------
		// Constructor — hooks only, no side effects
		// ---------------------------------------------------------------

		public function __construct() {
			add_action( 'transition_post_status', array( $this, 'handle_post_status_transition' ), 10, 3 );
			add_action( 'firefighter_stats_send_report', array( $this, 'send_report_async' ) );
			add_action( 'admin_notices', array( $this, 'show_consent_notice' ) );
			add_action( 'admin_notices', array( $this, 'show_token_invalid_notice' ) );
			add_action( 'admin_init', array( $this, 'handle_notice_actions' ) );
		}

		// ---------------------------------------------------------------
		// Token management
		// ---------------------------------------------------------------

		/**
		 * Return the stored token, creating one if it does not yet exist.
		 */
		private function ensure_token(): string {
			$token = get_option( self::OPTION_TOKEN, '' );
			if ( '' === $token ) {
				$token = $this->generate_uuid4();
				update_option( self::OPTION_TOKEN, $token, false );
			}
			return $token;
		}

		/**
		 * Generate a cryptographically random UUID4.
		 */
		private function generate_uuid4(): string {
			$bytes = random_bytes( 16 );

			// Set version bits (version 4).
			$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
			// Set variant bits (RFC 4122).
			$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );

			return strtolower( sprintf(
				'%s-%s-%s-%s-%s',
				bin2hex( substr( $bytes, 0, 4 ) ),
				bin2hex( substr( $bytes, 4, 2 ) ),
				bin2hex( substr( $bytes, 6, 2 ) ),
				bin2hex( substr( $bytes, 8, 2 ) ),
				bin2hex( substr( $bytes, 10, 6 ) )
			) );
		}

		/**
		 * Whether reporting is currently enabled.
		 */
		public function is_enabled(): bool {
			return '0' !== get_option( self::OPTION_ENABLED, '1' );
		}

		// ---------------------------------------------------------------
		// Publish hook
		// ---------------------------------------------------------------

		/**
		 * Schedule an async report when a firefighter_stats post is first published.
		 *
		 * @param string  $new  New post status.
		 * @param string  $old  Previous post status.
		 * @param WP_Post $post Post object.
		 */
		public function handle_post_status_transition( string $new, string $old, WP_Post $post ): void {
			if ( 'publish' !== $new ) {
				return;
			}
			// Only fire on first publish, not on subsequent saves.
			if ( 'publish' === $old ) {
				return;
			}
			if ( 'firefighter_stats' !== $post->post_type ) {
				return;
			}
			if ( ! $this->is_enabled() ) {
				return;
			}
			wp_schedule_single_event( time(), 'firefighter_stats_send_report', array( $post->ID ) );
		}

		// ---------------------------------------------------------------
		// Cron callback
		// ---------------------------------------------------------------

		/**
		 * WP-Cron callback: register the site (if needed) then send the report.
		 *
		 * @param int $post_id Post ID.
		 */
		public function send_report_async( int $post_id ): void {
			if ( ! $this->is_enabled() ) {
				return;
			}
			// Category-enforcement may have reverted the post to draft.
			if ( 'publish' !== get_post_status( $post_id ) ) {
				return;
			}
			if ( ! $this->ensure_registered() ) {
				return;
			}
			$this->dispatch( $this->build_payload( $post_id ) );
		}

		// ---------------------------------------------------------------
		// Registration
		// ---------------------------------------------------------------

		/**
		 * Return true if already registered; attempt registration otherwise.
		 */
		private function ensure_registered(): bool {
			if ( get_option( self::OPTION_REGISTERED ) ) {
				return true;
			}
			return $this->register_site();
		}

		/**
		 * POST /register to create or update this site's registration.
		 */
		private function register_site(): bool {
			$body = wp_json_encode( array(
				'token'     => $this->ensure_token(),
				'site_url'  => home_url(),
				'site_name' => get_bloginfo( 'name' ),
			) );

			$response = wp_remote_post(
				$this->get_endpoint( '/register' ),
				array(
					'body'    => $body,
					'headers' => array( 'Content-Type' => 'application/json' ),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $code || 201 === $code ) {
				update_option( self::OPTION_REGISTERED, '1', false );
				return true;
			}

			$this->handle_http_error( $response );
			return false;
		}

		// ---------------------------------------------------------------
		// Dispatch
		// ---------------------------------------------------------------

		/**
		 * POST /report with the given payload.
		 *
		 * @param array $payload Report payload.
		 */
		private function dispatch( array $payload ): void {
			$response = wp_remote_post(
				$this->get_endpoint( '/report' ),
				array(
					'body'    => wp_json_encode( $payload ),
					'headers' => array( 'Content-Type' => 'application/json' ),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $response ) ) {
				return;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( $code < 200 || $code >= 300 ) {
				$this->handle_http_error( $response );
			}
		}

		/**
		 * Build the report payload for a given post.
		 *
		 * @param int $post_id Post ID.
		 * @return array
		 */
		private function build_payload( int $post_id ): array {
			$post  = get_post( $post_id );
			$terms = wp_get_post_terms( $post_id, 'firefighter_stats_cat' );
			$term  = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0] : null;

			return array(
				'token'          => $this->ensure_token(),
				'site_url'       => home_url(),
				'site_name'      => get_bloginfo( 'name' ),
				'event'          => 'new_emergency',
				'post_title'     => $post->post_title,
				'post_excerpt'   => wp_trim_words( $post->post_content, 30, '...' ),
				'category_slug'  => $term ? $term->slug : '',
				'category_name'  => $term ? $term->name : '',
				'category_icon'  => $term ? firefighter_stats_get_category_emoji( $term->term_id ) : '',
				'emergency_date' => get_the_date( 'Y-m-d', $post_id ),
				'plugin_version' => FIREFIGHTER_STATS_VERSION,
				'reported_at'    => gmdate( 'Y-m-d\TH:i:s\Z' ),
			);
		}

		/**
		 * Handle non-2xx HTTP responses.
		 *
		 * 401/403 → mark token as invalid and clear the registered flag so
		 * the next publish attempt triggers a fresh /register call.
		 *
		 * @param array|WP_Error $response wp_remote_post response.
		 */
		private function handle_http_error( $response ): void {
			if ( is_wp_error( $response ) ) {
				return; // Silent; network errors are transient.
			}
			$code = wp_remote_retrieve_response_code( $response );
			if ( 401 === $code || 403 === $code ) {
				set_transient( 'firefighter_stats_token_invalid', true, 12 * HOUR_IN_SECONDS );
				delete_option( self::OPTION_REGISTERED );
			}
		}

		/**
		 * Return the full endpoint URL for the given path segment.
		 *
		 * @param string $path e.g. '/register' or '/report'.
		 * @return string
		 */
		private function get_endpoint( string $path ): string {
			$base = apply_filters(
				'firefighter_stats_reporting_endpoint',
				'https://remiza.pl/wp-json/remiza-stats/v1'
			);
			return rtrim( $base, '/' ) . $path;
		}

		// ---------------------------------------------------------------
		// Admin notices
		// ---------------------------------------------------------------

		/**
		 * Show the first-run consent notice to admins.
		 */
		public function show_consent_notice(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( get_option( 'firefighter_stats_reporting_notice_dismissed' ) ) {
				return;
			}
			if ( ! $this->is_enabled() ) {
				return;
			}

			$nonce        = wp_create_nonce( 'firefighter_stats_reporter_action' );
			$settings_url = admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-settings' );

			$keep_url = add_query_arg( array(
				'firefighter_stats_action' => 'keep_reporting',
				'_wpnonce'                 => $nonce,
			) );
			$disable_url = add_query_arg( array(
				'firefighter_stats_action' => 'disable_reporting',
				'_wpnonce'                 => $nonce,
			) );

			?>
			<div class="notice notice-info" style="display:flex; align-items:flex-start; gap:16px; padding:14px 16px;">
				<div style="flex-shrink:0; padding-top:2px;">
					<a href="https://remiza.pl" target="_blank" rel="noopener noreferrer">
						<img src="https://remiza.pl/wp-content/uploads/2026/01/logoR-bez-tla.png"
						     alt="Remiza.pl"
						     style="height:36px; width:auto; display:block;">
					</a>
				</div>
				<div>
					<p style="margin:0 0 6px;">
						<strong><?php
							echo esc_html( $this->t(
								'Firefighter Statistics — Data Sharing with Remiza.pl',
								'Statystyki Wyjazdów — Udostępnianie Danych Remiza.pl'
							) );
						?></strong>
					</p>
					<p style="margin:0 0 8px; color:#3c434a;">
						<?php echo esc_html( $this->t(
							'This plugin sends anonymised emergency statistics to Remiza.pl so the portal can display national firefighter activity. No personal data is ever shared.',
							'Ta wtyczka wysyła zanonimizowane statystyki wyjazdów do Remiza.pl, aby portal mógł wyświetlać krajową aktywność strażaków. Żadne dane osobowe nie są przesyłane.'
						) ); ?>
					</p>
					<p style="margin:0 0 8px; color:#3c434a; font-size:12px;">
						<strong><?php echo esc_html( $this->t( "What's shared:", 'Co jest udostępniane:' ) ); ?></strong>
						<?php echo esc_html( $this->t(
							'site name &amp; URL · post title · 30-word excerpt · category · emergency date · plugin version',
							'nazwa i URL strony · tytuł postu · 30-słowny skrót · kategoria · data wyjazdu · wersja wtyczki'
						) ); ?>
					</p>
					<p style="margin:0;">
						<a href="<?php echo esc_url( $keep_url ); ?>" class="button button-primary" style="margin-right:8px;">
							<?php echo esc_html( $this->t( 'OK, keep sharing', 'OK, pozostaw udostępnianie' ) ); ?>
						</a>
						<a href="<?php echo esc_url( $disable_url ); ?>" class="button">
							<?php echo esc_html( $this->t( 'Disable', 'Wyłącz' ) ); ?>
						</a>
						<a href="<?php echo esc_url( $settings_url ); ?>" style="margin-left:12px; font-size:12px; vertical-align:middle;">
							<?php echo esc_html( $this->t( 'Settings', 'Ustawienia' ) ); ?>
						</a>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * Show an error notice when the site token has been rejected.
		 */
		public function show_token_invalid_notice(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! get_transient( 'firefighter_stats_token_invalid' ) ) {
				return;
			}

			$nonce        = wp_create_nonce( 'firefighter_stats_reporter_action' );
			$settings_url = admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-settings' );

			$reregister_url = add_query_arg( array(
				'firefighter_stats_action' => 'reregister',
				'_wpnonce'                 => $nonce,
			) );

			?>
			<div class="notice notice-error">
				<p>
					<strong><?php echo esc_html( $this->t(
						'Firefighter Statistics — Remiza.pl token rejected.',
						'Statystyki Wyjazdów — token Remiza.pl odrzucony.'
					) ); ?></strong>
					<?php echo esc_html( $this->t(
						'Reporting was paused because the site token was not recognised. Click Re-register to obtain a new token.',
						'Raportowanie zostało wstrzymane, ponieważ token strony nie został rozpoznany. Kliknij Zarejestruj ponownie, aby uzyskać nowy token.'
					) ); ?>
					<a href="<?php echo esc_url( $reregister_url ); ?>" class="button button-small" style="margin-left:8px;">
						<?php echo esc_html( $this->t( 'Re-register', 'Zarejestruj ponownie' ) ); ?>
					</a>
					<a href="<?php echo esc_url( $settings_url ); ?>" style="margin-left:8px; font-size:12px; vertical-align:middle;">
						<?php echo esc_html( $this->t( 'Settings', 'Ustawienia' ) ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		// ---------------------------------------------------------------
		// Notice action handler
		// ---------------------------------------------------------------

		/**
		 * Handle GET-parameter actions triggered by notice buttons.
		 * Runs on `admin_init`.
		 */
		public function handle_notice_actions(): void {
			if ( empty( $_GET['firefighter_stats_action'] ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'firefighter_stats_reporter_action' ) ) {
				return;
			}

			$action = sanitize_key( $_GET['firefighter_stats_action'] );

			switch ( $action ) {
				case 'keep_reporting':
					update_option( 'firefighter_stats_reporting_notice_dismissed', '1', false );
					break;

				case 'disable_reporting':
					update_option( self::OPTION_ENABLED, '0', false );
					update_option( 'firefighter_stats_reporting_notice_dismissed', '1', false );
					break;

				case 'reregister':
					$this->regenerate_token();
					delete_transient( 'firefighter_stats_token_invalid' );
					break;
			}

			wp_safe_redirect( remove_query_arg( array( 'firefighter_stats_action', '_wpnonce' ) ) );
			exit;
		}

		// ---------------------------------------------------------------
		// Public mutators (used by Settings page too)
		// ---------------------------------------------------------------

		/**
		 * Generate a new token and attempt re-registration immediately.
		 */
		public function regenerate_token(): void {
			delete_option( self::OPTION_TOKEN );
			delete_option( self::OPTION_REGISTERED );
			$this->ensure_token();
			$this->register_site();
		}

		/**
		 * Enable reporting.
		 */
		public function enable_reporting(): void {
			update_option( self::OPTION_ENABLED, '1', false );
		}

		/**
		 * Disable reporting.
		 */
		public function disable_reporting(): void {
			update_option( self::OPTION_ENABLED, '0', false );
		}

		// ---------------------------------------------------------------
		// Locale helper (same pattern as Admin Guide)
		// ---------------------------------------------------------------

		/** @var bool|null */
		private $is_pl = null;

		private function t( string $en, string $pl ): string {
			if ( null === $this->is_pl ) {
				$this->is_pl = ( strpos( get_user_locale(), 'pl' ) === 0 );
			}
			return $this->is_pl ? $pl : $en;
		}
	}
}
