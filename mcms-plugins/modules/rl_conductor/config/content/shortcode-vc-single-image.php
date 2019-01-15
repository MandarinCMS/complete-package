<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Single Image', 'rl_conductor' ),
	'base' => 'vc_single_image',
	'icon' => 'icon-mcmsb-single-image',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Simple image with CSS animation', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image source', 'rl_conductor' ),
			'param_name' => 'source',
			'value' => array(
				__( 'Media library', 'rl_conductor' ) => 'media_library',
				__( 'External link', 'rl_conductor' ) => 'external_link',
				__( 'Featured Image', 'rl_conductor' ) => 'featured_image',
			),
			'std' => 'media_library',
			'description' => __( 'Select image source.', 'rl_conductor' ),
		),
		array(
			'type' => 'attach_image',
			'heading' => __( 'Image', 'rl_conductor' ),
			'param_name' => 'image',
			'value' => '',
			'description' => __( 'Select image from media library.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'media_library',
			),
			'admin_label' => true,
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'External link', 'rl_conductor' ),
			'param_name' => 'custom_src',
			'description' => __( 'Select external link.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'external_link',
			),
			'admin_label' => true,
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Image size', 'rl_conductor' ),
			'param_name' => 'img_size',
			'value' => 'thumbnail',
			'description' => __( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by myskin). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => array(
					'media_library',
					'featured_image',
				),
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Image size', 'rl_conductor' ),
			'param_name' => 'external_img_size',
			'value' => '',
			'description' => __( 'Enter image size in pixels. Example: 200x100 (Width x Height).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'external_link',
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Caption', 'rl_conductor' ),
			'param_name' => 'caption',
			'description' => __( 'Enter text for image caption.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'external_link',
			),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Add caption?', 'rl_conductor' ),
			'param_name' => 'add_caption',
			'description' => __( 'Add image caption.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
			'dependency' => array(
				'element' => 'source',
				'value' => array(
					'media_library',
					'featured_image',
				),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image alignment', 'rl_conductor' ),
			'param_name' => 'alignment',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
				__( 'Center', 'rl_conductor' ) => 'center',
			),
			'description' => __( 'Select image alignment.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image style', 'rl_conductor' ),
			'param_name' => 'style',
			'value' => getVcShared( 'single image styles' ),
			'description' => __( 'Select image display style.', 'js_comopser' ),
			'dependency' => array(
				'element' => 'source',
				'value' => array(
					'media_library',
					'featured_image',
				),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image style', 'rl_conductor' ),
			'param_name' => 'external_style',
			'value' => getVcShared( 'single image external styles' ),
			'description' => __( 'Select image display style.', 'js_comopser' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'external_link',
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Border color', 'rl_conductor' ),
			'param_name' => 'border_color',
			'value' => getVcShared( 'colors' ),
			'std' => 'grey',
			'dependency' => array(
				'element' => 'style',
				'value' => array(
					'vc_box_border',
					'vc_box_border_circle',
					'vc_box_outline',
					'vc_box_outline_circle',
					'vc_box_border_circle_2',
					'vc_box_outline_circle_2',
				),
			),
			'description' => __( 'Border color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Border color', 'rl_conductor' ),
			'param_name' => 'external_border_color',
			'value' => getVcShared( 'colors' ),
			'std' => 'grey',
			'dependency' => array(
				'element' => 'external_style',
				'value' => array(
					'vc_box_border',
					'vc_box_border_circle',
					'vc_box_outline',
					'vc_box_outline_circle',
				),
			),
			'description' => __( 'Border color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'On click action', 'rl_conductor' ),
			'param_name' => 'onclick',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Link to large image', 'rl_conductor' ) => 'img_link_large',
				__( 'Open prettyPhoto', 'rl_conductor' ) => 'link_image',
				__( 'Open custom link', 'rl_conductor' ) => 'custom_link',
				__( 'Zoom', 'rl_conductor' ) => 'zoom',
			),
			'description' => __( 'Select action for click action.', 'rl_conductor' ),
			'std' => '',
		),
		array(
			'type' => 'href',
			'heading' => __( 'Image link', 'rl_conductor' ),
			'param_name' => 'link',
			'description' => __( 'Enter URL if you want this image to have a link (Note: parameters like "mailto:" are also accepted).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'onclick',
				'value' => 'custom_link',
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Link Target', 'rl_conductor' ),
			'param_name' => 'img_link_target',
			'value' => vc_target_param_list(),
			'dependency' => array(
				'element' => 'onclick',
				'value' => array(
					'custom_link',
					'img_link_large',
				),
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
		// backward compatibility. since 4.6
		array(
			'type' => 'hidden',
			'param_name' => 'img_link_large',
		),
	),
);
