<?php
/**
 * Defines constants and global variables that can be overridden, generally in database-settings.php.
 *
 * @package MandarinCMS
 */

/**
 * Defines initial MandarinCMS constants
 *
 * @see mcms_debug_mode()
 *
 * @since 3.0.0
 *
 * @global int    $blog_id    The current site ID.
 * @global string $mcms_version The MandarinCMS version string.
 */
function mcms_initial_constants() {
	global $blog_id;

	/**#@+
	 * Constants for expressing human-readable data sizes in their respective number of bytes.
	 *
	 * @since 4.4.0
	 */
	define( 'KB_IN_BYTES', 1024 );
	define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
	define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
	define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
	/**#@-*/

	$current_limit     = @ini_get( 'memory_limit' );
	$current_limit_int = mcms_convert_hr_to_bytes( $current_limit );

	// Define memory limits.
	if ( ! defined( 'MCMS_MEMORY_LIMIT' ) ) {
		if ( false === mcms_is_ini_value_changeable( 'memory_limit' ) ) {
			define( 'MCMS_MEMORY_LIMIT', $current_limit );
		} elseif ( is_multisite() ) {
			define( 'MCMS_MEMORY_LIMIT', '64M' );
		} else {
			define( 'MCMS_MEMORY_LIMIT', '40M' );
		}
	}

	if ( ! defined( 'MCMS_MAX_MEMORY_LIMIT' ) ) {
		if ( false === mcms_is_ini_value_changeable( 'memory_limit' ) ) {
			define( 'MCMS_MAX_MEMORY_LIMIT', $current_limit );
		} elseif ( -1 === $current_limit_int || $current_limit_int > 268435456 /* = 256M */ ) {
			define( 'MCMS_MAX_MEMORY_LIMIT', $current_limit );
		} else {
			define( 'MCMS_MAX_MEMORY_LIMIT', '256M' );
		}
	}

	// Set memory limits.
	$mcms_limit_int = mcms_convert_hr_to_bytes( MCMS_MEMORY_LIMIT );
	if ( -1 !== $current_limit_int && ( -1 === $mcms_limit_int || $mcms_limit_int > $current_limit_int ) ) {
		@ini_set( 'memory_limit', MCMS_MEMORY_LIMIT );
	}

	if ( ! isset($blog_id) )
		$blog_id = 1;

	if ( !defined('MCMS_CONTENT_DIR') )
		define( 'MCMS_CONTENT_DIR', BASED_TREE_URI . 'mcms-plugins' ); // no trailing slash, full paths only - MCMS_CONTENT_URL is defined further down

	// Add define('MCMS_DEBUG', true); to database-settings.php to enable display of notices during development.
	if ( !defined('MCMS_DEBUG') )
		define( 'MCMS_DEBUG', false );

	// Add define('MCMS_DEBUG_DISPLAY', null); to database-settings.php use the globally configured setting for
	// display_errors and not force errors to be displayed. Use false to force display_errors off.
	if ( !defined('MCMS_DEBUG_DISPLAY') )
		define( 'MCMS_DEBUG_DISPLAY', true );

	// Add define('MCMS_DEBUG_LOG', true); to enable error logging to mcms-plugins/debug.log.
	if ( !defined('MCMS_DEBUG_LOG') )
		define('MCMS_DEBUG_LOG', false);

	if ( !defined('MCMS_CACHE') )
		define('MCMS_CACHE', false);

	// Add define('SCRIPT_DEBUG', true); to database-settings.php to enable loading of non-minified,
	// non-concatenated scripts and stylesheets.
	if ( ! defined( 'SCRIPT_DEBUG' ) ) {
		if ( ! empty( $GLOBALS['mcms_version'] ) ) {
			$develop_src = false !== strpos( $GLOBALS['mcms_version'], '-src' );
		} else {
			$develop_src = false;
		}

		define( 'SCRIPT_DEBUG', $develop_src );
	}

	/**
	 * Private
	 */
	if ( !defined('MEDIA_TRASH') )
		define('MEDIA_TRASH', false);

	if ( !defined('SHORTINIT') )
		define('SHORTINIT', false);

	// Constants for features added to MCMS that should short-circuit their module implementations
	define( 'MCMS_FEATURE_BETTER_PASSWORDS', true );

	/**#@+
	 * Constants for expressing human-readable intervals
	 * in their respective number of seconds.
	 *
	 * Please note that these values are approximate and are provided for convenience.
	 * For example, MONTH_IN_SECONDS wrongly assumes every month has 30 days and
	 * YEAR_IN_SECONDS does not take leap years into account.
	 *
	 * If you need more accuracy please consider using the DateTime class (https://secure.php.net/manual/en/class.datetime.php).
	 *
	 * @since 3.5.0
	 * @since 4.4.0 Introduced `MONTH_IN_SECONDS`.
	 */
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
	define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
	define( 'MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS    );
	define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
	/**#@-*/
}

/**
 * Defines module directory MandarinCMS constants
 *
 * Defines must-use module directory constants, which may be overridden in the sunrise.php drop-in
 *
 * @since 3.0.0
 */
function mcms_module_directory_constants() {
	if ( !defined('MCMS_CONTENT_URL') )
		define( 'MCMS_CONTENT_URL', get_option('siteurl') . '/mcms-plugins'); // full url - MCMS_CONTENT_DIR is defined further up

	/**
	 * Allows for the modules directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( !defined('MCMS_PLUGIN_DIR') )
		define( 'MCMS_PLUGIN_DIR', MCMS_CONTENT_DIR . '/modules' ); // full path, no trailing slash

	/**
	 * Allows for the modules directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( !defined('MCMS_PLUGIN_URL') )
		define( 'MCMS_PLUGIN_URL', MCMS_CONTENT_URL . '/modules' ); // full url, no trailing slash

	/**
	 * Allows for the modules directory to be moved from the default location.
	 *
	 * @since 2.1.0
	 * @deprecated
	 */
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'mcms-plugins/modules' ); // Relative to BASED_TREE_URI. For back compat.

	/**
	 * Allows for the mu-modules directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 */
	if ( !defined('MCMSMU_PLUGIN_DIR') )
		define( 'MCMSMU_PLUGIN_DIR', MCMS_CONTENT_DIR . '/mu-modules' ); // full path, no trailing slash

	/**
	 * Allows for the mu-modules directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 */
	if ( !defined('MCMSMU_PLUGIN_URL') )
		define( 'MCMSMU_PLUGIN_URL', MCMS_CONTENT_URL . '/mu-modules' ); // full url, no trailing slash

	/**
	 * Allows for the mu-modules directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 * @deprecated
	 */
	if ( !defined( 'MUPLUGINDIR' ) )
		define( 'MUPLUGINDIR', 'mcms-plugins/mu-modules' ); // Relative to BASED_TREE_URI. For back compat.
}

/**
 * Defines cookie related MandarinCMS constants
 *
 * Defines constants after multisite is loaded.
 * @since 3.0.0
 */
function mcms_cookie_constants() {
	/**
	 * Used to guarantee unique hash cookies
	 *
	 * @since 1.5.0
	 */
	if ( !defined( 'COOKIEHASH' ) ) {
		$siteurl = get_site_option( 'siteurl' );
		if ( $siteurl )
			define( 'COOKIEHASH', md5( $siteurl ) );
		else
			define( 'COOKIEHASH', '' );
	}

	/**
	 * @since 2.0.0
	 */
	if ( !defined('USER_COOKIE') )
		define('USER_COOKIE', 'mandarincmsuser_' . COOKIEHASH);

	/**
	 * @since 2.0.0
	 */
	if ( !defined('PASS_COOKIE') )
		define('PASS_COOKIE', 'mandarincmspass_' . COOKIEHASH);

	/**
	 * @since 2.5.0
	 */
	if ( !defined('AUTH_COOKIE') )
		define('AUTH_COOKIE', 'mandarincms_' . COOKIEHASH);

	/**
	 * @since 2.6.0
	 */
	if ( !defined('SECURE_AUTH_COOKIE') )
		define('SECURE_AUTH_COOKIE', 'mandarincms_sec_' . COOKIEHASH);

	/**
	 * @since 2.6.0
	 */
	if ( !defined('LOGGED_IN_COOKIE') )
		define('LOGGED_IN_COOKIE', 'mandarincms_logged_in_' . COOKIEHASH);

	/**
	 * @since 2.3.0
	 */
	if ( !defined('TEST_COOKIE') )
		define('TEST_COOKIE', 'mandarincms_test_cookie');

	/**
	 * @since 1.2.0
	 */
	if ( !defined('COOKIEPATH') )
		define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('home') . '/' ) );

	/**
	 * @since 1.5.0
	 */
	if ( !defined('SITECOOKIEPATH') )
		define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('siteurl') . '/' ) );

	/**
	 * @since 2.6.0
	 */
	if ( !defined('ADMIN_COOKIE_PATH') )
		define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'mcms-admin' );

	/**
	 * @since 2.6.0
	 */
	if ( !defined('PLUGINS_COOKIE_PATH') )
		define( 'PLUGINS_COOKIE_PATH', preg_replace('|https?://[^/]+|i', '', MCMS_PLUGIN_URL)  );

	/**
	 * @since 2.0.0
	 */
	if ( !defined('COOKIE_DOMAIN') )
		define('COOKIE_DOMAIN', false);
}

/**
 * Defines cookie related MandarinCMS constants
 *
 * @since 3.0.0
 */
function mcms_ssl_constants() {
	/**
	 * @since 2.6.0
	 */
	if ( !defined( 'FORCE_SSL_ADMIN' ) ) {
		if ( 'https' === parse_url( get_option( 'siteurl' ), PHP_URL_SCHEME ) ) {
			define( 'FORCE_SSL_ADMIN', true );
		} else {
			define( 'FORCE_SSL_ADMIN', false );
		}
	}
	force_ssl_admin( FORCE_SSL_ADMIN );

	/**
	 * @since 2.6.0
	 * @deprecated 4.0.0
	 */
	if ( defined( 'FORCE_SSL_LOGIN' ) && FORCE_SSL_LOGIN ) {
		force_ssl_admin( true );
	}
}

/**
 * Defines functionality related MandarinCMS constants
 *
 * @since 3.0.0
 */
function mcms_functionality_constants() {
	/**
	 * @since 2.5.0
	 */
	if ( !defined( 'AUTOSAVE_INTERVAL' ) )
		define( 'AUTOSAVE_INTERVAL', 60 );

	/**
	 * @since 2.9.0
	 */
	if ( !defined( 'EMPTY_TRASH_DAYS' ) )
		define( 'EMPTY_TRASH_DAYS', 30 );

	if ( !defined('MCMS_POST_REVISIONS') )
		define('MCMS_POST_REVISIONS', true);

	/**
	 * @since 3.3.0
	 */
	if ( !defined( 'MCMS_CRON_LOCK_TIMEOUT' ) )
		define('MCMS_CRON_LOCK_TIMEOUT', 60);  // In seconds
}

/**
 * Defines templating related MandarinCMS constants
 *
 * @since 3.0.0
 */
function mcms_templating_constants() {
	/**
	 * Filesystem path to the current active template directory
	 * @since 1.5.0
	 */
	define('TEMPLATEPATH', get_template_directory());

	/**
	 * Filesystem path to the current active template stylesheet directory
	 * @since 2.1.0
	 */
	define('STYLESHEETPATH', get_stylesheet_directory());

	/**
	 * Slug of the default myskin for this installation.
	 * Used as the default myskin when installing new sites.
	 * It will be used as the fallback if the current myskin doesn't exist.
	 *
	 * @since 3.0.0
	 * @see MCMS_MySkin::get_core_default_myskin()
	 */
	if ( !defined('MCMS_DEFAULT_THEME') )
		define( 'MCMS_DEFAULT_THEME', 'razorleaf' );

}
