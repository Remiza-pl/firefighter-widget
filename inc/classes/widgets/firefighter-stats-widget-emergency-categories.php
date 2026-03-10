<?php
/**
 * Firefighter Stats emergency categories widget
 *
 * Display list of firefighter_stats_cat tax terms
 */
if ( ! class_exists( 'Firefighter_Stats_Widget_Emergency_Categories' ) && class_exists( 'Firefighter_Stats_Widget' ) ) {
class Firefighter_Stats_Widget_Emergency_Categories extends Firefighter_Stats_Widget {

    public function __construct() {

    	// Init widget
		parent::__construct(array(
			'id' => 'firefighter_stats_emergency_categories',
			'classname' => 'firefighter-stats-emergency-categories-widget',
			'title' => esc_html__( 'Firefighter Stats Emergency Categories', 'firefighter-stats' ),
			'description' => esc_html__( 'List of Emergency categories', 'firefighter-stats' ),
			'fields' => array(
				'title' => array(
					'label' => esc_html__( 'Title:', 'firefighter-stats' ),
					'type' => 'text',
					'default' => esc_html__( 'Emergency Categories', 'firefighter-stats' ),
				),
			),
		));

    }

    function widget( $args, $instance ): void {

    	// Prepare template vars
    	global $firefighter_stats_template_vars;
  		$firefighter_stats_template_vars = array(
  			'instance' => $instance,
		);

        // Before widget content
        parent::before_widget_content( $args, $instance );

        // Load template
        $template_path = apply_filters( 'firefighter_stats_widget_emergency_categories_template_path', plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/widgets/emergency-categories.php' );
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            // Fallback if template doesn't exist
            $this->render_fallback_template( $firefighter_stats_template_vars );
        }

        // After widget content
        parent::after_widget_content( $args, $instance );

    }

    /**
     * Render fallback template if main template is not found
     */
    private function render_fallback_template( $template_vars ) {
        if ( empty( $template_vars ) || ! is_array( $template_vars ) ) {
            return;
        }

        extract( $template_vars );
        ?>
        <div class="widget__content">
            <ul class="root">
                <?php wp_list_categories( array(
                    'title_li' => '',
                    'taxonomy' => 'firefighter_stats_cat',
                    'show_count' => false,
                ) ); ?>
            </ul>
        </div>
        <?php
    }

}}

?>
