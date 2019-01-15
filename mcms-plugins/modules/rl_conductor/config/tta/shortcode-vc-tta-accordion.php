<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Accordion', 'rl_conductor' ),
	'base' => 'vc_tta_accordion',
	'icon' => 'icon-mcmsb-ui-accordion',
	'is_container' => true,
	'show_settings_on_create' => false,
	'as_parent' => array(
		'only' => 'vc_tta_section',
	),
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Collapsible content panels', 'rl_conductor' ),
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
			'description' => __( 'Select accordion display style.', 'rl_conductor' ),
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
			'description' => __( 'Select accordion shape.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'color',
			'value' => getVcShared( 'colors-dashed' ),
			'std' => 'grey',
			'heading' => __( 'Color', 'rl_conductor' ),
			'description' => __( 'Select accordion color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'checkbox',
			'param_name' => 'no_fill',
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
			'description' => __( 'Select accordion spacing.', 'rl_conductor' ),
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
			'description' => __( 'Select accordion gap.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'c_align',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
				__( 'Center', 'rl_conductor' ) => 'center',
			),
			'heading' => __( 'Alignment', 'rl_conductor' ),
			'description' => __( 'Select accordion section title alignment.', 'rl_conductor' ),
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
			'description' => __( 'Select auto rotate for accordion in seconds (Note: disabled by default).', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'param_name' => 'collapsible_all',
			'heading' => __( 'Allow collapse all?', 'rl_conductor' ),
			'description' => __( 'Allow collapse all accordion sections.', 'rl_conductor' ),
		),
		// Control Icons
		array(
			'type' => 'dropdown',
			'param_name' => 'c_icon',
			'value' => array(
				__( 'None', 'rl_conductor' ) => '',
				__( 'Chevron', 'rl_conductor' ) => 'chevron',
				__( 'Plus', 'rl_conductor' ) => 'plus',
				__( 'Triangle', 'rl_conductor' ) => 'triangle',
			),
			'std' => 'plus',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'description' => __( 'Select accordion navigation icon.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'param_name' => 'c_position',
			'value' => array(
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Right', 'rl_conductor' ) => 'right',
			),
			'dependency' => array(
				'element' => 'c_icon',
				'not_empty' => true,
			),
			'heading' => __( 'Position', 'rl_conductor' ),
			'description' => __( 'Select accordion navigation icon position.', 'rl_conductor' ),
		),
		// Control Icons END
		array(
			'type' => 'textfield',
			'param_name' => 'active_section',
			'heading' => __( 'Active section', 'rl_conductor' ),
			'value' => 1,
			'description' => __( 'Enter active section number (Note: to have all sections closed on initial load enter non-existing number).', 'rl_conductor' ),
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
	'js_view' => 'VcBackendTtaAccordionView',
	'custom_markup' => '
<div class="vc_tta-container" data-vc-action="collapseAll">
	<div class="vc_general vc_tta vc_tta-accordion vc_tta-color-backend-accordion-white vc_tta-style-flat vc_tta-shape-rounded vc_tta-o-shape-group vc_tta-controls-align-left vc_tta-gap-2">
	   <div class="vc_tta-panels vc_clearfix {{container-class}}">
	      {{ content }}
	      <div class="vc_tta-panel vc_tta-section-append">
	         <div class="vc_tta-panel-heading">
	            <h4 class="vc_tta-panel-title vc_tta-controls-icon-position-left">
	               <a href="javascript:;" aria-expanded="false" class="vc_tta-backend-add-control">
	                   <span class="vc_tta-title-text">' . __( 'Add Section', 'rl_conductor' ) . '</span>
	                    <i class="vc_tta-controls-icon vc_tta-controls-icon-plus"></i>
					</a>
	            </h4>
	         </div>
	      </div>
	   </div>
	</div>
</div>',
	'default_content' => '[vc_tta_section title="' . sprintf( '%s %d', __( 'Section', 'rl_conductor' ), 1 ) . '"][/vc_tta_section][vc_tta_section title="' . sprintf( '%s %d', __( 'Section', 'rl_conductor' ), 2 ) . '"][/vc_tta_section]',
);
