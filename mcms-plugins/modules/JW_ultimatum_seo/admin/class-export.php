<?php
/**
 * @package MCMSSEO\Admin\Export
 */

/**
 * Class MCMSSEO_Export
 *
 * Class with functionality to export the MCMS SEO settings
 */
class MCMSSEO_Export {

	const ZIP_FILENAME = 'ultimatum-seo-settings-export.zip';
	const INI_FILENAME = 'settings.ini';

	const NONCE_ACTION = 'mcmsseo_export';
	const NONCE_NAME   = 'mcmsseo_export_nonce';

	/**
	 * @var string
	 */
	private $export = '';

	/**
	 * @var string
	 */
	private $error = '';

	/**
	 * @var string
	 */
	public $export_zip_url = '';

	/**
	 * @var boolean
	 */
	public $success;

	/**
	 * Whether or not the export will include taxonomy metadata
	 *
	 * @var boolean
	 */
	private $include_taxonomy;

	/**
	 * @var array
	 */
	private $dir = array();


	/**
	 * Class constructor
	 *
	 * @param boolean $include_taxonomy Whether to include the taxonomy metadata the module creates.
	 */
	public function __construct( $include_taxonomy = false ) {
		$this->include_taxonomy = $include_taxonomy;
		$this->dir              = mcms_upload_dir();

		$this->export_settings();
	}

	/**
	 * Returns true when the property error has a value.
	 *
	 * @return bool
	 */
	public function has_error() {
		return ( $this->error !== '' );
	}

	/**
	 * Sets the error hook, to display the error to the user.
	 */
	public function set_error_hook() {
		$class   = 'notice notice-error';
		$message = sprintf( __( 'Error creating %1$s export: ', 'mandarincms-seo' ), 'Ultimatum SEO' ) . $this->error;

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}

	/**
	 * Exports the current site's MCMS SEO settings.
	 */
	private function export_settings() {

		$this->export_header();

		foreach ( MCMSSEO_Options::get_option_names() as $opt_group ) {
			$this->write_opt_group( $opt_group );
		}

		$this->taxonomy_metadata();

		if ( ! $this->write_settings_file() ) {
			$this->error = __( 'Could not write settings to file.', 'mandarincms-seo' );

			return;
		}

		if ( $this->zip_file() ) {
			// Just exit, because there is a download being served.
			exit;
		}
	}

	/**
	 * Writes the header of the export file.
	 */
	private function export_header() {
		/* translators: %1$s expands to Ultimatum SEO */
		$this->write_line( '; ' . sprintf( __( 'This is a settings export file for the %1$s module by Ultimatum.com', 'mandarincms-seo' ), 'Ultimatum SEO' ) . ' - https://jiiworks.net/mandarincms/modules/seo/' );
		if ( $this->include_taxonomy ) {
			$this->write_line( '; ' . __( 'This export includes taxonomy metadata', 'mandarincms-seo' ) );
		}
	}

	/**
	 * Writes a line to the export
	 *
	 * @param string  $line          Line string.
	 * @param boolean $newline_first Boolean flag whether to prepend with new line.
	 */
	private function write_line( $line, $newline_first = false ) {
		if ( $newline_first ) {
			$this->export .= PHP_EOL;
		}
		$this->export .= $line . PHP_EOL;
	}

	/**
	 * Writes an entire option group to the export
	 *
	 * @param string $opt_group Option group name.
	 */
	private function write_opt_group( $opt_group ) {

		$this->write_line( '[' . $opt_group . ']', true );

		$options = get_option( $opt_group );

		if ( ! is_array( $options ) ) {
			return;
		}

		foreach ( $options as $key => $elem ) {
			if ( is_array( $elem ) ) {
				for ( $i = 0; $i < count( $elem ); $i ++ ) {
					$this->write_setting( $key . '[]', $elem[ $i ] );
				}
			}
			else {
				$this->write_setting( $key, $elem );
			}
		}
	}

	/**
	 * Writes a settings line to the export
	 *
	 * @param string $key Key string.
	 * @param string $val Value string.
	 */
	private function write_setting( $key, $val ) {
		if ( is_string( $val ) ) {
			$val = '"' . $val . '"';
		}
		$this->write_line( $key . ' = ' . $val );
	}

	/**
	 * Adds the taxonomy meta data if there is any
	 */
	private function taxonomy_metadata() {
		if ( $this->include_taxonomy ) {
			$taxonomy_meta = get_option( 'mcmsseo_taxonomy_meta' );
			if ( is_array( $taxonomy_meta ) ) {
				$this->write_line( '[mcmsseo_taxonomy_meta]', true );
				$this->write_setting( 'mcmsseo_taxonomy_meta', urlencode( mcms_json_encode( $taxonomy_meta ) ) );
			}
			else {
				$this->write_line( '; ' . __( 'No taxonomy metadata found', 'mandarincms-seo' ), true );
			}
		}
	}

	/**
	 * Writes the settings to our temporary settings.ini file
	 *
	 * @return boolean unsigned
	 */
	private function write_settings_file() {
		$handle = fopen( $this->dir['path'] . '/' . self::INI_FILENAME, 'w' );
		if ( ! $handle ) {
			return false;
		}

		$res = fwrite( $handle, $this->export );
		if ( ! $res ) {
			return false;
		}

		fclose( $handle );

		return true;
	}

	/**
	 * Zips the settings ini file
	 *
	 * @return bool|null
	 */
	private function zip_file() {
		$is_zip_created = $this->create_zip();

		// The settings.ini isn't needed, because it's in the zipfile.
		$this->remove_settings_ini();

		if ( ! $is_zip_created ) {
			$this->error = __( 'Could not zip settings-file.', 'mandarincms-seo' );

			return false;
		}

		$this->serve_settings_export();
		$this->remove_zip();

		return true;
	}

	/**
	 * Creates the zipfile and returns true if it created successful.
	 *
	 * @return bool
	 */
	private function create_zip() {
		chdir( $this->dir['path'] );
		$zip = new PclZip( './' . self::ZIP_FILENAME );
		if ( 0 === $zip->create( './' . self::INI_FILENAME ) ) {
			return false;
		}

		return file_exists( self::ZIP_FILENAME );
	}

	/**
	 * Downloads the zip file.
	 */
	private function serve_settings_export() {
		// Clean any content that has been already output. For example by other modules or faulty PHP files.
		if ( ob_get_contents() ) {
			ob_clean();
		}
		header( 'Content-Type: application/octet-stream; charset=utf-8' );
		header( 'Content-Transfer-Encoding: Binary' );
		header( 'Content-Disposition: attachment; filename=' . self::ZIP_FILENAME );
		header( 'Content-Length: ' . filesize( self::ZIP_FILENAME ) );

		readfile( self::ZIP_FILENAME );
	}

	/**
	 * Removes the settings ini file.
	 */
	private function remove_settings_ini() {
		unlink( './' . self::INI_FILENAME );
	}

	/**
	 * Removes the files because they are already downloaded.
	 */
	private function remove_zip() {
		unlink( './' . self::ZIP_FILENAME );
	}
}
