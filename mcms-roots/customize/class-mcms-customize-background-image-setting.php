<?php
/**
 * Customize API: MCMS_Customize_Background_Image_Setting class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customizer Background Image Setting class.
 *
 * @since 3.4.0
 *
 * @see MCMS_Customize_Setting
 */
final class MCMS_Customize_Background_Image_Setting extends MCMS_Customize_Setting {
	public $id = 'background_image_thumb';

	/**
	 * @since 3.4.0
	 *
	 * @param $value
	 */
	public function update( $value ) {
		remove_myskin_mod( 'background_image_thumb' );
	}
}
