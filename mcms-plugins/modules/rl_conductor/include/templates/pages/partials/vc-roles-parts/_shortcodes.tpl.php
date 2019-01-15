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
	'capabilities' => MCMSBMap::getSortedAllShortCodes(),
	'ignore_capabilities' => array(
		'vc_gitem',
		'vc_gitem_animated_block',
		'vc_gitem_zone',
		'vc_gitem_zone_a',
		'vc_gitem_zone_b',
		'vc_gitem_zone_c',
		'vc_column',
		'vc_row_inner',
		'vc_column_inner',
		'vc_posts_grid',
	),
	'categories' => MCMSBMap::getCategories(),
	'cap_types' => array(
		array( 'all', __( 'All', 'rl_conductor' ) ),
		array( 'edit', __( 'Edit', 'rl_conductor' ) ),
	),
	'item_header_name' => __( 'Element', 'rl_conductor' ),
	'options' => array(
		array( true, __( 'All', 'rl_conductor' ) ),
		array( 'edit', __( 'Edit only', 'rl_conductor' ) ),
		array( 'custom', __( 'Custom', 'rl_conductor' ) ),
	),
	'main_label' => __( 'Elements', 'rl_conductor' ),
	'custom_label' => __( 'Elements', 'rl_conductor' ),
	'description' => __( 'Control user access to content elements.', 'rl_conductor' ),
	'use_table' => true,
) );
