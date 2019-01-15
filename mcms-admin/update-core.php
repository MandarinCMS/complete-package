<?php
/**
 * Update Core administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

mcms_enqueue_style( 'module-install' );
mcms_enqueue_script( 'module-install' );
mcms_enqueue_script( 'updates' );
add_thickbox();

if ( is_multisite() && ! is_network_admin() ) {
	mcms_redirect( network_admin_url( 'update-core.php' ) );
	exit();
}

if ( ! current_user_can( 'update_core' ) && ! current_user_can( 'update_myskins' ) && ! current_user_can( 'update_modules' ) && ! current_user_can( 'update_languages' ) )
	mcms_die( __( 'Sorry, you are not allowed to update this site.' ) );

/**
 *
 * @global string $mcms_local_package
 * @global mcmsdb   $mcmsdb
 *
 * @staticvar bool $first_pass
 *
 * @param object $update
 */
function list_core_update( $update ) {
 	global $mcms_local_package, $mcmsdb;
  	static $first_pass = true;

	$mcms_version = get_bloginfo( 'version' );

 	if ( 'en_US' == $update->locale && 'en_US' == get_locale() )
 		$version_string = $update->current;
 	// If the only available update is a partial builds, it doesn't need a language-specific version string.
 	elseif ( 'en_US' == $update->locale && $update->packages->partial && $mcms_version == $update->partial_version && ( $updates = get_core_updates() ) && 1 == count( $updates ) )
 		$version_string = $update->current;
 	else
 		$version_string = sprintf( "%s&ndash;<strong>%s</strong>", $update->current, $update->locale );

	$current = false;
	if ( !isset($update->response) || 'latest' == $update->response )
		$current = true;
	$submit = __('Update Now');
	$form_action = 'update-core.php?action=do-core-upgrade';
	$php_version    = phpversion();
	$mysql_version  = $mcmsdb->db_version();
	$show_buttons = true;
	if ( 'development' == $update->response ) {
		$message = __('You are using a development version of MandarinCMS. You can update to the latest nightly build automatically:');
	} else {
		if ( $current ) {
			$message = sprintf( __( 'If you need to re-install version %s, you can do so here:' ), $version_string );
			$submit = __('Re-install Now');
			$form_action = 'update-core.php?action=do-core-reinstall';
		} else {
			$php_compat     = version_compare( $php_version, $update->php_version, '>=' );
			if ( file_exists( MCMS_CONTENT_DIR . '/db.php' ) && empty( $mcmsdb->is_mysql ) )
				$mysql_compat = true;
			else
				$mysql_compat = version_compare( $mysql_version, $update->mysql_version, '>=' );

			if ( !$mysql_compat && !$php_compat )
				/* translators: 1: MandarinCMS version number, 2: Minimum required PHP version number, 3: Minimum required MySQL version number, 4: Current PHP version number, 5: Current MySQL version number */
				$message = sprintf( __('You cannot update because <a href="https://dev.mandarincms.com/Version_%1$s">MandarinCMS %1$s</a> requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $update->current, $update->php_version, $update->mysql_version, $php_version, $mysql_version );
			elseif ( !$php_compat )
				/* translators: 1: MandarinCMS version number, 2: Minimum required PHP version number, 3: Current PHP version number */
				$message = sprintf( __('You cannot update because <a href="https://dev.mandarincms.com/Version_%1$s">MandarinCMS %1$s</a> requires PHP version %2$s or higher. You are running version %3$s.'), $update->current, $update->php_version, $php_version );
			elseif ( !$mysql_compat )
				/* translators: 1: MandarinCMS version number, 2: Minimum required MySQL version number, 3: Current MySQL version number */
				$message = sprintf( __('You cannot update because <a href="https://dev.mandarincms.com/Version_%1$s">MandarinCMS %1$s</a> requires MySQL version %2$s or higher. You are running version %3$s.'), $update->current, $update->mysql_version, $mysql_version );
			else
				/* translators: 1: MandarinCMS version number, 2: MandarinCMS version number including locale if necessary */
				$message = 	sprintf(__('You can update to <a href="https://dev.mandarincms.com/Version_%1$s">MandarinCMS %2$s</a> automatically:'), $update->current, $version_string);
			if ( !$mysql_compat || !$php_compat )
				$show_buttons = false;
		}
	}

	echo '<p>';
	echo $message;
	echo '</p>';
	echo '<form method="post" action="' . $form_action . '" name="upgrade" class="upgrade">';
	mcms_nonce_field('upgrade-core');
	echo '<p>';
	echo '<input name="version" value="'. esc_attr($update->current) .'" type="hidden"/>';
	echo '<input name="locale" value="'. esc_attr($update->locale) .'" type="hidden"/>';
	if ( $show_buttons ) {
		if ( $first_pass ) {
			submit_button( $submit, $current ? '' : 'primary regular', 'upgrade', false );
			$first_pass = false;
		} else {
			submit_button( $submit, '', 'upgrade', false );
		}
	}
	if ( 'en_US' != $update->locale )
		if ( !isset( $update->dismissed ) || !$update->dismissed )
			submit_button( __( 'Hide this update' ), '', 'dismiss', false );
		else
			submit_button( __( 'Bring back this update' ), '', 'undismiss', false );
	echo '</p>';
	if ( 'en_US' != $update->locale && ( !isset($mcms_local_package) || $mcms_local_package != $update->locale ) )
	    echo '<p class="hint">'.__('This localized version contains both the translation and various other localization fixes. You can skip upgrading if you want to keep your current translation.').'</p>';
	// Partial builds don't need language-specific warnings.
	elseif ( 'en_US' == $update->locale && get_locale() != 'en_US' && ( ! $update->packages->partial && $mcms_version == $update->partial_version ) ) {
	    echo '<p class="hint">'.sprintf( __('You are about to install MandarinCMS %s <strong>in English (US).</strong> There is a chance this update will break your translation. You may prefer to wait for the localized version to be released.'), $update->response != 'development' ? $update->current : '' ).'</p>';
	}
	echo '</form>';

}

/**
 * @since 2.7.0
 */
function dismissed_updates() {
	$dismissed = get_core_updates( array( 'dismissed' => true, 'available' => false ) );
	if ( $dismissed ) {

		$show_text = esc_js(__('Show hidden updates'));
		$hide_text = esc_js(__('Hide hidden updates'));
	?>
	<script type="text/javascript">

		jQuery(function($) {
			$('dismissed-updates').show();
			$('#show-dismissed').toggle(function(){$(this).text('<?php echo $hide_text; ?>');}, function() {$(this).text('<?php echo $show_text; ?>')});
			$('#show-dismissed').click(function() { $('#dismissed-updates').toggle('slow');});
		});
	</script>
	<?php
		echo '<p class="hide-if-no-js"><a id="show-dismissed" href="#">'.__('Show hidden updates').'</a></p>';
		echo '<ul id="dismissed-updates" class="core-updates dismissed">';
		foreach ( (array) $dismissed as $update) {
			echo '<li>';
			list_core_update( $update );
			echo '</li>';
		}
		echo '</ul>';
	}
}

/**
 * Display upgrade MandarinCMS for downloading latest or upgrading automatically form.
 *
 * @since 2.7.0
 *
 * @global string $required_php_version
 * @global string $required_mysql_version
 */
function core_upgrade_preamble() {
	global $required_php_version, $required_mysql_version;

	$mcms_version = get_bloginfo( 'version' );
	$updates = get_core_updates();

	if ( !isset($updates[0]->response) || 'latest' == $updates[0]->response ) {
		echo '<h2>';
		_e('You have the latest version of MandarinCMS.');

		if ( mcms_http_supports( array( 'ssl' ) ) ) {
			require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
			$upgrader = new MCMS_Automatic_Updater;
			$future_minor_update = (object) array(
				'current'       => $mcms_version . '.1.next.minor',
				'version'       => $mcms_version . '.1.next.minor',
				'php_version'   => $required_php_version,
				'mysql_version' => $required_mysql_version,
			);
			$should_auto_update = $upgrader->should_update( 'core', $future_minor_update, BASED_TREE_URI );
			if ( $should_auto_update )
				echo ' ' . __( 'Future security updates will be applied automatically.' );
		}
		echo '</h2>';
	} else {
		echo '<div class="notice notice-warning"><p>';
		_e('<strong>Important:</strong> before updating, please <a href="https://dev.mandarincms.com/MandarinCMS_Backups">back up your database and files</a>. For help with updates, visit the <a href="https://dev.mandarincms.com/Updating_MandarinCMS">Updating MandarinCMS</a> Codex page.');
		echo '</p></div>';

		echo '<h2 class="response">';
		_e( 'An updated version of MandarinCMS is available.' );
		echo '</h2>';
	}

	if ( isset( $updates[0] ) && $updates[0]->response == 'development' ) {
		require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
		$upgrader = new MCMS_Automatic_Updater;
		if ( mcms_http_supports( 'ssl' ) && $upgrader->should_update( 'core', $updates[0], BASED_TREE_URI ) ) {
			echo '<div class="updated inline"><p>';
			echo '<strong>' . __( 'BETA TESTERS:' ) . '</strong> ' . __( 'This site is set up to install updates of future beta versions automatically.' );
			echo '</p></div>';
		}
	}

	echo '<ul class="core-updates">';
	foreach ( (array) $updates as $update ) {
		echo '<li>';
		list_core_update( $update );
		echo '</li>';
	}
	echo '</ul>';
	// Don't show the maintenance mode notice when we are only showing a single re-install option.
	if ( $updates && ( count( $updates ) > 1 || $updates[0]->response != 'latest' ) ) {
		echo '<p>' . __( 'While your site is being updated, it will be in maintenance mode. As soon as your updates are complete, your site will return to normal.' ) . '</p>';
	} elseif ( ! $updates ) {
		list( $normalized_version ) = explode( '-', $mcms_version );
		echo '<p>' . sprintf( __( '<a href="%s">Learn more about MandarinCMS %s</a>.' ), esc_url( self_admin_url( 'about.php' ) ), $normalized_version ) . '</p>';
	}
	dismissed_updates();
}

function list_module_updates() {
	$mcms_version = get_bloginfo( 'version' );
	$cur_mcms_version = preg_replace( '/-.*$/', '', $mcms_version );

	require_once(BASED_TREE_URI . 'mcms-admin/includes/module-install.php');
	$modules = get_module_updates();
	if ( empty( $modules ) ) {
		echo '<h2>' . __( 'Modules' ) . '</h2>';
		echo '<p>' . __( 'Your modules are all up to date.' ) . '</p>';
		return;
	}
	$form_action = 'update-core.php?action=do-module-upgrade';

	$core_updates = get_core_updates();
	if ( !isset($core_updates[0]->response) || 'latest' == $core_updates[0]->response || 'development' == $core_updates[0]->response || version_compare( $core_updates[0]->current, $cur_mcms_version, '=') )
		$core_update_version = false;
	else
		$core_update_version = $core_updates[0]->current;
	?>
<h2><?php _e( 'Modules' ); ?></h2>
<p><?php _e( 'The following modules have new versions available. Check the ones you want to update and then click &#8220;Update Modules&#8221;.' ); ?></p>
<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-modules" class="upgrade">
<?php mcms_nonce_field('upgrade-core'); ?>
<p><input id="upgrade-modules" class="button" type="submit" value="<?php esc_attr_e('Update Modules'); ?>" name="upgrade" /></p>
<table class="widefat updates-table" id="update-modules-table">
	<thead>
	<tr>
		<td class="manage-column check-column"><input type="checkbox" id="modules-select-all" /></td>
		<td class="manage-column"><label for="modules-select-all"><?php _e( 'Select All' ); ?></label></td>
	</tr>
	</thead>

	<tbody class="modules">
<?php
	foreach ( (array) $modules as $module_file => $module_data ) {
		$module_data = (object) _get_module_data_markup_translate( $module_file, (array) $module_data, false, true );

		$icon = '<span class="dashicons dashicons-admin-modules"></span>';
		$preferred_icons = array( 'svg', '1x', '2x', 'default' );
		foreach ( $preferred_icons as $preferred_icon ) {
			if ( ! empty( $module_data->update->icons[ $preferred_icon ] ) ) {
				$icon = '<img src="' . esc_url( $module_data->update->icons[ $preferred_icon ] ) . '" alt="" />';
				break;
			}			
		}

		// Get module compat for running version of MandarinCMS.
		if ( isset($module_data->update->tested) && version_compare($module_data->update->tested, $cur_mcms_version, '>=') ) {
			$compat = '<br />' . sprintf(__('Compatibility with MandarinCMS %1$s: 100%% (according to its author)'), $cur_mcms_version);
		} elseif ( isset($module_data->update->compatibility->{$cur_mcms_version}) ) {
			$compat = $module_data->update->compatibility->{$cur_mcms_version};
			$compat = '<br />' . sprintf(__('Compatibility with MandarinCMS %1$s: %2$d%% (%3$d "works" votes out of %4$d total)'), $cur_mcms_version, $compat->percent, $compat->votes, $compat->total_votes);
		} else {
			$compat = '<br />' . sprintf(__('Compatibility with MandarinCMS %1$s: Unknown'), $cur_mcms_version);
		}
		// Get module compat for updated version of MandarinCMS.
		if ( $core_update_version ) {
			if ( isset( $module_data->update->tested ) && version_compare( $module_data->update->tested, $core_update_version, '>=' ) ) {
				$compat .= '<br />' . sprintf( __( 'Compatibility with MandarinCMS %1$s: 100%% (according to its author)' ), $core_update_version );
			} elseif ( isset( $module_data->update->compatibility->{$core_update_version} ) ) {
				$update_compat = $module_data->update->compatibility->{$core_update_version};
				$compat .= '<br />' . sprintf(__('Compatibility with MandarinCMS %1$s: %2$d%% (%3$d "works" votes out of %4$d total)'), $core_update_version, $update_compat->percent, $update_compat->votes, $update_compat->total_votes);
			} else {
				$compat .= '<br />' . sprintf(__('Compatibility with MandarinCMS %1$s: Unknown'), $core_update_version);
			}
		}
		// Get the upgrade notice for the new module version.
		if ( isset($module_data->update->upgrade_notice) ) {
			$upgrade_notice = '<br />' . strip_tags($module_data->update->upgrade_notice);
		} else {
			$upgrade_notice = '';
		}

		$details_url = self_admin_url('module-install.php?tab=module-information&module=' . $module_data->update->slug . '&section=changelog&TB_iframe=true&width=640&height=662');
		$details = sprintf(
			'<a href="%1$s" class="thickbox open-module-details-modal" aria-label="%2$s">%3$s</a>',
			esc_url( $details_url ),
			/* translators: 1: module name, 2: version number */
			esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $module_data->Name, $module_data->update->new_version ) ),
			/* translators: %s: module version */
			sprintf( __( 'View version %s details.' ), $module_data->update->new_version )
		);

		$checkbox_id = "checkbox_" . md5( $module_data->Name );
		?>
		<tr>
			<td class="check-column">
				<input type="checkbox" name="checked[]" id="<?php echo $checkbox_id; ?>" value="<?php echo esc_attr( $module_file ); ?>" />
				<label for="<?php echo $checkbox_id; ?>" class="screen-reader-text"><?php
					/* translators: %s: module name */
					printf( __( 'Select %s' ),
						$module_data->Name
					);
				?></label>
			</td>
			<td class="module-title"><p>
				<?php echo $icon; ?>
				<strong><?php echo $module_data->Name; ?></strong>
				<?php
					/* translators: 1: module version, 2: new version */
					printf( __( 'You have version %1$s installed. Update to %2$s.' ),
						$module_data->Version,
						$module_data->update->new_version
					);
					echo ' ' . $details . $compat . $upgrade_notice;
				?>
			</p></td>
		</tr>
		<?php
	}
?>
	</tbody>

	<tfoot>
	<tr>
		<td class="manage-column check-column"><input type="checkbox" id="modules-select-all-2" /></td>
		<td class="manage-column"><label for="modules-select-all-2"><?php _e( 'Select All' ); ?></label></td>
	</tr>
	</tfoot>
</table>
<p><input id="upgrade-modules-2" class="button" type="submit" value="<?php esc_attr_e('Update Modules'); ?>" name="upgrade" /></p>
</form>
<?php
}

/**
 * @since 2.9.0
 */
function list_myskin_updates() {
	$myskins = get_myskin_updates();
	if ( empty( $myskins ) ) {
		echo '<h2>' . __( 'MySkins' ) . '</h2>';
		echo '<p>' . __( 'Your myskins are all up to date.' ) . '</p>';
		return;
	}

	$form_action = 'update-core.php?action=do-myskin-upgrade';
?>
<h2><?php _e( 'MySkins' ); ?></h2>
<p><?php _e( 'The following myskins have new versions available. Check the ones you want to update and then click &#8220;Update MySkins&#8221;.' ); ?></p>
<p><?php printf( __( '<strong>Please Note:</strong> Any customizations you have made to myskin files will be lost. Please consider using <a href="%s">child myskins</a> for modifications.' ), __( 'https://dev.mandarincms.com/Child_MySkins' ) ); ?></p>
<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-myskins" class="upgrade">
<?php mcms_nonce_field('upgrade-core'); ?>
<p><input id="upgrade-myskins" class="button" type="submit" value="<?php esc_attr_e('Update MySkins'); ?>" name="upgrade" /></p>
<table class="widefat updates-table" id="update-myskins-table">
	<thead>
	<tr>
		<td class="manage-column check-column"><input type="checkbox" id="myskins-select-all" /></td>
		<td class="manage-column"><label for="myskins-select-all"><?php _e( 'Select All' ); ?></label></td>
	</tr>
	</thead>

	<tbody class="modules">
<?php
	foreach ( $myskins as $stylesheet => $myskin ) {
		$checkbox_id = 'checkbox_' . md5( $myskin->get( 'Name' ) );
		?>
		<tr>
			<td class="check-column">
				<input type="checkbox" name="checked[]" id="<?php echo $checkbox_id; ?>" value="<?php echo esc_attr( $stylesheet ); ?>" />
				<label for="<?php echo $checkbox_id; ?>" class="screen-reader-text"><?php
					/* translators: %s: myskin name */
					printf( __( 'Select %s' ),
						$myskin->display( 'Name' )
					);
				?></label>
			</td>
			<td class="module-title"><p>
				<img src="<?php echo esc_url( $myskin->get_screenshot() ); ?>" width="85" height="64" class="updates-table-screenshot" alt="" />
				<strong><?php echo $myskin->display( 'Name' ); ?></strong>
				<?php
					/* translators: 1: myskin version, 2: new version */
					printf( __( 'You have version %1$s installed. Update to %2$s.' ),
						$myskin->display( 'Version' ),
						$myskin->update['new_version']
					);
				?>
			</p></td>
		</tr>
		<?php
	}
?>
	</tbody>

	<tfoot>
	<tr>
		<td class="manage-column check-column"><input type="checkbox" id="myskins-select-all-2" /></td>
		<td class="manage-column"><label for="myskins-select-all-2"><?php _e( 'Select All' ); ?></label></td>
	</tr>
	</tfoot>
</table>
<p><input id="upgrade-myskins-2" class="button" type="submit" value="<?php esc_attr_e('Update MySkins'); ?>" name="upgrade" /></p>
</form>
<?php
}

/**
 * @since 3.7.0
 */
function list_translation_updates() {
	$updates = mcms_get_translation_updates();
	if ( ! $updates ) {
		if ( 'en_US' != get_locale() ) {
			echo '<h2>' . __( 'Translations' ) . '</h2>';
			echo '<p>' . __( 'Your translations are all up to date.' ) . '</p>';
		}
		return;
	}

	$form_action = 'update-core.php?action=do-translation-upgrade';
	?>
	<h2><?php _e( 'Translations' ); ?></h2>
	<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-translations" class="upgrade">
		<p><?php _e( 'New translations are available.' ); ?></p>
		<?php mcms_nonce_field( 'upgrade-translations' ); ?>
		<p><input class="button" type="submit" value="<?php esc_attr_e( 'Update Translations' ); ?>" name="upgrade" /></p>
	</form>
	<?php
}

/**
 * Upgrade MandarinCMS core display.
 *
 * @since 2.7.0
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 *
 * @param bool $reinstall
 */
function do_core_upgrade( $reinstall = false ) {
	global $mcms_filesystem;

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

	if ( $reinstall )
		$url = 'update-core.php?action=do-core-reinstall';
	else
		$url = 'update-core.php?action=do-core-upgrade';
	$url = mcms_nonce_url($url, 'upgrade-core');

	$version = isset( $_POST['version'] )? $_POST['version'] : false;
	$locale = isset( $_POST['locale'] )? $_POST['locale'] : 'en_US';
	$update = find_core_update( $version, $locale );
	if ( !$update )
		return;

	// Allow relaxed file ownership writes for User-initiated upgrades when the API specifies
	// that it's safe to do so. This only happens when there are no new files to create.
	$allow_relaxed_file_ownership = ! $reinstall && isset( $update->new_files ) && ! $update->new_files;

?>
	<div class="wrap">
	<h1><?php _e( 'Update MandarinCMS' ); ?></h1>
<?php

	if ( false === ( $credentials = request_filesystem_credentials( $url, '', false, BASED_TREE_URI, array( 'version', 'locale' ), $allow_relaxed_file_ownership ) ) ) {
		echo '</div>';
		return;
	}

	if ( ! MCMS_Filesystem( $credentials, BASED_TREE_URI, $allow_relaxed_file_ownership ) ) {
		// Failed to connect, Error and request again
		request_filesystem_credentials( $url, '', true, BASED_TREE_URI, array( 'version', 'locale' ), $allow_relaxed_file_ownership );
		echo '</div>';
		return;
	}

	if ( $mcms_filesystem->errors->get_error_code() ) {
		foreach ( $mcms_filesystem->errors->get_error_messages() as $message )
			show_message($message);
		echo '</div>';
		return;
	}

	if ( $reinstall )
		$update->response = 'reinstall';

	add_filter( 'update_feedback', 'show_message' );

	$upgrader = new Core_Upgrader();
	$result = $upgrader->upgrade( $update, array(
		'allow_relaxed_file_ownership' => $allow_relaxed_file_ownership
	) );

	if ( is_mcms_error($result) ) {
		show_message($result);
		if ( 'up_to_date' != $result->get_error_code() && 'locked' != $result->get_error_code() )
			show_message( __('Installation Failed') );
		echo '</div>';
		return;
	}

	show_message( __('MandarinCMS updated successfully') );
	show_message( '<span class="hide-if-no-js">' . sprintf( __( 'Welcome to MandarinCMS %1$s. You will be redirected to the About MandarinCMS screen. If not, click <a href="%2$s">here</a>.' ), $result, esc_url( self_admin_url( 'about.php?updated' ) ) ) . '</span>' );
	show_message( '<span class="hide-if-js">' . sprintf( __( 'Welcome to MandarinCMS %1$s. <a href="%2$s">Learn more</a>.' ), $result, esc_url( self_admin_url( 'about.php?updated' ) ) ) . '</span>' );
	?>
	</div>
	<script type="text/javascript">
	window.location = '<?php echo self_admin_url( 'about.php?updated' ); ?>';
	</script>
	<?php
}

/**
 * @since 2.7.0
 */
function do_dismiss_core_update() {
	$version = isset( $_POST['version'] )? $_POST['version'] : false;
	$locale = isset( $_POST['locale'] )? $_POST['locale'] : 'en_US';
	$update = find_core_update( $version, $locale );
	if ( !$update )
		return;
	dismiss_core_update( $update );
	mcms_redirect( mcms_nonce_url('update-core.php?action=upgrade-core', 'upgrade-core') );
	exit;
}

/**
 * @since 2.7.0
 */
function do_undismiss_core_update() {
	$version = isset( $_POST['version'] )? $_POST['version'] : false;
	$locale = isset( $_POST['locale'] )? $_POST['locale'] : 'en_US';
	$update = find_core_update( $version, $locale );
	if ( !$update )
		return;
	undismiss_core_update( $version, $locale );
	mcms_redirect( mcms_nonce_url('update-core.php?action=upgrade-core', 'upgrade-core') );
	exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'upgrade-core';

$upgrade_error = false;
if ( ( 'do-myskin-upgrade' == $action || ( 'do-module-upgrade' == $action && ! isset( $_GET['modules'] ) ) )
	&& ! isset( $_POST['checked'] ) ) {
	$upgrade_error = $action == 'do-myskin-upgrade' ? 'myskins' : 'modules';
	$action = 'upgrade-core';
}

$title = __('MandarinCMS Updates');
$parent_file = 'index.php';

$updates_overview  = '<p>' . __( 'On this screen, you can update to the latest version of MandarinCMS, as well as update your myskins, modules, and translations from the MandarinCMS.org repositories.' ) . '</p>';
$updates_overview .= '<p>' . __( 'If an update is available, you&#8127;ll see a notification appear in the Toolbar and navigation menu.' ) . ' ' . __( 'Keeping your site updated is important for security. It also makes the internet a safer place for you and your readers.' ) . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __( 'Overview' ),
	'content' => $updates_overview
) );

$updates_howto  = '<p>' . __( '<strong>MandarinCMS</strong> &mdash; Updating your MandarinCMS installation is a simple one-click procedure: just <strong>click on the &#8220;Update Now&#8221; button</strong> when you are notified that a new version is available.' ) . ' ' . __( 'In most cases, MandarinCMS will automatically apply maintenance and security updates in the background for you.' ) . '</p>';
$updates_howto .= '<p>' . __( '<strong>MySkins and Modules</strong> &mdash; To update individual myskins or modules from this screen, use the checkboxes to make your selection, then <strong>click on the appropriate &#8220;Update&#8221; button</strong>. To update all of your myskins or modules at once, you can check the box at the top of the section to select all before clicking the update button.' ) . '</p>';

if ( 'en_US' != get_locale() ) {
	$updates_howto .= '<p>' . __( '<strong>Translations</strong> &mdash; The files translating MandarinCMS into your language are updated for you whenever any other updates occur. But if these files are out of date, you can <strong>click the &#8220;Update Translations&#8221;</strong> button.' ) . '</p>';
}

get_current_screen()->add_help_tab( array(
	'id'      => 'how-to-update',
	'title'   => __( 'How to Update' ),
	'content' => $updates_howto
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __( '<a href="https://dev.mandarincms.com/Dashboard_Updates_Screen">Documentation on Updating MandarinCMS</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://mandarincms.com/support/">Support Forums</a>' ) . '</p>'
);

if ( 'upgrade-core' == $action ) {
	// Force a update check when requested
	$force_check = ! empty( $_GET['force-check'] );
	mcms_version_check( array(), $force_check );

	require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');
	?>
	<div class="wrap">
	<h1><?php _e( 'MandarinCMS Updates' ); ?></h1>
	<?php
	if ( $upgrade_error ) {
		echo '<div class="error"><p>';
		if ( $upgrade_error == 'myskins' )
			_e('Please select one or more myskins to update.');
		else
			_e('Please select one or more modules to update.');
		echo '</p></div>';
	}

	$last_update_check = false;
	$current = get_site_transient( 'update_core' );

	if ( $current && isset ( $current->last_checked ) )	{
		$last_update_check = $current->last_checked + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
	}

	echo '<p>';
	/* translators: %1 date, %2 time. */
	printf( __( 'Last checked on %1$s at %2$s.' ), date_i18n( __( 'F j, Y' ), $last_update_check ), date_i18n( __( 'g:i a' ), $last_update_check ) );
	echo ' &nbsp; <a class="button" href="' . esc_url( self_admin_url('update-core.php?force-check=1') ) . '">' . __( 'Check Again' ) . '</a>';
	echo '</p>';

	if ( current_user_can( 'update_core' ) ) {
		core_upgrade_preamble();
	}
	if ( current_user_can( 'update_modules' ) ) {
		list_module_updates();
	}
	if ( current_user_can( 'update_myskins' ) ) {
		list_myskin_updates();
	}
	if ( current_user_can( 'update_languages' ) ) {
		list_translation_updates();
	}

	/**
	 * Fires after the core, module, and myskin update tables.
	 *
	 * @since 2.9.0
	 */
	do_action( 'core_upgrade_preamble' );
	echo '</div>';

	mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
		'totals'  => mcms_get_update_data(),
	) );

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

} elseif ( 'do-core-upgrade' == $action || 'do-core-reinstall' == $action ) {

	if ( ! current_user_can( 'update_core' ) )
		mcms_die( __( 'Sorry, you are not allowed to update this site.' ) );

	check_admin_referer('upgrade-core');

	// Do the (un)dismiss actions before headers, so that they can redirect.
	if ( isset( $_POST['dismiss'] ) )
		do_dismiss_core_update();
	elseif ( isset( $_POST['undismiss'] ) )
		do_undismiss_core_update();

	require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');
	if ( 'do-core-reinstall' == $action )
		$reinstall = true;
	else
		$reinstall = false;

	if ( isset( $_POST['upgrade'] ) )
		do_core_upgrade($reinstall);

	mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
		'totals'  => mcms_get_update_data(),
	) );

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

} elseif ( 'do-module-upgrade' == $action ) {

	if ( ! current_user_can( 'update_modules' ) )
		mcms_die( __( 'Sorry, you are not allowed to update this site.' ) );

	check_admin_referer('upgrade-core');

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', $_GET['modules'] );
	} elseif ( isset( $_POST['checked'] ) ) {
		$modules = (array) $_POST['checked'];
	} else {
		mcms_redirect( admin_url('update-core.php') );
		exit;
	}

	$url = 'update.php?action=update-selected&modules=' . urlencode(implode(',', $modules));
	$url = mcms_nonce_url($url, 'bulk-update-modules');

	$title = __('Update Modules');

	require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');
	echo '<div class="wrap">';
	echo '<h1>' . __( 'Update Modules' ) . '</h1>';
	echo '<iframe src="', $url, '" style="width: 100%; height: 100%; min-height: 750px;" frameborder="0" title="' . esc_attr__( 'Update progress' ) . '"></iframe>';
	echo '</div>';

	mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
		'totals'  => mcms_get_update_data(),
	) );

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

} elseif ( 'do-myskin-upgrade' == $action ) {

	if ( ! current_user_can( 'update_myskins' ) )
		mcms_die( __( 'Sorry, you are not allowed to update this site.' ) );

	check_admin_referer('upgrade-core');

	if ( isset( $_GET['myskins'] ) ) {
		$myskins = explode( ',', $_GET['myskins'] );
	} elseif ( isset( $_POST['checked'] ) ) {
		$myskins = (array) $_POST['checked'];
	} else {
		mcms_redirect( admin_url('update-core.php') );
		exit;
	}

	$url = 'update.php?action=update-selected-myskins&myskins=' . urlencode(implode(',', $myskins));
	$url = mcms_nonce_url($url, 'bulk-update-myskins');

	$title = __('Update MySkins');

	require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');
	?>
	<div class="wrap">
		<h1><?php _e( 'Update MySkins' ); ?></h1>
		<iframe src="<?php echo $url ?>" style="width: 100%; height: 100%; min-height: 750px;" frameborder="0" title="<?php esc_attr_e( 'Update progress' ); ?>"></iframe>
	</div>
	<?php

	mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
		'totals'  => mcms_get_update_data(),
	) );

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

} elseif ( 'do-translation-upgrade' == $action ) {

	if ( ! current_user_can( 'update_languages' ) )
		mcms_die( __( 'Sorry, you are not allowed to update this site.' ) );

	check_admin_referer( 'upgrade-translations' );

	require_once( BASED_TREE_URI . 'mcms-admin/admin-header.php' );
	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

	$url = 'update-core.php?action=do-translation-upgrade';
	$nonce = 'upgrade-translations';
	$title = __( 'Update Translations' );
	$context = MCMS_LANG_DIR;

	$upgrader = new Language_Pack_Upgrader( new Language_Pack_Upgrader_Skin( compact( 'url', 'nonce', 'title', 'context' ) ) );
	$result = $upgrader->bulk_upgrade();

	mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
		'totals'  => mcms_get_update_data(),
	) );

	require_once( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );

} else {
	/**
	 * Fires for each custom update action on the MandarinCMS Updates screen.
	 *
	 * The dynamic portion of the hook name, `$action`, refers to the
	 * passed update action. The hook fires in lieu of all available
	 * default update actions.
	 *
	 * @since 3.2.0
	 */
	do_action( "update-core-custom_{$action}" );
}
