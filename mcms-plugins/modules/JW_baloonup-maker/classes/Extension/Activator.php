<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * BaloonUp Maker Extension Activation Handler Class
 *
 * @version       1.1.0
 */
class PUM_Extension_Activator {

	public $extension_class_name;

	/**
	 * @var string
	 */
	public $extension_name;

	/**
	 * @var string
	 */
	public $extension_slug;

	/**
	 * @var int
	 */
	public $extension_id;

	/**
	 * @var string
	 */
	public $extension_version;

	/**
	 * @var string
	 */
	public $extension_file;

	public $required_core_version;

	/**
	 * @var bool
	 */
	public $core_installed = false;

	public $core_path;

	/**
	 * @param $class_name
	 * @param $prop_name
	 *
	 * @return bool|mixed
	 */
	public function get_static_prop( $class_name, $prop_name ) {
		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			try {
				$class = new ReflectionClass( $class_name );

				return $class->getStaticPropertyValue( $prop_name );
			} catch ( ReflectionException $e ) {
				return false;
			}
		}

		return $class_name::$$prop_name;
	}

	/**
	 * Setup the activator class
	 *
	 * @param  $class_name
	 */
	public function __construct( $class_name ) {
		// We need module.php!
		require_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' );

		// Validate extension class is valid.
		if ( in_array( false, array(
			class_exists( $class_name ),
			property_exists( $class_name, 'REQUIRED_CORE_VER' ),
			property_exists( $class_name, 'NAME' ),
			method_exists( $class_name, 'instance' ),
		) ) ) {
			return;
		}

		$this->extension_class_name  = $class_name;
		$this->extension_id          = $this->get_static_prop( $class_name, 'ID' );
		$this->extension_name        = $this->get_static_prop( $class_name, 'NAME' );
		$this->extension_version     = $this->get_static_prop( $class_name, 'VER' );
		$this->required_core_version = $this->get_static_prop( $class_name, 'REQUIRED_CORE_VER' );

		$modules = get_modules();

		// Is BaloonUp Maker installed?
		foreach ( $modules as $module_path => $module ) {
			if ( $module['Name'] == 'BaloonUp Maker' ) {
				$this->core_installed = true;
				$this->core_path      = $module_path;
				break;
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_status() {
		if ( $this->core_installed && ! class_exists( 'BaloonUp_Maker' ) ) {
			return 'not_activated';
		} elseif ( $this->core_installed && isset( $this->required_core_version ) && version_compare( BaloonUp_Maker::$VER, $this->required_core_version, '<' ) ) {
			return 'not_updated';
		} elseif ( ! $this->core_installed ) {
			return 'not_installed';
		}

		return 'active';
	}


	/**
	 * Process module deactivation
	 *
	 * @access      public
	 */
	public function run() {
		if ( $this->get_status() != 'active' ) {
			// Display notice
			add_action( 'admin_notices', array( $this, 'missing_balooncreate_notice' ) );
		} else {
			$class_name = $this->extension_class_name;

			// Generate an instance of the extension class in a PHP 5.2 compatible way.
			call_user_func( array( $class_name, 'instance' ) );

			$this->extension_file = $this->get_static_prop( $class_name, 'FILE' );

			$module_slug          = explode( '/', module_basename( $this->extension_file ), 2 );
			$this->extension_slug = str_replace( array( 'baloonup-maker-', 'pum-' ), '', $module_slug[0] );

			// Handle licensing
			if ( class_exists( 'PUM_Extension_License' ) ) {
				new PUM_Extension_License( $this->extension_file, $this->extension_name, $this->extension_version, 'MCMS BaloonUp Maker', null, null, $this->extension_id );
			}

			add_filter( 'pum_enabled_extensions', array( $this, 'enabled_extensions' ) );
		}
	}


	/**
	 * Display notice if BaloonUp Maker isn't installed
	 */
	public function missing_balooncreate_notice() {
		switch ( $this->get_status() ) {
			case 'not_activated':
				$url  = esc_url( mcms_nonce_url( admin_url( 'modules.php?action=activate&module=' . $this->core_path ), 'activate-module_' . $this->core_path ) );
				$link = '<a href="' . $url . '">' . __( 'activate it' ) . '</a>';
				echo '<div class="error"><p>' . $this->extension_name . sprintf( __( ' requires BaloonUp Maker! Please %s to continue!' ), $link ) . '</p></div>';

				break;
			case 'not_updated':
				$url  = esc_url( mcms_nonce_url( admin_url( 'update.php?action=upgrade-module&module=' . $this->core_path ), 'upgrade-module_' . $this->core_path ) );
				$link = '<a href="' . $url . '">' . __( 'update it' ) . '</a>';
				echo '<div class="error"><p>' . $this->extension_name . sprintf( __( ' requires BaloonUp Maker v%s or higher! Please %s to continue!' ), $this->required_core_version, $link ) . '</p></div>';

				break;
			case 'not_installed':
				$url  = esc_url( mcms_nonce_url( self_admin_url( 'update.php?action=install-module&module=baloonup-maker' ), 'install-module_baloonup-maker' ) );
				$link = '<a href="' . $url . '">' . __( 'install it' ) . '</a>';
				echo '<div class="error"><p>' . $this->extension_name . sprintf( __( ' requires BaloonUp Maker! Please %s to continue!' ), $link ) . '</p></div>';

				break;
			case 'active':
			default:
				return;
		}
	}

	/**
	 * @param array $enabled_extensions
	 *
	 * @return array
	 */
	public function enabled_extensions( $enabled_extensions = array() ) {
		$enabled_extensions[ $this->extension_slug ] = $this->extension_class_name;

		return $enabled_extensions;
	}
}
