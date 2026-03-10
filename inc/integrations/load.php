<?php
/**
 * Page Builder Integrations Loader
 *
 * Registers integration hooks for Elementor, WPBakery, Beaver Builder, and Bricks.
 * Each integration is loaded only when the corresponding builder is active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Elementor — new API (≥ 3.5) with old-API fallback for < 3.5.
add_action( 'elementor/widgets/register', function ( $wm ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}
	require_once FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/elementor.php';
	$wm->register( new Firefighter_Stats_Elementor_Widget() );
} );

add_action( 'elementor/widgets/widgets_registered', function ( $wm ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) || did_action( 'elementor/widgets/register' ) ) {
		return;
	}
	require_once FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/elementor.php';
	$wm->register_widget_type( new Firefighter_Stats_Elementor_Widget() );
} );

// WPBakery — vc_before_init is the correct hook for vc_map().
add_action( 'vc_before_init', function () {
	require_once FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/wpbakery.php';
} );

// Beaver Builder — init priority 20 (FL Builder itself loads at 10).
add_action( 'init', function () {
	if ( ! class_exists( 'FLBuilder' ) ) {
		return;
	}
	require_once FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/beaver-builder.php';
}, 20 );

// Bricks — init priority 11 (shortcode class available after priority 10).
add_action( 'init', function () {
	if ( ! function_exists( 'bricks_is_builder' ) ) {
		return;
	}
	\Bricks\Elements::register_element( FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/bricks.php' );
}, 11 );
