<?php
/**
 * @package MCMSSEO\admin|ajax
 */

/**
 * Class Ultimatum_Module_Conflict_Ajax
 */
class Ultimatum_Module_Conflict_Ajax {

	/**
	 * @var string
	 */
	private $option_name = 'mcmsseo_dismissed_conflicts';

	/**
	 * @var array
	 */
	private $dismissed_conflicts = array();

	/**
	 * Initialize the hooks for the AJAX request
	 */
	public function __construct() {
		add_action( 'mcms_ajax_mcmsseo_dismiss_module_conflict', array( $this, 'dismiss_notice' ) );
	}

	/**
	 * Handles the dismiss notice request
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'dismiss-module-conflict' );

		$conflict_data = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		$this->dismissed_conflicts = $this->get_dismissed_conflicts( $conflict_data['section'] );

		$this->compare_modules( $conflict_data['modules'] );

		$this->save_dismissed_conflicts( $conflict_data['section'] );

		mcms_die( 'true' );
	}

	/**
	 * Getting the user option from the database
	 *
	 * @return bool|array
	 */
	private function get_dismissed_option() {
		return get_user_meta( get_current_user_id(), $this->option_name, true );
	}

	/**
	 * Getting the dismissed conflicts from the database
	 *
	 * @param string $module_section Type of conflict group (such as Open Graph or sitemap).
	 *
	 * @return array
	 */
	private function get_dismissed_conflicts( $module_section ) {
		$dismissed_conflicts = $this->get_dismissed_option();

		if ( is_array( $dismissed_conflicts ) && array_key_exists( $module_section, $dismissed_conflicts ) ) {
			return $dismissed_conflicts[ $module_section ];
		}

		return array();
	}

	/**
	 * Storing the conflicting modules as an user option in the database
	 *
	 * @param string $module_section Module conflict type (such as Open Graph or sitemap).
	 */
	private function save_dismissed_conflicts( $module_section ) {
		$dismissed_conflicts = $this->get_dismissed_option();

		$dismissed_conflicts[ $module_section ] = $this->dismissed_conflicts;

		update_user_meta( get_current_user_id(), $this->option_name, $dismissed_conflicts );
	}

	/**
	 * Loop through the modules to compare them with the already stored dismissed module conflicts
	 *
	 * @param array $posted_modules Module set to check.
	 */
	public function compare_modules( array $posted_modules ) {
		foreach ( $posted_modules as $posted_module ) {
			$this->compare_module( $posted_module );
		}
	}

	/**
	 * Check if module is already dismissed, if not store it in the array that will be saved later
	 *
	 * @param string $posted_module Module to check against dismissed conflicts.
	 */
	private function compare_module( $posted_module ) {
		if ( ! in_array( $posted_module, $this->dismissed_conflicts ) ) {
			$this->dismissed_conflicts[] = $posted_module;
		}
	}
}
