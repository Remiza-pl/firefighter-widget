<?php
/**
 * WPBakery Page Builder Integration
 *
 * Maps the emergency list shortcode as a WPBakery element via vc_map().
 * No custom render needed — WPBakery executes the shortcode by its base tag.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vc_map' ) ) {
	return;
}

// Build category dropdown options.
$vc_category_options = array( firefighter_stats_t( 'All Categories', 'Wszystkie kategorie' ) => '' );
$vc_terms = get_terms( array(
	'taxonomy'   => 'firefighter_stats_cat',
	'hide_empty' => false,
) );
if ( ! is_wp_error( $vc_terms ) && ! empty( $vc_terms ) ) {
	foreach ( $vc_terms as $vc_term ) {
		$vc_category_options[ esc_html( $vc_term->name ) ] = (string) $vc_term->term_id;
	}
}

vc_map( array(
	'base'        => 'firefighter_stats_emergency_list_widget',
	'name'        => firefighter_stats_t( 'Emergency Statistics', 'Statystyki Wyjazdów' ),
	'icon'        => 'dashicons dashicons-shield-alt',
	'category'    => firefighter_stats_t( 'Emergency Statistics', 'Statystyki' ),
	'description' => firefighter_stats_t( 'Display emergency statistics.', 'Wyświetla statystyki wyjazdów.' ),
	'params'      => array(

		array(
			'type'        => 'textfield',
			'heading'     => firefighter_stats_t( 'Title', 'Tytuł' ),
			'param_name'  => 'title',
			'value'       => firefighter_stats_t( '🚨 Emergency Statistics', '🚨 Statystyki wyjazdów' ),
			'description' => firefighter_stats_t( 'Title of this section.', 'Tytuł sekcji.' ),
		),

		array(
			'type'        => 'dropdown',
			'heading'     => firefighter_stats_t( 'Category Count Period', 'Okres liczenia kategorii' ),
			'param_name'  => 'category_time_period',
			'value'       => array(
				firefighter_stats_t( 'This Year', 'Ten rok' )     => 'year',
				firefighter_stats_t( 'This Month', 'Ten miesiąc' ) => 'month',
				firefighter_stats_t( 'All Time', 'Cały czas' )    => 'all',
			),
			'std'         => 'year',
			'description' => firefighter_stats_t( 'Time period for category emergency counts.', 'Zakres czasu dla liczenia kategorii.' ),
		),

		array(
			'type'        => 'checkbox',
			'heading'     => firefighter_stats_t( 'Show Category Summary', 'Pokaż podsumowanie kategorii' ),
			'param_name'  => 'show_category_summary',
			'value'       => array( firefighter_stats_t( 'Enable', 'Włącz' ) => 'true' ),
			'std'         => 'true',
			'description' => firefighter_stats_t( 'Display category counts at the top.', 'Wyświetl liczby kategorii u góry.' ),
		),

		array(
			'type'        => 'dropdown',
			'heading'     => firefighter_stats_t( 'Sort Categories By', 'Sortuj kategorie' ),
			'param_name'  => 'category_sort',
			'value'       => array(
				firefighter_stats_t( 'Alphabetical', 'Alfabetycznie' )     => 'alphabet',
				firefighter_stats_t( 'Count (High to Low)', 'Malejąco' )   => 'count_desc',
				firefighter_stats_t( 'Count (Low to High)', 'Rosnąco' )    => 'count_asc',
			),
			'std'         => 'alphabet',
			'dependency'  => array( 'element' => 'show_category_summary', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'checkbox',
			'heading'     => firefighter_stats_t( 'Show Zero-Count Categories', 'Pokaż kategorie z zerem' ),
			'param_name'  => 'show_zero_categories',
			'value'       => array( firefighter_stats_t( 'Enable', 'Włącz' ) => 'true' ),
			'std'         => 'true',
			'dependency'  => array( 'element' => 'show_category_summary', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'checkbox',
			'heading'     => firefighter_stats_t( 'Show Emergency Posts List', 'Pokaż listę wyjazdów' ),
			'param_name'  => 'show_posts_list',
			'value'       => array( firefighter_stats_t( 'Enable', 'Włącz' ) => 'true' ),
			'std'         => 'true',
		),

		array(
			'type'        => 'dropdown',
			'heading'     => firefighter_stats_t( 'Posts Category Filter', 'Filtr kategorii wpisów' ),
			'param_name'  => 'category',
			'value'       => $vc_category_options,
			'std'         => '',
			'description' => firefighter_stats_t( 'Display emergency posts from a specific category.', 'Wyświetl wpisy z wybranej kategorii.' ),
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'textfield',
			'heading'     => firefighter_stats_t( 'Posts Limit', 'Limit wpisów' ),
			'param_name'  => 'limit',
			'value'       => '5',
			'description' => firefighter_stats_t( 'How many emergency posts to display.', 'Ile wyjazdów wyświetlić.' ),
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'dropdown',
			'heading'     => firefighter_stats_t( 'Order', 'Kolejność' ),
			'param_name'  => 'order',
			'value'       => array(
				firefighter_stats_t( 'Default', 'Domyślna' )               => 'default',
				firefighter_stats_t( 'By date, newest first', 'Najnowsze' ) => 'date_desc',
				firefighter_stats_t( 'By date, oldest first', 'Najstarsze' ) => 'date_asc',
				firefighter_stats_t( 'By title, ascending', 'A–Z' )        => 'title_asc',
				firefighter_stats_t( 'By title, descending', 'Z–A' )       => 'title_desc',
				firefighter_stats_t( 'Random', 'Losowa' )                  => 'random',
			),
			'std'         => 'default',
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'checkbox',
			'heading'     => firefighter_stats_t( 'Display Date', 'Pokaż datę' ),
			'param_name'  => 'show_date',
			'value'       => array( firefighter_stats_t( 'Enable', 'Włącz' ) => 'true' ),
			'std'         => 'true',
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'checkbox',
			'heading'     => firefighter_stats_t( 'Display Category', 'Pokaż kategorię' ),
			'param_name'  => 'show_category',
			'value'       => array( firefighter_stats_t( 'Enable', 'Włącz' ) => 'true' ),
			'std'         => 'true',
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'textfield',
			'heading'     => firefighter_stats_t( 'Recent Emergencies Title', 'Tytuł ostatnich wyjazdów' ),
			'param_name'  => 'recent_emergencies_title',
			'value'       => firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ),
			'dependency'  => array( 'element' => 'show_posts_list', 'value' => array( 'true' ) ),
		),

		array(
			'type'        => 'textfield',
			'heading'     => firefighter_stats_t( 'More Link Label', 'Etykieta linku "więcej"' ),
			'param_name'  => 'more_label',
			'value'       => '',
			'description' => firefighter_stats_t( 'Leave empty to hide the link.', 'Zostaw puste, aby ukryć link.' ),
		),

	),
) );
