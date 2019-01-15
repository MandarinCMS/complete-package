<?php

add_action( 'mcmscf7_init', 'mcmscf7_add_form_tag_hidden' );

function mcmscf7_add_form_tag_hidden() {
	mcmscf7_add_form_tag( 'hidden',
		'mcmscf7_hidden_form_tag_handler',
		array(
			'name-attr' => true,
			'display-hidden' => true,
		)
	);
}

function mcmscf7_hidden_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$atts = array();

	$class = mcmscf7_form_controls_class( $tag->type );
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	$value = (string) reset( $tag->values );
	$value = $tag->get_default_option( $value );
	$atts['value'] = $value;

	$atts['type'] = 'hidden';
	$atts['name'] = $tag->name;
	$atts = mcmscf7_format_atts( $atts );

	$html = sprintf( '<input %s />', $atts );
	return $html;
}
