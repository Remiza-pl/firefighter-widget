<?php
/**
 * Firefighter Stats Category Seeder
 *
 * Seeds the default emergency categories on plugin activation.
 * Category names are seeded in the site's language (get_locale()),
 * not the activating admin's personal language preference.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Category_Seeder' ) ) {
	class Firefighter_Stats_Category_Seeder {

		/**
		 * Return the localised version of a category name.
		 * Uses the site locale (get_locale()), not the user locale.
		 * Only English and Polish are supported; falls back to English.
		 *
		 * @param string $en English name.
		 * @param string $pl Polish name.
		 * @return string
		 */
		private static function cat_name( $en, $pl ) {
			static $is_pl = null;
			if ( null === $is_pl ) {
				$is_pl = ( strpos( get_locale(), 'pl' ) === 0 );
			}
			return $is_pl ? $pl : $en;
		}

		/**
		 * Seed default emergency categories.
		 *
		 * Safe to call multiple times — skips categories whose slug already exists.
		 * Filter 'firefighter_stats_default_categories' to add, remove or modify defaults.
		 */
		public static function seed(): void {
			$defaults = apply_filters( 'firefighter_stats_default_categories', array(
				array(
					'name'  => self::cat_name( 'Fire', 'Pożar' ),
					'slug'  => 'fire',
					'icon'  => 'fire',
					'color' => '#e74c3c',
				),
				array(
					'name'  => self::cat_name( 'Medical', 'Medyczny' ),
					'slug'  => 'medical',
					'icon'  => 'medical',
					'color' => '#3498db',
				),
				array(
					'name'  => self::cat_name( 'Rescue', 'Ratownictwo' ),
					'slug'  => 'rescue',
					'icon'  => 'rescue',
					'color' => '#f39c12',
				),
				array(
					'name'  => self::cat_name( 'Accident', 'Wypadek' ),
					'slug'  => 'accident',
					'icon'  => 'accident',
					'color' => '#e67e22',
				),
				array(
					'name'  => self::cat_name( 'Local Threat', 'Miejscowe Zagrożenie' ),
					'slug'  => 'threat',
					'icon'  => 'threat',
					'color' => '#9b59b6',
				),
				array(
					'name'  => self::cat_name( 'Hazmat', 'Chemiczny' ),
					'slug'  => 'hazmat',
					'icon'  => 'hazmat',
					'color' => '#8e44ad',
				),
				array(
					'name'  => self::cat_name( 'Water Rescue', 'Ratownictwo Wodne' ),
					'slug'  => 'water',
					'icon'  => 'water',
					'color' => '#2980b9',
				),
				array(
					'name'  => self::cat_name( 'Technical', 'Techniczny' ),
					'slug'  => 'technical',
					'icon'  => 'technical',
					'color' => '#27ae60',
				),
				array(
					'name'  => self::cat_name( 'Vehicle', 'Pojazd' ),
					'slug'  => 'vehicle',
					'icon'  => 'vehicle',
					'color' => '#34495e',
				),
				array(
					'name'  => self::cat_name( 'Structure', 'Budynek' ),
					'slug'  => 'structure',
					'icon'  => 'structure',
					'color' => '#95a5a6',
				),
				array(
					'name'  => self::cat_name( 'False Alarm', 'Alarm Fałszywy' ),
					'slug'  => 'false-alarm',
					'icon'  => 'false-alarm',
					'color' => '#7f8c8d',
				),
				array(
					'name'  => self::cat_name( 'Exercise', 'Ćwiczenia' ),
					'slug'  => 'exercise',
					'icon'  => 'exercise',
					'color' => '#16a085',
				),
				array(
					'name'  => self::cat_name( 'Other', 'Inne' ),
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
