<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

if ( class_exists( 'Ultimatum_Product' ) && ! class_exists( 'MCMSSEO_Product_Premium', false ) ) {

	/**
	 * Class MCMSSEO_Product_Premium
	 */
	class MCMSSEO_Product_Premium extends Ultimatum_Product {

		/**
		 * Construct the Product Premium class
		 */
		public function __construct() {
			$file = module_basename( MCMSSEO_FILE );
			$slug = dirname( $file );

			parent::__construct(
				trailingslashit( MCMSSEO_Premium::EDD_STORE_URL ) . 'edd-sl-api',
				MCMSSEO_Premium::EDD_PLUGIN_NAME,
				$slug,
				MCMSSEO_Premium::PLUGIN_VERSION_NAME,
				'https://jiiworks.net/mandarincms/modules/seo-premium/',
				'admin.php?page=mcmsseo_licenses#top#licenses',
				'mandarincms-seo',
				MCMSSEO_Premium::PLUGIN_AUTHOR,
				$file
			);
		}
	}

}
