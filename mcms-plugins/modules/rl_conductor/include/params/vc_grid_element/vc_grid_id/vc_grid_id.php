<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @param $settings
 * @param $value
 *
 * @since 4.4.3
 * @return string
 */
function vc_vc_grid_id_form_field( $settings, $value ) {
	return '<div class="vc_param-vc-grid-id">'
	       . '<input name="' . $settings['param_name']
	       . '" class="mcmsb_vc_param_value mcmsb-textinput '
	       . $settings['param_name'] . ' ' . $settings['type'] . '_field" type="hidden" value="'
	       . $value . '" />'
	       . '</div>';
}
