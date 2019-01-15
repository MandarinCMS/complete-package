<?php
/**
 * Bootstrap file for setting the BASED_TREE_URI constant
 * and loading the database-settings.php file. The database-settings.php
 * file will then load the proclass.php file, which
 * will then set up the MandarinCMS environment.
 *
 * If the database-settings.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * database-settings.php file.
 *
 * Will also search for database-settings.php in MandarinCMS' parent
 * directory to allow the MandarinCMS directory to remain
 * untouched.
 *
 * @package MandarinCMS
 */

/** Define BASED_TREE_URI as this file's directory */
if ( ! defined( 'BASED_TREE_URI' ) ) {
	define( 'BASED_TREE_URI', dirname( __FILE__ ) . '/' );
}

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/*
 * If database-settings.php exists in the MandarinCMS root, or if it exists in the root and proclass.php
 * doesn't, load database-settings.php. The secondary check for proclass.php has the added benefit
 * of avoiding cases where the current directory is a nested installation, e.g. / is MandarinCMS(a)
 * and /blog/ is MandarinCMS(b).
 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( BASED_TREE_URI . 'database-settings.php') ) {
	
	/** The config file resides in BASED_TREE_URI */
	require_once( BASED_TREE_URI . 'database-settings.php' );
	
	
	
} elseif ( @file_exists( dirname( BASED_TREE_URI ) . '/database-settings.php' ) && ! @file_exists( dirname( BASED_TREE_URI ) . '/proclass.php' ) ) {

	/** The config file resides one level above BASED_TREE_URI but is not part of another installation */
	require_once( dirname( BASED_TREE_URI ) . '/database-settings.php' );
	
} else {

	// A config file doesn't exist

	define( 'MCMSINC', 'mcms-roots' );
	require_once( BASED_TREE_URI . MCMSINC . '/load.php' );

	// Standardize $_SERVER variables across setups.
	mcms_fix_server_vars();
	
	require_once( BASED_TREE_URI . MCMSINC . '/functions.php' );

	$path = mcms_guess_url() . '/mcms-admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'MCMS_CONTENT_DIR', BASED_TREE_URI . 'mcms-plugins' );
	require_once( BASED_TREE_URI . MCMSINC . '/version.php' );

	mcms_check_php_mysql_versions();
	mcms_load_translations_early();

	// Die with an error message
	$die  = sprintf(
		/* translators: %s: database-settings.php */
		__( "There doesn't seem to be a %s file. I need this before we can get started." ),
		'<code>database-settings.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: Codex URL */
		__( "Need more help? <a href='%s'>We got it</a>." ),
		__( 'https://dev.mandarincms.com/Editing_database-settings.php' )
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: database-settings.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>database-settings.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	mcms_die( $die, __( 'MandarinCMS &rsaquo; Error' ) );
}
