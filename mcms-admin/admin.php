<?php
/**
 * MandarinCMS Administration Bootstrap
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * In MandarinCMS Administration Screens
 *
 * @since 2.3.2
 */
if ( ! defined( 'MCMS_ADMIN' ) ) {
	define( 'MCMS_ADMIN', true );
}

if ( ! defined('MCMS_NETWORK_ADMIN') )
	define('MCMS_NETWORK_ADMIN', false);

if ( ! defined('MCMS_USER_ADMIN') )
	define('MCMS_USER_ADMIN', false);

if ( ! MCMS_NETWORK_ADMIN && ! MCMS_USER_ADMIN ) {
	define('MCMS_BLOG_ADMIN', true);
}

if ( isset($_GET['import']) && !defined('MCMS_LOAD_IMPORTERS') )
	define('MCMS_LOAD_IMPORTERS', true);

require_once(dirname(dirname(__FILE__)) . '/bootstrap.php');

nocache_headers();

if ( get_option('db_upgraded') ) {
	flush_rewrite_rules();
	update_option( 'db_upgraded',  false );

	/**
	 * Fires on the next page load after a successful DB upgrade.
	 *
	 * @since 2.8.0
	 */
	do_action( 'after_db_upgrade' );
} elseif ( get_option('db_version') != $mcms_db_version && empty($_POST) ) {
	if ( !is_multisite() ) {
		mcms_redirect( admin_url( 'upgrade.php?_mcms_http_referer=' . urlencode( mcms_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		exit;

	/**
	 * Filters whether to attempt to perform the multisite DB upgrade routine.
	 *
	 * In single site, the user would be redirected to mcms-admin/upgrade.php.
	 * In multisite, the DB upgrade routine is automatically fired, but only
	 * when this filter returns true.
	 *
	 * If the network is 50 sites or less, it will run every time. Otherwise,
	 * it will throttle itself to reduce load.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $do_mu_upgrade Whether to perform the Multisite upgrade routine. Default true.
	 */
	} elseif ( apply_filters( 'do_mu_upgrade', true ) ) {
		$c = get_blog_count();

		/*
		 * If there are 50 or fewer sites, run every time. Otherwise, throttle to reduce load:
		 * attempt to do no more than threshold value, with some +/- allowed.
		 */
		if ( $c <= 50 || ( $c > 50 && mt_rand( 0, (int)( $c / 50 ) ) == 1 ) ) {
			require_once( BASED_TREE_URI . MCMSINC . '/http.php' );
			$response = mcms_remote_get( admin_url( 'upgrade.php?step=1' ), array( 'timeout' => 120, 'httpversion' => '1.1' ) );
			/** This action is documented in mcms-admin/network/upgrade.php */
			do_action( 'after_mu_upgrade', $response );
			unset($response);
		}
		unset($c);
	}
}

require_once(BASED_TREE_URI . 'mcms-admin/includes/admin.php');

auth_redirect();

// Schedule trash collection
if ( ! mcms_next_scheduled( 'mcms_scheduled_delete' ) && ! mcms_installing() )
	mcms_schedule_event(time(), 'daily', 'mcms_scheduled_delete');

// Schedule Transient cleanup.
if ( ! mcms_next_scheduled( 'delete_expired_transients' ) && ! mcms_installing() ) {
	mcms_schedule_event( time(), 'daily', 'delete_expired_transients' );
}

set_screen_options();

$date_format = __( 'F j, Y' );
$time_format = __( 'g:i a' );

mcms_enqueue_script( 'common' );

/**
 * $pagenow is set in vars.php
 * $mcms_importers is sometimes set in mcms-admin/includes/import.php
 * The remaining variables are imported as globals elsewhere, declared as globals here
 *
 * @global string $pagenow
 * @global array  $mcms_importers
 * @global string $hook_suffix
 * @global string $module_page
 * @global string $typenow
 * @global string $taxnow
 */
global $pagenow, $mcms_importers, $hook_suffix, $module_page, $typenow, $taxnow;

$page_hook = null;

$editing = false;

if ( isset($_GET['page']) ) {
	$module_page = mcms_unslash( $_GET['page'] );
	$module_page = module_basename($module_page);
}

if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) )
	$typenow = $_REQUEST['post_type'];
else
	$typenow = '';

if ( isset( $_REQUEST['taxonomy'] ) && taxonomy_exists( $_REQUEST['taxonomy'] ) )
	$taxnow = $_REQUEST['taxonomy'];
else
	$taxnow = '';

if ( MCMS_NETWORK_ADMIN )
	require(BASED_TREE_URI . 'mcms-admin/network/menu.php');
elseif ( MCMS_USER_ADMIN )
	require(BASED_TREE_URI . 'mcms-admin/user/menu.php');
else
	require(BASED_TREE_URI . 'mcms-admin/menu.php');

if ( current_user_can( 'manage_options' ) ) {
	mcms_raise_memory_limit( 'admin' );
}

/**
 * Fires as an admin screen or script is being initialized.
 *
 * Note, this does not just run on user-facing admin screens.
 * It runs on admin-ajax.php and admin-post.php as well.
 *
 * This is roughly analogous to the more general {@see 'init'} hook, which fires earlier.
 *
 * @since 2.5.0
 */
do_action( 'admin_init' );

if ( isset($module_page) ) {
	if ( !empty($typenow) )
		$the_parent = $pagenow . '?post_type=' . $typenow;
	else
		$the_parent = $pagenow;
	if ( ! $page_hook = get_module_page_hook($module_page, $the_parent) ) {
		$page_hook = get_module_page_hook($module_page, $module_page);

		// Back-compat for modules using add_management_page().
		if ( empty( $page_hook ) && 'edit.php' == $pagenow && '' != get_module_page_hook($module_page, 'tools.php') ) {
			// There could be module specific params on the URL, so we need the whole query string
			if ( !empty($_SERVER[ 'QUERY_STRING' ]) )
				$query_string = $_SERVER[ 'QUERY_STRING' ];
			else
				$query_string = 'page=' . $module_page;
			mcms_redirect( admin_url('tools.php?' . $query_string) );
			exit;
		}
	}
	unset($the_parent);
}

$hook_suffix = '';
if ( isset( $page_hook ) ) {
	$hook_suffix = $page_hook;
} elseif ( isset( $module_page ) ) {
	$hook_suffix = $module_page;
} elseif ( isset( $pagenow ) ) {
	$hook_suffix = $pagenow;
}

set_current_screen();

// Handle module admin pages.
if ( isset($module_page) ) {
	if ( $page_hook ) {
		/**
		 * Fires before a particular screen is loaded.
		 *
		 * The load-* hook fires in a number of contexts. This hook is for module screens
		 * where a callback is provided when the screen is registered.
		 *
		 * The dynamic portion of the hook name, `$page_hook`, refers to a mixture of module
		 * page information including:
		 * 1. The page type. If the module page is registered as a submenu page, such as for
		 *    Settings, the page type would be 'settings'. Otherwise the type is 'toplevel'.
		 * 2. A separator of '_page_'.
		 * 3. The module basename minus the file extension.
		 *
		 * Together, the three parts form the `$page_hook`. Citing the example above,
		 * the hook name used would be 'load-settings_page_modulebasename'.
		 *
		 * @see get_module_page_hook()
		 *
		 * @since 2.1.0
		 */
		do_action( "load-{$page_hook}" );
		if (! isset($_GET['noheader']))
			require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		/**
		 * Used to call the registered callback for a module screen.
		 *
		 * @ignore
		 * @since 1.5.0
		 */
		do_action( $page_hook );
	} else {
		if ( validate_file( $module_page ) ) {
			mcms_die( __( 'Invalid module page.' ) );
		}

		if ( !( file_exists(MCMS_PLUGIN_DIR . "/$module_page") && is_file(MCMS_PLUGIN_DIR . "/$module_page") ) && !( file_exists(MCMSMU_PLUGIN_DIR . "/$module_page") && is_file(MCMSMU_PLUGIN_DIR . "/$module_page") ) )
			mcms_die(sprintf(__('Cannot load %s.'), htmlentities($module_page)));

		/**
		 * Fires before a particular screen is loaded.
		 *
		 * The load-* hook fires in a number of contexts. This hook is for module screens
		 * where the file to load is directly included, rather than the use of a function.
		 *
		 * The dynamic portion of the hook name, `$module_page`, refers to the module basename.
		 *
		 * @see module_basename()
		 *
		 * @since 1.5.0
		 */
		do_action( "load-{$module_page}" );

		if ( !isset($_GET['noheader']))
			require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		if ( file_exists(MCMSMU_PLUGIN_DIR . "/$module_page") )
			include(MCMSMU_PLUGIN_DIR . "/$module_page");
		else
			include(MCMS_PLUGIN_DIR . "/$module_page");
	}

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	exit();
} elseif ( isset( $_GET['import'] ) ) {

	$importer = $_GET['import'];

	if ( ! current_user_can( 'import' ) ) {
		mcms_die( __( 'Sorry, you are not allowed to import content.' ) );
	}

	if ( validate_file($importer) ) {
		mcms_redirect( admin_url( 'import.php?invalid=' . $importer ) );
		exit;
	}

	if ( ! isset($mcms_importers[$importer]) || ! is_callable($mcms_importers[$importer][2]) ) {
		mcms_redirect( admin_url( 'import.php?invalid=' . $importer ) );
		exit;
	}

	/**
	 * Fires before an importer screen is loaded.
	 *
	 * The dynamic portion of the hook name, `$importer`, refers to the importer slug.
	 *
	 * @since 3.5.0
	 */
	do_action( "load-importer-{$importer}" );

	$parent_file = 'tools.php';
	$submenu_file = 'import.php';
	$title = __('Import');

	if (! isset($_GET['noheader']))
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

	require_once(BASED_TREE_URI . 'mcms-admin/includes/upgrade.php');

	define('MCMS_IMPORTING', true);

	/**
	 * Whether to filter imported data through kses on import.
	 *
	 * Multisite uses this hook to filter all data through kses by default,
	 * as a super administrator may be assisting an untrusted user.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $force Whether to force data to be filtered through kses. Default false.
	 */
	if ( apply_filters( 'force_filtered_html_on_import', false ) ) {
		kses_init_filters();  // Always filter imported data with kses on multisite.
	}

	call_user_func($mcms_importers[$importer][2]);

	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	// Make sure rules are flushed
	flush_rewrite_rules(false);

	exit();
} else {
	/**
	 * Fires before a particular screen is loaded.
	 *
	 * The load-* hook fires in a number of contexts. This hook is for core screens.
	 *
	 * The dynamic portion of the hook name, `$pagenow`, is a global variable
	 * referring to the filename of the current page, such as 'admin.php',
	 * 'post-new.php' etc. A complete hook for the latter would be
	 * 'load-post-new.php'.
	 *
	 * @since 2.1.0
	 */
	do_action( "load-{$pagenow}" );

	/*
	 * The following hooks are fired to ensure backward compatibility.
	 * In all other cases, 'load-' . $pagenow should be used instead.
	 */
	if ( $typenow == 'page' ) {
		if ( $pagenow == 'post-new.php' )
			do_action( 'load-page-new.php' );
		elseif ( $pagenow == 'post.php' )
			do_action( 'load-page.php' );
	}  elseif ( $pagenow == 'edit-tags.php' ) {
		if ( $taxnow == 'category' )
			do_action( 'load-categories.php' );
		elseif ( $taxnow == 'link_category' )
			do_action( 'load-edit-link-categories.php' );
	} elseif( 'term.php' === $pagenow ) {
		do_action( 'load-edit-tags.php' );
	}
}

if ( ! empty( $_REQUEST['action'] ) ) {
	/**
	 * Fires when an 'action' request variable is sent.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the action derived from the `GET` or `POST` request.
	 *
	 * @since 2.6.0
	 */
	do_action( 'admin_action_' . $_REQUEST['action'] );
}
