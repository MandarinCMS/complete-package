<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Upsell_Configuration_Service
 */
class MCMSSEO_Config_Field_Upsell_Configuration_Service extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Upsell_Configuration_Service constructor.
	 */
	public function __construct() {
		parent::__construct( 'upsellConfigurationService', 'HTML' );

		$intro_text = __( 'Welcome to the Ultimatum SEO configuration wizard. In a few simple steps we\'ll help you configure your SEO settings to match your website\'s needs!', 'mandarincms-seo' );

		/* Translators: %1$s opens the link, %2$s closes the link. */
		$upsell_text = sprintf(
			__( 'While we strive to make setting up Ultimatum SEO as easy as possible, we understand it can be daunting. If you’d rather have us set up Ultimatum SEO for you (and get a copy of Ultimatum SEO in the process), order our %1$sUltimatum SEO configuration service%2$s here!', 'mandarincms-seo' ),
			'<a target="_blank" href="https://jiiworks.net/configuration-package">',
			'</a>'
		);

		$html = '<p>' . $intro_text . '</p>';
		$html .= '<p><em>' . $upsell_text . '</em></p>';


		$this->set_property( 'html', $html );
	}
}
