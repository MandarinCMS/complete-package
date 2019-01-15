<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/* Message box 2
---------------------------------------------------------- */
$pixel_icons = vc_pixel_icons();
$custom_colors = array(
	__( 'Informational', 'rl_conductor' ) => 'info',
	__( 'Warning', 'rl_conductor' ) => 'warning',
	__( 'Success', 'rl_conductor' ) => 'success',
	__( 'Error', 'rl_conductor' ) => 'danger',
	__( 'Informational Classic', 'rl_conductor' ) => 'alert-info',
	__( 'Warning Classic', 'rl_conductor' ) => 'alert-warning',
	__( 'Success Classic', 'rl_conductor' ) => 'alert-success',
	__( 'Error Classic', 'rl_conductor' ) => 'alert-danger',
);

return array(
	'name' => __( 'Message Box', 'rl_conductor' ),
	'base' => 'vc_message',
	'icon' => 'icon-mcmsb-information-white',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Notification box', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'params_preset',
			'heading' => __( 'Message Box Presets', 'rl_conductor' ),
			'param_name' => 'color',
			// due to backward compatibility, really it is message_box_type
			'value' => '',
			'options' => array(
				array(
					'label' => __( 'Custom', 'rl_conductor' ),
					'value' => '',
					'params' => array(),
				),
				array(
					'label' => __( 'Informational', 'rl_conductor' ),
					'value' => 'info',
					'params' => array(
						'message_box_color' => 'info',
						'icon_type' => 'fontawesome',
						'icon_fontawesome' => 'fa fa-info-circle',
					),
				),
				array(
					'label' => __( 'Warning', 'rl_conductor' ),
					'value' => 'warning',
					'params' => array(
						'message_box_color' => 'warning',
						'icon_type' => 'fontawesome',
						'icon_fontawesome' => 'fa fa-exclamation-triangle',
					),
				),
				array(
					'label' => __( 'Success', 'rl_conductor' ),
					'value' => 'success',
					'params' => array(
						'message_box_color' => 'success',
						'icon_type' => 'fontawesome',
						'icon_fontawesome' => 'fa fa-check',
					),
				),
				array(
					'label' => __( 'Error', 'rl_conductor' ),
					'value' => 'danger',
					'params' => array(
						'message_box_color' => 'danger',
						'icon_type' => 'fontawesome',
						'icon_fontawesome' => 'fa fa-times',
					),
				),
				array(
					'label' => __( 'Informational Classic', 'rl_conductor' ),
					'value' => 'alert-info',
					// due to backward compatibility
					'params' => array(
						'message_box_color' => 'alert-info',
						'icon_type' => 'pixelicons',
						'icon_pixelicons' => 'vc_pixel_icon vc_pixel_icon-info',
					),
				),
				array(
					'label' => __( 'Warning Classic', 'rl_conductor' ),
					'value' => 'alert-warning',
					// due to backward compatibility
					'params' => array(
						'message_box_color' => 'alert-warning',
						'icon_type' => 'pixelicons',
						'icon_pixelicons' => 'vc_pixel_icon vc_pixel_icon-alert',
					),
				),
				array(
					'label' => __( 'Success Classic', 'rl_conductor' ),
					'value' => 'alert-success',
					// due to backward compatibility
					'params' => array(
						'message_box_color' => 'alert-success',
						'icon_type' => 'pixelicons',
						'icon_pixelicons' => 'vc_pixel_icon vc_pixel_icon-tick',
					),
				),
				array(
					'label' => __( 'Error Classic', 'rl_conductor' ),
					'value' => 'alert-danger',
					// due to backward compatibility
					'params' => array(
						'message_box_color' => 'alert-danger',
						'icon_type' => 'pixelicons',
						'icon_pixelicons' => 'vc_pixel_icon vc_pixel_icon-explanation',
					),
				),
			),
			'description' => __( 'Select predefined message box design or choose "Custom" for custom styling.', 'rl_conductor' ),
			'param_holder_class' => 'vc_message-type vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Style', 'rl_conductor' ),
			'param_name' => 'message_box_style',
			'value' => getVcShared( 'message_box_styles' ),
			'description' => __( 'Select message box design style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Shape', 'rl_conductor' ),
			'param_name' => 'style',
			// due to backward compatibility message_box_shape
			'std' => 'rounded',
			'value' => array(
				__( 'Square', 'rl_conductor' ) => 'square',
				__( 'Rounded', 'rl_conductor' ) => 'rounded',
				__( 'Round', 'rl_conductor' ) => 'round',
			),
			'description' => __( 'Select message box shape.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'message_box_color',
			'value' => $custom_colors + getVcShared( 'colors' ),
			'description' => __( 'Select message box color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_message-type vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Icon library', 'rl_conductor' ),
			'value' => array(
				__( 'Font Awesome', 'rl_conductor' ) => 'fontawesome',
				__( 'Open Iconic', 'rl_conductor' ) => 'openiconic',
				__( 'Typicons', 'rl_conductor' ) => 'typicons',
				__( 'Entypo', 'rl_conductor' ) => 'entypo',
				__( 'Linecons', 'rl_conductor' ) => 'linecons',
				__( 'Pixel', 'rl_conductor' ) => 'pixelicons',
				__( 'Mono Social', 'rl_conductor' ) => 'monosocial',
			),
			'param_name' => 'icon_type',
			'description' => __( 'Select icon library.', 'rl_conductor' ),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_fontawesome',
			'value' => 'fa fa-info-circle',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'iconsPerPage' => 4000,
				// default 100, how many icons per/page to display
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'fontawesome',
			),
			'description' => __( 'Select icon from library.', 'rl_conductor' ),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_openiconic',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'type' => 'openiconic',
				'iconsPerPage' => 4000,
				// default 100, how many icons per/page to display
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'openiconic',
			),
			'description' => __( 'Select icon from library.', 'rl_conductor' ),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_typicons',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'type' => 'typicons',
				'iconsPerPage' => 4000,
				// default 100, how many icons per/page to display
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'typicons',
			),
			'description' => __( 'Select icon from library.', 'rl_conductor' ),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_entypo',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'type' => 'entypo',
				'iconsPerPage' => 4000,
				// default 100, how many icons per/page to display
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'entypo',
			),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_linecons',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'type' => 'linecons',
				'iconsPerPage' => 4000,
				// default 100, how many icons per/page to display
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'linecons',
			),
			'description' => __( 'Select icon from library.', 'rl_conductor' ),
		),
		array(
			'type' => 'iconpicker',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon_pixelicons',
			'settings' => array(
				'emptyIcon' => false,
				// default true, display an "EMPTY" icon?
				'type' => 'pixelicons',
				'source' => $pixel_icons,
			),
			'dependency' => array(
				'element' => 'icon_type',
				'value' => 'pixelicons',
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
				'element' => 'icon_type',
				'value' => 'monosocial',
			),
			'description' => __( 'Select icon from library.', 'rl_conductor' ),
		),
		array(
			'type' => 'textarea_html',
			'holder' => 'div',
			'class' => 'messagebox_text',
			'heading' => __( 'Message text', 'rl_conductor' ),
			'param_name' => 'content',
			'value' => __( '<p>I am message box. Click edit button to change this text.</p>', 'rl_conductor' ),
		),
		vc_map_add_css_animation( false ),
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
	'js_view' => 'VcMessageView_Backend',
);
