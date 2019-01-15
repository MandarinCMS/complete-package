<?php
/**
 * @package MCMSSEO\Admin|Google_Search_Console
 */

/**
 * Class MCMSSEO_GSC_Ajax
 */
class MCMSSEO_GSC_Ajax {

	/**
	 * Setting the AJAX hooks for GSC
	 */
	public function __construct() {
		add_action( 'mcms_ajax_mcmsseo_mark_fixed_crawl_issue',  array( $this, 'ajax_mark_as_fixed' ) );
		add_action( 'mcms_ajax_mcmsseo_gsc_create_redirect_url', array( $this, 'ajax_create_redirect' ) );
		add_action( 'mcms_ajax_mcmsseo_dismiss_gsc', array( $this, 'dismiss_notice' ) );
		add_action( 'mcms_ajax_mcmsseo_save_auth_code', array( $this, 'save_auth_code' ) );
		add_action( 'mcms_ajax_mcmsseo_clear_auth_code', array( $this, 'clear_auth_code' ) );
		add_action( 'mcms_ajax_mcmsseo_get_profiles', array( $this, 'get_profiles' ) );
	}

	/**
	 * This method will be access by an AJAX request and will mark an issue as fixed.
	 *
	 * First it will do a request to the Google API
	 */
	public function ajax_mark_as_fixed() {
		if ( $this->valid_nonce() ) {
			$marker = new MCMSSEO_GSC_Marker( filter_input( INPUT_POST, 'url' ) );

			mcms_die( $marker->get_response() );
		}

		mcms_die( 'false' );
	}

	/**
	 * Handling the request to create a new redirect from the issued URL
	 */
	public function ajax_create_redirect() {
		if ( $this->valid_nonce() && class_exists( 'MCMSSEO_Redirect_Manager' ) && defined( 'MCMSSEO_PREMIUM_PATH' ) ) {
			$redirect_manager = new MCMSSEO_Redirect_Manager();

			$old_url = filter_input( INPUT_POST, 'old_url' );

			// Creates the redirect.
			$redirect = new MCMSSEO_Redirect( $old_url, filter_input( INPUT_POST, 'new_url' ), filter_input( INPUT_POST, 'type' ) );

			if ( $redirect_manager->create_redirect( $redirect ) ) {
				if ( filter_input( INPUT_POST, 'mark_as_fixed' ) === 'true' ) {
					new MCMSSEO_GSC_Marker( $old_url );
				}

				mcms_die( 'true' );
			}
		}

		mcms_die( 'false' );
	}

	/**
	 * Handle the AJAX request and dismiss the GSC notice
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'dismiss-gsc-notice' );

		update_user_meta( get_current_user_id(), 'mcmsseo_dismissed_gsc_notice', true );

		mcms_die( 'true' );
	}

	/**
	 * Saves the authorization code.
	 */
	public function save_auth_code() {
		if ( ! $this->valid_nonce() ) {
			mcms_die( '0' );
		}

		// Validate the authorization.
		$service                = $this->get_service();
		$authorization_code     = filter_input( INPUT_POST, 'authorization' );
		$is_authorization_valid = MCMSSEO_GSC_Settings::validate_authorization( $authorization_code, $service->get_client() );
		if ( ! $is_authorization_valid ) {
			mcms_die( '0' );
		}

		$this->get_profiles();
	}

	/**
	 * Clears all authorization data.
	 */
	public function clear_auth_code() {
		if ( ! $this->valid_nonce() ) {
			mcms_die( '0' );
		}

		$service = $this->get_service();

		MCMSSEO_GSC_Settings::clear_data( $service );

		$this->get_profiles();
	}

	/**
	 * Check if posted nonce is valid and return true if it is
	 *
	 * @return mixed
	 */
	private function valid_nonce() {
		return mcms_verify_nonce( filter_input( INPUT_POST, 'ajax_nonce' ), 'mcmsseo-gsc-ajax-security' );
	}

	/**
	 * Returns an instance of the Google Search Console service.
	 *
	 * @return MCMSSEO_GSC_Service
	 */
	private function get_service() {
		return new MCMSSEO_GSC_Service();
	}

	/**
	 * Prints a JSON encoded string with the current profile config.
	 */
	private function get_profiles() {
		$component = new MCMSSEO_Config_Component_Connect_Google_Search_Console();

		mcms_die( mcms_json_encode( $component->get_data() ) );
	}
}
