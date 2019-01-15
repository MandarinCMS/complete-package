<?php
/**
 * @package MCMSSEO\Module_Availability
 */

/**
 * Class MCMSSEO_Module_Availability
 */
class MCMSSEO_Module_Availability {

	/**
	 * @var array
	 */
	protected $modules = array();

	/**
	 * MCMSSEO_Module_Availability constructor.
	 */
	public function __construct() {
		$this->register_ultimatum_modules();
		$this->register_ultimatum_modules_status();
	}

	/**
	 * Registers all the available Ultimatum SEO modules.
	 */
	protected function register_ultimatum_modules() {
		$this->modules = array(
			'ultimatum-seo-premium' => array(
				'url'       => 'https://jiiworks.net/mandarincms/modules/seo-premium/',
				'title'     => 'Ultimatum SEO',
				/* translators: %1$s expands to Ultimatum SEO */
				'description' => sprintf( __( 'The premium version of %1$s with more features & support.', 'mandarincms-seo' ), 'Ultimatum SEO' ),
				'installed' => false,
				'slug' => 'mandarincms-seo-premium/mcms-seo-premium.php',
			),

			'video-seo-for-mandarincms-seo-by-ultimatum' => array(
				'url'       => 'https://jiiworks.net/mandarincms/modules/video-seo/',
				'title'     => 'Video SEO',
				'description' => __( 'Optimize your videos to show them off in search results and get more clicks!', 'mandarincms-seo' ),
				'installed' => false,
				'slug' => 'mcmsseo-video/video-seo.php',
			),

			'ultimatum-news-seo' => array(
				'url'       => 'https://jiiworks.net/mandarincms/modules/news-seo/',
				'title'     => 'News SEO',
				'description' => __( 'Are you in Google News? Increase your traffic from Google News by optimizing for it!', 'mandarincms-seo' ),
				'installed' => false,
				'slug' => 'mcmsseo-news/mcmsseo-news.php',
			),

			'local-seo-for-ultimatum-seo' => array(
				'url'       => 'https://jiiworks.net/mandarincms/modules/local-seo/',
				'title'     => 'Local SEO',
				'description' => __( 'Rank better locally and in Google Maps, without breaking a sweat!', 'mandarincms-seo' ),
				'installed' => false,
				'slug' => 'mandarincms-seo-local/local-seo.php',
			),

			'ultimatum-woocommerce-seo' => array(
				'url'       => 'https://jiiworks.net/mandarincms/modules/ultimatum-woocommerce-seo/',
				'title'     => 'Ultimatum WooCommerce SEO',
				/* translators: %1$s expands to Ultimatum SEO */
				'description' => sprintf( __( 'Seamlessly integrate WooCommerce with %1$s and get extra features!', 'mandarincms-seo' ), 'Ultimatum SEO' ),
				'installed' => false,
				'slug' => 'mcmsseo-woocommerce/mcmsseo-woocommerce.php',
			),
		);
	}

	/**
	 * Sets certain module properties based on MandarinCMS' status.
	 */
	protected function register_ultimatum_modules_status() {

		foreach ( $this->modules as $name => $module ) {

			$module_slug = $module['slug'];
			$module_path = MCMS_PLUGIN_DIR . '/' . $module_slug;

			if ( file_exists( $module_path ) ) {
				$module_data                         = get_module_data( $module_path, false, false );
				$this->modules[ $name ]['installed'] = true;
				$this->modules[ $name ]['version']   = $module_data['Version'];
				$this->modules[ $name ]['active']    = is_module_active( $module_slug );
			}
		}
	}

	/**
	 * Checks whether or not a module is known within the Ultimatum SEO collection.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return bool Whether or not the module is exists.
	 */
	protected function module_exists( $module ) {
		return isset( $this->modules[ $module ] );
	}

	/**
	 * Gets all the possibly available modules.
	 *
	 * @return array Array containing the information about the modules.
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Gets a specific module. Returns an empty array if it cannot be found.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return array The module properties.
	 */
	public function get_module( $module ) {
		if ( ! $this->module_exists( $module ) ) {
			return array();
		}

		return $this->modules[ $module ];
	}

	/**
	 * Gets the version of the module.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return string The version associated with the module.
	 */
	public function get_version( $module ) {
		if ( ! isset( $module['version'] ) ) {
			return '';
		}

		return $module['version'];
	}

	/**
	 * Checks if there are dependencies available for the module.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return bool Whether or not there is a dependency present.
	 */
	public function has_dependencies( $module ) {
		return ( isset( $module['_dependencies'] ) && ! empty( $module['_dependencies'] ) );
	}

	/**
	 * Gets the dependencies for the module.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return array Array containing all the dependencies associated with the module.
	 */
	public function get_dependencies( $module ) {
		if ( ! $this->has_dependencies( $module ) ) {
			return array();
		}

		return $module['_dependencies'];
	}

	/**
	 * Checks if all dependencies are satisfied.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return bool Whether or not the dependencies are satisfied.
	 */
	public function dependencies_are_satisfied( $module ) {
		if ( ! $this->has_dependencies( $module ) ) {
			return true;
		}

		$dependencies = $this->get_dependencies( $module );
		$installed_dependencies = array_filter( $dependencies, array( $this, 'is_dependency_available' ) );

		return count( $installed_dependencies ) === count( $dependencies );
	}

	/**
	 * Checks whether or not one of the modules is properly installed and usable.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return bool Whether or not the module is properly installed.
	 */
	public function is_installed( $module ) {
		if ( empty( $module ) ) {
			return false;
		}

		$dependencies_are_satisfied = $this->dependencies_are_satisfied( $module );

		return $dependencies_are_satisfied && $this->is_available( $module );
	}

	/**
	 * Gets all installed modules.
	 *
	 * @return array
	 */
	public function get_installed_modules() {
		$installed = array();

		foreach ( $this->modules as $moduleKey => $module ) {
			if ( $this->is_installed( $module ) ) {
				$installed[ $moduleKey ] = $module;
			}
		}

		return $installed;
	}

	/**
	 * Checks for the availability of the module.
	 *
	 * @param {string} $module The module to search for.
	 *
	 * @return bool Whether or not the module is available.
	 */
	public function is_available( $module ) {
		return isset( $module['installed'] ) && $module['installed'] === true;
	}

	/**
	 * Checks whether a dependency is available.
	 *
	 * @param {string} $dependency The dependency to look for.
	 *
	 * @return bool Whether or not the dependency is available.
	 */
	public function is_dependency_available( $dependency ) {
		return class_exists( $dependency );
	}
}
