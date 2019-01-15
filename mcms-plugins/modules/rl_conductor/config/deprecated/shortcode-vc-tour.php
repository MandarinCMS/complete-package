<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Tour', 'rl_conductor' ),
	'base' => 'vc_tour',
	'show_settings_on_create' => false,
	'is_container' => true,
	'container_not_allowed' => true,
	'deprecated' => '4.6',
	'icon' => 'icon-mcmsb-ui-tab-content-vertical',
	'category' => __( 'Content', 'rl_conductor' ),
	'wrapper_class' => 'vc_clearfix',
	'description' => __( 'Vertical tabbed content', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Auto rotate slides', 'rl_conductor' ),
			'param_name' => 'interval',
			'value' => array(
				__( 'Disable', 'rl_conductor' ) => 0,
				3,
				5,
				10,
				15,
			),
			'std' => 0,
			'description' => __( 'Auto rotate slides each X seconds.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
	'custom_markup' => '
<div class="mcmsb_tabs_holder mcmsb_holder vc_clearfix vc_container_for_children">
<ul class="tabs_controls">
</ul>
%content%
</div>',
	'default_content' => '
[vc_tab title="' . __( 'Tab 1', 'rl_conductor' ) . '" tab_id=""][/vc_tab]
[vc_tab title="' . __( 'Tab 2', 'rl_conductor' ) . '" tab_id=""][/vc_tab]
',
	'js_view' => 'VcTabsView',
);
