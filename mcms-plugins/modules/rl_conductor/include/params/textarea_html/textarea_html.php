<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

global $vc_html_editor_already_is_use;
$vc_html_editor_already_is_use = false;
/**
 * @param $settings
 * @param $value
 *
 * @since 4.2
 * @return string
 */
function vc_textarea_html_form_field( $settings, $value ) {
	global $vc_html_editor_already_is_use;
	$output = '';
	if ( false !== $vc_html_editor_already_is_use ) {
		$output .= '<textarea name="'
		           . $settings['param_name']
		           . '" class="mcmsb_vc_param_value mcmsb-textarea '
		           . $settings['param_name'] . ' textarea">' . $value . '</textarea>';
		$output .= '<div class="updated"><p>'
		           . sprintf( __( 'Field type is changed from "textarea_html" to "textarea", because it is already used by %s field. Textarea_html field\'s type can be used only once per shortcode.', 'rl_conductor' ), $vc_html_editor_already_is_use )
		           . '</p></div>';
	} elseif ( function_exists( 'mcms_editor' ) ) {
		$default_content = $value;
		// MCMS 3.3+
		ob_start();
		mcms_editor( '', 'mcmsb_tinymce_' . $settings['param_name'], array(
			'editor_class' => 'mcmsb-textarea visual_composer_tinymce ' . $settings['param_name'] . ' ' . $settings['type'],
			'media_buttons' => true,
			'mcmsautop' => false,
		) );
		$output_value = ob_get_contents();
		ob_end_clean();
		$output .= $output_value
		           . '<input type="hidden" name="' . $settings['param_name']
		           . '"  class="vc_textarea_html_content mcmsb_vc_param_value '
		           . $settings['param_name']
		           . '" value="' . htmlspecialchars( $default_content ) . '"/>';
		$vc_html_editor_already_is_use = $settings['param_name'];
	}

	return $output;
}
