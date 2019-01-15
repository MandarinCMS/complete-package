<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Social_Profiles_Intro
 */
class MCMSSEO_Config_Field_Social_Profiles_Intro extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Social_Profiles_Intro constructor.
	 */
	public function __construct() {
		parent::__construct( 'socialProfilesIntro', 'HTML' );

		$intro_text = __( 'Please add all your relevant social profiles. We use these to let search engines know about them, and to enhance your social metadata:', 'mandarincms-seo' );

		$html = '<p>' . $intro_text . '</p>';

		$this->set_property( 'html', $html );
	}
}
