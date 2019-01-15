<?php
/**
 * @package MCMSSEO\admin|ajax
 */

/**
 * Class Ultimatum_OnPage_Ajax
 *
 * This class will catch the request to dismiss the OnPage.org notice and will store the dismiss status as an user meta
 * in the database
 */
class Ultimatum_OnPage_Ajax {

	/**
	 * Initialize the hooks for the AJAX request
	 */
	public function __construct() {
		add_action( 'mcms_ajax_mcmsseo_dismiss_onpageorg', array( $this, 'dismiss_notice' ) );
	}

	/**
	 * Handles the dismiss notice request
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'mcmsseo-dismiss-onpageorg' );

		$this->save_dismissed();

		mcms_die( 'true' );
	}

	/**
	 * Storing the dismissed value as an user option in the database
	 */
	private function save_dismissed() {
		update_user_meta( get_current_user_id(), MCMSSEO_OnPage::USER_META_KEY, 1 );
	}
}
