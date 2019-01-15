<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Button', 'rl_conductor' ) . ' 2',
	'base' => 'vc_button2',
	'icon' => 'icon-mcmsb-ui-button',
	'deprecated' => '4.5',
	'content_element' => false,
	'category' => array(
		__( 'Content', 'rl_conductor' ),
	),
	'description' => __( 'Eye catching button', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'vc_link',
			'heading' => __( 'URL (Link)', 'rl_conductor' ),
			'param_name' => 'link',
			'description' => __( 'Add link to button.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Text', 'rl_conductor' ),
			'holder' => 'button',
			'class' => 'vc_btn',
			'param_name' => 'title',
			'value' => __( 'Text on the button', 'rl_conductor' ),
			'description' => __( 'Enter text on the button.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Alignment', 'rl_conductor' ),
			'param_name' => 'align',
			'value' => array(
				__( 'Inline', 'rl_conductor' ) => 'inline',
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Center', 'rl_conductor' ) => 'center',
				__( 'Right', 'rl_conductor' ) => 'right',
			),
			'description' => __( 'Select button alignment.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Shape', 'rl_conductor' ),
			'param_name' => 'style',
			'value' => getVcShared( 'button styles' ),
			'description' => __( 'Select button display style and shape.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'color',
			'value' => getVcShared( 'colors' ),
			'description' => __( 'Select button color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Size', 'rl_conductor' ),
			'param_name' => 'size',
			'value' => getVcShared( 'sizes' ),
			'std' => 'md',
			'description' => __( 'Select button size.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcButton2View',
);
