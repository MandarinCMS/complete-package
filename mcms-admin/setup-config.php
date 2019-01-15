<?php
/**
 * Retrieves and creates the mcms-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the mcms-config.php to be created using this page.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * We are installing.
 */
define('MCMS_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('MCMS_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging
 */
error_reporting(0);

if ( ! defined( 'BASED_TREE_URI' ) ) {
	define( 'BASED_TREE_URI', dirname( dirname( __FILE__ ) ) . '/' );
}

require( BASED_TREE_URI . 'proclass.php' );

/** Load MandarinCMS Administration Upgrade API */
require_once( BASED_TREE_URI . 'mcms-admin/includes/upgrade.php' );

/** Load MandarinCMS Translation Installation API */
require_once( BASED_TREE_URI . 'mcms-admin/includes/translation-install.php' );

nocache_headers();

// Support database-create-test.php one level up, for the develop repo.
if ( file_exists( BASED_TREE_URI . 'database-create-test.php' ) ) {
	$config_file = file( BASED_TREE_URI . 'database-create-test.php' );
} elseif ( file_exists( dirname( BASED_TREE_URI ) . '/database-create-test.php' ) ) {
	$config_file = file( dirname( BASED_TREE_URI ) . '/database-create-test.php' );
} else {
	mcms_die( sprintf(
		/* translators: %s: database-create-test.php */
		__( 'Sorry, I need a %s file to work from. Please re-upload this file to your MandarinCMS installation.' ),
		'<code>database-create-test.php</code>'
	) );
}

// Check if mcms-config.php has been created
if ( file_exists( BASED_TREE_URI . 'mcms-config.php' ) ) {
	mcms_die( '<p>' . sprintf(
			/* translators: 1: mcms-config.php 2: install.php */
			__( 'The file %1$s already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href="%2$s">installing now</a>.' ),
			'<code>mcms-config.php</code>',
			'install.php'
		) . '</p>'
	);
}

// Check if mcms-config.php exists above the root directory but is not part of another installation
if ( @file_exists( BASED_TREE_URI . '../mcms-config.php' ) && ! @file_exists( BASED_TREE_URI . '../proclass.php' ) ) {
	mcms_die( '<p>' . sprintf(
			/* translators: 1: mcms-config.php 2: install.php */
			__( 'The file %1$s already exists one level above your MandarinCMS installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href="%2$s">installing now</a>.' ),
			'<code>mcms-config.php</code>',
			'install.php'
		) . '</p>'
	);
}

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : -1;

/**
 * Display setup mcms-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 *
 * @global string    $mcms_local_package
 * @global MCMS_Locale $mcms_locale
 *
 * @param string|array $body_classes
 */
function setup_config_display_header( $body_classes = array() ) {
	$body_classes = (array) $body_classes;
	$body_classes[] = 'mcms-core-ui';
	if ( is_rtl() ) {
		$body_classes[] = 'rtl';
	}

	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php _e( 'MandarinCMS &rsaquo; Setup Configuration File' ); ?></title>
	<?php mcms_admin_css( 'install', true ); ?>
</head>
<body class="<?php echo implode( ' ', $body_classes ); ?>">
<p id="logo"><a href="<?php echo esc_url( __( 'https://mandarincms.com/' ) ); ?>" tabindex="-1"><?php _e( 'MandarinCMS' ); ?></a></p>
<?php
} // end function setup_config_display_header();

$language = '';
if ( ! empty( $_REQUEST['language'] ) ) {
	$language = preg_replace( '/[^a-zA-Z0-9_]/', '', $_REQUEST['language'] );
} elseif ( isset( $GLOBALS['mcms_local_package'] ) ) {
	$language = $GLOBALS['mcms_local_package'];
}

switch($step) {
	case -1:
		if ( mcms_can_install_language_pack() && empty( $language ) && ( $languages = mcms_get_available_translations() ) ) {
			setup_config_display_header( 'language-chooser' );
			echo '<h1 class="screen-reader-text">Select a default language</h1>';
			echo '<form id="setup" method="post" action="?step=0">';
			mcms_install_language_form( $languages );
			echo '</form>';
			break;
		}

		// Deliberately fall through if we can't reach the translations API.

	case 0:
		if ( ! empty( $language ) ) {
			$loaded_language = mcms_download_language_pack( $language );
			if ( $loaded_language ) {
				load_default_textdomain( $loaded_language );
				$GLOBALS['mcms_locale'] = new MCMS_Locale();
			}
		}

		setup_config_display_header();
		$step_1 = 'setup-config.php?step=1';
		if ( isset( $_REQUEST['noapi'] ) ) {
			$step_1 .= '&amp;noapi';
		}
		if ( ! empty( $loaded_language ) ) {
			$step_1 .= '&amp;language=' . $loaded_language;
		}
?>
<h1 class="screen-reader-text"><?php _e( 'Before getting started' ) ?></h1>
<p><?php _e( 'Welcome to MandarinCMS. Before getting started, we need some information on the database. You will need to know the following items before proceeding.' ) ?></p>
<ol>
	<li><?php _e( 'Database name' ); ?></li>
	<li><?php _e( 'Database username' ); ?></li>
	<li><?php _e( 'Database password' ); ?></li>
	<li><?php _e( 'Database host' ); ?></li>
	<li><?php _e( 'Table prefix (if you want to run more than one MandarinCMS in a single database)' ); ?></li>
</ol>
<p><?php
	/* translators: %s: mcms-config.php */
	printf( __( 'We&#8217;re going to use this information to create a %s file.' ),
		'<code>mcms-config.php</code>'
	);
	?>
	<strong><?php
		/* translators: 1: database-create-test.php, 2: mcms-config.php */
		printf( __( 'If for any reason this automatic file creation doesn&#8217;t work, don&#8217;t worry. All this does is fill in the database information to a configuration file. You may also simply open %1$s in a text editor, fill in your information, and save it as %2$s.' ),
			'<code>database-create-test.php</code>',
			'<code>mcms-config.php</code>'
		);
	?></strong>
	<?php
	/* translators: %s: Codex URL */
	printf( __( 'Need more help? <a href="%s">We got it</a>.' ),
		__( 'https://dev.mandarincms.com/Editing_mcms-config.php' )
	);
?></p>
<p><?php _e( 'In all likelihood, these items were supplied to you by your Web Host. If you don&#8217;t have this information, then you will need to contact them before you can continue. If you&#8217;re all ready&hellip;' ); ?></p>

<p class="step"><a href="<?php echo $step_1; ?>" class="button button-large"><?php _e( 'Let&#8217;s go!' ); ?></a></p>
<?php
	break;

	case 1:
		load_default_textdomain( $language );
		$GLOBALS['mcms_locale'] = new MCMS_Locale();

		setup_config_display_header();
	?>
<h1 class="screen-reader-text"><?php _e( 'Set up your database connection' ) ?></h1>
<form method="post" action="setup-config.php?step=2">
	<p><?php _e( 'Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host.' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname"><?php _e( 'Database Name' ); ?></label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="mandarincms" /></td>
			<td><?php _e( 'The name of the database you want to use with MandarinCMS.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="uname"><?php _e( 'Username' ); ?></label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'username', 'example username' ), ENT_QUOTES ); ?>" /></td>
			<td><?php _e( 'Your database username.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd"><?php _e( 'Password' ); ?></label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'password', 'example password' ), ENT_QUOTES ); ?>" autocomplete="off" /></td>
			<td><?php _e( 'Your database password.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost"><?php _e( 'Database Host' ); ?></label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td><?php
				/* translators: %s: localhost */
				printf( __( 'You should be able to get this info from your web host, if %s doesn&#8217;t work.' ),'<code>localhost</code>' );
			?></td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix"><?php _e( 'Table Prefix' ); ?></label></th>
			<td><input name="prefix" id="prefix" type="text" value="mcms_" size="25" /></td>
			<td><?php _e( 'If you want to run multiple MandarinCMS installations in a single database, change this.' ); ?></td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="1" /><?php } ?>
	<input type="hidden" name="language" value="<?php echo esc_attr( $language ); ?>" />
	<p class="step"><input name="submit" type="submit" value="<?php echo htmlspecialchars( __( 'Submit' ), ENT_QUOTES ); ?>" class="button button-large" /></p>
</form>
<?php
	break;

	case 2:
	load_default_textdomain( $language );
	$GLOBALS['mcms_locale'] = new MCMS_Locale();

	$dbname = trim( mcms_unslash( $_POST[ 'dbname' ] ) );
	$uname = trim( mcms_unslash( $_POST[ 'uname' ] ) );
	$pwd = trim( mcms_unslash( $_POST[ 'pwd' ] ) );
	$dbhost = trim( mcms_unslash( $_POST[ 'dbhost' ] ) );
	$prefix = trim( mcms_unslash( $_POST[ 'prefix' ] ) );

	$step_1 = 'setup-config.php?step=1';
	$install = 'install.php';
	if ( isset( $_REQUEST['noapi'] ) ) {
		$step_1 .= '&amp;noapi';
	}

	if ( ! empty( $language ) ) {
		$step_1 .= '&amp;language=' . $language;
		$install .= '?language=' . $language;
	} else {
		$install .= '?language=en_US';
	}

	$tryagain_link = '</p><p class="step"><a href="' . $step_1 . '" onclick="javascript:history.go(-1);return false;" class="button button-large">' . __( 'Try again' ) . '</a>';

	if ( empty( $prefix ) )
		mcms_die( __( '<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link ) );

	// Validate $prefix: it can only contain letters, numbers and underscores.
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		mcms_die( __( '<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link ) );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $pwd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// Re-construct $mcmsdb with these new values.
	unset( $mcmsdb );
	require_mcms_db();

	/*
	 * The mcmsdb constructor bails when MCMS_SETUP_CONFIG is set, so we must
	 * fire this manually. We'll fail here if the values are no good.
	 */
	$mcmsdb->db_connect();

	if ( ! empty( $mcmsdb->error ) )
		mcms_die( $mcmsdb->error->get_error_message() . $tryagain_link );

	$errors = $mcmsdb->hide_errors();
	$mcmsdb->query( "SELECT $prefix" );
	$mcmsdb->show_errors( $errors );
	if ( ! $mcmsdb->last_error ) {
		// MySQL was able to parse the prefix as a value, which we don't want. Bail.
		mcms_die( __( '<strong>ERROR</strong>: "Table Prefix" is invalid.' ) );
	}

	// Generate keys and salts using secure CSPRNG; fallback to API if enabled; further fallback to original mcms_generate_password().
	try {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
		$max = strlen($chars) - 1;
		for ( $i = 0; $i < 8; $i++ ) {
			$key = '';
			for ( $j = 0; $j < 64; $j++ ) {
				$key .= substr( $chars, random_int( 0, $max ), 1 );
			}
			$secret_keys[] = $key;
		}
	} catch ( Exception $ex ) {
		$no_api = isset( $_POST['noapi'] );

		if ( ! $no_api ) {
			$secret_keys = mcms_remote_get( 'https://api.mandarincms.com/secret-key/1.1/salt/' );
		}

		if ( $no_api || is_mcms_error( $secret_keys ) ) {
			$secret_keys = array();
			for ( $i = 0; $i < 8; $i++ ) {
				$secret_keys[] = mcms_generate_password( 64, true, true );
			}
		} else {
			$secret_keys = explode( "\n", mcms_remote_retrieve_body( $secret_keys ) );
			foreach ( $secret_keys as $k => $v ) {
				$secret_keys[$k] = substr( $v, 28, 64 );
			}
		}
	}

	$key = 0;
	foreach ( $config_file as $line_num => $line ) {
		if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
			$config_file[ $line_num ] = '$table_prefix  = \'' . addcslashes( $prefix, "\\'" ) . "';\r\n";
			continue;
		}

		if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) )
			continue;

		$constant = $match[1];
		$padding  = $match[2];

		switch ( $constant ) {
			case 'DB_NAME'     :
			case 'DB_USER'     :
			case 'DB_PASSWORD' :
			case 'DB_HOST'     :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
				break;
			case 'DB_CHARSET'  :
				if ( 'utf8mb4' === $mcmsdb->charset || ( ! $mcmsdb->charset && $mcmsdb->has_cap( 'utf8mb4' ) ) ) {
					$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'utf8mb4');\r\n";
				}
				break;
			case 'AUTH_KEY'         :
			case 'SECURE_AUTH_KEY'  :
			case 'LOGGED_IN_KEY'    :
			case 'NONCE_KEY'        :
			case 'AUTH_SALT'        :
			case 'SECURE_AUTH_SALT' :
			case 'LOGGED_IN_SALT'   :
			case 'NONCE_SALT'       :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . $secret_keys[$key++] . "');\r\n";
				break;
		}
	}
	unset( $line );

	if ( ! is_writable(BASED_TREE_URI) ) :
		setup_config_display_header();
?>
<p><?php
	/* translators: %s: mcms-config.php */
	printf( __( 'Sorry, but I can&#8217;t write the %s file.' ), '<code>mcms-config.php</code>' );
?></p>
<p><?php
	/* translators: %s: mcms-config.php */
	printf( __( 'You can create the %s file manually and paste the following text into it.' ), '<code>mcms-config.php</code>' );
?></p>
<textarea id="mcms-config" cols="98" rows="15" class="code" readonly="readonly"><?php
		foreach ( $config_file as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p><?php _e( 'After you&#8217;ve done that, click &#8220;Run the installation.&#8221;' ); ?></p>
<p class="step"><a href="<?php echo $install; ?>" class="button button-large"><?php _e( 'Run the installation' ); ?></a></p>
<script>
(function(){
if ( ! /iPad|iPod|iPhone/.test( navigator.userAgent ) ) {
	var el = document.getElementById('mcms-config');
	el.focus();
	el.select();
}
})();
</script>
<?php
	else :
		/*
		 * If this file doesn't exist, then we are using the database-create-test.php
		 * file one level up, which is for the develop repo.
		 */
		if ( file_exists( BASED_TREE_URI . 'database-create-test.php' ) )
			$path_to_mcms_config = BASED_TREE_URI . 'mcms-config.php';
		else
			$path_to_mcms_config = dirname( BASED_TREE_URI ) . '/mcms-config.php';

		$handle = fopen( $path_to_mcms_config, 'w' );
		foreach ( $config_file as $line ) {
			fwrite( $handle, $line );
		}
		fclose( $handle );
		chmod( $path_to_mcms_config, 0666 );
		setup_config_display_header();
?>
<h1 class="screen-reader-text"><?php _e( 'Successful database connection' ) ?></h1>
<p><?php _e( 'All right, sparky! You&#8217;ve made it through this part of the installation. MandarinCMS can now communicate with your database. If you are ready, time now to&hellip;' ); ?></p>

<p class="step"><a href="<?php echo $install; ?>" class="button button-large"><?php _e( 'Run the installation' ); ?></a></p>
<?php
	endif;
	break;
}
?>
<?php mcms_print_scripts( 'language-chooser' ); ?>
</body>
</html>
