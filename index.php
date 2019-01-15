<?php
/**
 * Front to the MandarinCMS application. This file doesn't do anything, but loads
 * components.php which does and tells MandarinCMS to load the myskin.
 *
 * @package MandarinCMS
 */

/**
 * Tells MandarinCMS to load the MandarinCMS myskin and output it.
 *
 * @var bool
 */
define('MCMS_USE_THEMES', true);

/** Loads the MandarinCMS Environment and Template */
require( dirname( __FILE__ ) . '/components.php' );
