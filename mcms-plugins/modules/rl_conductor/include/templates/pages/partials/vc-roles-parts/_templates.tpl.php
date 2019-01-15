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
		array( true, __( 'All', 'rl_conductor' ) ),
		array( 'add', __( 'Apply templates only', 'rl_conductor' ) ),
		array( false, __( 'Disabled', 'rl_conductor' ) ),
	),
	'main_label' => __( 'Templates', 'rl_conductor' ),
	'description' => __( 'Control access rights to templates and predefined templates. Note: "Apply templates only" restricts users from saving new templates and deleting existing.', 'rl_conductor' ),
) );
