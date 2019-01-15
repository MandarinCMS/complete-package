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

	class FS_Module_Manager {
		/**
		 * @since 1.2.2
		 *
		 * @var string|number
		 */
		protected $_module_id;
		/**
		 * @since 1.2.2
		 *
		 * @var FS_Module
		 */
		protected $_module;

		/**
		 * @var FS_Module_Manager[]
		 */
		private static $_instances = array();
		/**
		 * @var FS_Logger
		 */
		protected $_logger;

		/**
		 * Option names
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.2.2
		 */
		const OPTION_NAME_PLUGINS = 'modules';
		const OPTION_NAME_THEMES  = 'myskins';

		/**
		 * @param  string|number $module_id
		 *
		 * @return FS_Module_Manager
		 */
		static function instance( $module_id ) {
			$key = 'm_' . $module_id;

			if ( ! isset( self::$_instances[ $key ] ) ) {
				self::$_instances[ $key ] = new FS_Module_Manager( $module_id );
			}

			return self::$_instances[ $key ];
        }

		/**
		 * @param string|number $module_id
		 */
		protected function __construct( $module_id ) {
			$this->_logger    = FS_Logger::get_logger( MCMS_FS__SLUG . '_' . $module_id . '_' . 'modules', MCMS_FS__DEBUG_SDK, MCMS_FS__ECHO_DEBUG_SDK );
			$this->_module_id = $module_id;

			$this->load();
		}

		protected function get_option_manager() {
			return FS_Option_Manager::get_manager( MCMS_FS__ACCOUNTS_OPTION_NAME, true, true );
		}

		/**
		 * @author Leo Fajardo (@leorw)
		 * @since  1.2.2
		 *
		 * @param  string|bool $module_type "module", "myskin", or "false" for all modules.
		 *
		 * @return array
		 */
		protected function get_all_modules( $module_type = false ) {
			$option_manager = $this->get_option_manager();

			if ( false !== $module_type ) {
				return $option_manager->get_option( $module_type . 's', array() );
			}

			return array(
				self::OPTION_NAME_PLUGINS => $option_manager->get_option( self::OPTION_NAME_PLUGINS, array() ),
				self::OPTION_NAME_THEMES  => $option_manager->get_option( self::OPTION_NAME_THEMES, array() ),
			);
		}

		/**
		 * Load module data from local DB.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.6
		 */
		function load() {
			$all_modules = $this->get_all_modules();

			if ( ! is_numeric( $this->_module_id ) ) {
				unset( $all_modules[ self::OPTION_NAME_THEMES ] );
			}

			foreach ( $all_modules as $modules ) {
				/**
				 * @since 1.2.2
				 *
				 * @var $modules FS_Module[]
				 */
				foreach ( $modules as $module ) {
					$found_module = false;

					/**
					 * If module ID is not numeric, it must be a module's slug.
					 *
					 * @author Leo Fajardo (@leorw)
					 * @since  1.2.2
					 */
					if ( ! is_numeric( $this->_module_id ) ) {
						if ( $this->_module_id === $module->slug ) {
							$this->_module_id = $module->id;
							$found_module     = true;
						}
					} else if ( $this->_module_id == $module->id ) {
						$found_module = true;
					}

					if ( $found_module ) {
						$this->_module = $module;
						break;
					}
				}
			}
		}

		/**
		 * Store module on local DB.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.6
		 *
		 * @param bool|FS_Module $module
		 * @param bool           $flush
		 *
		 * @return bool|\FS_Module
		 */
		function store( $module = false, $flush = true ) {
			if ( false !== $module ) {
				$this->_module = $module;
			}

			$all_modules = $this->get_all_modules( $this->_module->type );
			$all_modules[ $this->_module->slug ] = $this->_module;

			$options_manager = $this->get_option_manager();
			$options_manager->set_option( $this->_module->type . 's', $all_modules, $flush );

			return $this->_module;
		}

		/**
		 * Update local module data if different.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.6
		 *
		 * @param \FS_Module $module
		 * @param bool       $store
		 *
		 * @return bool True if module was updated.
		 */
		function update( FS_Module $module, $store = true ) {
			if ( ! ($this->_module instanceof FS_Module ) ||
			     $this->_module->slug != $module->slug ||
			     $this->_module->public_key != $module->public_key ||
			     $this->_module->secret_key != $module->secret_key ||
			     $this->_module->parent_module_id != $module->parent_module_id ||
			     $this->_module->title != $module->title
			) {
				$this->store( $module, $store );

				return true;
			}

			return false;
		}

		/**
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.6
		 *
		 * @param FS_Module $module
		 * @param bool      $store
		 */
		function set( FS_Module $module, $store = false ) {
			$this->_module = $module;

			if ( $store ) {
				$this->store();
			}
		}

		/**
		 * @author Vova Feldman (@svovaf)
		 * @since  1.0.6
		 *
		 * @return bool|\FS_Module
		 */
		function get() {
			return isset( $this->_module ) ?
				$this->_module :
				false;
		}


	}