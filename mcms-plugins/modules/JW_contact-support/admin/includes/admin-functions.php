<?php

function mcmscf7_current_action() {
	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
		return $_REQUEST['action'];
	}

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
		return $_REQUEST['action2'];
	}

	return false;
}

function mcmscf7_admin_has_edit_cap() {
	return current_user_can( 'mcmscf7_edit_contact_forms' );
}

function mcmscf7_add_tag_generator( $name, $title, $elm_id, $callback, $options = array() ) {
	$tag_generator = MCMSCF7_TagGenerator::get_instance();
	return $tag_generator->add( $name, $title, $callback, $options );
}
