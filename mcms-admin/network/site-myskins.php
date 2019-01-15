<?php
/**
 * Edit Site MySkins Administration Screen
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'manage_sites' ) )
	mcms_die( __( 'Sorry, you are not allowed to manage myskins for this site.' ) );

get_current_screen()->add_help_tab( get_site_screen_help_tab_args() );
get_current_screen()->set_help_sidebar( get_site_screen_help_sidebar_content() );

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter site myskins list' ),
	'heading_pagination' => __( 'Site myskins list navigation' ),
	'heading_list'       => __( 'Site myskins list' ),
) );

$mcms_list_table = _get_list_table('MCMS_MS_MySkins_List_Table');

$action = $mcms_list_table->current_action();

$s = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';

// Clean up request URI from temporary args for screen options/paging uri's to work as expected.
$temp_args = array( 'enabled', 'disabled', 'error' );
$_SERVER['REQUEST_URI'] = remove_query_arg( $temp_args, $_SERVER['REQUEST_URI'] );
$referer = remove_query_arg( $temp_args, mcms_get_referer() );

if ( ! empty( $_REQUEST['paged'] ) ) {
	$referer = add_query_arg( 'paged', (int) $_REQUEST['paged'], $referer );
}

$id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

if ( ! $id )
	mcms_die( __('Invalid site ID.') );

$mcms_list_table->prepare_items();

$details = get_site( $id );
if ( ! $details ) {
	mcms_die( __( 'The requested site does not exist.' ) );
}

if ( !can_edit_network( $details->site_id ) )
	mcms_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );

$is_main_site = is_main_site( $id );

if ( $action ) {
	switch_to_blog( $id );
	$allowed_myskins = get_option( 'allowedmyskins' );

	switch ( $action ) {
		case 'enable':
			check_admin_referer( 'enable-myskin_' . $_GET['myskin'] );
			$myskin = $_GET['myskin'];
			$action = 'enabled';
			$n = 1;
			if ( !$allowed_myskins )
				$allowed_myskins = array( $myskin => true );
			else
				$allowed_myskins[$myskin] = true;
			break;
		case 'disable':
			check_admin_referer( 'disable-myskin_' . $_GET['myskin'] );
			$myskin = $_GET['myskin'];
			$action = 'disabled';
			$n = 1;
			if ( !$allowed_myskins )
				$allowed_myskins = array();
			else
				unset( $allowed_myskins[$myskin] );
			break;
		case 'enable-selected':
			check_admin_referer( 'bulk-myskins' );
			if ( isset( $_POST['checked'] ) ) {
				$myskins = (array) $_POST['checked'];
				$action = 'enabled';
				$n = count( $myskins );
				foreach ( (array) $myskins as $myskin )
					$allowed_myskins[ $myskin ] = true;
			} else {
				$action = 'error';
				$n = 'none';
			}
			break;
		case 'disable-selected':
			check_admin_referer( 'bulk-myskins' );
			if ( isset( $_POST['checked'] ) ) {
				$myskins = (array) $_POST['checked'];
				$action = 'disabled';
				$n = count( $myskins );
				foreach ( (array) $myskins as $myskin )
					unset( $allowed_myskins[ $myskin ] );
			} else {
				$action = 'error';
				$n = 'none';
			}
			break;
		default:
			if ( isset( $_POST['checked'] ) ) {
				check_admin_referer( 'bulk-myskins' );
				$myskins = (array) $_POST['checked'];
				$n = count( $myskins );
				$screen = get_current_screen()->id;

				/**
				 * Fires when a custom bulk action should be handled.
				 *
				 * The redirect link should be modified with success or failure feedback
				 * from the action to be used to display feedback to the user.
				 *
				 * The dynamic portion of the hook name, `$screen`, refers to the current screen ID.
				 *
				 * @since 4.7.0
				 *
				 * @param string $redirect_url The redirect URL.
				 * @param string $action       The action being taken.
				 * @param array  $items        The items to take the action on.
				 * @param int    $site_id      The site ID.
				 */
				$referer = apply_filters( "handle_network_bulk_actions-{$screen}", $referer, $action, $myskins, $id );
			} else {
				$action = 'error';
				$n = 'none';
			}
	}

	update_option( 'allowedmyskins', $allowed_myskins );
	restore_current_blog();

	mcms_safe_redirect( add_query_arg( array( 'id' => $id, $action => $n ), $referer ) );
	exit;
}

if ( isset( $_GET['action'] ) && 'update-site' == $_GET['action'] ) {
	mcms_safe_redirect( $referer );
	exit();
}

add_thickbox();
add_screen_option( 'per_page' );

/* translators: %s: site name */
$title = sprintf( __( 'Edit Site: %s' ), esc_html( $details->blogname ) );

$parent_file = 'sites.php';
$submenu_file = 'sites.php';

require( BASED_TREE_URI . 'mcms-admin/admin-header.php' ); ?>

<div class="wrap">
<h1 id="edit-site"><?php echo $title; ?></h1>
<p class="edit-site-actions"><a href="<?php echo esc_url( get_home_url( $id, '/' ) ); ?>"><?php _e( 'Visit' ); ?></a> | <a href="<?php echo esc_url( get_admin_url( $id ) ); ?>"><?php _e( 'Dashboard' ); ?></a></p>
<?php

network_edit_site_nav( array(
	'blog_id'  => $id,
	'selected' => 'site-myskins'
) );

if ( isset( $_GET['enabled'] ) ) {
	$enabled = absint( $_GET['enabled'] );
	if ( 1 == $enabled ) {
		$message = __( 'MySkin enabled.' );
	} else {
		$message = _n( '%s myskin enabled.', '%s myskins enabled.', $enabled );
	}
	echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $enabled ) ) . '</p></div>';
} elseif ( isset( $_GET['disabled'] ) ) {
	$disabled = absint( $_GET['disabled'] );
	if ( 1 == $disabled ) {
		$message = __( 'MySkin disabled.' );
	} else {
		$message = _n( '%s myskin disabled.', '%s myskins disabled.', $disabled );
	}
	echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $disabled ) ) . '</p></div>';
} elseif ( isset( $_GET['error'] ) && 'none' == $_GET['error'] ) {
	echo '<div id="message" class="error notice is-dismissible"><p>' . __( 'No myskin selected.' ) . '</p></div>';
} ?>

<p><?php _e( 'Network enabled myskins are not shown on this screen.' ) ?></p>

<form method="get">
<?php $mcms_list_table->search_box( __( 'Search Installed MySkins' ), 'myskin' ); ?>
<input type="hidden" name="id" value="<?php echo esc_attr( $id ) ?>" />
</form>

<?php $mcms_list_table->views(); ?>

<form method="post" action="site-myskins.php?action=update-site">
	<input type="hidden" name="id" value="<?php echo esc_attr( $id ) ?>" />

<?php $mcms_list_table->display(); ?>

</form>

</div>
<?php include(BASED_TREE_URI . 'mcms-admin/admin-footer.php'); ?>
