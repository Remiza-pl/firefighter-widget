<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// General class for handling permalink settings
if ( ! class_exists( 'Firefighter_Stats_Permalink_Settings' ) ) {
	class Firefighter_Stats_Permalink_Settings {

		public $args;

		public function __construct( $args ) {

			$this->args = $args;
			add_action( 'admin_init', array( $this, 'form' ) );
			$this->change_permalinks();

		}

		public function form() {

			// Show form
		    add_settings_section(
		        $this->args['id'],
		        $this->args['title'],
		        array( $this, 'display_form' ),
		        'permalink'
		    );

		    // Save form — nonce is verified by WordPress core for the Permalinks settings page.
		    // phpcs:disable WordPress.Security.NonceVerification.Missing
		    if ( isset( $_POST[ 'permalink_structure' ] ) ) {

		    	$permalinks = get_option( $this->args['option_id'] );
				if ( empty( $permalinks ) || ! is_array( $permalinks ) ) {
					$permalinks = array();
				}

				// Parse all fields
				foreach ( $this->args['fields'] as $field_id => $field ) {

					$field_name = $field_id . '-slug';

					if ( ! empty( $_POST[ $field_name ] ) ) {
						$raw_slug = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
						$permalinks[ $field_id ] = strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', $raw_slug ) );
						$permalinks[ $field_id ] = ! empty( $permalinks[ $field_id ] ) ? $permalinks[ $field_id ] : $field['default'];
					} else {
						$permalinks[ $field_id ] = $field['default'];
					}

				}

				// Save settings
				update_option( $this->args['option_id'], $permalinks );

		    }
			// phpcs:enable WordPress.Security.NonceVerification.Missing

		}

		public function display_form() {

			$permalinks = get_option( $this->args['option_id'] );

			?>

			<table class="form-table">
				<tbody>

					<?php foreach ( $this->args['fields'] as $field_id => $field ) : ?>
						<tr>
							<th>
								<label for="<?php echo esc_attr( $field_id ); ?>-slug">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
							</th>
							<td>
								<?php $value = ! empty( $permalinks[ $field_id ] ) ? $permalinks[ $field_id ] : $field['default']; ?>
								<input type="text" class="regular-text code"
									name="<?php echo esc_attr( $field_id ); ?>-slug"
									id="<?php echo esc_attr( $field_id ); ?>-slug"
									value="<?php echo esc_attr( $value ); ?>">
							</td>
						</tr>
					<?php endforeach; ?>

				</tbody>
			</table>

			<?php

		}

		public function change_permalinks() {

			$permalinks = get_option( $this->args['option_id'] );

			foreach ( $this->args['fields'] as $field_id => $field ) {

				if ( ! empty( $permalinks[ $field_id ] ) ) {

					$slug = $permalinks[ $field_id ];

					// CPT
					if ( 'cpt' === $field['type'] ) {
						add_filter( $field_id .'_cpt_wp_args', function () use ( $slug ) {
							return array( 'rewrite' => array( 'slug' => $slug ) );
						});
					}

					// Tax
					else if ( 'tax' === $field['type'] ) {
						add_filter( $field_id .'_tax_wp_args', function () use ( $slug ) {
							return array( 'rewrite' => array( 'slug' => $slug ) );
						});
					}

				}

			}

		}

	}
}