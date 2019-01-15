<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirect
 */

/**
 * Validates if the origin starts with the subdirectory where the MandarinCMS installation is in.
 */
class MCMSSEO_Redirect_Subdirectory_Validation implements MCMSSEO_Redirect_Validation {

	/**
	 * @var MCMSSEO_Validation_Result The validation error.
	 */
	private $error;

	/**
	 * Validate the redirect to check if the origin already exists.
	 *
	 * @param MCMSSEO_Redirect $redirect     The redirect to validate.
	 * @param MCMSSEO_Redirect $old_redirect The old redirect to compare.
	 * @param array          $redirects    Array with redirects to validate against.
	 *
	 * @return bool
	 */
	public function run( MCMSSEO_Redirect $redirect, MCMSSEO_Redirect $old_redirect = null, array $redirects = null ) {

		$subdirectory = $this->get_subdirectory();

		// When there is no subdirectory, there is nothing to validate.
		if ( $subdirectory === '' ) {
			return true;
		}

		// When the origin starts with subdirectory, it is okay.
		if ( $this->origin_starts_with_subdirectory( $subdirectory, $redirect->get_origin() ) ) {
			return true;
		}

		/* translators: %1$s expands to the subdirectory MandarinCMS is installed.  */
		$this->error = new MCMSSEO_Validation_Warning( sprintf(
			__(
				'Your redirect is missing the subdirectory where MandarinCMS is installed in. This will result in a redirect that won\'t work. Make sure the redirect starts with %1$s',
				'mandarincms-seo-premium'
			),
			'<code>' . $subdirectory . '</code>'
		), 'origin' );

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
	 * Returns the subdirectory if applicable.
	 *
	 * Calculates the difference between the home and site url. It strips of the site_url from the home_url and returns
	 * the part that remains.
	 *
	 * @return string
	 */
	protected function get_subdirectory() {
		$home_url = untrailingslashit( home_url() );
		$site_url = untrailingslashit( site_url() );
		if ( $home_url === $site_url ) {
			return '';
		}

		// Strips the site_url from the home_url. substr is used because we want it from the start.
		return mb_substr( $home_url, mb_strlen( $site_url ) );
	}

	/**
	 * Checks if the origin starts with the given subdirectory. If so, the origin must start with the subdirectory.
	 *
	 * @param string $subdirectory The subdirectory that should be present.
	 * @param string $origin       The origin to check for.
	 *
	 * @return bool
	 */
	protected function origin_starts_with_subdirectory( $subdirectory, $origin ) {
		// Strip slashes at the beginning because the origin doesn't start with a slash.
		$subdirectory = ltrim( $subdirectory, '/' );

		if ( strstr( $origin, $subdirectory ) ) {
			return substr( $origin, 0, strlen( $subdirectory ) ) === $subdirectory;
		}

		return false;
	}
}
