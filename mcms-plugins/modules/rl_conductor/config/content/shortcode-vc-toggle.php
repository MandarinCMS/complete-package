<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'CONFIG_DIR', 'content/vc-custom-heading-element.php' );
$cta_custom_heading = vc_map_integrate_shortcode( vc_custom_heading_element_params(), 'custom_', __( 'Heading', 'rl_conductor' ), array(
	'exclude' => array(
		'source',
		'text',
		'css',
		'link',
	),
), array(
	'element' => 'use_custom_heading',
	'value' => 'true',
) );

$params = array_merge( array(
	array(
		'type' => 'textfield',
		'holder' => 'h4',
		'class' => 'vc_toggle_title',
		'heading' => __( 'Toggle title', 'rl_conductor' ),
		'param_name' => 'title',
		'value' => __( 'Toggle title', 'rl_conductor' ),
		'description' => __( 'Enter title of toggle block.', 'rl_conductor' ),
		'edit_field_class' => 'vc_col-sm-9',
	),
	array(
		'type' => 'checkbox',
		'heading' => __( 'Use custom font?', 'rl_conductor' ),
		'param_name' => 'use_custom_heading',
		'description' => __( 'Enable Google fonts.', 'rl_conductor' ),
		'edit_field_class' => 'vc_col-sm-3',
	),
	array(
		'type' => 'textarea_html',
		'holder' => 'div',
		'class' => 'vc_toggle_content',
		'heading' => __( 'Toggle content', 'rl_conductor' ),
		'param_name' => 'content',
		'value' => __( '<p>Toggle content goes here, click edit button to change this text.</p>', 'rl_conductor' ),
		'description' => __( 'Toggle block content.', 'rl_conductor' ),
	),
	array(
		'type' => 'dropdown',
		'heading' => __( 'Style', 'rl_conductor' ),
		'param_name' => 'style',
		'value' => getVcShared( 'toggle styles' ),
		'description' => __( 'Select toggle design style.', 'rl_conductor' ),
	),
	array(
		'type' => 'dropdown',
		'heading' => __( 'Icon color', 'rl_conductor' ),
		'param_name' => 'color',
		'value' => array( __( 'Default', 'rl_conductor' ) => 'default' ) + getVcShared( 'colors' ),
		'description' => __( 'Select icon color.', 'rl_conductor' ),
		'param_holder_class' => 'vc_colored-dropdown',
	),
	array(
		'type' => 'dropdown',
		'heading' => __( 'Size', 'rl_conductor' ),
		'param_name' => 'size',
		'value' => array_diff_key( getVcShared( 'sizes' ), array( 'Mini' => '' ) ),
		'std' => 'md',
		'description' => __( 'Select toggle size', 'rl_conductor' ),
	),
	array(
		'type' => 'dropdown',
		'heading' => __( 'Default state', 'rl_conductor' ),
		'param_name' => 'open',
		'value' => array(
			__( 'Closed', 'rl_conductor' ) => 'false',
			__( 'Open', 'rl_conductor' ) => 'true',
		),
		'description' => __( 'Select "Open" if you want toggle to be open by default.', 'rl_conductor' ),
	),
	vc_map_add_css_animation(),
	array(
		'type' => 'el_id',
		'heading' => __( 'Element ID', 'rl_conductor' ),
		'param_name' => 'el_id',
		'description' => sprintf( __( 'Enter optional ID. Make sure it is unique, and it is valid as w3c specification: %s (Must not have spaces)', 'rl_conductor' ), '<a target="_blank" href="http://www.w3schools.com/tags/att_global_id.asp">' . __( 'link', 'rl_conductor' ) . '</a>' ),
	),
	array(
		'type' => 'textfield',
		'heading' => __( 'Extra class name', 'rl_conductor' ),
		'param_name' => 'el_class',
		'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
	),
), $cta_custom_heading, array(
	array(
		'type' => 'css_editor',
		'heading' => __( 'CSS box', 'rl_conductor' ),
		'param_name' => 'css',
		'group' => __( 'Design Options', 'rl_conductor' ),
	),
) );

return array(
	'name' => __( 'FAQ', 'rl_conductor' ),
	'base' => 'vc_toggle',
	'icon' => 'icon-mcmsb-toggle-small-expand',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Toggle element for Q&A block', 'rl_conductor' ),
	'params' => $params,
	'js_view' => 'VcToggleView',
);
