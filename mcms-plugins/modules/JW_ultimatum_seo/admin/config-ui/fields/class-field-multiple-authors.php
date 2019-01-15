<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Multiple_Authors
 */
class MCMSSEO_Config_Field_Multiple_Authors extends MCMSSEO_Config_Field_Choice {
	/**
	 * MCMSSEO_Config_Field_Multiple_Authors constructor.
	 */
	public function __construct() {
		parent::__construct( 'multipleAuthors' );

		$this->set_property( 'label', __( 'Does, or will, your site have multiple authors?', 'mandarincms-seo' ) );

		$this->add_choice( 'yes', __( 'Yes', 'mandarincms-seo' ) );
		$this->add_choice( 'no', __( 'No', 'mandarincms-seo' ) );
	}

	/**
	 * Set adapter.
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_custom_lookup(
			$this->get_identifier(),
			array( $this, 'get_data' ),
			array( $this, 'set_data' )
		);
	}

	/**
	 * Get the data from the stored options.
	 *
	 * @return null|string
	 */
	public function get_data() {

		$option = MCMSSEO_Options::get_option( 'mcmsseo' );
		if ( isset( $option['has_multiple_authors'] ) ) {
			$value = $option['has_multiple_authors'];
		}

		if ( ! isset( $value ) || is_null( $value ) ) {
			// If there are more than one users with level > 1 default to multiple authors.
			$users = get_users( array(
				'fields' => 'IDs',
				'who'    => 'authors',
			) );

			$value = count( $users ) > 1;
		}

		return ( $value ) ? 'yes' : 'no';
	}

	/**
	 * Set the data in the options.
	 *
	 * @param {string} $data The data to set for the field.
	 *
	 * @return bool Returns true or false for successful storing the data.
	 */
	public function set_data( $data ) {
		$value = ( $data === 'yes' );

		// Set multiple authors option.
		$result_multiple_authors = MCMSSEO_Options::save_option( 'mcmsseo', 'has_multiple_authors', $value );

		/*
		 * Set disable author archives option. When multiple authors is set to true,
		 * the disable author option has to be false. Because of this the $value is inversed.
		 */
		$result_author_archives = MCMSSEO_Options::save_option( 'mcmsseo_titles', 'disable-author', ! $value );

		return ( $result_multiple_authors === true && $result_author_archives === true );
	}
}
