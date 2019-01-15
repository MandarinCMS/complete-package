<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! function_exists( '_ultimatum_display_alerts' ) ) {
	/**
	 * Create the alert HTML with restore/dismiss button
	 *
	 * @param array  $list   List of alerts.
	 * @param string $status Status of the alerts (active/dismissed).
	 */
	function _ultimatum_display_alerts( $list, $status ) {
		foreach ( $list as $notification ) {

			switch ( $status ) {
				case 'active':
					$button = sprintf( '<button type="button" class="button button-link dismiss"><span class="screen-reader-text">%1$s</span><span class="dashicons dashicons-no-alt"></span></button>', __( 'Dismiss this item.', 'mandarincms-seo' ) );
					break;

				case 'dismissed':
					$button = sprintf( '<button type="button" class="button button-link restore"><span class="screen-reader-text">%1$s</span><span class="dashicons dashicons-hidden"></span></button>', __( 'Restore this item.', 'mandarincms-seo' ) );
					break;
			}

			printf( '<div class="ultimatum-alert-holder" id="%1$s" data-nonce="%2$s" data-json="%3$s">%4$s%5$s</div>', $notification->get_id(), $notification->get_nonce(), $notification->get_json(), $notification, $button );
		}
	}
}

if ( ! $active ) {
	$dashicon = 'yes';
}

?>
<h3><span class="dashicons dashicons-<?php echo $dashicon; ?>"></span> <?php echo $i18n_title ?> (<?php echo $active_total ?>)</h3>

<div id="ultimatum-<?php echo $type ?>">

	<?php if ( $total ) : ?>
		<p><?php echo ( ! $active ) ? $i18n_no_issues : $i18n_issues; ?></p>

		<div class="container" id="ultimatum-<?php echo $type ?>-active">
			<?php _ultimatum_display_alerts( $active, 'active' ); ?>
		</div>

		<?php if ( $dismissed ) : ?>
			<div class="separator"></div>
		<?php endif; ?>

		<div class="container" id="ultimatum-<?php echo $type ?>-dismissed">
			<?php _ultimatum_display_alerts( $dismissed, 'dismissed' ); ?>
		</div>

		<div class="ultimatum-bottom-spacing"></div>

	<?php else : ?>

		<p><?php echo $i18n_no_issues; ?></p>

	<?php endif; ?>
</div>
