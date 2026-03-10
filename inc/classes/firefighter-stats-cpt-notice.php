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
						'name' => esc_html__( 'Emergencies', 'firefighter-widget' ),
						'singular_name' => esc_html__( 'Emergency', 'firefighter-widget' ),
						'add_new' => esc_html__( 'Add New Emergency', 'firefighter-widget' ),
						'add_new_item' => esc_html__( 'Add New Emergency', 'firefighter-widget' ),
						'edit_item' => esc_html__( 'Edit Emergency', 'firefighter-widget' ),
						'new_item' => esc_html__( 'Add New Emergency', 'firefighter-widget' ),
						'view_item' => esc_html__( 'View Emergency', 'firefighter-widget' ),
						'search_items' => esc_html__( 'Search emergencies', 'firefighter-widget' ),
						'not_found' => esc_html__( 'No emergencies found', 'firefighter-widget' ),
						'not_found_in_trash' => esc_html__( 'No emergencies found in trash', 'firefighter-widget' ),
					),
					'exclude_from_search' => false,
					'public' => true,
					'supports' => array( 'title', 'editor', 'custom-fields', 'author' ),
					'capability_type' => 'post',
					'rewrite' => array( 'slug' => _x( 'emergency', 'URL slug', 'firefighter-widget' ) ),
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
						'name' => esc_html__( 'Emergency Categories', 'firefighter-widget' ),
						'singular_name' => esc_html__( 'Emergency Category', 'firefighter-widget' ),
						'search_items' => esc_html__( 'Search Emergency Categories', 'firefighter-widget' ),
						'popular_items' => esc_html__( 'Popular Emergency Categories', 'firefighter-widget' ),
						'all_items' => esc_html__( 'All Emergency Categories', 'firefighter-widget' ),
						'parent_item' => esc_html__( 'Parent Emergency Category', 'firefighter-widget' ),
						'parent_item_colon' => esc_html__( 'Parent Emergency Category:', 'firefighter-widget' ),
						'edit_item' => esc_html__( 'Edit Emergency Category', 'firefighter-widget' ),
						'update_item' => esc_html__( 'Update Emergency Category', 'firefighter-widget' ),
						'add_new_item' => esc_html__( 'Add New Emergency Category', 'firefighter-widget' ),
						'new_item_name' => esc_html__( 'New Emergency Category Name', 'firefighter-widget' ),
						'separate_items_with_commas' => esc_html__( 'Separate emergency categories by comma', 'firefighter-widget' ),
						'add_or_remove_items' => esc_html__( 'Add or remove emergency categories', 'firefighter-widget' ),
						'choose_from_most_used' => esc_html__( 'Choose from the most used emergency categories', 'firefighter-widget' ),
						'menu_name' => esc_html__( 'Emergency Categories', 'firefighter-widget' )
					),
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_admin_column' => true,
					'show_tagcloud' => true,
					'hierarchical' => true,
					'rewrite' => array( 'slug' => _x( 'emergency-category', 'URL slug', 'firefighter-widget' ) ),
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
						'name' => esc_html__( 'Emergency Tags', 'firefighter-widget' ),
						'singular_name' => esc_html__( 'Emergency Tag', 'firefighter-widget' ),
						'search_items' => esc_html__( 'Search Emergency Tags', 'firefighter-widget' ),
						'popular_items' => esc_html__( 'Popular Emergency Tags', 'firefighter-widget' ),
						'all_items' => esc_html__( 'All Emergency Tags', 'firefighter-widget' ),
						'parent_item' => esc_html__( 'Parent Emergency Tag', 'firefighter-widget' ),
						'parent_item_colon' => esc_html__( 'Parent Emergency Tag:', 'firefighter-widget' ),
						'edit_item' => esc_html__( 'Edit Emergency Tag', 'firefighter-widget' ),
						'update_item' => esc_html__( 'Update Emergency Tag', 'firefighter-widget' ),
						'add_new_item' => esc_html__( 'Add New Emergency Tag', 'firefighter-widget' ),
						'new_item_name' => esc_html__( 'New Emergency Tag Name', 'firefighter-widget' ),
						'separate_items_with_commas' => esc_html__( 'Separate emergency tags by comma', 'firefighter-widget' ),
						'add_or_remove_items' => esc_html__( 'Add or remove emergency tags', 'firefighter-widget' ),
						'choose_from_most_used' => esc_html__( 'Choose from the most used emergency tags', 'firefighter-widget' ),
						'menu_name' => esc_html__( 'Emergency Tags', 'firefighter-widget' )
					),
					'public' => true,
					'show_in_nav_menus' => true,
					'show_ui' => true,
					'show_admin_column' => true,
					'show_tagcloud' => true,
					'hierarchical' => false,
					'rewrite' => array( 'slug' => _x( 'emergency-tag', 'URL slug', 'firefighter-widget' ) ),
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