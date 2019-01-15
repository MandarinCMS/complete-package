<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Represents a redirect export
 */
interface MCMSSEO_Redirect_Exporter {

	/**
	 * Exports an array of redirects.
	 *
	 * @param MCMSSEO_Redirect[] $redirects The redirects to export.
	 */
	public function export( $redirects );

	/**
	 * Formats a redirect for use in the export.
	 *
	 * @param MCMSSEO_Redirect $redirect The redirect to format.
	 *
	 * @return mixed
	 */
	public function format( MCMSSEO_Redirect $redirect );

}
