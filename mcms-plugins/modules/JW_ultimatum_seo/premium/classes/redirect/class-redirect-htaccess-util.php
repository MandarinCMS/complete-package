<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Redirect_Htaccess
 */
class MCMSSEO_Redirect_Htaccess_Util {

	/**
	 * Clear the MCMSSEO added entries added in the .htaccess file
	 */
	public static function clear_htaccess_entries() {

		$htaccess = '';
		if ( file_exists( self::get_htaccess_file_path() ) ) {
			$htaccess = file_get_contents( self::get_htaccess_file_path() );
		}

		$cleaned = preg_replace( '`# BEGIN YOAST REDIRECTS.*# END YOAST REDIRECTS' . PHP_EOL . '`is', '', $htaccess );
		// If nothing changed, don't even try to save it.
		if ( $cleaned === $htaccess ) {
			return;
		}

		MCMSSEO_Redirect_File_Util::write_file( self::get_htaccess_file_path(), $cleaned );
	}

	/**
	 * Get the full path to the .htaccess file
	 *
	 * @return string
	 */
	public static function get_htaccess_file_path() {
		return get_home_path() . '.htaccess';
	}
}
