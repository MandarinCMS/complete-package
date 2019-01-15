<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

function vc_icon_element_params() {
	return array(
		'name' => __( 'Icon', 'rl_conductor' ),
		'base' => 'vc_icon',
		'icon' => 'icon-mcmsb-vc_icon',
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Eye catching icons from libraries', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Icon library', 'rl_conductor' ),
				'value' => array(
					__( 'Font Awesome', 'rl_conductor' ) => 'fontawesome',
					__( 'Open Iconic', 'rl_conductor' ) => 'openiconic',
					__( 'Typicons', 'rl_conductor' ) => 'typicons',
					__( 'Entypo', 'rl_conductor' ) => 'entypo',
					__( 'Linecons', 'rl_conductor' ) => 'linecons',
					__( 'Mono Social', 'rl_conductor' ) => 'monosocial',
					__( 'Material', 'rl_conductor' ) => 'material',
				),
				'admin_label' => true,
				'param_name' => 'type',
				'description' => __( 'Select icon library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_fontawesome',
				'value' => 'fa fa-adjust',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display, we use (big number) to display all icons in single page
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'fontawesome',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_openiconic',
				'value' => 'vc-oi vc-oi-dial',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'openiconic',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'openiconic',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_typicons',
				'value' => 'typcn typcn-adjust-brightness',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'typicons',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'typicons',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_entypo',
				'value' => 'entypo-icon entypo-icon-note',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'entypo',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'entypo',
				),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_linecons',
				'value' => 'vc_li vc_li-heart',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'linecons',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'linecons',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_monosocial',
				'value' => 'vc-mono vc-mono-fivehundredpx',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'monosocial',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'monosocial',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'iconpicker',
				'heading' => __( 'Icon', 'rl_conductor' ),
				'param_name' => 'icon_material',
				'value' => 'vc-material vc-material-cake',
				// default value to backend editor admin_label
				'settings' => array(
					'emptyIcon' => false,
					// default true, display an "EMPTY" icon?
					'type' => 'material',
					'iconsPerPage' => 4000,
					// default 100, how many icons per/page to display
				),
				'dependency' => array(
					'element' => 'type',
					'value' => 'material',
				),
				'description' => __( 'Select icon from library.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Icon color', 'rl_conductor' ),
				'param_name' => 'color',
				'value' => array_merge( getVcShared( 'colors' ), array( __( 'Custom color', 'rl_conductor' ) => 'custom' ) ),
				'description' => __( 'Select icon color.', 'rl_conductor' ),
				'param_holder_class' => 'vc_colored-dropdown',
			),
			array(
				'type' => 'colorpicker',
				'heading' => __( 'Custom color', 'rl_conductor' ),
				'param_name' => 'custom_color',
				'description' => __( 'Select custom icon color.', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'color',
					'value' => 'custom',
				),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Background shape', 'rl_conductor' ),
				'param_name' => 'background_style',
				'value' => array(
					__( 'None', 'rl_conductor' ) => '',
					__( 'Circle', 'rl_conductor' ) => 'rounded',
					__( 'Square', 'rl_conductor' ) => 'boxed',
					__( 'Rounded', 'rl_conductor' ) => 'rounded-less',
					__( 'Outline Circle', 'rl_conductor' ) => 'rounded-outline',
					__( 'Outline Square', 'rl_conductor' ) => 'boxed-outline',
					__( 'Outline Rounded', 'rl_conductor' ) => 'rounded-less-outline',
				),
				'description' => __( 'Select background shape and style for icon.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Background color', 'rl_conductor' ),
				'param_name' => 'background_color',
				'value' => array_merge( getVcShared( 'colors' ), array( __( 'Custom color', 'rl_conductor' ) => 'custom' ) ),
				'std' => 'grey',
				'description' => __( 'Select background color for icon.', 'rl_conductor' ),
				'param_holder_class' => 'vc_colored-dropdown',
				'dependency' => array(
					'element' => 'background_style',
					'not_empty' => true,
				),
			),
			array(
				'type' => 'colorpicker',
				'heading' => __( 'Custom background color', 'rl_conductor' ),
				'param_name' => 'custom_background_color',
				'description' => __( 'Select custom icon background color.', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'background_color',
					'value' => 'custom',
				),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Size', 'rl_conductor' ),
				'param_name' => 'size',
				'value' => array_merge( getVcShared( 'sizes' ), array( 'Extra Large' => 'xl' ) ),
				'std' => 'md',
				'description' => __( 'Icon size.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Icon alignment', 'rl_conductor' ),
				'param_name' => 'align',
				'value' => array(
					__( 'Left', 'rl_conductor' ) => 'left',
					__( 'Right', 'rl_conductor' ) => 'right',
					__( 'Center', 'rl_conductor' ) => 'center',
				),
				'description' => __( 'Select icon alignment.', 'rl_conductor' ),
			),
			array(
				'type' => 'vc_link',
				'heading' => __( 'URL (Link)', 'rl_conductor' ),
				'param_name' => 'link',
				'description' => __( 'Add link to icon.', 'rl_conductor' ),
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
		'js_view' => 'VcIconElementView_Backend',
	);
}
