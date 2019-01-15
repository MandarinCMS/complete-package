<?php
/**
 * @package MCMSSEO\Premium|Classes
 */

/**
 * The metabox for premium
 */
class MCMSSEO_Premium_Metabox {

	/**
	 * Registers relevant hooks to MandarinCMS
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Registers assets to MandarinCMS
	 */
	public function register_assets() {
		mcms_register_script( MCMSSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcms-seo-premium-metabox-380' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery', 'mcms-util', 'underscore' ), MCMSSEO_VERSION );
		mcms_register_style( MCMSSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/css/dist/premium-metabox-380' . MCMSSEO_CSSJS_SUFFIX . '.css', array(), MCMSSEO_VERSION );
	}

	/**
	 * Enqueues assets when relevant
	 */
	public function enqueue_assets() {
		if ( MCMSSEO_Metabox::is_post_edit( $GLOBALS['pagenow'] ) ) {
			mcms_enqueue_script( MCMSSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox' );
			mcms_enqueue_style( MCMSSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox' );

			$this->send_data_to_assets();
		}
	}

	/**
	 * Send data to assets by using mcms_localize_script.
	 */
	public function send_data_to_assets() {
		$options = MCMSSEO_Options::get_option( 'mcmsseo' );
		$insights_enabled = ( isset( $options['enable_metabox_insights'] ) && $options['enable_metabox_insights'] );
		$language = MCMSSEO_Utils::get_language( get_locale() );

		if ( $language !== 'en' ) {
			$insights_enabled = false;
		}

		$data = array(
			'insightsEnabled' => ( $insights_enabled ) ? 'enabled' : 'disabled',
		);

		mcms_localize_script( MCMSSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox', 'mcmsseoPremiumMetaboxL10n', $data );
	}
}
