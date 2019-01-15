<?php
/**
 * @package MCMSSEO\Admin
 * @since      1.7.0
 */

/**
 * Base class for handling module conflicts.
 */
class Ultimatum_Module_Conflict {

	/**
	 * The modules must be grouped per section.
	 *
	 * It's possible to check for each section if there are conflicting module
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * All the current active modules will be stored in this private var
	 *
	 * @var array
	 */
	protected $all_active_modules = array();

	/**
	 * After searching for active modules that are in $this->modules the active modules will be stored in this
	 * property
	 *
	 * @var array
	 */
	protected $active_modules = array();

	/**
	 * Property for holding instance of itself
	 *
	 * @var Ultimatum_Module_Conflict
	 */
	protected static $instance;

	/**
	 * For the use of singleton pattern. Create instance of itself and return his instance
	 *
	 * @param string $class_name Give the classname to initialize. If classname is false (empty) it will use it's own __CLASS__.
	 *
	 * @return Ultimatum_Module_Conflict
	 */
	public static function get_instance( $class_name = '' ) {

		if ( is_null( self::$instance ) ) {
			if ( ! is_string( $class_name ) || $class_name === '' ) {
				$class_name = __CLASS__;
			}

			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	/**
	 * Setting instance, all active modules and search for active modules
	 *
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
		// Set active modules.
		$this->all_active_modules = get_option( 'active_modules' );

		if ( filter_input( INPUT_GET, 'action' ) === 'deactivate' ) {
			$this->remove_deactivated_module();
		}

		// Search for active modules.
		$this->search_active_modules();
	}

	/**
	 * Check if there are conflicting modules for given $module_section
	 *
	 * @param string $module_section Type of module conflict (such as Open Graph or sitemap).
	 *
	 * @return bool
	 */
	public function check_for_conflicts( $module_section ) {

		static $sections_checked;

		if ( $sections_checked === null ) {
			$sections_checked = array();
		}

		if ( ! in_array( $module_section, $sections_checked ) ) {
			$sections_checked[] = $module_section;
			$has_conflicts      = ( ! empty( $this->active_modules[ $module_section ] ) );

			return $has_conflicts;
		}

		return false;
	}

	/**
	 * Getting all the conflicting modules and return them as a string.
	 *
	 * This method will loop through all conflicting modules to get the details of each module. The module name
	 * will be taken from the details to parse a comma separated string, which can be use for by example a notice
	 *
	 * @param string $module_section Module conflict type (such as Open Graph or sitemap).
	 *
	 * @return string
	 */
	public function get_conflicting_modules_as_string( $module_section ) {
		if ( ! function_exists( 'get_module_data' ) ) {
			require_once( BASED_TREE_URI . '/mcms-admin/includes/module.php' );
		}

		// Getting the active modules by given section.
		$modules = $this->active_modules[ $module_section ];

		$module_names = array();
		foreach ( $modules as $module ) {
			if ( $name = MCMSSEO_Utils::get_module_name( $module ) ) {
				$module_names[] = '<em>' . $name . '</em>';
			}
		}
		unset( $modules, $module );

		if ( ! empty( $module_names ) ) {
			return implode( ' &amp; ', $module_names );
		}
	}

	/**
	 * Checks for given $module_sections for conflicts
	 *
	 * @param array $module_sections Set of sections.
	 */
	public function check_module_conflicts( $module_sections ) {
		foreach ( $module_sections as $module_section => $readable_module_section ) {
			// Check for conflicting modules and show error if there are conflicts.
			if ( $this->check_for_conflicts( $module_section ) ) {
				$this->set_error( $module_section, $readable_module_section );
			}
		}

		// List of all active sections.
		$sections = array_keys( $module_sections );
		// List of all sections.
		$all_module_sections = array_keys( $this->modules );

		/*
		 * Get all sections that are inactive.
		 * These modules need to be cleared.
		 *
		 * This happens when Sitemaps or OpenGraph implementations toggle active/disabled.
		 */
		$inactive_sections = array_diff( $all_module_sections, $sections );
		if ( ! empty( $inactive_sections ) ) {
			foreach ( $inactive_sections as $section ) {
				array_walk( $this->modules[ $section ], array( $this, 'clear_error' ) );
			}
		}

		// For active sections clear errors for inactive modules.
		foreach ( $sections as $section ) {
			// By default clear errors for all modules of the section.
			$inactive_modules = $this->modules[ $section ];

			// If there are active modules, filter them from being cleared.
			if ( isset( $this->active_modules[ $section ] ) ) {
				$inactive_modules = array_diff( $this->modules[ $section ], $this->active_modules[ $section ] );
			}

			array_walk( $inactive_modules, array( $this, 'clear_error' ) );
		}
	}

	/**
	 * Setting an error on the screen
	 *
	 * @param string $module_section          Type of conflict group (such as Open Graph or sitemap).
	 * @param string $readable_module_section This is the value for the translation.
	 */
	protected function set_error( $module_section, $readable_module_section ) {

		$notification_center = Ultimatum_Notification_Center::get();

		foreach ( $this->active_modules[ $module_section ] as $module_file ) {

			$module_name = MCMSSEO_Utils::get_module_name( $module_file );

			$error_message = '';
			/* translators: %1$s: 'Facebook & Open Graph' module name(s) of possibly conflicting module(s), %2$s to Ultimatum SEO */
			$error_message .= '<p>' . sprintf( __( 'The %1$s module might cause issues when used in conjunction with %2$s.', 'mandarincms-seo' ), '<em>' . $module_name . '</em>', 'Ultimatum SEO' ) . '</p>';
			$error_message .= '<p>' . sprintf( $readable_module_section, 'Ultimatum SEO', $module_name ) . '</p>';

			/* translators: %s: 'Facebook' module name of possibly conflicting module */
			$error_message .= '<a class="button button-primary" href="' . mcms_nonce_url( 'modules.php?action=deactivate&amp;module=' . $module_file . '&amp;module_status=all', 'deactivate-module_' . $module_file ) . '">' . sprintf( __( 'Deactivate %s', 'mandarincms-seo' ), MCMSSEO_Utils::get_module_name( $module_file ) ) . '</a> ';

			$identifier = $this->get_notification_identifier( $module_file );

			// Add the message to the notifications center.
			$notification_center->add_notification(
				new Ultimatum_Notification(
					$error_message,
					array(
						'type' => Ultimatum_Notification::ERROR,
						'id'   => 'mcmsseo-conflict-' . $identifier,
					)
				)
			);
		}
	}

	/**
	 * Clear the notification for a module
	 *
	 * @param string $module_file Clear the optional notification for this module.
	 */
	protected function clear_error( $module_file ) {
		$identifier = $this->get_notification_identifier( $module_file );

		$notification_center = Ultimatum_Notification_Center::get();
		$notification = $notification_center->get_notification_by_id( 'mcmsseo-conflict-' . $identifier );

		if ( $notification ) {
			$notification_center->remove_notification( $notification );
		}
	}

	/**
	 * Loop through the $this->modules to check if one of the modules is active.
	 *
	 * This method will store the active modules in $this->active_modules.
	 */
	protected function search_active_modules() {
		foreach ( $this->modules as $module_section => $modules ) {
			$this->check_modules_active( $modules, $module_section );
		}
	}

	/**
	 * Loop through modules and check if each module is active
	 *
	 * @param array  $modules        Set of modules.
	 * @param string $module_section Type of conflict group (such as Open Graph or sitemap).
	 */
	protected function check_modules_active( $modules, $module_section ) {
		foreach ( $modules as $module ) {
			if ( $this->check_module_is_active( $module ) ) {
				$this->add_active_module( $module_section, $module );
			}
		}
	}


	/**
	 * Check if given module exists in array with all_active_modules
	 *
	 * @param string $module Module basename string.
	 *
	 * @return bool
	 */
	protected function check_module_is_active( $module ) {
		return in_array( $module, $this->all_active_modules );
	}

	/**
	 * Add module to the list of active modules.
	 *
	 * This method will check first if key $module_section exists, if not it will create an empty array
	 * If $module itself doesn't exist it will be added.
	 *
	 * @param string $module_section Type of conflict group (such as Open Graph or sitemap).
	 * @param string $module         Module basename string.
	 */
	protected function add_active_module( $module_section, $module ) {

		if ( ! array_key_exists( $module_section, $this->active_modules ) ) {
			$this->active_modules[ $module_section ] = array();
		}

		if ( ! in_array( $module, $this->active_modules[ $module_section ] ) ) {
			$this->active_modules[ $module_section ][] = $module;
		}
	}

	/**
	 * Search in $this->modules for the given $module
	 *
	 * If there is a result it will return the module category
	 *
	 * @param string $module Module basename string.
	 *
	 * @return int|string
	 */
	protected function find_module_category( $module ) {

		foreach ( $this->modules as $module_section => $modules ) {
			if ( in_array( $module, $modules ) ) {
				return $module_section;
			}
		}

	}

	/**
	 * When being in the deactivation process the currently deactivated module has to be removed.
	 */
	private function remove_deactivated_module() {
		$deactivated_module = filter_input( INPUT_GET, 'module' );
		$key_to_remove      = array_search( $deactivated_module, $this->all_active_modules );

		if ( $key_to_remove !== false ) {
			unset( $this->all_active_modules[ $key_to_remove ] );
		}
	}

	/**
	 * Get the identifier from the module file
	 *
	 * @param string $module_file Module file to get Identifier from.
	 *
	 * @return string
	 */
	private function get_notification_identifier( $module_file ) {
		return md5( $module_file );
	}
}
