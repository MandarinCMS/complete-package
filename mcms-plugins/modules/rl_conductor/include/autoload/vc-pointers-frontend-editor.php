<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Add MCMS ui pointers to backend editor.
 */
function vc_frontend_editor_pointer() {
	vc_is_frontend_editor() && add_filter( 'vc-ui-pointers', 'vc_frontend_editor_register_pointer' );
}

add_action( 'admin_init', 'vc_frontend_editor_pointer' );

function vc_frontend_editor_register_pointer( $pointers ) {
	global $post;
	if ( is_object( $post ) && ! strlen( $post->post_content ) ) {
		$pointers['vc_pointers_frontend_editor'] = array(
			'name' => 'vcPointerController',
			'messages' => array(
				array(
					'target' => '#vc_add-new-element',
					'options' => array(
						'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
							__( 'Add Elements', 'rl_conductor' ),
							__( 'Add new element or start with a template.', 'rl_conductor' )
						),
						'position' => array(
							'edge' => 'top',
							'align' => 'left',
						),
						'buttonsEvent' => 'vcPointersEditorsTourEvents',
					),
					'closeEvent' => 'shortcodes:add',
				),
				array(
					'target' => '.vc_controls-out-tl:first',
					'options' => array(
						'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
							__( 'Rows and Columns', 'rl_conductor' ),
							__( 'This is a row container. Divide it into columns and style it. You can add elements into columns.', 'rl_conductor' )
						),
						'position' => array(
							'edge' => 'left',
							'align' => 'center',
						),
						'buttonsEvent' => 'vcPointersEditorsTourEvents',
					),
					'closeCallback' => 'vcPointersCloseInIFrame',
					'showCallback' => 'vcPointersSetInIFrame',
				),
				array(
					'target' => '.vc_controls-cc:first',
					'options' => array(
						'content' => sprintf( '<h3> %s </h3> <p> %s <br/><br/> %s</p>',
							__( 'Control Elements', 'rl_conductor' ),
							__( 'You can edit your element at any time and drag it around your layout.', 'rl_conductor' ),
							sprintf( __( 'P.S. Learn more at our <a href="%s" target="_blank">Knowledge Base</a>.', 'rl_conductor' ), 'http://jiiworks.net' )
						),
						'position' => array(
							'edge' => 'left',
							'align' => 'center',
						),
						'buttonsEvent' => 'vcPointersEditorsTourEvents',
					),
					'closeCallback' => 'vcPointersCloseInIFrame',
					'showCallback' => 'vcPointersSetInIFrame',
				),
			),
		);
	}

	return $pointers;
}

// @todo check is this correct place (editable page)
function vc_page_editable_enqueue_pointer_scripts() {
	if ( vc_is_page_editable() ) {
		mcms_enqueue_style( 'mcms-pointer' );
		mcms_enqueue_script( 'mcms-pointer' );
	}
}

add_action( 'mcms_enqueue_scripts', 'vc_page_editable_enqueue_pointer_scripts' );
