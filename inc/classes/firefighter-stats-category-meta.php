<?php
/**
 * Firefighter Stats Category Meta Fields
 *
 * Adds custom fields to emergency categories for icons and other metadata
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Category_Meta' ) ) {
    class Firefighter_Stats_Category_Meta {

        public function __construct() {
            add_action( 'firefighter_stats_cat_add_form_fields', array( $this, 'add_category_fields' ) );
            add_action( 'firefighter_stats_cat_edit_form_fields', array( $this, 'edit_category_fields' ) );
            add_action( 'edited_firefighter_stats_cat', array( $this, 'save_category_fields' ) );
            add_action( 'create_firefighter_stats_cat', array( $this, 'save_category_fields' ) );
        }

        /**
         * Add fields to category add form
         */
        public function add_category_fields() {
            ?>
            <?php wp_nonce_field( 'firefighter_stats_save_cat_meta', 'firefighter_stats_cat_nonce' ); ?>
            <div class="form-field">
                <label for="firefighter_stats_category_icon"><?php esc_html_e( 'Category Icon', 'firefighter-widget' ); ?></label>
                <select name="firefighter_stats_category_icon" id="firefighter_stats_category_icon">
                    <option value=""><?php esc_html_e( 'Select Icon', 'firefighter-widget' ); ?></option>
                    <?php echo wp_kses( $this->get_icon_options(), array( 'option' => array( 'value' => array(), 'selected' => array() ) ) ); ?>
                </select>
                <p class="description"><?php esc_html_e( 'Choose an icon to represent this emergency category.', 'firefighter-widget' ); ?></p>
            </div>

            <div class="form-field">
                <label for="firefighter_stats_category_custom_icon"><?php esc_html_e( 'Custom Icon (Emoji)', 'firefighter-widget' ); ?></label>
                <input type="text" name="firefighter_stats_category_custom_icon" id="firefighter_stats_category_custom_icon" value="" maxlength="10" style="width: 60px;" />
                <p class="description"><?php esc_html_e( 'Enter any emoji to override the icon above. Leave empty to use the dropdown selection.', 'firefighter-widget' ); ?></p>
            </div>

            <div class="form-field">
                <label for="firefighter_stats_category_color"><?php esc_html_e( 'Category Color', 'firefighter-widget' ); ?></label>
                <input type="color" name="firefighter_stats_category_color" id="firefighter_stats_category_color" value="#e74c3c" />
                <p class="description"><?php esc_html_e( 'Choose a color for this emergency category in widgets and lists.', 'firefighter-widget' ); ?></p>
            </div>
            <?php
        }

        /**
         * Add fields to category edit form
         */
        public function edit_category_fields( $term ) {
            $icon        = get_term_meta( $term->term_id, 'firefighter_stats_category_icon', true );
            $custom_icon = get_term_meta( $term->term_id, 'firefighter_stats_category_custom_icon', true );
            $color       = get_term_meta( $term->term_id, 'firefighter_stats_category_color', true );
            if ( empty( $color ) ) {
                $color = '#e74c3c'; // Default red color
            }
            ?>
            <tr>
                <td colspan="2"><?php wp_nonce_field( 'firefighter_stats_save_cat_meta', 'firefighter_stats_cat_nonce' ); ?></td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="firefighter_stats_category_icon"><?php esc_html_e( 'Category Icon', 'firefighter-widget' ); ?></label>
                </th>
                <td>
                    <select name="firefighter_stats_category_icon" id="firefighter_stats_category_icon">
                        <option value=""><?php esc_html_e( 'Select Icon', 'firefighter-widget' ); ?></option>
                        <?php echo wp_kses( $this->get_icon_options( $icon ), array( 'option' => array( 'value' => array(), 'selected' => array() ) ) ); ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Choose an icon to represent this emergency category.', 'firefighter-widget' ); ?></p>
                </td>
            </tr>

            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="firefighter_stats_category_custom_icon"><?php esc_html_e( 'Custom Icon (Emoji)', 'firefighter-widget' ); ?></label>
                </th>
                <td>
                    <input type="text" name="firefighter_stats_category_custom_icon" id="firefighter_stats_category_custom_icon" value="<?php echo esc_attr( $custom_icon ); ?>" maxlength="10" style="width: 60px;" />
                    <p class="description"><?php esc_html_e( 'Enter any emoji to override the icon above. Leave empty to use the dropdown selection.', 'firefighter-widget' ); ?></p>
                </td>
            </tr>

            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="firefighter_stats_category_color"><?php esc_html_e( 'Category Color', 'firefighter-widget' ); ?></label>
                </th>
                <td>
                    <input type="color" name="firefighter_stats_category_color" id="firefighter_stats_category_color" value="<?php echo esc_attr( $color ); ?>" />
                    <p class="description"><?php esc_html_e( 'Choose a color for this emergency category in widgets and lists.', 'firefighter-widget' ); ?></p>
                </td>
            </tr>
            <?php
        }

        /**
         * Save category fields
         */
        public function save_category_fields( $term_id ) {
            if ( ! isset( $_POST['firefighter_stats_cat_nonce'] ) ||
                ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['firefighter_stats_cat_nonce'] ) ), 'firefighter_stats_save_cat_meta' ) ) {
                return;
            }

            if ( isset( $_POST['firefighter_stats_category_icon'] ) ) {
                update_term_meta( $term_id, 'firefighter_stats_category_icon', sanitize_text_field( wp_unslash( $_POST['firefighter_stats_category_icon'] ) ) );
            }

            if ( isset( $_POST['firefighter_stats_category_custom_icon'] ) ) {
                update_term_meta( $term_id, 'firefighter_stats_category_custom_icon', sanitize_text_field( wp_unslash( $_POST['firefighter_stats_category_custom_icon'] ) ) );
            }

            if ( isset( $_POST['firefighter_stats_category_color'] ) ) {
                $color = sanitize_hex_color( wp_unslash( $_POST['firefighter_stats_category_color'] ) );
                if ( $color ) {
                    update_term_meta( $term_id, 'firefighter_stats_category_color', $color );
                }
            }
        }

        /**
         * Get default color for category based on icon
         */
        public static function get_category_color( $category_id, $icon = '' ) {
            // First try to get saved color
            $saved_color = get_term_meta( $category_id, 'firefighter_stats_category_color', true );
            if ( $saved_color ) {
                return $saved_color;
            }

            // If no saved color, get icon and return default color
            if ( empty( $icon ) ) {
                $icon = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );
            }

            // Default colors based on emergency type
            $default_colors = array(
                'fire' => '#e74c3c',        // Red
                'medical' => '#3498db',     // Blue
                'rescue' => '#f39c12',      // Orange
                'accident' => '#e67e22',    // Dark Orange
                'threat' => '#9b59b6',      // Purple
                'hazmat' => '#8e44ad',      // Dark Purple
                'water' => '#2980b9',       // Dark Blue
                'technical' => '#27ae60',   // Green
                'vehicle' => '#34495e',     // Dark Gray
                'structure' => '#95a5a6',   // Gray
                'false-alarm' => '#7f8c8d', // Light Gray
                'exercise' => '#16a085',    // Teal
                'other' => '#2c3e50',       // Very Dark Gray
            );

            return isset( $default_colors[ $icon ] ) ? $default_colors[ $icon ] : '#e74c3c';
        }

        /**
         * Get icon options for select field
         */
        private function get_icon_options( $selected = '' ) {
            $icons = array(
                'fire' => esc_html__( 'Fire 🔥', 'firefighter-widget' ),
                'medical' => esc_html__( 'Medical 🚑', 'firefighter-widget' ),
                'rescue' => esc_html__( 'Rescue 🆘', 'firefighter-widget' ),
                'accident' => esc_html__( 'Accident ⚠️', 'firefighter-widget' ),
                'threat' => esc_html__( 'Local Threat ⚠️', 'firefighter-widget' ),
                'hazmat' => esc_html__( 'Hazmat ☢️', 'firefighter-widget' ),
                'water' => esc_html__( 'Water 🌊', 'firefighter-widget' ),
                'technical' => esc_html__( 'Technical 🔧', 'firefighter-widget' ),
                'vehicle' => esc_html__( 'Vehicle 🚗', 'firefighter-widget' ),
                'structure' => esc_html__( 'Structure 🏢', 'firefighter-widget' ),
                'false-alarm' => esc_html__( 'False Alarm 🚫', 'firefighter-widget' ),
                'exercise' => esc_html__( 'Exercise 🏋️', 'firefighter-widget' ),
                'other' => esc_html__( 'Other 📋', 'firefighter-widget' ),
            );

            $options = '';
            foreach ( $icons as $class => $label ) {
                $selected_attr = selected( $selected, $class, false );
                $options .= sprintf( 
                    '<option value="%s" %s>%s</option>', 
                    esc_attr( $class ), 
                    $selected_attr, 
                    esc_html( $label ) 
                );
            }

            return $options;
        }


    }
}
