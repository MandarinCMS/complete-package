<?php
/**
 * @package MCMSSEO\Admin\Configurator
 */

/**
 * Class MCMSSEO_Config_Field_Company_Name
 */
class MCMSSEO_Config_Field_Company_Name extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Company_Name constructor.
	 */
	public function __construct() {
		parent::__construct( 'publishingEntityCompanyName', 'Input' );

		$this->set_property( 'label', __( 'The name of the company', 'mandarincms-seo' ) );

		$this->set_requires( 'publishingEntityType', 'company' );
	}

	/**
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo', 'company_name' );
	}
}
