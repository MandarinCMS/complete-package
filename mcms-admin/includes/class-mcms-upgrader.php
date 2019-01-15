<?php
/**
 * Upgrade API: MCMS_Upgrader class
 *
 * Requires skin classes and MCMS_Upgrader subclasses for backward compatibility.
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 2.8.0
 */

/** MCMS_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader-skin.php';

/** Module_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-module-upgrader-skin.php';

/** MySkin_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-myskin-upgrader-skin.php';

/** Bulk_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-bulk-upgrader-skin.php';

/** Bulk_Module_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-bulk-module-upgrader-skin.php';

/** Bulk_MySkin_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-bulk-myskin-upgrader-skin.php';

/** Module_Installer_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-module-installer-skin.php';

/** MySkin_Installer_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-myskin-installer-skin.php';

/** Language_Pack_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-language-pack-upgrader-skin.php';

/** Automatic_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-automatic-upgrader-skin.php';

/** MCMS_Ajax_Upgrader_Skin class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-ajax-upgrader-skin.php';

/**
 * Core class used for upgrading/installing a local set of files via
 * the Filesystem Abstraction classes from a Zip file.
 *
 * @since 2.8.0
 */
class MCMS_Upgrader {

	/**
	 * The error/notification strings used to update the user on the progress.
	 *
	 * @since 2.8.0
	 * @var array $strings
	 */
	public $strings = array();

	/**
	 * The upgrader skin being used.
	 *
	 * @since 2.8.0
	 * @var Automatic_Upgrader_Skin|MCMS_Upgrader_Skin $skin
	 */
	public $skin = null;

	/**
	 * The result of the installation.
	 *
	 * This is set by MCMS_Upgrader::install_package(), only when the package is installed
	 * successfully. It will then be an array, unless a MCMS_Error is returned by the
	 * {@see 'upgrader_post_install'} filter. In that case, the MCMS_Error will be assigned to
	 * it.
	 *
	 * @since 2.8.0
	 *
	 * @var MCMS_Error|array $result {
	 *      @type string $source             The full path to the source the files were installed from.
	 *      @type string $source_files       List of all the files in the source directory.
	 *      @type string $destination        The full path to the installation destination folder.
	 *      @type string $destination_name   The name of the destination folder, or empty if `$destination`
	 *                                       and `$local_destination` are the same.
	 *      @type string $local_destination  The full local path to the destination folder. This is usually
	 *                                       the same as `$destination`.
	 *      @type string $remote_destination The full remote path to the destination folder
	 *                                       (i.e., from `$mcms_filesystem`).
	 *      @type bool   $clear_destination  Whether the destination folder was cleared.
	 * }
	 */
	public $result = array();

	/**
	 * The total number of updates being performed.
	 *
	 * Set by the bulk update methods.
	 *
	 * @since 3.0.0
	 * @var int $update_count
	 */
	public $update_count = 0;

	/**
	 * The current update if multiple updates are being performed.
	 *
	 * Used by the bulk update methods, and incremented for each update.
	 *
	 * @since 3.0.0
	 * @var int
	 */
	public $update_current = 0;

	/**
	 * Construct the upgrader with a skin.
	 *
	 * @since 2.8.0
	 *
	 * @param MCMS_Upgrader_Skin $skin The upgrader skin to use. Default is a MCMS_Upgrader_Skin.
	 *                               instance.
	 */
	public function __construct( $skin = null ) {
		if ( null == $skin )
			$this->skin = new MCMS_Upgrader_Skin();
		else
			$this->skin = $skin;
	}

	/**
	 * Initialize the upgrader.
	 *
	 * This will set the relationship between the skin being used and this upgrader,
	 * and also add the generic strings to `MCMS_Upgrader::$strings`.
	 *
	 * @since 2.8.0
	 */
	public function init() {
		$this->skin->set_upgrader($this);
		$this->generic_strings();
	}

	/**
	 * Add the generic strings to MCMS_Upgrader::$strings.
	 *
	 * @since 2.8.0
	 */
	public function generic_strings() {
		$this->strings['bad_request'] = __('Invalid data provided.');
		$this->strings['fs_unavailable'] = __('Could not access filesystem.');
		$this->strings['fs_error'] = __('Filesystem error.');
		$this->strings['fs_no_root_dir'] = __('Unable to locate MandarinCMS root directory.');
		$this->strings['fs_no_content_dir'] = __('Unable to locate MandarinCMS content directory (mcms-plugins).');
		$this->strings['fs_no_modules_dir'] = __('Unable to locate MandarinCMS module directory.');
		$this->strings['fs_no_myskins_dir'] = __('Unable to locate MandarinCMS myskin directory.');
		/* translators: %s: directory name */
		$this->strings['fs_no_folder'] = __('Unable to locate needed folder (%s).');

		$this->strings['download_failed'] = __('Download failed.');
		$this->strings['installing_package'] = __('Installing the latest version&#8230;');
		$this->strings['no_files'] = __('The package contains no files.');
		$this->strings['folder_exists'] = __('Destination folder already exists.');
		$this->strings['mkdir_failed'] = __('Could not create directory.');
		$this->strings['incompatible_archive'] = __('The package could not be installed.');
		$this->strings['files_not_writable'] = __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' );

		$this->strings['maintenance_start'] = __('Enabling Maintenance mode&#8230;');
		$this->strings['maintenance_end'] = __('Disabling Maintenance mode&#8230;');
	}

	/**
	 * Connect to the filesystem.
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param array $directories                  Optional. A list of directories. If any of these do
	 *                                            not exist, a MCMS_Error object will be returned.
	 *                                            Default empty array.
	 * @param bool  $allow_relaxed_file_ownership Whether to allow relaxed file ownership.
	 *                                            Default false.
	 * @return bool|MCMS_Error True if able to connect, false or a MCMS_Error otherwise.
	 */
	public function fs_connect( $directories = array(), $allow_relaxed_file_ownership = false ) {
		global $mcms_filesystem;

		if ( false === ( $credentials = $this->skin->request_filesystem_credentials( false, $directories[0], $allow_relaxed_file_ownership ) ) ) {
			return false;
		}

		if ( ! MCMS_Filesystem( $credentials, $directories[0], $allow_relaxed_file_ownership ) ) {
			$error = true;
			if ( is_object($mcms_filesystem) && $mcms_filesystem->errors->get_error_code() )
				$error = $mcms_filesystem->errors;
			// Failed to connect, Error and request again
			$this->skin->request_filesystem_credentials( $error, $directories[0], $allow_relaxed_file_ownership );
			return false;
		}

		if ( ! is_object($mcms_filesystem) )
			return new MCMS_Error('fs_unavailable', $this->strings['fs_unavailable'] );

		if ( is_mcms_error($mcms_filesystem->errors) && $mcms_filesystem->errors->get_error_code() )
			return new MCMS_Error('fs_error', $this->strings['fs_error'], $mcms_filesystem->errors);

		foreach ( (array)$directories as $dir ) {
			switch ( $dir ) {
				case BASED_TREE_URI:
					if ( ! $mcms_filesystem->abspath() )
						return new MCMS_Error('fs_no_root_dir', $this->strings['fs_no_root_dir']);
					break;
				case MCMS_CONTENT_DIR:
					if ( ! $mcms_filesystem->mcms_content_dir() )
						return new MCMS_Error('fs_no_content_dir', $this->strings['fs_no_content_dir']);
					break;
				case MCMS_PLUGIN_DIR:
					if ( ! $mcms_filesystem->mcms_modules_dir() )
						return new MCMS_Error('fs_no_modules_dir', $this->strings['fs_no_modules_dir']);
					break;
				case get_myskin_root():
					if ( ! $mcms_filesystem->mcms_myskins_dir() )
						return new MCMS_Error('fs_no_myskins_dir', $this->strings['fs_no_myskins_dir']);
					break;
				default:
					if ( ! $mcms_filesystem->find_folder($dir) )
						return new MCMS_Error( 'fs_no_folder', sprintf( $this->strings['fs_no_folder'], esc_html( basename( $dir ) ) ) );
					break;
			}
		}
		return true;
	} //end fs_connect();

	/**
	 * Download a package.
	 *
	 * @since 2.8.0
	 *
	 * @param string $package The URI of the package. If this is the full path to an
	 *                        existing local file, it will be returned untouched.
	 * @return string|MCMS_Error The full path to the downloaded package file, or a MCMS_Error object.
	 */
	public function download_package( $package ) {

		/**
		 * Filters whether to return the package.
		 *
		 * @since 3.7.0
		 *
		 * @param bool        $reply   Whether to bail without returning the package.
		 *                             Default false.
		 * @param string      $package The package file name.
		 * @param MCMS_Upgrader $this    The MCMS_Upgrader instance.
		 */
		$reply = apply_filters( 'upgrader_pre_download', false, $package, $this );
		if ( false !== $reply )
			return $reply;

		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new MCMS_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);

		$download_file = download_url($package);

		if ( is_mcms_error($download_file) )
			return new MCMS_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}

	/**
	 * Unpack a compressed package file.
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param string $package        Full path to the package file.
	 * @param bool   $delete_package Optional. Whether to delete the package file after attempting
	 *                               to unpack it. Default true.
	 * @return string|MCMS_Error The path to the unpacked contents, or a MCMS_Error on failure.
	 */
	public function unpack_package( $package, $delete_package = true ) {
		global $mcms_filesystem;

		$this->skin->feedback('unpack_package');

		$upgrade_folder = $mcms_filesystem->mcms_content_dir() . 'upgrade/';

		//Clean up contents of upgrade directory beforehand.
		$upgrade_files = $mcms_filesystem->dirlist($upgrade_folder);
		if ( !empty($upgrade_files) ) {
			foreach ( $upgrade_files as $file )
				$mcms_filesystem->delete($upgrade_folder . $file['name'], true);
		}

		// We need a working directory - Strip off any .tmp or .zip suffixes
		$working_dir = $upgrade_folder . basename( basename( $package, '.tmp' ), '.zip' );

		// Clean up working directory
		if ( $mcms_filesystem->is_dir($working_dir) )
			$mcms_filesystem->delete($working_dir, true);

		// Unzip package to working directory
		$result = unzip_file( $package, $working_dir );

		// Once extracted, delete the package if required.
		if ( $delete_package )
			unlink($package);

		if ( is_mcms_error($result) ) {
			$mcms_filesystem->delete($working_dir, true);
			if ( 'incompatible_archive' == $result->get_error_code() ) {
				return new MCMS_Error( 'incompatible_archive', $this->strings['incompatible_archive'], $result->get_error_data() );
			}
			return $result;
		}

		return $working_dir;
	}

	/**
	 * Flatten the results of MCMS_Filesystem::dirlist() for iterating over.
	 *
	 * @since 4.9.0
	 * @access protected
	 *
	 * @param  array  $nested_files  Array of files as returned by MCMS_Filesystem::dirlist()
	 * @param  string $path          Relative path to prepend to child nodes. Optional.
	 * @return array $files A flattened array of the $nested_files specified.
	 */
	protected function flatten_dirlist( $nested_files, $path = '' ) {
		$files = array();

		foreach ( $nested_files as $name => $details ) {
			$files[ $path . $name ] = $details;

			// Append children recursively
			if ( ! empty( $details['files'] ) ) {
				$children = $this->flatten_dirlist( $details['files'], $path . $name . '/' );

				// Merge keeping possible numeric keys, which array_merge() will reindex from 0..n
				$files = $files + $children;
			}
		}

		return $files;
	}

	/**
	 * Clears the directory where this item is going to be installed into.
	 *
	 * @since 4.3.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param string $remote_destination The location on the remote filesystem to be cleared
	 * @return bool|MCMS_Error True upon success, MCMS_Error on failure.
	 */
	public function clear_destination( $remote_destination ) {
		global $mcms_filesystem;

		$files = $mcms_filesystem->dirlist( $remote_destination, true, true );

		// False indicates that the $remote_destination doesn't exist.
		if ( false === $files ) {
			return true;
		}

		// Flatten the file list to iterate over
		$files = $this->flatten_dirlist( $files );

		// Check all files are writable before attempting to clear the destination.
		$unwritable_files = array();

		// Check writability.
		foreach ( $files as $filename => $file_details ) {
			if ( ! $mcms_filesystem->is_writable( $remote_destination . $filename ) ) {
				// Attempt to alter permissions to allow writes and try again.
				$mcms_filesystem->chmod( $remote_destination . $filename, ( 'd' == $file_details['type'] ? FS_CHMOD_DIR : FS_CHMOD_FILE ) );
				if ( ! $mcms_filesystem->is_writable( $remote_destination . $filename ) ) {
					$unwritable_files[] = $filename;
				}
			}
		}

		if ( ! empty( $unwritable_files ) ) {
			return new MCMS_Error( 'files_not_writable', $this->strings['files_not_writable'], implode( ', ', $unwritable_files ) );
		}

		if ( ! $mcms_filesystem->delete( $remote_destination, true ) ) {
			return new MCMS_Error( 'remove_old_failed', $this->strings['remove_old_failed'] );
		}

		return true;
	}

	/**
	 * Install a package.
	 *
	 * Copies the contents of a package form a source directory, and installs them in
	 * a destination directory. Optionally removes the source. It can also optionally
	 * clear out the destination folder if it already exists.
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 * @global array              $mcms_myskin_directories
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments for installing a package. Default empty array.
	 *
	 *     @type string $source                      Required path to the package source. Default empty.
	 *     @type string $destination                 Required path to a folder to install the package in.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the destination
	 *                                               folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files form the working directory
	 *                                               after copying to the destination. Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if
	 *                                               the destination folder already exists. Default true.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               MCMS_Upgrader::install_package(). Default empty array.
	 * }
	 *
	 * @return array|MCMS_Error The result (also stored in `MCMS_Upgrader::$result`), or a MCMS_Error on failure.
	 */
	public function install_package( $args = array() ) {
		global $mcms_filesystem, $mcms_myskin_directories;

		$defaults = array(
			'source' => '', // Please always pass this
			'destination' => '', // and this
			'clear_destination' => false,
			'clear_working' => false,
			'abort_if_destination_exists' => true,
			'hook_extra' => array()
		);

		$args = mcms_parse_args($args, $defaults);

		// These were previously extract()'d.
		$source = $args['source'];
		$destination = $args['destination'];
		$clear_destination = $args['clear_destination'];

		@set_time_limit( 300 );

		if ( empty( $source ) || empty( $destination ) ) {
			return new MCMS_Error( 'bad_request', $this->strings['bad_request'] );
		}
		$this->skin->feedback( 'installing_package' );

		/**
		 * Filters the install response before the installation has started.
		 *
		 * Returning a truthy value, or one that could be evaluated as a MCMS_Error
		 * will effectively short-circuit the installation, returning that value
		 * instead.
		 *
		 * @since 2.8.0
		 *
		 * @param bool|MCMS_Error $response   Response.
		 * @param array         $hook_extra Extra arguments passed to hooked filters.
		 */
		$res = apply_filters( 'upgrader_pre_install', true, $args['hook_extra'] );

		if ( is_mcms_error( $res ) ) {
			return $res;
		}

		//Retain the Original source and destinations
		$remote_source = $args['source'];
		$local_destination = $destination;

		$source_files = array_keys( $mcms_filesystem->dirlist( $remote_source ) );
		$remote_destination = $mcms_filesystem->find_folder( $local_destination );

		//Locate which directory to copy to the new folder, This is based on the actual folder holding the files.
		if ( 1 == count( $source_files ) && $mcms_filesystem->is_dir( trailingslashit( $args['source'] ) . $source_files[0] . '/' ) ) { //Only one folder? Then we want its contents.
			$source = trailingslashit( $args['source'] ) . trailingslashit( $source_files[0] );
		} elseif ( count( $source_files ) == 0 ) {
			return new MCMS_Error( 'incompatible_archive_empty', $this->strings['incompatible_archive'], $this->strings['no_files'] ); // There are no files?
		} else { // It's only a single file, the upgrader will use the folder name of this file as the destination folder. Folder name is based on zip filename.
			$source = trailingslashit( $args['source'] );
		}

		/**
		 * Filters the source file location for the upgrade package.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 The $hook_extra parameter became available.
		 *
		 * @param string      $source        File source location.
		 * @param string      $remote_source Remote file source location.
		 * @param MCMS_Upgrader $this          MCMS_Upgrader instance.
		 * @param array       $hook_extra    Extra arguments passed to hooked filters.
		 */
		$source = apply_filters( 'upgrader_source_selection', $source, $remote_source, $this, $args['hook_extra'] );

		if ( is_mcms_error( $source ) ) {
			return $source;
		}

		// Has the source location changed? If so, we need a new source_files list.
		if ( $source !== $remote_source ) {
			$source_files = array_keys( $mcms_filesystem->dirlist( $source ) );
		}

		/*
		 * Protection against deleting files in any important base directories.
		 * MySkin_Upgrader & Module_Upgrader also trigger this, as they pass the
		 * destination directory (MCMS_PLUGIN_DIR / mcms-plugins/myskins) intending
		 * to copy the directory into the directory, whilst they pass the source
		 * as the actual files to copy.
		 */
		$protected_directories = array( BASED_TREE_URI, MCMS_CONTENT_DIR, MCMS_PLUGIN_DIR, MCMS_CONTENT_DIR . '/myskins' );

		if ( is_array( $mcms_myskin_directories ) ) {
			$protected_directories = array_merge( $protected_directories, $mcms_myskin_directories );
		}

		if ( in_array( $destination, $protected_directories ) ) {
			$remote_destination = trailingslashit( $remote_destination ) . trailingslashit( basename( $source ) );
			$destination = trailingslashit( $destination ) . trailingslashit( basename( $source ) );
		}

		if ( $clear_destination ) {
			// We're going to clear the destination if there's something there.
			$this->skin->feedback('remove_old');

			$removed = $this->clear_destination( $remote_destination );

			/**
			 * Filters whether the upgrader cleared the destination.
			 *
			 * @since 2.8.0
			 *
			 * @param mixed  $removed            Whether the destination was cleared. true on success, MCMS_Error on failure
			 * @param string $local_destination  The local package destination.
			 * @param string $remote_destination The remote package destination.
			 * @param array  $hook_extra         Extra arguments passed to hooked filters.
			 */
			$removed = apply_filters( 'upgrader_clear_destination', $removed, $local_destination, $remote_destination, $args['hook_extra'] );

			if ( is_mcms_error( $removed ) ) {
				return $removed;
			}
		} elseif ( $args['abort_if_destination_exists'] && $mcms_filesystem->exists($remote_destination) ) {
			//If we're not clearing the destination folder and something exists there already, Bail.
			//But first check to see if there are actually any files in the folder.
			$_files = $mcms_filesystem->dirlist($remote_destination);
			if ( ! empty($_files) ) {
				$mcms_filesystem->delete($remote_source, true); //Clear out the source files.
				return new MCMS_Error('folder_exists', $this->strings['folder_exists'], $remote_destination );
			}
		}

		//Create destination if needed
		if ( ! $mcms_filesystem->exists( $remote_destination ) ) {
			if ( ! $mcms_filesystem->mkdir( $remote_destination, FS_CHMOD_DIR ) ) {
				return new MCMS_Error( 'mkdir_failed_destination', $this->strings['mkdir_failed'], $remote_destination );
			}
		}
		// Copy new version of item into place.
		$result = copy_dir($source, $remote_destination);
		if ( is_mcms_error($result) ) {
			if ( $args['clear_working'] ) {
				$mcms_filesystem->delete( $remote_source, true );
			}
			return $result;
		}

		//Clear the Working folder?
		if ( $args['clear_working'] ) {
			$mcms_filesystem->delete( $remote_source, true );
		}

		$destination_name = basename( str_replace($local_destination, '', $destination) );
		if ( '.' == $destination_name ) {
			$destination_name = '';
		}

		$this->result = compact( 'source', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination' );

		/**
		 * Filters the installation response after the installation has finished.
		 *
		 * @since 2.8.0
		 *
		 * @param bool  $response   Installation response.
		 * @param array $hook_extra Extra arguments passed to hooked filters.
		 * @param array $result     Installation result data.
		 */
		$res = apply_filters( 'upgrader_post_install', true, $args['hook_extra'], $this->result );

		if ( is_mcms_error($res) ) {
			$this->result = $res;
			return $res;
		}

		//Bombard the calling function will all the info which we've just used.
		return $this->result;
	}

	/**
	 * Run an upgrade/installation.
	 *
	 * Attempts to download the package (if it is not a local file), unpack it, and
	 * install it in the destination folder.
	 *
	 * @since 2.8.0
	 *
	 * @param array $options {
	 *     Array or string of arguments for upgrading/installing a package.
	 *
	 *     @type string $package                     The full path or URI of the package to install.
	 *                                               Default empty.
	 *     @type string $destination                 The full path to the destination folder.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the
	 *                                               destination folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files form the working
	 *                                               directory after copying to the destination.
	 *                                               Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if the destination
	 *                                               folder already exists. When true, `$clear_destination`
	 *                                               should be false. Default true.
	 *     @type bool   $is_multi                    Whether this run is one of multiple upgrade/installation
	 *                                               actions being performed in bulk. When true, the skin
	 *                                               MCMS_Upgrader::header() and MCMS_Upgrader::footer()
	 *                                               aren't called. Default false.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               MCMS_Upgrader::run().
	 * }
	 * @return array|false|MCMS_error The result from self::install_package() on success, otherwise a MCMS_Error,
	 *                              or false if unable to connect to the filesystem.
	 */
	public function run( $options ) {

		$defaults = array(
			'package' => '', // Please always pass this.
			'destination' => '', // And this
			'clear_destination' => false,
			'abort_if_destination_exists' => true, // Abort if the Destination directory exists, Pass clear_destination as false please
			'clear_working' => true,
			'is_multi' => false,
			'hook_extra' => array() // Pass any extra $hook_extra args here, this will be passed to any hooked filters.
		);

		$options = mcms_parse_args( $options, $defaults );

		/**
		 * Filters the package options before running an update.
		 *
		 * See also {@see 'upgrader_process_complete'}.
		 *
		 * @since 4.3.0
		 *
		 * @param array $options {
		 *     Options used by the upgrader.
		 *
		 *     @type string $package                     Package for update.
		 *     @type string $destination                 Update location.
		 *     @type bool   $clear_destination           Clear the destination resource.
		 *     @type bool   $clear_working               Clear the working resource.
		 *     @type bool   $abort_if_destination_exists Abort if the Destination directory exists.
		 *     @type bool   $is_multi                    Whether the upgrader is running multiple times.
		 *     @type array  $hook_extra {
		 *         Extra hook arguments.
		 *
		 *         @type string $action               Type of action. Default 'update'.
		 *         @type string $type                 Type of update process. Accepts 'module', 'myskin', or 'core'.
		 *         @type bool   $bulk                 Whether the update process is a bulk update. Default true.
		 *         @type string $module               The base module path from the modules directory.
		 *         @type string $myskin                The stylesheet or template name of the myskin.
		 *         @type string $language_update_type The language pack update type. Accepts 'module', 'myskin',
		 *                                            or 'core'.
		 *         @type object $language_update      The language pack update offer.
		 *     }
		 * }
		 */
		$options = apply_filters( 'upgrader_package_options', $options );

		if ( ! $options['is_multi'] ) { // call $this->header separately if running multiple times
			$this->skin->header();
		}

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array( MCMS_CONTENT_DIR, $options['destination'] ) );
		// Mainly for non-connected filesystem.
		if ( ! $res ) {
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return false;
		}

		$this->skin->before();

		if ( is_mcms_error($res) ) {
			$this->skin->error($res);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $res;
		}

		/*
		 * Download the package (Note, This just returns the filename
		 * of the file if the package is a local file)
		 */
		$download = $this->download_package( $options['package'] );
		if ( is_mcms_error($download) ) {
			$this->skin->error($download);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $download;
		}

		$delete_package = ( $download != $options['package'] ); // Do not delete a "local" file

		// Unzips the file into a temporary directory.
		$working_dir = $this->unpack_package( $download, $delete_package );
		if ( is_mcms_error($working_dir) ) {
			$this->skin->error($working_dir);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $working_dir;
		}

		// With the given options, this installs it to the destination directory.
		$result = $this->install_package( array(
			'source' => $working_dir,
			'destination' => $options['destination'],
			'clear_destination' => $options['clear_destination'],
			'abort_if_destination_exists' => $options['abort_if_destination_exists'],
			'clear_working' => $options['clear_working'],
			'hook_extra' => $options['hook_extra']
		) );

		$this->skin->set_result($result);
		if ( is_mcms_error($result) ) {
			$this->skin->error($result);
			$this->skin->feedback('process_failed');
		} else {
			// Installation succeeded.
			$this->skin->feedback('process_success');
		}

		$this->skin->after();

		if ( ! $options['is_multi'] ) {

			/**
			 * Fires when the upgrader process is complete.
			 *
			 * See also {@see 'upgrader_package_options'}.
			 *
			 * @since 3.6.0
			 * @since 3.7.0 Added to MCMS_Upgrader::run().
			 * @since 4.6.0 `$translations` was added as a possible argument to `$hook_extra`.
			 *
			 * @param MCMS_Upgrader $this MCMS_Upgrader instance. In other contexts, $this, might be a
			 *                          MySkin_Upgrader, Module_Upgrader, Core_Upgrade, or Language_Pack_Upgrader instance.
			 * @param array       $hook_extra {
			 *     Array of bulk item update data.
			 *
			 *     @type string $action       Type of action. Default 'update'.
			 *     @type string $type         Type of update process. Accepts 'module', 'myskin', 'translation', or 'core'.
			 *     @type bool   $bulk         Whether the update process is a bulk update. Default true.
			 *     @type array  $modules      Array of the basename paths of the modules' main files.
			 *     @type array  $myskins       The myskin slugs.
			 *     @type array  $translations {
			 *         Array of translations update data.
			 *
			 *         @type string $language The locale the translation is for.
			 *         @type string $type     Type of translation. Accepts 'module', 'myskin', or 'core'.
			 *         @type string $slug     Text domain the translation is for. The slug of a myskin/module or
			 *                                'default' for core translations.
			 *         @type string $version  The version of a myskin, module, or core.
			 *     }
			 * }
			 */
			do_action( 'upgrader_process_complete', $this, $options['hook_extra'] );

			$this->skin->footer();
		}

		return $result;
	}

	/**
	 * Toggle maintenance mode for the site.
	 *
	 * Creates/deletes the maintenance file to enable/disable maintenance mode.
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param bool $enable True to enable maintenance mode, false to disable.
	 */
	public function maintenance_mode( $enable = false ) {
		global $mcms_filesystem;
		$file = $mcms_filesystem->abspath() . '.maintenance';
		if ( $enable ) {
			$this->skin->feedback('maintenance_start');
			// Create maintenance file to signal that we are upgrading
			$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
			$mcms_filesystem->delete($file);
			$mcms_filesystem->put_contents($file, $maintenance_string, FS_CHMOD_FILE);
		} elseif ( ! $enable && $mcms_filesystem->exists( $file ) ) {
			$this->skin->feedback('maintenance_end');
			$mcms_filesystem->delete($file);
		}
	}

	/**
 	 * Creates a lock using MandarinCMS options.
 	 *
 	 * @since 4.5.0
 	 * @static
 	 *
 	 * @param string $lock_name       The name of this unique lock.
 	 * @param int    $release_timeout Optional. The duration in seconds to respect an existing lock.
	 *                                Default: 1 hour.
 	 * @return bool False if a lock couldn't be created or if the lock is still valid. True otherwise.
 	 */
	public static function create_lock( $lock_name, $release_timeout = null ) {
		global $mcmsdb;
		if ( ! $release_timeout ) {
			$release_timeout = HOUR_IN_SECONDS;
		}
		$lock_option = $lock_name . '.lock';

		// Try to lock.
		$lock_result = $mcmsdb->query( $mcmsdb->prepare( "INSERT IGNORE INTO `$mcmsdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_option, time() ) );

		if ( ! $lock_result ) {
			$lock_result = get_option( $lock_option );

			// If a lock couldn't be created, and there isn't a lock, bail.
			if ( ! $lock_result ) {
				return false;
			}

			// Check to see if the lock is still valid. If it is, bail.
			if ( $lock_result > ( time() - $release_timeout ) ) {
				return false;
			}

			// There must exist an expired lock, clear it and re-gain it.
			MCMS_Upgrader::release_lock( $lock_name );

			return MCMS_Upgrader::create_lock( $lock_name, $release_timeout );
		}

		// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
		update_option( $lock_option, time() );

		return true;
	}

	/**
 	 * Releases an upgrader lock.
 	 *
 	 * @since 4.5.0
 	 * @static
	 *
	 * @see MCMS_Upgrader::create_lock()
 	 *
 	 * @param string $lock_name The name of this unique lock.
	 * @return bool True if the lock was successfully released. False on failure.
 	 */
	public static function release_lock( $lock_name ) {
		return delete_option( $lock_name . '.lock' );
	}

}

/** Module_Upgrader class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-module-upgrader.php';

/** MySkin_Upgrader class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-myskin-upgrader.php';

/** Language_Pack_Upgrader class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-language-pack-upgrader.php';

/** Core_Upgrader class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-core-upgrader.php';

/** File_Upload_Upgrader class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-file-upload-upgrader.php';

/** MCMS_Automatic_Updater class */
require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-automatic-updater.php';
