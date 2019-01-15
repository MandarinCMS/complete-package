<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Accordion', 'rl_conductor' ),
	'base' => 'vc_accordion',
	'show_settings_on_create' => false,
	'is_container' => true,
	'icon' => 'icon-mcmsb-ui-accordion',
	'deprecated' => '4.6',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Collapsible content panels', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Active section', 'rl_conductor' ),
			'param_name' => 'active_tab',
			'value' => 1,
			'description' => __( 'Enter section number to be active on load or enter "false" to collapse all sections.', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Allow collapse all sections?', 'rl_conductor' ),
			'param_name' => 'collapsible',
			'description' => __( 'If checked, it is allowed to collapse all sections.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Disable keyboard interactions?', 'rl_conductor' ),
			'param_name' => 'disable_keyboard',
			'description' => __( 'If checked, disables keyboard arrow interactions (Keys: Left, Up, Right, Down, Space).', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
	'custom_markup' => '
<div class="mcmsb_accordion_holder mcmsb_holder clearfix vc_container_for_children">
%content%
</div>
<div class="tab_controls">
    <a class="add_tab" title="' . __( 'Add section', 'rl_conductor' ) . '"><span class="vc_icon"></span> <span class="tab-label">' . __( 'Add section', 'rl_conductor' ) . '</span></a>
</div>
',
	'default_content' => '
    [vc_accordion_tab title="' . __( 'Section 1', 'rl_conductor' ) . '"][/vc_accordion_tab]
    [vc_accordion_tab title="' . __( 'Section 2', 'rl_conductor' ) . '"][/vc_accordion_tab]
',
	'js_view' => 'VcAccordionView',
);
