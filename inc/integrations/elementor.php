<?php
/**
 * Elementor Widget: Firefighter Stats Emergency List
 *
 * Registers a native Elementor widget that wraps the emergency list shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Elementor_Widget' ) ) {

	class Firefighter_Stats_Elementor_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'firefighter_stats_emergency_list';
		}

		public function get_title() {
			return firefighter_stats_t( 'Emergency Statistics', 'Statystyki Wyjazdów' );
		}

		public function get_icon() {
			return 'eicon-alert';
		}

		public function get_categories() {
			return array( 'general' );
		}

		public function get_keywords() {
			return array( 'firefighter', 'emergency', 'statistics', 'rescue', 'straż', 'pożar' );
		}

		public function get_style_depends() {
			return array( 'firefighter-stats-widget' );
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_content',
				array(
					'label' => firefighter_stats_t( 'Content', 'Zawartość' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			// Title.
			$this->add_control(
				'title',
				array(
					'label'   => firefighter_stats_t( 'Title', 'Tytuł' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => firefighter_stats_t( '🚨 Emergency Statistics', '🚨 Statystyki wyjazdów' ),
				)
			);

			// Category Count Period.
			$this->add_control(
				'category_time_period',
				array(
					'label'   => firefighter_stats_t( 'Category Count Period', 'Okres liczenia kategorii' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => array(
						'year'  => firefighter_stats_t( 'This Year', 'Ten rok' ),
						'month' => firefighter_stats_t( 'This Month', 'Ten miesiąc' ),
						'all'   => firefighter_stats_t( 'All Time', 'Cały czas' ),
					),
					'default' => 'year',
				)
			);

			// Show Category Summary.
			$this->add_control(
				'show_category_summary',
				array(
					'label'        => firefighter_stats_t( 'Show Category Summary', 'Pokaż podsumowanie kategorii' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			// Sort Categories By.
			$this->add_control(
				'category_sort',
				array(
					'label'     => firefighter_stats_t( 'Sort Categories By', 'Sortuj kategorie' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => array(
						'alphabet'   => firefighter_stats_t( 'Alphabetical', 'Alfabetycznie' ),
						'count_desc' => firefighter_stats_t( 'Count (High to Low)', 'Liczba (malejąco)' ),
						'count_asc'  => firefighter_stats_t( 'Count (Low to High)', 'Liczba (rosnąco)' ),
					),
					'default'   => 'alphabet',
					'condition' => array( 'show_category_summary' => 'true' ),
				)
			);

			// Show Zero Categories.
			$this->add_control(
				'show_zero_categories',
				array(
					'label'        => firefighter_stats_t( 'Show Zero-Count Categories', 'Pokaż kategorie z zerem' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
					'condition'    => array( 'show_category_summary' => 'true' ),
				)
			);

			// Show Posts List.
			$this->add_control(
				'show_posts_list',
				array(
					'label'        => firefighter_stats_t( 'Show Emergency Posts List', 'Pokaż listę wyjazdów' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			// Category Filter for Posts.
			$category_options = array( '0' => firefighter_stats_t( 'All Categories', 'Wszystkie kategorie' ) );
			$terms = get_terms( array(
				'taxonomy'   => 'firefighter_stats_cat',
				'hide_empty' => false,
			) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$category_options[ (string) $term->term_id ] = esc_html( $term->name );
				}
			}
			$this->add_control(
				'category',
				array(
					'label'     => firefighter_stats_t( 'Posts Category Filter', 'Filtr kategorii wpisów' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $category_options,
					'default'   => '0',
					'condition' => array( 'show_posts_list' => 'true' ),
				)
			);

			// Posts Limit.
			$this->add_control(
				'limit',
				array(
					'label'     => firefighter_stats_t( 'Posts Limit', 'Limit wpisów' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'min'       => 1,
					'max'       => 100,
					'default'   => 5,
					'condition' => array( 'show_posts_list' => 'true' ),
				)
			);

			// Order.
			$this->add_control(
				'order',
				array(
					'label'     => firefighter_stats_t( 'Order', 'Kolejność' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => array(
						'default'    => firefighter_stats_t( 'Default', 'Domyślna' ),
						'date_desc'  => firefighter_stats_t( 'By date, newest first', 'Po dacie, najnowsze' ),
						'date_asc'   => firefighter_stats_t( 'By date, oldest first', 'Po dacie, najstarsze' ),
						'title_asc'  => firefighter_stats_t( 'By title, ascending', 'Po tytule (A–Z)' ),
						'title_desc' => firefighter_stats_t( 'By title, descending', 'Po tytule (Z–A)' ),
						'random'     => firefighter_stats_t( 'Random', 'Losowa' ),
					),
					'default'   => 'default',
					'condition' => array( 'show_posts_list' => 'true' ),
				)
			);

			// Show Date.
			$this->add_control(
				'show_date',
				array(
					'label'        => firefighter_stats_t( 'Display Date', 'Pokaż datę' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
					'condition'    => array( 'show_posts_list' => 'true' ),
				)
			);

			// Show Category.
			$this->add_control(
				'show_category',
				array(
					'label'        => firefighter_stats_t( 'Display Category', 'Pokaż kategorię' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
					'condition'    => array( 'show_posts_list' => 'true' ),
				)
			);

			// Recent Emergencies Title.
			$this->add_control(
				'recent_emergencies_title',
				array(
					'label'     => firefighter_stats_t( 'Recent Emergencies Title', 'Tytuł ostatnich wyjazdów' ),
					'type'      => \Elementor\Controls_Manager::TEXT,
					'default'   => firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ),
					'condition' => array( 'show_posts_list' => 'true' ),
				)
			);

			// More Link Label.
			$this->add_control(
				'more_label',
				array(
					'label'       => firefighter_stats_t( 'More Link Label', 'Etykieta linku "więcej"' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => '',
					'description' => firefighter_stats_t( 'Leave empty to hide the link.', 'Zostaw puste, aby ukryć link.' ),
				)
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();

			$is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();

			$atts = array(
				'title'                    => isset( $settings['title'] ) ? $settings['title'] : '',
				'category_time_period'     => isset( $settings['category_time_period'] ) ? $settings['category_time_period'] : 'year',
				'show_category_summary'    => ! empty( $settings['show_category_summary'] ) ? 'true' : 'false',
				'category_sort'            => isset( $settings['category_sort'] ) ? $settings['category_sort'] : 'alphabet',
				'show_zero_categories'     => ! empty( $settings['show_zero_categories'] ) ? 'true' : 'false',
				'show_posts_list'          => ! empty( $settings['show_posts_list'] ) ? 'true' : 'false',
				'category'                 => isset( $settings['category'] ) ? intval( $settings['category'] ) : 0,
				'limit'                    => isset( $settings['limit'] ) ? intval( $settings['limit'] ) : 5,
				'order'                    => isset( $settings['order'] ) ? $settings['order'] : 'default',
				'show_date'                => ! empty( $settings['show_date'] ) ? 'true' : 'false',
				'show_category'            => ! empty( $settings['show_category'] ) ? 'true' : 'false',
				'recent_emergencies_title' => isset( $settings['recent_emergencies_title'] ) ? $settings['recent_emergencies_title'] : '',
				'more_label'               => isset( $settings['more_label'] ) ? $settings['more_label'] : '',
				'editor_view'              => $is_edit ? 'true' : 'false',
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo Firefighter_Stats_Shortcode_Emergency_List_Widget::shortcode( $atts );
		}
	}
}
