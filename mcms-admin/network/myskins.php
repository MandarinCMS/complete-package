<?php
/**
 * Multisite myskins administration panel.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( !current_user_can('manage_network_myskins') )
	mcms_die( __( 'Sorry, you are not allowed to manage network myskins.' ) );

$mcms_list_table = _get_list_table('MCMS_MS_MySkins_List_Table');
$pagenum = $mcms_list_table->get_pagenum();

$action = $mcms_list_table->current_action();

$s = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';

// Clean up request URI from temporary args for screen options/paging uri's to work as expected.
$temp_args = array( 'enabled', 'disabled', 'deleted', 'error' );
$_SERVER['REQUEST_URI'] = remove_query_arg( $temp_args, $_SERVER['REQUEST_URI'] );
$referer = remove_query_arg( $temp_args, mcms_get_referer() );

if ( $action ) {
	switch ( $action ) {
		case 'enable':
			check_admin_referer('enable-myskin_' . $_GET['myskin']);
			MCMS_MySkin::network_enable_myskin( $_GET['myskin'] );
			if ( false === strpos( $referer, '/network/myskins.php' ) )
				mcms_redirect( network_admin_url( 'myskins.php?enabled=1' ) );
			else
				mcms_safe_redirect( add_query_arg( 'enabled', 1, $referer ) );
			exit;
		case 'disable':
			check_admin_referer('disable-myskin_' . $_GET['myskin']);
			MCMS_MySkin::network_disable_myskin( $_GET['myskin'] );
			mcms_safe_redirect( add_query_arg( 'disabled', '1', $referer ) );
			exit;
		case 'enable-selected':
			check_admin_referer('bulk-myskins');
			$myskins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
			if ( empty($myskins) ) {
				mcms_safe_redirect( add_query_arg( 'error', 'none', $referer ) );
				exit;
			}
			MCMS_MySkin::network_enable_myskin( (array) $myskins );
			mcms_safe_redirect( add_query_arg( 'enabled', count( $myskins ), $referer ) );
			exit;
		case 'disable-selected':
			check_admin_referer('bulk-myskins');
			$myskins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
			if ( empty($myskins) ) {
				mcms_safe_redirect( add_query_arg( 'error', 'none', $referer ) );
				exit;
			}
			MCMS_MySkin::network_disable_myskin( (array) $myskins );
			mcms_safe_redirect( add_query_arg( 'disabled', count( $myskins ), $referer ) );
			exit;
		case 'update-selected' :
			check_admin_referer( 'bulk-myskins' );

			if ( isset( $_GET['myskins'] ) )
				$myskins = explode( ',', $_GET['myskins'] );
			elseif ( isset( $_POST['checked'] ) )
				$myskins = (array) $_POST['checked'];
			else
				$myskins = array();

			$title = __( 'Update MySkins' );
			$parent_file = 'myskins.php';

			require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

			echo '<div class="wrap">';
			echo '<h1>' . esc_html( $title ) . '</h1>';

			$url = self_admin_url('update.php?action=update-selected-myskins&amp;myskins=' . urlencode( join(',', $myskins) ));
			$url = mcms_nonce_url($url, 'bulk-update-myskins');

			echo "<iframe src='$url' style='width: 100%; height:100%; min-height:850px;'></iframe>";
			echo '</div>';
			require_once(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;
		case 'delete-selected':
			if ( ! current_user_can( 'delete_myskins' ) ) {
				mcms_die( __('Sorry, you are not allowed to delete myskins for this site.') );
			}

			check_admin_referer( 'bulk-myskins' );

			$myskins = isset( $_REQUEST['checked'] ) ? (array) $_REQUEST['checked'] : array();

			if ( empty( $myskins ) ) {
				mcms_safe_redirect( add_query_arg( 'error', 'none', $referer ) );
				exit;
			}

			$myskins = array_diff( $myskins, array( get_option( 'stylesheet' ), get_option( 'template' ) ) );

			if ( empty( $myskins ) ) {
				mcms_safe_redirect( add_query_arg( 'error', 'main', $referer ) );
				exit;
			}

			$myskin_info = array();
			foreach ( $myskins as $key => $myskin ) {
				$myskin_info[ $myskin ] = mcms_get_myskin( $myskin );
			}

			include(BASED_TREE_URI . 'mcms-admin/update.php');

			$parent_file = 'myskins.php';

			if ( ! isset( $_REQUEST['verify-delete'] ) ) {
				mcms_enqueue_script( 'jquery' );
				require_once( BASED_TREE_URI . 'mcms-admin/admin-header.php' );
				$myskins_to_delete = count( $myskins );
				?>
			<div class="wrap">
				<?php if ( 1 == $myskins_to_delete ) : ?>
					<h1><?php _e( 'Delete MySkin' ); ?></h1>
					<div class="error"><p><strong><?php _e( 'Caution:' ); ?></strong> <?php _e( 'This myskin may be active on other sites in the network.' ); ?></p></div>
					<p><?php _e( 'You are about to remove the following myskin:' ); ?></p>
				<?php else : ?>
					<h1><?php _e( 'Delete MySkins' ); ?></h1>
					<div class="error"><p><strong><?php _e( 'Caution:' ); ?></strong> <?php _e( 'These myskins may be active on other sites in the network.' ); ?></p></div>
					<p><?php _e( 'You are about to remove the following myskins:' ); ?></p>
				<?php endif; ?>
					<ul class="ul-disc">
					<?php
						foreach ( $myskin_info as $myskin ) {
							echo '<li>' . sprintf(
								/* translators: 1: myskin name, 2: myskin author */
								_x( '%1$s by %2$s', 'myskin' ),
								'<strong>' . $myskin->display( 'Name' ) . '</strong>',
								'<em>' . $myskin->display( 'Author' ) . '</em>'
							) . '</li>';
						}
					?>
					</ul>
				<?php if ( 1 == $myskins_to_delete ) : ?>
					<p><?php _e( 'Are you sure you wish to delete this myskin?' ); ?></p>
				<?php else : ?>
					<p><?php _e( 'Are you sure you wish to delete these myskins?' ); ?></p>
				<?php endif; ?>
				<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
					<input type="hidden" name="verify-delete" value="1" />
					<input type="hidden" name="action" value="delete-selected" />
					<?php
						foreach ( (array) $myskins as $myskin ) {
							echo '<input type="hidden" name="checked[]" value="' . esc_attr($myskin) . '" />';
						}

						mcms_nonce_field( 'bulk-myskins' );

						if ( 1 == $myskins_to_delete ) {
							submit_button( __( 'Yes, delete this myskin' ), '', 'submit', false );
						} else {
							submit_button( __( 'Yes, delete these myskins' ), '', 'submit', false );
						}
					?>
				</form>
				<?php
				$referer = mcms_get_referer();
				?>
				<form method="post" action="<?php echo $referer ? esc_url( $referer ) : ''; ?>" style="display:inline;">
					<?php submit_button( __( 'No, return me to the myskin list' ), '', 'submit', false ); ?>
				</form>
			</div>
				<?php
				require_once(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
				exit;
			} // Endif verify-delete

			foreach ( $myskins as $myskin ) {
				$delete_result = delete_myskin( $myskin, esc_url( add_query_arg( array(
					'verify-delete' => 1,
					'action' => 'delete-selected',
					'checked' => $_REQUEST['checked'],
					'_mcmsnonce' => $_REQUEST['_mcmsnonce']
				), network_admin_url( 'myskins.php' ) ) ) );
			}

			$paged = ( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			mcms_redirect( add_query_arg( array(
				'deleted' => count( $myskins ),
				'paged' => $paged,
				's' => $s
			), network_admin_url( 'myskins.php' ) ) );
			exit;
		default:
			$myskins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
			if ( empty( $myskins ) ) {
				mcms_safe_redirect( add_query_arg( 'error', 'none', $referer ) );
				exit;
			}
			check_admin_referer( 'bulk-myskins' );

			/** This action is documented in mcms-admin/network/site-myskins.php */
			$referer = apply_filters( 'handle_network_bulk_actions-' . get_current_screen()->id, $referer, $action, $myskins );

			mcms_safe_redirect( $referer );
			exit;
	}

}

$mcms_list_table->prepare_items();

add_thickbox();

add_screen_option( 'per_page' );

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' =>
		'<p>' . __('This screen enables and disables the inclusion of myskins available to choose in the Dexign menu for each site. It does not activate or deactivate which myskin a site is currently using.') . '</p>' .
		'<p>' . __('If the network admin disables a myskin that is in use, it can still remain selected on that site. If another myskin is chosen, the disabled myskin will not appear in the site&#8217;s Dexign > MySkins screen.') . '</p>' .
		'<p>' . __('MySkins can be enabled on a site by site basis by the network admin on the Edit Site screen (which has a MySkins tab); get there via the Edit action link on the All Sites screen. Only network admins are able to install or edit myskins.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Network_Admin_MySkins_Screen">Documentation on Network MySkins</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter myskins list' ),
	'heading_pagination' => __( 'MySkins list navigation' ),
	'heading_list'       => __( 'MySkins list' ),
) );

$title = __('MySkins');
$parent_file = 'myskins.php';

mcms_enqueue_script( 'updates' );
mcms_enqueue_script( 'myskin-preview' );

require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

?>

<div class="wrap">
<h1 class="mcms-heading-inline"><?php echo esc_html( $title ); ?></h1>

<?php if ( current_user_can( 'install_myskins' ) ) : ?>
	<a href="myskin-install.php" class="page-title-action"><?php echo esc_html_x( 'Add New', 'myskin' ); ?></a>
<?php endif; ?>

<?php
if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
	/* translators: %s: search keywords */
	printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $s ) );
}
?>

<hr class="mcms-header-end">

<?php
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
} elseif ( isset( $_GET['deleted'] ) ) {
	$deleted = absint( $_GET['deleted'] );
	if ( 1 == $deleted ) {
		$message = __( 'MySkin deleted.' );
	} else {
		$message = _n( '%s myskin deleted.', '%s myskins deleted.', $deleted );
	}
	echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $deleted ) ) . '</p></div>';
} elseif ( isset( $_GET['error'] ) && 'none' == $_GET['error'] ) {
	echo '<div id="message" class="error notice is-dismissible"><p>' . __( 'No myskin selected.' ) . '</p></div>';
} elseif ( isset( $_GET['error'] ) && 'main' == $_GET['error'] ) {
	echo '<div class="error notice is-dismissible"><p>' . __( 'You cannot delete a myskin while it is active on the main site.' ) . '</p></div>';
}

?>

<form method="get">
<?php $mcms_list_table->search_box( __( 'Search Installed MySkins' ), 'myskin' ); ?>
</form>

<?php
$mcms_list_table->views();

if ( 'broken' == $status )
	echo '<p class="clear">' . __( 'The following myskins are installed but incomplete.' ) . '</p>';
?>

<form id="bulk-action-form" method="post">
<input type="hidden" name="myskin_status" value="<?php echo esc_attr($status) ?>" />
<input type="hidden" name="paged" value="<?php echo esc_attr($page) ?>" />

<?php $mcms_list_table->display(); ?>
</form>

</div>

<?php
mcms_print_request_filesystem_credentials_modal();
mcms_print_admin_notice_templates();
mcms_print_update_row_templates();

include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
