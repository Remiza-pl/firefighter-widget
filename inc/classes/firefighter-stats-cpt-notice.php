<?php
/**
 * Emergency post type
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Firefighter_Stats_CPT_Notice' ) && class_exists( 'Firefighter_Stats_CPT' ) ) {
    class Firefighter_Stats_CPT_Notice extends Firefighter_Stats_CPT {

		public function __construct() {

			parent::__construct( array(
				'id' => 'firefighter_stats',
				'wp_args' => array(
					'labels' => array(
						'name' => esc_html__( 'Emergencies', 'firefighter-stats' ),
						'singular_name' => esc_html__( 'Emergency', 'firefighter-stats' ),
						'add_new' => esc_html__( 'Add New Emergency', 'firefighter-stats' ),
						'add_new_item' => esc_html__( 'Add New Emergency', 'firefighter-stats' ),
						'edit_item' => esc_html__( 'Edit Emergency', 'firefighter-stats' ),
						'new_item' => esc_html__( 'Add New Emergency', 'firefighter-stats' ),
						'view_item' => esc_html__( 'View Emergency', 'firefighter-stats' ),
						'search_items' => esc_html__( 'Search emergencies', 'firefighter-stats' ),
						'not_found' => esc_html__( 'No emergencies found', 'firefighter-stats' ),
						'not_found_in_trash' => esc_html__( 'No emergencies found in trash', 'firefighter-stats' ),
					),
					'exclude_from_search' => false,
					'public' => true,
					'supports' => array( 'title', 'editor', 'custom-fields', 'author' ),
					'capability_type' => 'post',
					'rewrite' => array( 'slug' => _x( 'emergency', 'URL slug', 'firefighter-stats' ) ),
					'menu_position' => 5,
					'has_archive' => true,
					'show_in_nav_menus' => true,
					'show_in_rest' => true,
					'menu_icon' => 'dashicons-shield-alt',
				),
			));

			// Add Category taxonomy
			$this->add_taxonomy(array(
				'id' => 'firefighter_stats_cat',
				'wp_args' => array(
					'labels' => array(
						'name' => esc_html__( 'Emergency Categories', 'firefighter-stats' ),
						'singular_name' => esc_html__( 'Emergency Category', 'firefighter-stats' ),
						'search_items' => esc_html__( 'Search Emergency Categories', 'firefighter-stats' ),
						'popular_items' => esc_html__( 'Popular Emergency Categories', 'firefighter-stats' ),
						'all_items' => esc_html__( 'All Emergency Categories', 'firefighter-stats' ),
						'parent_item' => esc_html__( 'Parent Emergency Category', 'firefighter-stats' ),
						'parent_item_colon' => esc_html__( 'Parent Emergency Category:', 'firefighter-stats' ),
						'edit_item' => esc_html__( 'Edit Emergency Category', 'firefighter-stats' ),
						'update_item' => esc_html__( 'Update Emergency Category', 'firefighter-stats' ),
						'add_new_item' => esc_html__( 'Add New Emergency Category', 'firefighter-stats' ),
						'new_item_name' => esc_html__( 'New Emergency Category Name', 'firefighter-stats' ),
						'separate_items_with_commas' => esc_html__( 'Separate emergency categories by comma', 'firefighter-stats' ),
						'add_or_remove_items' => esc_html__( 'Add or remove emergency categories', 'firefighter-stats' ),
						'choose_from_most_used' => esc_html__( 'Choose from the most used emergency categories', 'firefighter-stats' ),
						'menu_name' => esc_html__( 'Emergency Categories', 'firefighter-stats' )
					),
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_admin_column' => true,
					'show_tagcloud' => true,
					'hierarchical' => true,
					'rewrite' => array( 'slug' => _x( 'emergency-category', 'URL slug', 'firefighter-stats' ) ),
					'query_var' => true,
					'show_in_rest' => true,
				),
				'args' => array(
					'admin_tax_filter' => true,
				),
			));

			// Add Tag taxonomy
			$this->add_taxonomy(array(
				'id' => 'firefighter_stats_tag',
				'wp_args' => array(
					'labels' => array(
						'name' => esc_html__( 'Emergency Tags', 'firefighter-stats' ),
						'singular_name' => esc_html__( 'Emergency Tag', 'firefighter-stats' ),
						'search_items' => esc_html__( 'Search Emergency Tags', 'firefighter-stats' ),
						'popular_items' => esc_html__( 'Popular Emergency Tags', 'firefighter-stats' ),
						'all_items' => esc_html__( 'All Emergency Tags', 'firefighter-stats' ),
						'parent_item' => esc_html__( 'Parent Emergency Tag', 'firefighter-stats' ),
						'parent_item_colon' => esc_html__( 'Parent Emergency Tag:', 'firefighter-stats' ),
						'edit_item' => esc_html__( 'Edit Emergency Tag', 'firefighter-stats' ),
						'update_item' => esc_html__( 'Update Emergency Tag', 'firefighter-stats' ),
						'add_new_item' => esc_html__( 'Add New Emergency Tag', 'firefighter-stats' ),
						'new_item_name' => esc_html__( 'New Emergency Tag Name', 'firefighter-stats' ),
						'separate_items_with_commas' => esc_html__( 'Separate emergency tags by comma', 'firefighter-stats' ),
						'add_or_remove_items' => esc_html__( 'Add or remove emergency tags', 'firefighter-stats' ),
						'choose_from_most_used' => esc_html__( 'Choose from the most used emergency tags', 'firefighter-stats' ),
						'menu_name' => esc_html__( 'Emergency Tags', 'firefighter-stats' )
					),
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_admin_column' => true,
					'show_tagcloud' => true,
					'hierarchical' => false,
					'rewrite' => array( 'slug' => _x( 'emergency-tag', 'URL slug', 'firefighter-stats' ) ),
					'query_var' => true,
					'show_in_rest' => true,
				),
				'args' => array(
					'admin_tax_filter' => true,
				),
			));

		}

	}
}