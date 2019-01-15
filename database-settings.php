<?php
// ** MySQL settings - You can get this info from your web host ** //
/** MySQL hostname */
$DB_HOST = "localhost";
/** The name of the database for mandarincms */
$DB_NAME = "jmdrevolution";
/** MySQL database username */
$DB_USER = "root";
/** MySQL database password */
$DB_PASSWORD = "";
/** Database Charset to use in creating database tables. */
$DB_CHARSET = "utf8mb4";
/** The Database Collate type. Don't change this if in doubt. */
$DB_COLLATE = "";
/**
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$TBL_PREFIX = "_";
/**#@+
 * Authentication Unique Keys and Salts.
 * */
$_AUTH_KEY_ 		= "Rgr[r-{Z+QqKzwaLDbt^O/nA$dqVqd5zX)V[)1QXVC`WC3=&e3Wn>D(xIz _b{Ep";
$_SECURE_AUTH_KEY_ 	= "X|n4}r2V@nM5smQn3@`T)jb/CJLV]dgc$ n8IZnciLGnCKu][CWaajx7G>,|5gTc";
$_LOGGED_IN_KEY_	= "B9oGQ-q9:V8e1OuV 8T(#IA-T0^>31|wyVfGOaLch[fi@v?NbP_#n|UpmG@v{i/D";
$_NONCE_KEY_		= "QM5yS@AiI.oQ ~ZGQDU{4d_KF.k!R39]C[28Y6{;<:^Cf7wG&U(Ku]}Yk9dMq/G[";
$_AUTH_SALT_		= "dw</JhTcpbYfbx1HSe58zQfvc[aV>PXqp<?dASpmB^(B{=1h0p&Y&PP^aWX=P^[N";
$_SECURE_AUTH_SALT_	= "tAv^.ABZ_RU^BqT=4s2thMEmj+S9obOE=bd%ly|>^caP+2NTa)NIGnO_NsBa[Fd,";
$_LOGGED_IN_SALT_	= "oGj[?i%9I`N[~a1 x(o;U*1p;;6S=!pP|<cHq/o0I(>d|P@VeqKi`GZkDp%*!VAr";
$_NONCE_SALT_		= "r<D[P,dLq*:+2j)[ti]Z3jDJhCxNW%wY?g)WI?C>1,G*D?,#,%R?+qr[F&L{s8L%";
define('DB_NAME', $DB_NAME);
define('DB_USER', $DB_USER);
define('DB_PASSWORD', $DB_PASSWORD);
define('DB_HOST', $DB_HOST);
define('DB_CHARSET', $DB_CHARSET);
define('DB_COLLATE', $DB_COLLATE);
/**#@+
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.mandarincms.com/secret-key/1.1/salt/ mandarincms.com secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 */
define('AUTH_KEY',         $_AUTH_KEY_);
define('SECURE_AUTH_KEY',  $_SECURE_AUTH_KEY_);
define('LOGGED_IN_KEY',    $_LOGGED_IN_KEY_);
define('NONCE_KEY',        $_NONCE_KEY_);
define('AUTH_SALT',        $_AUTH_SALT_);
define('SECURE_AUTH_SALT', $_SECURE_AUTH_SALT_);
define('LOGGED_IN_SALT',   $_LOGGED_IN_SALT_);
define('NONCE_SALT',       $_NONCE_SALT_);
/**#@-*/
$table_prefix  = $TBL_PREFIX;
/**
 * For developers: mandarincms debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that module and myskin developers use MCMS_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.mandarincms.com/Debugging_in_mandarincms
 */
define('MCMS_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the mandarincms directory. */
if ( !defined('MYFOLDIR') )
	define('MYFOLDIR', dirname(__FILE__) . '/');

/** Sets up mandarincms vars and included files. */
require_once(MYFOLDIR . 'proclass.php');


// Put this code in the myskin functions.php file.
 function my_filter_module_updates( $value ) {
   if( isset( $value->response['facebook-comments-module/facebook-comments.php'] ) ) {        
      unset( $value->response['facebook-comments-module/facebook-comments.php'] );
    }
    return $value;
 }
 add_filter( 'site_transient_update_modules', 'my_filter_module_updates' );
 
add_filter('site_transient_update_modules', '__return_false');
add_filter( 'pre_site_transient_update_core', '__return_null' ); 
// Single mcms-config.php switch:
define( 'AUTOMATIC_UPDATER_DISABLED', false );
# Disables all core updates:
define( 'MCMS_AUTO_UPDATE_CORE', false );

# Disable all core updates, including minor and major:
define( 'MCMS_AUTO_UPDATE_CORE', false );

// Disable module update
add_filter('site_transient_update_modules', 'remove_update_notification');
function remove_update_notification($value) {
     unset($value->response[ module_basename(__FILE__) ]);
     return $value;
} 




# define( 'MCMS_CACHE', true );
# define( 'CUSTOM_USER_TABLE', $table_prefix.'my_users' );
# define( 'CUSTOM_USER_META_TABLE', $table_prefix.'my_usermeta' );
# define( 'MCMSLANG', 'en_US' );
# define( 'MCMS_LANG_DIR', dirname(__FILE__) . '/mywebspace/languages' );
# define( 'SAVEQUERIES', true );

# define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
# define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );

# define( 'FS_METHOD', 'ftpext' );
# define( 'FTP_BASE', '/path/to/myfolder/' );
# define( 'FTP_CONTENT_DIR', '/path/to/myfolder/content/' );
# define( 'FTP_PLUGIN_DIR ', '/path/to/myfolder/content/modules/' );
# define( 'FTP_PUBKEY', '/home/username/.ssh/id_rsa.pub' );
# define( 'FTP_PRIKEY', '/home/username/.ssh/id_rsa' );
# define( 'FTP_USER', 'username' );
# define( 'FTP_PASS');
# define( 'MCMS_CRON_LOCK_TIMEOUT', 60 );
# define( 'DISABLE_MCMS_CRON', true );
# define( 'COOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'home' ) . '/' ) );
# define( 'SITECOOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'siteurl' ) . '/' ) );
# define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'io-controler' );
# define( 'PLUGINS_COOKIE_PATH', preg_replace( '|https?://[^/]+|i', '', MCMS_PLUGIN_URL ) );
# define( 'EMPTY_TRASH_DAYS', 30 ); // 30 days
# define( 'MCMS_ALLOW_REPAIR', true );
# define( 'DO_NOT_UPGRADE_GLOBAL_TABLES', true );
# define( 'DISALLOW_FILE_EDIT', true );
# define( 'MCMS_HTTP_BLOCK_EXTERNAL', true );
# define( 'MCMS_ACCESSIBLE_HOSTS', 'mysite.site,*.github.com' );

# define( 'MCMS_AUTO_UPDATE_CORE', 'minor' );
# define( 'IMAGE_EDIT_OVERWRITE', true );
# define( 'DISALLOW_UNFILTERED_HTML', true );

# define( 'MCMS_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/path/to/myfolder/' );
# define( 'MCMS_HOME', 'http://example.com' );
# define( 'MCMS_CONTENT_URL', 'http://example/blog/content' );
# define( 'MCMS_PLUGIN_URL', 'http://example/blog/content/modules' );


# define( 'AUTOSAVE_INTERVAL', 160 ); // Seconds
# define( 'MCMS_POST_REVISIONS', false );
# define( 'COOKIE_DOMAIN', 'www.example.com' );
# define( 'MCMS_ALLOW_MULTISITE', true );


add_filter( 'contextual_help', 'mymyskin_remove_help_tabs', 999, 3 );
function mymyskin_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}
	add_filter('screen_options_show_screen', '__return_false');
	define( 'MCMS_AUTO_UPDATE_CORE', false );
    define( 'AUTOMATIC_UPDATER_DISABLED', true );
    define( 'MCMS_AUTO_UPDATE_CORE', false );# Disable all core updates:
	add_filter( 'auto_update_module', '__return_false' );
	add_filter( 'auto_update_myskin', '__return_false' );

function remove_core_updates(){
	global $mcms_version;return(object) array('last_checked'=> time(),'version_checked'=> $mcms_version,);
}
	add_filter('pre_site_transient_update_core','remove_core_updates');
	add_filter('pre_site_transient_update_modules','remove_core_updates');
	add_filter('pre_site_transient_update_myskins','remove_core_updates');

function hide_update_notice() {
    get_currentuserinfo();
		if (!current_user_can('manage_options')) {
			remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
	add_action( 'admin_notices', 'hide_update_notice', 1 );

		if ($user_login != "username") {
				remove_action( 'admin_notices', 'update_nag', 3 );
			}

     


// OS_Disable_mandarincms_Updates
class OS_Disable_mandarincms_Updates {
	/**
	 * The OS_Disable_mandarincms_Updates class constructor
	 * initializing required stuff for the module
	 *
	 * PHP 5 Constructor
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function __construct() {
		add_action( 'admin_init', array(&$this, 'admin_init') );

		/*
		 * Disable MySkin Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_myskins', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_myskins', array($this, 'last_checked_atm') );


		/*
		 * Disable Module Updates
		 * 2.8 to 3.0
		 */
		add_action( 'pre_transient_update_modules', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_modules', array($this, 'last_checked_atm') );


		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_core', array($this, 'last_checked_atm') );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_core', array($this, 'last_checked_atm') );
		
		
		/*
		 * Filter schedule checks
		 *
		 * @link https://mandarincms.com/support/topic/possible-performance-improvement/#post-8970451
		 */
		add_action('schedule_event', array($this, 'filter_cron_events'));


		/*
		 * Disable All Automatic Updates
		 * 3.7+
		 *
		 * @author	sLa NGjI's @ slangji.mandarincms.com
		 */
		add_filter( 'auto_update_translation', '__return_false' );
		add_filter( 'automatic_updater_disabled', '__return_true' );
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );
		add_filter( 'allow_major_auto_core_updates', '__return_false' );
		add_filter( 'allow_dev_auto_core_updates', '__return_false' );
		add_filter( 'auto_update_core', '__return_false' );
		add_filter( 'mcms_auto_update_core', '__return_false' );
		add_filter( 'auto_core_update_send_email', '__return_false' );
		add_filter( 'send_core_update_notification_email', '__return_false' );
		add_filter( 'auto_update_module', '__return_false' );
		add_filter( 'auto_update_myskin', '__return_false' );
		add_filter( 'automatic_updates_send_debug_email', '__return_false' );
		add_filter( 'automatic_updates_is_vcs_checkout', '__return_true' );


		add_filter( 'automatic_updates_send_debug_email ', '__return_false', 1 );
		if( !defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) define( 'AUTOMATIC_UPDATER_DISABLED', true );
		if( !defined( 'MCMS_AUTO_UPDATE_CORE') ) define( 'MCMS_AUTO_UPDATE_CORE', false );

		add_filter( 'pre_http_request', array($this, 'block_request'), 10, 3 );
	}


	/**
	 * Initialize and load the module stuff
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function admin_init() {
		if ( !function_exists("remove_action") ) return;
		
		/*
		 * Remove 'update modules' option from bulk operations select list
		 */
		global $current_user;
		$current_user->allcaps['update_modules'] = 0;
		
		/*
		 * Hide maintenance and update nag
		 */
		remove_action( 'admin_notices', 'update_nag', 3 );
		remove_action( 'network_admin_notices', 'update_nag', 3 );
		remove_action( 'admin_notices', 'maintenance_nag' );
		remove_action( 'network_admin_notices', 'maintenance_nag' );
		

		/*
		 * Disable MySkin Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-myskins.php', 'mcms_update_myskins' );
		remove_action( 'load-update.php', 'mcms_update_myskins' );
		remove_action( 'admin_init', '_maybe_update_myskins' );
		remove_action( 'mcms_update_myskins', 'mcms_update_myskins' );
		mcms_clear_scheduled_hook( 'mcms_update_myskins' );


		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'mcms_update_myskins' );
		mcms_clear_scheduled_hook( 'mcms_update_myskins' );


		/*
		 * Disable Module Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-modules.php', 'mcms_update_modules' );
		remove_action( 'load-update.php', 'mcms_update_modules' );
		remove_action( 'admin_init', '_maybe_update_modules' );
		remove_action( 'mcms_update_modules', 'mcms_update_modules' );
		mcms_clear_scheduled_hook( 'mcms_update_modules' );

		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'mcms_update_modules' );
		mcms_clear_scheduled_hook( 'mcms_update_modules' );


		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_option_update_core', '__return_null' );

		remove_action( 'mcms_version_check', 'mcms_version_check' );
		remove_action( 'admin_init', '_maybe_update_core' );
		mcms_clear_scheduled_hook( 'mcms_version_check' );


		/*
		 * 3.0
		 */
		mcms_clear_scheduled_hook( 'mcms_version_check' );


		/*
		 * 3.7+
		 */
		remove_action( 'mcms_maybe_auto_update', 'mcms_maybe_auto_update' );
		remove_action( 'admin_init', 'mcms_maybe_auto_update' );
		remove_action( 'admin_init', 'mcms_auto_update_core' );
		mcms_clear_scheduled_hook( 'mcms_maybe_auto_update' );
	}




	/**
	 * Check the outgoing request
	 *
	 * @since 		1.4.4
	 */
	public function block_request($pre, $args, $url) {
		/* Empty url */
		if( empty( $url ) ) {
			return $pre;
		}

		/* Invalid host */
		if( !$host = parse_url($url, PHP_URL_HOST) ) {
			return $pre;
		}

		$url_data = parse_url( $url );

		/* block request */
		if( false !== stripos( $host, 'api.mandarincms.com' ) && (false !== stripos( $url_data['path'], 'update-check' ) || false !== stripos( $url_data['path'], 'browse-happy' )) ) {
			return true;
		}

		return $pre;
	}


	/**
	 * Filter cron events
	 *
	 * @since 		1.5.0
	 */
	public function filter_cron_events($event) {
		switch( $event->hook ) {
			case 'mcms_version_check':
			case 'mcms_update_modules':
			case 'mcms_update_myskins':
			case 'mcms_maybe_auto_update':
				$event = false;
				break;
		}
		return $event;
	}
	
	
	/**
	 * Override version check info
	 *
	 * @since 		1.6.0
	 */
	public function last_checked_atm( $t ) {
		include( MYFOLDIR . MCMSINC . '/version.php' );
		
		$current = new stdClass;
		$current->updates = array();
		$current->version_checked = $mcms_version;
		$current->last_checked = time();
		
		return $current;
	}
}

if ( class_exists('OS_Disable_mandarincms_Updates') ) { $OS_Disable_mandarincms_Updates = new OS_Disable_mandarincms_Updates();}

