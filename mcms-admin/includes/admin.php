<?php
/**
 * Core Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 2.3.0
 */

if ( ! defined('MCMS_ADMIN') ) {
	/*
	 * This file is being included from a file other than mcms-admin/admin.php, so
	 * some setup was skipped. Make sure the admin message catalog is loaded since
	 * load_default_textdomain() will not have done so in this context.
	 */
	load_textdomain( 'default', MCMS_LANG_DIR . '/admin-' . get_locale() . '.mo' );
}

/** MandarinCMS Administration Hooks */
require_once(BASED_TREE_URI . 'mcms-admin/includes/admin-filters.php');

/** MandarinCMS Administration UI */
#include_once( BASED_TREE_URI . '/inc/jwadmindash_settings' );
#require_once( BASED_TREE_URI . '/jiiworks-admin-ui.php' );

/** MandarinCMS Bookmark Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/bookmark.php');

/** MandarinCMS Comment Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/comment.php');

/** MandarinCMS Administration File API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/file.php');

/** MandarinCMS Image Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/image.php');

/** MandarinCMS Media Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/media.php');

/** MandarinCMS Import Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/import.php');

/** MandarinCMS Misc Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/misc.php');

/** MandarinCMS Options Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/options.php');

/** MandarinCMS Module Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/module.php');

/** MandarinCMS Post Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/post.php');

/** MandarinCMS Administration Screen API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/class-mcms-screen.php');
require_once(BASED_TREE_URI . 'mcms-admin/includes/screen.php');

/** MandarinCMS Taxonomy Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/taxonomy.php');

/** MandarinCMS Template Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/template.php');

/** MandarinCMS List Table Administration API and base class */
require_once(BASED_TREE_URI . 'mcms-admin/includes/class-mcms-list-table.php');
require_once(BASED_TREE_URI . 'mcms-admin/includes/class-mcms-list-table-compat.php');
require_once(BASED_TREE_URI . 'mcms-admin/includes/list-table.php');

/** MandarinCMS MySkin Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/myskin.php');

/** MandarinCMS User Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/user.php');

/** MandarinCMS Site Icon API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/class-mcms-site-icon.php');

/** MandarinCMS Update Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/update.php');

/** MandarinCMS Deprecated Administration API */
require_once(BASED_TREE_URI . 'mcms-admin/includes/deprecated.php');

/** MandarinCMS Multisite support API */
if ( is_multisite() ) {
	require_once(BASED_TREE_URI . 'mcms-admin/includes/ms-admin-filters.php');
	require_once(BASED_TREE_URI . 'mcms-admin/includes/ms.php');
	require_once(BASED_TREE_URI . 'mcms-admin/includes/ms-deprecated.php');
}
