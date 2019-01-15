<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Profile_URL_Twitter
 */
class MCMSSEO_Config_Field_Profile_URL_Twitter extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Profile_URL_Twitter constructor.
	 */
	public function __construct() {
		parent::__construct( 'profileUrlTwitter', 'Input' );

		$this->set_property( 'label', __( 'Twitter Username', 'mandarincms-seo' ) );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_social', 'twitter_site' );
	}
}
