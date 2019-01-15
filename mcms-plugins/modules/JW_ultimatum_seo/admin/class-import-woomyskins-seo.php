<?php
/**
 * @package MCMSSEO\Admin\Import\External
 */

/**
 * Class MCMSSEO_Import_WooMySkins_SEO
 *
 * Class with functionality to import Ultimatum SEO settings from WooMySkins SEO
 */
class MCMSSEO_Import_WooMySkins_SEO extends MCMSSEO_Import_External {

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->import_home();
		$this->import_option( 'seo_woo_single_layout', 'post' );
		$this->import_option( 'seo_woo_page_layout', 'page' );
		$this->import_archive_option();
		$this->import_custom_values( 'seo_woo_meta_home_desc', 'metadesc-home-mcmsseo' );
		$this->import_custom_values( 'seo_woo_meta_home_key', 'metakey-home-mcmsseo' );
		$this->import_metas();

		update_option( 'mcmsseo_titles', $this->options );

		$this->set_msg( __( 'WooMySkins SEO framework settings &amp; data successfully imported.', 'mandarincms-seo' ) );
	}

	/**
	 * Holds the MCMSSEO Title Options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Import options.
	 *
	 * @param string $option    Option key.
	 * @param string $post_type Post type name to import for.
	 */
	private function import_option( $option, $post_type ) {
		switch ( get_option( $option ) ) {
			case 'a':
				$this->options[ 'title-' . $post_type ] = '%%title%% %%sep%% %%sitename%%';
				break;
			case 'b':
				$this->options[ 'title-' . $post_type ] = '%%title%%';
				break;
			case 'c':
				$this->options[ 'title-' . $post_type ] = '%%sitename%% %%sep%% %%title%%';
				break;
			case 'd':
				$this->options[ 'title-' . $post_type ] = '%%title%% %%sep%% %%sitedesc%%';
				break;
			case 'e':
				$this->options[ 'title-' . $post_type ] = '%%sitename%% %%sep%% %%title%% %%sep%% %%sitedesc%%';
				break;
		}
		$this->perhaps_delete( $option );
	}

	/**
	 * Import the archive layout for all taxonomies
	 */
	private function import_archive_option() {
		$reinstate_replace = false;
		if ( $this->replace ) {
			$this->replace     = false;
			$reinstate_replace = true;
		}
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
			foreach ( $taxonomies as $tax ) {
				$this->import_option( 'seo_woo_archive_layout', 'tax-' . $tax );
			}
		}
		if ( $reinstate_replace ) {
			$this->replace = true;
			$this->perhaps_delete( 'seo_woo_archive_layout' );
		}
	}

	/**
	 * Import custom descriptions and meta keys
	 *
	 * @param string $option Option key.
	 * @param string $key    Internal key to import over.
	 */
	private function import_custom_values( $option, $key ) {
		// Import the custom homepage description.
		if ( 'c' == get_option( $option ) ) {
			$this->options[ $key ] = get_option( $option . '_custom' );
		}
		$this->perhaps_delete( $option );
		$this->perhaps_delete( $option . '_custom' );
	}

	/**
	 * Imports the WooMySkins SEO homepage settings
	 */
	private function import_home() {
		switch ( get_option( 'seo_woo_home_layout' ) ) {
			case 'a':
				$this->options['title-home-mcmsseo'] = '%%sitename%% %%sep%% %%sitedesc%%';
				break;
			case 'b':
				$this->options['title-home-mcmsseo'] = '%%sitename%% ' . get_option( 'seo_woo_paged_var' ) . ' %%pagenum%%';
				break;
			case 'c':
				$this->options['title-home-mcmsseo'] = '%%sitedesc%%';
				break;
		}
		$this->perhaps_delete( 'seo_woo_home_layout' );
	}

	/**
	 * Import meta values if they're applicable
	 */
	private function import_metas() {
		MCMSSEO_Meta::replace_meta( 'seo_follow', MCMSSEO_Meta::$meta_prefix . 'meta-robots-nofollow', $this->replace );
		MCMSSEO_Meta::replace_meta( 'seo_noindex', MCMSSEO_Meta::$meta_prefix . 'meta-robots-noindex', $this->replace );

		// If WooSEO is set to use the Woo titles, import those.
		if ( 'true' == get_option( 'seo_woo_mcms_title' ) ) {
			MCMSSEO_Meta::replace_meta( 'seo_title', MCMSSEO_Meta::$meta_prefix . 'title', $this->replace );
		}

		// If WooSEO is set to use the Woo meta descriptions, import those.
		if ( 'b' == get_option( 'seo_woo_meta_single_desc' ) ) {
			MCMSSEO_Meta::replace_meta( 'seo_description', MCMSSEO_Meta::$meta_prefix . 'metadesc', $this->replace );
		}

		// If WooSEO is set to use the Woo meta keywords, import those.
		if ( 'b' == get_option( 'seo_woo_meta_single_key' ) ) {
			MCMSSEO_Meta::replace_meta( 'seo_keywords', MCMSSEO_Meta::$meta_prefix . 'metakeywords', $this->replace );
		}

		foreach ( array( 'seo_woo_mcms_title', 'seo_woo_meta_single_desc', 'seo_woo_meta_single_key' ) as $option ) {
			$this->perhaps_delete( $option );
		}
	}
}
