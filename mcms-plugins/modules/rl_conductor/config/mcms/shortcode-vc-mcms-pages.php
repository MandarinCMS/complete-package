<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => 'MCMS ' . __( 'Pages' ),
	'base' => 'vc_mcms_pages',
	'icon' => 'icon-mcmsb-mcms',
	'category' => __( 'MandarinCMS Widgets', 'rl_conductor' ),
	'class' => 'mcmsb_vc_mcms_widget',
	'weight' => - 50,
	'description' => __( 'Your sites MandarinCMS Pages', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'What text use as a widget title. Leave blank to use default widget title.', 'rl_conductor' ),
			'value' => __( 'Pages' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Order by', 'rl_conductor' ),
			'param_name' => 'sortby',
			'value' => array(
				__( 'Page title', 'rl_conductor' ) => 'post_title',
				__( 'Page order', 'rl_conductor' ) => 'menu_order',
				__( 'Page ID', 'rl_conductor' ) => 'ID',
			),
			'description' => __( 'Select how to sort pages.', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Exclude', 'rl_conductor' ),
			'param_name' => 'exclude',
			'description' => __( 'Enter page IDs to be excluded (Note: separate values by commas (,)).', 'rl_conductor' ),
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
