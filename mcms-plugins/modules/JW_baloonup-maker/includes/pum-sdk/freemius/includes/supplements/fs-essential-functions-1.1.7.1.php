<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.1.7
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	/**
	 * Find the module main file path based on any given file inside the module's folder.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.1.7.1
	 *
	 * @param string $file Absolute path to a file inside a module's folder.
	 *
	 * @return string
	 */
	function fs_find_direct_caller_module_file( $file ) {
		/**
		 * All the code below will be executed once on activation.
		 * If the user changes the main module's file name, the file_exists()
		 * will catch it.
		 */
		if ( ! function_exists( 'get_modules' ) ) {
			require_once BASED_TREE_URI . 'mcms-admin/includes/module.php';
		}

		$all_modules = get_modules();

		$file_real_path = fs_normalize_path( realpath( $file ) );

		// Get active module's main files real full names (might be symlinks).
		foreach ( $all_modules as $relative_path => &$data ) {
			if ( 0 === strpos( $file_real_path, fs_normalize_path( dirname( realpath( MCMS_PLUGIN_DIR . '/' . $relative_path ) ) ) ) ) {
				if ( '.' !== dirname( trailingslashit( $relative_path ) ) ) {
	                return $relative_path;
	            }
			}
		}

		return null;
	}
