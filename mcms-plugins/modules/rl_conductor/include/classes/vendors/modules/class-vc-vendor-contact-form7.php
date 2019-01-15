<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Contact form7 vendor
 * =======
 * Module Contact form 7 vendor
 * To fix issues when shortcode doesn't exists in frontend editor. #1053, #1054 etc.
 * @since 4.3
 */
class Vc_Vendor_ContactForm7 implements Vc_Vendor_Interface {

	/**
	 * Add action when contact form 7 is initialized to add shortcode.
	 * @since 4.3
	 */
	public function load() {

		vc_lean_map( 'contact-form-7',
			array(
				$this,
				'addShortcodeSettings',
			) );
	}

	/**
	 * Mapping settings for lean method.
	 *
	 * @since 4.9
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	public function addShortcodeSettings( $tag ) {
		/**
		 * Add Shortcode To RazorLeaf Conductor
		 */
		$cf7 = get_posts( 'post_type="mcmscf7_contact_form"&numberposts=-1' );

		$contact_forms = array();
		if ( $cf7 ) {
			foreach ( $cf7 as $cform ) {
				$contact_forms[ $cform->post_title ] = $cform->ID;
			}
		} else {
			$contact_forms[ __( 'No contact forms found', 'rl_conductor' ) ] = 0;
		}

		return array(
			'base' => $tag,
			'name' => __( 'Contact Form 7', 'rl_conductor' ),
			'icon' => 'icon-mcmsb-contactform7',
			'category' => __( 'Content', 'rl_conductor' ),
			'description' => __( 'Place Contact Form7', 'rl_conductor' ),
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Select contact form', 'rl_conductor' ),
					'param_name' => 'id',
					'value' => $contact_forms,
					'save_always' => true,
					'description' => __( 'Choose previously created contact form from the drop down list.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Search title', 'rl_conductor' ),
					'param_name' => 'title',
					'admin_label' => true,
					'description' => __( 'Enter optional title to search if no ID selected or cannot find by ID.', 'rl_conductor' ),
				),
			),
		);
	}
}
