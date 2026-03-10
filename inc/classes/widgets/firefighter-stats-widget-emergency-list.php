<?php
/**
 * Firefighter Stats emergency list widget
 *
 * Display list of firefighter_stats posts
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Firefighter_Stats_Widget_Emergency_List' ) && class_exists( 'Firefighter_Stats_Widget' ) ) {
class Firefighter_Stats_Widget_Emergency_List extends Firefighter_Stats_Widget {

    public function __construct() {

    	// Init widget
		parent::__construct(array(
			'id'          => 'firefighter_stats_emergency_list',
			'classname'   => 'firefighter-stats-emergency-list-widget',
			'title'       => firefighter_stats_t( 'Firefighter Stats Emergencies', 'Statystyki Wyjazdów' ),
			'description' => firefighter_stats_t( 'List of Emergency posts', 'Lista wyjazdów interwencyjnych' ),
			'fields'      => array(
				'title' => array(
					'label'   => firefighter_stats_t( 'Title:', 'Tytuł:' ),
					'type'    => 'text',
					'default' => firefighter_stats_t( '🚨 Emergency Statistics', '🚨 Statystyki wyjazdów' ),
				),

				// Category Summary Settings
				'section_category_summary' => array(
					'type'  => 'section',
					'label' => firefighter_stats_t( 'Category Summary Settings', 'Ustawienia podsumowania kategorii' ),
				),
				'show_category_summary' => array(
					'label'       => firefighter_stats_t( 'Show Category Summary', 'Pokaż podsumowanie kategorii' ),
					'description' => firefighter_stats_t( 'Display category counts at the top of the widget.', 'Wyświetla liczniki kategorii na górze widżetu.' ),
					'type'        => 'checkbox',
					'default'     => 'true',
				),
				'category_time_period' => array(
					'label'       => firefighter_stats_t( 'Category Count Period:', 'Okres licznika kategorii:' ),
					'description' => firefighter_stats_t( 'Time period for category emergency counts.', 'Zakres czasowy dla liczników kategorii.' ),
					'type'        => 'select',
					'choices'     => array(
						'all'   => firefighter_stats_t( 'All Time', 'Cały czas' ),
						'year'  => firefighter_stats_t( 'This Year', 'Ten rok' ),
						'month' => firefighter_stats_t( 'This Month', 'Ten miesiąc' ),
					),
					'default'     => 'year',
				),
				'selected_categories' => array(
					'label'       => firefighter_stats_t( 'Selected Categories:', 'Wybrane kategorie:' ),
					'description' => firefighter_stats_t( 'Choose specific categories to display. Leave empty to show all.', 'Wybierz konkretne kategorie do wyświetlenia. Pozostaw puste, aby pokazać wszystkie.' ),
					'type'        => 'multiselect',
					'taxonomy'    => 'firefighter_stats_cat',
				),
				'category_sort' => array(
					'label'       => firefighter_stats_t( 'Sort Categories By:', 'Sortuj kategorie według:' ),
					'description' => firefighter_stats_t( 'How to sort the category statistics.', 'Sposób sortowania statystyk kategorii.' ),
					'type'        => 'select',
					'choices'     => array(
						'alphabet'   => firefighter_stats_t( 'Alphabetical', 'Alfabetycznie' ),
						'count_desc' => firefighter_stats_t( 'Count (High to Low)', 'Liczba (od najwyższej)' ),
						'count_asc'  => firefighter_stats_t( 'Count (Low to High)', 'Liczba (od najniższej)' ),
					),
					'default'     => 'alphabet',
				),
				'show_zero_categories' => array(
					'label'       => firefighter_stats_t( 'Show Categories with Zero Count', 'Pokazuj kategorie z zerem' ),
					'description' => firefighter_stats_t( 'Display categories even when they have no emergencies.', 'Wyświetla kategorie, nawet gdy nie mają wyjazdów.' ),
					'type'        => 'checkbox',
					'default'     => 'true',
				),

				// Emergency Posts List Settings
				'section_posts_list' => array(
					'type'  => 'section',
					'label' => firefighter_stats_t( 'Emergency Posts List Settings', 'Ustawienia listy wpisów wyjazdów' ),
				),
				'show_posts_list' => array(
					'label'       => firefighter_stats_t( 'Show Emergency Posts List', 'Pokaż listę wpisów wyjazdów' ),
					'description' => firefighter_stats_t( 'Display list of emergency posts below category summary.', 'Wyświetla listę wpisów wyjazdów poniżej podsumowania kategorii.' ),
					'type'        => 'checkbox',
					'default'     => 'true',
				),
				'category' => array(
					'label'         => firefighter_stats_t( 'Posts Category Filter:', 'Filtr kategorii wpisów:' ),
					'description'   => firefighter_stats_t( 'Display emergency posts only from a certain category.', 'Wyświetla wyjazdy tylko z wybranej kategorii.' ),
					'type'          => 'taxonomy',
					'taxonomy'      => 'firefighter_stats_cat',
					'default_label' => firefighter_stats_t( 'All Categories', 'Wszystkie kategorie' ),
				),
				'limit' => array(
					'label'       => firefighter_stats_t( 'Posts Limit:', 'Liczba wpisów:' ),
					'description' => firefighter_stats_t( 'Number of emergency posts to display.', 'Liczba wyjazdów do wyświetlenia.' ),
					'type'        => 'select',
					'choices'     => array(
						0  => firefighter_stats_t( 'All', 'Wszystkie' ),
						1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20,
					),
					'default'     => 5,
				),
				'order' => array(
					'label'       => firefighter_stats_t( 'Order:', 'Kolejność:' ),
					'description' => firefighter_stats_t( 'Order of emergency posts.', 'Kolejność wyświetlania wyjazdów.' ),
					'type'        => 'select',
					'choices'     => array(
						'default'    => firefighter_stats_t( 'Default', 'Domyślna' ),
						'date_desc'  => firefighter_stats_t( 'By date, newest first', 'Według daty, najnowsze pierwsze' ),
						'date_asc'   => firefighter_stats_t( 'By date, oldest first', 'Według daty, najstarsze pierwsze' ),
						'title_asc'  => firefighter_stats_t( 'By title, ascending', 'Według tytułu, rosnąco' ),
						'title_desc' => firefighter_stats_t( 'By title, descending', 'Według tytułu, malejąco' ),
						'random'     => firefighter_stats_t( 'Random', 'Losowo' ),
					),
					'default'     => 'default',
				),
				'show_date' => array(
					'label'   => firefighter_stats_t( 'Display Date', 'Wyświetl datę' ),
					'type'    => 'checkbox',
					'default' => 'true',
				),
				'show_category' => array(
					'label'   => firefighter_stats_t( 'Display Category', 'Wyświetl kategorię' ),
					'type'    => 'checkbox',
					'default' => 'true',
				),
				'recent_emergencies_title' => array(
					'label'       => firefighter_stats_t( 'Recent Emergencies Title:', 'Tytuł sekcji ostatnich wyjazdów:' ),
					'description' => firefighter_stats_t( 'Title for the recent emergencies section.', 'Tytuł sekcji ostatnich wyjazdów.' ),
					'type'        => 'text',
					'default'     => firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ),
				),
				'more_label' => array(
					'label'       => firefighter_stats_t( 'More Button Label:', 'Etykieta przycisku „Więcej":' ),
					'description' => firefighter_stats_t( 'Link to emergency post archive. Leave blank to hide.', 'Link do archiwum wyjazdów. Pozostaw puste, aby ukryć.' ),
					'type'        => 'text',
					'default'     => firefighter_stats_t( 'More Emergencies', 'Pozostałe interwencje' ),
				),
			),
		));

    }

    function widget( $args, $instance ) {

    	// Widget display settings with defaults
    	$show_category_summary = ! isset( $instance['show_category_summary'] ) || true === $instance['show_category_summary'] || 'true' === $instance['show_category_summary'] || '1' === $instance['show_category_summary'];

    	$show_posts_list = ! isset( $instance['show_posts_list'] ) || true === $instance['show_posts_list'] || 'true' === $instance['show_posts_list'] || '1' === $instance['show_posts_list'];

    	$show_date = ! isset( $instance['show_date'] ) || true === $instance['show_date'] || 'true' === $instance['show_date'] || '1' === $instance['show_date'];

    	$show_category = ! isset( $instance['show_category'] ) || true === $instance['show_category'] || 'true' === $instance['show_category'] || '1' === $instance['show_category'];

		// Set posts limit
		$limit = isset( $instance['limit'] ) && (int) $instance['limit'] > 0 ? (int) $instance['limit'] : 5;

		// Category summary data
		$category_stats = array();
		if ( $show_category_summary ) {
			$category_stats = $this->get_category_statistics( $instance );
		}

    	// Get emergency posts (only if posts list is enabled)
    	$posts = array();
    	if ( $show_posts_list ) {
	    	$query_args = array(
	    		'post_type'        => 'firefighter_stats',
	    		'posts_per_page'   => $limit,
	    		'suppress_filters' => false,
			);
			if ( ! empty( $instance['category'] ) && 'none' !== $instance['category'] && is_numeric( $instance['category'] ) ) {
				$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'firefighter_stats_cat',
						'field'    => 'term_id',
						'terms'    => (int) $instance['category'],
					),
				);
			}
			if ( ! empty( $instance['order'] ) && 'default' !== $instance['order'] ) {
				if ( 'date_desc' === $instance['order'] ) {
					$query_args['orderby'] = 'date';
					$query_args['order']   = 'DESC';
				} elseif ( 'date_asc' === $instance['order'] ) {
					$query_args['orderby'] = 'date';
					$query_args['order']   = 'ASC';
				} elseif ( 'title_asc' === $instance['order'] ) {
					$query_args['orderby'] = 'title';
					$query_args['order']   = 'ASC';
				} elseif ( 'title_desc' === $instance['order'] ) {
					$query_args['orderby'] = 'title';
					$query_args['order']   = 'DESC';
				} elseif ( 'random' === $instance['order'] ) {
					$query_args['orderby'] = 'rand';
				}
			}
	    	$posts = get_posts( $query_args );
    	}

    	// Prepare template vars
    	global $firefighter_stats_template_vars;
  		$firefighter_stats_template_vars = array(
  			'instance'              => $instance,
  			'show_category_summary' => $show_category_summary,
  			'show_posts_list'       => $show_posts_list,
  			'show_date'             => $show_date,
  			'show_category'         => $show_category,
  			'category_stats'        => $category_stats,
  			'emergency_posts'       => $posts,
		);

        // Before widget content
        parent::before_widget_content( $args, $instance );

        // Load template
        $template_path = apply_filters( 'firefighter_stats_widget_emergency_list_template_path', plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/widgets/emergency-list.php' );
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            $this->render_fallback_template( $firefighter_stats_template_vars );
        }

        // Admin-only quick-actions panel (visible on frontend, admins only)
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            $this->render_frontend_admin_panel();
        }

        // After widget content
        parent::after_widget_content( $args, $instance );

    }

    /**
     * Render the admin quick-actions panel inside the widget on the frontend.
     * Visible only to logged-in administrators.
     */
    private function render_frontend_admin_panel() {
        $categories   = get_terms( array( 'taxonomy' => 'firefighter_stats_cat', 'hide_empty' => false ) );
        $new_post_url = admin_url( 'post-new.php?post_type=firefighter_stats' );
        $nonce        = wp_create_nonce( 'firefighter_stats_add_count_ajax' );
        $ajax_url     = admin_url( 'admin-ajax.php' );

        $lbl_actions  = firefighter_stats_t( '⚡ Quick Actions', '⚡ Szybkie akcje' );
        $lbl_addcount = firefighter_stats_t( 'Add count:', 'Dodaj licznik:' );
        $lbl_add      = firefighter_stats_t( '+ Add', '+ Dodaj' );
        $lbl_newpost  = firefighter_stats_t( '📝 Add new emergency', '📝 Dodaj nowy wyjazd' );
        $lbl_select   = firefighter_stats_t( '— Category —', '— Kategoria —' );
        ?>
        <div class="fs-widget-actions fs-widget-actions--frontend">
            <div class="fs-widget-actions__title"><?php echo esc_html( $lbl_actions ); ?></div>

            <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                <div class="fs-wqa-label"><?php echo esc_html( $lbl_addcount ); ?></div>
                <div class="fs-wqa-form">
                    <select class="fs-wqa-cat">
                        <option value=""><?php echo esc_html( $lbl_select ); ?></option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->term_id ); ?>">
                                <?php echo esc_html( firefighter_stats_get_category_emoji( $cat->term_id ) . ' ' . $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" class="fs-wqa-count" value="1" min="1" max="99" aria-label="count">
                    <input type="hidden" class="fs-wqa-nonce" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" class="fs-wqa-ajax" value="<?php echo esc_url( $ajax_url ); ?>">
                    <button type="button" class="button fs-wqa-btn" onclick="fsWidgetQuickAdd(this)">
                        <?php echo esc_html( $lbl_add ); ?>
                    </button>
                </div>
                <span class="fs-wqa-msg"></span>
            <?php endif; ?>

            <hr class="fs-widget-actions__sep">
            <a href="<?php echo esc_url( $new_post_url ); ?>" class="button button-secondary fs-widget-actions__link" target="_blank">
                <?php echo esc_html( $lbl_newpost ); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Get category statistics based on widget settings
     */
    private function get_category_statistics( $instance ) {
        $category_stats      = array();
        $time_period         = ! empty( $instance['category_time_period'] ) ? $instance['category_time_period'] : 'all';
        $selected_categories = ! empty( $instance['selected_categories'] ) ? $instance['selected_categories'] : array();
        $category_sort       = ! empty( $instance['category_sort'] ) ? $instance['category_sort'] : 'alphabet';
        $show_zero_categories = isset( $instance['show_zero_categories'] ) ?
            ( true === $instance['show_zero_categories'] || 'true' === $instance['show_zero_categories'] || '1' === $instance['show_zero_categories'] ) :
            true;

        $categories_args = array(
            'taxonomy'   => 'firefighter_stats_cat',
            'hide_empty' => false,
        );

        if ( ! empty( $selected_categories ) && is_array( $selected_categories ) ) {
            $categories_args['include'] = $selected_categories;
        }

        $categories = get_terms( $categories_args );

        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            foreach ( $categories as $category ) {
                $count = $this->get_category_emergency_count( $category->term_id, $time_period );

                if ( ! $show_zero_categories && 0 === $count ) {
                    continue;
                }

                $icon = $this->get_category_icon( $category->term_id );

                $category_stats[] = array(
                    'term'  => $category,
                    'count' => $count,
                    'icon'  => $icon,
                    'link'  => get_term_link( $category ),
                );
            }

            $category_stats = $this->sort_category_statistics( $category_stats, $category_sort );
        }

        return $category_stats;
    }

    /**
     * Sort category statistics based on settings
     */
    private function sort_category_statistics( $category_stats, $sort_by ) {
        if ( empty( $category_stats ) ) {
            return $category_stats;
        }

        switch ( $sort_by ) {
            case 'count_desc':
                usort( $category_stats, function( $a, $b ) {
                    if ( $a['count'] === $b['count'] ) {
                        return strcmp( $a['term']->name, $b['term']->name );
                    }
                    return $b['count'] - $a['count'];
                });
                break;

            case 'count_asc':
                usort( $category_stats, function( $a, $b ) {
                    if ( $a['count'] === $b['count'] ) {
                        return strcmp( $a['term']->name, $b['term']->name );
                    }
                    return $a['count'] - $b['count'];
                });
                break;

            case 'alphabet':
            default:
                usort( $category_stats, function( $a, $b ) {
                    return strcmp( $a['term']->name, $b['term']->name );
                });
                break;
        }

        return $category_stats;
    }

    /**
     * Get emergency count for a specific category and time period
     */
    private function get_category_emergency_count( $category_id, $time_period = 'all' ) {
        $query_args = array(
            'post_type'      => 'firefighter_stats',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => 'firefighter_stats_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                ),
            ),
        );

        if ( 'year' === $time_period ) {
            $query_args['date_query'] = array(
                array( 'year' => (int) wp_date( 'Y' ) ),
            );
        } elseif ( 'month' === $time_period ) {
            $query_args['date_query'] = array(
                array(
                    'year'  => (int) wp_date( 'Y' ),
                    'month' => (int) wp_date( 'n' ),
                ),
            );
        }

        $query      = new WP_Query( $query_args );
        $post_count = $query->found_posts;

        $manual_count = $this->get_manual_emergency_count( $category_id, $time_period );

        return $post_count + $manual_count;
    }

    /**
     * Get manual emergency count for a specific category and time period
     */
    private function get_manual_emergency_count( $category_id, $time_period = 'all' ) {
        $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );

        if ( ! is_array( $manual_counts ) ) {
            return 0;
        }

        $total         = 0;
        $current_year  = (int) wp_date( 'Y' );
        $current_month = (int) wp_date( 'n' );

        foreach ( $manual_counts as $data ) {
            $entry_date  = $data['date'];
            $entry_year  = (int) wp_date( 'Y', strtotime( $entry_date ) );
            $entry_month = (int) wp_date( 'n', strtotime( $entry_date ) );

            if ( 'year' === $time_period && $entry_year != $current_year ) {
                continue;
            } elseif ( 'month' === $time_period && ( $entry_year != $current_year || $entry_month != $current_month ) ) {
                continue;
            }

            $total += intval( $data['count'] );
        }

        return $total;
    }

    /**
     * Get category icon (from term meta or default)
     */
    private function get_category_icon( $category_id ) {
        $icon = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );

        if ( empty( $icon ) ) {
            $term = get_term( $category_id );
            if ( $term && ! is_wp_error( $term ) ) {
                $icon = $this->get_default_category_icon( $term->slug );
            }
        }

        return $icon;
    }

    /**
     * Get default icon based on category slug
     */
    private function get_default_category_icon( $category_slug ) {
        $default_icons = array(
            'fire'      => 'dashicons-admin-site-alt3',
            'medical'   => 'dashicons-heart',
            'rescue'    => 'dashicons-sos',
            'accident'  => 'dashicons-warning',
            'hazmat'    => 'dashicons-shield-alt',
            'water'     => 'dashicons-admin-site-alt2',
            'technical' => 'dashicons-admin-tools',
            'other'     => 'dashicons-megaphone',
        );

        foreach ( $default_icons as $key => $icon ) {
            if ( strpos( $category_slug, $key ) !== false ) {
                return $icon;
            }
        }

        return 'dashicons-megaphone';
    }

    /**
     * Render fallback template if main template is not found
     */
    private function render_fallback_template( $template_vars ) {
        if ( empty( $template_vars ) || ! is_array( $template_vars ) ) {
            return;
        }

        extract( $template_vars );

        $total_count = 0;
        if ( ! empty( $category_stats ) ) {
            foreach ( $category_stats as $stat ) {
                $total_count += $stat['count'];
            }
        }

        $time_period_text = $this->get_time_period_text( $instance );
        $lbl_total        = firefighter_stats_t( 'Total in %s:', 'Łącznie w %s:' );
        $lbl_total_fmt    = sprintf( $lbl_total, $time_period_text );
        $lbl_emergency    = firefighter_stats_t( '%d emergency', '%d wyjazd' );
        $lbl_emergencies  = firefighter_stats_t( '%d emergencies', '%d wyjazdów' );
        $lbl_no_data      = firefighter_stats_t( 'No emergency data available.', 'Brak danych o wyjazdach.' );
        ?>

        <section class="emergency-widget">

            <?php if ( ! empty( $show_category_summary ) && ! empty( $category_stats ) ) : ?>
                <div class="emergency-summary">
                    <?php foreach ( $category_stats as $stat ) : ?>
                        <div class="stat-item">
                            <span class="icon"><?php echo esc_html( $this->get_category_emoji( $stat['term']->term_id ) ); ?></span>
                            <span><?php echo esc_html( $stat['term']->name ); ?></span>
                            <span class="count"><?php echo esc_html( $stat['count'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-footer">
                    <strong><?php echo esc_html( $lbl_total_fmt ); ?></strong>
                    <?php echo esc_html( sprintf( 1 === $total_count ? $lbl_emergency : $lbl_emergencies, $total_count ) ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $show_posts_list ) && ! empty( $emergency_posts ) ) : ?>
                <div class="emergency-recent">
                    <h4><?php echo esc_html( ! empty( $instance['recent_emergencies_title'] ) ? $instance['recent_emergencies_title'] : firefighter_stats_t( '📝 Recent Emergencies', '📝 Ostatnie interwencje' ) ); ?></h4>
                    <ul>
                        <?php foreach ( $emergency_posts as $emergency_post ) : ?>
                            <li>
                                <a href="<?php echo esc_url( get_permalink( $emergency_post->ID ) ); ?>" class="emergency-link">
                                    <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $emergency_post->post_date ) ) ); ?></strong> –
                                    <?php
                                    $post_categories = wp_get_post_terms( $emergency_post->ID, 'firefighter_stats_cat' );
                                    if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
                                        $cat_item      = $post_categories[0];
                                        $category_class = $this->get_category_css_class( $cat_item->slug );
                                        $category_color = Firefighter_Stats_Category_Meta::get_category_color( $cat_item->term_id );
                                        $color_style    = 'style="background-color: ' . esc_attr( $category_color ) . ';"';
                                        echo '<span class="tag ' . esc_attr( $category_class ) . '" style="background-color: ' . esc_attr( $category_color ) . ';">' . esc_html( $cat_item->name ) . '</span> – ';
                                    }
                                    ?>
                                    <?php echo esc_html( get_the_title( $emergency_post->ID ) ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( empty( $category_stats ) && empty( $emergency_posts ) ) : ?>
                <p style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                    <?php echo esc_html( $lbl_no_data ); ?>
                </p>
            <?php endif; ?>
        </section>
        <?php
    }

    /**
     * Get time period text for display
     */
    private function get_time_period_text( $instance ) {
        $time_period = ! empty( $instance['category_time_period'] ) ? $instance['category_time_period'] : 'all';

        switch ( $time_period ) {
            case 'year':
                return wp_date( 'Y' );
            case 'month':
                return date_i18n( 'F Y' );
            default:
                return firefighter_stats_t( 'all time', 'cały czas' );
        }
    }

    /**
     * Get display icon for category (custom emoji or mapped icon)
     */
    private function get_category_emoji( $category_id ) {
        $custom_icon = get_term_meta( $category_id, 'firefighter_stats_category_custom_icon', true );
        if ( ! empty( $custom_icon ) ) {
            return $custom_icon;
        }

        $icon = $this->get_category_icon( $category_id );
        return $this->convert_icon_to_emoji( $icon );
    }

    /**
     * Convert icon key to emoji for display
     */
    private function convert_icon_to_emoji( $icon_key ) {
        $icon_map = array(
            'fire'        => '🔥',
            'medical'     => '🚑',
            'rescue'      => '🆘',
            'accident'    => '⚠️',
            'threat'      => '⚠️',
            'hazmat'      => '☢️',
            'water'       => '🌊',
            'technical'   => '🔧',
            'vehicle'     => '🚗',
            'structure'   => '🏢',
            'false-alarm' => '🚫',
            'exercise'    => '🏋️',
            'other'       => '📋',
        );

        return isset( $icon_map[ $icon_key ] ) ? $icon_map[ $icon_key ] : '📋';
    }

    /**
     * Get CSS class for category based on slug
     */
    private function get_category_css_class( $category_slug ) {
        $classes = array(
            'fire'        => 'fire',
            'medical'     => 'medical',
            'rescue'      => 'rescue',
            'accident'    => 'accident',
            'hazmat'      => 'hazmat',
            'water'       => 'water',
            'technical'   => 'technical',
            'threat'      => 'threat',
            'false-alarm' => 'false-alarm',
            'exercise'    => 'exercise',
            'other'       => 'other',
        );

        if ( isset( $classes[ $category_slug ] ) ) {
            return $classes[ $category_slug ];
        }

        foreach ( $classes as $key => $class ) {
            if ( false !== strpos( $category_slug, $key ) ) {
                return $class;
            }
        }

        return 'other';
    }

}}
