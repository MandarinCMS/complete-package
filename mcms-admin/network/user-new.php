<?php
/**
 * Add New User network administration panel.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('create_users') )
	mcms_die(__('Sorry, you are not allowed to add users to this network.'));

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' =>
		'<p>' . __('Add User will set up a new user account on the network and send that person an email with username and password.') . '</p>' .
		'<p>' . __('Users who are signed up to the network without a site are added as subscribers to the main or primary dashboard site, giving them profile pages to manage their accounts. These users will only see Dashboard and My Sites in the main navigation until a site is created for them.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Network_Admin_Users_Screen">Documentation on Network Users</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/forum/multisite/">Support Forums</a>') . '</p>'
);

if ( isset($_REQUEST['action']) && 'add-user' == $_REQUEST['action'] ) {
	check_admin_referer( 'add-user', '_mcmsnonce_add-user' );

	if ( ! current_user_can( 'manage_network_users' ) )
		mcms_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );

	if ( ! is_array( $_POST['user'] ) )
		mcms_die( __( 'Cannot create an empty user.' ) );

	$user = mcms_unslash( $_POST['user'] );

	$user_details = mcmsmu_validate_user_signup( $user['username'], $user['email'] );
	if ( is_mcms_error( $user_details[ 'errors' ] ) && ! empty( $user_details[ 'errors' ]->errors ) ) {
		$add_user_errors = $user_details[ 'errors' ];
	} else {
		$password = mcms_generate_password( 12, false);
		$user_id = mcmsmu_create_user( esc_html( strtolower( $user['username'] ) ), $password, sanitize_email( $user['email'] ) );

		if ( ! $user_id ) {
	 		$add_user_errors = new MCMS_Error( 'add_user_fail', __( 'Cannot add user.' ) );
		} else {
			/**
			  * Fires after a new user has been created via the network user-new.php page.
			  *
			  * @since 4.4.0
			  *
			  * @param int $user_id ID of the newly created user.
			  */
			do_action( 'network_user_new_created_user', $user_id );
			mcms_redirect( add_query_arg( array('update' => 'added', 'user_id' => $user_id ), 'user-new.php' ) );
			exit;
		}
	}
}

if ( isset($_GET['update']) ) {
	$messages = array();
	if ( 'added' == $_GET['update'] ) {
		$edit_link = '';
		if ( isset( $_GET['user_id'] ) ) {
			$user_id_new = absint( $_GET['user_id'] );
			if ( $user_id_new ) {
				$edit_link = esc_url( add_query_arg( 'mcms_http_referer', urlencode( mcms_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_id_new ) ) );
			}
		}

		if ( empty( $edit_link ) ) {
			$messages[] = __( 'User added.' );
		} else {
			/* translators: %s: edit page url */
			$messages[] = sprintf( __( 'User added. <a href="%s">Edit user</a>' ), $edit_link );
		}
	}
}

$title = __('Add New User');
$parent_file = 'users.php';

require( BASED_TREE_URI . 'mcms-admin/admin-header.php' ); ?>

<div class="wrap">
<h1 id="add-new-user"><?php _e( 'Add New User' ); ?></h1>
<?php
if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
}

if ( isset( $add_user_errors ) && is_mcms_error( $add_user_errors ) ) { ?>
	<div class="error">
		<?php
			foreach ( $add_user_errors->get_error_messages() as $message )
				echo "<p>$message</p>";
		?>
	</div>
<?php } ?>
	<form action="<?php echo network_admin_url('user-new.php?action=add-user'); ?>" id="adduser" method="post" novalidate="novalidate">
	<table class="form-table">
		<tr class="form-field form-required">
			<th scope="row"><label for="username"><?php _e( 'Username' ) ?></label></th>
			<td><input type="text" class="regular-text" name="user[username]" id="username" autocapitalize="none" autocorrect="off" maxlength="60" /></td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row"><label for="email"><?php _e( 'Email' ) ?></label></th>
			<td><input type="email" class="regular-text" name="user[email]" id="email"/></td>
		</tr>
		<tr class="form-field">
			<td colspan="2"><?php _e( 'A password reset link will be sent to the user via email.' ) ?></td>
		</tr>
	</table>
	<?php
	/**
	 * Fires at the end of the new user form in network admin.
	 *
	 * @since 4.5.0
	 */
	do_action( 'network_user_new_form' );

	mcms_nonce_field( 'add-user', '_mcmsnonce_add-user' );
	submit_button( __('Add User'), 'primary', 'add-user' );
	?>
	</form>
</div>
<?php
require( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
