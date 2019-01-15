<?php
/**
 * @package MCMSSEO\Admin\Import\External
 */

/**
 * Class MCMSSEO_Import_WooMySkins_SEO
 *
 * Class with functionality to import Ultimatum SEO settings from WooMySkins SEO
 */
class MCMSSEO_Import_AIOSEO extends MCMSSEO_Import_External {

	/**
	 * Holds the AOIOSEO options
	 *
	 * @var array
	 */
	private $aioseo_options;

	/**
	 * Import All In One SEO settings
	 */
	public function __construct() {
		parent::__construct();

		$this->aioseo_options = get_option( 'aioseop_options' );

		$this->import_metas();
		$this->import_ga();
	}

	/**
	 * Import All In One SEO meta values
	 */
	private function import_metas() {
		MCMSSEO_Meta::replace_meta( '_aioseop_description', MCMSSEO_Meta::$meta_prefix . 'metadesc', $this->replace );
		MCMSSEO_Meta::replace_meta( '_aioseop_keywords', MCMSSEO_Meta::$meta_prefix . 'metakeywords', $this->replace );
		MCMSSEO_Meta::replace_meta( '_aioseop_title', MCMSSEO_Meta::$meta_prefix . 'title', $this->replace );
	}

	/**
	 * Import the Google Analytics settings
	 */
	private function import_ga() {
		if ( isset( $this->aioseo_options['aiosp_google_analytics_id'] ) ) {

			if ( get_option( 'yst_ga' ) === false ) {
				update_option( 'yst_ga', $this->determine_ga_settings() );
			}

			$module_install_nonce = mcms_create_nonce( 'install-module_google-analytics-for-mandarincms' ); // Use the old name because that's the MandarinCMS.org repo.

			$this->set_msg( __( sprintf(
				'All in One SEO data successfully imported. Would you like to %sdisable the All in One SEO module%s. You\'ve had Google Analytics enabled in All in One SEO, would you like to install our %sGoogle Analytics module%s?',
				'<a href="' . esc_url( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export&deactivate_aioseo=1#top#import-seo' ) ) . '">',
				'</a>',
				'<a href="' . esc_url( admin_url( 'update.php?action=install-module&module=google-analytics-for-mandarincms&_mcmsnonce=' . $module_install_nonce ) ) . '">',
				'</a>'
			), 'mandarincms-seo' ) );
		}
		else {
			$this->set_msg( __( sprintf( 'All in One SEO data successfully imported. Would you like to %sdisable the All in One SEO module%s.', '<a href="' . esc_url( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export&deactivate_aioseo=1#top#import-seo' ) ) . '">', '</a>' ), 'mandarincms-seo' ) );
		}
	}

	/**
	 * Determine the appropriate GA settings for this site
	 *
	 * @return array $ga_settings
	 */
	private function determine_ga_settings() {
		$ga_universal = 0;
		if ( $this->aioseo_options['aiosp_ga_use_universal_analytics'] == 'on' ) {
			$ga_universal = 1;
		}

		$ga_track_outbound = 0;
		if ( $this->aioseo_options['aiosp_ga_track_outbound_links'] == 'on' ) {
			$ga_track_outbound = 1;
		}

		$ga_anonymize_ip = 0;
		if ( $this->aioseo_options['aiosp_ga_anonymize_ip'] == 'on' ) {
			$ga_anonymize_ip = 1;
		}

		return array(
			'ga_general' => array(
				'manual_ua_code'       => (int) 1,
				'manual_ua_code_field' => $this->aioseo_options['aiosp_google_analytics_id'],
				'enable_universal'     => $ga_universal,
				'track_outbound'       => $ga_track_outbound,
				'ignore_users'         => (array) $this->aioseo_options['aiosp_ga_exclude_users'],
				'anonymize_ips'        => (int) $ga_anonymize_ip,
			),
		);
	}
}
