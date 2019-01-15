<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Profile_URL_Facebook
 */
class MCMSSEO_Config_Field_Profile_URL_Facebook extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Profile_URL_Facebook constructor.
	 */
	public function __construct() {
		parent::__construct( 'profileUrlFacebook', 'Input' );

		$this->set_property( 'label', __( 'Facebook Page URL', 'mandarincms-seo' ) );
		$this->set_property( 'pattern', '^https:\/\/www\.facebook\.com\/([^/]+)\/$' );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_social', 'facebook_site' );
	}
}
