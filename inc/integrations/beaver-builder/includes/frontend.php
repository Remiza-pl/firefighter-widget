<?php
/**
 * Beaver Builder Module — Frontend Template
 *
 * $module  FLBuilderModule instance.
 * $settings stdClass with module field values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s = $module->settings;

$atts = array(
	'title'                    => isset( $s->title ) ? $s->title : '',
	'category_time_period'     => isset( $s->category_time_period ) ? $s->category_time_period : 'year',
	'show_category_summary'    => ! empty( $s->show_category_summary ) ? 'true' : 'false',
	'category_sort'            => isset( $s->category_sort ) ? $s->category_sort : 'alphabet',
	'show_zero_categories'     => ! empty( $s->show_zero_categories ) ? 'true' : 'false',
	'show_posts_list'          => ! empty( $s->show_posts_list ) ? 'true' : 'false',
	'category'                 => isset( $s->category ) ? intval( $s->category ) : 0,
	'limit'                    => isset( $s->limit ) ? intval( $s->limit ) : 5,
	'order'                    => isset( $s->order ) ? $s->order : 'default',
	'show_date'                => ! empty( $s->show_date ) ? 'true' : 'false',
	'show_category'            => ! empty( $s->show_category ) ? 'true' : 'false',
	'recent_emergencies_title' => isset( $s->recent_emergencies_title ) ? $s->recent_emergencies_title : '',
	'more_label'               => isset( $s->more_label ) ? $s->more_label : '',
);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo Firefighter_Stats_Shortcode_Emergency_List_Widget::shortcode( $atts );
