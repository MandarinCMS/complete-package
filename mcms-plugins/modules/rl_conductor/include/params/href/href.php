<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @param $settings
 * @param $value
 *
 * @since 4.4
 * @return string
 */
function vc_href_form_field( $settings, $value ) {
	if ( ! is_string( $value ) || strlen( $value ) === 0 ) {
		$value = 'http://';
	}

	return '<div class="vc_href-form-field">'
	       . '<input name="' . $settings['param_name'] . '" class="mcmsb_vc_param_value mcmsb-textinput ' . $settings['param_name'] . ' ' . $settings['type'] . '_field" type="text" value="' . $value . '"/>'
	       . '</div>';
}
