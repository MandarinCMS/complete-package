<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.0.6
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	/**
	 * @var array $VARS
	 *
	 * @var FS_Module $module
	 */
	$module = $VARS['module'];

	$screenshots = $VARS['screenshots'];
?>
<ol>
	<?php $i = 0;
		foreach ( $screenshots as $s => $url ) : ?>
			<?php
			// Relative URLs are replaced with MandarinCMS.org base URL
			// therefore we need to set absolute URLs.
			$url = 'http' . ( MCMS_FS__IS_HTTPS ? 's' : '' ) . ':' . $url;
			?>
			<li>
				<a href="<?php echo $url ?>" title="<?php echo esc_attr( sprintf( fs_text_inline( 'Click to view full-size screenshot %d', 'view-full-size-x', $module->slug ), $i ) ) ?>"><img src="<?php echo $url ?>"></a>
			</li>
			<?php $i ++; endforeach ?>
</ol>
