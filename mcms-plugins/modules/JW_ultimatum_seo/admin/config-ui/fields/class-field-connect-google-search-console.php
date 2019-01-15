<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Connect_Google_Search_Console
 */
class MCMSSEO_Config_Field_Connect_Google_Search_Console extends MCMSSEO_Config_Field {
	/**
	 * MCMSSEO_Config_Field_Connect_Google_Search_Console constructor.
	 */
	public function __construct() {
		parent::__construct( 'connectGoogleSearchConsole', 'ConnectGoogleSearchConsole' );
	}

	/**
	 * Get the data
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'profile'     => '',
			'profileList' => '',
		);
	}
}
