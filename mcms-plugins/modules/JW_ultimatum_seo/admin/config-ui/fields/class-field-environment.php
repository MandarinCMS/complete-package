<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Environment
 */
class MCMSSEO_Config_Field_Environment extends MCMSSEO_Config_Field_Choice {
	/**
	 * MCMSSEO_Config_Field_Environment constructor.
	 */
	public function __construct() {
		parent::__construct( 'environment_type' );

		/* Translators: %1$s resolves to the home_url of the blog. */
		$this->set_property( 'label', sprintf( __( 'Please specify the environment in which this site - %1$s - is running.', 'mandarincms-seo' ), get_home_url() ) );

		$this->add_choice( 'production', __( 'Production (this is a live site with real traffic)', 'mandarincms-seo' ) );
		$this->add_choice( 'staging', __( 'Staging (this is a copy of a live site used for testing purposes only)', 'mandarincms-seo' ) );
		$this->add_choice( 'development', __( 'Development (this site is running locally for development purposes)', 'mandarincms-seo' ) );
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
	 * Gets the option that is set for this field.
	 *
	 * @return string The value for the environment_type mcmsseo option.
	 */
	public function get_data() {
		$option = MCMSSEO_Options::get_option( 'mcmsseo' );

		return $option['environment_type'];
	}

	/**
	 * Set new data.
	 *
	 * @param string $environment_type The site's environment type.
	 *
	 * @return bool Returns whether the value is successfully set.
	 */
	public function set_data( $environment_type ) {
		$option = MCMSSEO_Options::get_option( 'mcmsseo' );

		if ( $option['environment_type'] !== $environment_type ) {
			$option['environment_type'] = $environment_type;
			update_option( 'mcmsseo', $option );
			if ( ! $this->set_indexation( $environment_type ) ) {
				return false;
			}
		}

		$saved_environment_option = MCMSSEO_Options::get_option( 'mcmsseo' );

		return ( $saved_environment_option['environment_type'] === $option['environment_type'] );
	}

	/**
	 * Set the MandarinCMS Search Engine Visibility option based on the environment type.
	 *
	 * @param string $environment_type The environment the site is running in.
	 *
	 * @return bool Returns if the options is set successfully.
	 */
	protected function set_indexation( $environment_type ) {
		$new_blog_public_value     = 0;
		$current_blog_public_value = get_option( 'blog_public' );

		if ( $environment_type === 'production' ) {
			$new_blog_public_value = 1;
		}

		if ( $current_blog_public_value !== $new_blog_public_value ) {
			update_option( 'blog_public', $new_blog_public_value );

			return true;
		}
		$saved_blog_public_value = get_option( 'blog_public' );

		return ( $saved_blog_public_value === $new_blog_public_value );
	}
}
