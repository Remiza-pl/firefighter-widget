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

			$updated = isset( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html( $this->t( 'Settings saved.', 'Ustawienia zapisane.' ) ); ?></p>
					</div>
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
