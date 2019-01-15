<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Image Gallery', 'rl_conductor' ),
	'base' => 'vc_gallery',
	'icon' => 'icon-mcmsb-images-stack',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Responsive image gallery', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Gallery type', 'rl_conductor' ),
			'param_name' => 'type',
			'value' => array(
				__( 'Flex slider fade', 'rl_conductor' ) => 'flexslider_fade',
				__( 'Flex slider slide', 'rl_conductor' ) => 'flexslider_slide',
				__( 'Nivo slider', 'rl_conductor' ) => 'nivo',
				__( 'Image grid', 'rl_conductor' ) => 'image_grid',
			),
			'description' => __( 'Select gallery type.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Auto rotate', 'rl_conductor' ),
			'param_name' => 'interval',
			'value' => array(
				3,
				5,
				10,
				15,
				__( 'Disable', 'rl_conductor' ) => 0,
			),
			'description' => __( 'Auto rotate slides each X seconds.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'type',
				'value' => array(
					'flexslider_fade',
					'flexslider_slide',
					'nivo',
				),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image source', 'rl_conductor' ),
			'param_name' => 'source',
			'value' => array(
				__( 'Media library', 'rl_conductor' ) => 'media_library',
				__( 'External links', 'rl_conductor' ) => 'external_link',
			),
			'std' => 'media_library',
			'description' => __( 'Select image source.', 'rl_conductor' ),
		),
		array(
			'type' => 'attach_images',
			'heading' => __( 'Images', 'rl_conductor' ),
			'param_name' => 'images',
			'value' => '',
			'description' => __( 'Select images from media library.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'media_library',
			),
		),
		array(
			'type' => 'exploded_textarea_safe',
			'heading' => __( 'External links', 'rl_conductor' ),
			'param_name' => 'custom_srcs',
			'description' => __( 'Enter external link for each gallery image (Note: divide links with linebreaks (Enter)).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'external_link',
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Image size', 'rl_conductor' ),
			'param_name' => 'img_size',
			'value' => 'thumbnail',
			'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current myskin. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'source',
				'value' => 'media_library',
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
			'type' => 'dropdown',
			'heading' => __( 'On click action', 'rl_conductor' ),
			'param_name' => 'onclick',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Link to large image', 'rl_conductor' ) => 'img_link_large',
				__( 'Open prettyPhoto', 'rl_conductor' ) => 'link_image',
				__( 'Open custom link', 'rl_conductor' ) => 'custom_link',
			),
			'description' => __( 'Select action for click action.', 'rl_conductor' ),
			'std' => 'link_image',
		),
		array(
			'type' => 'exploded_textarea_safe',
			'heading' => __( 'Custom links', 'rl_conductor' ),
			'param_name' => 'custom_links',
			'description' => __( 'Enter links for each slide (Note: divide links with linebreaks (Enter)).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'onclick',
				'value' => array( 'custom_link' ),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Custom link target', 'rl_conductor' ),
			'param_name' => 'custom_links_target',
			'description' => __( 'Select where to open  custom links.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'onclick',
				'value' => array(
					'custom_link',
					'img_link_large',
				),
			),
			'value' => vc_target_param_list(),
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
