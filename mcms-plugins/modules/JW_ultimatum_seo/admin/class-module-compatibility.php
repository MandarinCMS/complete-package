<?php
/**
 * @package MCMSSEO\Module_Compatibility
 */

/**
 * Class MCMSSEO_Module_Compatibility
 */
class MCMSSEO_Module_Compatibility {

	/**
	 * @var string
	 */
	protected $current_mcmsseo_version;

	/**
	 * @var MCMSSEO_Module_Availability
	 */
	protected $availability_checker;

	/**
	 * @var array
	 */
	protected $installed_modules;

	/**
	 * MCMSSEO_Module_Compatibility constructor.
	 *
	 * @param string     $version The version to check against.
	 * @param null|class $availability_checker The checker to use.
	 */
	public function __construct( $version, $availability_checker = null ) {
		// We trim off the patch version, as this shouldn't break the comparison.
		$this->current_mcmsseo_version = $this->get_major_minor_version( $version );
		$this->availability_checker = $this->retrieve_availability_checker( $availability_checker );
		$this->installed_modules = $this->availability_checker->get_installed_modules();
	}

	/**
	 * Retrieves the availability checker.
	 *
	 * @param null|object $checker The checker to set.
	 *
	 * @return MCMSSEO_Module_Availability The checker to use.
	 */
	private function retrieve_availability_checker( $checker ) {
		if ( is_null( $checker ) || ! is_object( $checker ) ) {
			return new MCMSSEO_Module_Availability();
		}

		return $checker;
	}

	/**
	 * Wraps the availability checker's get_installed_modules method.
	 *
	 * @return array Array containing all the installed modules.
	 */
	public function get_installed_modules() {
		return $this->installed_modules;
	}

	/**
	 * Creates a list of installed modules and whether or not they are compatible.
	 *
	 * @return array Array containing the installed modules and compatibility.
	 */
	public function get_installed_modules_compatibility() {
		foreach ( $this->installed_modules as $key => $module ) {

			$this->installed_modules[ $key ]['compatible'] = $this->is_compatible( $key );
		}

		return $this->installed_modules;
	}

	/**
	 * Checks whether or not a module is compatible.
	 *
	 * @param string $module The module to look for and match.
	 *
	 * @return bool Whether or not the module is compatible.
	 */
	public function is_compatible( $module ) {
		$module = $this->availability_checker->get_module( $module );
		$module_version = $this->availability_checker->get_version( $module );

		return $this->get_major_minor_version( $module_version ) === $this->current_mcmsseo_version;
	}

	/**
	 * Gets the major/minor version of the module for easier comparing.
	 *
	 * @param string $version The version to trim.
	 *
	 * @return string The major/minor version of the module.
	 */
	protected function get_major_minor_version( $version ) {
		return substr( $version, 0, 3 );
	}
}
