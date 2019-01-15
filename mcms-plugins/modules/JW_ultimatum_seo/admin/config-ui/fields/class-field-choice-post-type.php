<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Choice_Post_Type
 */
class MCMSSEO_Config_Field_Choice_Post_Type extends MCMSSEO_Config_Field_Choice {

	/** @var string Post type */
	protected $post_type;

	/**
	 * MCMSSEO_Config_Field_Choice_Post_Type constructor.
	 *
	 * @param string $post_type The post type to add.
	 * @param string $label     Label to show (translated post type).
	 */
	public function __construct( $post_type, $label ) {
		parent::__construct( 'postType' . ucfirst( $post_type ) );

		$this->post_type = $post_type;

		/* Translators: %1$s expands to the name of the post type. The options given to the user are "visible" and "hidden" */
		$this->set_property( 'label', sprintf( __( 'The post type "%1$s" should be', 'mandarincms-seo' ), $label ) );

		$this->add_choice( 'true', __( 'Visible', 'mandarincms-seo' ) );
		$this->add_choice( 'false', __( 'Hidden', 'mandarincms-seo' ) );
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
		);
	}

	/**
	 * Get the post type of this field.
	 *
	 * @return string Post type.
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * @return bool
	 */
	public function get_data() {
		$option = MCMSSEO_Options::get_option( 'mcmsseo_xml' );

		$key = 'post_types-' . $this->get_post_type() . '-not_in_sitemap';

		$storedData = ! isset( $option[ $key ] ) || false === $option[ $key ];

		if ( $storedData ) {
			return 'true';
		}

		return 'false';
	}

	/**
	 * Set new data
	 *
	 * @param string $visible Visible (true) or hidden (false).
	 *
	 * @return bool
	 */
	public function set_data( $visible ) {
		$post_type = $this->get_post_type();

		$option = MCMSSEO_Options::get_option( 'mcmsseo_xml' );

		$option[ 'post_types-' . $post_type . '-not_in_sitemap' ] = ( $visible === 'false' );

		update_option( 'mcmsseo_xml', $option );

		// Check if everything got saved properly.
		$saved_option = MCMSSEO_Options::get_option( 'mcmsseo_xml' );

		return ( ( $visible === 'false' ) && $saved_option[ 'post_types-' . $post_type . '-not_in_sitemap' ] === true );
	}
}
