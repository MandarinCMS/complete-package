<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Profile_URL_LinkedIn
 */
class MCMSSEO_Config_Field_Profile_URL_LinkedIn extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Profile_URL_LinkedIn constructor.
	 */
	public function __construct() {
		parent::__construct( 'profileUrlLinkedIn', 'Input' );

		$this->set_property( 'label', __( 'LinkedIn URL', 'mandarincms-seo' ) );
		$this->set_property( 'pattern', '^https:\/\/www\.linkedin\.com\/in\/([^/]+)$' );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_social', 'linkedin_url' );
	}
}
