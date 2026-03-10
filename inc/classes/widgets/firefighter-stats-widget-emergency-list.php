<?php
/**
 * Firefighter Stats emergency list widget
 *
 * Display list of firefighter_stats posts
 */
if ( ! class_exists( 'Firefighter_Stats_Widget_Emergency_List' ) && class_exists( 'Firefighter_Stats_Widget' ) ) {
class Firefighter_Stats_Widget_Emergency_List extends Firefighter_Stats_Widget {

    public function __construct() {

    	// Init widget
		parent::__construct(array(
			'id' => 'firefighter_stats_emergency_list',
			'classname' => 'firefighter-stats-emergency-list-widget',
			'title' => esc_html__( 'Firefighter Stats Emergencies', 'firefighter-stats' ),
			'description' => esc_html__( 'List of Emergency posts', 'firefighter-stats' ),
			'fields' => array(
				'title' => array(
					'label' => esc_html__( 'Title:', 'firefighter-stats' ),
					'type' => 'text',
					'default' => esc_html__( '🚨 Emergency Statistics', 'firefighter-stats' ),
				),

				// Category Summary Settings
				'show_category_summary' => array(
					'label' => esc_html__( 'Show Category Summary', 'firefighter-stats' ),
					'description' => esc_html__( 'Display category counts at the top of the widget.', 'firefighter-stats' ),
					'type' => 'checkbox',
					'default' => 'true',
				),
				'category_time_period' => array(
					'label' => esc_html__( 'Category Count Period:', 'firefighter-stats' ),
					'description' => esc_html__( 'Time period for category emergency counts.', 'firefighter-stats' ),
					'type' => 'select',
					'choices' => array(
						'all' => esc_html__( 'All Time', 'firefighter-stats' ),
						'year' => esc_html__( 'This Year', 'firefighter-stats' ),
						'month' => esc_html__( 'This Month', 'firefighter-stats' ),
					),
					'default' => 'all',
				),
				'selected_categories' => array(
					'label' => esc_html__( 'Selected Categories:', 'firefighter-stats' ),
					'description' => esc_html__( 'Choose specific categories to display. Leave empty to show all.', 'firefighter-stats' ),
					'type' => 'multiselect',
					'taxonomy' => 'firefighter_stats_cat',
				),
				'category_sort' => array(
					'label' => esc_html__( 'Sort Categories By:', 'firefighter-stats' ),
					'description' => esc_html__( 'How to sort the category statistics.', 'firefighter-stats' ),
					'type' => 'select',
					'choices' => array(
						'alphabet' => esc_html__( 'Alphabetical', 'firefighter-stats' ),
						'count_desc' => esc_html__( 'Count (High to Low)', 'firefighter-stats' ),
						'count_asc' => esc_html__( 'Count (Low to High)', 'firefighter-stats' ),
					),
					'default' => 'alphabet',
				),
				'show_zero_categories' => array(
					'label' => esc_html__( 'Show Categories with Zero Count', 'firefighter-stats' ),
					'description' => esc_html__( 'Display categories even when they have no emergencies.', 'firefighter-stats' ),
					'type' => 'checkbox',
					'default' => 'true',
				),

				// Emergency Posts List Settings
				'show_posts_list' => array(
					'label' => esc_html__( 'Show Emergency Posts List', 'firefighter-stats' ),
					'description' => esc_html__( 'Display list of emergency posts below category summary.', 'firefighter-stats' ),
					'type' => 'checkbox',
					'default' => 'true',
				),
				'category' => array(
					'label' => esc_html__( 'Posts Category Filter:', 'firefighter-stats' ),
					'description' => esc_html__( 'Display emergency posts only from a certain category.', 'firefighter-stats' ),
					'type' => 'taxonomy',
					'taxonomy' => 'firefighter_stats_cat',
					'default_label' => esc_html__( 'All Categories', 'firefighter-stats' ),
				),
				'limit' => array(
					'label' => esc_html__( 'Posts Limit:', 'firefighter-stats' ),
					'description' => esc_html__( 'Number of emergency posts to display.', 'firefighter-stats' ),
					'type' => 'select',
					'choices' => array( 0 => esc_html__( 'All', 'firefighter-stats' ), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20 ),
					'default' => 5,
				),
				'order' => array(
					'label' => esc_html__( 'Order:', 'firefighter-stats' ),
					'description' => esc_html__( 'Order of emergency posts.', 'firefighter-stats' ),
					'type' => 'select',
					'choices' => array(
						'default' => esc_html__( 'Default', 'firefighter-stats' ),
                        'date_desc' => esc_html__( 'By date, newest first', 'firefighter-stats' ),
                        'date_asc' => esc_html__( 'By date, oldest first', 'firefighter-stats' ),
                        'title_asc' => esc_html__( 'By title, ascending', 'firefighter-stats' ),
                        'title_desc' => esc_html__( 'By title, descending', 'firefighter-stats' ),
                        'random' => esc_html__( 'Random', 'firefighter-stats' ),
					),
					'default' => 'default',
				),
				'show_date' => array(
					'label' => esc_html__( 'Display Date', 'firefighter-stats' ),
					'type' => 'checkbox',
					'default' => 'true',
				),
				'show_category' => array(
					'label' => esc_html__( 'Display Category', 'firefighter-stats' ),
					'type' => 'checkbox',
					'default' => 'true',
				),
				'recent_emergencies_title' => array(
					'label' => esc_html__( 'Recent Emergencies Title:', 'firefighter-stats' ),
					'description' => esc_html__( 'Title for the recent emergencies section.', 'firefighter-stats' ),
					'type' => 'text',
					'default' => esc_html__( '📝 Recent Emergencies', 'firefighter-stats' ),
				),
				'more_label' => array(
					'label' => esc_html__( 'More Button Label:', 'firefighter-stats' ),
					'description' => esc_html__( 'Link to emergency post archive. Leave blank to hide.', 'firefighter-stats' ),
					'type' => 'text',
					'default' => esc_html__( 'More Emergencies', 'firefighter-stats' ),
				),
			),
		));

    }

    function widget( $args, $instance ) {



    	// Widget display settings with defaults
    	$show_category_summary = ! isset( $instance['show_category_summary'] ) || true === $instance['show_category_summary'] || 'true' === $instance['show_category_summary'] || '1' === $instance['show_category_summary']; // Default to true

    	$show_posts_list = ! isset( $instance['show_posts_list'] ) || true === $instance['show_posts_list'] || 'true' === $instance['show_posts_list'] || '1' === $instance['show_posts_list']; // Default to true

    	$show_date = ! isset( $instance['show_date'] ) || true === $instance['show_date'] || 'true' === $instance['show_date'] || '1' === $instance['show_date']; // Default to true

    	$show_category = ! isset( $instance['show_category'] ) || true === $instance['show_category'] || 'true' === $instance['show_category'] || '1' === $instance['show_category']; // Default to true

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
	    		'post_type' => 'firefighter_stats',
	    		'posts_per_page' => $limit,
	    		'suppress_filters' => false,
			);
			if ( ! empty( $instance['category'] ) && 'none' !== $instance['category'] && is_numeric( $instance['category'] ) ) {
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => 'firefighter_stats_cat',
						'field' => 'term_id',
						'terms' => (int) $instance['category'],
					),
				);
			}
			if ( ! empty( $instance['order'] ) && 'default' !== $instance['order'] ) {
				if ( 'date_desc' === $instance['order'] ) {
					$query_args['orderby'] = 'date';
					$query_args['order'] = 'DESC';
				}
				elseif ( 'date_asc' === $instance['order'] ) {
					$query_args['orderby'] = 'date';
					$query_args['order'] = 'ASC';
				}
				elseif ( 'title_asc' === $instance['order'] ) {
					$query_args['orderby'] = 'title';
					$query_args['order'] = 'ASC';
				}
				elseif ( 'title_desc' === $instance['order'] ) {
					$query_args['orderby'] = 'title';
					$query_args['order'] = 'DESC';
				}
				elseif ( 'random' === $instance['order'] ) {
					$query_args['orderby'] = 'rand';
				}
			}
	    	$posts = get_posts( $query_args );
    	}

    	// Prepare template vars
    	global $firefighter_stats_template_vars;
  		$firefighter_stats_template_vars = array(
  			'instance' => $instance,
  			'show_category_summary' => $show_category_summary,
  			'show_posts_list' => $show_posts_list,
  			'show_date' => $show_date,
  			'show_category' => $show_category,
  			'category_stats' => $category_stats,
  			'emergency_posts' => $posts,
		);


        // Before widget content
        parent::before_widget_content( $args, $instance );

        // Load template
        $template_path = apply_filters( 'firefighter_stats_widget_emergency_list_template_path', plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/widgets/emergency-list.php' );
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            // Fallback if template doesn't exist
            $this->render_fallback_template( $firefighter_stats_template_vars );
        }

        // After widget content
        parent::after_widget_content( $args, $instance );

    }

    /**
     * Get category statistics based on widget settings
     */
    private function get_category_statistics( $instance ) {
        $category_stats = array();
        $time_period = ! empty( $instance['category_time_period'] ) ? $instance['category_time_period'] : 'all';
        $selected_categories = ! empty( $instance['selected_categories'] ) ? $instance['selected_categories'] : array();
        $category_sort = ! empty( $instance['category_sort'] ) ? $instance['category_sort'] : 'alphabet';
        $show_zero_categories = isset( $instance['show_zero_categories'] ) ?
            ( true === $instance['show_zero_categories'] || 'true' === $instance['show_zero_categories'] || '1' === $instance['show_zero_categories'] ) :
            true; // Default to true

        // Get categories to display
        $categories_args = array(
            'taxonomy' => 'firefighter_stats_cat',
            'hide_empty' => false,
        );

        // If specific categories are selected, only get those
        if ( ! empty( $selected_categories ) && is_array( $selected_categories ) ) {
            $categories_args['include'] = $selected_categories;
        }

        $categories = get_terms( $categories_args );

        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            foreach ( $categories as $category ) {
                $count = $this->get_category_emergency_count( $category->term_id, $time_period );

                // Skip categories with zero count if setting is disabled
                if ( ! $show_zero_categories && $count === 0 ) {
                    continue;
                }

                $icon = $this->get_category_icon( $category->term_id );

                $category_stats[] = array(
                    'term' => $category,
                    'count' => $count,
                    'icon' => $icon,
                    'link' => get_term_link( $category ),
                );
            }

            // Sort the category statistics based on settings
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
            'post_type' => 'firefighter_stats',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'firefighter_stats_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            ),
        );

        // Add date query based on time period
        if ( 'year' === $time_period ) {
            $query_args['date_query'] = array(
                array(
                    'year' => (int) wp_date( 'Y' ),
                ),
            );
        } elseif ( 'month' === $time_period ) {
            $query_args['date_query'] = array(
                array(
                    'year'  => (int) wp_date( 'Y' ),
                    'month' => (int) wp_date( 'n' ),
                ),
            );
        }

        $query = new WP_Query( $query_args );
        $post_count = $query->found_posts;

        // Get manual counts
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

            // Filter by time period
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
        // Try to get icon from term meta
        $icon = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );

        // If no custom icon, return default based on category name/slug
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
            'fire' => 'dashicons-admin-site-alt3',
            'medical' => 'dashicons-heart',
            'rescue' => 'dashicons-sos',
            'accident' => 'dashicons-warning',
            'hazmat' => 'dashicons-shield-alt',
            'water' => 'dashicons-admin-site-alt2',
            'technical' => 'dashicons-admin-tools',
            'other' => 'dashicons-megaphone',
        );

        // Check if category slug matches any default
        foreach ( $default_icons as $key => $icon ) {
            if ( strpos( $category_slug, $key ) !== false ) {
                return $icon;
            }
        }

        // Default fallback icon
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

        // Get total count for summary
        $total_count = 0;
        if ( ! empty( $category_stats ) ) {
            foreach ( $category_stats as $stat ) {
                $total_count += $stat['count'];
            }
        }

        // Get time period text
        $time_period_text = $this->get_time_period_text( $instance );
        ?>

        <section class="emergency-widget">

            <?php if ( ! empty( $show_category_summary ) && ! empty( $category_stats ) ) : ?>
                <div class="emergency-summary">
                    <?php foreach ( $category_stats as $stat ) : ?>
                        <div class="stat-item">
                            <span class="icon"><?php echo $this->get_category_emoji( $stat['term']->term_id ); ?></span>
                            <span><?php echo esc_html( $stat['term']->name ); ?></span>
                            <span class="count"><?php echo esc_html( $stat['count'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-footer">
                    <strong><?php echo sprintf( esc_html__( 'Total in %s:', 'firefighter-stats' ), $time_period_text ); ?></strong>
                    <?php echo sprintf( _n( '%d emergency', '%d emergencies', $total_count, 'firefighter-stats' ), $total_count ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $show_posts_list ) && ! empty( $emergency_posts ) ) : ?>
                <div class="emergency-recent">
                    <h4><?php echo esc_html( ! empty( $instance['recent_emergencies_title'] ) ? $instance['recent_emergencies_title'] : __( '📝 Recent Emergencies', 'firefighter-stats' ) ); ?></h4>
                    <ul>
                        <?php foreach ( $emergency_posts as $emergency_post ) : ?>
                            <li>
                                <a href="<?php echo esc_url( get_permalink( $emergency_post->ID ) ); ?>" class="emergency-link">
                                    <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $emergency_post->post_date ) ) ); ?></strong> –
                                    <?php
                                    $post_categories = wp_get_post_terms( $emergency_post->ID, 'firefighter_stats_cat' );
                                    if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
                                        $category = $post_categories[0];
                                        $category_class = $this->get_category_css_class( $category->slug );
                                        $category_color = Firefighter_Stats_Category_Meta::get_category_color( $category->term_id );
                                        $color_style = 'style="background-color: ' . esc_attr( $category_color ) . ';"';
                                        echo '<span class="tag ' . esc_attr( $category_class ) . '" ' . $color_style . '>' . esc_html( $category->name ) . '</span> – ';
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
                    <?php esc_html_e( 'No emergency data available.', 'firefighter-stats' ); ?>
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
                // date_i18n formats the date using WordPress locale (timezone-aware).
                return date_i18n( 'F Y' );
            default:
                return __( 'all time', 'firefighter-stats' );
        }
    }

    /**
     * Get display icon for category (custom emoji or mapped icon)
     */
    private function get_category_emoji( $category_id ) {
        // Custom emoji set by admin takes priority over the predefined map.
        $custom_icon = get_term_meta( $category_id, 'firefighter_stats_category_custom_icon', true );
        if ( ! empty( $custom_icon ) ) {
            return $custom_icon;
        }

        // Fall back to predefined icon map.
        $icon = $this->get_category_icon( $category_id );
        return $this->convert_icon_to_emoji( $icon );
    }

    /**
     * Convert icon key to emoji for display
     */
    private function convert_icon_to_emoji( $icon_key ) {
        $icon_map = array(
            'fire' => '🔥',
            'medical' => '🚑',
            'rescue' => '🆘',
            'accident' => '⚠️',
            'threat' => '⚠️',
            'hazmat' => '☢️',
            'water' => '🌊',
            'technical' => '🔧',
            'vehicle' => '🚗',
            'structure' => '🏢',
            'false-alarm' => '🚫',
            'exercise' => '🏋️',
            'other' => '📋',
        );

        return $icon_map[ $icon_key ] ?? '📋';
    }



    /**
     * Get CSS class for category based on slug
     */
    private function get_category_css_class( $category_slug ) {
        $classes = array(
            'fire' => 'fire',
            'medical' => 'medical',
            'rescue' => 'rescue',
            'accident' => 'accident',
            'hazmat' => 'hazmat',
            'water' => 'water',
            'technical' => 'technical',
            'threat' => 'threat',
            'false-alarm' => 'false-alarm',
            'exercise' => 'exercise',
            'other' => 'other',
        );

        // Check for exact match first
        if ( isset( $classes[ $category_slug ] ) ) {
            return $classes[ $category_slug ];
        }

        // Check for partial matches
        foreach ( $classes as $key => $class ) {
            if ( false !== strpos( $category_slug, $key ) ) {
                return $class;
            }
        }

        // Default class
        return 'other';
    }

}}
