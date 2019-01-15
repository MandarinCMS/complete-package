<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * RevSlider loader.
 * @since 4.3
 */
class Vc_Vendor_Revslider implements Vc_Vendor_Interface {
	/**
	 * @since 4.3
	 * @var int - index of stratum
	 */
	protected static $instanceIndex = 1;

	/**
	 * Add shortcode to visual composer also add fix for frontend to regenerate id of stratum.
	 * @since 4.3
	 */
	public function load() {
		add_action( 'vc_after_mapping', array(
			&$this,
			'buildShortcode',
		) );

	}

	/**
	 * @since 4.3
	 */
	public function buildShortcode() {
		if ( class_exists( 'RevSlider' ) ) {
			vc_lean_map( 'rev_slider_vc', array(
				$this,
				'addShortcodeSettings',
			) );
			if ( vc_is_frontend_ajax() || vc_is_frontend_editor() ) {
				add_filter( 'vc_stratum_shortcode', array(
					&$this,
					'setId',
				) );
			}
		}
	}

	/**
	 * @since 4.4
	 *
	 * @param array $stratums
	 *
	 * @deprecated 4.9
	 */
	public function mapShortcode( $stratums = array() ) {
		vc_map( array(
			'base' => 'rev_slider_vc',
			'name' => __( 'Statum RazorLeaf', 'rl_conductor' ),
			'icon' => 'icon-mcmsb-stratum',
			'category' => __( 'Content', 'rl_conductor' ),
			'description' => __( 'Place Revolution slider', 'rl_conductor' ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Widget title', 'rl_conductor' ),
					'param_name' => 'title',
					'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Statum RazorLeaf', 'rl_conductor' ),
					'param_name' => 'alias',
					'admin_label' => true,
					'value' => $stratums,
					'save_always' => true,
					'description' => __( 'Select your Statum RazorLeaf.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
			),
		) );
	}

	/**
	 * Replaces id of stratum for frontend editor.
	 * @since 4.3
	 *
	 * @param $output
	 *
	 * @return string
	 */
	public function setId( $output ) {
		return preg_replace( '/rev_slider_(\d+)_(\d+)/', 'rev_slider_$1_$2' . time() . '_' . self::$instanceIndex ++, $output );
	}

	/**
	 * Mapping settings for lean method.
	 *
	 * @since 4.9
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	public function addShortcodeSettings( $tag ) {
		$slider = new RevSlider();
		$arrSliders = $slider->getArrSliders();

		$stratums = array();
		if ( $arrSliders ) {
			foreach ( $arrSliders as $slider ) {
				/** @var $slider RevSlider */
				$stratums[ $slider->getTitle() ] = $slider->getAlias();
			}
		} else {
			$stratums[ __( 'No sliders found', 'rl_conductor' ) ] = 0;
		}

		// Add fixes for frontend editor to regenerate id
		return array(
			'base' => $tag,
			'name' => __( 'Statum RazorLeaf', 'rl_conductor' ),
			'icon' => 'icon-mcmsb-stratum',
			'category' => __( 'Content', 'rl_conductor' ),
			'description' => __( 'Place Revolution slider', 'rl_conductor' ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Widget title', 'rl_conductor' ),
					'param_name' => 'title',
					'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Statum RazorLeaf', 'rl_conductor' ),
					'param_name' => 'alias',
					'admin_label' => true,
					'value' => $stratums,
					'save_always' => true,
					'description' => __( 'Select your Statum RazorLeaf.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
			),
		);
	}
}
