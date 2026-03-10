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

/**
 * Global locale-aware translation helper.
 * Returns Polish for pl_* locales, English otherwise.
 * No MO file required.
 *
 * @param string $en English text.
 * @param string $pl Polish text.
 * @return string
 */
if ( ! function_exists( 'firefighter_stats_t' ) ) {
	function firefighter_stats_t( $en, $pl ) {
		static $is_pl = null;
		if ( null === $is_pl ) {
			$is_pl = ( strpos( get_user_locale(), 'pl' ) === 0 );
		}
		return $is_pl ? $pl : $en;
	}
}

/**
 * Get display emoji for a category.
 * Checks custom_icon meta first, then falls back to the icon map.
 *
 * @param int $category_id Term ID.
 * @return string Emoji character(s).
 */
if ( ! function_exists( 'firefighter_stats_get_category_emoji' ) ) {
	function firefighter_stats_get_category_emoji( $category_id ) {
		$custom = get_term_meta( $category_id, 'firefighter_stats_category_custom_icon', true );
		if ( ! empty( $custom ) ) {
			return $custom;
		}

		$icon = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );

		$map = array(
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

		return isset( $map[ $icon ] ) ? $map[ $icon ] : '📋';
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