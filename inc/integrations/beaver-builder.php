<?php
/**
 * Beaver Builder Module: Firefighter Stats Emergency List
 *
 * Registers a native Beaver Builder module that wraps the emergency list shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FLBuilderModule' ) || ! class_exists( 'FLBuilder' ) ) {
	return;
}

if ( ! class_exists( 'Firefighter_Stats_Beaver_Module' ) ) {

	class Firefighter_Stats_Beaver_Module extends FLBuilderModule {

		public function __construct() {
			parent::__construct( array(
				'name'            => firefighter_stats_t( 'Emergency Statistics', 'Statystyki Wyjazdów' ),
				'description'     => firefighter_stats_t( 'Display emergency statistics.', 'Wyświetla statystyki wyjazdów.' ),
				'category'        => firefighter_stats_t( 'Emergency Statistics', 'Statystyki' ),
				'partial_refresh' => true,
			) );

			// Tell Beaver Builder where to find the frontend template.
			$this->dir = FIREFIGHTER_STATS_PLUGIN_DIR . 'inc/integrations/beaver-builder/';
		}
	}
}

// Build category select options.
$firefighter_stats_bb_options = array( '' => firefighter_stats_t( 'All Categories', 'Wszystkie kategorie' ) );
$firefighter_stats_bb_terms = get_terms( array(
	'taxonomy'   => 'firefighter_stats_cat',
	'hide_empty' => false,
) );
if ( ! is_wp_error( $firefighter_stats_bb_terms ) && ! empty( $firefighter_stats_bb_terms ) ) {
	foreach ( $firefighter_stats_bb_terms as $firefighter_stats_bb_term ) {
		$firefighter_stats_bb_options[ (string) $firefighter_stats_bb_term->term_id ] = esc_html( $firefighter_stats_bb_term->name );
	}
}

FLBuilder::register_module(
	'Firefighter_Stats_Beaver_Module',
	array(

		// Tab: General.
		'general' => array(
			'title'    => firefighter_stats_t( 'General', 'Ogólne' ),
			'sections' => array(
				'general_section' => array(
					'title'  => '',
					'fields' => array(

						'title' => array(
							'type'    => 'text',
							'label'   => firefighter_stats_t( 'Title', 'Tytuł' ),
							'default' => firefighter_stats_t( '🚨 Emergency Statistics', '🚨 Statystyki wyjazdów' ),
						),

						'category_time_period' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Category Count Period', 'Okres liczenia kategorii' ),
							'default' => 'year',
							'options' => array(
								'year'  => firefighter_stats_t( 'This Year', 'Ten rok' ),
								'month' => firefighter_stats_t( 'This Month', 'Ten miesiąc' ),
								'all'   => firefighter_stats_t( 'All Time', 'Cały czas' ),
							),
						),

					),
				),
			),
		),

		// Tab: Category Summary.
		'category_summary' => array(
			'title'    => firefighter_stats_t( 'Category Summary', 'Podsumowanie kategorii' ),
			'sections' => array(
				'category_summary_section' => array(
					'title'  => '',
					'fields' => array(

						'show_category_summary' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Show Category Summary', 'Pokaż podsumowanie kategorii' ),
							'default' => '1',
							'options' => array(
								'1' => firefighter_stats_t( 'Yes', 'Tak' ),
								''  => firefighter_stats_t( 'No', 'Nie' ),
							),
						),

						'category_sort' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Sort Categories By', 'Sortuj kategorie' ),
							'default' => 'alphabet',
							'options' => array(
								'alphabet'   => firefighter_stats_t( 'Alphabetical', 'Alfabetycznie' ),
								'count_desc' => firefighter_stats_t( 'Count (High to Low)', 'Malejąco' ),
								'count_asc'  => firefighter_stats_t( 'Count (Low to High)', 'Rosnąco' ),
							),
						),

						'show_zero_categories' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Show Zero-Count Categories', 'Pokaż kategorie z zerem' ),
							'default' => '1',
							'options' => array(
								'1' => firefighter_stats_t( 'Yes', 'Tak' ),
								''  => firefighter_stats_t( 'No', 'Nie' ),
							),
						),

					),
				),
			),
		),

		// Tab: Posts List.
		'posts_list' => array(
			'title'    => firefighter_stats_t( 'Posts List', 'Lista wpisów' ),
			'sections' => array(
				'posts_list_section' => array(
					'title'  => '',
					'fields' => array(

						'show_posts_list' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Show Emergency Posts List', 'Pokaż listę wyjazdów' ),
							'default' => '1',
							'options' => array(
								'1' => firefighter_stats_t( 'Yes', 'Tak' ),
								''  => firefighter_stats_t( 'No', 'Nie' ),
							),
						),

						'category' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Posts Category Filter', 'Filtr kategorii wpisów' ),
							'default' => '',
							'options' => $firefighter_stats_bb_options,
						),

						'limit' => array(
							'type'    => 'text',
							'label'   => firefighter_stats_t( 'Posts Limit', 'Limit wpisów' ),
							'default' => '5',
						),

						'order' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Order', 'Kolejność' ),
							'default' => 'default',
							'options' => array(
								'default'    => firefighter_stats_t( 'Default', 'Domyślna' ),
								'date_desc'  => firefighter_stats_t( 'By date, newest first', 'Najnowsze' ),
								'date_asc'   => firefighter_stats_t( 'By date, oldest first', 'Najstarsze' ),
								'title_asc'  => firefighter_stats_t( 'By title, ascending', 'A–Z' ),
								'title_desc' => firefighter_stats_t( 'By title, descending', 'Z–A' ),
								'random'     => firefighter_stats_t( 'Random', 'Losowa' ),
							),
						),

						'show_date' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Display Date', 'Pokaż datę' ),
							'default' => '1',
							'options' => array(
								'1' => firefighter_stats_t( 'Yes', 'Tak' ),
								''  => firefighter_stats_t( 'No', 'Nie' ),
							),
						),

						'show_category' => array(
							'type'    => 'select',
							'label'   => firefighter_stats_t( 'Display Category', 'Pokaż kategorię' ),
							'default' => '1',
							'options' => array(
								'1' => firefighter_stats_t( 'Yes', 'Tak' ),
								''  => firefighter_stats_t( 'No', 'Nie' ),
							),
						),

						'recent_emergencies_title' => array(
							'type'    => 'text',
							'label'   => firefighter_stats_t( 'Recent Emergencies Title', 'Tytuł ostatnich wyjazdów' ),
							'default' => firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ),
						),

						'more_label' => array(
							'type'    => 'text',
							'label'   => firefighter_stats_t( 'More Link Label', 'Etykieta linku "więcej"' ),
							'default' => '',
						),

					),
				),
			),
		),

	)
);
