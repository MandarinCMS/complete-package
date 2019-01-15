<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Post Carousel', 'rl_conductor' ),
	'base' => 'vc_carousel',
	'content_element' => false,
	'deprecated' => '4.4',
	'class' => '',
	'icon' => 'icon-mcmsb-vc_carousel',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Animated carousel with posts', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'loop',
			'heading' => __( 'Carousel content', 'rl_conductor' ),
			'param_name' => 'posts_query',
			'value' => 'size:10|order_by:date',
			'settings' => array(
				'size' => array(
					'hidden' => false,
					'value' => 10,
				),
				'order_by' => array( 'value' => 'date' ),
			),
			'description' => __( 'Create MandarinCMS loop, to populate content from your site.', 'rl_conductor' ),
		),
		array(
			'type' => 'sorted_list',
			'heading' => __( 'Teaser layout', 'rl_conductor' ),
			'param_name' => 'layout',
			'description' => __( 'Control teasers look. Enable blocks and place them in desired order. Note: This setting can be overrriden on post to post basis.', 'rl_conductor' ),
			'value' => 'title,image,text',
			'options' => array(
				array(
					'image',
					__( 'Thumbnail', 'rl_conductor' ),
					vc_layout_sub_controls(),
				),
				array(
					'title',
					__( 'Title', 'rl_conductor' ),
					vc_layout_sub_controls(),
				),
				array(
					'text',
					__( 'Text', 'rl_conductor' ),
					array(
						array(
							'excerpt',
							__( 'Teaser/Excerpt', 'rl_conductor' ),
						),
						array(
							'text',
							__( 'Full content', 'rl_conductor' ),
						),
					),
				),
				array(
					'link',
					__( 'Read more link', 'rl_conductor' ),
				),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Link target', 'rl_conductor' ),
			'param_name' => 'link_target',
			'value' => vc_target_param_list(),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Thumbnail size', 'rl_conductor' ),
			'param_name' => 'thumb_size',
			'value' => 'thumbnail',
			'description' => __( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current myskin. Alternatively enter image size in pixels: 200x100 (Width x Height) . ', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Slider speed', 'rl_conductor' ),
			'param_name' => 'speed',
			'value' => '5000',
			'description' => __( 'Duration of animation between slides (in ms).', 'rl_conductor' ),
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
			'description' => __( 'If "YES" pagination control will be removed', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Hide prev/next buttons', 'rl_conductor' ),
			'param_name' => 'hide_prev_next_buttons',
			'description' => __( 'If "YES" prev/next control will be removed', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Partial view', 'rl_conductor' ),
			'param_name' => 'partial_view',
			'description' => __( 'If "YES" part of the next slide will be visible on the right side', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Slider loop', 'rl_conductor' ),
			'param_name' => 'wrap',
			'description' => __( 'Enable slider loop mode.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
);
