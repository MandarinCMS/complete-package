<?php
/**
 * @package MCMSSEO\Admin\Configurator
 */

/**
 * Class MCMSSEO_Config_Field_Company_Logo
 */
class MCMSSEO_Config_Field_Company_Logo extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Company_Logo constructor.
	 */
	public function __construct() {
		parent::__construct( 'publishingEntityCompanyLogo', 'MediaUpload' );

		$this->set_property( 'label', __( 'Provide an image of the company logo', 'mandarincms-seo' ) );

		$this->set_requires( 'publishingEntityType', 'company' );
	}

	/**
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo', 'company_logo' );
	}
}
