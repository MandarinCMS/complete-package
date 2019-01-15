<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Post_Type_Visibility
 */
class MCMSSEO_Config_Field_Post_Type_Visibility extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Post_Type_Visibility constructor.
	 */
	public function __construct() {
		parent::__construct( 'postTypeVisibility', 'HTML' );

		$copy = __( 'Please specify which of the following public post types you would like Google to see.', 'mandarincms-seo' );

		$html = '<p>' . $copy . '</p><br/>';

		$this->set_property( 'html', $html );
	}
}
