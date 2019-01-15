<?php
/**
 * @package MCMSSEO\Admin|Google_Search_Console
 *
 * This is the view for the modal box that appears when the create redirect link is clicked
 */

/**
 * @var string         $view_type        The type of view to be displayed, can be 'create', 'already_exists', 'no_premium'
 * @var MCMSSEO_Redirect $current_redirect The existing redirect
 * @var string         $url              Redirect for URL
 */

$unique_id = md5( $url );
?>
<div id='redirect-<?php echo $unique_id; ?>' class="hidden">
	<div class='form-wrap mcmsseo_content_wrapper'>
	<?php
	switch ( $view_type ) {
		case 'create' :
			echo '<h1 class="mcmsseo-redirect-url-title">', __( 'Redirect this broken URL and fix the error', 'mandarincms-seo' ), '</h1>';
			?>
			<div class='form-field form-required'>
				<label for='mcmsseo-current-url-<?php echo $unique_id; ?>'><?php _e( 'Current URL:', 'mandarincms-seo' ); ?></label>
				<input type='text' id='mcmsseo-current-url-<?php echo $unique_id; ?>' name='current_url' value='<?php echo $url; ?>' readonly />
			</div>
			<div class='form-field form-required'>
				<label for='mcmsseo-new-url-<?php echo $unique_id; ?>'><?php _e( 'New URL:', 'mandarincms-seo' ); ?></label>
				<input type='text' id='mcmsseo-new-url-<?php echo $unique_id; ?>' name='new_url' value='' />
			</div>
			<div class='form-field form-required'>
				<label for='mcmsseo-mark-as-fixed-<?php echo $unique_id; ?>' class='clear'><?php _e( 'Mark as fixed:', 'mandarincms-seo' ); ?></label>
				<input type='checkbox' checked value='1' id='mcmsseo-mark-as-fixed-<?php echo $unique_id; ?>' name='mark_as_fixed' class='clear' aria-describedby='mcmsseo-mark-as-fixed-desc-<?php echo $unique_id; ?>' />
				<p id='mcmsseo-mark-as-fixed-desc-<?php echo $unique_id; ?>'><?php
					/* Translators: %1$s: expands to 'Google Search Console'. */
					echo sprintf( __( 'Mark this issue as fixed in %1$s.', 'mandarincms-seo' ), 'Google Search Console' );
					?></p>
			</div>
			<p class='submit'>
				<input type='button' name='submit' id='submit-<?php echo $unique_id; ?>' class='button button-primary' value='<?php _e( 'Create redirect', 'mandarincms-seo' ); ?>' onclick='mcmsseo_gsc_post_redirect( jQuery( this ) );' />
				<button type="button" class="button mcmsseo-redirect-close"><?php esc_html_e( 'Cancel', 'mandarincms-seo' ); ?></button>
			</p>
			<?php
			break;

		case 'already_exists' :
			echo '<h1 class="mcmsseo-redirect-url-title">', __( 'Error: a redirect for this URL already exists', 'mandarincms-seo' ), '</h1>';
			echo '<p>';

			// There is no target.
			if ( in_array( $current_redirect->get_type(), array( MCMSSEO_Redirect::DELETED, MCMSSEO_Redirect::UNAVAILABLE ) ) ) {
				/* Translators: %1$s: expands to the current URL. */
				echo sprintf(
					__( 'You do not have to create a redirect for URL %1$s because a redirect already exists. If this is fine you can mark this issue as fixed. If not, please go to the redirects page and change the redirect.', 'mandarincms-seo' ),
					'<code>' . $url . '</code>'
				);
			}
			else {
				/* Translators: %1$s: expands to the current URL and %2$s expands to URL the redirects points to. */
				echo sprintf(
					__( 'You do not have to create a redirect for URL %1$s because a redirect already exists. The existing redirect points to %2$s. If this is fine you can mark this issue as fixed. If not, please go to the redirects page and change the target URL.', 'mandarincms-seo' ),
					'<code>' . $url . '</code>',
					'<code>' . $current_redirect->get_target() . '</code>'
				);
			}
			echo '</p>';
			break;

		case 'no_premium' :
			/* Translators: %s: expands to Ultimatum SEO */
			echo '<h1 class="mcmsseo-redirect-url-title">', sprintf( __( 'Creating redirects is a %s feature', 'mandarincms-seo' ), 'Ultimatum SEO' ), '</h1>';
			echo '<p>';
			/* Translators: %1$s: expands to 'Ultimatum SEO', %2$s: links to Ultimatum SEO module page. */
			echo sprintf(
				__( 'To be able to create a redirect and fix this issue, you need %1$s. You can buy the module, including one year of support and updates, on %2$s.', 'mandarincms-seo' ),
				'Ultimatum SEO',
				'<a href="https://jiiworks.net/redirects" target="_blank">jiiworks.net</a>'
			);
			echo '</p>';
			echo '<button type="button" class="button mcmsseo-redirect-close">' . __( 'Close', 'mandarincms-seo' ) . '</button>';
			break;
	}
	?>
	</div>
</div>
