<?php
/**
 * @package MCMSSEO\Admin\Import
 */

/**
 * Class MCMSSEO_Import
 *
 * Class with functionality to import the Ultimatum SEO settings
 */
class MCMSSEO_Import {

	/**
	 * Message about the import
	 *
	 * @var string
	 */
	public $msg = '';

	/** @var bool $success If import was a success flag. */
	public $success = false;

	/**
	 * @var array
	 */
	private $file;

	/**
	 * @var string
	 */
	private $filename;

	/**
	 * @var string
	 */
	private $old_mcmsseo_version = null;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var array
	 */
	private $upload_dir;

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( ! $this->handle_upload() ) {
			return;
		}

		$this->determine_path();

		if ( ! $this->unzip_file() ) {
			$this->clean_up();

			return;
		}

		$this->parse_options();

		$this->clean_up();
	}

	/**
	 * Handle the file upload
	 *
	 * @return boolean
	 */
	private function handle_upload() {
		$overrides  = array( 'mimes' => array( 'zip' => 'application/zip' ) ); // Explicitly allow zip in multisite.
		$this->file = mcms_handle_upload( $_FILES['settings_import_file'], $overrides );

		if ( is_mcms_error( $this->file ) ) {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . $this->file->get_error_message();

			return false;
		}

		if ( is_array( $this->file ) && isset( $this->file['error'] ) ) {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . $this->file['error'];

			return false;
		}

		if ( ! isset( $this->file['file'] ) ) {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . __( 'Upload failed.', 'mandarincms-seo' );

			return false;
		}

		return true;
	}

	/**
	 * Determine the path to the import file
	 */
	private function determine_path() {
		$this->upload_dir = mcms_upload_dir();

		if ( ! defined( 'DIRECTORY_SEPARATOR' ) ) {
			define( 'DIRECTORY_SEPARATOR', '/' );
		}
		$this->path = $this->upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'mcmsseo-import' . DIRECTORY_SEPARATOR;

		if ( ! isset( $GLOBALS['mcms_filesystem'] ) || ! is_object( $GLOBALS['mcms_filesystem'] ) ) {
			MCMS_Filesystem();
		}
	}

	/**
	 * Unzip the file
	 *
	 * @return boolean
	 */
	private function unzip_file() {
		$unzipped = unzip_file( $this->file['file'], $this->path );
		if ( is_mcms_error( $unzipped ) ) {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . sprintf( __( 'Unzipping failed with error "%s".', 'mandarincms-seo' ), $unzipped->get_error_message() );

			return false;
		}

		$this->filename = $this->path . 'settings.ini';
		if ( ! is_file( $this->filename ) || ! is_readable( $this->filename ) ) {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . __( 'Unzipping failed - file settings.ini not found.', 'mandarincms-seo' );

			return false;
		}

		return true;
	}

	/**
	 * Parse the option file
	 */
	private function parse_options() {
		$options = parse_ini_file( $this->filename, true );

		if ( is_array( $options ) && $options !== array() ) {
			if ( isset( $options['mcmsseo']['version'] ) && $options['mcmsseo']['version'] !== '' ) {
				$this->old_mcmsseo_version = $options['mcmsseo']['version'];
			}
			foreach ( $options as $name => $opt_group ) {
				$this->parse_option_group( $name, $opt_group, $options );
			}
			$this->msg     = __( 'Settings successfully imported.', 'mandarincms-seo' );
			$this->success = true;
		}
		else {
			$this->msg = __( 'Settings could not be imported:', 'mandarincms-seo' ) . ' ' . __( 'No settings found in file.', 'mandarincms-seo' );
		}
	}

	/**
	 * Parse the option group and import it
	 *
	 * @param string $name      Name string.
	 * @param array  $opt_group Option group data.
	 * @param array  $options   Options data.
	 */
	private function parse_option_group( $name, $opt_group, $options ) {
		if ( $name === 'mcmsseo_taxonomy_meta' ) {
			$opt_group = json_decode( urldecode( $opt_group['mcmsseo_taxonomy_meta'] ), true );
		}

		// Make sure that the imported options are cleaned/converted on import.
		$option_instance = MCMSSEO_Options::get_option_instance( $name );
		if ( is_object( $option_instance ) && method_exists( $option_instance, 'import' ) ) {
			$option_instance->import( $opt_group, $this->old_mcmsseo_version, $options );
		}
		elseif ( MCMS_DEBUG === true || ( defined( 'MCMSSEO_DEBUG' ) && MCMSSEO_DEBUG === true ) ) {
			$this->msg = sprintf( __( 'Setting "%s" is no longer used and has been discarded.', 'mandarincms-seo' ), $name );
		}
	}

	/**
	 * Remove the files
	 */
	private function clean_up() {
		if ( file_exists( $this->filename ) && is_writable( $this->filename ) ) {
			unlink( $this->filename );
		}
		if ( ! empty( $this->file['file'] ) && file_exists( $this->file['file'] ) && is_writable( $this->file['file'] ) ) {
			unlink( $this->file['file'] );
		}
		if ( file_exists( $this->path ) && is_writable( $this->path ) ) {
			$mcms_file = new MCMS_Filesystem_Direct( $this->path );
			$mcms_file->rmdir( $this->path, true );
		}
	}
}
