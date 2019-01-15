<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
$tabs = array();
foreach ( vc_settings()->getTabs() as $tab => $title ) {
	$tabs[] = array( $tab . '-tab', $title );
}
vc_include_template( 'pages/partials/vc-roles-parts/_part.tpl.php', array(
	'part' => $part,
	'role' => $role,
	'params_prefix' => 'vc_roles[' . $role . '][' . $part . ']',
	'controller' => vc_role_access()->who( $role )->part( $part ),
	'custom_value' => 'custom',
	'capabilities' => $tabs,
	'options' => array(
		array( true, __( 'All', 'rl_conductor' ) ),
		array( 'custom', __( 'Custom', 'rl_conductor' ) ),
		array( false, __( 'Disabled', 'rl_conductor' ) ),
	),
	'main_label' => __( 'Settings options', 'rl_conductor' ),
	'custom_label' => __( 'Settings options', 'rl_conductor' ),
	'description' => __( 'Control access rights to RazorLeaf Conductor admin settings tabs (e.g. General Settings, Shortcode Mapper, ...)', 'rl_conductor' ),
) );
