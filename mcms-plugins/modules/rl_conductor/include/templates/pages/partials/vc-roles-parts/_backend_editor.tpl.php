<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
vc_include_template( 'pages/partials/vc-roles-parts/_part.tpl.php', array(
	'part' => $part,
	'role' => $role,
	'params_prefix' => 'vc_roles[' . $role . '][' . $part . ']',
	'controller' => vc_role_access()->who( $role )->part( $part ),
	// 'custom_value' => array(true, 'default'),
		'capabilities' => array(
			array( 'disabled_ce_editor', __( 'Disable Classic editor', 'rl_conductor' ) ),
		),
		'options' => array(
		array( true, __( 'Enabled', 'rl_conductor' ) ),
		array( 'default', __( 'Enabled and default', 'rl_conductor' ) ),
		array( false, __( 'Disabled', 'rl_conductor' ) ),
		),
		'main_label' => __( 'Backend editor', 'rl_conductor' ),
		'custom_label' => __( 'Backend editor', 'rl_conductor' ),
) );
