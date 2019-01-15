<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Enqueues a JavaScript module for UltimatumSEO.js that adds custom fields to the content that were defined in the titles
 * and meta's section of the Ultimatum SEO settings when those fields are available.
 */
class MCMSSEO_Custom_Fields_Module {

	/**
	 * Initialize the AJAX hooks
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueues all the needed JS scripts.
	 */
	public function enqueue() {
		mcms_enqueue_script( 'mcms-seo-premium-custom-fields-module', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcms-seo-premium-custom-fields-module-350' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery' ), MCMSSEO_VERSION );
		mcms_localize_script( 'mcms-seo-premium-custom-fields-module', 'UltimatumCustomFieldsModuleL10', $this->localize_script() );
	}

	/**
	 * Loads the custom fields translations
	 *
	 * @return array
	 */
	public function localize_script() {
		return array(
			'custom_field_names' => $this->get_custom_field_names(),
		);
	}

	/**
	 * Retrieve all custom field names set in SEO ->
	 *
	 * @return array
	 */
	private function get_custom_field_names() {
		$custom_field_names = array();

		$post          = $this->get_post();
		$options       = get_option( MCMSSEO_Options::get_option_instance( 'mcmsseo_titles' )->option_name, array() );

		if ( is_object( $post ) ) {
			$target_option = 'page-analyse-extra-' . $post->post_type;

			if ( array_key_exists( $target_option, $options ) ) {
				$custom_field_names = explode( ',', $options[ $target_option ] );
			}
		}

		return $custom_field_names;
	}

	/**
	 * Retrieves post data given a post ID or the global
	 *
	 * @return array|null|MCMS_Post Returns a post if found, otherwise returns an empty array.
	 */
	private function get_post() {
		if ( $post = filter_input( INPUT_GET, 'post' ) ) {
			$post_id = (int) MCMSSEO_Utils::validate_int( $post );

			return get_post( $post_id );
		}


		if ( isset( $GLOBALS['post'] ) ) {
			return $GLOBALS['post'];
		}

		return array();
	}
}
