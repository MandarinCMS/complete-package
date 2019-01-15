<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to add gravity forms shortcode into visual composer
 */
add_action( 'modules_loaded', 'vc_init_vendor_gravity_forms' );
function vc_init_vendor_gravity_forms() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'gravityforms/gravityforms.php' ) || class_exists( 'RGForms' ) || class_exists( 'RGFormsModel' ) ) {
		// Call on map
		add_action( 'vc_after_init', 'vc_vendor_gravityforms_load' );
	} // if gravityforms active
}

function vc_vendor_gravityforms_load() {
	$gravity_forms_array[ __( 'No Gravity forms found.', 'rl_conductor' ) ] = '';
	if ( class_exists( 'RGFormsModel' ) ) {
		$gravity_forms = RGFormsModel::get_forms( 1, 'title' );
		if ( $gravity_forms ) {
			$gravity_forms_array = array( __( 'Select a form to display.', 'rl_conductor' ) => '' );
			foreach ( $gravity_forms as $gravity_form ) {
				$gravity_forms_array[ $gravity_form->title ] = $gravity_form->id;
			}
		}
	}
	vc_map( array(
		'name' => __( 'Gravity Form', 'rl_conductor' ),
		'base' => 'gravityform',
		'icon' => 'icon-mcmsb-vc_gravityform',
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Place Gravity form', 'rl_conductor' ),
		'params' => array(
			array(
				'type' => 'dropdown',
				'heading' => __( 'Form', 'rl_conductor' ),
				'param_name' => 'id',
				'value' => $gravity_forms_array,
				'save_always' => true,
				'description' => __( 'Select a form to add it to your post or page.', 'rl_conductor' ),
				'admin_label' => true,
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Display Form Title', 'rl_conductor' ),
				'param_name' => 'title',
				'value' => array(
					__( 'No', 'rl_conductor' ) => 'false',
					__( 'Yes', 'rl_conductor' ) => 'true',
				),
				'save_always' => true,
				'description' => __( 'Would you like to display the forms title?', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'id',
					'not_empty' => true,
				),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Display Form Description', 'rl_conductor' ),
				'param_name' => 'description',
				'value' => array(
					__( 'No', 'rl_conductor' ) => 'false',
					__( 'Yes', 'rl_conductor' ) => 'true',
				),
				'save_always' => true,
				'description' => __( 'Would you like to display the forms description?', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'id',
					'not_empty' => true,
				),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Enable AJAX?', 'rl_conductor' ),
				'param_name' => 'ajax',
				'value' => array(
					__( 'No', 'rl_conductor' ) => 'false',
					__( 'Yes', 'rl_conductor' ) => 'true',
				),
				'save_always' => true,
				'description' => __( 'Enable AJAX submission?', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'id',
					'not_empty' => true,
				),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Tab Index', 'rl_conductor' ),
				'param_name' => 'tabindex',
				'description' => __( '(Optional) Specify the starting tab index for the fields of this form. Leave blank if you\'re not sure what this is.',
				'rl_conductor' ),
				'dependency' => array(
					'element' => 'id',
					'not_empty' => true,
				),
			),
		),
	) );
}
