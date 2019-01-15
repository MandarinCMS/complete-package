<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

$groups = function_exists( 'acf_get_field_groups' ) ? acf_get_field_groups() : apply_filters( 'acf/get_field_groups', array() );
$groups_param_values = $fields_params = array();
foreach ( $groups as $group ) {
	$id = isset( $group['id'] ) ? 'id' : ( isset( $group['ID'] ) ? 'ID' : 'id' );
	$groups_param_values[ $group['title'] ] = $group[ $id ];
	$fields = function_exists( 'acf_get_fields' ) ? acf_get_fields( $group[ $id ] ) : apply_filters( 'acf/field_group/get_fields', array(), $group[ $id ] );
	$fields_param_value = array();
	foreach ( (array) $fields as $field ) {
		$fields_param_value[ $field['label'] ] = (string) $field['key'];
	}
	$fields_params[] = array(
		'type' => 'dropdown',
		'heading' => __( 'Field name', 'rl_conductor' ),
		'param_name' => 'field_from_' . $group[ $id ],
		'value' => $fields_param_value,
		'save_always' => true,
		'description' => __( 'Select field from group.', 'rl_conductor' ),
		'dependency' => array(
			'element' => 'field_group',
			'value' => array( (string) $group[ $id ] ),
		),
	);
}

return array(
	'vc_gitem_acf' => array(
		'name' => __( 'Advanced Custom Field', 'rl_conductor' ),
		'base' => 'vc_gitem_acf',
		'icon' => 'vc_icon-acf',
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Advanced Custom Field', 'rl_conductor' ),
		'php_class_name' => 'Vc_Gitem_Acf_Shortcode',
		'params' => array_merge(
			array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Field group', 'rl_conductor' ),
					'param_name' => 'field_group',
					'value' => $groups_param_values,
					'save_always' => true,
					'description' => __( 'Select field group.', 'rl_conductor' ),
				),
			), $fields_params,
			array(
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show label', 'rl_conductor' ),
					'param_name' => 'show_label',
					'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
					'description' => __( 'Enter label to display before key value.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Align', 'rl_conductor' ),
					'param_name' => 'align',
					'value' => array(
						__( 'left', 'rl_conductor' ) => 'left',
						__( 'right', 'rl_conductor' ) => 'right',
						__( 'center', 'rl_conductor' ) => 'center',
						__( 'justify', 'rl_conductor' ) => 'justify',
					),
					'description' => __( 'Select alignment.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
			)
		),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
);
