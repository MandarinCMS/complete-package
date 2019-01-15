<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Profile_URL_GooglePlus
 */
class MCMSSEO_Config_Field_Profile_URL_GooglePlus extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Profile_URL_GooglePlus constructor.
	 */
	public function __construct() {
		parent::__construct( 'profileUrlGooglePlus', 'Input' );

		$this->set_property( 'label', __( 'Google+ URL', 'mandarincms-seo' ) );
		$this->set_property( 'pattern', '^https:\/\/plus\.google\.com\/([^/]+)$' );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_social', 'google_plus_url' );
	}
}
