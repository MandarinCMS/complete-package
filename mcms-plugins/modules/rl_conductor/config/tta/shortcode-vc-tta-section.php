<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

$parent_tag = vc_post_param( 'parent_tag', '' );
$include_icon_params = ( 'vc_tta_pageable' !== $parent_tag );

if ( $include_icon_params ) {
	require_once vc_path_dir( 'CONFIG_DIR', 'content/vc-icon-element.php' );
	$icon_params = array(
		array(
			'type' => 'checkbox',
			'param_name' => 'add_icon',
			'heading' => __( 'Add icon?', 'rl_conductor' ),
			'description' => __( 'Add icon next to section title.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'i_position',
			'value' => array(
				__( 'Before title', 'rl_conductor' ) => 'left',
				__( 'After title', 'rl_conductor' ) => 'right',
			),
			'dependency' => array(
				'element' => 'add_icon',
				'value' => 'true',
			),
			'heading' => __( 'Icon position', 'rl_conductor' ),
			'description' => __( 'Select icon position.', 'rl_conductor' ),
		),
	);
	$icon_params = array_merge( $icon_params, (array) vc_map_integrate_shortcode( vc_icon_element_params(), 'i_', '', array(
			// we need only type, icon_fontawesome, icon_.., NOT color and etc
			'include_only_regex' => '/^(type|icon_\w*)/',
		), array(
			'element' => 'add_icon',
			'value' => 'true',
		) ) );
} else {
	$icon_params = array();
}

$params = array_merge( array(
	array(
		'type' => 'textfield',
		'param_name' => 'title',
		'heading' => __( 'Title', 'rl_conductor' ),
		'description' => __( 'Enter section title (Note: you can leave it empty).', 'rl_conductor' ),
	),
	array(
		'type' => 'el_id',
		'param_name' => 'tab_id',
		'settings' => array(
			'auto_generate' => true,
		),
		'heading' => __( 'Section ID', 'rl_conductor' ),
		'description' => __( 'Enter section ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ),
	),
), $icon_params, array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'rl_conductor' ),
		),
	) );

return array(
	'name' => __( 'Section', 'rl_conductor' ),
	'base' => 'vc_tta_section',
	'icon' => 'icon-mcmsb-ui-tta-section',
	'allowed_container_element' => 'vc_row',
	'is_container' => true,
	'show_settings_on_create' => false,
	'as_child' => array(
		'only' => 'vc_tta_tour,vc_tta_tabs,vc_tta_accordion',
	),
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Section for Tabs, Tours, Accordions.', 'rl_conductor' ),
	'params' => $params,
	'js_view' => 'VcBackendTtaSectionView',
	'custom_markup' => '
		<div class="vc_tta-panel-heading">
		    <h4 class="vc_tta-panel-title vc_tta-controls-icon-position-left"><a href="javascript:;" data-vc-target="[data-model-id=\'{{ model_id }}\']" data-vc-accordion data-vc-container=".vc_tta-container"><span class="vc_tta-title-text">{{ section_title }}</span><i class="vc_tta-controls-icon vc_tta-controls-icon-plus"></i></a></h4>
		</div>
		<div class="vc_tta-panel-body">
			{{ editor_controls }}
			<div class="{{ container-class }}">
			{{ content }}
			</div>
		</div>',
	'default_content' => '',
);
