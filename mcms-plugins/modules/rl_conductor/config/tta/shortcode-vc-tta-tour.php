<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Tour', 'rl_conductor' ),
	'base' => 'vc_tta_tour',
	'icon' => 'icon-mcmsb-ui-tab-content-vertical',
	'is_container' => true,
	'show_settings_on_create' => false,
	'as_parent' => array(
		'only' => 'vc_tta_section',
	),
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Vertical tabbed content', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'param_name' => 'title',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'style',
			'value' => array(
				__( 'Classic', 'rl_conductor' ) => 'classic',
				__( 'Modern', 'rl_conductor' ) => 'modern',
				__( 'Flat', 'rl_conductor' ) => 'flat',
				__( 'Outline', 'rl_conductor' ) => 'outline',
			),
			'heading' => __( 'Style', 'rl_conductor' ),
			'description' => __( 'Select tour display style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'shape',
			'value' => array(
				__( 'Rounded', 'rl_conductor' ) => 'rounded',
				__( 'Square', 'rl_conductor' ) => 'square',
				__( 'Round', 'rl_conductor' ) => 'round',
			),
			'heading' => __( 'Shape', 'rl_conductor' ),
			'description' => __( 'Select tour shape.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'color',
			'heading' => __( 'Color', 'rl_conductor' ),
			'description' => __( 'Select tour color.', 'rl_conductor' ),
			'value' => getVcShared( 'colors-dashed' ),
			'std' => 'grey',
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'checkbox',
			'param_name' => 'no_fill_content_area',
			'heading' => __( 'Do not fill content area?', 'rl_conductor' ),
			'description' => __( 'Do not fill content area with color.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'spacing',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				'1px' => '1',
				'2px' => '2',
				'3px' => '3',
				'4px' => '4',
				'5px' => '5',
				'10px' => '10',
				'15px' => '15',
				'20px' => '20',
				'25px' => '25',
				'30px' => '30',
				'35px' => '35',
			),
			'heading' => __( 'Spacing', 'rl_conductor' ),
			'description' => __( 'Select tour spacing.', 'rl_conductor' ),
			'std' => '1',
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'gap',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				'1px' => '1',
				'2px' => '2',
				'3px' => '3',
				'4px' => '4',
				'5px' => '5',
				'10px' => '10',
				'15px' => '15',
				'20px' => '20',
				'25px' => '25',
				'30px' => '30',
				'35px' => '35',
			),
			'heading' => __( 'Gap', 'rl_conductor' ),
			'description' => __( 'Select tour gap.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'tab_position',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
			),
			'heading' => __( 'Position', 'rl_conductor' ),
			'description' => __( 'Select tour navigation position.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'alignment',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
				__( 'Center', 'rl_conductor' ) => 'center',
			),
			'heading' => __( 'Alignment', 'rl_conductor' ),
			'description' => __( 'Select tour section title alignment.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'controls_size',
			'value' => array(
				__( 'Auto', 'rl_conductor' ) => '',
				__( 'Extra large', 'rl_conductor' ) => 'xl',
				__( 'Large', 'rl_conductor' ) => 'lg',
				__( 'Medium', 'rl_conductor' ) => 'md',
				__( 'Small', 'rl_conductor' ) => 'sm',
				__( 'Extra small', 'rl_conductor' ) => 'xs',
			),
			'heading' => __( 'Navigation width', 'rl_conductor' ),
			'description' => __( 'Select tour navigation width.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'autoplay',
			'value' => array(
				__( 'None', 'rl_conductor' ) => 'none',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'10' => '10',
				'20' => '20',
				'30' => '30',
				'40' => '40',
				'50' => '50',
				'60' => '60',
			),
			'std' => 'none',
			'heading' => __( 'Autoplay', 'rl_conductor' ),
			'description' => __( 'Select auto rotate for tour in seconds (Note: disabled by default).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'param_name' => 'active_section',
			'heading' => __( 'Active section', 'rl_conductor' ),
			'value' => 1,
			'description' => __( 'Enter active section number (Note: to have all sections closed on initial load enter non-existing number).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'pagination_style',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Square Dots', 'rl_conductor' ) => 'outline-square',
				__( 'Radio Dots', 'rl_conductor' ) => 'outline-round',
				__( 'Point Dots', 'rl_conductor' ) => 'flat-round',
				__( 'Fill Square Dots', 'rl_conductor' ) => 'flat-square',
				__( 'Rounded Fill Square Dots', 'rl_conductor' ) => 'flat-rounded',
			),
			'heading' => __( 'Pagination style', 'rl_conductor' ),
			'description' => __( 'Select pagination style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'pagination_color',
			'value' => getVcShared( 'colors-dashed' ),
			'heading' => __( 'Pagination color', 'rl_conductor' ),
			'description' => __( 'Select pagination color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
			'std' => 'grey',
			'dependency' => array(
				'element' => 'pagination_style',
				'not_empty' => true,
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
			'description' => __( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'rl_conductor' ),
		),
		array(
			'type' => 'css_editor',
			'heading' => __( 'CSS box', 'rl_conductor' ),
			'param_name' => 'css',
			'group' => __( 'Design Options', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcBackendTtaTourView',
	'custom_markup' => '
<div class="vc_tta-container" data-vc-action="collapse">
	<div class="vc_general vc_tta vc_tta-tabs vc_tta-color-backend-tabs-white vc_tta-style-flat vc_tta-shape-rounded vc_tta-spacing-1 vc_tta-tabs-position-left vc_tta-controls-align-left">
		<div class="vc_tta-tabs-container">'
	                   . '<ul class="vc_tta-tabs-list">'
	                   . '<li class="vc_tta-tab" data-vc-tab data-vc-target-model-id="{{ model_id }}"><a href="javascript:;" data-vc-container=".vc_tta" data-vc-target="[data-model-id=\'{{ model_id }}\']" data-vc-target-model-id="{{ model_id }}" data-vc-tabs>{{ section_title }}</a></li>'
	                   . '</ul>
		</div>
		<div class="vc_tta-panels {{container-class}}">
		  {{ content }}
		</div>
	</div>
</div>',
	'default_content' => '
[vc_tta_section title="' . sprintf( '%s %d', __( 'Section', 'rl_conductor' ), 1 ) . '"][/vc_tta_section]
[vc_tta_section title="' . sprintf( '%s %d', __( 'Section', 'rl_conductor' ), 2 ) . '"][/vc_tta_section]
	',
	'admin_enqueue_js' => array(
		vc_asset_url( 'lib/vc_tabs/vc-tabs.min.js' ),
	),
);
