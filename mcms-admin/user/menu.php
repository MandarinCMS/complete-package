<?php
/**
 * Build User Administration Menu.
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

$menu[2] = array(__('Dashboard'), 'exist', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'dashicons-dashboard');

$menu[4] = array( '', 'exist', 'separator1', '', 'mcms-menu-separator' );

$menu[70] = array( __('Profile'), 'exist', 'profile.php', '', 'menu-top menu-icon-users', 'menu-users', 'dashicons-admin-users' );

$menu[99] = array( '', 'exist', 'separator-last', '', 'mcms-menu-separator' );

$_mcms_real_parent_file['users.php'] = 'profile.php';
$compat = array();
$submenu = array();

require_once(BASED_TREE_URI . 'mcms-admin/includes/menu.php');
