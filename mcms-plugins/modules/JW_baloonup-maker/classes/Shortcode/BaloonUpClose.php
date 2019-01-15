<?php

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Shortcode_BaloonUpClose
 *
 * Registers the baloonup_close shortcode.
 */
class PUM_Shortcode_BaloonUpClose extends PUM_Shortcode {

	public $version = 2;

	public $has_content = true;

	/**
	 * The shortcode tag.
	 */
	public function tag() {
		return 'baloonup_close';
	}

	public function label() {
		return __( 'BaloonUp Close Button', 'baloonup-maker' );
	}

	public function description() {
		return __( 'Make text or html a close trigger for your baloonup.', 'baloonup-maker' );
	}

	public function inner_content_labels() {
		return array(
			'label'       => __( 'Content', 'baloonup-maker' ),
			'description' => __( 'Can contain other shortcodes, images, text or html content.' ),
		);
	}

	public function post_types() {
		return array( 'baloonup' );
	}

	public function fields() {
		return array(
			'general' => array(
				'main' => array(
					'tag'        => array(
						'label'       => __( 'HTML Tag', 'baloonup-maker' ),
						'placeholder' => __( 'HTML Tag', 'baloonup-maker' ) . ': button, span etc',
						'desc'        => __( 'The HTML tag used for this element.', 'baloonup-maker' ),
						'type'        => 'text',
						'std'         => '',
						'required'    => true,
					),

				),
			),
			'options' => array(
				'main' => array(
					'classes'    => array(
						'label'       => __( 'CSS Class', 'baloonup-maker' ),
						'placeholder' => 'my-custom-class',
						'type'        => 'text',
						'desc'        => __( 'Add additional classes for styling.', 'baloonup-maker' ),
						'std'         => '',
					),
					'do_default' => array(
						'type'     => 'checkbox',
						'label'    => __( 'Do not prevent the default click functionality.', 'baloonup-maker' ),
						'desc'     => __( 'This prevents us from disabling the browsers default action when a close button is clicked. It can be used to allow a link to a file to both close a baloonup and still download the file.', 'baloonup-maker' ),
					),
				),
			),
		);
	}

	/**
	 * Process shortcode attributes.
	 *
	 * Also remaps and cleans old ones.
	 *
	 * @param $atts
	 *
	 * @return array
	 */
	public function shortcode_atts( $atts ) {
		$atts = parent::shortcode_atts( $atts );

		if ( empty( $atts[''] ) ) {
			$atts['tag'] = 'span';
		}

		if ( ! empty( $atts['class'] ) ) {
			$atts['classes'] .= ' ' . $atts['class'];
			unset( $atts['class'] );
		}

		return $atts;
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
		$atts = $this->shortcode_atts( $atts );

		$do_default = $atts['do_default'] ? " data-do-default='" . esc_attr( $atts['do_default'] ) . "'" : '';

		$return = "<{$atts['tag']} class='pum-close balooncreate-close {$atts['classes']}' {$do_default}>";
		$return .= PUM_Helpers::do_shortcode( $content );
		$return .= "</{$atts['tag']}>";

		return $return;
	}

	public function template() { ?>
		<{{{attrs.tag}}} class="pum-close  balooncreate-close <# if (typeof attrs.classes !== 'undefined') print(attrs.classes); #>">{{{attrs._inner_content}}}</{{{attrs.tag}}}><?php
	}

}

