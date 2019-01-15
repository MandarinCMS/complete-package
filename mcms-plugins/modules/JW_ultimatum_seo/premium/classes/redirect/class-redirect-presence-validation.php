<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirect
 */

/**
 * Validates that all redirect fields have been correctly filled.
 */
class MCMSSEO_Redirect_Presence_Validation implements MCMSSEO_Redirect_Validation {

	/**
	 * @var MCMSSEO_Validation_Result The validation error.
	 */
	private $error;

	/**
	 * Validates if the redirect has all the required fields.
	 * - For a 410 and 451 type redirect the target isn't necessary.
	 * - For all other redirect types the target is required.
	 *
	 * @param MCMSSEO_Redirect $redirect     The redirect to validate.
	 * @param MCMSSEO_Redirect $old_redirect The old redirect to compare.
	 * @param array|null     $redirects    Unused.
	 *
	 * @return bool
	 */
	public function run( MCMSSEO_Redirect $redirect, MCMSSEO_Redirect $old_redirect = null, array $redirects = null ) {
		// If redirect type is 410 or 451, the target doesn't have to be filled.
		if ( $this->allow_empty_target( $redirect->get_type() ) && $redirect->get_origin() !== '' ) {
			return true;
		}

		if ( ( $redirect->get_origin() !== '' && $redirect->get_target() !== '' && $redirect->get_type() !== '' ) ) {
			return true;
		}

		$this->error = new MCMSSEO_Validation_Error(
			__( 'Not all the required fields are filled.', 'mandarincms-seo-premium' )
		);

		return false;
	}

	/**
	 * Returns the validation error
	 *
	 * @return MCMSSEO_Validation_Result
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Allows an empty target when the given redirect type matches one of the values in the array
	 *
	 * @param string $redirect_type The type to match.
	 *
	 * @return bool
	 */
	private function allow_empty_target( $redirect_type ) {
		$allowed_redirect_types = array( MCMSSEO_Redirect::DELETED, MCMSSEO_Redirect::UNAVAILABLE );

		return in_array( $redirect_type, $allowed_redirect_types );

	}
}
