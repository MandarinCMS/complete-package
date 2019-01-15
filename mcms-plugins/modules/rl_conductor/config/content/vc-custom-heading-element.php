<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

function vc_custom_heading_element_params() {
	return array(
		'name' => __( 'Custom Heading', 'rl_conductor' ),
		'base' => 'vc_custom_heading',
		'icon' => 'icon-mcmsb-ui-custom_heading',
		'show_settings_on_create' => true,
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Text with Google fonts', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Text source', 'rl_conductor' ),
				'param_name' => 'source',
				'value' => array(
					__( 'Custom text', 'rl_conductor' ) => '',
					__( 'Post or Page Title', 'rl_conductor' ) => 'post_title',
				),
				'std' => '',
				'description' => __( 'Select text source.', 'rl_conductor' ),
			),
			array(
				'type' => 'textarea',
				'heading' => __( 'Text', 'rl_conductor' ),
				'param_name' => 'text',
				'admin_label' => true,
				'value' => __( 'This is custom heading element', 'rl_conductor' ),
				'description' => __( 'Note: If you are using non-latin characters be sure to activate them under Settings/RazorLeaf Conductor/General Settings.', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'source',
					'is_empty' => true,
				),
			),
			array(
				'type' => 'vc_link',
				'heading' => __( 'URL (Link)', 'rl_conductor' ),
				'param_name' => 'link',
				'description' => __( 'Add link to custom heading.', 'rl_conductor' ),
				// compatible with btn2 and converted from href{btn1}
			),
			array(
				'type' => 'font_container',
				'param_name' => 'font_container',
				'value' => 'tag:h2|text_align:left',
				'settings' => array(
					'fields' => array(
						'tag' => 'h2',
						// default value h2
						'text_align',
						'font_size',
						'line_height',
						'color',
						'tag_description' => __( 'Select element tag.', 'rl_conductor' ),
						'text_align_description' => __( 'Select text alignment.', 'rl_conductor' ),
						'font_size_description' => __( 'Enter font size.', 'rl_conductor' ),
						'line_height_description' => __( 'Enter line height.', 'rl_conductor' ),
						'color_description' => __( 'Select heading color.', 'rl_conductor' ),
					),
				),
			),
			array(
				'type' => 'checkbox',
				'heading' => __( 'Use myskin default font family?', 'rl_conductor' ),
				'param_name' => 'use_myskin_fonts',
				'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
				'description' => __( 'Use font family from the myskin.', 'rl_conductor' ),
			),
			array(
				'type' => 'google_fonts',
				'param_name' => 'google_fonts',
				'value' => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
				'settings' => array(
					'fields' => array(
						'font_family_description' => __( 'Select font family.', 'rl_conductor' ),
						'font_style_description' => __( 'Select font styling.', 'rl_conductor' ),
					),
				),
				'dependency' => array(
					'element' => 'use_myskin_fonts',
					'value_not_equal_to' => 'yes',
				),
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
}
