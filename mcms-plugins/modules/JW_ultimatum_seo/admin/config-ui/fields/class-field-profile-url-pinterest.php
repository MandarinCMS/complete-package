<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Profile_URL_Pinterest
 */
class MCMSSEO_Config_Field_Profile_URL_Pinterest extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Profile_URL_Pinterest constructor.
	 */
	public function __construct() {
		parent::__construct( 'profileUrlPinterest', 'Input' );

		$this->set_property( 'label', __( 'Pinterest URL', 'mandarincms-seo' ) );
		$this->set_property( 'pattern', '^https:\/\/www\.pinterest\.com\/([^/]+)\/$' );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_social', 'pinterest_url' );
	}
}
