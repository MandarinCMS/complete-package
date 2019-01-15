<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Site_Name
 */
class MCMSSEO_Config_Field_Site_Name extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Site_Name constructor.
	 */
	public function __construct() {
		parent::__construct( 'siteName', 'Input' );

		$this->set_property( 'label', __( 'Website name', 'mandarincms-seo' ) );
		$this->set_property( 'explanation', __( 'Google shows your website\'s name in the search results, if you want to change it, you can do that here.', 'mandarincms-seo' ) );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_custom_lookup(
			$this->get_identifier(),
			array( $this, 'get_data' ),
			array( $this, 'set_data' )
		);	}

	/**
	 * Get the data from the stored options.
	 *
	 * @return null|string
	 */
	public function get_data() {
		$option = MCMSSEO_Options::get_option( 'mcmsseo' );
		if ( ! empty( $option['website_name'] ) ) {
			return $option['website_name'];
		}

		return get_bloginfo( 'name' );
	}

	/**
	 * Set the data in the options.
	 *
	 * @param {string} $data The data to set for the field.
	 *
	 * @return bool Returns true or false for successful storing the data.
	 */
	public function set_data( $data ) {
		$value = $data;

		$option                   = MCMSSEO_Options::get_option( 'mcmsseo' );
		$option['website_name'] = $value;

		update_option( 'mcmsseo', $option );

		// Check if everything got saved properly.
		$saved_option = MCMSSEO_Options::get_option( 'mcmsseo' );

		return ( $saved_option['website_name'] === $option['website_name'] );
	}
}
