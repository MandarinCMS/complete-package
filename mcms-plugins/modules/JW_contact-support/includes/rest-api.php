<?php

add_action( 'rest_api_init', 'mcmscf7_rest_api_init' );

function mcmscf7_rest_api_init() {
	$namespace = 'jw-contact-support/v1';

	register_rest_route( $namespace,
		'/contact-supports',
		array(
			array(
				'methods' => MCMS_REST_Server::READABLE,
				'callback' => 'mcmscf7_rest_get_contact_forms',
			),
			array(
				'methods' => MCMS_REST_Server::CREATABLE,
				'callback' => 'mcmscf7_rest_create_contact_form',
			),
		)
	);

	register_rest_route( $namespace,
		'/contact-supports/(?P<id>\d+)',
		array(
			array(
				'methods' => MCMS_REST_Server::READABLE,
				'callback' => 'mcmscf7_rest_get_contact_form',
			),
			array(
				'methods' => MCMS_REST_Server::EDITABLE,
				'callback' => 'mcmscf7_rest_update_contact_form',
			),
			array(
				'methods' => MCMS_REST_Server::DELETABLE,
				'callback' => 'mcmscf7_rest_delete_contact_form',
			),
		)
	);

	register_rest_route( $namespace,
		'/contact-supports/(?P<id>\d+)/feedback',
		array(
			array(
				'methods' => MCMS_REST_Server::CREATABLE,
				'callback' => 'mcmscf7_rest_create_feedback',
			),
		)
	);

	register_rest_route( $namespace,
		'/contact-supports/(?P<id>\d+)/refill',
		array(
			array(
				'methods' => MCMS_REST_Server::READABLE,
				'callback' => 'mcmscf7_rest_get_refill',
			),
		)
	);
}

function mcmscf7_rest_get_contact_forms( MCMS_REST_Request $request ) {
	if ( ! current_user_can( 'mcmscf7_read_contact_forms' ) ) {
		return new MCMS_Error( 'mcmscf7_forbidden',
			__( "You are not allowed to access contact forms.", 'jw-contact-support' ),
			array( 'status' => 403 ) );
	}

	$args = array();

	$per_page = $request->get_param( 'per_page' );

	if ( null !== $per_page ) {
		$args['posts_per_page'] = (int) $per_page;
	}

	$offset = $request->get_param( 'offset' );

	if ( null !== $offset ) {
		$args['offset'] = (int) $offset;
	}

	$order = $request->get_param( 'order' );

	if ( null !== $order ) {
		$args['order'] = (string) $order;
	}

	$orderby = $request->get_param( 'orderby' );

	if ( null !== $orderby ) {
		$args['orderby'] = (string) $orderby;
	}

	$search = $request->get_param( 'search' );

	if ( null !== $search ) {
		$args['s'] = (string) $search;
	}

	$items = MCMSCF7_ContactForm::find( $args );

	$response = array();

	foreach ( $items as $item ) {
		$response[] = array(
			'id' => $item->id(),
			'slug' => $item->name(),
			'title' => $item->title(),
			'locale' => $item->locale(),
		);
	}

	return rest_ensure_response( $response );
}

function mcmscf7_rest_create_contact_form( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );

	if ( $id ) {
		return new MCMS_Error( 'mcmscf7_post_exists',
			__( "Cannot create existing contact form.", 'jw-contact-support' ),
			array( 'status' => 400 ) );
	}

	if ( ! current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
		return new MCMS_Error( 'mcmscf7_forbidden',
			__( "You are not allowed to create a contact form.", 'jw-contact-support' ),
			array( 'status' => 403 ) );
	}

	$args = $request->get_params();
	$args['id'] = -1; // Create
	$context = $request->get_param( 'context' );
	$item = mcmscf7_save_contact_form( $args, $context );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_cannot_save',
			__( "There was an error saving the contact form.", 'jw-contact-support' ),
			array( 'status' => 500 ) );
	}

	$response = array(
		'id' => $item->id(),
		'slug' => $item->name(),
		'title' => $item->title(),
		'locale' => $item->locale(),
		'properties' => $item->get_properties(),
		'config_errors' => array(),
	);

	if ( mcmscf7_validate_configuration() ) {
		$config_validator = new MCMSCF7_ConfigValidator( $item );
		$config_validator->validate();

		$response['config_errors'] = $config_validator->collect_error_messages();

		if ( 'save' == $context ) {
			$config_validator->save();
		}
	}

	return rest_ensure_response( $response );
}

function mcmscf7_rest_get_contact_form( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );
	$item = mcmscf7_contact_form( $id );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_not_found',
			__( "The requested contact form was not found.", 'jw-contact-support' ),
			array( 'status' => 404 ) );
	}

	if ( ! current_user_can( 'mcmscf7_edit_contact_form', $id ) ) {
		return new MCMS_Error( 'mcmscf7_forbidden',
			__( "You are not allowed to access the requested contact form.", 'jw-contact-support' ),
			array( 'status' => 403 ) );
	}

	$response = array(
		'id' => $item->id(),
		'slug' => $item->name(),
		'title' => $item->title(),
		'locale' => $item->locale(),
		'properties' => $item->get_properties(),
	);

	return rest_ensure_response( $response );
}

function mcmscf7_rest_update_contact_form( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );
	$item = mcmscf7_contact_form( $id );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_not_found',
			__( "The requested contact form was not found.", 'jw-contact-support' ),
			array( 'status' => 404 ) );
	}

	if ( ! current_user_can( 'mcmscf7_edit_contact_form', $id ) ) {
		return new MCMS_Error( 'mcmscf7_forbidden',
			__( "You are not allowed to access the requested contact form.", 'jw-contact-support' ),
			array( 'status' => 403 ) );
	}

	$args = $request->get_params();
	$context = $request->get_param( 'context' );
	$item = mcmscf7_save_contact_form( $args, $context );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_cannot_save',
			__( "There was an error saving the contact form.", 'jw-contact-support' ),
			array( 'status' => 500 ) );
	}

	$response = array(
		'id' => $item->id(),
		'slug' => $item->name(),
		'title' => $item->title(),
		'locale' => $item->locale(),
		'properties' => $item->get_properties(),
		'config_errors' => array(),
	);

	if ( mcmscf7_validate_configuration() ) {
		$config_validator = new MCMSCF7_ConfigValidator( $item );
		$config_validator->validate();

		$response['config_errors'] = $config_validator->collect_error_messages();

		if ( 'save' == $context ) {
			$config_validator->save();
		}
	}

	return rest_ensure_response( $response );
}

function mcmscf7_rest_delete_contact_form( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );
	$item = mcmscf7_contact_form( $id );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_not_found',
			__( "The requested contact form was not found.", 'jw-contact-support' ),
			array( 'status' => 404 ) );
	}

	if ( ! current_user_can( 'mcmscf7_delete_contact_form', $id ) ) {
		return new MCMS_Error( 'mcmscf7_forbidden',
			__( "You are not allowed to access the requested contact form.", 'jw-contact-support' ),
			array( 'status' => 403 ) );
	}

	$result = $item->delete();

	if ( ! $result ) {
		return new MCMS_Error( 'mcmscf7_cannot_delete',
			__( "There was an error deleting the contact form.", 'jw-contact-support' ),
			array( 'status' => 500 ) );
	}

	$response = array( 'deleted' => true );

	return rest_ensure_response( $response );
}

function mcmscf7_rest_create_feedback( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );
	$item = mcmscf7_contact_form( $id );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_not_found',
			__( "The requested contact form was not found.", 'jw-contact-support' ),
			array( 'status' => 404 ) );
	}

	$result = $item->submit();

	$unit_tag = $request->get_param( '_mcmscf7_unit_tag' );

	$response = array(
		'into' => '#' . mcmscf7_sanitize_unit_tag( $unit_tag ),
		'status' => $result['status'],
		'message' => $result['message'],
	);

	if ( 'validation_failed' == $result['status'] ) {
		$invalid_fields = array();

		foreach ( (array) $result['invalid_fields'] as $name => $field ) {
			$invalid_fields[] = array(
				'into' => 'span.mcmscf7-form-control-wrap.'
					. sanitize_html_class( $name ),
				'message' => $field['reason'],
				'idref' => $field['idref'],
			);
		}

		$response['invalidFields'] = $invalid_fields;
	}

	$response = apply_filters( 'mcmscf7_ajax_json_echo', $response, $result );

	return rest_ensure_response( $response );
}

function mcmscf7_rest_get_refill( MCMS_REST_Request $request ) {
	$id = (int) $request->get_param( 'id' );
	$item = mcmscf7_contact_form( $id );

	if ( ! $item ) {
		return new MCMS_Error( 'mcmscf7_not_found',
			__( "The requested contact form was not found.", 'jw-contact-support' ),
			array( 'status' => 404 ) );
	}

	$response = apply_filters( 'mcmscf7_ajax_onload', array() );

	return rest_ensure_response( $response );
}
