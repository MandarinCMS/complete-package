<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Interface PUM_Interface_Provider
 *
 * @since 1.7.0
 */
interface PUM_Interface_Provider {

	/**
	 * Determines whether to load this providers fields in the shortcode editor among other things.
	 *
	 * @return bool
	 */
	public function enabled();


	/**
	 * Contains each providers unique global settings.
	 *
	 * @return array
	 */
	public function register_settings();


	/**
	 * Contains each providers unique global settings tab sections..
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function register_settings_tab_section( $sections = array() );


	/**
	 * Creates the inputs for each of the needed extra fields for the email provider
	 *
	 * @param $shortcode_atts
	 */
	public function render_fields( $shortcode_atts );

	/**
	 * Allows processing of form value sanitization.
	 *
	 * @param array $values
	 *
	 * @return array $values
	 */
	public function form_sanitization( $values = array() );

	/**
	 * Allows processing of form value validation.
	 *
	 * @param MCMS_Error $errors
	 * @param array $values
	 *
	 * @return MCMS_Error
	 */
	public function form_validation( MCMS_Error $errors, $values = array() );

	/**
	 * Subscribes the user to the list.
	 *
	 * @param $values
	 * @param array $json_response
	 * @param MCMS_Error $errors
	 */
	public function form_submission( $values, &$json_response, MCMS_Error &$errors );

}