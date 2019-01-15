<?php
/*
Module Name: JW Contact Support
Description: Just another contact form module. Simple but flexible.
Text Domain: jw-contact-support
Domain Path: /languages/
Version: 5.0.2
*/

define( 'MCMSCF7_VERSION', '5.0.2' );

define( 'MCMSCF7_REQUIRED_MCMS_VERSION', '4.8' );

define( 'MCMSCF7_MODULE', __FILE__ );

define( 'MCMSCF7_MODULE_BASENAME', module_basename( MCMSCF7_MODULE ) );

define( 'MCMSCF7_MODULE_NAME', trim( dirname( MCMSCF7_MODULE_BASENAME ), '/' ) );

define( 'MCMSCF7_MODULE_DIR', untrailingslashit( dirname( MCMSCF7_MODULE ) ) );

define( 'MCMSCF7_MODULE_MODULES_DIR', MCMSCF7_MODULE_DIR . '/modules' );

if ( ! defined( 'MCMSCF7_LOAD_JS' ) ) {
	define( 'MCMSCF7_LOAD_JS', true );
}

if ( ! defined( 'MCMSCF7_LOAD_CSS' ) ) {
	define( 'MCMSCF7_LOAD_CSS', true );
}

if ( ! defined( 'MCMSCF7_AUTOP' ) ) {
	define( 'MCMSCF7_AUTOP', true );
}

if ( ! defined( 'MCMSCF7_USE_PIPE' ) ) {
	define( 'MCMSCF7_USE_PIPE', true );
}

if ( ! defined( 'MCMSCF7_ADMIN_READ_CAPABILITY' ) ) {
	define( 'MCMSCF7_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

if ( ! defined( 'MCMSCF7_ADMIN_READ_WRITE_CAPABILITY' ) ) {
	define( 'MCMSCF7_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

if ( ! defined( 'MCMSCF7_VERIFY_NONCE' ) ) {
	define( 'MCMSCF7_VERIFY_NONCE', false );
}

if ( ! defined( 'MCMSCF7_USE_REALLY_SIMPLE_CAPTCHA' ) ) {
	define( 'MCMSCF7_USE_REALLY_SIMPLE_CAPTCHA', false );
}

if ( ! defined( 'MCMSCF7_VALIDATE_CONFIGURATION' ) ) {
	define( 'MCMSCF7_VALIDATE_CONFIGURATION', true );
}

// Deprecated, not used in the module core. Use mcmscf7_module_url() instead.
define( 'MCMSCF7_MODULE_URL', untrailingslashit( modules_url( '', MCMSCF7_MODULE ) ) );

require_once MCMSCF7_MODULE_DIR . '/settings.php';
