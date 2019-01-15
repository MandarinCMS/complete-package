<?php
/**
 * Upgrader API: Module_Installer_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Module Installer Skin for MandarinCMS Module Installer.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see MCMS_Upgrader_Skin
 */
class Module_Installer_Skin extends MCMS_Upgrader_Skin {
	public $api;
	public $type;

	/**
	 *
	 * @param array $args
	 */
	public function __construct($args = array()) {
		$defaults = array( 'type' => 'web', 'url' => '', 'module' => '', 'nonce' => '', 'title' => '' );
		$args = mcms_parse_args($args, $defaults);

		$this->type = $args['type'];
		$this->api = isset($args['api']) ? $args['api'] : array();

		parent::__construct($args);
	}

	/**
	 */
	public function before() {
		if ( !empty($this->api) )
			$this->upgrader->strings['process_success'] = sprintf( __('Successfully installed the module <strong>%s %s</strong>.'), $this->api->name, $this->api->version);
	}

	/**
	 */
	public function after() {
		$module_file = $this->upgrader->module_info();

		$install_actions = array();

		$from = isset($_GET['from']) ? mcms_unslash( $_GET['from'] ) : 'modules';

		if ( 'import' == $from ) {
			$install_actions['activate_module'] = '<a class="button button-primary" href="' . mcms_nonce_url( 'modules.php?action=activate&amp;from=import&amp;module=' . urlencode( $module_file ), 'activate-module_' . $module_file ) . '" target="_parent">' . __( 'Activate Module &amp; Run Importer' ) . '</a>';
		} else if ( 'press-this' == $from ) {
			$install_actions['activate_module'] = '<a class="button button-primary" href="' . mcms_nonce_url( 'modules.php?action=activate&amp;from=press-this&amp;module=' . urlencode( $module_file ), 'activate-module_' . $module_file ) . '" target="_parent">' . __( 'Activate Module &amp; Return to Press This' ) . '</a>';
		} else {
			$install_actions['activate_module'] = '<a class="button button-primary" href="' . mcms_nonce_url( 'modules.php?action=activate&amp;module=' . urlencode( $module_file ), 'activate-module_' . $module_file ) . '" target="_parent">' . __( 'Activate Module' ) . '</a>';
		}

		if ( is_multisite() && current_user_can( 'manage_network_modules' ) ) {
			$install_actions['network_activate'] = '<a class="button button-primary" href="' . mcms_nonce_url( 'modules.php?action=activate&amp;networkwide=1&amp;module=' . urlencode( $module_file ), 'activate-module_' . $module_file ) . '" target="_parent">' . __( 'Network Activate' ) . '</a>';
			unset( $install_actions['activate_module'] );
		}

		if ( 'import' == $from ) {
			$install_actions['importers_page'] = '<a href="' . admin_url( 'import.php' ) . '" target="_parent">' . __( 'Return to Importers' ) . '</a>';
		} elseif ( $this->type == 'web' ) {
			$install_actions['modules_page'] = '<a href="' . self_admin_url( 'module-install.php' ) . '" target="_parent">' . __( 'Return to Module Installer' ) . '</a>';
		} elseif ( 'upload' == $this->type && 'modules' == $from ) {
			$install_actions['modules_page'] = '<a href="' . self_admin_url( 'module-install.php' ) . '">' . __( 'Return to Module Installer' ) . '</a>';
		} else {
			$install_actions['modules_page'] = '<a href="' . self_admin_url( 'modules.php' ) . '" target="_parent">' . __( 'Return to Modules page' ) . '</a>';
		}

		if ( ! $this->result || is_mcms_error($this->result) ) {
			unset( $install_actions['activate_module'], $install_actions['network_activate'] );
		} elseif ( ! current_user_can( 'activate_module', $module_file ) ) {
			unset( $install_actions['activate_module'] );
		}

		/**
		 * Filters the list of action links available following a single module installation.
		 *
		 * @since 2.7.0
		 *
		 * @param array  $install_actions Array of module action links.
		 * @param object $api             Object containing MandarinCMS.org API module data. Empty
		 *                                for non-API installs, such as when a module is installed
		 *                                via upload.
		 * @param string $module_file     Path to the module file.
		 */
		$install_actions = apply_filters( 'install_module_complete_actions', $install_actions, $this->api, $module_file );

		if ( ! empty( $install_actions ) ) {
			$this->feedback( implode( ' ', (array) $install_actions ) );
		}
	}
}
