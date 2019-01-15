<?php

add_action( 'parse_request', 'mcmscf7_control_init', 20 );

function mcmscf7_control_init() {
	if ( MCMSCF7_Submission::is_restful() ) {
		return;
	}

	if ( isset( $_POST['_mcmscf7'] ) ) {
		$contact_form = mcmscf7_contact_form( (int) $_POST['_mcmscf7'] );

		if ( $contact_form ) {
			$contact_form->submit();
		}
	}
}

add_filter( 'widget_text', 'mcmscf7_widget_text_filter', 9 );

function mcmscf7_widget_text_filter( $content ) {
	$pattern = '/\[[\r\n\t ]*contact-support(-7)?[\r\n\t ].*?\]/';

	if ( ! preg_match( $pattern, $content ) ) {
		return $content;
	}

	$content = do_shortcode( $content );

	return $content;
}

add_action( 'mcms_enqueue_scripts', 'mcmscf7_do_enqueue_scripts' );

function mcmscf7_do_enqueue_scripts() {
	if ( mcmscf7_load_js() ) {
		mcmscf7_enqueue_scripts();
	}

	if ( mcmscf7_load_css() ) {
		mcmscf7_enqueue_styles();
	}
}

function mcmscf7_enqueue_scripts() {
	$in_footer = true;

	if ( 'header' === mcmscf7_load_js() ) {
		$in_footer = false;
	}

	mcms_enqueue_script( 'jw-contact-support',
		mcmscf7_module_url( 'includes/js/scripts.js' ),
		array( 'jquery' ), MCMSCF7_VERSION, $in_footer );

	$mcmscf7 = array(
		'apiSettings' => array(
			'root' => esc_url_raw( rest_url( 'jw-contact-support/v1' ) ),
			'namespace' => 'jw-contact-support/v1',
		),
		'recaptcha' => array(
			'messages' => array(
				'empty' =>
					__( 'Please verify that you are not a robot.', 'jw-contact-support' ),
			),
		),
	);

	if ( defined( 'MCMS_CACHE' ) && MCMS_CACHE ) {
		$mcmscf7['cached'] = 1;
	}

	if ( mcmscf7_support_html5_fallback() ) {
		$mcmscf7['jqueryUi'] = 1;
	}

	mcms_localize_script( 'jw-contact-support', 'mcmscf7', $mcmscf7 );

	do_action( 'mcmscf7_enqueue_scripts' );
}

function mcmscf7_script_is() {
	return mcms_script_is( 'jw-contact-support' );
}

function mcmscf7_enqueue_styles() {
	mcms_enqueue_style( 'jw-contact-support',
		mcmscf7_module_url( 'includes/css/styles.css' ),
		array(), MCMSCF7_VERSION, 'all' );

	if ( mcmscf7_is_rtl() ) {
		mcms_enqueue_style( 'jw-contact-support-rtl',
			mcmscf7_module_url( 'includes/css/styles-rtl.css' ),
			array(), MCMSCF7_VERSION, 'all' );
	}

	do_action( 'mcmscf7_enqueue_styles' );
}

function mcmscf7_style_is() {
	return mcms_style_is( 'jw-contact-support' );
}

/* HTML5 Fallback */

add_action( 'mcms_enqueue_scripts', 'mcmscf7_html5_fallback', 20 );

function mcmscf7_html5_fallback() {
	if ( ! mcmscf7_support_html5_fallback() ) {
		return;
	}

	if ( mcmscf7_script_is() ) {
		mcms_enqueue_script( 'jquery-ui-datepicker' );
		mcms_enqueue_script( 'jquery-ui-spinner' );
	}

	if ( mcmscf7_style_is() ) {
		mcms_enqueue_style( 'jquery-ui-smoothness',
			mcmscf7_module_url(
				'includes/js/jquery-ui/myskins/smoothness/jquery-ui.min.css' ),
			array(), '1.11.4', 'screen' );
	}
}
