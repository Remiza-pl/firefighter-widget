<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $firefighter_stats_template_vars;
if (!empty($firefighter_stats_template_vars) && is_array($firefighter_stats_template_vars)) : extract($firefighter_stats_template_vars);

// Get total count for summary
$firefighter_stats_total_count = 0;
if (!empty($category_stats)) {
    foreach ($category_stats as $stat) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        $firefighter_stats_total_count += $stat['count'];
    }
}

// Get time period text
$firefighter_stats_time_period_text = '';
if ( ! empty( $instance['category_time_period'] ) && 'year' === $instance['category_time_period'] ) {
	$firefighter_stats_time_period_text = wp_date( 'Y' );
} elseif ( ! empty( $instance['category_time_period'] ) && 'month' === $instance['category_time_period'] ) {
	$firefighter_stats_time_period_text = date_i18n( 'F Y' );
} else {
	$firefighter_stats_time_period_text = __( 'all time', 'firefighter-widget' );
}

// Helper function for category emoji.
// Wrapped in function_exists to prevent fatal errors if template is included multiple times.
if ( ! function_exists( 'firefighter_stats_get_category_emoji' ) ) {
    function firefighter_stats_get_category_emoji( $category_id ) {
        // Custom emoji set by admin takes priority over the predefined map.
        $custom_icon = get_term_meta( $category_id, 'firefighter_stats_category_custom_icon', true );
        if ( ! empty( $custom_icon ) ) {
            return $custom_icon;
        }

        $icon     = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );
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

        return isset( $icon_map[ $icon ] ) ? $icon_map[ $icon ] : '📋';
    }
}

// Helper function for category CSS class.
if ( ! function_exists( 'firefighter_stats_get_category_css_class' ) ) {
    function firefighter_stats_get_category_css_class( $category_slug ) {
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
}

// TEMPLATE : BEGIN ?>

<section class="emergency-widget">

    <?php if (!empty($show_category_summary) && !empty($category_stats)) : ?>
        <div class="emergency-summary">
            <?php foreach ($category_stats as $stat) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                <div class="stat-item">
                    <span class="icon"><?php echo esc_html( firefighter_stats_get_category_emoji( $stat['term']->term_id ) ); ?></span>
                    <span><?php echo esc_html($stat['term']->name); ?></span>
                    <span class="count"><?php echo esc_html($stat['count']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-footer">
            <strong><?php
                /* translators: %s: time period (year, month name, or "all time") */
                echo esc_html( sprintf( esc_html__( 'Total in %s:', 'firefighter-widget' ), $firefighter_stats_time_period_text ) );
                ?></strong>
            <?php
            /* translators: %d: number of emergencies */
            echo esc_html( sprintf( _n( '%d emergency', '%d emergencies', $firefighter_stats_total_count, 'firefighter-widget' ), $firefighter_stats_total_count ) );
            ?>
        </div>
    <?php endif; ?>

    <?php // Emergency Posts List Section
    if (!empty($show_posts_list)) : ?>

        <?php if (!empty($emergency_posts)) : ?>
            <div class="emergency-recent">
                <h4><?php echo esc_html(!empty($instance['recent_emergencies_title']) ? $instance['recent_emergencies_title'] : __('📝 Recent Emergencies', 'firefighter-widget')); ?></h4>
                <ul>
                    <?php foreach ($emergency_posts as $emergency_post) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($emergency_post->ID)); ?>" class="emergency-link">
                                <strong><?php echo esc_html(date_i18n('d.m.Y', strtotime($emergency_post->post_date))); ?></strong> –
                                <?php
                                $post_categories = wp_get_post_terms($emergency_post->ID, 'firefighter_stats_cat'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                if (!empty($post_categories) && !is_wp_error($post_categories)) {
                                    $category = $post_categories[0]; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    $category_class = firefighter_stats_get_category_css_class( $category->slug ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    $category_color = Firefighter_Stats_Category_Meta::get_category_color($category->term_id); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    echo '<span class="tag ' . esc_attr( $category_class ) . '" style="background-color: ' . esc_attr( $category_color ) . ';">' . esc_html( $category->name ) . '</span> – ';
                                }
                                ?>
                                <?php echo esc_html(get_the_title($emergency_post->ID)); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else : ?>
            <p style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                <?php esc_html_e('No emergency data available.', 'firefighter-widget'); ?>
            </p>
        <?php endif; ?>

    <?php endif; // End emergency posts list section ?>

    <?php if (empty($category_stats) && empty($emergency_posts)) : ?>
        <p style="text-align: center; color: #666; font-style: italic; padding: 20px;">
            <?php esc_html_e('No emergency data available.', 'firefighter-widget'); ?>
        </p>
    <?php endif; ?>
</section>

<?php // TEMPLATE : END
endif; ?>
