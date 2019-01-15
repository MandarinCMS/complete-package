<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Posts Slider', 'rl_conductor' ),
	'base' => 'vc_posts_slider',
	'icon' => 'icon-mcmsb-slideshow',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Slider with MCMS Posts', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Slider type', 'rl_conductor' ),
			'param_name' => 'type',
			'admin_label' => true,
			'value' => array(
				__( 'Flex slider fade', 'rl_conductor' ) => 'flexslider_fade',
				__( 'Flex slider slide', 'rl_conductor' ) => 'flexslider_slide',
				__( 'Nivo slider', 'rl_conductor' ) => 'nivo',
			),
			'description' => __( 'Select slider type.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Slider count', 'rl_conductor' ),
			'param_name' => 'count',
			'value' => 3,
			'description' => __( 'Enter number of slides to display (Note: Enter "All" to display all slides).', 'rl_conductor' ),
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
		),
		array(
			'type' => 'posttypes',
			'heading' => __( 'Post types', 'rl_conductor' ),
			'param_name' => 'posttypes',
			'description' => __( 'Select source for slider.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Description', 'rl_conductor' ),
			'param_name' => 'slides_content',
			'value' => array(
				__( 'No description', 'rl_conductor' ) => '',
				__( 'Teaser (Excerpt)', 'rl_conductor' ) => 'teaser',
			),
			'description' => __( 'Select source to use for description (Note: some sliders do not support it).', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'type',
				'value' => array(
					'flexslider_fade',
					'flexslider_slide',
				),
			),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Output post title?', 'rl_conductor' ),
			'param_name' => 'slides_title',
			'description' => __( 'If selected, title will be printed before the teaser text.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => true ),
			'dependency' => array(
				'element' => 'slides_content',
				'value' => array( 'teaser' ),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Link', 'rl_conductor' ),
			'param_name' => 'link',
			'value' => array(
				__( 'Link to post', 'rl_conductor' ) => 'link_post',
				__( 'Link to bigger image', 'rl_conductor' ) => 'link_image',
				__( 'Open custom links', 'rl_conductor' ) => 'custom_link',
				__( 'No link', 'rl_conductor' ) => 'link_no',
			),
			'description' => __( 'Link type.', 'rl_conductor' ),
		),
		array(
			'type' => 'exploded_textarea_safe',
			'heading' => __( 'Custom links', 'rl_conductor' ),
			'param_name' => 'custom_links',
			'value' => site_url() . '/',
			'dependency' => array(
				'element' => 'link',
				'value' => 'custom_link',
			),
			'description' => __( 'Enter links for each slide here. Divide links with linebreaks (Enter).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Thumbnail size', 'rl_conductor' ),
			'param_name' => 'thumb_size',
			'value' => 'medium',
			'description' => __( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current myskin. Alternatively enter image size in pixels: 200x100 (Width x Height) . ', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Post/Page IDs', 'rl_conductor' ),
			'param_name' => 'posts_in',
			'description' => __( 'Enter page/posts IDs to display only those records (Note: separate values by commas (,)). Use this field in conjunction with "Post types" field.', 'rl_conductor' ),
		),
		array(
			'type' => 'exploded_textarea_safe',
			'heading' => __( 'Categories', 'rl_conductor' ),
			'param_name' => 'categories',
			'description' => __( 'Enter categories by names to narrow output (Note: only listed categories will be displayed, divide categories with linebreak (Enter)).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Order by', 'rl_conductor' ),
			'param_name' => 'orderby',
			'value' => array(
				'',
				__( 'Date', 'rl_conductor' ) => 'date',
				__( 'ID', 'rl_conductor' ) => 'ID',
				__( 'Author', 'rl_conductor' ) => 'author',
				__( 'Title', 'rl_conductor' ) => 'title',
				__( 'Modified', 'rl_conductor' ) => 'modified',
				__( 'Random', 'rl_conductor' ) => 'rand',
				__( 'Comment count', 'rl_conductor' ) => 'comment_count',
				__( 'Menu order', 'rl_conductor' ) => 'menu_order',
			),
			'description' => sprintf( __( 'Select how to sort retrieved posts. More at %s.', 'rl_conductor' ), '<a href="http://codex.mandarincms.com/Class_Reference/MCMS_Query#Order_.26_Orderby_Parameters" target="_blank">MandarinCMS codex page</a>' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Sort order', 'rl_conductor' ),
			'param_name' => 'order',
			'value' => array(
				__( 'Descending', 'rl_conductor' ) => 'DESC',
				__( 'Ascending', 'rl_conductor' ) => 'ASC',
			),
			'description' => sprintf( __( 'Select ascending or descending order. More at %s.', 'rl_conductor' ), '<a href="http://codex.mandarincms.com/Class_Reference/MCMS_Query#Order_.26_Orderby_Parameters" target="_blank">MandarinCMS codex page</a>' ),
		),
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
