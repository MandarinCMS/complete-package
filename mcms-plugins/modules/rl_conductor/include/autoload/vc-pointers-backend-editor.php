<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Add MCMS ui pointers to backend editor.
 */
function vc_add_admin_pointer() {
	if ( is_admin() ) {
		foreach ( vc_editor_post_types() as $post_type ) {
			add_filter( 'vc_ui-pointers-' . $post_type, 'vc_backend_editor_register_pointer' );
		}
	}
}

add_action( 'admin_init', 'vc_add_admin_pointer' );

function vc_backend_editor_register_pointer( $pointers ) {
	$screen = get_current_screen();
	if ( 'add' === $screen->action ) {
		$pointers['vc_pointers_backend_editor'] = array(
			'name' => 'vcPointerController',
			'messages' => array(
				array(
					'target' => '.composer-switch',
					'options' => array(
						'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
							__( 'Welcome to RazorLeaf Conductor', 'rl_conductor' ),
							__( 'Choose Backend or Frontend editor.', 'rl_conductor' )
						),
						'position' => array(
							'edge' => 'left',
							'align' => 'center',
						),
						'buttonsEvent' => 'vcPointersEditorsTourEvents',
					),
				),
				array(
					'target' => '#vc_templates-editor-button, #vc-templatera-editor-button',
					'options' => array(
						'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
							__( 'Add Elements', 'rl_conductor' ),
							__( 'Add new element or start with a template.', 'rl_conductor' )
						),
						'position' => array(
							'edge' => 'left',
							'align' => 'center',
						),
						'buttonsEvent' => 'vcPointersEditorsTourEvents',
					),
					'closeEvent' => 'shortcodes:vc_row:add',
					'showEvent' => 'backendEditor.show',
				),
				array(
					'target' => '[data-vc-control="add"]:first',
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
					'closeEvent' => 'click #mcmsb_visual_composer',
					'showEvent' => 'shortcodeView:ready',
				),
				array(
					'target' => '.mcmsb_column_container:first .mcmsb_content_element:first .vc_controls-cc',
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
					'showCallback' => 'vcPointersShowOnContentElementControls',
					'closeEvent' => 'click #mcmsb_visual_composer',
				),
			),
		);
	}

	return $pointers;
}
