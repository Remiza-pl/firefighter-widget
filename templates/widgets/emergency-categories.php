<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $firefighter_stats_template_vars;
if ( ! empty( $firefighter_stats_template_vars ) && is_array( $firefighter_stats_template_vars ) ) : extract( $firefighter_stats_template_vars );

// TEMPLATE : BEGIN ?>

<div class="widget__content">

	<ul class="root">

    	<?php wp_list_categories(array(
			'title_li' => '',
			'taxonomy' => 'firefighter_stats_cat',
			'show_count' => false,
		)); ?>

	</ul>

</div>

<?php // TEMPLATE : END
endif; ?>
