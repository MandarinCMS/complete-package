<?php

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Shortcode_BaloonUp
 *
 * Registers the baloonup_close shortcode.
 */
class PUM_Shortcode_BaloonUp extends PUM_Shortcode {

	public $version = 2;

	public $has_content = true;

	public $inner_content_priority = 15;

	/**
	 * The shortcode tag.
	 */
	public function tag() {
		return 'baloonup';
	}

	public function label() {
		return __( 'BaloonUp', 'baloonup-maker' );
	}

	public function description() {
		return __( 'Insert a baloonup inline rather. Great for simple baloonups used for supporting content.', 'baloonup-maker' );
	}

	public function inner_content_labels() {
		return array(
			'label'       => __( 'Content', 'baloonup-maker' ),
			'description' => __( 'Can contain other shortcodes, images, text or html content.', 'baloonup-maker' ),
		);
	}

	public function post_types() {
		return array();
	}

	/**
	 * @return array
	 */
	public function tabs() {
		return array(
			'general'   => __( 'General', 'baloonup-maker' ),
			'display'   => __( 'Display', 'baloonup-maker' ),
			'position'  => __( 'Position', 'baloonup-maker' ),
			'animation' => __( 'Animation', 'baloonup-maker' ),
			'close'     => __( 'Close', 'baloonup-maker' ),
		);
	}

	/**
	 * @return array
	 */
	public function subtabs() {
		return apply_filters( 'pum_sub_form_shortcode_subtabs', array(
			'general'   => array(
				'main' => __( 'General', 'baloonup-maker' ),
			),
			'display'   => array(
				'main' => __( 'Display', 'baloonup-maker' ),
			),
			'position'  => array(
				'main' => __( 'Position', 'baloonup-maker' ),
			),
			'animation' => array(
				'main' => __( 'Animation', 'baloonup-maker' ),
			),
			'close'     => array(
				'main' => __( 'Close', 'baloonup-maker' ),
			),
		) );
	}

	public function get_baloonup_myskins() {
		$myskins = balooncreate_get_all_baloonup_myskins();

		$baloonup_myskins = array();

		foreach ( $myskins as $myskin ) {
			$baloonup_myskins[ $myskin->ID ] = $myskin->post_title;
		}

		return $baloonup_myskins;
	}

	public function fields() {
		return array(
			'general'   => array(
				'main' => array(
					'id'    => array(
						'label'       => __( 'Unique BaloonUp ID', 'baloonup-maker' ),
						'placeholder' => __( '`offer`, `more-info`', 'baloonup-maker' ),
						'desc'        => __( 'Used in baloonup triggers to target this baloonup', 'baloonup-maker' ),
						'priority'    => 5,
						'required'    => true,
					),
					'title' => array(
						'label'       => __( 'BaloonUp Title', 'baloonup-maker' ),
						'placeholder' => __( 'Enter baloonup title text,', 'baloonup-maker' ),
						'desc'        => __( 'This will be displayed above the content. Leave it empty to disable it.', 'baloonup-maker' ),
						'priority'    => 10,
					),
				),
			),
			'display'   => array(
				'main' => array(
					'myskin_id'         => array(
						'type'        => 'select',
						'label'       => __( 'BaloonUp mySkin', 'baloonup-maker' ),
						'placeholder' => __( 'Choose a myskin,', 'baloonup-maker' ),
						'desc'        => __( 'Choose which baloonup myskin will be used.', 'baloonup-maker' ),
						'std'         => balooncreate_get_default_baloonup_myskin(),
						'select2'     => true,
						'options'     => $this->get_baloonup_myskins(),
						'required'    => true,
						'priority'    => 5,
					),
					'overlay_disabled' => array(
						'label'       => __( 'Disable Overlay', 'baloonup-maker' ),
						'description' => __( 'Checking this will disable and hide the overlay for this baloonup.', 'baloonup-maker' ),
						'type'        => 'checkbox',
						'std'         => false,
						'priority'    => 10,
					),
					'size'             => array(
						'label'       => __( 'Size', 'baloonup-maker' ),
						'description' => __( 'Select the size of the baloonup.', 'baloonup-maker' ),
						'type'        => 'select',
						'std'         => 'small',
						'options'     => array_flip( apply_filters( 'balooncreate_baloonup_display_size_options', array() ) ),
						'priority'    => 15,
					),
					'width'            => array(
						'label'    => __( 'Width', 'baloonup-maker' ),
						'priority' => 20,
					),
					'width_unit'       => array(
						'label'    => __( 'Width Unit', 'baloonup-maker' ),
						'type'     => 'select',
						'std'      => 'px',
						'options'  => array_flip( apply_filters( 'balooncreate_size_unit_options', array() ) ),
						'priority' => 25,
					),
					'height'           => array(
						'label'    => __( 'Height', 'baloonup-maker' ),
						'priority' => 30,
					),
					'height_unit'      => array(
						'label'    => __( 'Height Unit', 'baloonup-maker' ),
						'type'     => 'select',
						'std'      => 'px',
						'options'  => array_flip( apply_filters( 'balooncreate_size_unit_options', array() ) ),
						'priority' => 35,
					),
				),
			),
			'position'  => array(
				'main' => array(
					'location'        => array(
						'label'       => __( 'Location', 'baloonup-maker' ),
						'description' => __( 'Choose where the baloonup will be displayed.', 'baloonup-maker' ),
						'type'        => 'select',
						'std'         => 'center top',
						'priority'    => 4,
						'options'     => array_flip( apply_filters( 'balooncreate_baloonup_display_location_options', array() ) ),
					),
					'position_top'    => array(
						'label'       => __( 'Top', 'baloonup-maker' ),
						'description' => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Top', 'baloonup-maker' ) ) ),
						'type'        => 'rangeslider',
						'std'         => 100,
						'priority'    => 10,
						'step'        => 1,
						'min'         => 0,
						'max'         => 500,
						'unit'        => 'px',
					),
					'position_bottom' => array(
						'label'       => __( 'Bottom', 'baloonup-maker' ),
						'description' => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Bottom', 'baloonup-maker' ) ) ),
						'type'        => 'rangeslider',
						'std'         => 0,
						'priority'    => 10,
						'step'        => 1,
						'min'         => 0,
						'max'         => 500,
						'unit'        => 'px',
					),
					'position_left'   => array(
						'label'       => __( 'Left', 'baloonup-maker' ),
						'description' => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Left', 'baloonup-maker' ) ) ),
						'type'        => 'rangeslider',
						'std'         => 0,
						'priority'    => 10,
						'step'        => 1,
						'min'         => 0,
						'max'         => 500,
						'unit'        => 'px',
					),
					'position_right'  => array(
						'label'       => __( 'Right', 'baloonup-maker' ),
						'description' => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Right', 'baloonup-maker' ) ) ),
						'type'        => 'rangeslider',
						'std'         => 0,
						'priority'    => 10,
						'step'        => 1,
						'min'         => 0,
						'max'         => 500,
						'unit'        => 'px',
					),
				),
			),
			'animation' => array(
				'main' => array(
					'animation_type'   => array(
						'label'       => __( 'Animation Type', 'baloonup-maker' ),
						'description' => __( 'Select an animation type for your baloonup.', 'baloonup-maker' ),
						'type'        => 'select',
						'std'         => 'fade',
						'priority'    => 5,
						'options'     => array_flip( apply_filters( 'balooncreate_baloonup_display_animation_type_options', array() ) ),
					),
					'animation_speed'  => array(
						'label'       => __( 'Animation Speed', 'baloonup-maker' ),
						'description' => __( 'Set the animation speed for the baloonup.', 'baloonup-maker' ),
						'type'        => 'rangeslider',
						'std'         => 350,
						'priority'    => 10,
						'step'        => 10,
						'min'         => 50,
						'max'         => 1000,
						'unit'        => __( 'ms', 'baloonup-maker' ),
					),
					'animation_origin' => array(
						'label'       => __( 'Animation Origin', 'baloonup-maker' ),
						'description' => __( 'Choose where the animation will begin.', 'baloonup-maker' ),
						'type'        => 'select',
						'std'         => 'center top',
						'priority'    => 15,
						'options'     => array_flip( apply_filters( 'balooncreate_baloonup_display_animation_origin_options', array() ) ),
					),
				),
			),
			'close'     => array(
				'main' => array(
					'overlay_click' => array(
						'label'       => __( 'Click Overlay to Close', 'baloonup-maker' ),
						'description' => __( 'Checking this will cause baloonup to close when user clicks on overlay.', 'baloonup-maker' ),
						'type'        => 'checkbox',
						'std'         => false,
						'priority'    => 5,
					),
				),
			),
		);
	}

	/**
	 * Shortcode handler
	 *
	 * @param  array  $atts    shortcode attributes
	 * @param  string $content shortcode content
	 *
	 * @return string
	 */
	public function handler( $atts, $content = null ) {
		global $baloonup;

		$atts = shortcode_atts( apply_filters( 'pum_baloonup_shortcode_default_atts', array(

			'id'    => "",
			'title' => "",

			'myskin_id'         => null,
			'myskin'            => null,
			'overlay_disabled' => 0,
			'size'             => "small",
			'width'            => "",
			'width_unit'       => "px",
			'height'           => "",
			'height_unit'      => "px",

			'location'        => "center top",
			'position_top'    => 100,
			'position_left'   => 0,
			'position_bottom' => 0,
			'position_right'  => 0,
			'position_fixed'  => 0,

			'animation_type'   => "slide",
			'animation_speed'  => 350,
			'animation_origin' => 'top',

			'overlay_click' => 0,
			'esc_press'     => 1,
		) ), $atts, 'baloonup' );

		// We need to fake a baloonup using the PUM_BaloonUp data model.
		$baloonup = new PUM_BaloonUp;

		$baloonup->ID           = $atts['id'];
		$baloonup->title        = $atts['title'];
		$baloonup->post_content = $content;

		// Get mySkin ID
		if ( ! $atts['myskin_id'] ) {
			$atts['myskin_id'] = $atts['myskin'] ? $atts['myskin'] : balooncreate_get_default_baloonup_myskin();
		}

		// mySkin ID
		$baloonup->myskin_id = $atts['myskin_id'];

		// Display Meta
		$baloonup->display = array(
			'size'               => $atts['size'],
			'overlay_disabled'   => $atts['overlay_disabled'],
			'custom_width'       => $atts['width'],
			'custom_width_unit'  => $atts['width_unit'],
			'custom_height'      => $atts['height'],
			'custom_height_unit' => $atts['height_unit'],
			'custom_height_auto' => $atts['width'] > 0 ? 0 : 1,
			'location'           => $atts['location'],
			'position_top'       => $atts['position_top'],
			'position_left'      => $atts['position_left'],
			'position_bottom'    => $atts['position_bottom'],
			'position_right'     => $atts['position_right'],
			'position_fixed'     => $atts['position_fixed'],
			'animation_type'     => $atts['animation_type'],
			'animation_speed'    => $atts['animation_speed'],
			'animation_origin'   => $atts['animation_origin'],
		);

		// Close Meta
		$baloonup->close = array(
			'overlay_click' => $atts['overlay_click'],
			'esc_press'     => $atts['esc_press'],
		);

		ob_start();
		balooncreate_get_template_part( 'baloonup' );

		return ob_get_clean();
	}

	public function template() { ?>
		<p class="pum-sub-form-desc">
			<?php _e( 'BaloonUp', 'baloonup-maker' ); ?>: ID "{{attrs.id}}"
		</p>
		<?php
	}

}
