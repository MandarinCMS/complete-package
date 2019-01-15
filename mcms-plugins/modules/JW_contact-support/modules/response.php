<?php
/**
** A base module for [response]
**/

/* form_tag handler */

add_action( 'mcmscf7_init', 'mcmscf7_add_form_tag_response' );

function mcmscf7_add_form_tag_response() {
	mcmscf7_add_form_tag( 'response', 'mcmscf7_response_form_tag_handler',
		array( 'display-block' => true ) );
}

function mcmscf7_response_form_tag_handler( $tag ) {
	if ( $contact_form = mcmscf7_get_current_contact_form() ) {
		return $contact_form->form_response_output();
	}
}
