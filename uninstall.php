<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes all options and term meta created by the plugin.
 * Post data (CPT posts, terms) is intentionally left in place
 * so users do not lose their emergency records on uninstall.
 */

// Only run when WordPress itself requests an uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'firefighter_stats_locale' );
delete_option( 'firefighter_stats_permalinks' );

// Remove reporter options and transients.
delete_option( 'firefighter_stats_reporting_token' );
delete_option( 'firefighter_stats_reporting_enabled' );
delete_option( 'firefighter_stats_reporting_registered' );
delete_option( 'firefighter_stats_reporting_notice_dismissed' );
delete_option( 'firefighter_stats_reporting_endpoint' );
delete_transient( 'firefighter_stats_token_invalid' );

// Remove term meta for all firefighter_stats_cat terms.
$firefighter_stats_terms = get_terms( array(
	'taxonomy'   => 'firefighter_stats_cat',
	'hide_empty' => false,
	'fields'     => 'ids',
) );

if ( ! is_wp_error( $firefighter_stats_terms ) && ! empty( $firefighter_stats_terms ) ) {
	foreach ( $firefighter_stats_terms as $firefighter_stats_term_id ) {
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_category_icon' );
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_category_custom_icon' );
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_category_color' );
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_manual_counts' );
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_total_count' );
		delete_term_meta( $firefighter_stats_term_id, 'firefighter_stats_manual_total' );
	}
}
