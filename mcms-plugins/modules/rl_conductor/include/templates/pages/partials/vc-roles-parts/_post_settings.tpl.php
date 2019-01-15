<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
vc_include_template( 'pages/partials/vc-roles-parts/_part.tpl.php', array(
	'part' => $part,
	'role' => $role,
	'params_prefix' => 'vc_roles[' . $role . '][' . $part . ']',
	'controller' => vc_role_access()->who( $role )->part( $part ),
	'options' => array(
		array( true, __( 'Enabled', 'rl_conductor' ) ),
		array( false, __( 'Disabled', 'rl_conductor' ) ),
	),
	'main_label' => __( 'Page settings', 'rl_conductor' ),
	'description' => __( 'Control access to RazorLeaf Conductor page settings. Note: Disable page settings to restrict editing of Custom CSS through page.', 'rl_conductor' ),
) );
