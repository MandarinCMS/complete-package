<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

if ( ! class_exists( 'PUM_Extension_Updater' ) ) {
	require_once 'class-pum-extension-updater.php';
}

/**
 * @deprecated 1.5.0
 *
 * Use PUM_Extension_Updater.
 */
class BaloonUpMaker_Module_Updater  extends PUM_Extension_Updater {}
