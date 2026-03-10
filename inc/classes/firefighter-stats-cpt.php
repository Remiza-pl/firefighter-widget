<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// General class for handling custom post types.
if ( ! class_exists( 'Firefighter_Stats_CPT' ) ) {
	class Firefighter_Stats_CPT {

		public $cpt_id;
		public $wp_args;
		public $taxonomies = [];

		public function __construct( $args ) {

			$this->cpt_id = ! empty( $args['id'] ) ? sanitize_key( $args['id'] ) : false;
			$this->wp_args = ! empty( $args['wp_args'] ) && is_array( $args['wp_args'] ) ? $args['wp_args'] : false;

			if ( ! empty( $this->cpt_id ) && ! empty( $this->wp_args ) ) {

				add_action( 'init', array( $this, 'register_post_type' ) );
				add_action( 'init', array( $this, 'register_taxonomies' ) );

				// Add admin tax filters
				add_action( 'restrict_manage_posts', array( $this, 'add_admin_tax_filters' ) );

			}

		}

		/**
		 * Activate CPT
		 *
		 * This action should be fired only after plugin activation
		 */
		public function activate_cpt(): void {
			$this->register_post_type();
			$this->register_taxonomies();
			flush_rewrite_rules();
		}

		/**
		 * Register post type
		 */
		public function register_post_type(): void {

			// Override wp_args
			$cpt_wp_args_override = apply_filters( $this->cpt_id . '_cpt_wp_args', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			if ( is_array( $cpt_wp_args_override ) && ! empty( $cpt_wp_args_override ) ) {
				$this->wp_args = array_merge( $this->wp_args, $cpt_wp_args_override );
			}

			// Register CPT
			register_post_type( sanitize_key( $this->cpt_id ), $this->wp_args );

			// Show post thumb in admin listing
			if ( array_key_exists( 'supports', $this->wp_args )
				&& is_array( $this->wp_args['supports'] ) && in_array( 'thumbnail', $this->wp_args['supports'] ) ) {
				add_filter( 'manage_edit-' . $this->cpt_id . '_columns', array( $this, 'add_admin_post_thumb_column' ), 10, 1 );
				add_action( 'manage_posts_custom_column', array( $this, 'display_admin_post_thumb_column' ), 10, 1 );
			}

		}

		/**
		 * Add taxonomy
		 */
		public function add_taxonomy( $args ): void {

			$taxonomy_id = ! empty( $args['id'] ) ? sanitize_key( $args['id'] ) : false;
			$taxonomy_wp_args = ! empty( $args['wp_args'] ) ? $args['wp_args'] : false;

			if ( ! empty( $taxonomy_id ) && ! empty( $taxonomy_wp_args ) ) {
				$this->taxonomies[ $taxonomy_id ]['wp_args'] = $taxonomy_wp_args;
				$this->taxonomies[ $taxonomy_id ]['args'] = ! empty( $args['args'] ) ? $args['args'] : false;
			}

		}

		/**
		 * Register taxonomies
		 */
		public function register_taxonomies(): void {
			foreach( $this->taxonomies as $taxonomy_id => $taxonomy_arr ) {

				// Override wp_args
				$tax_wp_args_override = apply_filters( $taxonomy_id . '_tax_wp_args', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				if ( is_array( $tax_wp_args_override ) && ! empty( $tax_wp_args_override ) ) {
					$taxonomy_arr['wp_args'] = array_merge( $taxonomy_arr['wp_args'], $tax_wp_args_override );
				}

				// Override args
				$tax_args_override = apply_filters( $taxonomy_id . '_tax_args', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				if ( is_array( $tax_args_override ) && ! empty( $tax_args_override ) ) {
					$taxonomy_arr['args'] = array_merge( $taxonomy_arr['args'], $tax_args_override );
				}

				// Register tax
				if ( array_key_exists( 'wp_args', $taxonomy_arr ) ) {
					register_taxonomy( sanitize_key( $taxonomy_id ), array( $this->cpt_id ), $taxonomy_arr['wp_args'] );
				}

			}
		}

		/**
		 * Add admin post thumb column
		 *
		 * Display post thumb in admin post listing
		 *
		 * @link http://wptheming.com/2010/07/column-edit-pages/
		 */
		public function add_admin_post_thumb_column( $columns ) {
			$column_thumbnail = array( 'thumbnail' => '' );

			return array_slice( $columns, 0, 2, true ) + $column_thumbnail + array_slice( $columns, 1, NULL, true );
		}
		public function display_admin_post_thumb_column( $column ): void {
			global $post;
			global $typenow;
			if (($typenow == $this->cpt_id) && 'thumbnail' === $column) {
                echo get_the_post_thumbnail( $post->ID, array( 35, 35 ) );
            }
		}

		/**
		 * Add taxonomy filters to admin
		 *
		 * @link https://pippinsplugins.com/post-list-filters-for-custom-taxonomies-in-manage-posts/
		 */
		public function add_admin_tax_filters(): void {
			if ( is_array( $this->taxonomies ) && ! empty( $this->taxonomies ) ) {

				global $typenow;

				$taxonomies = array_filter( $this->taxonomies, function( $taxonomy ) {
					return ! empty( $taxonomy['args']['admin_tax_filter'] );
				});

				if ( $typenow == $this->cpt_id && ! empty( $taxonomies ) ) {
					foreach ( $taxonomies as $tax_slug => $tax_arr ) {
						$current_tax_slug = isset( $_GET[ $tax_slug ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax_slug ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$tax_obj = get_taxonomy( $tax_slug );
						$tax_name = $tax_obj->labels->name;
						$terms = get_terms( $tax_slug );
						if ( count( $terms ) > 0 ) {
							echo '<select name="' . esc_attr( $tax_slug ) . '" id="' . esc_attr( $tax_slug ) . '" class="postform">';
							echo '<option value="">' . esc_html( $tax_name ) . '</option>';
							foreach ( $terms as $term ) {
								echo '<option value="' . esc_attr( $term->slug ) . '"';
								echo $current_tax_slug === $term->slug ? ' selected="selected"' : '';
								echo '>' . esc_html( $term->name ) . ' (' . esc_html( $term->count ) . ')</option>';
							}
							echo '</select>';
						}
					}
				}

			}
		}

	}
}