<?php
/**
 * Bricks Builder Element: Firefighter Stats Emergency List
 *
 * Registers a native Bricks element that wraps the emergency list shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Bricks\Element' ) ) {
	return;
}

if ( ! class_exists( 'Firefighter_Stats_Bricks_Element' ) ) {

	class Firefighter_Stats_Bricks_Element extends \Bricks\Element {

		public $category = 'general';
		public $name     = 'firefighter-stats-emergency-list';
		public $icon     = 'ti-shield';
		public $css_selector = '.firefighter-stats-emergency-list-widget';

		public function get_label() {
			return firefighter_stats_t( 'Emergency Statistics', 'Statystyki Wyjazdów' );
		}

		public function set_controls() {

			// Title.
			$this->controls['title'] = array(
				'tab'     => 'content',
				'label'   => firefighter_stats_t( 'Title', 'Tytuł' ),
				'type'    => 'text',
				'default' => firefighter_stats_t( '🚨 Emergency Statistics', '🚨 Statystyki wyjazdów' ),
			);

			// Category Count Period.
			$this->controls['category_time_period'] = array(
				'tab'     => 'content',
				'label'   => firefighter_stats_t( 'Category Count Period', 'Okres liczenia kategorii' ),
				'type'    => 'select',
				'options' => array(
					'year'  => firefighter_stats_t( 'This Year', 'Ten rok' ),
					'month' => firefighter_stats_t( 'This Month', 'Ten miesiąc' ),
					'all'   => firefighter_stats_t( 'All Time', 'Cały czas' ),
				),
				'default' => 'year',
			);

			// Show Category Summary.
			$this->controls['show_category_summary'] = array(
				'tab'     => 'content',
				'label'   => firefighter_stats_t( 'Show Category Summary', 'Pokaż podsumowanie kategorii' ),
				'type'    => 'checkbox',
				'default' => true,
			);

			// Sort Categories By.
			$this->controls['category_sort'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Sort Categories By', 'Sortuj kategorie' ),
				'type'     => 'select',
				'options'  => array(
					'alphabet'   => firefighter_stats_t( 'Alphabetical', 'Alfabetycznie' ),
					'count_desc' => firefighter_stats_t( 'Count (High to Low)', 'Malejąco' ),
					'count_asc'  => firefighter_stats_t( 'Count (Low to High)', 'Rosnąco' ),
				),
				'default'  => 'alphabet',
				'required' => array( 'show_category_summary', '=', true ),
			);

			// Show Zero-Count Categories.
			$this->controls['show_zero_categories'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Show Zero-Count Categories', 'Pokaż kategorie z zerem' ),
				'type'     => 'checkbox',
				'default'  => true,
				'required' => array( 'show_category_summary', '=', true ),
			);

			// Show Posts List.
			$this->controls['show_posts_list'] = array(
				'tab'     => 'content',
				'label'   => firefighter_stats_t( 'Show Emergency Posts List', 'Pokaż listę wyjazdów' ),
				'type'    => 'checkbox',
				'default' => true,
			);

			// Category Filter for Posts.
			$bricks_category_options = array( '0' => firefighter_stats_t( 'All Categories', 'Wszystkie kategorie' ) );
			$bricks_terms = get_terms( array(
				'taxonomy'   => 'firefighter_stats_cat',
				'hide_empty' => false,
			) );
			if ( ! is_wp_error( $bricks_terms ) && ! empty( $bricks_terms ) ) {
				foreach ( $bricks_terms as $bricks_term ) {
					$bricks_category_options[ (string) $bricks_term->term_id ] = esc_html( $bricks_term->name );
				}
			}
			$this->controls['category'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Posts Category Filter', 'Filtr kategorii wpisów' ),
				'type'     => 'select',
				'options'  => $bricks_category_options,
				'default'  => '0',
				'required' => array( 'show_posts_list', '=', true ),
			);

			// Posts Limit.
			$this->controls['limit'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Posts Limit', 'Limit wpisów' ),
				'type'     => 'number',
				'min'      => 1,
				'max'      => 100,
				'default'  => 5,
				'required' => array( 'show_posts_list', '=', true ),
			);

			// Order.
			$this->controls['order'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Order', 'Kolejność' ),
				'type'     => 'select',
				'options'  => array(
					'default'    => firefighter_stats_t( 'Default', 'Domyślna' ),
					'date_desc'  => firefighter_stats_t( 'By date, newest first', 'Najnowsze' ),
					'date_asc'   => firefighter_stats_t( 'By date, oldest first', 'Najstarsze' ),
					'title_asc'  => firefighter_stats_t( 'By title, ascending', 'A–Z' ),
					'title_desc' => firefighter_stats_t( 'By title, descending', 'Z–A' ),
					'random'     => firefighter_stats_t( 'Random', 'Losowa' ),
				),
				'default'  => 'default',
				'required' => array( 'show_posts_list', '=', true ),
			);

			// Show Date.
			$this->controls['show_date'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Display Date', 'Pokaż datę' ),
				'type'     => 'checkbox',
				'default'  => true,
				'required' => array( 'show_posts_list', '=', true ),
			);

			// Show Category.
			$this->controls['show_category'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Display Category', 'Pokaż kategorię' ),
				'type'     => 'checkbox',
				'default'  => true,
				'required' => array( 'show_posts_list', '=', true ),
			);

			// Recent Emergencies Title.
			$this->controls['recent_emergencies_title'] = array(
				'tab'      => 'content',
				'label'    => firefighter_stats_t( 'Recent Emergencies Title', 'Tytuł ostatnich wyjazdów' ),
				'type'     => 'text',
				'default'  => firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ),
				'required' => array( 'show_posts_list', '=', true ),
			);

			// More Link Label.
			$this->controls['more_label'] = array(
				'tab'         => 'content',
				'label'       => firefighter_stats_t( 'More Link Label', 'Etykieta linku "więcej"' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => firefighter_stats_t( 'Leave empty to hide', 'Zostaw puste, aby ukryć' ),
			);
		}

		public function render() {
			$s = $this->settings;

			$atts = array(
				'title'                    => isset( $s['title'] ) ? $s['title'] : '',
				'category_time_period'     => isset( $s['category_time_period'] ) ? $s['category_time_period'] : 'year',
				'show_category_summary'    => ! empty( $s['show_category_summary'] ) ? 'true' : 'false',
				'category_sort'            => isset( $s['category_sort'] ) ? $s['category_sort'] : 'alphabet',
				'show_zero_categories'     => ! empty( $s['show_zero_categories'] ) ? 'true' : 'false',
				'show_posts_list'          => ! empty( $s['show_posts_list'] ) ? 'true' : 'false',
				'category'                 => isset( $s['category'] ) ? intval( $s['category'] ) : 0,
				'limit'                    => isset( $s['limit'] ) ? intval( $s['limit'] ) : 5,
				'order'                    => isset( $s['order'] ) ? $s['order'] : 'default',
				'show_date'                => ! empty( $s['show_date'] ) ? 'true' : 'false',
				'show_category'            => ! empty( $s['show_category'] ) ? 'true' : 'false',
				'recent_emergencies_title' => isset( $s['recent_emergencies_title'] ) ? $s['recent_emergencies_title'] : '',
				'more_label'               => isset( $s['more_label'] ) ? $s['more_label'] : '',
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo Firefighter_Stats_Shortcode_Emergency_List_Widget::shortcode( $atts );
		}
	}
}
