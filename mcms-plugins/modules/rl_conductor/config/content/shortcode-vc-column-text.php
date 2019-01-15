<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Text Block', 'rl_conductor' ),
	'icon' => 'icon-mcmsb-layer-shape-text',
	'wrapper_class' => 'clearfix',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'A block of text with WYSIWYG editor', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textarea_html',
			'holder' => 'div',
			'heading' => __( 'Text', 'rl_conductor' ),
			'param_name' => 'content',
			'value' => __( '<p>I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>', 'rl_conductor' ),
		),
		vc_map_add_css_animation(),
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
		array(
			'type' => 'css_editor',
			'heading' => __( 'CSS box', 'rl_conductor' ),
			'param_name' => 'css',
			'group' => __( 'Design Options', 'rl_conductor' ),
		),
	),
);
