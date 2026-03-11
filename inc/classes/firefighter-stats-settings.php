<?php
/**
 * Firefighter Stats — Settings admin page.
 *
 * Provides a submenu page under Emergencies where admins can toggle
 * Remiza.pl reporting and manage the site token.
 *
 * @package Firefighter_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Settings' ) ) {

	/**
	 * Renders and handles the plugin Settings admin page.
	 */
	class Firefighter_Stats_Settings {

		/** @var bool|null Cached Polish-locale flag. */
		private $is_pl = null;

		/**
		 * Register hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			add_action( 'admin_init', array( $this, 'handle_form' ) );
		}

		// ---------------------------------------------------------------
		// Menu
		// ---------------------------------------------------------------

		/**
		 * Register the Settings submenu page.
		 *
		 * @return void
		 */
		public function add_menu() {
			add_submenu_page(
				'edit.php?post_type=firefighter_stats',
				$this->t( 'Firefighter Stats Settings', 'Ustawienia Statystyk Wyjazdów' ),
				$this->t( 'Settings', 'Ustawienia' ),
				'manage_options',
				'firefighter-stats-settings',
				array( $this, 'render' )
			);
		}

		// ---------------------------------------------------------------
		// Form handler
		// ---------------------------------------------------------------

		/**
		 * Process the settings form submission.
		 *
		 * @return void
		 */
		public function handle_form() {
			if ( empty( $_POST['fs_settings_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if (
				empty( $_POST['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'firefighter_stats_settings_action' )
			) {
				return;
			}

			$fs_action = sanitize_key( wp_unslash( $_POST['fs_settings_action'] ) );

			switch ( $fs_action ) {
				case 'save':
					$enabled = ! empty( $_POST['fs_reporting_enabled'] ) ? '1' : '0';
					update_option( Firefighter_Stats_Reporter::OPTION_ENABLED, $enabled, false );
					if ( '1' === $enabled ) {
						update_option( 'firefighter_stats_reporting_notice_dismissed', '1', false );
					}
					break;

				case 'reregister':
					if ( class_exists( 'Firefighter_Stats_Reporter' ) ) {
						( new Firefighter_Stats_Reporter() )->regenerate_token();
					}
					delete_transient( 'firefighter_stats_token_invalid' );
					break;

				case 'validate_token':
					$fs_validate_result = 'failed';
					if ( class_exists( 'Firefighter_Stats_Reporter' ) ) {
						$fs_validate_result = ( new Firefighter_Stats_Reporter() )->validate_registration();
					}
					wp_safe_redirect( add_query_arg(
						array(
							'updated'     => '1',
							'fs_validate' => $fs_validate_result,
						),
						wp_get_referer()
					) );
					exit;
			}

			wp_safe_redirect( add_query_arg( 'updated', '1', wp_get_referer() ) );
			exit;
		}

		// ---------------------------------------------------------------
		// Render
		// ---------------------------------------------------------------

		/**
		 * Output the Settings page HTML.
		 *
		 * @return void
		 */
		public function render() {
			$token         = get_option( Firefighter_Stats_Reporter::OPTION_TOKEN, '' );
			$is_enabled    = class_exists( 'Firefighter_Stats_Reporter' ) && ( new Firefighter_Stats_Reporter() )->is_enabled();
			$token_invalid = (bool) get_transient( 'firefighter_stats_token_invalid' );

			$updated    = isset( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$fs_vresult = isset( $_GET['fs_validate'] ) ? sanitize_key( wp_unslash( $_GET['fs_validate'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Masked token: show first 8 chars only.
			if ( '' !== $token ) {
				$token_masked = esc_html( substr( $token, 0, 8 ) ) . '&hellip;';
			} else {
				$token_masked = '<em>' . esc_html( $this->t( 'not generated yet', 'jeszcze nie wygenerowany' ) ) . '</em>';
			}
			?>
			<div class="wrap">
				<h1><?php echo esc_html( $this->t( 'Firefighter Stats — Settings', 'Statystyki Wyjazdów — Ustawienia' ) ); ?></h1>
				<p style="color:#646970; max-width:680px;">
					<?php echo esc_html( $this->t(
						'Manage Remiza.pl data sharing and other plugin settings.',
						'Zarządzaj udostępnianiem danych do Remiza.pl i innymi ustawieniami wtyczki.'
					) ); ?>
				</p>

				<?php if ( '1' === $updated ) : ?>
					<?php if ( 'reregistered' === $fs_vresult ) : ?>
						<div class="notice notice-info is-dismissible">
							<p><?php echo esc_html( $this->t( 'Token was rejected — a new token has been obtained and the site is now re-registered.', 'Token został odrzucony — uzyskano nowy token i strona jest teraz ponownie zarejestrowana.' ) ); ?></p>
						</div>
					<?php elseif ( 'network_error' === $fs_vresult ) : ?>
						<div class="notice notice-error is-dismissible">
							<p><?php echo esc_html( $this->t( "Could not reach Remiza.pl — check your server's internet connection and try again.", 'Nie można połączyć się z Remiza.pl — sprawdź połączenie internetowe serwera i spróbuj ponownie.' ) ); ?></p>
						</div>
					<?php elseif ( 'failed' === $fs_vresult ) : ?>
						<div class="notice notice-error is-dismissible">
							<p><?php echo esc_html( $this->t( 'Validation failed. Check the Connection Status panel below for details.', 'Walidacja nie powiodła się. Sprawdź panel Status Połączenia poniżej.' ) ); ?></p>
						</div>
					<?php else : ?>
						<div class="notice notice-success is-dismissible">
							<p><?php echo esc_html( $this->t( 'Settings saved.', 'Ustawienia zapisane.' ) ); ?></p>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<!-- Remiza.pl feature card -->
				<div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px; display:flex; gap:20px; align-items:flex-start;">
					<div style="flex-shrink:0;">
						<img src="<?php echo esc_url( FIREFIGHTER_STATS_PLUGIN_URL . 'assets/images/remiza-logo.webp' ); ?>" alt="Remiza.pl" height="48" style="display:block;">
					</div>
					<div>
						<h2 style="margin-top:0;">
							<?php echo esc_html( $this->t( 'Remiza.pl Data Sharing', 'Udostępnianie Danych Remiza.pl' ) ); ?>
						</h2>
						<p>
							<?php echo esc_html( $this->t(
								"When you publish an emergency post this plugin sends a small anonymised payload to Remiza.pl — Poland's largest firefighter portal — so the site can display aggregated national emergency activity. Reporting is enabled by default and can be turned off at any time.",
								'Gdy publikujesz post wyjazdu, wtyczka wysyła niewielki zanonimizowany pakiet do Remiza.pl — największego polskiego portalu strażackiego — aby portal mógł wyświetlać zagregowaną krajową aktywność ratowniczą. Raportowanie jest domyślnie włączone i można je wyłączyć w dowolnym momencie.'
							) ); ?>
						</p>
					</div>
				</div>

				<!-- What's shared info box -->
				<div style="max-width:760px; margin-bottom:20px; background:#f0f6fc; border-left:4px solid #2271b1; padding:12px 16px;">
					<p style="margin:0 0 8px; font-weight:600;">
						📡 <?php echo esc_html( $this->t( "What's shared:", 'Co jest udostępniane:' ) ); ?>
					</p>
					<ul style="margin:0; padding-left:20px; line-height:1.8;">
						<li><?php echo esc_html( $this->t( 'Site name and URL', 'Nazwa i URL strony' ) ); ?></li>
						<li><?php echo esc_html( $this->t( 'Post title', 'Tytuł postu' ) ); ?></li>
						<li><?php echo esc_html( $this->t( '30-word excerpt of the post content', '30-słowny skrót treści postu' ) ); ?></li>
						<li><?php echo esc_html( $this->t( 'Category slug, name, and icon', 'Slug, nazwa i ikona kategorii' ) ); ?></li>
						<li><?php echo esc_html( $this->t( 'Emergency date', 'Data wyjazdu' ) ); ?></li>
						<li><?php echo esc_html( $this->t( 'Plugin version', 'Wersja wtyczki' ) ); ?></li>
					</ul>
					<p style="margin:8px 0 0; color:#3c434a; font-size:12px;">
						🔒 <?php echo esc_html( $this->t(
							'No personal data, user IDs, or IP addresses are ever sent.',
							'Żadne dane osobowe, identyfikatory użytkowników ani adresy IP nie są przesyłane.'
						) ); ?>
					</p>
				</div>

				<!-- Main settings form -->
				<form method="post" style="max-width:760px;">
					<?php wp_nonce_field( 'firefighter_stats_settings_action' ); ?>
					<input type="hidden" name="fs_settings_action" value="save">

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="fs_reporting_enabled">
									<?php echo esc_html( $this->t( 'Enable reporting', 'Włącz raportowanie' ) ); ?>
								</label>
							</th>
							<td>
								<label>
									<input type="checkbox"
									       id="fs_reporting_enabled"
									       name="fs_reporting_enabled"
									       value="1"
									       <?php checked( $is_enabled ); ?>>
									<?php echo esc_html( $this->t(
										'Send emergency statistics to Remiza.pl when a post is published.',
										'Wysyłaj statystyki wyjazdów do Remiza.pl po opublikowaniu postu.'
									) ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html( $this->t( 'Site token', 'Token strony' ) ); ?>
							</th>
							<td>
								<code><?php echo $token_masked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?></code>
								<p class="description">
									<?php echo esc_html( $this->t(
										'Your unique identifier used to authenticate with Remiza.pl. Never share the full token.',
										'Twój unikalny identyfikator używany do uwierzytelniania w Remiza.pl. Nigdy nie udostępniaj pełnego tokenu.'
									) ); ?>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button( $this->t( 'Save Settings', 'Zapisz Ustawienia' ) ); ?>
				</form>

				<!-- Validate connection card -->
				<div class="card" style="max-width:760px; margin-top:20px; padding:20px 24px; border-left:4px solid #2271b1;">
					<h3 style="margin-top:0;">
						<?php echo esc_html( $this->t( 'Validate Token', 'Sprawdzenie Tokenu' ) ); ?>
					</h3>
					<p style="margin-top:0; color:#3c434a;">
						<?php echo esc_html( $this->t(
							"Request a new registration token from Remiza.pl. Use this if reporting has stopped working or after a site migration. Previous reports on the server are preserved.",
							'Wysyła prośbę o nowy token rejestracyjny do Remiza.pl. Użyj jeśli raportowanie przestało działać lub po migracji strony. Poprzednie raporty na serwerze są zachowane.'
						) ); ?>
					</p>
					<form method="post">
						<?php wp_nonce_field( 'firefighter_stats_settings_action' ); ?>
						<input type="hidden" name="fs_settings_action" value="validate_token">
						<?php submit_button(
							$this->t( 'Validate & Refresh Registration', 'Sprawdź i Odśwież Rejestrację' ),
							'secondary',
							'submit',
							false
						); ?>
					</form>
				</div>

				<?php $this->render_status_panel(); ?>

				<?php if ( $token_invalid ) : ?>
					<!-- Re-register panel (only shown when token was rejected) -->
					<div class="card" style="max-width:760px; margin-top:20px; padding:20px 24px; border-left:4px solid #d63638;">
						<h3 style="margin-top:0; color:#d63638;">
							⚠️ <?php echo esc_html( $this->t( 'Token rejected by Remiza.pl', 'Token odrzucony przez Remiza.pl' ) ); ?>
						</h3>
						<p>
							<?php echo esc_html( $this->t(
								'The site token was not recognised by the Remiza.pl server. This can happen after a site migration or if the token was manually deleted. Click the button below to generate a fresh token and re-register.',
								'Token strony nie został rozpoznany przez serwer Remiza.pl. Może się to zdarzyć po migracji strony lub jeśli token został ręcznie usunięty. Kliknij poniższy przycisk, aby wygenerować nowy token i zarejestrować się ponownie.'
							) ); ?>
						</p>
						<form method="post">
							<?php wp_nonce_field( 'firefighter_stats_settings_action' ); ?>
							<input type="hidden" name="fs_settings_action" value="reregister">
							<?php submit_button(
								$this->t( 'Re-register with Remiza.pl', 'Zarejestruj ponownie w Remiza.pl' ),
								'secondary'
							); ?>
						</form>
					</div>
				<?php endif; ?>

			</div>
			<?php
		}

		// ---------------------------------------------------------------
		// Connection status panel
		// ---------------------------------------------------------------

		/**
		 * Render the Connection Status card on the Settings page.
		 *
		 * @return void
		 */
		private function render_status_panel() {
			if ( ! class_exists( 'Firefighter_Stats_Reporter' ) ) {
				return;
			}
			$fs_status = Firefighter_Stats_Reporter::get_last_status();
			$fs_now    = time();
			?>
			<div class="card" style="max-width:760px; margin-top:20px; padding:20px 24px;">
				<h3 style="margin-top:0;">
					<?php echo esc_html( $this->t( 'Connection Status', 'Status Połączenia' ) ); ?>
				</h3>
				<?php if ( empty( $fs_status ) ) : ?>
					<p style="color:#646970; margin:0;">
						<?php echo esc_html( $this->t(
							'No connection attempts yet. Status will appear here after the first emergency post is published.',
							'Brak prób połączenia. Status pojawi się tutaj po opublikowaniu pierwszego wpisu wyjazdu.'
						) ); ?>
					</p>
				<?php else : ?>
					<table class="widefat striped" style="border:none;">
						<thead>
							<tr>
								<th style="width:140px;"><?php echo esc_html( $this->t( 'Channel', 'Kanał' ) ); ?></th>
								<th><?php echo esc_html( $this->t( 'Status', 'Status' ) ); ?></th>
								<th style="width:160px;"><?php echo esc_html( $this->t( 'Last updated', 'Ostatnia aktualizacja' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$fs_channels = array(
								'register' => $this->t( 'Registration', 'Rejestracja' ),
								'report'   => $this->t( 'Last report', 'Ostatni raport' ),
							);
							foreach ( $fs_channels as $fs_channel_key => $fs_channel_label ) :
								$fs_entry   = isset( $fs_status[ $fs_channel_key ] ) ? $fs_status[ $fs_channel_key ] : null;
								$fs_state   = $fs_entry ? $fs_entry['status'] : 'none';
								$fs_time    = $fs_entry ? (int) $fs_entry['time'] : 0;
								$fs_error   = $fs_entry && ! empty( $fs_entry['error'] ) ? $fs_entry['error'] : '';
								$fs_attempt = $fs_entry && isset( $fs_entry['attempt'] ) ? (int) $fs_entry['attempt'] : 0;
								$fs_next    = $fs_entry && ! empty( $fs_entry['next_retry'] ) ? (int) $fs_entry['next_retry'] : 0;
								?>
								<tr>
									<td><strong><?php echo esc_html( $fs_channel_label ); ?></strong></td>
									<td>
										<?php
										if ( 'success' === $fs_state ) :
											echo '<span style="color:#00a32a;">✅ ' . esc_html( $this->t( 'OK', 'OK' ) ) . '</span>';
										elseif ( 'retrying' === $fs_state ) :
											$fs_next_in = ( $fs_next > $fs_now ) ? human_time_diff( $fs_now, $fs_next ) : $this->t( 'any moment', 'lada chwila' );
											echo '<span style="color:#2271b1;">⏳ ' . sprintf(
												esc_html( $this->t( 'Retrying (attempt %d, next in %s)', 'Ponawianie (próba %d, następna za %s)' ) ),
												$fs_attempt,
												esc_html( $fs_next_in )
											) . '</span>';
										elseif ( 'failed' === $fs_state ) :
											echo '<span style="color:#d63638;">❌ ' . esc_html( $this->t( 'Failed', 'Błąd' ) );
											if ( $fs_error ) {
												echo ': <code>' . esc_html( $fs_error ) . '</code>';
											}
											echo '</span>';
										elseif ( 'abandoned' === $fs_state ) :
											echo '<span style="color:#d63638;">❌ ' . esc_html( $this->t(
												'Abandoned after 48 h — re-register to restore reporting.',
												'Porzucono po 48 h — zarejestruj ponownie, aby przywrócić raportowanie.'
											) ) . '</span>';
										else :
											echo '<span style="color:#646970;">&mdash; ' . esc_html( $this->t( 'No data yet', 'Brak danych' ) ) . '</span>';
										endif;
										?>
									</td>
									<td style="color:#646970; font-size:12px;">
										<?php
										if ( $fs_time > 0 ) {
											echo esc_html( sprintf(
												/* translators: %s = human-readable time difference */
												$this->t( '%s ago', '%s temu' ),
												human_time_diff( $fs_time, $fs_now )
											) );
										} else {
											echo '&mdash;';
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
			<?php
		}

		// ---------------------------------------------------------------
		// Locale helper
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
