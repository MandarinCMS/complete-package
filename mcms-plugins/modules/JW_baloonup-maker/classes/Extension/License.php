<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * License handler for BaloonUp Maker
 *
 * This class should simplify the process of adding license information to new BaloonUp Maker extensions.
 *
 * Note for mandarincms.org admins. This is not called in the free hosted version and is simply used for hooking in addons to one update system rather than including it in each module.
 *
 * @version 1.1
 */

class PUM_Extension_License {

	private $file;

	private $license;

	private $item_name;

	private $item_id;

	private $item_shortname;

	private $version;

	private $author;

	private $api_url = 'https://mcmsbaloonupmaker.com/edd-sl-api/';

	/**
	 * Class constructor
	 *
	 * @param string $_file
	 * @param string $_item
	 * @param string $_version
	 * @param string $_author
	 * @param string $_optname
	 * @param string $_api_url
	 */
	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {
		$this->file      = $_file;
		$this->item_name = $_item_name;

		if ( is_numeric( $_item_id ) ) {
			$this->item_id = absint( $_item_id );
		}

		$this->item_shortname = 'balooncreate_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( PUM_Options::get( $this->item_shortname . '_license_key', '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the modules had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactive their license.
		 */
		if ( ! empty( $_optname ) ) {
			$opt = PUM_Options::get( $_optname );

			if ( isset( $opt ) && empty( $this->license ) ) {
				$this->license = trim( $opt );
			}
		}

		// Setup hooks
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'pum_settings_fields', array( $this, 'settings' ), 1 );

		// Display help text at the top of the Licenses tab
		add_action( 'balooncreate_settings_tab_top', array( $this, 'license_help_text' ) );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		// Check that license is valid once per week
		add_action( 'balooncreate_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );

		// For testing license notices, uncomment this line to force checks on every page load
		//add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'in_module_update_message-' . module_basename( $this->file ), array(
			$this,
			'module_row_license_missing',
		), 10, 2 );

		// Register modules for beta support
		add_filter( 'pum_beta_enabled_extensions', array( $this, 'register_beta_support' ) );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {
		$args = array(
			'version' => $this->version,
			'license' => $this->license,
			'author'  => $this->author,
			'beta'    => PUM_Admin_Tools::extension_has_beta_support( $this->item_shortname ),
		);

		if ( ! empty( $this->item_id ) ) {
			$args['item_id'] = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		$balooncreate_updater = new PUM_Extension_Updater( $this->api_url, $this->file, $args );
	}


	/**
	 * Add license field to settings
	 *
	 * @access  public
	 *
	 * @param array $settings
	 *
	 * @return  array
	 */
	public function settings( $tabs = array() ) {
		$tabs['licenses']['main'][ $this->item_shortname . '_license_key' ] = array(
			'type'    => 'license_key',
			'label'   => sprintf( __( '%1$s', 'baloonup-maker' ), $this->item_name ),
			'options' => array(
				'is_valid_license_option' => $this->item_shortname . '_license_active',
				'activation_callback'     => array( $this, 'activate_license' ),
			),
		);

		return $tabs;
	}


	/**
	 * Display help text at the top of the Licenses tab
	 *
	 * @param   string $active_tab
	 *
	 * @return  void
	 */
	public function license_help_text( $active_tab = '' ) {
		// This global is here to ensure no double messaging while migration to the new class takes place in all of the extensions.
		global $pum_temp_license_help_text_global;

		static $has_ran;

		if ( ! isset( $has_ran ) && isset( $pum_temp_license_help_text_global ) ) {
			$has_ran = $pum_temp_license_help_text_global;
		}

		if ( 'licenses' !== $active_tab ) {
			return;
		}

		if ( isset( $has_ran ) ) {
			return;
		}

		echo '<p>' . sprintf( __( 'Enter your extension license keys here to receive updates for purchased extensions. If your license key has expired, please %srenew your license%s.', 'baloonup-maker' ), '<a href="https://docs.mcmsbaloonupmaker.com/article/177-license-renewal?utm_medium=license-help-text&utm_campaign=Licensing&utm_source=module-settings-page-licenses-tab" target="_blank">', '</a>' ) . '</p>';

		$has_ran                           = true;
		$pum_temp_license_help_text_global = true;
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {

		if ( ! isset( $_POST['pum_settings'] ) ) {
			return;
		}
		if ( ! isset( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		// Don't activate a key when deactivating a different key
		if ( ! empty( $_POST['pum_license_deactivate'] )) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$details = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] );

		if ( empty( $license ) && empty( $_POST['pum_license_activate'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url(),
		);

		// Call the API
		$response = mcms_remote_post( $this->api_url, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		// Make sure there are no errors
		if ( is_mcms_error( $response ) ) {
			return;
		}

		// Tell MandarinCMS to look for updates
		set_site_transient( 'update_modules', null );

		// Decode license data
		$license_data = json_decode( mcms_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );
	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['pum_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ 'pum_license_deactivate'][ $this->item_shortname . '_license_key' ] ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url(),
			);

			// Call the API
			$response = mcms_remote_post( $this->api_url, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// Make sure there are no errors
			if ( is_mcms_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( mcms_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_active' );

		}
	}


	/**
	 * Check if license key is valid once per week
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		if ( ! empty( $_POST['balooncreate_settings'] ) ) {
			return; // Don't fire when saving settings
		}

		if ( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $this->license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url(),
		);

		// Call the API
		$response = mcms_remote_post( $this->api_url, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		// make sure the response came back okay
		if ( is_mcms_error( $response ) ) {
			return;
		}

		$license_data = json_decode( mcms_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}


	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if ( empty( $this->license ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || $showed_invalid_message ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $license ) && 'valid' !== $license->license ) {

			if ( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {

				$messages[] = sprintf( __( 'You have invalid or expired license keys for BaloonUp Maker. Please go to the %sLicenses page%s to correct this issue.', 'baloonup-maker' ), '<a href="' . admin_url( 'edit.php?post_type=baloonup&page=pum-settings&tab=licenses' ) . '">', '</a>' );

				$showed_invalid_message = true;

			}

		}

		if ( ! empty( $messages ) ) {

			foreach ( $messages as $message ) {

				echo '<div class="error">';
				echo '<p>' . $message . '</p>';
				echo '</div>';

			}

		}

	}

	/**
	 * Displays message inline on module row that the license key is missing
	 */
	public function module_row_license_missing( $module_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {

			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=baloonup&page=pum-settings&tab=licenses' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'baloonup-maker' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}

	}

	/**
	 * Adds this module to the beta page
	 *
	 * @access  public
	 *
	 * @param   array $products
	 *
	 * @return array
	 */
	public function register_beta_support( $products ) {
		$products[ $this->item_shortname ] = $this->item_name;

		return $products;
	}

}
