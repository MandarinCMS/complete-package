<?php
/**
 * REST API: MCMS_REST_User_Meta_Fields class
 *
 * @package MandarinCMS
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * Core class used to manage meta values for users via the REST API.
 *
 * @since 4.7.0
 *
 * @see MCMS_REST_Meta_Fields
 */
class MCMS_REST_User_Meta_Fields extends MCMS_REST_Meta_Fields {

	/**
	 * Retrieves the object meta type.
	 *
	 * @since 4.7.0
	 *
	 * @return string The user meta type.
	 */
	protected function get_meta_type() {
		return 'user';
	}

	/**
	 * Retrieves the type for register_rest_field().
	 *
	 * @since 4.7.0
	 *
	 * @return string The user REST field type.
	 */
	public function get_rest_field_type() {
		return 'user';
	}
}
