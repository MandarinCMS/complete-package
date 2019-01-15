<?php
/**
 * @package MCMSSEO\Main
 */

/**
 * Module Name: RazorLeaf Ultimatum
 * Version: 3.8
 * Description: The first true all-in-one SEO solution for MandarinCMS, including on-page content analysis, XML sitemaps and much more.
 * Text Domain: mandarincms-seo
 * Domain Path: /languages/
 * License: GPL v3
 */

/**
 * Ultimatum SEO Module
 * Copyright (C) 2008-2016, Ultimatum BV - support@jiiworks.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(dirname(__FILE__) .'/assets/stylesheets/bootstrap.min.css');

if ( ! defined( 'MCMSSEO_FILE' ) ) {
	define( 'MCMSSEO_FILE', __FILE__ );
}

if ( ! defined( 'MCMSSEO_PREMIUM_PLUGIN_FILE' ) ) {
	define( 'MCMSSEO_PREMIUM_PLUGIN_FILE', __FILE__ );
}

$mcmsseo_premium_dir = module_dir_path( MCMSSEO_PREMIUM_PLUGIN_FILE ) . 'premium/';

// Run the redirects when frontend is being opened.
if ( ! is_admin() ) {
	require_once( $mcmsseo_premium_dir . 'classes/redirect/class-redirect-util.php' );
	require_once( $mcmsseo_premium_dir . 'classes/redirect/class-redirect-handler.php' );

	new MCMSSEO_Redirect_Handler();
}

/**
 * Filters the defaults for the `mcmsseo` option.
 *
 * @param array $mcmsseo_defaults The defaults for the `mcmsseo` option.
 *
 * @return array
 */
function mcmsseo_premium_add_general_option_defaults( array $mcmsseo_defaults ) {
	$premium_defaults = array(
		'enable_metabox_insights' => true,
	);

	return array_merge( $mcmsseo_defaults, $premium_defaults );
}
add_filter( 'mcmsseo_option_mcmsseo_defaults', 'mcmsseo_premium_add_general_option_defaults' );

// Load the MandarinCMS SEO module.
require_once( dirname( MCMSSEO_FILE ) . '/mcms-seo-main.php' );
require_once( dirname( MCMSSEO_PREMIUM_PLUGIN_FILE ) . '/premium/class-premium.php' );

MCMSSEO_Premium::autoloader();

/**
 * Run the upgrade for Ultimatum SEO.
 */
function mcmsseo_premium_run_upgrade() {
	$upgrade_manager = new MCMSSEO_Upgrade_Manager();
	$upgrade_manager->run_upgrade( MCMSSEO_VERSION );
}

/*
 * If the user is admin, check for the upgrade manager.
 * Considered to use 'admin_init' but that is called too late in the process.
 */
if ( is_admin() ) {
	add_action( 'init', 'mcmsseo_premium_run_upgrade' );
}

/**
 * The premium setup
 */
function mcmsseo_premium_init() {
	new MCMSSEO_Premium();
}

add_action( 'modules_loaded', 'mcmsseo_premium_init', 14 );

// Activation hook.
if ( is_admin() ) {
	register_activation_hook( __FILE__, array( 'MCMSSEO_Premium', 'install' ) );
}
