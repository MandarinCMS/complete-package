<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

VcShortcodeAutoloader::getInstance()->includeClass( 'MCMSBakeryShortCode_VC_Gitem_Animated_Block' );

global $vc_gitem_add_link_param;
$vc_gitem_add_link_param = apply_filters( 'vc_gitem_add_link_param', array(
	'type' => 'dropdown',
	'heading' => __( 'Add link', 'rl_conductor' ),
	'param_name' => 'link',
	'value' => array(
		__( 'None', 'rl_conductor' ) => 'none',
		__( 'Post link', 'rl_conductor' ) => 'post_link',
		__( 'Post author', 'rl_conductor' ) => 'post_author',
		__( 'Large image', 'rl_conductor' ) => 'image',
		__( 'Large image (prettyPhoto)', 'rl_conductor' ) => 'image_lightbox',
		__( 'Custom', 'rl_conductor' ) => 'custom',
	),
	'description' => __( 'Select link option.', 'rl_conductor' ),
) );
$zone_params = array(
	$vc_gitem_add_link_param,
	array(
		'type' => 'vc_link',
		'heading' => __( 'URL (Link)', 'rl_conductor' ),
		'param_name' => 'url',
		'dependency' => array(
			'element' => 'link',
			'value' => array( 'custom' ),
		),
		'description' => __( 'Add custom link.', 'rl_conductor' ),
	),
	array(
		'type' => 'checkbox',
		'heading' => __( 'Use featured image on background?', 'rl_conductor' ),
		'param_name' => 'featured_image',
		'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		'description' => __( 'Note: Featured image overwrites background image and color from "Design Options".', 'rl_conductor' ),
	),
	array(
		'type' => 'textfield',
		'heading' => __( 'Image size', 'rl_conductor' ),
		'param_name' => 'img_size',
		'value' => 'large',
		'description' => __( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by myskin). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'rl_conductor' ),
		'dependency' => array(
			'element' => 'featured_image',
			'not_empty' => true,
		),
	),
	array(
		'type' => 'css_editor',
		'heading' => __( 'CSS box', 'rl_conductor' ),
		'param_name' => 'css',
		'group' => __( 'Design Options', 'rl_conductor' ),
	),
	array(
		'type' => 'textfield',
		'heading' => __( 'Extra class name', 'rl_conductor' ),
		'param_name' => 'el_class',
		'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
	),
);
$post_data_params = array(
	$vc_gitem_add_link_param,
	array(
		'type' => 'vc_link',
		'heading' => __( 'URL (Link)', 'rl_conductor' ),
		'param_name' => 'url',
		'dependency' => array(
			'element' => 'link',
			'value' => array( 'custom' ),
		),
		'description' => __( 'Add custom link.', 'rl_conductor' ),
	),
	array(
		'type' => 'css_editor',
		'heading' => __( 'CSS box', 'rl_conductor' ),
		'param_name' => 'css',
		'group' => __( 'Design Options', 'rl_conductor' ),
	),
);
$custom_fonts_params = array(
	array(
		'type' => 'font_container',
		'param_name' => 'font_container',
		'value' => '',
		'settings' => array(
			'fields' => array(
				'tag' => 'div', // default value h2
				'text_align',
				'tag_description' => __( 'Select element tag.', 'rl_conductor' ),
				'text_align_description' => __( 'Select text alignment.', 'rl_conductor' ),
				'font_size_description' => __( 'Enter font size.', 'rl_conductor' ),
				'line_height_description' => __( 'Enter line height.', 'rl_conductor' ),
				'color_description' => __( 'Select color for your element.', 'rl_conductor' ),
			),
		),
	),
	array(
		'type' => 'checkbox',
		'heading' => __( 'Use custom fonts?', 'rl_conductor' ),
		'param_name' => 'use_custom_fonts',
		'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		'description' => __( 'Enable Google fonts.', 'rl_conductor' ),
	),
	array(
		'type' => 'font_container',
		'param_name' => 'block_container',
		'value' => '',
		'settings' => array(
			'fields' => array(
				'font_size',
				'line_height',
				'color',
				'tag_description' => __( 'Select element tag.', 'rl_conductor' ),
				'text_align_description' => __( 'Select text alignment.', 'rl_conductor' ),
				'font_size_description' => __( 'Enter font size.', 'rl_conductor' ),
				'line_height_description' => __( 'Enter line height.', 'rl_conductor' ),
				'color_description' => __( 'Select color for your element.', 'rl_conductor' ),
			),
		),
		'group' => __( 'Custom fonts', 'rl_conductor' ),
		'dependency' => array(
			'element' => 'use_custom_fonts',
			'value' => array( 'yes' ),
		),
	),
	array(
		'type' => 'checkbox',
		'heading' => __( 'Yes myskin default font family?', 'rl_conductor' ),
		'param_name' => 'use_myskin_fonts',
		'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		'description' => __( 'Yes font family from the myskin.', 'rl_conductor' ),
		'group' => __( 'Custom fonts', 'rl_conductor' ),
		'dependency' => array(
			'element' => 'use_custom_fonts',
			'value' => array( 'yes' ),
		),
	),
	array(
		'type' => 'google_fonts',
		'param_name' => 'google_fonts',
		'value' => '',
		// Not recommended, this will override 'settings'. 'font_family:'.rawurlencode('Exo:100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic').'|font_style:'.rawurlencode('900 bold italic:900:italic'),
		'settings' => array(
			'fields' => array(
				// Default font style. Name:weight:style, example: "800 bold regular:800:normal"
				'font_family_description' => __( 'Select font family.', 'rl_conductor' ),
				'font_style_description' => __( 'Select font styling.', 'rl_conductor' ),
			),
		),
		'group' => __( 'Custom fonts', 'rl_conductor' ),
		'dependency' => array(
			'element' => 'use_myskin_fonts',
			'value_not_equal_to' => 'yes',
		),
	),
);
$list = array(
	'vc_gitem' => array(
		'name' => __( 'Grid Item', 'rl_conductor' ),
		'base' => 'vc_gitem',
		'is_container' => true,
		'icon' => 'icon-mcmsb-gitem',
		'content_element' => false,
		'show_settings_on_create' => false,
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Main grid item', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'css_editor',
				'heading' => __( 'CSS box', 'rl_conductor' ),
				'param_name' => 'css',
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'js_view' => 'VcGitemView',
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_animated_block' => array(
		'base' => 'vc_gitem_animated_block',
		'name' => __( 'A/B block', 'rl_conductor' ),
		'content_element' => false,
		'is_container' => true,
		'show_settings_on_create' => false,
		'icon' => 'icon-mcmsb-gitem-block',
		'category' => __( 'Content', 'rl_conductor' ),
		'controls' => array(),
		'as_parent' => array( 'only' => array( 'vc_gitem_zone_a', 'vc_gitem_zone_b' ) ),
		'params' => array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Animation', 'rl_conductor' ),
				'param_name' => 'animation',
				'value' => MCMSBakeryShortCode_VC_Gitem_Animated_Block::animations(),
			),
		),
		'js_view' => 'VcGitemAnimatedBlockView',
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_zone' => array(
		'name' => __( 'Zone', 'rl_conductor' ),
		'base' => 'vc_gitem_zone',
		'content_element' => false,
		'is_container' => true,
		'show_settings_on_create' => false,
		'icon' => 'icon-mcmsb-gitem-zone',
		'category' => __( 'Content', 'rl_conductor' ),
		'controls' => array( 'edit' ),
		'as_parent' => array( 'only' => 'vc_gitem_row' ),
		'js_view' => 'VcGitemZoneView',
		'params' => $zone_params,
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_zone_a' => array(
		'name' => __( 'Normal', 'rl_conductor' ),
		'base' => 'vc_gitem_zone_a',
		'content_element' => false,
		'is_container' => true,
		'show_settings_on_create' => false,
		'icon' => 'icon-mcmsb-gitem-zone',
		'category' => __( 'Content', 'rl_conductor' ),
		'controls' => array( 'edit' ),
		'as_parent' => array( 'only' => 'vc_gitem_row' ),
		'js_view' => 'VcGitemZoneView',
		'params' => array_merge( array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Height mode', 'rl_conductor' ),
				'param_name' => 'height_mode',
				'value' => array(
					'1:1' => '1-1',
					__( 'Original', 'rl_conductor' ) => 'original',
					'4:3' => '4-3',
					'3:4' => '3-4',
					'16:9' => '16-9',
					'9:16' => '9-16',
					__( 'Custom', 'rl_conductor' ) => 'custom',
				),
				'description' => __( 'Sizing proportions for height and width. Select "Original" to scale image without cropping.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Height', 'rl_conductor' ),
				'param_name' => 'height',
				'dependency' => array(
					'element' => 'height_mode',
					'value' => array( 'custom' ),
				),
				'description' => __( 'Enter custom height.', 'rl_conductor' ),
			),
		), $zone_params ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_zone_b' => array(
		'name' => __( 'Hover', 'rl_conductor' ),
		'base' => 'vc_gitem_zone_b',
		'content_element' => false,
		'is_container' => true,
		'show_settings_on_create' => false,
		'icon' => 'icon-mcmsb-gitem-zone',
		'category' => __( 'Content', 'rl_conductor' ),
		'controls' => array( 'edit' ),
		'as_parent' => array( 'only' => 'vc_gitem_row' ),
		'js_view' => 'VcGitemZoneView',
		'params' => $zone_params,
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_zone_c' => array(
		'name' => __( 'Additional', 'rl_conductor' ),
		'base' => 'vc_gitem_zone_c',
		'content_element' => false,
		'is_container' => true,
		'show_settings_on_create' => false,
		'icon' => 'icon-mcmsb-gitem-zone',
		'category' => __( 'Content', 'rl_conductor' ),
		'controls' => array( 'move', 'delete', 'edit' ),
		'as_parent' => array( 'only' => 'vc_gitem_row' ),
		'js_view' => 'VcGitemZoneCView',
		'params' => array(
			array(
				'type' => 'css_editor',
				'heading' => __( 'CSS box', 'rl_conductor' ),
				'param_name' => 'css',
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_row' => array(
		'name' => __( 'Row', 'rl_conductor' ),
		'base' => 'vc_gitem_row',
		'content_element' => false,
		'is_container' => true,
		'icon' => 'icon-mcmsb-row',
		'weight' => 1000,
		'show_settings_on_create' => false,
		'controls' => array( 'layout', 'delete' ),
		'allowed_container_element' => 'vc_gitem_col',
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Place content elements inside the row', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'js_view' => 'VcGitemRowView',
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_col' => array(
		'name' => __( 'Column', 'rl_conductor' ),
		'base' => 'vc_gitem_col',
		'icon' => 'icon-mcmsb-row',
		'weight' => 1000,
		'is_container' => true,
		'allowed_container_element' => false,
		'content_element' => false,
		'controls' => array( 'edit' ),
		'description' => __( 'Place content elements inside the column', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Width', 'rl_conductor' ),
				'param_name' => 'width',
				'value' => array(
					__( '1 column - 1/12', 'rl_conductor' ) => '1/12',
					__( '2 columns - 1/6', 'rl_conductor' ) => '1/6',
					__( '3 columns - 1/4', 'rl_conductor' ) => '1/4',
					__( '4 columns - 1/3', 'rl_conductor' ) => '1/3',
					__( '5 columns - 5/12', 'rl_conductor' ) => '5/12',
					__( '6 columns - 1/2', 'rl_conductor' ) => '1/2',
					__( '7 columns - 7/12', 'rl_conductor' ) => '7/12',
					__( '8 columns - 2/3', 'rl_conductor' ) => '2/3',
					__( '9 columns - 3/4', 'rl_conductor' ) => '3/4',
					__( '10 columns - 5/6', 'rl_conductor' ) => '5/6',
					__( '11 columns - 11/12', 'rl_conductor' ) => '11/12',
					__( '12 columns - 1/1', 'rl_conductor' ) => '1/1',
				),
				'description' => __( 'Select column width.', 'rl_conductor' ),
				'std' => '1/1',
			),
			array(
				'type' => 'checkbox',
				'heading' => __( 'Use featured image on background?', 'rl_conductor' ),
				'param_name' => 'featured_image',
				'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
				'description' => __( 'Note: Featured image overwrites background image and color from "Design Options".', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Image size', 'rl_conductor' ),
				'param_name' => 'img_size',
				'value' => 'large',
				'description' => __( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by myskin). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'featured_image',
					'not_empty' => true,
				),
			),
			array(
				'type' => 'css_editor',
				'heading' => __( 'CSS box', 'rl_conductor' ),
				'param_name' => 'css',
				'group' => __( 'Design Options', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'js_view' => 'VcGitemColView',
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	/*'vc_gitem_post_data' => array(
		'name' => __( 'Post data', 'rl_conductor' ),
		'base' => 'vc_gitem_post_data',
		'content_element' => false,
		'category' => __( 'Post', 'rl_conductor' ),
		'params' => array_merge( array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Post data source', 'rl_conductor' ),
				'param_name' => 'data_source',
				'value' => 'ID',
			)
		), $post_data_params, $custom_fonts_params, array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		) ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),*/
	'vc_gitem_post_title' => array(
		'name' => __( 'Post Title', 'rl_conductor' ),
		'base' => 'vc_gitem_post_title',
		'icon' => 'vc_icon-vc-gitem-post-title',
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Title of current post', 'rl_conductor' ),
		'params' => array_merge( $post_data_params, $custom_fonts_params, array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		) ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_post_excerpt' => array(
		'name' => __( 'Post Excerpt', 'rl_conductor' ),
		'base' => 'vc_gitem_post_excerpt',
		'icon' => 'vc_icon-vc-gitem-post-excerpt',
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Excerpt or manual excerpt', 'rl_conductor' ),
		'params' => array_merge( $post_data_params, $custom_fonts_params, array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		) ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_post_author' => array(
		'name' => __( 'Post Author', 'rl_conductor' ),
		'base' => 'vc_gitem_post_author',
		'icon' => 'vc_icon-vc-gitem-post-author', // @todo change icon ?
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Author of current post', 'rl_conductor' ),
		'params' => array_merge( array(
			array(
				'type' => 'checkbox',
				'heading' => __( 'Add link', 'rl_conductor' ),
				'param_name' => 'link',
				'value' => '',
				'description' => __( 'Add link to author?', 'rl_conductor' ),
			),
			array(
				'type' => 'css_editor',
				'heading' => __( 'CSS box', 'rl_conductor' ),
				'param_name' => 'css',
				'group' => __( 'Design Options', 'rl_conductor' ),
			),
		), $custom_fonts_params, array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		) ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_post_categories' => array(
		'name' => __( 'Post Categories', 'rl_conductor' ),
		'base' => 'vc_gitem_post_categories',
		'icon' => 'vc_icon-vc-gitem-post-categories', // @todo change icon ?
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Categories of current post', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'checkbox',
				'heading' => __( 'Add link', 'rl_conductor' ),
				'param_name' => 'link',
				'value' => '',
				'description' => __( 'Add link to category?', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Style', 'rl_conductor' ),
				'param_name' => 'category_style',
				'value' => array(
					__( 'None', 'rl_conductor' ) => ' ',
					__( 'Comma', 'rl_conductor' ) => ', ',
					__( 'Rounded', 'rl_conductor' ) => 'filled vc_grid-filter-filled-round-all',
					__( 'Less Rounded', 'rl_conductor' ) => 'filled vc_grid-filter-filled-rounded-all',
					__( 'Border', 'rl_conductor' ) => 'bordered',
					__( 'Rounded Border', 'rl_conductor' ) => 'bordered-rounded vc_grid-filter-filled-round-all',
					__( 'Less Rounded Border', 'rl_conductor' ) => 'bordered-rounded-less vc_grid-filter-filled-rounded-all',
				),
				'description' => __( 'Select category display style.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Color', 'rl_conductor' ),
				'param_name' => 'category_color',
				'value' => getVcShared( 'colors' ),
				'std' => 'grey',
				'param_holder_class' => 'vc_colored-dropdown',
				'dependency' => array(
					'element' => 'category_style',
					'value_not_equal_to' => array( ' ', ', ' ),
				),
				'description' => __( 'Select category color.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Category size', 'rl_conductor' ),
				'param_name' => 'category_size',
				'value' => getVcShared( 'sizes' ),
				'std' => 'md',
				'description' => __( 'Select category size.', 'rl_conductor' ),
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
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_image' => array(
		'name' => __( 'Post Image', 'rl_conductor' ),
		'base' => 'vc_gitem_image',
		'icon' => 'vc_icon-vc-gitem-image',
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Featured image', 'rl_conductor' ),
		'params' => array(
			$vc_gitem_add_link_param,
			array(
				'type' => 'vc_link',
				'heading' => __( 'URL (Link)', 'rl_conductor' ),
				'param_name' => 'url',
				'dependency' => array(
					'element' => 'link',
					'value' => array( 'custom' ),
				),
				'description' => __( 'Add custom link.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Image size', 'rl_conductor' ),
				'param_name' => 'img_size',
				'description' => __( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by myskin). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave parameter empty to use "thumbnail" by default.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Image alignment', 'rl_conductor' ),
				'param_name' => 'alignment',
				'value' => array(
					__( 'Left', 'rl_conductor' ) => '',
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
				'description' => __( 'Select image display style.', 'rl_conductor' ),
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
					),
				),
				'description' => __( 'Border color.', 'rl_conductor' ),
				'param_holder_class' => 'vc_colored-dropdown',
			),
			vc_add_css_animation(),
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
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_post_date' => array(
		'name' => __( 'Post Date', 'rl_conductor' ),
		'base' => 'vc_gitem_post_date',
		'icon' => 'vc_icon-vc-gitem-post-date',
		'category' => __( 'Post', 'rl_conductor' ),
		'description' => __( 'Post publish date', 'rl_conductor' ),
		'params' => array_merge( $post_data_params, $custom_fonts_params, array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		) ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
	'vc_gitem_post_meta' => array(
		'name' => __( 'Custom Field', 'rl_conductor' ),
		'base' => 'vc_gitem_post_meta',
		'icon' => 'vc_icon-vc-gitem-post-meta',
		'category' => array(
			__( 'Elements', 'rl_conductor' )
		),
		'description' => __( 'Custom fields data from meta values of the post.', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'textfield',
				'heading' => __( 'Field key name', 'rl_conductor' ),
				'param_name' => 'key',
				'description' => __( 'Enter custom field name to retrieve meta data value.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Label', 'rl_conductor' ),
				'param_name' => 'label',
				'description' => __( 'Enter label to display before key value.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Alignment', 'rl_conductor' ),
				'param_name' => 'align',
				'value' => array(
					__( 'Left', 'rl_conductor' ) => 'left',
					__( 'Right', 'rl_conductor' ) => 'right',
					__( 'Center', 'rl_conductor' ) => 'center',
					__( 'Justify', 'rl_conductor' ) => 'justify',
				),
				'description' => __( 'Select alignment.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
);
$shortcode_vc_column_text = MCMSBMap::getShortCode( 'vc_column_text' );
if ( is_array( $shortcode_vc_column_text ) && isset( $shortcode_vc_column_text['base'] ) ) {
	$list['vc_column_text'] = $shortcode_vc_column_text;
	$list['vc_column_text']['post_type'] = Vc_Grid_Item_Editor::postType();
	$remove = array( 'el_id' );
	foreach ( $list['vc_column_text']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_column_text']['params'][ $k ] );
		}
	}
}
$shortcode_vc_separator = MCMSBMap::getShortCode( 'vc_separator' );
if ( is_array( $shortcode_vc_separator ) && isset( $shortcode_vc_separator['base'] ) ) {
	$list['vc_separator'] = $shortcode_vc_separator;
	$list['vc_separator']['post_type'] = Vc_Grid_Item_Editor::postType();
	$remove = array( 'el_id' );
	foreach ( $list['vc_separator']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_separator']['params'][ $k ] );
		}
	}
}
$shortcode_vc_text_separator = MCMSBMap::getShortCode( 'vc_text_separator' );
if ( is_array( $shortcode_vc_text_separator ) && isset( $shortcode_vc_text_separator['base'] ) ) {
	$list['vc_text_separator'] = $shortcode_vc_text_separator;
	$list['vc_text_separator']['post_type'] = Vc_Grid_Item_Editor::postType();

	$remove = array( 'el_id' );
	foreach ( $list['vc_text_separator']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_text_separator']['params'][ $k ] );
		}
	}
}
$shortcode_vc_icon = MCMSBMap::getShortCode( 'vc_icon' );
if ( is_array( $shortcode_vc_icon ) && isset( $shortcode_vc_icon['base'] ) ) {
	$list['vc_icon'] = $shortcode_vc_icon;
	$list['vc_icon']['post_type'] = Vc_Grid_Item_Editor::postType();
	$list['vc_icon']['params'] = vc_map_integrate_shortcode( 'vc_icon', '', '', array( 'exclude' => array( 'link', 'el_id' ) ) );
}
$list['vc_single_image'] = array(
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
		),
		vc_map_add_css_animation(),
		array(
			'type' => 'textfield',
			'heading' => __( 'Image size', 'rl_conductor' ),
			'param_name' => 'img_size',
			'description' => __( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by myskin). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave parameter empty to use "thumbnail" by default.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Image alignment', 'rl_conductor' ),
			'param_name' => 'alignment',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => '',
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
			'description' => __( 'Select image display style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Border color', 'rl_conductor' ),
			'param_name' => 'border_color',
			'value' => getVcShared( 'colors' ),
			'std' => 'grey',
			'dependency' => array(
				'element' => 'style',
				'value' => array( 'vc_box_border', 'vc_box_border_circle', 'vc_box_outline', 'vc_box_outline_circle' ),
			),
			'description' => __( 'Border color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
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
	'post_type' => Vc_Grid_Item_Editor::postType(),
);
$shortcode_vc_button2 = MCMSBMap::getShortCode( 'vc_button2' );
if ( is_array( $shortcode_vc_button2 ) && isset( $shortcode_vc_button2['base'] ) ) {
	$list['vc_button2'] = $shortcode_vc_button2;
	$list['vc_button2']['post_type'] = Vc_Grid_Item_Editor::postType();
}

$shortcode_vc_btn = MCMSBMap::getShortCode( 'vc_btn' );
if ( is_array( $shortcode_vc_btn ) && isset( $shortcode_vc_btn['base'] ) ) {
	$list['vc_btn'] = $shortcode_vc_btn;
	$list['vc_btn']['post_type'] = Vc_Grid_Item_Editor::postType();
	unset( $list['vc_btn']['params'][1] );
	$remove = array( 'el_id' );
	foreach ( $list['vc_btn']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_btn']['params'][ $k ] );
		}
	}
}
$shortcode_vc_custom_heading = MCMSBMap::getShortCode( 'vc_custom_heading' );
if ( is_array( $shortcode_vc_custom_heading ) && isset( $shortcode_vc_custom_heading['base'] ) ) {
	$list['vc_custom_heading'] = $shortcode_vc_custom_heading;
	$list['vc_custom_heading']['post_type'] = Vc_Grid_Item_Editor::postType();

	$remove = array( 'link', 'source', 'el_id' );
	foreach ( $list['vc_custom_heading']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_custom_heading']['params'][ $k ] );
		}

		// text depends on source. remove dependency so text is always saved
		if ( 'text' === $v['param_name'] ) {
			unset( $list['vc_custom_heading']['params'][ $k ]['dependency'] );
		}
	}
}
$shortcode_vc_empty_space = MCMSBMap::getShortCode( 'vc_empty_space' );
if ( is_array( $shortcode_vc_empty_space ) && isset( $shortcode_vc_empty_space['base'] ) ) {
	$list['vc_empty_space'] = $shortcode_vc_empty_space;
	$list['vc_empty_space']['post_type'] = Vc_Grid_Item_Editor::postType();
	$remove = array( 'el_id' );
	foreach ( $list['vc_empty_space']['params'] as $k => $v ) {
		if ( in_array( $v['param_name'], $remove ) ) {
			unset( $list['vc_empty_space']['params'][ $k ] );
		}
	}
}
foreach ( array( 'vc_icon', 'vc_button2', 'vc_btn', 'vc_custom_heading', 'vc_single_image' ) as $key ) {
	if ( isset( $list[ $key ] ) ) {
		if ( ! isset( $list[ $key ]['params'] ) ) {
			$list[ $key ]['params'] = array();
		}
		if ( 'vc_button2' === $key ) {
			// change settings for vc_link in dropdown. Add dependency.
			$list[ $key ]['params'][0] = array(
				'type' => 'vc_link',
				'heading' => __( 'URL (Link)', 'rl_conductor' ),
				'param_name' => 'url',
				'dependency' => array(
					'element' => 'link',
					'value' => array( 'custom' ),
				),
				'description' => __( 'Add custom link.', 'rl_conductor' ),
			);
		} else {
			array_unshift( $list[ $key ]['params'], array(
				'type' => 'vc_link',
				'heading' => __( 'URL (Link)', 'rl_conductor' ),
				'param_name' => 'url',
				'dependency' => array(
					'element' => 'link',
					'value' => array( 'custom' ),
				),
				'description' => __( 'Add custom link.', 'rl_conductor' ),
			) );
		}
		// Add link dropdown
		array_unshift( $list[ $key ]['params'], $vc_gitem_add_link_param );
	}
}
foreach ( $list as $key => $value ) {
	if ( isset( $list[ $key ]['params'] ) ) {
		$list[ $key ]['params'] = array_values( $list[ $key ]['params'] );
	}
}

return $list;
