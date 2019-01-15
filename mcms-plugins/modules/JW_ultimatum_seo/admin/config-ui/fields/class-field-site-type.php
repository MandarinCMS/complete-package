<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Site_Type
 */
class MCMSSEO_Config_Field_Site_Type extends MCMSSEO_Config_Field_Choice {

	/**
	 * MCMSSEO_Config_Field_Site_Type constructor.
	 */
	public function __construct() {
		parent::__construct( 'siteType' );

		/* translators: %1$s resolves to the home_url of the blog. */
		$this->set_property( 'label', sprintf( __( 'What kind of site is %1$s?', 'mandarincms-seo' ), get_home_url() ) );

		$this->add_choice( 'blog', __( 'Blog', 'mandarincms-seo' ) );
		$this->add_choice( 'shop', __( 'Webshop', 'mandarincms-seo' ) );
		$this->add_choice( 'news', __( 'News site', 'mandarincms-seo' ) );
		$this->add_choice( 'smallBusiness', __( 'Small business site', 'mandarincms-seo' ) );
		$this->add_choice( 'corporateOther', __( 'Other corporate site', 'mandarincms-seo' ) );
		$this->add_choice( 'personalOther', __( 'Other personal site', 'mandarincms-seo' ) );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo', 'site_type' );
	}
}
