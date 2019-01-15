<?php
/**
 * Front-end Actions
 *
 * @package     POPMAKE
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Hooks BaloonUp Maker actions, when present in the $_GET superglobal. Every balooncreate_action
 * present in $_GET is called using MandarinCMS's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_get_actions() {
	if ( isset( $_GET['balooncreate_action'] ) ) {
		do_action( 'balooncreate_' . $_GET['balooncreate_action'], $_GET );
	}
}

add_action( 'init', 'balooncreate_get_actions' );

/**
 * Hooks BaloonUp Maker actions, when present in the $_POST superglobal. Every balooncreate_action
 * present in $_POST is called using MandarinCMS's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_post_actions() {
	if ( isset( $_POST['balooncreate_action'] ) ) {
		do_action( 'balooncreate_' . $_POST['balooncreate_action'], $_POST );
	}
}

add_action( 'init', 'balooncreate_post_actions' );