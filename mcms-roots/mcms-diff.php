<?php
/**
 * MandarinCMS Diff bastard child of old MediaWiki Diff Formatter.
 *
 * Basically all that remains is the table structure and some method names.
 *
 * @package MandarinCMS
 * @subpackage Diff
 */

if ( ! class_exists( 'Text_Diff', false ) ) {
	/** Text_Diff class */
	require( BASED_TREE_URI . MCMSINC . '/Text/Diff.php' );
	/** Text_Diff_Renderer class */
	require( BASED_TREE_URI . MCMSINC . '/Text/Diff/Renderer.php' );
	/** Text_Diff_Renderer_inline class */
	require( BASED_TREE_URI . MCMSINC . '/Text/Diff/Renderer/inline.php' );
}

require( BASED_TREE_URI . MCMSINC . '/class-mcms-text-diff-renderer-table.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-text-diff-renderer-inline.php' );