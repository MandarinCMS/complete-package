<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirect
 */

/**
 * Validates the uniqueness of a redirect
 */
class MCMSSEO_Redirect_Uniqueness_Validation implements MCMSSEO_Redirect_Validation {

	/**
	 * @var MCMSSEO_Validation_Result
	 */
	private $error;

	/**
	 * Validates if the redirect already exists as a redirect.
	 *
	 * @param MCMSSEO_Redirect $redirect     The redirect to validate.
	 * @param MCMSSEO_Redirect $old_redirect The old redirect to compare.
	 * @param array          $redirects    Array with redirect to validate against.
	 *
	 * @return bool
	 */
	public function run( MCMSSEO_Redirect $redirect, MCMSSEO_Redirect $old_redirect = null, array $redirects = null ) {

		// Remove uniqueness validation when old origin is the same as the current one.
		if ( is_a( $old_redirect, 'MCMSSEO_Redirect' ) && $redirect->get_origin() === $old_redirect->get_origin() ) {
			return true;
		}

		if ( array_key_exists( $redirect->get_origin(), $redirects ) ) {
			$this->error = new MCMSSEO_Validation_Error(
				__( 'The old URL already exists as a redirect.', 'mandarincms-seo-premium' ),
				'origin'
			);

			return false;
		}

		return true;
	}

	/**
	 * Returns the validation error
	 *
	 * @return MCMSSEO_Validation_Result
	 */
	public function get_error() {
		return $this->error;
	}
}
