<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirects
 */

/**
 * Saving the redirects from a single file into two smaller options files.
 */
class MCMSSEO_Redirect_Option_Exporter implements MCMSSEO_Redirect_Exporter {

	/**
	 * This method will split the redirects in separate arrays and store them in an option.
	 *
	 * @param MCMSSEO_Redirect[] $redirects The redirects to export.
	 *
	 * @return bool
	 */
	public function export( $redirects ) {
		$formatted_redirects = array(
			MCMSSEO_Redirect::FORMAT_PLAIN => array(),
			MCMSSEO_Redirect::FORMAT_REGEX => array(),
		);

		foreach ( $redirects as $redirect ) {
			$formatted_redirects[ $redirect->get_format() ][ $redirect->get_origin() ] = $this->format( $redirect );
		}

		// Save the plain redirects. No need to autoload, since the option is fetched straight from the DB.
		update_option( MCMSSEO_Redirect_Option::OPTION_PLAIN, $formatted_redirects[ MCMSSEO_Redirect::FORMAT_PLAIN ], 'no' );

		// Save the regex redirects. No need to autoload, since the option is fetched straight from the DB.
		update_option( MCMSSEO_Redirect_Option::OPTION_REGEX, $formatted_redirects[ MCMSSEO_Redirect::FORMAT_REGEX ], 'no' );

		return true;
	}

	/**
	 * Formats a redirect for use in the export.
	 *
	 * @param MCMSSEO_Redirect $redirect The redirect to format.
	 *
	 * @return mixed
	 */
	public function format( MCMSSEO_Redirect $redirect ) {
		return array(
			'url'  => $redirect->get_target(),
			'type' => $redirect->get_type(),
		);
	}
}
