<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.0.6
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	class FS_License_Manager /*extends FS_Abstract_Manager*/
	{
//
//
//		/**
//		 * @var FS_License_Manager[]
//		 */
//		private static $_instances = array();
//
//		static function instance( Freemius $fs ) {
//			$slug = strtolower( $fs->get_slug() );
//
//			if ( ! isset( self::$_instances[ $slug ] ) ) {
//				self::$_instances[ $slug ] = new FS_License_Manager( $slug, $fs );
//			}
//
//			return self::$_instances[ $slug ];
//		}
//
////		private function __construct($slug) {
////			parent::__construct($slug);
////		}
//
//		function entry_id() {
//			return 'licenses';
//		}
//
//		function sync( $id ) {
//
//		}
//
//		/**
//		 * @author Vova Feldman (@svovaf)
//		 * @since  1.0.5
//		 * @uses   FS_Api
//		 *
//		 * @param number|bool $module_id
//		 *
//		 * @return FS_Module_License[]|stdClass Licenses or API error.
//		 */
//		function api_get_user_module_licenses( $module_id = false ) {
//			$api = $this->_fs->get_api_user_scope();
//
//			if ( ! is_numeric( $module_id ) ) {
//				$module_id = $this->_fs->get_id();
//			}
//
//			$result = $api->call( "/modules/{$module_id}/licenses.json" );
//
//			if ( ! isset( $result->error ) ) {
//				for ( $i = 0, $len = count( $result->licenses ); $i < $len; $i ++ ) {
//					$result->licenses[ $i ] = new FS_Module_License( $result->licenses[ $i ] );
//				}
//
//				$result = $result->licenses;
//			}
//
//			return $result;
//		}
//
//		function api_get_many() {
//
//		}
//
//		function api_activate( $id ) {
//
//		}
//
//		function api_deactivate( $id ) {
//
//		}

		/**
		 * @param FS_Module_License[] $licenses
		 *
		 * @return bool
		 */
		static function has_premium_license( $licenses ) {
			if ( is_array( $licenses ) ) {
				foreach ( $licenses as $license ) {
					/**
					 * @var FS_Module_License $license
					 */
					if ( ! $license->is_utilized() && $license->is_features_enabled() ) {
						return true;
					}
				}
			}

			return false;
		}
	}