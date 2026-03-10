<?php
/**
 * Gutenberg block registration for Firefighter Statistics.
 *
 * Uses the native register_block_type() API — no external framework required.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'firefighter_stats_register_blocks', 20 );
if ( ! function_exists( 'firefighter_stats_register_blocks' ) ) {
	function firefighter_stats_register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register the editor script manually so we can declare proper dependencies.
		wp_register_script(
			'firefighter-stats-block-editor',
			FIREFIGHTER_STATS_PLUGIN_URL . 'assets/js/block-editor.js',
			array(
				'wp-blocks',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-element',
				'wp-server-side-render',
			),
			FIREFIGHTER_STATS_VERSION,
			true
		);

		// The frontend widget style is reused in the editor preview.
		wp_register_style(
			'firefighter-stats-widget',
			FIREFIGHTER_STATS_PLUGIN_URL . 'assets/css/firefighter-stats-widget.css',
			array(),
			FIREFIGHTER_STATS_VERSION
		);

		// Register the block. block.json provides the attribute schema;
		// PHP provides the render callback and script/style handles.
		register_block_type(
			FIREFIGHTER_STATS_PLUGIN_DIR . 'blocks/emergency-list-widget',
			array(
				'editor_script'   => 'firefighter-stats-block-editor',
				'style'           => 'firefighter-stats-widget',
				'render_callback' => 'firefighter_stats_render_emergency_list_block',
			)
		);
	}
}

/**
 * Server-side render callback for the Emergency Statistics block.
 *
 * Maps camelCase block attributes to the snake_case shortcode parameters
 * and delegates rendering to the shortcode class.
 *
 * @param array $attributes Block attributes from the editor.
 * @return string Rendered HTML output.
 */
if ( ! function_exists( 'firefighter_stats_render_emergency_list_block' ) ) {
	function firefighter_stats_render_emergency_list_block( $attributes ) {
		if ( ! class_exists( 'Firefighter_Stats_Shortcode_Emergency_List_Widget' ) ) {
			return '';
		}

		$bool = static function ( $attributes, $key, $default = true ) {
			return isset( $attributes[ $key ] ) ? (bool) $attributes[ $key ] : $default;
		};

		$shortcode_atts = array(
			'title'                 => isset( $attributes['title'] ) ? $attributes['title'] : '',
			'show_category_summary' => $bool( $attributes, 'showCategorySummary' ) ? 'true' : 'false',
			'category_time_period'  => isset( $attributes['categoryTimePeriod'] ) ? $attributes['categoryTimePeriod'] : 'all',
			'category_sort'         => isset( $attributes['categorySort'] ) ? $attributes['categorySort'] : 'alphabet',
			'show_zero_categories'  => $bool( $attributes, 'showZeroCategories' ) ? 'true' : 'false',
			'show_posts_list'       => $bool( $attributes, 'showPostsList' ) ? 'true' : 'false',
			'limit'                 => isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 5,
			'order'                 => isset( $attributes['order'] ) ? $attributes['order'] : 'default',
			'show_date'             => $bool( $attributes, 'showDate' ) ? 'true' : 'false',
			'show_category'         => $bool( $attributes, 'showCategory' ) ? 'true' : 'false',
		);

		return Firefighter_Stats_Shortcode_Emergency_List_Widget::shortcode( $shortcode_atts );
	}
}
