<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirect
 */

/**
 * Validate interface for the validation classes.
 */
interface MCMSSEO_Redirect_Validation {

	/**
	 * Validate the redirect to check if the origin already exists.
	 *
	 * @param MCMSSEO_Redirect $redirect     The redirect to validate.
	 * @param MCMSSEO_Redirect $old_redirect The old redirect to compare.
	 * @param array          $redirects    Array with redirect to validate against.
	 *
	 * @return bool
	 */
	public function run( MCMSSEO_Redirect $redirect, MCMSSEO_Redirect $old_redirect = null, array $redirects = null );

	/**
	 * Getting the validation error.
	 *
	 * @return string|boolean
	 */
	public function get_error();

}
