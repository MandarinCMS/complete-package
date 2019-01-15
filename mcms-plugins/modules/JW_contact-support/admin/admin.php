<?php

require_once MCMSCF7_MODULE_DIR . '/admin/includes/admin-functions.php';
require_once MCMSCF7_MODULE_DIR . '/admin/includes/help-tabs.php';
require_once MCMSCF7_MODULE_DIR . '/admin/includes/tag-generator.php';
require_once MCMSCF7_MODULE_DIR . '/admin/includes/welcome-panel.php';

add_action( 'admin_init', 'mcmscf7_admin_init' );

function mcmscf7_admin_init() {
	do_action( 'mcmscf7_admin_init' );
}

add_action( 'admin_menu', 'mcmscf7_admin_menu', 9 );

function mcmscf7_admin_menu() {
	global $_mcms_last_object_menu;

	$_mcms_last_object_menu++;

	add_menu_page( __( 'Contact Form 7', 'jw-contact-support' ),
		__( 'Contact', 'jw-contact-support' ),
		'mcmscf7_read_contact_forms', 'mcmscf7',
		'mcmscf7_admin_management_page', 'dashicons-email',
		$_mcms_last_object_menu );

	$edit = add_submenu_page( 'mcmscf7',
		__( 'Edit Contact Form', 'jw-contact-support' ),
		__( 'Contact Supports', 'jw-contact-support' ),
		'mcmscf7_read_contact_forms', 'mcmscf7',
		'mcmscf7_admin_management_page' );

	add_action( 'load-' . $edit, 'mcmscf7_load_contact_form_admin' );

	$addnew = add_submenu_page( 'mcmscf7',
		__( 'Add New Contact Form', 'jw-contact-support' ),
		__( 'Add New', 'jw-contact-support' ),
		'mcmscf7_edit_contact_forms', 'mcmscf7-new',
		'mcmscf7_admin_add_new_page' );

	add_action( 'load-' . $addnew, 'mcmscf7_load_contact_form_admin' );

	$integration = MCMSCF7_Integration::get_instance();

	if ( $integration->service_exists() ) {
		$integration = add_submenu_page( 'mcmscf7',
			__( 'Integration with Other Services', 'jw-contact-support' ),
			__( 'Integration', 'jw-contact-support' ),
			'mcmscf7_manage_integration', 'mcmscf7-integration',
			'mcmscf7_admin_integration_page' );

		add_action( 'load-' . $integration, 'mcmscf7_load_integration_page' );
	}
}

add_filter( 'set-screen-option', 'mcmscf7_set_screen_options', 10, 3 );

function mcmscf7_set_screen_options( $result, $option, $value ) {
	$mcmscf7_screens = array(
		'cfseven_contact_forms_per_page' );

	if ( in_array( $option, $mcmscf7_screens ) ) {
		$result = $value;
	}

	return $result;
}

function mcmscf7_load_contact_form_admin() {
	global $module_page;

	$action = mcmscf7_current_action();

	if ( 'save' == $action ) {
		$id = isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : '-1';
		check_admin_referer( 'mcmscf7-save-contact-support_' . $id );

		if ( ! current_user_can( 'mcmscf7_edit_contact_form', $id ) ) {
			mcms_die( __( 'You are not allowed to edit this item.', 'jw-contact-support' ) );
		}

		$args = $_REQUEST;
		$args['id'] = $id;

		$args['title'] = isset( $_POST['post_title'] )
			? $_POST['post_title'] : null;

		$args['locale'] = isset( $_POST['mcmscf7-locale'] )
			? $_POST['mcmscf7-locale'] : null;

		$args['form'] = isset( $_POST['mcmscf7-form'] )
			? $_POST['mcmscf7-form'] : '';

		$args['mail'] = isset( $_POST['mcmscf7-mail'] )
			? mcmscf7_sanitize_mail( $_POST['mcmscf7-mail'] )
			: array();

		$args['mail_2'] = isset( $_POST['mcmscf7-mail-2'] )
			? mcmscf7_sanitize_mail( $_POST['mcmscf7-mail-2'] )
			: array();

		$args['messages'] = isset( $_POST['mcmscf7-messages'] )
			? $_POST['mcmscf7-messages'] : array();

		$args['additional_settings'] = isset( $_POST['mcmscf7-additional-settings'] )
			? $_POST['mcmscf7-additional-settings'] : '';

		$contact_form = mcmscf7_save_contact_form( $args );

		if ( $contact_form && mcmscf7_validate_configuration() ) {
			$config_validator = new MCMSCF7_ConfigValidator( $contact_form );
			$config_validator->validate();
			$config_validator->save();
		}

		$query = array(
			'post' => $contact_form ? $contact_form->id() : 0,
			'active-tab' => isset( $_POST['active-tab'] )
				? (int) $_POST['active-tab'] : 0,
		);

		if ( ! $contact_form ) {
			$query['message'] = 'failed';
		} elseif ( -1 == $id ) {
			$query['message'] = 'created';
		} else {
			$query['message'] = 'saved';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'mcmscf7', false ) );
		mcms_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'copy' == $action ) {
		$id = empty( $_POST['post_ID'] )
			? absint( $_REQUEST['post'] )
			: absint( $_POST['post_ID'] );

		check_admin_referer( 'mcmscf7-copy-contact-support_' . $id );

		if ( ! current_user_can( 'mcmscf7_edit_contact_form', $id ) ) {
			mcms_die( __( 'You are not allowed to edit this item.', 'jw-contact-support' ) );
		}

		$query = array();

		if ( $contact_form = mcmscf7_contact_form( $id ) ) {
			$new_contact_form = $contact_form->copy();
			$new_contact_form->save();

			$query['post'] = $new_contact_form->id();
			$query['message'] = 'created';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'mcmscf7', false ) );

		mcms_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete' == $action ) {
		if ( ! empty( $_POST['post_ID'] ) ) {
			check_admin_referer( 'mcmscf7-delete-contact-support_' . $_POST['post_ID'] );
		} elseif ( ! is_array( $_REQUEST['post'] ) ) {
			check_admin_referer( 'mcmscf7-delete-contact-support_' . $_REQUEST['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$posts = empty( $_POST['post_ID'] )
			? (array) $_REQUEST['post']
			: (array) $_POST['post_ID'];

		$deleted = 0;

		foreach ( $posts as $post ) {
			$post = MCMSCF7_ContactForm::get_instance( $post );

			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'mcmscf7_delete_contact_form', $post->id() ) ) {
				mcms_die( __( 'You are not allowed to delete this item.', 'jw-contact-support' ) );
			}

			if ( ! $post->delete() ) {
				mcms_die( __( 'Error in deleting.', 'jw-contact-support' ) );
			}

			$deleted += 1;
		}

		$query = array();

		if ( ! empty( $deleted ) ) {
			$query['message'] = 'deleted';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'mcmscf7', false ) );

		mcms_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'validate' == $action && mcmscf7_validate_configuration() ) {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'mcmscf7-bulk-validate' );

			if ( ! current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
				mcms_die( __( "You are not allowed to validate configuration.", 'jw-contact-support' ) );
			}

			$contact_forms = MCMSCF7_ContactForm::find();

			$result = array(
				'timestamp' => current_time( 'timestamp' ),
				'version' => MCMSCF7_VERSION,
				'count_valid' => 0,
				'count_invalid' => 0,
			);

			foreach ( $contact_forms as $contact_form ) {
				$config_validator = new MCMSCF7_ConfigValidator( $contact_form );
				$config_validator->validate();
				$config_validator->save();

				if ( $config_validator->is_valid() ) {
					$result['count_valid'] += 1;
				} else {
					$result['count_invalid'] += 1;
				}
			}

			MCMSCF7::update_option( 'bulk_validate', $result );

			$query = array(
				'message' => 'validated',
			);

			$redirect_to = add_query_arg( $query, menu_page_url( 'mcmscf7', false ) );
			mcms_safe_redirect( $redirect_to );
			exit();
		}
	}

	$_GET['post'] = isset( $_GET['post'] ) ? $_GET['post'] : '';

	$post = null;

	if ( 'mcmscf7-new' == $module_page ) {
		$post = MCMSCF7_ContactForm::get_template( array(
			'locale' => isset( $_GET['locale'] ) ? $_GET['locale'] : null,
		) );
	} elseif ( ! empty( $_GET['post'] ) ) {
		$post = MCMSCF7_ContactForm::get_instance( $_GET['post'] );
	}

	$current_screen = get_current_screen();

	$help_tabs = new MCMSCF7_Help_Tabs( $current_screen );

	if ( $post && current_user_can( 'mcmscf7_edit_contact_form', $post->id() ) ) {
		$help_tabs->set_help_tabs( 'edit' );
	} else {
		$help_tabs->set_help_tabs( 'list' );

		if ( ! class_exists( 'MCMSCF7_Contact_Form_List_Table' ) ) {
			require_once MCMSCF7_MODULE_DIR . '/admin/includes/class-contact-supports-list-table.php';
		}

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'MCMSCF7_Contact_Form_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'default' => 20,
			'option' => 'cfseven_contact_forms_per_page',
		) );
	}
}

add_action( 'admin_enqueue_scripts', 'mcmscf7_admin_enqueue_scripts' );

function mcmscf7_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'mcmscf7' ) ) {
		return;
	}

	mcms_enqueue_style( 'jw-contact-support-admin',
		mcmscf7_module_url( 'admin/css/styles.css' ),
		array(), MCMSCF7_VERSION, 'all' );

	if ( mcmscf7_is_rtl() ) {
		mcms_enqueue_style( 'jw-contact-support-admin-rtl',
			mcmscf7_module_url( 'admin/css/styles-rtl.css' ),
			array(), MCMSCF7_VERSION, 'all' );
	}

	mcms_enqueue_script( 'mcmscf7-admin',
		mcmscf7_module_url( 'admin/js/scripts.js' ),
		array( 'jquery', 'jquery-ui-tabs' ),
		MCMSCF7_VERSION, true );

	$args = array(
		'apiSettings' => array(
			'root' => esc_url_raw( rest_url( 'jw-contact-support/v1' ) ),
			'namespace' => 'jw-contact-support/v1',
			'nonce' => ( mcms_installing() && ! is_multisite() )
				? '' : mcms_create_nonce( 'mcms_rest' ),
		),
		'moduleUrl' => mcmscf7_module_url(),
		'saveAlert' => __(
			"The changes you made will be lost if you navigate away from this page.",
			'jw-contact-support' ),
		'activeTab' => isset( $_GET['active-tab'] )
			? (int) $_GET['active-tab'] : 0,
		'configValidator' => array(
			'errors' => array(),
			'howToCorrect' => __( "How to resolve?", 'jw-contact-support' ),
			'oneError' => __( '1 configuration error detected', 'jw-contact-support' ),
			'manyErrors' => __( '%d configuration errors detected', 'jw-contact-support' ),
			'oneErrorInTab' => __( '1 configuration error detected in this tab panel', 'jw-contact-support' ),
			'manyErrorsInTab' => __( '%d configuration errors detected in this tab panel', 'jw-contact-support' ),
			'docUrl' => MCMSCF7_ConfigValidator::get_doc_link(),
			/* translators: screen reader text */
			'iconAlt' => __( '(configuration error)', 'jw-contact-support' ),
		),
	);

	if ( ( $post = mcmscf7_get_current_contact_form() )
	&& current_user_can( 'mcmscf7_edit_contact_form', $post->id() )
	&& mcmscf7_validate_configuration() ) {
		$config_validator = new MCMSCF7_ConfigValidator( $post );
		$config_validator->restore();
		$args['configValidator']['errors'] =
			$config_validator->collect_error_messages();
	}

	mcms_localize_script( 'mcmscf7-admin', 'mcmscf7', $args );

	add_thickbox();

	mcms_enqueue_script( 'mcmscf7-admin-taggenerator',
		mcmscf7_module_url( 'admin/js/tag-generator.js' ),
		array( 'jquery', 'thickbox', 'mcmscf7-admin' ), MCMSCF7_VERSION, true );
}

function mcmscf7_admin_management_page() {
	if ( $post = mcmscf7_get_current_contact_form() ) {
		$post_id = $post->initial() ? -1 : $post->id();

		require_once MCMSCF7_MODULE_DIR . '/admin/includes/editor.php';
		require_once MCMSCF7_MODULE_DIR . '/admin/edit-contact-support.php';
		return;
	}

	if ( 'validate' == mcmscf7_current_action()
	&& mcmscf7_validate_configuration()
	&& current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
		mcmscf7_admin_bulk_validate_page();
		return;
	}

	$list_table = new MCMSCF7_Contact_Form_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">

<h1 class="mcms-heading-inline"><?php
	echo esc_html( __( 'Contact Supports', 'jw-contact-support' ) );
?></h1>

<?php
	if ( current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
		echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>',
			esc_url( menu_page_url( 'mcmscf7-new', false ) ),
			esc_html( __( 'Add New', 'jw-contact-support' ) ) );
	}

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			/* translators: %s: search keywords */
			. __( 'Search results for &#8220;%s&#8221;', 'jw-contact-support' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?>

<hr class="mcms-header-end">

<?php do_action( 'mcmscf7_admin_warnings' ); ?>
<?php mcmscf7_welcome_panel(); ?>
<?php do_action( 'mcmscf7_admin_notices' ); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contact Supports', 'jw-contact-support' ), 'mcmscf7-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function mcmscf7_admin_bulk_validate_page() {
	$contact_forms = MCMSCF7_ContactForm::find();
	$count = MCMSCF7_ContactForm::count();

	$submit_text = sprintf(
		/* translators: %s: number of contact forms */
		_n(
			"Validate %s Contact Form Now",
			"Validate %s Contact Supports Now",
			$count, 'jw-contact-support' ),
		number_format_i18n( $count ) );

?>
<div class="wrap">

<h1><?php echo esc_html( __( 'Validate Configuration', 'jw-contact-support' ) ); ?></h1>

<form method="post" action="">
	<input type="hidden" name="action" value="validate" />
	<?php mcms_nonce_field( 'mcmscf7-bulk-validate' ); ?>
	<p><input type="submit" class="button" value="<?php echo esc_attr( $submit_text ); ?>" /></p>
</form>

<?php echo mcmscf7_link( __( 'https://jiiworks.net/configuration-validator-faq/', 'jw-contact-support' ), __( 'FAQ about Configuration Validator', 'jw-contact-support' ) ); ?>

</div>
<?php
}

function mcmscf7_admin_add_new_page() {
	$post = mcmscf7_get_current_contact_form();

	if ( ! $post ) {
		$post = MCMSCF7_ContactForm::get_template();
	}

	$post_id = -1;

	require_once MCMSCF7_MODULE_DIR . '/admin/includes/editor.php';
	require_once MCMSCF7_MODULE_DIR . '/admin/edit-contact-support.php';
}

function mcmscf7_load_integration_page() {
	$integration = MCMSCF7_Integration::get_instance();

	if ( isset( $_REQUEST['service'] )
	&& $integration->service_exists( $_REQUEST['service'] ) ) {
		$service = $integration->get_service( $_REQUEST['service'] );
		$service->load( mcmscf7_current_action() );
	}

	$help_tabs = new MCMSCF7_Help_Tabs( get_current_screen() );
	$help_tabs->set_help_tabs( 'integration' );
}

function mcmscf7_admin_integration_page() {
	$integration = MCMSCF7_Integration::get_instance();

?>
<div class="wrap">

<h1><?php echo esc_html( __( 'Integration with Other Services', 'jw-contact-support' ) ); ?></h1>

<?php do_action( 'mcmscf7_admin_warnings' ); ?>
<?php do_action( 'mcmscf7_admin_notices' ); ?>

<?php
	if ( isset( $_REQUEST['service'] )
	&& $service = $integration->get_service( $_REQUEST['service'] ) ) {
		$message = isset( $_REQUEST['message'] ) ? $_REQUEST['message'] : '';
		$service->admin_notice( $message );
		$integration->list_services( array( 'include' => $_REQUEST['service'] ) );
	} else {
		$integration->list_services();
	}
?>

</div>
<?php
}

/* Misc */

add_action( 'mcmscf7_admin_notices', 'mcmscf7_admin_updated_message' );

function mcmscf7_admin_updated_message() {
	if ( empty( $_REQUEST['message'] ) ) {
		return;
	}

	if ( 'created' == $_REQUEST['message'] ) {
		$updated_message = __( "Contact form created.", 'jw-contact-support' );
	} elseif ( 'saved' == $_REQUEST['message'] ) {
		$updated_message = __( "Contact form saved.", 'jw-contact-support' );
	} elseif ( 'deleted' == $_REQUEST['message'] ) {
		$updated_message = __( "Contact form deleted.", 'jw-contact-support' );
	}

	if ( ! empty( $updated_message ) ) {
		echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
		return;
	}

	if ( 'failed' == $_REQUEST['message'] ) {
		$updated_message = __( "There was an error saving the contact form.",
			'jw-contact-support' );

		echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
		return;
	}

	if ( 'validated' == $_REQUEST['message'] ) {
		$bulk_validate = MCMSCF7::get_option( 'bulk_validate', array() );
		$count_invalid = isset( $bulk_validate['count_invalid'] )
			? absint( $bulk_validate['count_invalid'] ) : 0;

		if ( $count_invalid ) {
			$updated_message = sprintf(
				/* translators: %s: number of contact forms */
				_n(
					"Configuration validation completed. %s invalid contact form was found.",
					"Configuration validation completed. %s invalid contact forms were found.",
					$count_invalid, 'jw-contact-support' ),
				number_format_i18n( $count_invalid ) );

			echo sprintf( '<div id="message" class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
		} else {
			$updated_message = __( "Configuration validation completed. No invalid contact form was found.", 'jw-contact-support' );

			echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
		}

		return;
	}
}

add_filter( 'module_action_links', 'mcmscf7_module_action_links', 10, 2 );

function mcmscf7_module_action_links( $links, $file ) {
	if ( $file != MCMSCF7_MODULE_BASENAME ) {
		return $links;
	}

	if ( ! current_user_can( 'mcmscf7_read_contact_forms' ) ) {
		return $links;
	}

	$settings_link = sprintf( '<a href="%1$s">%2$s</a>',
		menu_page_url( 'mcmscf7', false ),
		esc_html( __( 'Settings', 'jw-contact-support' ) ) );

	array_unshift( $links, $settings_link );

	return $links;
}

add_action( 'mcmscf7_admin_warnings', 'mcmscf7_old_mcms_version_error' );

function mcmscf7_old_mcms_version_error() {
	$mcms_version = get_bloginfo( 'version' );

	if ( ! version_compare( $mcms_version, MCMSCF7_REQUIRED_MCMS_VERSION, '<' ) ) {
		return;
	}

?>
<div class="notice notice-warning">
<p><?php
	/* translators: 1: version of Contact Form 7, 2: version of MandarinCMS, 3: URL */
	echo sprintf( __( '<strong>JW Contact Support%1$s requires MandarinCMS %2$s or higher.</strong> Please <a href="%3$s">update MandarinCMS</a> first.', 'jw-contact-support' ), MCMSCF7_VERSION, MCMSCF7_REQUIRED_MCMS_VERSION, admin_url( 'update-core.php' ) );
?></p>
</div>
<?php
}

add_action( 'mcmscf7_admin_warnings', 'mcmscf7_not_allowed_to_edit' );

function mcmscf7_not_allowed_to_edit() {
	if ( ! $contact_form = mcmscf7_get_current_contact_form() ) {
		return;
	}

	$post_id = $contact_form->id();

	if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) {
		return;
	}

	$message = __( "You are not allowed to edit this contact form.",
		'jw-contact-support' );

	echo sprintf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html( $message ) );
}

add_action( 'mcmscf7_admin_warnings', 'mcmscf7_notice_bulk_validate_config', 5 );

function mcmscf7_notice_bulk_validate_config() {
	if ( ! mcmscf7_validate_configuration()
	|| ! current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
		return;
	}

	if ( isset( $_GET['page'] ) && 'mcmscf7' == $_GET['page']
	&& isset( $_GET['action'] ) && 'validate' == $_GET['action'] ) {
		return;
	}

	$result = MCMSCF7::get_option( 'bulk_validate' );
	$last_important_update = '4.9';

	if ( ! empty( $result['version'] )
	&& version_compare( $last_important_update, $result['version'], '<=' ) ) {
		return;
	}

	$link = add_query_arg(
		array( 'action' => 'validate' ),
		menu_page_url( 'mcmscf7', false ) );

	$link = sprintf( '<a href="%s">%s</a>', $link, esc_html( __( 'Validate JW Contact SupportConfiguration', 'jw-contact-support' ) ) );

	$message = __( "Misconfiguration leads to mail delivery failure or other troubles. Validate your contact forms now.", 'jw-contact-support' );

	echo sprintf( '<div class="notice notice-warning"><p>%s &raquo; %s</p></div>',
		esc_html( $message ), $link );
}
