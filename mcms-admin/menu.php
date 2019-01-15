<?php
/**
 * Build Administration Menu.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Constructs the admin menu.
 *
 * The elements in the array are :
 *     0: Menu item name
 *     1: Minimum level or capability required.
 *     2: The URL of the item's file
 *     3: Class
 *     4: ID
 *     5: Icon for top level menu
 *
 * @global array $menu
 */

$menu[2] = array( __('Main'), 'read', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'dashicons-dashboard' );

$submenu[ 'index.php' ][0] = array( __('Home'), 'read', 'index.php' );

if ( is_multisite() ) {
	$submenu[ 'index.php' ][5] = array( __('My Sites'), 'read', 'my-sites.php' );
}

if ( ! is_multisite() || current_user_can( 'update_core' ) ) {
	$update_data = mcms_get_update_data();
}

if ( ! is_multisite() ) {
	if ( current_user_can( 'update_core' ) ) {
		$cap = 'update_core';
	} elseif ( current_user_can( 'update_modules' ) ) {
		$cap = 'update_modules';
	} elseif ( current_user_can( 'update_myskins' ) ) {
		$cap = 'update_myskins';
	} else {
		$cap = 'update_languages';
	}
	
	unset( $cap );
}

$menu[4] = array( '', 'read', 'separator1', '', 'mcms-menu-separator' );

// $menu[5] = Posts

$menu[10] = array( __('Gallery'), 'upload_files', 'upload.php', '', 'menu-top menu-icon-media', 'menu-media', 'dashicons-admin-media' );
	$submenu['upload.php'][5] = array( __('Gallery'), 'upload_files', 'upload.php');
	/* translators: add new file */
	$submenu['upload.php'][10] = array( _x('Upload New', 'file'), 'upload_files', 'media-new.php');
	$i = 15;
	foreach ( get_taxonomies_for_attachments( 'objects' ) as $tax ) {
		if ( ! $tax->show_ui || ! $tax->show_in_menu )
			continue;

		$submenu['upload.php'][$i++] = array( esc_attr( $tax->labels->menu_name ), $tax->cap->manage_terms, 'edit-tags.php?taxonomy=' . $tax->name . '&amp;post_type=attachment' );
	}
	unset( $tax, $i );

$menu[15] = array( __('Links'), 'manage_links', 'link-manager.php', '', 'menu-top menu-icon-links', 'menu-links', 'dashicons-admin-links' );
	$submenu['link-manager.php'][5] = array( _x('All Links', 'admin menu'), 'manage_links', 'link-manager.php' );
	/* translators: add new links */
	$submenu['link-manager.php'][10] = array( _x('Add New', 'link'), 'manage_links', 'link-add.php' );
	$submenu['link-manager.php'][15] = array( __('Link Categories'), 'manage_categories', 'edit-tags.php?taxonomy=link_category' );

// $menu[20] = Pages

// Avoid the comment count query for users who cannot edit_posts.
if ( current_user_can( 'edit_posts' ) ) {
	$awaiting_mod = mcms_count_comments();
	$awaiting_mod = $awaiting_mod->moderated;
	$menu[25] = array(
		sprintf( __( 'Feedbacks %s' ), '<span class="awaiting-mod count-' . absint( $awaiting_mod ) . '"><span class="pending-count">' . number_format_i18n( $awaiting_mod ) . '</span></span>' ),
		'edit_posts',
		'edit-comments.php',
		'',
		'menu-top menu-icon-comments',
		'menu-comments',
		'dashicons-admin-comments',
	);
	unset( $awaiting_mod );
}

$submenu[ 'edit-comments.php' ][0] = array( __('All Feedbacks'), 'edit_posts', 'edit-comments.php' );

$_mcms_last_object_menu = 25; // The index of the last top-level menu in the object menu group

$types = (array) get_post_types( array('show_ui' => true, '_builtin' => false, 'show_in_menu' => true ) );
$builtin = array( 'post', 'page' );
foreach ( array_merge( $builtin, $types ) as $ptype ) {
	$ptype_obj = get_post_type_object( $ptype );
	// Check if it should be a submenu.
	if ( $ptype_obj->show_in_menu !== true )
		continue;
	$ptype_menu_position = is_int( $ptype_obj->menu_position ) ? $ptype_obj->menu_position : ++$_mcms_last_object_menu; // If we're to use $_mcms_last_object_menu, increment it first.
	$ptype_for_id = sanitize_html_class( $ptype );

	$menu_icon = 'dashicons-admin-post';
	if ( is_string( $ptype_obj->menu_icon ) ) {
		// Special handling for data:image/svg+xml and Dashicons.
		if ( 0 === strpos( $ptype_obj->menu_icon, 'data:image/svg+xml;base64,' ) || 0 === strpos( $ptype_obj->menu_icon, 'dashicons-' ) ) {
			$menu_icon = $ptype_obj->menu_icon;
		} else {
			$menu_icon = esc_url( $ptype_obj->menu_icon );
		}
	} elseif ( in_array( $ptype, $builtin ) ) {
		$menu_icon = 'dashicons-admin-' . $ptype;
	}

	$menu_class = 'menu-top menu-icon-' . $ptype_for_id;
	// 'post' special case
	if ( 'post' === $ptype ) {
		$menu_class .= ' open-if-no-js';
		$ptype_file = "edit.php";
		$post_new_file = "post-new.php";
		$edit_tags_file = "edit-tags.php?taxonomy=%s";
	} else {
		$ptype_file = "edit.php?post_type=$ptype";
		$post_new_file = "post-new.php?post_type=$ptype";
		$edit_tags_file = "edit-tags.php?taxonomy=%s&amp;post_type=$ptype";
	}

	if ( in_array( $ptype, $builtin ) ) {
		$ptype_menu_id = 'menu-' . $ptype_for_id . 's';
	} else {
		$ptype_menu_id = 'menu-posts-' . $ptype_for_id;
	}
	/*
	 * If $ptype_menu_position is already populated or will be populated
	 * by a hard-coded value below, increment the position.
	 */
	$core_menu_positions = array(59, 60, 65, 70, 75, 80, 85, 99);
	while ( isset($menu[$ptype_menu_position]) || in_array($ptype_menu_position, $core_menu_positions) )
		$ptype_menu_position++;

	$menu[$ptype_menu_position] = array( esc_attr( $ptype_obj->labels->menu_name ), $ptype_obj->cap->edit_posts, $ptype_file, '', $menu_class, $ptype_menu_id, $menu_icon );
	$submenu[ $ptype_file ][5]  = array( $ptype_obj->labels->all_items, $ptype_obj->cap->edit_posts,  $ptype_file );
	$submenu[ $ptype_file ][10]  = array( $ptype_obj->labels->add_new, $ptype_obj->cap->create_posts, $post_new_file );

	$i = 15;
	foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
		if ( ! $tax->show_ui || ! $tax->show_in_menu || ! in_array($ptype, (array) $tax->object_type, true) )
			continue;

		$submenu[ $ptype_file ][$i++] = array( esc_attr( $tax->labels->menu_name ), $tax->cap->manage_terms, sprintf( $edit_tags_file, $tax->name ) );
	}
}
unset( $ptype, $ptype_obj, $ptype_for_id, $ptype_menu_position, $menu_icon, $i, $tax, $post_new_file );

$menu[59] = array( '', 'read', 'separator2', '', 'mcms-menu-separator' );

$appearance_cap = current_user_can( 'switch_myskins') ? 'switch_myskins' : 'edit_myskin_options';

$menu[60] = array( __( 'Skin Templates' ), $appearance_cap, 'myskins.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'dashicons-admin-appearance' );
	$submenu['myskins.php'][5] = array( __( 'Skin Templates' ), $appearance_cap, 'myskins.php' );

	$customize_url = add_query_arg( 'return', urlencode( remove_query_arg( mcms_removable_query_args(), mcms_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );
	$submenu['myskins.php'][6] = array( __( 'Live UI-Editor' ), 'customize', esc_url( $customize_url ), '', 'hide-if-no-customize' );

	if ( current_myskin_supports( 'menus' ) || current_myskin_supports( 'widgets' ) ) {
		$submenu['myskins.php'][10] = array( __( 'Navbar' ), 'edit_myskin_options', 'nav-menus.php' );
	}

	unset( $customize_url );

unset( $appearance_cap );

// Add 'Editor' to the bottom of the Dexign menu.
if ( ! is_multisite() ) {
	add_action('admin_menu', '_add_myskins_utility_last', 101);
}
/**
 * Adds the (myskin) 'Editor' link to the bottom of the Dexign menu.
 *
 * @access private
 * @since 3.0.0
 */
function _add_myskins_utility_last() {
	// Must use API on the admin_menu hook, direct modification is only possible on/before the _admin_menu hook
	add_submenu_page('myskins.php', _x('Live Code-Editor', 'myskin editor'), _x('Live Code-Editor', 'myskin editor'), 'edit_myskins', 'myskin-editor.php');
}


unset( $update_data );

if ( current_user_can('list_users') )
	$menu[70] = array( __('Members'), 'list_users', 'users.php', '', 'menu-top menu-icon-users', 'menu-users', 'dashicons-admin-users' );
else
	$menu[70] = array( __('My Profile'), 'read', 'profile.php', '', 'menu-top menu-icon-users', 'menu-users', 'dashicons-admin-users' );

if ( current_user_can('list_users') ) {
	$_mcms_real_parent_file['profile.php'] = 'users.php'; // Back-compat for modules adding submenus to profile.php.
	$submenu['users.php'][5] = array(__('View All'), 'list_users', 'users.php');
	if ( current_user_can( 'create_users' ) ) {
		$submenu['users.php'][10] = array(_x('Create New', 'user'), 'create_users', 'user-new.php');
	} elseif ( is_multisite() ) {
		$submenu['users.php'][10] = array(_x('Create New', 'user'), 'promote_users', 'user-new.php');
	}

	$submenu['users.php'][15] = array(__('My Profile'), 'read', 'profile.php');
} else {
	$_mcms_real_parent_file['users.php'] = 'profile.php';
	$submenu['profile.php'][5] = array(__('My Profile'), 'read', 'profile.php');
	if ( current_user_can( 'create_users' ) ) {
		$submenu['profile.php'][10] = array(__('Create New User'), 'create_users', 'user-new.php');
	} elseif ( is_multisite() ) {
		$submenu['profile.php'][10] = array(__('Create New User'), 'promote_users', 'user-new.php');
	}
}


$change_notice = '';
if ( current_user_can( 'manage_privacy_options' ) && MCMS_Privacy_Policy_Content::text_change_check() ) {
	$change_notice = ' <span class="update-modules 1"><span class="module-count">' . number_format_i18n( 1 ) . '</span></span>';
}

// translators: %s is the update notification bubble, if updates are available.
$menu[80]                               = array( sprintf( __( 'Options %s' ), $change_notice ), 'manage_options', 'options-general.php', '', 'menu-top menu-icon-settings', 'menu-settings', 'dashicons-admin-settings' );
	$submenu['options-general.php'][10] = array( _x( 'General Options', 'settings screen' ), 'manage_options', 'options-general.php' );
	$submenu['options-general.php'][15] = array( __( 'Opt-Writing' ), 'manage_options', 'options-writing.php' );
	$submenu['options-general.php'][20] = array( __( 'Opt-Reading' ), 'manage_options', 'options-reading.php' );
	$submenu['options-general.php'][25] = array( __( 'Opt-Discussion' ), 'manage_options', 'options-discussion.php' );
	$submenu['options-general.php'][30] = array( __( 'Opt-Media' ), 'manage_options', 'options-media.php' );
	$submenu['options-general.php'][40] = array( __( 'Opt-Permalinks' ), 'manage_options', 'options-permalink.php' );
	// translators: %s is the update notification bubble, if updates are available.
	$submenu['options-general.php'][45] = array( sprintf( __( 'Opt-Privacy %s' ), $change_notice ), 'manage_privacy_options', 'privacy.php' );

$_mcms_last_utility_menu = 80; // The index of the last top-level menu in the utility menu group

$menu[99] = array( '', 'read', 'separator-last', '', 'mcms-menu-separator' );

// Back-compat for old top-levels
$_mcms_real_parent_file['post.php'] = 'edit.php';
$_mcms_real_parent_file['post-new.php'] = 'edit.php';
$_mcms_real_parent_file['edit-pages.php'] = 'edit.php?post_type=page';
$_mcms_real_parent_file['page-new.php'] = 'edit.php?post_type=page';
$_mcms_real_parent_file['mcmsmu-admin.php'] = 'tools.php';
$_mcms_real_parent_file['ms-admin.php'] = 'tools.php';

// Ensure backward compatibility.
$compat = array(
	'index' => 'dashboard',
	'edit' => 'posts',
	'post' => 'posts',
	'upload' => 'media',
	'link-manager' => 'links',
	'edit-pages' => 'pages',
	'page' => 'pages',
	'edit-comments' => 'comments',
	'options-general' => 'settings',
	'myskins' => 'appearance',
	);

require_once(BASED_TREE_URI . 'mcms-admin/includes/menu.php');
