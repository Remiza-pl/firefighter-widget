<?php
/**
 * Events permalink settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Firefighter_Stats_Permalink_Settings_Notices' ) && class_exists( 'Firefighter_Stats_Permalink_Settings' ) ) {
    class Firefighter_Stats_Permalink_Settings_Notices extends Firefighter_Stats_Permalink_Settings {

    	public function __construct() {

			parent::__construct( array(
				'id' => 'firefighter_stats_permalink_settings',
				'title' => esc_html__( 'Firefighter Stats', 'firefighter-widget' ),
				'option_id' => 'firefighter_stats_permalinks',
				'fields' => array(
					'firefighter_stats' => array(
						'type' => 'cpt',
						'label' => esc_html__( 'Archive Slug', 'firefighter-widget' ),
						'default' => _x( 'emergency', 'URL slug', 'firefighter-widget' ),
					),
					'firefighter_stats_cat' => array(
						'type' => 'tax',
						'label' => esc_html__( 'Category Slug', 'firefighter-widget' ),
						'default' => _x( 'emergency-category', 'URL slug', 'firefighter-widget' ),
					),
					'firefighter_stats_tag' => array(
						'type' => 'tax',
						'label' => esc_html__( 'Tag Slug', 'firefighter-widget' ),
						'default' => _x( 'emergency-tag', 'URL slug', 'firefighter-widget' ),
					),
				),
			));

    	}

    }
}