<?php
/**
 * MandarinCMS Ajax Process Execution
 *
 * @package MandarinCMS
 * @subpackage Administration
 *
 * @link https://dev.mandarincms.com/AJAX_in_Modules
 */

/**
 * Executing Ajax process.
 *
 * @since 2.1.0
 */
define( 'DOING_AJAX', true );
if ( ! defined( 'MCMS_ADMIN' ) ) {
	define( 'MCMS_ADMIN', true );
}

/** Load MandarinCMS Bootstrap */
require_once( dirname( dirname( __FILE__ ) ) . '/bootstrap.php' );

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

// Require an action parameter
if ( empty( $_REQUEST['action'] ) )
	mcms_die( '0', 400 );

/** Load MandarinCMS Administration APIs */
require_once( BASED_TREE_URI . 'mcms-admin/includes/admin.php' );

/** Load Ajax Handlers for MandarinCMS Core */
require_once( BASED_TREE_URI . 'mcms-admin/includes/ajax-actions.php' );

@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

/** This action is documented in mcms-admin/admin.php */
do_action( 'admin_init' );

$core_actions_get = array(
	'fetch-list', 'ajax-tag-search', 'mcms-compression-test', 'imgedit-preview', 'oembed-cache',
	'autocomplete-user', 'dashboard-widgets', 'logged-in',
);

$core_actions_post = array(
	'oembed-cache', 'image-editor', 'delete-comment', 'delete-tag', 'delete-link',
	'delete-meta', 'delete-post', 'trash-post', 'untrash-post', 'delete-page', 'dim-comment',
	'add-link-category', 'add-tag', 'get-tagcloud', 'get-comments', 'replyto-comment',
	'edit-comment', 'add-menu-item', 'add-meta', 'add-user', 'closed-postboxes',
	'hidden-columns', 'update-welcome-panel', 'menu-get-metabox', 'mcms-link-ajax',
	'menu-locations-save', 'menu-quick-search', 'meta-box-order', 'get-permalink',
	'sample-permalink', 'inline-save', 'inline-save-tax', 'find_posts', 'widgets-order',
	'save-widget', 'delete-inactive-widgets', 'set-post-thumbnail', 'date_format', 'time_format',
	'mcms-remove-post-lock', 'dismiss-mcms-pointer', 'upload-attachment', 'get-attachment',
	'query-attachments', 'save-attachment', 'save-attachment-compat', 'send-link-to-editor',
	'send-attachment-to-editor', 'save-attachment-order', 'heartbeat', 'get-revision-diffs',
	'save-user-color-scheme', 'update-widget', 'query-myskins', 'parse-embed', 'set-attachment-thumbnail',
	'parse-media-shortcode', 'destroy-sessions', 'install-module', 'update-module', 'crop-image',
	'generate-password', 'save-mcmsorg-username', 'delete-module', 'search-modules',
	'search-install-modules', 'activate-module', 'update-myskin', 'delete-myskin', 'install-myskin',
	'get-post-thumbnail-html', 'get-community-events', 'edit-myskin-module-file',
	'mcms-privacy-export-personal-data',
	'mcms-privacy-erase-personal-data',
);

// Deprecated
$core_actions_post_deprecated = array( 'mcms-fullscreen-save-post', 'press-this-save-post', 'press-this-add-category' );
$core_actions_post = array_merge( $core_actions_post, $core_actions_post_deprecated );

// Register core Ajax calls.
if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], $core_actions_get ) )
	add_action( 'mcms_ajax_' . $_GET['action'], 'mcms_ajax_' . str_replace( '-', '_', $_GET['action'] ), 1 );

if ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $core_actions_post ) )
	add_action( 'mcms_ajax_' . $_POST['action'], 'mcms_ajax_' . str_replace( '-', '_', $_POST['action'] ), 1 );

add_action( 'mcms_ajax_nopriv_heartbeat', 'mcms_ajax_nopriv_heartbeat', 1 );

if ( is_user_logged_in() ) {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( 'mcms_ajax_' . $_REQUEST['action'] ) ) {
		mcms_die( '0', 400 );
	}

	/**
	 * Fires authenticated Ajax actions for logged-in users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.1.0
	 */
	do_action( 'mcms_ajax_' . $_REQUEST['action'] );
} else {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( 'mcms_ajax_nopriv_' . $_REQUEST['action'] ) ) {
		mcms_die( '0', 400 );
	}

	/**
	 * Fires non-authenticated Ajax actions for logged-out users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.8.0
	 */
	do_action( 'mcms_ajax_nopriv_' . $_REQUEST['action'] );
}
// Default status
mcms_die( '0' );
