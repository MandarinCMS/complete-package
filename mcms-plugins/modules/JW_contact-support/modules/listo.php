<?php
/**
** Retrieve list data from the Listo module.
** Listo http://mandarincms.com/modules/listo/
**/

add_filter( 'mcmscf7_form_tag_data_option', 'mcmscf7_listo', 10, 3 );

function mcmscf7_listo( $data, $options, $args ) {
	if ( ! function_exists( 'listo' ) ) {
		return $data;
	}

	$args = mcms_parse_args( $args, array() );

	$contact_form = mcmscf7_get_current_contact_form();
	$args['locale'] = $contact_form->locale();

	foreach ( (array) $options as $option ) {
		$option = explode( '.', $option );
		$type = $option[0];
		$args['group'] = isset( $option[1] ) ? $option[1] : null;

		if ( $list = listo( $type, $args ) ) {
			$data = array_merge( (array) $data, $list );
		}
	}

	return $data;
}
