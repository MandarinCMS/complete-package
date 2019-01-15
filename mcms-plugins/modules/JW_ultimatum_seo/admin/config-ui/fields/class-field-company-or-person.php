<?php
/**
 * @package MCMSSEO\Admin\Configurator
 */

/**
 * Class MCMSSEO_Config_Field_Company_Or_Person
 */
class MCMSSEO_Config_Field_Company_Or_Person extends MCMSSEO_Config_Field_Choice {

	/**
	 * MCMSSEO_Config_Field_Company_Or_Person constructor.
	 */
	public function __construct() {
		parent::__construct( 'publishingEntityType' );

		$this->set_property( 'label', __( 'This data is shown as metadata in your site. It is intended to appear in Google\'s Knowledge Graph. You can be either a company, or a person, choose either:', 'mandarincms-seo' ) );

		$this->add_choice( 'company', __( 'Company', 'mandarincms-seo' ) );
		$this->add_choice( 'person', __( 'Person', 'mandarincms-seo' ) );
	}

	/**
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo', 'company_or_person' );
	}
}
