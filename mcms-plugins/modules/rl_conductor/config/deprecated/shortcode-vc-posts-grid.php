<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

$vc_layout_sub_controls = array(
	array(
		'link_post',
		__( 'Link to post', 'rl_conductor' ),
	),
	array(
		'no_link',
		__( 'No link', 'rl_conductor' ),
	),
	array(
		'link_image',
		__( 'Link to bigger image', 'rl_conductor' ),
	),
);
return array(
	'name' => __( 'Old Posts Grid', 'rl_conductor' ),
	'base' => 'vc_posts_grid',
	'content_element' => false,
	'deprecated' => '4.4',
	'icon' => 'icon-mcmsb-application-icon-large',
	'description' => __( 'Posts in grid view', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'loop',
			'heading' => __( 'Grids content', 'rl_conductor' ),
			'param_name' => 'loop',
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
			'type' => 'dropdown',
			'heading' => __( 'Columns count', 'rl_conductor' ),
			'param_name' => 'grid_columns_count',
			'value' => array(
				6,
				4,
				3,
				2,
				1,
			),
			'std' => 3,
			'admin_label' => true,
			'description' => __( 'Select columns count.', 'rl_conductor' ),
		),
		array(
			'type' => 'sorted_list',
			'heading' => __( 'Teaser layout', 'rl_conductor' ),
			'param_name' => 'grid_layout',
			'description' => __( 'Control teasers look. Enable blocks and place them in desired order. Note: This setting can be overrriden on post to post basis.', 'rl_conductor' ),
			'value' => 'title,image,text',
			'options' => array(
				array(
					'image',
					__( 'Thumbnail', 'rl_conductor' ),
					$vc_layout_sub_controls,
				),
				array(
					'title',
					__( 'Title', 'rl_conductor' ),
					$vc_layout_sub_controls,
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
			'param_name' => 'grid_link_target',
			'value' => vc_target_param_list(),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Show filter', 'rl_conductor' ),
			'param_name' => 'filter',
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
			'description' => __( 'Select to add animated category filter to your posts grid.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Layout mode', 'rl_conductor' ),
			'param_name' => 'grid_layout_mode',
			'value' => array(
				__( 'Fit rows', 'rl_conductor' ) => 'fitRows',
				__( 'Masonry', 'rl_conductor' ) => 'masonry',
			),
			'description' => __( 'Teaser layout template.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Thumbnail size', 'rl_conductor' ),
			'param_name' => 'grid_thumb_size',
			'value' => 'thumbnail',
			'description' => __( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current myskin. Alternatively enter image size in pixels: 200x100 (Width x Height) . ', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
);