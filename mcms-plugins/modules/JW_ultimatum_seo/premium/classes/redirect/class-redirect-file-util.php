<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Redirect_File_Manager
 */
class MCMSSEO_Redirect_File_Util {

	/**
	 * Get the full path to the MCMSSEO redirect directory
	 *
	 * @return string
	 */
	public static function get_dir() {
		$mcms_upload_dir = mcms_upload_dir();

		return $mcms_upload_dir['basedir'] . '/mcmsseo-redirects';
	}

	/**
	 * Get the full path to the redirect file
	 *
	 * @return string
	 */
	public static function get_file_path() {
		return self::get_dir() . '/.redirects';
	}

	/**
	 * Function that creates the MCMSSEO redirect directory
	 */
	public static function create_upload_dir() {
		$basedir = self::get_dir();

		// Create the Redirect file dir.
		if ( ! mcms_mkdir_p( $basedir ) ) {
			Ultimatum_Notification_Center::get()->add_notification(
				new Ultimatum_Notification(
				/* translators: %s expands to the file path that we tried to write to */
					sprintf( __( "We're unable to create the directory %s", 'mandarincms-seo-premium' ), $basedir ),
					array( 'type' => 'error' )
				)
			);

			return;
		}

		// Create the .htaccess file.
		if ( ! file_exists( $basedir . '/.htaccess' ) ) {
			self::write_file( $basedir . '/.htaccess', "Options -Indexes\ndeny from all" );
		}

		// Create an empty index.php file.
		if ( ! file_exists( $basedir . '/index.php' ) ) {
			self::write_file( $basedir . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Create an empty redirect file.
		if ( ! file_exists( self::get_file_path() ) ) {
			self::write_file( self::get_file_path(), '' );
		}
	}

	/**
	 * Wrapper method for file_put_contents. Catches the result, if result is false add notification.
	 *
	 * @param string $file_path    The path to write the content to.
	 * @param string $file_content The content that will be saved.
	 *
	 * @return int
	 */
	public static function write_file( $file_path, $file_content ) {
		$has_written = false;
		if ( is_writable( dirname( $file_path ) ) ) {
			$has_written = file_put_contents( $file_path, $file_content );
		}

		if ( $has_written === false ) {
			Ultimatum_Notification_Center::get()->add_notification(
				new Ultimatum_Notification(
				/* translators: %s expands to the file path that we tried to write to */
					sprintf( __( "We're unable to write data to the file %s", 'mandarincms-seo-premium' ), $file_path ),
					array( 'type' => 'error' )
				)
			);

			return false;
		}

		return true;
	}

	/**
	 * Getting the object which will save the redirects file
	 *
	 * @param string $separate_file Saving the redirects in an separate apache file.
	 *
	 * @return null|MCMSSEO_Redirect_File_Exporter
	 */
	public static function get_file_exporter( $separate_file ) {
		// Create the correct file object.
		if ( MCMSSEO_Utils::is_apache() ) {
			if ( 'on' === $separate_file ) {
				return new MCMSSEO_Redirect_Apache_Exporter();
			}

			return new MCMSSEO_Redirect_Htaccess_Exporter();
		}

		if ( MCMSSEO_Utils::is_nginx() ) {
			return new MCMSSEO_Redirect_Nginx_Exporter();
		}

		return null;
	}
}
