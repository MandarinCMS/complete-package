<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Inner Row', 'rl_conductor' ),
	//Inner Row
	'content_element' => false,
	'is_container' => true,
	'icon' => 'icon-mcmsb-row',
	'weight' => 1000,
	'show_settings_on_create' => false,
	'description' => __( 'Place content elements inside the inner row', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'el_id',
			'heading' => __( 'Row ID', 'rl_conductor' ),
			'param_name' => 'el_id',
			'description' => sprintf( __( 'Enter optional row ID. Make sure it is unique, and it is valid as w3c specification: %s (Must not have spaces)', 'rl_conductor' ), '<a target="_blank" href="http://www.w3schools.com/tags/att_global_id.asp">' . __( 'link', 'rl_conductor' ) . '</a>' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Equal height', 'rl_conductor' ),
			'param_name' => 'equal_height',
			'description' => __( 'If checked columns will be set to equal height.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Content position', 'rl_conductor' ),
			'param_name' => 'content_placement',
			'value' => array(
				__( 'Default', 'rl_conductor' ) => '',
				__( 'Top', 'rl_conductor' ) => 'top',
				__( 'Middle', 'rl_conductor' ) => 'middle',
				__( 'Bottom', 'rl_conductor' ) => 'bottom',
			),
			'description' => __( 'Select content position within columns.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Columns gap', 'rl_conductor' ),
			'param_name' => 'gap',
			'value' => array(
				'0px' => '0',
				'1px' => '1',
				'2px' => '2',
				'3px' => '3',
				'4px' => '4',
				'5px' => '5',
				'10px' => '10',
				'15px' => '15',
				'20px' => '20',
				'25px' => '25',
				'30px' => '30',
				'35px' => '35',
			),
			'std' => '0',
			'description' => __( 'Select gap between columns in row.', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Disable row', 'rl_conductor' ),
			'param_name' => 'disable_element',
			// Inner param name.
			'description' => __( 'If checked the row won\'t be visible on the public side of your website. You can switch it back any time.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
		array(
			'type' => 'css_editor',
			'heading' => __( 'CSS box', 'rl_conductor' ),
			'param_name' => 'css',
			'group' => __( 'Design Options', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcRowView',
);
