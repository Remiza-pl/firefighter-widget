<?php
/**
 * Firefighter Stats -- Remiza.pl Reporter.
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

		const OPTION_ENABLED     = 'firefighter_stats_reporting_enabled';
		const OPTION_TOKEN       = 'firefighter_stats_reporting_token';
		const OPTION_REGISTERED  = 'firefighter_stats_reporting_registered';
		const OPTION_LAST_STATUS = 'firefighter_stats_reporting_last_status';

		/** Maximum time window for retries: 48 hours. */
		const MAX_RETRY_SECONDS = 172800;

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
		// Constructor -- hooks only, no side effects
		// ---------------------------------------------------------------

		/**
		 * Register all hooks.
		 */
		public function __construct() {
			add_action( 'wp_after_insert_post', array( $this, 'handle_post_after_insert' ), 10, 4 );
			add_action( 'firefighter_stats_send_report', array( $this, 'send_report_async' ) );
			add_action( 'firefighter_stats_retry_report', array( $this, 'retry_report_async' ), 10, 3 );
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
		private function get_token() {
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
		 * Send a report when a firefighter_stats post is first published.
		 *
		 * Hooked into wp_after_insert_post so category-enforcement (which runs
		 * on the wp_insert_post action and may revert the post to draft) has
		 * already completed before we check the final post status.
		 *
		 * @param int          $post_id     Post ID.
		 * @param WP_Post      $post        Post object after save.
		 * @param bool         $update      Whether this is an update.
		 * @param WP_Post|null $post_before Post object before save, or null on insert.
		 * @return void
		 */
		public function handle_post_after_insert( $post_id, $post, $update, $post_before ) {
			if ( 'firefighter_stats' !== $post->post_type ) {
				return;
			}
			// Final published status only (enforcement may have reverted to draft).
			if ( 'publish' !== $post->post_status ) {
				return;
			}
			// First publish only -- skip re-saves of already-published posts.
			if ( $post_before instanceof WP_Post && 'publish' === $post_before->post_status ) {
				return;
			}
			if ( ! $this->is_enabled() ) {
				return;
			}
			// Call directly -- no cron needed for initial send; retries are cron-based.
			$this->send_report_async( $post_id );
		}

		// ---------------------------------------------------------------
		// Cron callbacks
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
				// Registration failed -- start retry chain for this report.
				$this->schedule_report_retry( $post_id, 0, time() );
				return;
			}
			$this->dispatch( $this->build_payload( $post_id ), $post_id, 0, time() );
		}

		/**
		 * WP-Cron retry callback.
		 *
		 * @param int $post_id       Post ID.
		 * @param int $attempt       Current retry attempt number (0-based).
		 * @param int $first_failure Unix timestamp of the first failure.
		 * @return void
		 */
		public function retry_report_async( $post_id, $attempt, $first_failure ) {
			if ( ! $this->is_enabled() ) {
				return;
			}
			if ( 'publish' !== get_post_status( $post_id ) ) {
				return;
			}
			if ( ! $this->ensure_registered() ) {
				$this->schedule_report_retry( $post_id, $attempt + 1, $first_failure );
				return;
			}
			$this->dispatch( $this->build_payload( $post_id ), $post_id, $attempt + 1, $first_failure );
		}

		// ---------------------------------------------------------------
		// Registration
		// ---------------------------------------------------------------

		/**
		 * Return true if already registered and a token is stored; attempt registration otherwise.
		 *
		 * @return bool
		 */
		private function ensure_registered() {
			if ( get_option( self::OPTION_REGISTERED ) ) {
				if ( '' !== $this->get_token() ) {
					return true;
				}
				// Token was deleted independently -- reset so register_site() runs again.
				delete_option( self::OPTION_REGISTERED );
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
				$this->log_status( 'register', 'failed', array( 'error' => $response->get_error_message() ) );
				return false;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $code || 201 === $code ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty( $data['token'] ) ) {
					update_option( self::OPTION_TOKEN, sanitize_text_field( $data['token'] ), false );
					update_option( self::OPTION_REGISTERED, '1', false );
					$this->log_status( 'register', 'success', array() );
					return true;
				}
				$this->log_status( 'register', 'failed', array( 'error' => 'No token in response' ) );
				return false;
			}

			$this->handle_http_error( $response );
			$this->log_status( 'register', 'failed', array( 'error' => 'HTTP ' . $code ) );
			return false;
		}

		// ---------------------------------------------------------------
		// Dispatch
		// ---------------------------------------------------------------

		/**
		 * POST /report with the given payload.
		 *
		 * @param array $payload      Report payload.
		 * @param int   $post_id      Post ID (for retry scheduling).
		 * @param int   $attempt      Current attempt number.
		 * @param int   $first_failure Unix timestamp of the first failure (0 = first try).
		 * @return void
		 */
		private function dispatch( array $payload, $post_id = 0, $attempt = 0, $first_failure = 0 ) {
			$response = wp_remote_post(
				$this->get_endpoint( '/report' ),
				array(
					'body'    => wp_json_encode( $payload ),
					'headers' => array( 'Content-Type' => 'application/json' ),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->log_status( 'report', 'failed', array(
					'post_id' => $post_id,
					'attempt' => $attempt,
					'error'   => $response->get_error_message(),
				) );
				if ( $post_id > 0 ) {
					$this->schedule_report_retry( $post_id, $attempt, $first_failure ?: time() );
				}
				return;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( $code >= 200 && $code < 300 ) {
				$this->log_status( 'report', 'success', array( 'post_id' => $post_id ) );
				return;
			}

			$this->handle_http_error( $response );
			$this->log_status( 'report', 'failed', array(
				'post_id' => $post_id,
				'attempt' => $attempt,
				'error'   => 'HTTP ' . $code,
			) );
			// Do not retry on 401/403 -- token is invalid, user must re-register.
			if ( $post_id > 0 && 401 !== $code && 403 !== $code ) {
				$this->schedule_report_retry( $post_id, $attempt, $first_failure ?: time() );
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
				'token'          => $this->get_token(),
				'event'          => 'new_emergency',
				'post_title'     => $post->post_title,
				'post_url'       => get_permalink( $post_id ),
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
		// Retry mechanics
		// ---------------------------------------------------------------

		/**
		 * Schedule the next retry for a failed report.
		 * Abandons after MAX_RETRY_SECONDS (48 h) from the first failure.
		 *
		 * @param int $post_id       Post ID.
		 * @param int $attempt       Number of retries already attempted.
		 * @param int $first_failure Unix timestamp of first failure.
		 * @return void
		 */
		private function schedule_report_retry( $post_id, $attempt, $first_failure ) {
			if ( ( time() - $first_failure ) >= self::MAX_RETRY_SECONDS ) {
				$this->log_status( 'report', 'abandoned', array(
					'post_id'  => $post_id,
					'attempts' => $attempt,
				) );
				return;
			}

			$delay      = $this->get_retry_delay( $attempt );
			$next_retry = time() + $delay;

			wp_schedule_single_event(
				$next_retry,
				'firefighter_stats_retry_report',
				array( $post_id, $attempt, $first_failure )
			);

			$this->log_status( 'report', 'retrying', array(
				'post_id'    => $post_id,
				'attempt'    => $attempt + 1,
				'next_retry' => $next_retry,
			) );
		}

		/**
		 * Return the delay in seconds before a given retry attempt.
		 * Incremental backoff: 5 min → 15 → 30 → 1 h → 2 h → 4 h → 8 h → 16 h.
		 *
		 * @param int $attempt Zero-based retry count.
		 * @return int Seconds to wait.
		 */
		private function get_retry_delay( $attempt ) {
			$delays = array(
				0 => 5  * MINUTE_IN_SECONDS,
				1 => 15 * MINUTE_IN_SECONDS,
				2 => 30 * MINUTE_IN_SECONDS,
				3 => HOUR_IN_SECONDS,
				4 => 2  * HOUR_IN_SECONDS,
				5 => 4  * HOUR_IN_SECONDS,
				6 => 8  * HOUR_IN_SECONDS,
				7 => 16 * HOUR_IN_SECONDS,
			);
			return isset( $delays[ $attempt ] ) ? $delays[ $attempt ] : 16 * HOUR_IN_SECONDS;
		}

		// ---------------------------------------------------------------
		// Status logging
		// ---------------------------------------------------------------

		/**
		 * Persist the latest status for a channel (register/report) into an option
		 * so the Settings page can display it.
		 *
		 * @param string $channel  'register' or 'report'.
		 * @param string $status   'success', 'failed', 'retrying', or 'abandoned'.
		 * @param array  $context  Extra data (post_id, attempt, next_retry, error …).
		 * @return void
		 */
		private function log_status( $channel, $status, array $context ) {
			$log = get_option( self::OPTION_LAST_STATUS, array() );
			if ( ! is_array( $log ) ) {
				$log = array();
			}
			$log[ $channel ] = array_merge(
				$context,
				array(
					'status' => $status,
					'time'   => time(),
				)
			);
			update_option( self::OPTION_LAST_STATUS, $log, false );
		}

		/**
		 * Return the persisted status log array.
		 *
		 * @return array
		 */
		public static function get_last_status() {
			$log = get_option( 'firefighter_stats_reporting_last_status', array() );
			return is_array( $log ) ? $log : array();
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
							<?php echo esc_html( $this->t( 'Firefighter Statistics -- Data Sharing with Remiza.pl', 'Statystyki Wyjazdów -- Udostępnianie Danych Remiza.pl' ) ); ?>
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
						<?php echo esc_html( $this->t( 'Firefighter Statistics -- Remiza.pl token rejected.', 'Statystyki Wyjazdów -- token Remiza.pl odrzucony.' ) ); ?>
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

		/**
		 * Obtain a fresh registration token from Remiza.pl.
		 *
		 * The /register endpoint does not accept an existing token for validation —
		 * it always issues a new token. Previous reports on the server are preserved
		 * under the old domain_label entry.
		 *
		 * @return string 'reregistered' | 'failed'
		 */
		public function validate_registration() {
			delete_option( self::OPTION_TOKEN );
			delete_option( self::OPTION_REGISTERED );
			delete_transient( 'firefighter_stats_token_invalid' );
			return $this->register_site() ? 'reregistered' : 'failed';
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
