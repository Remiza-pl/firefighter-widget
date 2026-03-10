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
                    'label' => esc_html__( 'Title', 'firefighter-widget' ),
                    'description' => esc_html__( 'Title of this section.', 'firefighter-widget' ),
                    'default' => esc_html__( '🚨 Emergency Statistics', 'firefighter-widget' ),
                    'priority' => 10,
                ),

                // Show Category Summary
                array(
                    'name' => 'show_category_summary',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Category Summary', 'firefighter-widget' ),
                    'description' => esc_html__( 'Display category counts at the top.', 'firefighter-widget' ),
                    'default' => true,
                    'priority' => 15,
                ),

                // Category Time Period
                array(
                    'name' => 'category_time_period',
                    'type' => 'select',
                    'label' => esc_html__( 'Category Count Period', 'firefighter-widget' ),
                    'description' => esc_html__( 'Time period for category emergency counts.', 'firefighter-widget' ),
                    'choices' => array(
                        'all' => esc_html__( 'All Time', 'firefighter-widget' ),
                        'year' => esc_html__( 'This Year', 'firefighter-widget' ),
                        'month' => esc_html__( 'This Month', 'firefighter-widget' ),
                    ),
                    'default' => 'all',
                    'priority' => 17,
                ),

                // Selected Categories
                array(
                    'name' => 'selected_categories',
                    'type' => 'text',
                    'label' => esc_html__( 'Selected Categories', 'firefighter-widget' ),
                    'description' => esc_html__( 'Comma-separated category IDs to display. Leave empty for all.', 'firefighter-widget' ),
                    'priority' => 18,
                ),

                // Category Sort
                array(
                    'name' => 'category_sort',
                    'type' => 'select',
                    'label' => esc_html__( 'Sort Categories By', 'firefighter-widget' ),
                    'description' => esc_html__( 'How to sort the category statistics.', 'firefighter-widget' ),
                    'choices' => array(
                        'alphabet' => esc_html__( 'Alphabetical', 'firefighter-widget' ),
                        'count_desc' => esc_html__( 'Count (High to Low)', 'firefighter-widget' ),
                        'count_asc' => esc_html__( 'Count (Low to High)', 'firefighter-widget' ),
                    ),
                    'default' => 'alphabet',
                    'priority' => 19,
                ),

                // Show Zero Categories
                array(
                    'name' => 'show_zero_categories',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Categories with Zero Count', 'firefighter-widget' ),
                    'description' => esc_html__( 'Display categories even when they have no emergencies.', 'firefighter-widget' ),
                    'default' => true,
                    'priority' => 20,
                ),

                // Show Posts List
                array(
                    'name' => 'show_posts_list',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Show Emergency Posts List', 'firefighter-widget' ),
                    'description' => esc_html__( 'Display list of emergency posts below category summary.', 'firefighter-widget' ),
                    'default' => true,
                    'priority' => 21,
                ),

                // Category Filter for Posts
                array(
                    'name' => 'category',
                    'type' => 'taxonomy',
                    'tax' => 'firefighter_stats_cat',
                    'label' => esc_html__( 'Posts Category Filter', 'firefighter-widget' ),
                    'description' => esc_html__( 'Display emergency posts from a specific category.', 'firefighter-widget' ),
                    'priority' => 22,
                ),

                // Posts Limit
                array(
                    'name' => 'limit',
                    'type' => 'select',
                    'label' => esc_html__( 'Posts Limit', 'firefighter-widget' ),
                    'description' => esc_html__( 'How many emergency posts to display.', 'firefighter-widget' ),
                    'choices' => array( 0 => esc_html__( 'All', 'firefighter-widget' ) ) + range( 1, 20, 1 ),
                    'default' => 5,
                    'priority' => 23,
                ),

                // Order
                array(
                    'name' => 'order',
                    'type' => 'select',
                    'label' => esc_html__( 'Order', 'firefighter-widget' ),
                    'description' => esc_html__( 'Order of emergency posts.', 'firefighter-widget' ),
                    'choices' => array(
                        'default' => esc_html__( 'Default', 'firefighter-widget' ),
                        'date_desc' => esc_html__( 'By date, newest first', 'firefighter-widget' ),
                        'date_asc' => esc_html__( 'By date, oldest first', 'firefighter-widget' ),
                        'title_asc' => esc_html__( 'By title, ascending', 'firefighter-widget' ),
                        'title_desc' => esc_html__( 'By title, descending', 'firefighter-widget' ),
                        'random' => esc_html__( 'Random', 'firefighter-widget' ),
                    ),
                    'default' => 'default',
                    'priority' => 24,
                ),

                // Display date
                array(
                    'name' => 'show_date',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Display Date', 'firefighter-widget' ),
                    'default' => true,
                    'priority' => 25,
                ),

                // Display category
                array(
                    'name' => 'show_category',
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Display Category', 'firefighter-widget' ),
                    'default' => true,
                    'priority' => 26,
                ),

                // Recent Emergencies Title
                array(
                    'name' => 'recent_emergencies_title',
                    'type' => 'text',
                    'label' => esc_html__( 'Recent Emergencies Title', 'firefighter-widget' ),
                    'description' => esc_html__( 'Title for the recent emergencies section.', 'firefighter-widget' ),
                    'default' => esc_html__( '📝 Recent Emergencies', 'firefighter-widget' ),
                    'priority' => 27,
                ),

                // More label
                array(
                    'name' => 'more_label',
                    'type' => 'text',
                    'label' => esc_html__( 'More Link Label', 'firefighter-widget' ),
                    'description' => esc_html__( 'Link to emergency archive. Leave blank to hide.', 'firefighter-widget' ),
                    'default' => esc_html__( 'More emergencies', 'firefighter-widget' ),
                    'priority' => 28,
                ),

                // ID
                array(
                    'name' => 'id',
                    'type' => 'text',
                    'label' => esc_html__( 'Unique ID', 'firefighter-widget' ),
                    'description' => esc_html__( 'You can use this ID to style this specific element with custom CSS, for example.', 'firefighter-widget' ),
                    'priority' => 29,
                ),

            ), apply_filters( 'firefighter_stats_emergency_list_widget_shortcode_atts', array() ) );
        }

    }
}
?>
