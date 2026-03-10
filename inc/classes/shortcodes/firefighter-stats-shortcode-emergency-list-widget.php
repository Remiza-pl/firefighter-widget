<?php
/**
 * Firefighter Stats Emergency List Widget Shortcode
 */
if ( ! class_exists( 'Firefighter_Stats_Shortcode_Emergency_List_Widget' ) ) {
    class Firefighter_Stats_Shortcode_Emergency_List_Widget {

        public static function shortcode( $atts = array(), $content = null, $tag = '' ) {

            // Merge default atts and received atts
            $args = shortcode_atts(
                array(
                    'title' => '🚨 Emergency Statistics',
                    'icon' => '',
                    'show_category_summary' => 'true',
                    'category_time_period' => 'all',
                    'selected_categories' => '',
                    'category_sort' => 'alphabet',
                    'show_zero_categories' => 'true',
                    'show_posts_list' => 'true',
                    'category' => 0,
                    'limit' => 5,
                    'order' => 'default',
                    'show_date' => 'true',
                    'show_category' => 'true',
                    'recent_emergencies_title' => '📝 Recent Emergencies',
                    'more_label' => '',
                    'id' => '',
                    'className' => '',
                    'editor_view' => false,
                ),
                $atts
            );

            // Check if editor view
            $editor_view = true === $args['editor_view'] || '1' === $args['editor_view'] || 'true' === $args['editor_view'];

            // Element class
            $class_arr = array( 'widget shortcode-widget firefighter-stats-emergency-list-widget firefighter-stats-emergency-list-widget--shortcode' );
            if ( true === $editor_view ) {
                $class_arr[] = 'firefighter-stats-emergency-list-widget--editor-view';
            }
            if ( ! empty( $args['className'] ) ) {
                $class_arr[] = $args['className'];
            }

            ob_start(); ?>

            <?php the_widget( 'Firefighter_Stats_Widget_Emergency_List', array(
                'title' => $args['title'],
                'show_category_summary' => $args['show_category_summary'],
                'category_time_period' => $args['category_time_period'],
                'selected_categories' => ! empty( $args['selected_categories'] ) ? array_values( array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $args['selected_categories'] ) ) ) ) ) : array(),
                'category_sort' => $args['category_sort'],
                'show_zero_categories' => $args['show_zero_categories'],
                'show_posts_list' => $args['show_posts_list'],
                'category' => $args['category'],
                'limit' => $args['limit'],
                'order' => $args['order'],
                'show_date' => $args['show_date'],
                'show_category' => $args['show_category'],
                'recent_emergencies_title' => $args['recent_emergencies_title'],
                'more_label' => $args['more_label'],
                'editor_view' => $args['editor_view'],
            ), array(
                'before_widget' => '<div' . ( ! empty( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : '' ) . ' class="' . esc_attr( implode( ' ', $class_arr ) ) . '"><div class="widget__inner">',
                'after_widget' => '</div></div>',
                'before_title' => ! empty( $args['icon'] ) ? '<h3 class="widget__title widget__title--has-icon"><span class="widget__title-icon ' . esc_attr( $args['icon'] ) . '" aria-hidden="true"></span>' : '<h3 class="widget__title">',
                'after_title' => '</h3>',
            )); ?>

            <?php return ob_get_clean();

        }

        // Shortcode params
        public static function firefighter_stats_shortcode_atts() {
            return array_merge( array(

                // Title
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => esc_html__( 'Title', 'firefighter-stats' ),
                    'description' => esc_html__( 'Title of this section.', 'firefighter-stats' ),
                    'default' => esc_html__( '🚨 Emergency Statistics', 'firefighter-stats' ),
                    'priority' => 10,
                ),

                // Show Category Summary
                array(
                    'name' => 'show_category_summary',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Category Summary', 'firefighter-stats' ),
                    'description' => esc_html__( 'Display category counts at the top.', 'firefighter-stats' ),
                    'default' => true,
                    'priority' => 15,
                ),

                // Category Time Period
                array(
                    'name' => 'category_time_period',
                    'type' => 'select',
                    'label' => esc_html__( 'Category Count Period', 'firefighter-stats' ),
                    'description' => esc_html__( 'Time period for category emergency counts.', 'firefighter-stats' ),
                    'choices' => array(
                        'all' => esc_html__( 'All Time', 'firefighter-stats' ),
                        'year' => esc_html__( 'This Year', 'firefighter-stats' ),
                        'month' => esc_html__( 'This Month', 'firefighter-stats' ),
                    ),
                    'default' => 'all',
                    'priority' => 17,
                ),

                // Selected Categories
                array(
                    'name' => 'selected_categories',
                    'type' => 'text',
                    'label' => esc_html__( 'Selected Categories', 'firefighter-stats' ),
                    'description' => esc_html__( 'Comma-separated category IDs to display. Leave empty for all.', 'firefighter-stats' ),
                    'priority' => 18,
                ),

                // Category Sort
                array(
                    'name' => 'category_sort',
                    'type' => 'select',
                    'label' => esc_html__( 'Sort Categories By', 'firefighter-stats' ),
                    'description' => esc_html__( 'How to sort the category statistics.', 'firefighter-stats' ),
                    'choices' => array(
                        'alphabet' => esc_html__( 'Alphabetical', 'firefighter-stats' ),
                        'count_desc' => esc_html__( 'Count (High to Low)', 'firefighter-stats' ),
                        'count_asc' => esc_html__( 'Count (Low to High)', 'firefighter-stats' ),
                    ),
                    'default' => 'alphabet',
                    'priority' => 19,
                ),

                // Show Zero Categories
                array(
                    'name' => 'show_zero_categories',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Categories with Zero Count', 'firefighter-stats' ),
                    'description' => esc_html__( 'Display categories even when they have no emergencies.', 'firefighter-stats' ),
                    'default' => true,
                    'priority' => 20,
                ),

                // Show Posts List
                array(
                    'name' => 'show_posts_list',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Emergency Posts List', 'firefighter-stats' ),
                    'description' => esc_html__( 'Display list of emergency posts below category summary.', 'firefighter-stats' ),
                    'default' => true,
                    'priority' => 21,
                ),

                // Category Filter for Posts
                array(
                    'name' => 'category',
                    'type' => 'taxonomy',
                    'tax' => 'firefighter_stats_cat',
                    'label' => esc_html__( 'Posts Category Filter', 'firefighter-stats' ),
                    'description' => esc_html__( 'Display emergency posts from a specific category.', 'firefighter-stats' ),
                    'priority' => 22,
                ),

                // Posts Limit
                array(
                    'name' => 'limit',
                    'type' => 'select',
                    'label' => esc_html__( 'Posts Limit', 'firefighter-stats' ),
                    'description' => esc_html__( 'How many emergency posts to display.', 'firefighter-stats' ),
                    'choices' => array( 0 => esc_html__( 'All', 'firefighter-stats' ) ) + range( 1, 20, 1 ),
                    'default' => 5,
                    'priority' => 23,
                ),

                // Order
                array(
                    'name' => 'order',
                    'type' => 'select',
                    'label' => esc_html__( 'Order', 'firefighter-stats' ),
                    'description' => esc_html__( 'Order of emergency posts.', 'firefighter-stats' ),
                    'choices' => array(
                        'default' => esc_html__( 'Default', 'firefighter-stats' ),
                        'date_desc' => esc_html__( 'By date, newest first', 'firefighter-stats' ),
                        'date_asc' => esc_html__( 'By date, oldest first', 'firefighter-stats' ),
                        'title_asc' => esc_html__( 'By title, ascending', 'firefighter-stats' ),
                        'title_desc' => esc_html__( 'By title, descending', 'firefighter-stats' ),
                        'random' => esc_html__( 'Random', 'firefighter-stats' ),
                    ),
                    'default' => 'default',
                    'priority' => 24,
                ),

                // Display date
                array(
                    'name' => 'show_date',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Display Date', 'firefighter-stats' ),
                    'default' => true,
                    'priority' => 25,
                ),

                // Display category
                array(
                    'name' => 'show_category',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Display Category', 'firefighter-stats' ),
                    'default' => true,
                    'priority' => 26,
                ),

                // Recent Emergencies Title
                array(
                    'name' => 'recent_emergencies_title',
                    'type' => 'text',
                    'label' => esc_html__( 'Recent Emergencies Title', 'firefighter-stats' ),
                    'description' => esc_html__( 'Title for the recent emergencies section.', 'firefighter-stats' ),
                    'default' => esc_html__( '📝 Recent Emergencies', 'firefighter-stats' ),
                    'priority' => 27,
                ),

                // More label
                array(
                    'name' => 'more_label',
                    'type' => 'text',
                    'label' => esc_html__( 'More Link Label', 'firefighter-stats' ),
                    'description' => esc_html__( 'Link to emergency archive. Leave blank to hide.', 'firefighter-stats' ),
                    'default' => esc_html__( 'More emergencies', 'firefighter-stats' ),
                    'priority' => 28,
                ),

                // ID
                array(
                    'name' => 'id',
                    'type' => 'text',
                    'label' => esc_html__( 'Unique ID', 'firefighter-stats' ),
                    'description' => esc_html__( 'You can use this ID to style this specific element with custom CSS, for example.', 'firefighter-stats' ),
                    'priority' => 29,
                ),

            ), apply_filters( 'firefighter_stats_emergency_list_widget_shortcode_atts', array() ) );
        }

    }
}
?>
