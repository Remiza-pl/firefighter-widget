<?php
/**
 * Plugin Name:       Firefighter Statistics
 * Plugin URI:        https://github.com/sync667/firefighter-widget
 * Description:       Track and display emergency statistics for fire departments. Includes widgets, a Gutenberg block, and a shortcode.
 * Version:           1.0.0
 * Author:            sync667
 * Author URI:        https://github.com/sync667
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       firefighter-stats
 * Domain Path:       /languages
 * Requires at least: 5.9
 * Tested up to:      6.7
 * Requires PHP:      7.4
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FIREFIGHTER_STATS_VERSION', '1.0.0' );
define( 'FIREFIGHTER_STATS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FIREFIGHTER_STATS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load text domain for translations
add_action( 'plugins_loaded', 'firefighter_stats_load_textdomain' );
if ( ! function_exists( 'firefighter_stats_load_textdomain' ) ) {
	function firefighter_stats_load_textdomain(): void {
		load_plugin_textdomain( 'firefighter-stats', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Check if we need to flush rewrite rules due to language change
		firefighter_stats_maybe_flush_rewrites();
	}
}

// Flush rewrite rules if language changed
if ( ! function_exists( 'firefighter_stats_maybe_flush_rewrites' ) ) {
	function firefighter_stats_maybe_flush_rewrites(): void {
		$current_locale = get_locale();
		$saved_locale   = get_option( 'firefighter_stats_locale', '' );

		if ( $saved_locale !== $current_locale ) {
			update_option( 'firefighter_stats_locale', $current_locale );

			if ( is_admin() ) {
				add_action( 'admin_init', static function () {
					flush_rewrite_rules();
				}, 20 );
			}
		}
	}
}

// Include additional functions and classes
$firefighter_stats_plugin_dir = plugin_dir_path( __FILE__ );
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-cpt.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-cpt-notice.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-permalink-settings.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-permalink-settings-notices.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-widget.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-category-meta.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-admin-counts.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-category-seeder.php';
require_once $firefighter_stats_plugin_dir . 'inc/classes/firefighter-stats-admin-guide.php';
require_once $firefighter_stats_plugin_dir . 'inc/core-functions.php';
require_once $firefighter_stats_plugin_dir . 'inc/blocks-config.php';
require_once $firefighter_stats_plugin_dir . 'inc/integrations/load.php';
unset( $firefighter_stats_plugin_dir );

// Register Notice CPT
if ( class_exists( 'Firefighter_Stats_CPT_Notice' ) ) {

	// Register CPT on plugin activation
	if ( ! function_exists( 'firefighter_stats_activate_register_cpt' ) ) {
		function firefighter_stats_activate_register_cpt(): void {
			$firefighter_stats_cpt = new Firefighter_Stats_CPT_Notice();
			$firefighter_stats_cpt->activate_cpt();
			if ( class_exists( 'Firefighter_Stats_Category_Seeder' ) ) {
				Firefighter_Stats_Category_Seeder::seed();
			}
		}
	}
	register_activation_hook( __FILE__, 'firefighter_stats_activate_register_cpt' );

	// Register CPT
	add_action( 'init', 'firefighter_stats_register_cpt', 5 );
	if ( ! function_exists( 'firefighter_stats_register_cpt' ) ) {
		function firefighter_stats_register_cpt(): void {
			$firefighter_stats_cpt = new Firefighter_Stats_CPT_Notice();
		}
	}

}

// Initialize category meta fields
if ( class_exists( 'Firefighter_Stats_Category_Meta' ) ) {
	add_action( 'init', 'firefighter_stats_init_category_meta' );
	if ( ! function_exists( 'firefighter_stats_init_category_meta' ) ) {
		function firefighter_stats_init_category_meta(): void {
			new Firefighter_Stats_Category_Meta();
		}
	}
}

// Initialize admin counts management
if ( class_exists( 'Firefighter_Stats_Admin_Counts' ) ) {
	add_action( 'init', 'firefighter_stats_init_admin_counts' );
	if ( ! function_exists( 'firefighter_stats_init_admin_counts' ) ) {
		function firefighter_stats_init_admin_counts(): void {
			// Load on both admin and frontend for admin bar functionality
			new Firefighter_Stats_Admin_Counts();
		}
	}
}

// Initialize Getting Started guide page.
if ( class_exists( 'Firefighter_Stats_Admin_Guide' ) ) {
	add_action( 'init', static function () {
		new Firefighter_Stats_Admin_Guide();
	} );
}

// Enforce category assignment: revert post to draft if published without a category.
add_action( 'wp_insert_post', 'firefighter_stats_require_category', 10, 3 );
if ( ! function_exists( 'firefighter_stats_require_category' ) ) {
	function firefighter_stats_require_category( $post_id, $post, $update ) {
		if ( 'firefighter_stats' !== $post->post_type ) {
			return;
		}
		if ( 'publish' !== $post->post_status ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$terms = wp_get_post_terms( $post_id, 'firefighter_stats_cat', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			remove_action( 'wp_insert_post', 'firefighter_stats_require_category', 10 );
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			) );
			add_action( 'wp_insert_post', 'firefighter_stats_require_category', 10, 3 );
			set_transient( 'firefighter_stats_category_notice_' . get_current_user_id(), true, 60 );
		}
	}
}

// Display admin notice when a post was reverted due to missing category.
add_action( 'admin_notices', 'firefighter_stats_category_notice' );
if ( ! function_exists( 'firefighter_stats_category_notice' ) ) {
	function firefighter_stats_category_notice() {
		$key = 'firefighter_stats_category_notice_' . get_current_user_id();
		if ( get_transient( $key ) ) {
			delete_transient( $key );
			echo '<div class="notice notice-error"><p>' .
				esc_html__( 'The emergency post was reverted to draft because no category was assigned. Please select a category before publishing.', 'firefighter-stats' ) .
				'</p></div>';
		}
	}
}

// Add permalink settings
if ( class_exists( 'Firefighter_Stats_Permalink_Settings_Notices' ) ) {
	add_action( 'init', 'firefighter_stats_register_permalink_settings' );
	if ( ! function_exists( 'firefighter_stats_register_permalink_settings' ) ) {
		function firefighter_stats_register_permalink_settings(): void {
			$permalink_settings = new Firefighter_Stats_Permalink_Settings_Notices();
		}
	}
}

// Register widgets
add_action( 'widgets_init', 'firefighter_stats_register_widgets' );
if ( ! function_exists( 'firefighter_stats_register_widgets' ) ) {
	function firefighter_stats_register_widgets(): void {

		// Emergency categories
		require_once plugin_dir_path( __FILE__ ) . 'inc/classes/widgets/firefighter-stats-widget-emergency-categories.php';
		if ( class_exists( 'Firefighter_Stats_Widget_Emergency_Categories' ) ) {
			register_widget( 'Firefighter_Stats_Widget_Emergency_Categories' );
		}

		// Emergency list
		require_once plugin_dir_path( __FILE__ ) . 'inc/classes/widgets/firefighter-stats-widget-emergency-list.php';
		if ( class_exists( 'Firefighter_Stats_Widget_Emergency_List' ) ) {
			register_widget( 'Firefighter_Stats_Widget_Emergency_List' );
		}

	}
}

// Register shortcodes
add_action( 'init', 'firefighter_stats_register_shortcodes' );
if ( ! function_exists( 'firefighter_stats_register_shortcodes' ) ) {
	function firefighter_stats_register_shortcodes(): void {

		// Emergency List Widget
		require_once plugin_dir_path( __FILE__ ) . 'inc/classes/shortcodes/firefighter-stats-shortcode-emergency-list-widget.php';
		if ( class_exists( 'Firefighter_Stats_Shortcode_Emergency_List_Widget' ) ) {
			add_shortcode( 'firefighter_stats_emergency_list_widget', array(
				'Firefighter_Stats_Shortcode_Emergency_List_Widget',
				'shortcode',
			) );
		}

	}
}

// Enqueue widget styles on the frontend.
// The same handle is registered by blocks-config.php for the block editor style.
add_action( 'wp_enqueue_scripts', 'firefighter_stats_enqueue_styles' );
if ( ! function_exists( 'firefighter_stats_enqueue_styles' ) ) {
	function firefighter_stats_enqueue_styles(): void {
		wp_enqueue_style(
			'firefighter-stats-widget',
			FIREFIGHTER_STATS_PLUGIN_URL . 'assets/css/firefighter-stats-widget.css',
			array(),
			FIREFIGHTER_STATS_VERSION
		);
	}
}