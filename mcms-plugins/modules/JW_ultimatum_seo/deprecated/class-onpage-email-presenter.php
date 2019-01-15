<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * @deprecated
 *
 * This class is the presenter for the email. Based on the given parameters and data it will parse the email message
 * that will be send
 */
class MCMSSEO_OnPage_Email_Presenter {

	/**
	 * @deprecated 3.0.7
	 */
	public function __construct() {
		_deprecated_constructor( 'MCMSSEO_OnPage_Email_Presenter', 'MCMSSEO 3.0.7' );
	}

	/**
	 * @deprecated 3.0.7
	 */
	public function get_subject() {
		_deprecated_function( 'MCMSSEO_OnPage_Email_Presenter', 'MCMSSEO 3.0.7' );

		return array(
			'0' => '',
			'1' => '',
		);
	}

	/**
	 * @deprecated 3.0.7
	 */
	public function get_message() {
		_deprecated_function( 'MCMSSEO_OnPage_Email_Presenter', 'MCMSSEO 3.0.7' );

		return '';
	}
}
