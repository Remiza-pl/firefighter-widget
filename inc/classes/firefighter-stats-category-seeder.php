<?php
/**
 * Firefighter Stats Category Seeder
 *
 * Seeds the default emergency categories on plugin activation.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Category_Seeder' ) ) {
	class Firefighter_Stats_Category_Seeder {

		/**
		 * Seed default emergency categories.
		 *
		 * Safe to call multiple times — skips categories whose slug already exists.
		 * Filter 'firefighter_stats_default_categories' to add, remove or modify defaults.
		 */
		public static function seed(): void {
			// Ensure translations are available when seeding on activation.
			load_plugin_textdomain(
				'firefighter-stats',
				false,
				dirname( plugin_basename( FIREFIGHTER_STATS_PLUGIN_DIR . 'firefighter-stats.php' ) ) . '/languages/'
			);

			$defaults = apply_filters( 'firefighter_stats_default_categories', array(
				array(
					'name'  => __( 'Fire', 'firefighter-stats' ),
					'slug'  => 'fire',
					'icon'  => 'fire',
					'color' => '#e74c3c',
				),
				array(
					'name'  => __( 'Medical', 'firefighter-stats' ),
					'slug'  => 'medical',
					'icon'  => 'medical',
					'color' => '#3498db',
				),
				array(
					'name'  => __( 'Rescue', 'firefighter-stats' ),
					'slug'  => 'rescue',
					'icon'  => 'rescue',
					'color' => '#f39c12',
				),
				array(
					'name'  => __( 'Accident', 'firefighter-stats' ),
					'slug'  => 'accident',
					'icon'  => 'accident',
					'color' => '#e67e22',
				),
				array(
					'name'  => __( 'Local Threat', 'firefighter-stats' ),
					'slug'  => 'threat',
					'icon'  => 'threat',
					'color' => '#9b59b6',
				),
				array(
					'name'  => __( 'Hazmat', 'firefighter-stats' ),
					'slug'  => 'hazmat',
					'icon'  => 'hazmat',
					'color' => '#8e44ad',
				),
				array(
					'name'  => __( 'Water Rescue', 'firefighter-stats' ),
					'slug'  => 'water',
					'icon'  => 'water',
					'color' => '#2980b9',
				),
				array(
					'name'  => __( 'Technical', 'firefighter-stats' ),
					'slug'  => 'technical',
					'icon'  => 'technical',
					'color' => '#27ae60',
				),
				array(
					'name'  => __( 'Vehicle', 'firefighter-stats' ),
					'slug'  => 'vehicle',
					'icon'  => 'vehicle',
					'color' => '#34495e',
				),
				array(
					'name'  => __( 'Structure', 'firefighter-stats' ),
					'slug'  => 'structure',
					'icon'  => 'structure',
					'color' => '#95a5a6',
				),
				array(
					'name'  => __( 'False Alarm', 'firefighter-stats' ),
					'slug'  => 'false-alarm',
					'icon'  => 'false-alarm',
					'color' => '#7f8c8d',
				),
				array(
					'name'  => __( 'Exercise', 'firefighter-stats' ),
					'slug'  => 'exercise',
					'icon'  => 'exercise',
					'color' => '#16a085',
				),
				array(
					'name'  => __( 'Other', 'firefighter-stats' ),
					'slug'  => 'other',
					'icon'  => 'other',
					'color' => '#2c3e50',
				),
			) );

			foreach ( $defaults as $cat ) {
				if ( term_exists( $cat['slug'], 'firefighter_stats_cat' ) ) {
					continue;
				}

				$result = wp_insert_term(
					$cat['name'],
					'firefighter_stats_cat',
					array( 'slug' => $cat['slug'] )
				);

				if ( is_wp_error( $result ) ) {
					continue;
				}

				$term_id = $result['term_id'];
				update_term_meta( $term_id, 'firefighter_stats_category_icon', $cat['icon'] );
				update_term_meta( $term_id, 'firefighter_stats_category_color', $cat['color'] );
			}
		}
	}
}
