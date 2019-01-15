<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Section', 'rl_conductor' ),
	'is_container' => true,
	'icon' => 'vc_icon-vc-section',
	'show_settings_on_create' => false,
	'category' => __( 'Content', 'rl_conductor' ),
	'as_parent' => array(
		'only' => 'vc_row',
	),
	'as_child' => array(
		'only' => '', // Only root
	),
	'class' => 'vc_main-sortable-element',
	'description' => __( 'Group multiple rows in section', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'dropdown',
			'heading' => __( 'Section stretch', 'rl_conductor' ),
			'param_name' => 'full_width',
			'value' => array(
				__( 'Default', 'rl_conductor' ) => '',
				__( 'Stretch section', 'rl_conductor' ) => 'stretch_row',
				__( 'Stretch section and content', 'rl_conductor' ) => 'stretch_row_content',
			),
			'description' => __( 'Select stretching options for section and content (Note: stretched may not work properly if parent container has "overflow: hidden" CSS property).', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Full height section?', 'rl_conductor' ),
			'param_name' => 'full_height',
			'description' => __( 'If checked section will be set to full height.', 'rl_conductor' ),
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
			'description' => __( 'Select content position within section.', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Use video background?', 'rl_conductor' ),
			'param_name' => 'video_bg',
			'description' => __( 'If checked, video will be used as section background.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'YouTube link', 'rl_conductor' ),
			'param_name' => 'video_bg_url',
			'value' => 'https://www.youtube.com/watch?v=lMJXxhRFO1k',
			// default video url
			'description' => __( 'Add YouTube link.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'video_bg',
				'not_empty' => true,
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Parallax', 'rl_conductor' ),
			'param_name' => 'video_bg_parallax',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Simple', 'rl_conductor' ) => 'content-moving',
				__( 'With fade', 'rl_conductor' ) => 'content-moving-fade',
			),
			'description' => __( 'Add parallax type background for section.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'video_bg',
				'not_empty' => true,
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Parallax', 'rl_conductor' ),
			'param_name' => 'parallax',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Simple', 'rl_conductor' ) => 'content-moving',
				__( 'With fade', 'rl_conductor' ) => 'content-moving-fade',
			),
			'description' => __( 'Add parallax type background for section (Note: If no image is specified, parallax will use background image from Design Options).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'video_bg',
				'is_empty' => true,
			),
		),
		array(
			'type' => 'attach_image',
			'heading' => __( 'Image', 'rl_conductor' ),
			'param_name' => 'parallax_image',
			'value' => '',
			'description' => __( 'Select image from media library.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'parallax',
				'not_empty' => true,
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Parallax speed', 'rl_conductor' ),
			'param_name' => 'parallax_speed_video',
			'value' => '1.5',
			'description' => __( 'Enter parallax speed ratio (Note: Default value is 1.5, min value is 1)', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'video_bg_parallax',
				'not_empty' => true,
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Parallax speed', 'rl_conductor' ),
			'param_name' => 'parallax_speed_bg',
			'value' => '1.5',
			'description' => __( 'Enter parallax speed ratio (Note: Default value is 1.5, min value is 1)', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'parallax',
				'not_empty' => true,
			),
		),
		vc_map_add_css_animation( false ),
		array(
			'type' => 'el_id',
			'heading' => __( 'Section ID', 'rl_conductor' ),
			'param_name' => 'el_id',
			'description' => sprintf( __( 'Enter section ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Disable section', 'rl_conductor' ),
			'param_name' => 'disable_element',
			// Inner param name.
			'description' => __( 'If checked the section won\'t be visible on the public side of your website. You can switch it back any time.', 'rl_conductor' ),
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
	'js_view' => 'VcSectionView',
);
