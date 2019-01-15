<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
vc_include_template( 'pages/partials/vc-roles-parts/_part.tpl.php', array(
	'part' => $part,
	'role' => $role,
	'params_prefix' => 'vc_roles[' . $role . '][' . $part . ']',
	'controller' => vc_role_access()->who( $role )->part( $part ),
	'custom_value' => 'custom',

	'capabilities' => $vc_role->getPostTypes(),
	'options' => array(
		array( true, __( 'Pages only', 'rl_conductor' ) ),
		array( 'custom', __( 'Custom', 'rl_conductor' ) ),
		array( false, __( 'Disabled', 'rl_conductor' ) ),
	),
	'main_label' => __( 'Post types', 'rl_conductor' ),
	'custom_label' => __( 'Post types', 'rl_conductor' ),
	'description' => __( 'Enable RazorLeaf Conductor for pages, posts and custom post types. Note: By default RazorLeaf Conductor is available for pages only.', 'rl_conductor' ),
) );
