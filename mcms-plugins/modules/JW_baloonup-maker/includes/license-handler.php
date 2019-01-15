<?php
/**
 * License handler for BaloonUp Maker
 *
 * This class should simplify the process of adding license information to new BaloonUp Maker extensions.
 *
 * Note for mandarincms.org admins. This is not called in the free hosted version and is simply used for hooking in addons to one update system rather than including it in each module.
 * @version 1.1
 */

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
} // Exit if accessed directly

/**
 * PopMake_License Class
 *
 * @deprecated 1.5.0
 *
 * Use PUM_Extension_License instead.
 */
class PopMake_License extends PUM_Extension_License {}