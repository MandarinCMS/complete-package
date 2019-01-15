<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Image Carousel', 'rl_conductor' ),
	'base' => 'vc_images_carousel',
	'icon' => 'icon-mcmsb-images-carousel',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Animated carousel with images', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'attach_images',
			'heading' => __( 'Images', 'rl_conductor' ),
			'param_name' => 'images',
			'value' => '',
			'description' => __( 'Select images from media library.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Carousel size', 'rl_conductor' ),
			'param_name' => 'img_size',
			'value' => 'thumbnail',
			'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current myskin. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size. If used slides per view, this will be used to define carousel wrapper size.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'On click action', 'rl_conductor' ),
			'param_name' => 'onclick',
			'value' => array(
				__( 'Open prettyPhoto', 'rl_conductor' ) => 'link_image',
				__( 'None', 'rl_conductor' ) => 'link_no',
				__( 'Open custom links', 'rl_conductor' ) => 'custom_link',
			),
			'description' => __( 'Select action for click event.', 'rl_conductor' ),
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
			'description' => __( 'Select how to open custom links.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'onclick',
				'value' => array( 'custom_link' ),
			),
			'value' => vc_target_param_list(),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Slider orientation', 'rl_conductor' ),
			'param_name' => 'mode',
			'value' => array(
				__( 'Horizontal', 'rl_conductor' ) => 'horizontal',
				__( 'Vertical', 'rl_conductor' ) => 'vertical',
			),
			'description' => __( 'Select slider position (Note: this affects swiping orientation).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Slider speed', 'rl_conductor' ),
			'param_name' => 'speed',
			'value' => '5000',
			'description' => __( 'Duration of animation between slides (in ms).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Slides per view', 'rl_conductor' ),
			'param_name' => 'slides_per_view',
			'value' => '1',
			'description' => __( 'Enter number of slides to display at the same time.', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Slider autoplay', 'rl_conductor' ),
			'param_name' => 'autoplay',
			'description' => __( 'Enable autoplay mode.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Hide pagination control', 'rl_conductor' ),
			'param_name' => 'hide_pagination_control',
			'description' => __( 'If checked, pagination controls will be hidden.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Hide prev/next buttons', 'rl_conductor' ),
			'param_name' => 'hide_prev_next_buttons',
			'description' => __( 'If checked, prev/next buttons will be hidden.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Partial view', 'rl_conductor' ),
			'param_name' => 'partial_view',
			'description' => __( 'If checked, part of the next slide will be visible.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Slider loop', 'rl_conductor' ),
			'param_name' => 'wrap',
			'description' => __( 'Enable slider loop mode.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
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
