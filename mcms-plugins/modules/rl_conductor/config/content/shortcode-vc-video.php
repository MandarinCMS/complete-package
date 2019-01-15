<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Video Player', 'rl_conductor' ),
	'base' => 'vc_video',
	'icon' => 'icon-mcmsb-film-youtube',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Embed YouTube/Vimeo player', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Video link', 'rl_conductor' ),
			'param_name' => 'link',
			'value' => 'https://vimeo.com/51589652',
			'admin_label' => true,
			'description' => sprintf( __( 'Enter link to video (Note: read more about available formats at MandarinCMS <a href="%s" target="_blank">codex page</a>).', 'rl_conductor' ), 'http://codex.mandarincms.com/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Video width', 'rl_conductor' ),
			'param_name' => 'el_width',
			'value' => array(
				'100%' => '100',
				'90%' => '90',
				'80%' => '80',
				'70%' => '70',
				'60%' => '60',
				'50%' => '50',
				'40%' => '40',
				'30%' => '30',
				'20%' => '20',
				'10%' => '10',
			),
			'description' => __( 'Select video width (percentage).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Video aspect ration', 'rl_conductor' ),
			'param_name' => 'el_aspect',
			'value' => array(
				'16:9' => '169',
				'4:3' => '43',
				'2.35:1' => '235',
			),
			'description' => __( 'Select video aspect ratio.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Alignment', 'rl_conductor' ),
			'param_name' => 'align',
			'description' => __( 'Select video alignment.', 'rl_conductor' ),
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
				__( 'Center', 'rl_conductor' ) => 'center',
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
