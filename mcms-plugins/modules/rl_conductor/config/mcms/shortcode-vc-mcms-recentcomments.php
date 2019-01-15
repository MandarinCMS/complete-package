<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => 'MCMS ' . __( 'Recent Comments' ),
	'base' => 'vc_mcms_recentcomments',
	'icon' => 'icon-mcmsb-mcms',
	'category' => __( 'MandarinCMS Widgets', 'rl_conductor' ),
	'class' => 'mcmsb_vc_mcms_widget',
	'weight' => - 50,
	'description' => __( 'The most recent comments', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'What text use as a widget title. Leave blank to use default widget title.', 'rl_conductor' ),
			'value' => __( 'Recent Comments' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Number of comments', 'rl_conductor' ),
			'description' => __( 'Enter number of comments to display.', 'rl_conductor' ),
			'param_name' => 'number',
			'value' => 5,
			'admin_label' => true,
		),
		array(
			'type' => 'el_id',
			'heading' => __( 'Element ID', 'rl_conductor' ),
			'param_name' => 'el_id',
			'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
);
