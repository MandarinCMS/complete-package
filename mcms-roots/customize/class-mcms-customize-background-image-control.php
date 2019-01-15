<?php
/**
 * Customize API: MCMS_Customize_Background_Image_Control class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Background Image Control class.
 *
 * @since 3.4.0
 *
 * @see MCMS_Customize_Image_Control
 */
class MCMS_Customize_Background_Image_Control extends MCMS_Customize_Image_Control {
	public $type = 'background';

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses MCMS_Customize_Image_Control::__construct()
	 *
	 * @param MCMS_Customize_Manager $manager Customizer bootstrap instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager, 'background_image', array(
			'label'    => __( 'Background Image' ),
			'section'  => 'background_image',
		) );
	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 4.1.0
	 */
	public function enqueue() {
		parent::enqueue();

		$custom_background = get_myskin_support( 'custom-background' );
		mcms_localize_script( 'customize-controls', '_mcmsCustomizeBackground', array(
			'defaults' => ! empty( $custom_background[0] ) ? $custom_background[0] : array(),
			'nonces' => array(
				'add' => mcms_create_nonce( 'background-add' ),
			),
		) );
	}
}
