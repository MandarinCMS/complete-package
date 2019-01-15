<?php
/**
 * Build Network Administration Menu.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

/* translators: Network menu item */
$menu[2] = array(__('Dashboard'), 'manage_network', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'dashicons-dashboard');

$submenu['index.php'][0] = array( __( 'Home' ), 'read', 'index.php' );

if ( current_user_can( 'update_core' ) ) {
	$cap = 'update_core';
} elseif ( current_user_can( 'update_modules' ) ) {
	$cap = 'update_modules';
} elseif ( current_user_can( 'update_myskins' ) ) {
	$cap = 'update_myskins';
} else {
	$cap = 'update_languages';
}

$update_data = mcms_get_update_data();
if ( $update_data['counts']['total'] ) {
	$submenu['index.php'][10] = array( sprintf( __( 'Updates %s' ), "<span class='update-modules count-{$update_data['counts']['total']}'><span class='update-count'>" . number_format_i18n( $update_data['counts']['total'] ) . "</span></span>" ), $cap, 'update-core.php' );
} else {
	$submenu['index.php'][10] = array( __( 'Updates' ), $cap, 'update-core.php' );
}

unset( $cap );

$submenu['index.php'][15] = array( __( 'Upgrade Network' ), 'upgrade_network', 'upgrade.php' );

$menu[4] = array( '', 'read', 'separator1', '', 'mcms-menu-separator' );

/* translators: Sites menu item */
$menu[5] = array(__('Sites'), 'manage_sites', 'sites.php', '', 'menu-top menu-icon-site', 'menu-site', 'dashicons-admin-multisite');
$submenu['sites.php'][5]  = array( __('All Sites'), 'manage_sites', 'sites.php' );
$submenu['sites.php'][10]  = array( _x('Add New', 'site'), 'create_sites', 'site-new.php' );

$menu[10] = array(__('Users'), 'manage_network_users', 'users.php', '', 'menu-top menu-icon-users', 'menu-users', 'dashicons-admin-users');
$submenu['users.php'][5]  = array( __('All Users'), 'manage_network_users', 'users.php' );
$submenu['users.php'][10]  = array( _x('Add New', 'user'), 'create_users', 'user-new.php' );

if ( current_user_can( 'update_myskins' ) && $update_data['counts']['myskins'] ) {
	$menu[15] = array(sprintf( __( 'MySkins %s' ), "<span class='update-modules count-{$update_data['counts']['myskins']}'><span class='myskin-count'>" . number_format_i18n( $update_data['counts']['myskins'] ) . "</span></span>" ), 'manage_network_myskins', 'myskins.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'dashicons-admin-appearance' );
} else {
	$menu[15] = array( __( 'MySkins' ), 'manage_network_myskins', 'myskins.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'dashicons-admin-appearance' );
}
$submenu['myskins.php'][5]  = array( __('Installed MySkins'), 'manage_network_myskins', 'myskins.php' );
$submenu['myskins.php'][10] = array( _x('Add New', 'myskin'), 'install_myskins', 'myskin-install.php' );
$submenu['myskins.php'][15] = array( _x('Editor', 'myskin editor'), 'edit_myskins', 'myskin-editor.php' );

if ( current_user_can( 'update_modules' ) && $update_data['counts']['modules'] ) {
	$menu[20] = array( sprintf( __( 'Modules %s' ), "<span class='update-modules count-{$update_data['counts']['modules']}'><span class='module-count'>" . number_format_i18n( $update_data['counts']['modules'] ) . "</span></span>" ), 'manage_network_modules', 'modules.php', '', 'menu-top menu-icon-modules', 'menu-modules', 'dashicons-admin-modules');
} else {
	$menu[20] = array( __('Modules'), 'manage_network_modules', 'modules.php', '', 'menu-top menu-icon-modules', 'menu-modules', 'dashicons-admin-modules' );
}
$submenu['modules.php'][5]  = array( __('Installed Modules'), 'manage_network_modules', 'modules.php' );
$submenu['modules.php'][10] = array( _x('Add New', 'module'), 'install_modules', 'module-install.php' );
$submenu['modules.php'][15] = array( _x('Editor', 'module editor'), 'edit_modules', 'module-editor.php' );

$menu[25] = array(__('Settings'), 'manage_network_options', 'settings.php', '', 'menu-top menu-icon-settings', 'menu-settings', 'dashicons-admin-settings');
if ( defined( 'MULTISITE' ) && defined( 'MCMS_ALLOW_MULTISITE' ) && MCMS_ALLOW_MULTISITE ) {
	$submenu['settings.php'][5]  = array( __('Network Settings'), 'manage_network_options', 'settings.php' );
	$submenu['settings.php'][10] = array( __('Network Setup'), 'setup_network', 'setup.php' );
}
unset($update_data);

$menu[99] = array( '', 'exist', 'separator-last', '', 'mcms-menu-separator' );

require_once(BASED_TREE_URI . 'mcms-admin/includes/menu.php');
