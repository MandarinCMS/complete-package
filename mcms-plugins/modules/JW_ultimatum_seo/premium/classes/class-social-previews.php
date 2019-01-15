<?php
/**
 * @package Premium
 */

/**
 * Initializer for the social previews.
 */
class MCMSSEO_Social_Previews {

	/**
	 * Enqueues the assets.
	 */
	public function set_hooks() {
		$this->register_assets();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Sets the hooks necessary for AJAX
	 */
	public function set_ajax_hooks() {
		add_action( 'mcms_ajax_retrieve_image_data_from_url', array( $this, 'ajax_retrieve_image_data_from_url' ) );
	}

	/**
	 * Enqueues the javascript and css files needed for the social previews.
	 */
	public function enqueue_assets() {
		mcms_enqueue_style( 'ultimatum-social-preview-css' );
		mcms_enqueue_style( 'ultimatum-premium-social-preview' );
		mcms_enqueue_script( 'ultimatum-social-preview' );
	}

	/**
	 * Retrieves image data from an image URL
	 */
	public function ajax_retrieve_image_data_from_url() {
		$url = filter_input( INPUT_GET, 'imageURL' );

		$attachment_id = $this->retrieve_image_id_from_url( $url );

		if ( $attachment_id ) {
			$image = mcms_get_attachment_image_src( $attachment_id, 'full' );

			$result = array(
				'status' => 'success',
				'result' => $image[0],
			);
		}
		else {
			// Pass the original URL for consistency.
			$result = array(
				'status' => 'success',
				'result' => $url,
			);
		}

		mcms_die( mcms_json_encode( $result ) );
	}

	/**
	 * Determines an attachment ID from a URL which might be an attachment URL
	 *
	 * @link https://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-mandarincms/
	 *
	 * @param string $url The URL to retrieve the attachment ID for.
	 *
	 * @return bool|int The attachment ID or false.
	 */
	public function retrieve_image_id_from_url( $url ) {
		global $mcmsdb;

		$attachment_id = false;

		// Get the upload directory paths.
		$upload_dir_paths = mcms_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image.
		if ( false !== strpos( $url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image.
			$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url );

			// Remove the upload path base directory from the attachment URL.
			$url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL.
			$attachment_id = $mcmsdb->get_var( $mcmsdb->prepare( "SELECT mcmsosts.ID FROM $mcmsdb->posts mcmsosts, $mcmsdb->postmeta mcmsostmeta WHERE mcmsosts.ID = mcmsostmeta.post_id AND mcmsostmeta.meta_key = '_mcms_attached_file' AND mcmsostmeta.meta_value = '%s' AND mcmsosts.post_type = 'attachment'", $url ) );

		}

		return (int) $attachment_id;
	}

	/**
	 * Register the required assets.
	 */
	private function register_assets() {
		mcms_register_script( 'ultimatum-social-preview', module_dir_url( MCMSSEO_PREMIUM_FILE ) . '/assets/js/dist/ultimatum-premium-social-preview-380' . MCMSSEO_CSSJS_SUFFIX . '.js', array(
			'jquery',
			'jquery-ui-core',
		), MCMSSEO_VERSION );

		mcms_localize_script( 'ultimatum-social-preview', 'ultimatumSocialPreview', $this->localize() );

		$deps = array( MCMSSEO_Admin_Asset_Manager::PREFIX . 'metabox-css' );

		mcms_register_style( 'ultimatum-social-preview-css', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/dist/social_preview/ultimatum-social-preview-' . '350' . '.min.css', $deps, MCMSSEO_VERSION );
		mcms_register_style( 'ultimatum-premium-social-preview', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/css/dist/premium-social-preview-' . '340' . '.min.css', $deps, MCMSSEO_VERSION );
	}

	/**
	 * Returns the translations.
	 *
	 * @return array
	 */
	private function localize() {
		$options = MCMSSEO_Options::get_option( 'mcmsseo_social' );

		if ( empty( MCMSSEO_Social_Admin::$meta_fields['social']['opengraph-title']['description'] ) ) {
			MCMSSEO_Social_Admin::translate_meta_boxes();
		}

		$social = MCMSSEO_Social_Admin::$meta_fields['social'];

		return array(
			'website'               => $this->get_website(),
			'uploadImage'           => __( 'Upload image', 'mandarincms-seo-premium' ),
			'useOtherImage'         => __( 'Use other image', 'mandarincms-seo-premium' ),
			'removeImageButton'     => __( 'Remove image', 'mandarincms-seo-premium' ),
			'facebookDefaultImage'  => $options['og_default_image'],
			'i18n' => array(
				'help' => $this->get_help_translations( $social ),
				'helpButton' => array(
					'facebookTitle'       => __( 'Show information about Facebook title', 'mandarincms-seo-premium' ),
					'facebookDescription' => __( 'Show information about Facebook description', 'mandarincms-seo-premium' ),
					'facebookImage'       => __( 'Show information about Facebook image', 'mandarincms-seo-premium' ),
					'twitterTitle'        => __( 'Show information about Twitter title', 'mandarincms-seo-premium' ),
					'twitterDescription'  => __( 'Show information about Twitter description', 'mandarincms-seo-premium' ),
					'twitterImage'        => __( 'Show information about Twitter image', 'mandarincms-seo-premium' ),
				),
				'library' => $this->get_translations(),
			),
			'facebookNonce' => mcms_create_nonce( 'get_facebook_name' ),
		);
	}

	/**
	 * Gets the help translations.
	 *
	 * @param array $social_field The social fields that are available.
	 *
	 * @return array Translations to be used in the social previews.
	 */
	private function get_help_translations( $social_field ) {
		// Default everything to empty strings.
		$localized = array();

		if ( isset( $social_field['opengraph-title'] ) ) {
			$localized['facebookTitle']       = $social_field['opengraph-title']['description'];
			$localized['facebookDescription'] = $social_field['opengraph-description']['description'];
			$localized['facebookImage']       = $social_field['opengraph-image']['description'];
		}

		if ( isset( $social_field['twitter-title'] ) ) {
			$localized['twitterTitle']       = $social_field['twitter-title']['description'];
			$localized['twitterDescription'] = $social_field['twitter-description']['description'];
			$localized['twitterImage']       = $social_field['twitter-image']['description'];
		}

		return $localized;
	}

	/**
	 * Get the website hostname.
	 *
	 * @return string
	 */
	private function get_website() {
		// We only want the host part of the URL.
		$website = parse_url( home_url(), PHP_URL_HOST );
		$website = trim( $website, '/' );
		$website = strtolower( $website );

		return $website;
	}

	/**
	 * Returns Jed compatible UltimatumSEO.js translations.
	 *
	 * @return array
	 */
	private function get_translations() {
		$file = module_dir_path( MCMSSEO_FILE ) . 'premium/languages/mandarincms-seo-premium-' . get_locale() . '.json';
		if ( file_exists( $file ) && $file = file_get_contents( $file ) ) {
			return json_decode( $file, true );
		}

		return array();
	}
}
