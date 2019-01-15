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

	if ( ! empty( $module->info->selling_point_0 ) ||
	     ! empty( $module->info->selling_point_1 ) ||
	     ! empty( $module->info->selling_point_2 )
	) : ?>
		<div class="fs-selling-points">
			<ul>
				<?php for ( $i = 0; $i < 3; $i ++ ) : ?>
					<?php if ( ! empty( $module->info->{'selling_point_' . $i} ) ) : ?>
						<li><i class="dashicons dashicons-yes"></i>

							<h3><?php echo esc_html( $module->info->{'selling_point_' . $i} ) ?></h3></li>
					<?php endif ?>
				<?php endfor ?>
			</ul>
		</div>
	<?php endif ?>
	<div>
		<?php
			echo mcms_kses( $module->info->description, array(
				'a'          => array( 'href' => array(), 'title' => array(), 'target' => array() ),
				'b'          => array(),
				'i'          => array(),
				'p'          => array(),
				'blockquote' => array(),
				'h2'         => array(),
				'h3'         => array(),
				'ul'         => array(),
				'ol'         => array(),
				'li'         => array()
			) );
		?>
	</div>
<?php if ( ! empty( $module->info->screenshots ) ) : ?>
	<?php $screenshots = $module->info->screenshots ?>
	<div class="fs-screenshots clearfix">
		<h2><?php fs_esc_html_echo_inline( 'Screenshots', 'screenshots', $module->slug ) ?></h2>
		<ul>
			<?php $i = 0;
				foreach ( $screenshots as $s => $url ) : ?>
					<?php
					// Relative URLs are replaced with MandarinCMS.org base URL
					// therefore we need to set absolute URLs.
					$url = 'http' . ( MCMS_FS__IS_HTTPS ? 's' : '' ) . ':' . $url;
					?>
					<li class="<?php echo ( 0 === $i % 2 ) ? 'odd' : 'even' ?>">
						<style>
							#section-description .fs-screenshots <?php echo ".fs-screenshot-{$i}" ?>
							{
								background-image: url('<?php echo $url ?>');
							}
						</style>
						<a href="<?php echo $url ?>"
						   title="<?php echo esc_attr( sprintf( fs_text_inline( 'Click to view full-size screenshot %d', 'view-full-size-x', $module->slug ), $i ) ) ?>"
						   class="fs-screenshot-<?php echo $i ?>"></a>
					</li>
					<?php $i ++; endforeach ?>
		</ul>
	</div>
<?php endif ?>