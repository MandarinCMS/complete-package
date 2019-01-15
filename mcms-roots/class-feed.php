<?php
/**
 * Feed API
 *
 * @package MandarinCMS
 * @subpackage Feed
 */

_deprecated_file( basename( __FILE__ ), '4.7.0', 'fetch_feed()' );

if ( ! class_exists( 'SimplePie', false ) ) {
	require_once( BASED_TREE_URI . MCMSINC . '/class-simplepie.php' );
}

require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-feed-cache.php' );
require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-feed-cache-transient.php' );
require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-simplepie-file.php' );
require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-simplepie-sanitize-kses.php' );