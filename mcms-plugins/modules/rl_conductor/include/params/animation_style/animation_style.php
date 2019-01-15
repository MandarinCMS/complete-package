<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Class Vc_ParamAnimation
 *
 * For working with animations
 * array(
 *        'type' => 'animation_style',
 *        'heading' => __( 'Animation', 'rl_conductor' ),
 *        'param_name' => 'animation',
 * ),
 * Preview in http://daneden.github.io/animate.css/
 * @since 4.4
 */
class Vc_ParamAnimation {
	/**
	 * @since 4.4
	 * @var array $settings parameter settings from vc_map
	 */
	protected $settings;
	/**
	 * @since 4.4
	 * @var string $value parameter value
	 */
	protected $value;

	/**
	 * Define available animation effects
	 * @since 4.4
	 * vc_filter: vc_param_animation_style_list - to override animation styles
	 *     array
	 * @return array
	 */
	protected function animationStyles() {
		$styles = array(
			array(
				'values' => array(
					__( 'None', 'rl_conductor' ) => 'none',
				),
			),
			array(
				'label' => __( 'Attention Seekers', 'rl_conductor' ),
				'values' => array(
					// text to display => value
					__( 'bounce', 'rl_conductor' ) => array(
						'value' => 'bounce',
						'type' => 'other',
					),
					__( 'flash', 'rl_conductor' ) => array(
						'value' => 'flash',
						'type' => 'other',
					),
					__( 'pulse', 'rl_conductor' ) => array(
						'value' => 'pulse',
						'type' => 'other',
					),
					__( 'rubberBand', 'rl_conductor' ) => array(
						'value' => 'rubberBand',
						'type' => 'other',
					),
					__( 'shake', 'rl_conductor' ) => array(
						'value' => 'shake',
						'type' => 'other',
					),
					__( 'swing', 'rl_conductor' ) => array(
						'value' => 'swing',
						'type' => 'other',
					),
					__( 'tada', 'rl_conductor' ) => array(
						'value' => 'tada',
						'type' => 'other',
					),
					__( 'wobble', 'rl_conductor' ) => array(
						'value' => 'wobble',
						'type' => 'other',
					),
				),
			),
			array(
				'label' => __( 'Bouncing Entrances', 'rl_conductor' ),
				'values' => array(
					// text to display => value
					__( 'bounceIn', 'rl_conductor' ) => array(
						'value' => 'bounceIn',
						'type' => 'in',
					),
					__( 'bounceInDown', 'rl_conductor' ) => array(
						'value' => 'bounceInDown',
						'type' => 'in',
					),
					__( 'bounceInLeft', 'rl_conductor' ) => array(
						'value' => 'bounceInLeft',
						'type' => 'in',
					),
					__( 'bounceInRight', 'rl_conductor' ) => array(
						'value' => 'bounceInRight',
						'type' => 'in',
					),
					__( 'bounceInUp', 'rl_conductor' ) => array(
						'value' => 'bounceInUp',
						'type' => 'in',
					),
				),
			),
			array(
				'label' => __( 'Bouncing Exits', 'rl_conductor' ),
				'values' => array(
					// text to display => value
					__( 'bounceOut', 'rl_conductor' ) => array(
						'value' => 'bounceOut',
						'type' => 'out',
					),
					__( 'bounceOutDown', 'rl_conductor' ) => array(
						'value' => 'bounceOutDown',
						'type' => 'out',
					),
					__( 'bounceOutLeft', 'rl_conductor' ) => array(
						'value' => 'bounceOutLeft',
						'type' => 'out',
					),
					__( 'bounceOutRight', 'rl_conductor' ) => array(
						'value' => 'bounceOutRight',
						'type' => 'out',
					),
					__( 'bounceOutUp', 'rl_conductor' ) => array(
						'value' => 'bounceOutUp',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Fading Entrances', 'rl_conductor' ),
				'values' => array(
					// text to display => value
					__( 'fadeIn', 'rl_conductor' ) => array(
						'value' => 'fadeIn',
						'type' => 'in',
					),
					__( 'fadeInDown', 'rl_conductor' ) => array(
						'value' => 'fadeInDown',
						'type' => 'in',
					),
					__( 'fadeInDownBig', 'rl_conductor' ) => array(
						'value' => 'fadeInDownBig',
						'type' => 'in',
					),
					__( 'fadeInLeft', 'rl_conductor' ) => array(
						'value' => 'fadeInLeft',
						'type' => 'in',
					),
					__( 'fadeInLeftBig', 'rl_conductor' ) => array(
						'value' => 'fadeInLeftBig',
						'type' => 'in',
					),
					__( 'fadeInRight', 'rl_conductor' ) => array(
						'value' => 'fadeInRight',
						'type' => 'in',
					),
					__( 'fadeInRightBig', 'rl_conductor' ) => array(
						'value' => 'fadeInRightBig',
						'type' => 'in',
					),
					__( 'fadeInUp', 'rl_conductor' ) => array(
						'value' => 'fadeInUp',
						'type' => 'in',
					),
					__( 'fadeInUpBig', 'rl_conductor' ) => array(
						'value' => 'fadeInUpBig',
						'type' => 'in',
					),
				),
			),
			array(
				'label' => __( 'Fading Exits', 'rl_conductor' ),
				'values' => array(
					__( 'fadeOut', 'rl_conductor' ) => array(
						'value' => 'fadeOut',
						'type' => 'out',
					),
					__( 'fadeOutDown', 'rl_conductor' ) => array(
						'value' => 'fadeOutDown',
						'type' => 'out',
					),
					__( 'fadeOutDownBig', 'rl_conductor' ) => array(
						'value' => 'fadeOutDownBig',
						'type' => 'out',
					),
					__( 'fadeOutLeft', 'rl_conductor' ) => array(
						'value' => 'fadeOutLeft',
						'type' => 'out',
					),
					__( 'fadeOutLeftBig', 'rl_conductor' ) => array(
						'value' => 'fadeOutLeftBig',
						'type' => 'out',
					),
					__( 'fadeOutRight', 'rl_conductor' ) => array(
						'value' => 'fadeOutRight',
						'type' => 'out',
					),
					__( 'fadeOutRightBig', 'rl_conductor' ) => array(
						'value' => 'fadeOutRightBig',
						'type' => 'out',
					),
					__( 'fadeOutUp', 'rl_conductor' ) => array(
						'value' => 'fadeOutUp',
						'type' => 'out',
					),
					__( 'fadeOutUpBig', 'rl_conductor' ) => array(
						'value' => 'fadeOutUpBig',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Flippers', 'rl_conductor' ),
				'values' => array(
					__( 'flip', 'rl_conductor' ) => array(
						'value' => 'flip',
						'type' => 'other',
					),
					__( 'flipInX', 'rl_conductor' ) => array(
						'value' => 'flipInX',
						'type' => 'in',
					),
					__( 'flipInY', 'rl_conductor' ) => array(
						'value' => 'flipInY',
						'type' => 'in',
					),
					__( 'flipOutX', 'rl_conductor' ) => array(
						'value' => 'flipOutX',
						'type' => 'out',
					),
					__( 'flipOutY', 'rl_conductor' ) => array(
						'value' => 'flipOutY',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Lightspeed', 'rl_conductor' ),
				'values' => array(
					__( 'lightSpeedIn', 'rl_conductor' ) => array(
						'value' => 'lightSpeedIn',
						'type' => 'in',
					),
					__( 'lightSpeedOut', 'rl_conductor' ) => array(
						'value' => 'lightSpeedOut',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Rotating Entrances', 'rl_conductor' ),
				'values' => array(
					__( 'rotateIn', 'rl_conductor' ) => array(
						'value' => 'rotateIn',
						'type' => 'in',
					),
					__( 'rotateInDownLeft', 'rl_conductor' ) => array(
						'value' => 'rotateInDownLeft',
						'type' => 'in',
					),
					__( 'rotateInDownRight', 'rl_conductor' ) => array(
						'value' => 'rotateInDownRight',
						'type' => 'in',
					),
					__( 'rotateInUpLeft', 'rl_conductor' ) => array(
						'value' => 'rotateInUpLeft',
						'type' => 'in',
					),
					__( 'rotateInUpRight', 'rl_conductor' ) => array(
						'value' => 'rotateInUpRight',
						'type' => 'in',
					),
				),
			),
			array(
				'label' => __( 'Rotating Exits', 'rl_conductor' ),
				'values' => array(
					__( 'rotateOut', 'rl_conductor' ) => array(
						'value' => 'rotateOut',
						'type' => 'out',
					),
					__( 'rotateOutDownLeft', 'rl_conductor' ) => array(
						'value' => 'rotateOutDownLeft',
						'type' => 'out',
					),
					__( 'rotateOutDownRight', 'rl_conductor' ) => array(
						'value' => 'rotateOutDownRight',
						'type' => 'out',
					),
					__( 'rotateOutUpLeft', 'rl_conductor' ) => array(
						'value' => 'rotateOutUpLeft',
						'type' => 'out',
					),
					__( 'rotateOutUpRight', 'rl_conductor' ) => array(
						'value' => 'rotateOutUpRight',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Specials', 'rl_conductor' ),
				'values' => array(
					__( 'hinge', 'rl_conductor' ) => array(
						'value' => 'hinge',
						'type' => 'out',
					),
					__( 'rollIn', 'rl_conductor' ) => array(
						'value' => 'rollIn',
						'type' => 'in',
					),
					__( 'rollOut', 'rl_conductor' ) => array(
						'value' => 'rollOut',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Zoom Entrances', 'rl_conductor' ),
				'values' => array(
					__( 'zoomIn', 'rl_conductor' ) => array(
						'value' => 'zoomIn',
						'type' => 'in',
					),
					__( 'zoomInDown', 'rl_conductor' ) => array(
						'value' => 'zoomInDown',
						'type' => 'in',
					),
					__( 'zoomInLeft', 'rl_conductor' ) => array(
						'value' => 'zoomInLeft',
						'type' => 'in',
					),
					__( 'zoomInRight', 'rl_conductor' ) => array(
						'value' => 'zoomInRight',
						'type' => 'in',
					),
					__( 'zoomInUp', 'rl_conductor' ) => array(
						'value' => 'zoomInUp',
						'type' => 'in',
					),
				),
			),
			array(
				'label' => __( 'Zoom Exits', 'rl_conductor' ),
				'values' => array(
					__( 'zoomOut', 'rl_conductor' ) => array(
						'value' => 'zoomOut',
						'type' => 'out',
					),
					__( 'zoomOutDown', 'rl_conductor' ) => array(
						'value' => 'zoomOutDown',
						'type' => 'out',
					),
					__( 'zoomOutLeft', 'rl_conductor' ) => array(
						'value' => 'zoomOutLeft',
						'type' => 'out',
					),
					__( 'zoomOutRight', 'rl_conductor' ) => array(
						'value' => 'zoomOutRight',
						'type' => 'out',
					),
					__( 'zoomOutUp', 'rl_conductor' ) => array(
						'value' => 'zoomOutUp',
						'type' => 'out',
					),
				),
			),
			array(
				'label' => __( 'Slide Entrances', 'rl_conductor' ),
				'values' => array(
					__( 'slideInDown', 'rl_conductor' ) => array(
						'value' => 'slideInDown',
						'type' => 'in',
					),
					__( 'slideInLeft', 'rl_conductor' ) => array(
						'value' => 'slideInLeft',
						'type' => 'in',
					),
					__( 'slideInRight', 'rl_conductor' ) => array(
						'value' => 'slideInRight',
						'type' => 'in',
					),
					__( 'slideInUp', 'rl_conductor' ) => array(
						'value' => 'slideInUp',
						'type' => 'in',
					),
				),
			),
			array(
				'label' => __( 'Slide Exits', 'rl_conductor' ),
				'values' => array(
					__( 'slideOutDown', 'rl_conductor' ) => array(
						'value' => 'slideOutDown',
						'type' => 'out',
					),
					__( 'slideOutLeft', 'rl_conductor' ) => array(
						'value' => 'slideOutLeft',
						'type' => 'out',
					),
					__( 'slideOutRight', 'rl_conductor' ) => array(
						'value' => 'slideOutRight',
						'type' => 'out',
					),
					__( 'slideOutUp', 'rl_conductor' ) => array(
						'value' => 'slideOutUp',
						'type' => 'out',
					),
				),
			),
		);

		/**
		 * Used to override animation style list
		 * @since 4.4
		 */

		return apply_filters( 'vc_param_animation_style_list', $styles );
	}

	/**
	 * @param array $styles - array of styles to group
	 * @param string|array $type - what type to return
	 *
	 * @since 4.4
	 * @return array
	 */
	public function groupStyleByType( $styles, $type ) {
		$grouped = array();
		foreach ( $styles as $group ) {
			$inner_group = array( 'values' => array() );
			if ( isset( $group['label'] ) ) {
				$inner_group['label'] = $group['label'];
			}
			foreach ( $group['values'] as $key => $value ) {
				if ( ( is_array( $value ) && isset( $value['type'] ) && ( ( is_string( $type ) && $value['type'] == $type ) || is_array( $type ) && in_array( $value['type'], $type ) ) ) || ! is_array( $value ) || ! isset( $value['type'] ) ) {
					$inner_group['values'][ $key ] = $value;
				}
			}
			if ( ! empty( $inner_group['values'] ) ) {
				$grouped[] = $inner_group;
			}
		}

		return $grouped;
	}

	/**
	 * Set variables and register animate-css asset
	 * @since 4.4
	 *
	 * @param $settings
	 * @param $value
	 */
	public function __construct( $settings, $value ) {
		$this->settings = $settings;
		$this->value = $value;
		mcms_register_style( 'animate-css', vc_asset_url( 'lib/bower/animate-css/animate.min.css' ), array(), MCMSB_VC_VERSION );
	}

	/**
	 * Render edit form output
	 * @since 4.4
	 * @return string
	 */
	public function render() {
		$output = '<div class="vc_row">';
		mcms_enqueue_style( 'animate-css' );

		$styles = $this->animationStyles();
		if ( isset( $this->settings['settings']['type'] ) ) {
			$styles = $this->groupStyleByType( $styles, $this->settings['settings']['type'] );
		}
		if ( isset( $this->settings['settings']['custom'] ) && is_array( $this->settings['settings']['custom'] ) ) {
			$styles = array_merge( $styles, $this->settings['settings']['custom'] );
		}

		if ( is_array( $styles ) && ! empty( $styles ) ) {
			$left_side = '<div class="vc_col-sm-6">';
			$build_style_select = "\n" . '<select class="vc_param-animation-style">' . "\n";
			foreach ( $styles as $style ) {
				$build_style_select .= "\t\t" . '<optgroup ' . ( isset( $style['label'] ) ? 'label="' . $style['label'] . '"' : '' ) . '>' . "\n";
				if ( is_array( $style['values'] ) && ! empty( $style['values'] ) ) {
					foreach ( $style['values'] as $key => $value ) {
						$build_style_select .= "\t\t\t" . '<option value="' . ( is_array( $value ) ? $value['value'] : $value ) . '">' . $key . '</option>' . "\n";
					}
				}
				$build_style_select .= "\t\t" . '</optgroup>' . "\n";
			}
			$build_style_select .= '</select>' . "\n";
			$left_side .= $build_style_select;
			$left_side .= '</div>'; // Close left_side div
			$output .= $left_side;

			$right_side = '<div class="vc_col-sm-6">';
			$right_side .= '<div class="vc_param-animation-style-preview"><button class="vc_btn vc_btn-grey vc_btn-sm vc_param-animation-style-trigger">' . __( 'Animate it', 'rl_conductor' ) . '</button></div>';
			$right_side .= '</div>'; // Close right_side div
			$output .= $right_side;
		}

		$output .= '</div>'; // Close Row
		$output .= '<input name="' . $this->settings['param_name'] . '" class="mcmsb_vc_param_value  ' . $this->settings['param_name'] . ' ' . $this->settings['type'] . '_field" type="hidden" value="' . $this->value . '" ' . ' />';

		return $output;
	}
}

/**
 * Function for rendering param in edit form (add element)
 * Parse settings from vc_map and entered 'values'.
 *
 * @param array $settings - parameter settings in vc_map
 * @param string $value - parameter value
 * @param string $tag - shortcode tag
 *
 * vc_filter: vc_animation_style_render_filter - filter to override editor form
 *     field output
 *
 * @since 4.4
 * @return mixed|void rendered template for params in edit form
 *
 */
function vc_animation_style_form_field( $settings, $value, $tag ) {

	$field = new Vc_ParamAnimation( $settings, $value, $tag );

	/**
	 * Filter used to override full output of edit form field animation style
	 * @since 4.4
	 */

	return apply_filters( 'vc_animation_style_render_filter', $field->render(), $settings, $value, $tag );
}

