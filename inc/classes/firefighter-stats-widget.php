<?php
/**
 * Base Firefighter Stats Widget Class
 *
 * Provides common functionality for all firefighter stats widgets
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Firefighter_Stats_Widget' ) ) {
    class Firefighter_Stats_Widget extends WP_Widget {

        public $args;

        public function __construct( $args ) {

            $this->args = $args;

            // Widget options
            $widget_options = array(
                'classname' => ! empty( $args['classname'] ) ? $args['classname'] : '',
                'description' => ! empty( $args['description'] ) ? $args['description'] : '',
            );

            // Control options
            $control_options = array(
                'width' => 400,
                'height' => 350,
            );

            // Initialize widget
            parent::__construct(
                ! empty( $args['id'] ) ? $args['id'] : 'firefighter_stats_widget',
                ! empty( $args['title'] ) ? $args['title'] : esc_html__( 'Firefighter Stats Widget', 'firefighter-stats' ),
                $widget_options,
                $control_options
            );

        }

        /**
         * Widget output (must be overridden by child classes)
         */
        public function widget( $args, $instance ) {
            // This should be overridden by child classes
        }

        /**
         * Widget form (admin interface)
         */
        public function form( $instance ) {

            if ( ! empty( $this->args['fields'] ) && is_array( $this->args['fields'] ) ) {

                foreach ( $this->args['fields'] as $field_id => $field ) {

                    $field_value = ! empty( $instance[ $field_id ] ) ? $instance[ $field_id ] : ( ! empty( $field['default'] ) ? $field['default'] : '' );
                    $field_id_attr = $this->get_field_id( $field_id );
                    $field_name_attr = $this->get_field_name( $field_id );

                    echo '<p>';

                    // Field label (skip for checkbox — it renders its own inline label)
                    if ( ! empty( $field['label'] ) && 'checkbox' !== $field['type'] ) {
                        echo '<label for="' . esc_attr( $field_id_attr ) . '">' . esc_html( $field['label'] ) . '</label>';
                    }

                    // Field input
                    if ( 'text' === $field['type'] ) {
                        echo '<input type="text" id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '" value="' . esc_attr( $field_value ) . '" class="widefat" />';
                    }
                    elseif ( 'textarea' === $field['type'] ) {
                        echo '<textarea id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '" class="widefat" rows="4">' . esc_textarea( $field_value ) . '</textarea>';
                    }
                    elseif ( 'select' === $field['type'] && ! empty( $field['choices'] ) ) {
                        echo '<select id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '" class="widefat">';
                        foreach ( $field['choices'] as $choice_value => $choice_label ) {
                            $selected = selected( $field_value, $choice_value, false );
                            echo '<option value="' . esc_attr( $choice_value ) . '" ' . $selected . '>' . esc_html( $choice_label ) . '</option>';
                        }
                        echo '</select>';
                    }
                    elseif ( 'checkbox' === $field['type'] ) {
                        $checked = checked( $field_value, 'true', false );
                        echo '<input type="checkbox" id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '" value="true" ' . $checked . ' />';
                        echo '<label for="' . esc_attr( $field_id_attr ) . '">' . ( ! empty( $field['label'] ) ? esc_html( $field['label'] ) : '' ) . '</label>';
                    }
                    elseif ( 'taxonomy' === $field['type'] && ! empty( $field['taxonomy'] ) ) {
                        $terms = get_terms( array(
                            'taxonomy' => $field['taxonomy'],
                            'hide_empty' => false,
                        ) );
                        
                        echo '<select id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '" class="widefat">';
                        
                        if ( ! empty( $field['default_label'] ) ) {
                            echo '<option value="">' . esc_html( $field['default_label'] ) . '</option>';
                        }
                        
                        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                            foreach ( $terms as $term ) {
                                $selected = selected( $field_value, $term->term_id, false );
                                echo '<option value="' . esc_attr( $term->term_id ) . '" ' . $selected . '>' . esc_html( $term->name ) . '</option>';
                            }
                        }
                        echo '</select>';
                    }
                    elseif ( 'multiselect' === $field['type'] && ! empty( $field['taxonomy'] ) ) {
                        $terms = get_terms( array(
                            'taxonomy' => $field['taxonomy'],
                            'hide_empty' => false,
                        ) );
                        
                        $selected_values = is_array( $field_value ) ? $field_value : array();
                        
                        echo '<select id="' . esc_attr( $field_id_attr ) . '" name="' . esc_attr( $field_name_attr ) . '[]" class="widefat" multiple="multiple" size="5">';
                        
                        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                            foreach ( $terms as $term ) {
                                $selected = in_array( $term->term_id, $selected_values ) ? 'selected="selected"' : '';
                                echo '<option value="' . esc_attr( $term->term_id ) . '" ' . $selected . '>' . esc_html( $term->name ) . '</option>';
                            }
                        }
                        echo '</select>';
                    }

                    // Field description
                    if ( ! empty( $field['description'] ) ) {
                        echo '<small>' . esc_html( $field['description'] ) . '</small>';
                    }

                    echo '</p>';

                }

            }

        }

        /**
         * Update widget settings
         */
        public function update( $new_instance, $old_instance ) {

            $instance = array();

            if ( ! empty( $this->args['fields'] ) && is_array( $this->args['fields'] ) ) {
                foreach ( $this->args['fields'] as $field_id => $field ) {
                    if ( 'multiselect' === $field['type'] ) {
                        $instance[ $field_id ] = ! empty( $new_instance[ $field_id ] ) && is_array( $new_instance[ $field_id ] ) ? array_map( 'sanitize_text_field', $new_instance[ $field_id ] ) : array();
                    } else {
                        $instance[ $field_id ] = ! empty( $new_instance[ $field_id ] ) ? sanitize_text_field( $new_instance[ $field_id ] ) : '';
                    }
                }
            }

            return $instance;

        }

        /**
         * Before widget content
         */
        public function before_widget_content( $args, $instance ) {

            echo $args['before_widget'];

            // Widget title
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
            }

        }

        /**
         * After widget content
         */
        public function after_widget_content( $args, $instance ) {

            echo $args['after_widget'];

        }

    }
}
