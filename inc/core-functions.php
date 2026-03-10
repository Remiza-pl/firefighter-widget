<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'firefighter_stats_has_post_terms' ) ) {
	function firefighter_stats_has_post_terms( $post_id, $taxonomy ): bool {

        $terms = wp_get_post_terms( $post_id, $taxonomy );
        return ! empty( $terms );

	}
}

if ( ! function_exists( 'firefighter_stats_get_post_taxonomy_html' ) ) {
	function firefighter_stats_get_post_taxonomy_html( $post_id, $taxonomy = 'firefighter_stats_cat', $link_template = '<a href="%s">%s</a>' ): string {

		$html_output = '';
        $terms = wp_get_post_terms( $post_id, $taxonomy );

        if ( ! empty( $terms ) ) {

            foreach ( $terms as $term ) {

				$html_output .= sprintf( $link_template, esc_url( get_term_link( $term->term_id, $taxonomy ) ), $term->name );
                $html_output .= $term !== end( $terms ) ? ', ' : '';

            }

        }

        return $html_output;

	}
}