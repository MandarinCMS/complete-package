<?php
/**
 * Modules administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('activate_modules') )
	mcms_die( __( 'Sorry, you are not allowed to manage modules for this site.' ) );

$mcms_list_table = _get_list_table('MCMS_Modules_List_Table');
$pagenum = $mcms_list_table->get_pagenum();

$action = $mcms_list_table->current_action();

$module = isset($_REQUEST['module']) ? mcms_unslash( $_REQUEST['module'] ) : '';
$s = isset($_REQUEST['s']) ? urlencode( mcms_unslash( $_REQUEST['s'] ) ) : '';

// Clean up request URI from temporary args for screen options/paging uri's to work as expected.
$_SERVER['REQUEST_URI'] = remove_query_arg(array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce'), $_SERVER['REQUEST_URI']);

mcms_enqueue_script( 'updates' );

if ( $action ) {

	switch ( $action ) {
		case 'activate':
			if ( ! current_user_can( 'activate_module', $module ) ) {
				mcms_die( __( 'Sorry, you are not allowed to activate this module.' ) );
			}

			if ( is_multisite() && ! is_network_admin() && is_network_only_module( $module ) ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			check_admin_referer('activate-module_' . $module);

			$result = activate_module($module, self_admin_url('modules.php?error=true&module=' . urlencode( $module ) ), is_network_admin() );
			if ( is_mcms_error( $result ) ) {
				if ( 'unexpected_output' == $result->get_error_code() ) {
					$redirect = self_admin_url('modules.php?error=true&charsout=' . strlen($result->get_error_data()) . '&module=' . urlencode( $module ) . "&module_status=$status&paged=$page&s=$s");
					mcms_redirect(add_query_arg('_error_nonce', mcms_create_nonce('module-activation-error_' . $module), $redirect));
					exit;
				} else {
					mcms_die($result);
				}
			}

			if ( ! is_network_admin() ) {
				$recent = (array) get_option( 'recently_activated' );
				unset( $recent[ $module ] );
				update_option( 'recently_activated', $recent );
			} else {
				$recent = (array) get_site_option( 'recently_activated' );
				unset( $recent[ $module ] );
				update_site_option( 'recently_activated', $recent );
			}

			if ( isset($_GET['from']) && 'import' == $_GET['from'] ) {
				mcms_redirect( self_admin_url("import.php?import=" . str_replace('-importer', '', dirname($module))) ); // overrides the ?error=true one above and redirects to the Imports page, stripping the -importer suffix
			} else if ( isset($_GET['from']) && 'press-this' == $_GET['from'] ) {
				mcms_redirect( self_admin_url( "press-this.php") );
			} else {
				mcms_redirect( self_admin_url("modules.php?activate=true&module_status=$status&paged=$page&s=$s") ); // overrides the ?error=true one above
			}
			exit;

		case 'activate-selected':
			if ( ! current_user_can('activate_modules') )
				mcms_die(__('Sorry, you are not allowed to activate modules for this site.'));

			check_admin_referer('bulk-modules');

			$modules = isset( $_POST['checked'] ) ? (array) mcms_unslash( $_POST['checked'] ) : array();

			if ( is_network_admin() ) {
				foreach ( $modules as $i => $module ) {
					// Only activate modules which are not already network activated.
					if ( is_module_active_for_network( $module ) ) {
						unset( $modules[ $i ] );
					}
				}
			} else {
				foreach ( $modules as $i => $module ) {
					// Only activate modules which are not already active and are not network-only when on Multisite.
					if ( is_module_active( $module ) || ( is_multisite() && is_network_only_module( $module ) ) ) {
						unset( $modules[ $i ] );
					}
					// Only activate modules which the user can activate.
					if ( ! current_user_can( 'activate_module', $module ) ) {
						unset( $modules[ $i ] );
					}
				}
			}

			if ( empty($modules) ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			activate_modules($modules, self_admin_url('modules.php?error=true'), is_network_admin() );

			if ( ! is_network_admin() ) {
				$recent = (array) get_option('recently_activated' );
			} else {
				$recent = (array) get_site_option('recently_activated' );
			}

			foreach ( $modules as $module ) {
				unset( $recent[ $module ] );
			}

			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', $recent );
			} else {
				update_site_option( 'recently_activated', $recent );
			}

			mcms_redirect( self_admin_url("modules.php?activate-multi=true&module_status=$status&paged=$page&s=$s") );
			exit;

		case 'update-selected' :

			check_admin_referer( 'bulk-modules' );

			if ( isset( $_GET['modules'] ) )
				$modules = explode( ',', mcms_unslash( $_GET['modules'] ) );
			elseif ( isset( $_POST['checked'] ) )
				$modules = (array) mcms_unslash( $_POST['checked'] );
			else
				$modules = array();

			$title = __( 'Update Modules' );
			$parent_file = 'modules.php';

			mcms_enqueue_script( 'updates' );
			require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

			echo '<div class="wrap">';
			echo '<h1>' . esc_html( $title ) . '</h1>';

			$url = self_admin_url('update.php?action=update-selected&amp;modules=' . urlencode( join(',', $modules) ));
			$url = mcms_nonce_url($url, 'bulk-update-modules');

			echo "<iframe src='$url' style='width: 100%; height:100%; min-height:850px;'></iframe>";
			echo '</div>';
			require_once(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;

		case 'error_scrape':
			if ( ! current_user_can( 'activate_module', $module ) ) {
				mcms_die( __( 'Sorry, you are not allowed to activate this module.' ) );
			}

			check_admin_referer('module-activation-error_' . $module);

			$valid = validate_module($module);
			if ( is_mcms_error($valid) )
				mcms_die($valid);

			if ( ! MCMS_DEBUG ) {
				error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
			}

			@ini_set('display_errors', true); //Ensure that Fatal errors are displayed.
			// Go back to "sandbox" scope so we get the same errors as before
			module_sandbox_scrape( $module );
			/** This action is documented in mcms-admin/includes/module.php */
			do_action( "activate_{$module}" );
			exit;

		case 'deactivate':
			if ( ! current_user_can( 'deactivate_module', $module ) ) {
				mcms_die( __( 'Sorry, you are not allowed to deactivate this module.' ) );
			}

			check_admin_referer('deactivate-module_' . $module);

			if ( ! is_network_admin() && is_module_active_for_network( $module ) ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			deactivate_modules( $module, false, is_network_admin() );

			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', array( $module => time() ) + (array) get_option( 'recently_activated' ) );
			} else {
				update_site_option( 'recently_activated', array( $module => time() ) + (array) get_site_option( 'recently_activated' ) );
			}

			if ( headers_sent() )
				echo "<meta http-equiv='refresh' content='" . esc_attr( "0;url=modules.php?deactivate=true&module_status=$status&paged=$page&s=$s" ) . "' />";
			else
				mcms_redirect( self_admin_url("modules.php?deactivate=true&module_status=$status&paged=$page&s=$s") );
			exit;

		case 'deactivate-selected':
			if ( ! current_user_can( 'deactivate_modules' ) ) {
				mcms_die(__('Sorry, you are not allowed to deactivate modules for this site.'));
			}

			check_admin_referer('bulk-modules');

			$modules = isset( $_POST['checked'] ) ? (array) mcms_unslash( $_POST['checked'] ) : array();
			// Do not deactivate modules which are already deactivated.
			if ( is_network_admin() ) {
				$modules = array_filter( $modules, 'is_module_active_for_network' );
			} else {
				$modules = array_filter( $modules, 'is_module_active' );
				$modules = array_diff( $modules, array_filter( $modules, 'is_module_active_for_network' ) );

				foreach ( $modules as $i => $module ) {
					// Only deactivate modules which the user can deactivate.
					if ( ! current_user_can( 'deactivate_module', $module ) ) {
						unset( $modules[ $i ] );
					}
				}

			}
			if ( empty($modules) ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			deactivate_modules( $modules, false, is_network_admin() );

			$deactivated = array();
			foreach ( $modules as $module ) {
				$deactivated[ $module ] = time();
			}

			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', $deactivated + (array) get_option( 'recently_activated' ) );
			} else {
				update_site_option( 'recently_activated', $deactivated + (array) get_site_option( 'recently_activated' ) );
			}

			mcms_redirect( self_admin_url("modules.php?deactivate-multi=true&module_status=$status&paged=$page&s=$s") );
			exit;

		case 'delete-selected':
			if ( ! current_user_can('delete_modules') ) {
				mcms_die(__('Sorry, you are not allowed to delete modules for this site.'));
			}

			check_admin_referer('bulk-modules');

			//$_POST = from the module form; $_GET = from the FTP details screen.
			$modules = isset( $_REQUEST['checked'] ) ? (array) mcms_unslash( $_REQUEST['checked'] ) : array();
			if ( empty( $modules ) ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			$modules = array_filter($modules, 'is_module_inactive'); // Do not allow to delete Activated modules.
			if ( empty( $modules ) ) {
				mcms_redirect( self_admin_url( "modules.php?error=true&main=true&module_status=$status&paged=$page&s=$s" ) );
				exit;
			}

			// Bail on all if any paths are invalid.
			// validate_file() returns truthy for invalid files
			$invalid_module_files = array_filter( $modules, 'validate_file' );
			if ( $invalid_module_files ) {
				mcms_redirect( self_admin_url("modules.php?module_status=$status&paged=$page&s=$s") );
				exit;
			}

			include(BASED_TREE_URI . 'mcms-admin/update.php');

			$parent_file = 'modules.php';

			if ( ! isset($_REQUEST['verify-delete']) ) {
				mcms_enqueue_script('jquery');
				require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');
				?>
			<div class="wrap">
				<?php
					$module_info = array();
					$have_non_network_modules = false;
					foreach ( (array) $modules as $module ) {
						$module_slug = dirname( $module );

						if ( '.' == $module_slug ) {
							if ( $data = get_module_data( MCMS_PLUGIN_DIR . '/' . $module ) ) {
								$module_info[ $module ] = $data;
								$module_info[ $module ]['is_uninstallable'] = is_uninstallable_module( $module );
								if ( ! $module_info[ $module ]['Network'] ) {
									$have_non_network_modules = true;
								}
							}
						} else {
							// Get modules list from that folder.
							if ( $folder_modules = get_modules( '/' . $module_slug ) ) {
								foreach ( $folder_modules as $module_file => $data ) {
									$module_info[ $module_file ] = _get_module_data_markup_translate( $module_file, $data );
									$module_info[ $module_file ]['is_uninstallable'] = is_uninstallable_module( $module );
									if ( ! $module_info[ $module_file ]['Network'] ) {
										$have_non_network_modules = true;
									}
								}
							}
						}
					}
					$modules_to_delete = count( $module_info );
				?>
				<?php if ( 1 == $modules_to_delete ) : ?>
					<h1><?php _e( 'Delete Module' ); ?></h1>
					<?php if ( $have_non_network_modules && is_network_admin() ) : ?>
						<div class="error"><p><strong><?php _e( 'Caution:' ); ?></strong> <?php _e( 'This module may be active on other sites in the network.' ); ?></p></div>
					<?php endif; ?>
					<p><?php _e( 'You are about to remove the following module:' ); ?></p>
				<?php else: ?>
					<h1><?php _e( 'Delete Modules' ); ?></h1>
					<?php if ( $have_non_network_modules && is_network_admin() ) : ?>
						<div class="error"><p><strong><?php _e( 'Caution:' ); ?></strong> <?php _e( 'These modules may be active on other sites in the network.' ); ?></p></div>
					<?php endif; ?>
					<p><?php _e( 'You are about to remove the following modules:' ); ?></p>
				<?php endif; ?>
					<ul class="ul-disc">
						<?php
						$data_to_delete = false;
						foreach ( $module_info as $module ) {
							if ( $module['is_uninstallable'] ) {
								/* translators: 1: module name, 2: module author */
								echo '<li>', sprintf( __( '%1$s by %2$s (will also <strong>delete its data</strong>)' ), '<strong>' . $module['Name'] . '</strong>', '<em>' . $module['AuthorName'] . '</em>' ), '</li>';
								$data_to_delete = true;
							} else {
								/* translators: 1: module name, 2: module author */
								echo '<li>', sprintf( _x('%1$s by %2$s', 'module' ), '<strong>' . $module['Name'] . '</strong>', '<em>' . $module['AuthorName'] ) . '</em>', '</li>';
							}
						}
						?>
					</ul>
				<p><?php
				if ( $data_to_delete )
					_e('Are you sure you wish to delete these files and data?');
				else
					_e('Are you sure you wish to delete these files?');
				?></p>
				<form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" style="display:inline;">
					<input type="hidden" name="verify-delete" value="1" />
					<input type="hidden" name="action" value="delete-selected" />
					<?php
						foreach ( (array) $modules as $module ) {
							echo '<input type="hidden" name="checked[]" value="' . esc_attr( $module ) . '" />';
						}
					?>
					<?php mcms_nonce_field('bulk-modules') ?>
					<?php submit_button( $data_to_delete ? __( 'Yes, delete these files and data' ) : __( 'Yes, delete these files' ), '', 'submit', false ); ?>
				</form>
				<?php
				$referer = mcms_get_referer();
				?>
				<form method="post" action="<?php echo $referer ? esc_url( $referer ) : ''; ?>" style="display:inline;">
					<?php submit_button( __( 'No, return me to the module list' ), '', 'submit', false ); ?>
				</form>
			</div>
				<?php
				require_once(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
				exit;
			} else {
				$modules_to_delete = count( $modules );
			} // endif verify-delete

			$delete_result = delete_modules( $modules );

			set_transient('modules_delete_result_' . $user_ID, $delete_result); //Store the result in a cache rather than a URL param due to object type & length
			mcms_redirect( self_admin_url("modules.php?deleted=$modules_to_delete&module_status=$status&paged=$page&s=$s") );
			exit;

		case 'clear-recent-list':
			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', array() );
			} else {
				update_site_option( 'recently_activated', array() );
			}
			break;

		default:
			if ( isset( $_POST['checked'] ) ) {
				check_admin_referer('bulk-modules');
				$modules = isset( $_POST['checked'] ) ? (array) mcms_unslash( $_POST['checked'] ) : array();
				$sendback = mcms_get_referer();

				/** This action is documented in mcms-admin/edit-comments.php */
				$sendback = apply_filters( 'handle_bulk_actions-' . get_current_screen()->id, $sendback, $action, $modules );
				mcms_safe_redirect( $sendback );
				exit;
			}
			break;
	}

}

$mcms_list_table->prepare_items();

mcms_enqueue_script('module-install');
add_thickbox();

add_screen_option( 'per_page', array( 'default' => 999 ) );

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . __('Modules extend and expand the functionality of MandarinCMS. Once a module is installed, you may activate it or deactivate it here.') . '</p>' .
	'<p>' . __( 'The search for installed modules will search for terms in their name, description, or author.' ) . ' <span id="live-search-desc" class="hide-if-no-js">' . __( 'The search results will be updated as you type.' ) . '</span></p>' .
	'<p>' . sprintf(
		/* translators: %s: MandarinCMS Module Directory URL */
		__( 'If you would like to see more modules to choose from, click on the &#8220;Add New&#8221; button and you will be able to browse or search for additional modules from the <a href="%s">MandarinCMS Module Directory</a>. Modules in the MandarinCMS Module Directory are designed and developed by third parties, and are compatible with the license MandarinCMS uses. Oh, and they&#8217;re free!' ),
		__( 'https://mandarincms.com/modules/' )
	) . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'compatibility-problems',
'title'		=> __('Troubleshooting'),
'content'	=>
	'<p>' . __('Most of the time, modules play nicely with the core of MandarinCMS and with other modules. Sometimes, though, a module&#8217;s code will get in the way of another module, causing compatibility issues. If your site starts doing strange things, this may be the problem. Try deactivating all your modules and re-activating them in various combinations until you isolate which one(s) caused the issue.') . '</p>' .
	'<p>' . sprintf(
		/* translators: MCMS_PLUGIN_DIR constant value */
		__( 'If something goes wrong with a module and you can&#8217;t use MandarinCMS, delete or rename that file in the %s directory and it will be automatically deactivated.' ),
		'<code>' . MCMS_PLUGIN_DIR . '</code>'
	) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Managing_Modules#Module_Management">Documentation on Managing Modules</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter modules list' ),
	'heading_pagination' => __( 'Modules list navigation' ),
	'heading_list'       => __( 'Modules list' ),
) );

$title = __('Modules');
$parent_file = 'modules.php';

require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

$invalid = validate_active_modules();
if ( ! empty( $invalid ) ) {
	foreach ( $invalid as $module_file => $error ) {
		echo '<div id="message" class="error"><p>';
		printf(
			/* translators: 1: module file 2: error message */
			__( 'The module %1$s has been <strong>deactivated</strong> due to an error: %2$s' ),
			'<code>' . esc_html( $module_file ) . '</code>',
			$error->get_error_message() );
		echo '</p></div>';
	}
}
?>

<?php if ( isset($_GET['error']) ) :

	if ( isset( $_GET['main'] ) )
		$errmsg = __( 'You cannot delete a module while it is active on the main site.' );
	elseif ( isset($_GET['charsout']) )
		$errmsg = sprintf(__('The module generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this module.'), $_GET['charsout']);
	else
		$errmsg = __('Module could not be activated because it triggered a <strong>fatal error</strong>.');
	?>
	<div id="message" class="error"><p><?php echo $errmsg; ?></p>
	<?php
		if ( ! isset( $_GET['main'] ) && ! isset( $_GET['charsout'] ) && mcms_verify_nonce( $_GET['_error_nonce'], 'module-activation-error_' . $module ) ) {
			$iframe_url = add_query_arg( array(
				'action'   => 'error_scrape',
				'module'   => urlencode( $module ),
				'_mcmsnonce' => urlencode( $_GET['_error_nonce'] ),
			), admin_url( 'modules.php' ) );
		?>
		<iframe style="border:0" width="100%" height="70px" src="<?php echo esc_url( $iframe_url ); ?>"></iframe>
	<?php
		}
	?>
	</div>
<?php elseif ( isset($_GET['deleted']) ) :
		$delete_result = get_transient( 'modules_delete_result_' . $user_ID );
		// Delete it once we're done.
		delete_transient( 'modules_delete_result_' . $user_ID );

		if ( is_mcms_error($delete_result) ) : ?>
		<div id="message" class="error notice is-dismissible"><p><?php printf( __('Module could not be deleted due to an error: %s'), $delete_result->get_error_message() ); ?></p></div>
		<?php else : ?>
		<div id="message" class="updated notice is-dismissible">
			<p>
				<?php
				if ( 1 == (int) $_GET['deleted'] ) {
					_e( 'The selected module has been <strong>deleted</strong>.' );
				} else {
					_e( 'The selected modules have been <strong>deleted</strong>.' );
				}
				?>
			</p>
		</div>
		<?php endif; ?>
<?php elseif ( isset($_GET['activate']) ) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e('Module <strong>activated</strong>.') ?></p></div>
<?php elseif (isset($_GET['activate-multi'])) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e('Selected modules <strong>activated</strong>.'); ?></p></div>
<?php elseif ( isset($_GET['deactivate']) ) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e('Module <strong>deactivated</strong>.') ?></p></div>
<?php elseif (isset($_GET['deactivate-multi'])) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e('Selected modules <strong>deactivated</strong>.'); ?></p></div>
<?php elseif ( 'update-selected' == $action ) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e('All selected modules are up to date.'); ?></p></div>
<?php endif; ?>

<div class="wrap">
<h1 class="mcms-heading-inline"><?php
echo esc_html( $title );
?></h1>

<?php

if ( strlen( $s ) ) {
	/* translators: %s: search keywords */
	printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( urldecode( $s ) ) );
}
?>

<hr class="mcms-header-end">

<?php
/**
 * Fires before the modules list table is rendered.
 *
 * This hook also fires before the modules list table is rendered in the Network Admin.
 *
 * Please note: The 'active' portion of the hook name does not refer to whether the current
 * view is for active modules, but rather all modules actively-installed.
 *
 * @since 3.0.0
 *
 * @param array $modules_all An array containing all installed modules.
 */
do_action( 'pre_current_active_modules', $modules['all'] );
?>

<?php $mcms_list_table->views(); ?>

<form class="search-form search-modules" method="get">
<?php $mcms_list_table->search_box( __( 'Search Installed Modules' ), 'module' ); ?>
</form>

<form method="post" id="bulk-action-form">

<input type="hidden" name="module_status" value="<?php echo esc_attr($status) ?>" />
<input type="hidden" name="paged" value="<?php echo esc_attr($page) ?>" />

<?php $mcms_list_table->display(); ?>
</form>

	<span class="spinner"></span>
</div>

<?php
mcms_print_request_filesystem_credentials_modal();
mcms_print_admin_notice_templates();
mcms_print_update_row_templates();

include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
