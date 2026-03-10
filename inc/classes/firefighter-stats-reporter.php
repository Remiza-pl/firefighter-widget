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
 *
 * @package Firefighter_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Reporter' ) ) {

	/**
	 * Handles sending emergency statistics to Remiza.pl.
	 */
	class Firefighter_Stats_Reporter {

		const OPTION_ENABLED    = 'firefighter_stats_reporting_enabled';
		const OPTION_TOKEN      = 'firefighter_stats_reporting_token';
		const OPTION_REGISTERED = 'firefighter_stats_reporting_registered';

		/** @var bool|null Cached Polish-locale flag. */
		private $is_pl = null;

		// ---------------------------------------------------------------
		// Static entry points
		// ---------------------------------------------------------------

		/**
		 * Instantiate and register all hooks. Called on the `init` action.
		 *
		 * @return void
		 */
		public static function init() {
			new self();
		}

		/**
		 * Called on plugin activation. Token is now issued server-side during /register.
		 *
		 * @return void
		 */
		public static function generate_token_on_activation() {
			// No-op: the site token is issued by the Remiza.pl server when the first
			// report is dispatched (via ensure_registered → register_site).
		}

		// ---------------------------------------------------------------
		// Constructor — hooks only, no side effects
		// ---------------------------------------------------------------

		/**
		 * Register all hooks.
		 */
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
		 * Return the stored token (issued by the Remiza.pl server on registration).
		 * Returns an empty string when the site has not yet been registered.
		 *
		 * @return string
		 */
		private function ensure_token() {
			return get_option( self::OPTION_TOKEN, '' );
		}

		/**
		 * Whether reporting is currently enabled.
		 *
		 * @return bool
		 */
		public function is_enabled() {
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
		 * @return void
		 */
		public function handle_post_status_transition( $new, $old, $post ) {
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
		 * @return void
		 */
		public function send_report_async( $post_id ) {
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
		 *
		 * @return bool
		 */
		private function ensure_registered() {
			if ( get_option( self::OPTION_REGISTERED ) ) {
				return true;
			}
			return $this->register_site();
		}

		/**
		 * POST /register to create this site's registration and receive a server-issued token.
		 *
		 * @return bool
		 */
		private function register_site() {
			$body = wp_json_encode(
				array(
					'site_url'  => home_url(),
					'site_name' => get_bloginfo( 'name' ),
				)
			);

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
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty( $data['token'] ) ) {
					update_option( self::OPTION_TOKEN, sanitize_text_field( $data['token'] ), false );
					update_option( self::OPTION_REGISTERED, '1', false );
					return true;
				}
				return false;
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
		 * @return void
		 */
		private function dispatch( array $payload ) {
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
		private function build_payload( $post_id ) {
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
		 * @return void
		 */
		private function handle_http_error( $response ) {
			if ( is_wp_error( $response ) ) {
				return;
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
		private function get_endpoint( $path ) {
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
		 *
		 * @return void
		 */
		public function show_consent_notice() {
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

			$keep_url = add_query_arg(
				array(
					'firefighter_stats_action' => 'keep_reporting',
					'_wpnonce'                 => $nonce,
				)
			);
			$disable_url = add_query_arg(
				array(
					'firefighter_stats_action' => 'disable_reporting',
					'_wpnonce'                 => $nonce,
				)
			);
			?>
			<div class="notice notice-info is-dismissible" style="display:flex; align-items:flex-start; gap:16px; padding:14px 16px;">
				<div style="flex-shrink:0; padding-top:2px;">
					<img src="<?php echo esc_url( FIREFIGHTER_STATS_PLUGIN_URL . 'assets/images/remiza-logo.webp' ); ?>" alt="Remiza.pl" height="36" style="display:block;">
				</div>
				<div>
					<p style="margin:0 0 6px;">
						<strong>
							<?php echo esc_html( $this->t( 'Firefighter Statistics — Data Sharing with Remiza.pl', 'Statystyki Wyjazdów — Udostępnianie Danych Remiza.pl' ) ); ?>
						</strong>
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
							'site name & URL · post title · 30-word excerpt · category · emergency date · plugin version',
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
		 *
		 * @return void
		 */
		public function show_token_invalid_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! get_transient( 'firefighter_stats_token_invalid' ) ) {
				return;
			}

			$nonce        = wp_create_nonce( 'firefighter_stats_reporter_action' );
			$settings_url = admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-settings' );

			$reregister_url = add_query_arg(
				array(
					'firefighter_stats_action' => 'reregister',
					'_wpnonce'                 => $nonce,
				)
			);
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<strong>
						<?php echo esc_html( $this->t( 'Firefighter Statistics — Remiza.pl token rejected.', 'Statystyki Wyjazdów — token Remiza.pl odrzucony.' ) ); ?>
					</strong>
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
		 *
		 * @return void
		 */
		public function handle_notice_actions() {
			if ( empty( $_GET['firefighter_stats_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if (
				empty( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'firefighter_stats_reporter_action' )
			) {
				return;
			}

			$action = sanitize_key( wp_unslash( $_GET['firefighter_stats_action'] ) );

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
		 * Clear the stored token and attempt re-registration to obtain a fresh server-issued token.
		 *
		 * @return void
		 */
		public function regenerate_token() {
			delete_option( self::OPTION_TOKEN );
			delete_option( self::OPTION_REGISTERED );
			$this->register_site();
		}

		/**
		 * Enable reporting.
		 *
		 * @return void
		 */
		public function enable_reporting() {
			update_option( self::OPTION_ENABLED, '1', false );
		}

		/**
		 * Disable reporting.
		 *
		 * @return void
		 */
		public function disable_reporting() {
			update_option( self::OPTION_ENABLED, '0', false );
		}

		// ---------------------------------------------------------------
		// Locale helper (same pattern as Admin Guide)
		// ---------------------------------------------------------------

		/**
		 * Return the localised string for the admin's current language.
		 *
		 * @param string $en English text.
		 * @param string $pl Polish text.
		 * @return string
		 */
		private function t( $en, $pl ) {
			if ( null === $this->is_pl ) {
				$this->is_pl = ( 0 === strpos( get_user_locale(), 'pl' ) );
			}
			return $this->is_pl ? $pl : $en;
		}
	}
}
