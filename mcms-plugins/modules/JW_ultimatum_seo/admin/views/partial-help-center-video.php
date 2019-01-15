<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( ! empty( $tab_video_url ) ) :

	?>

	<div class="mcmsseo-tab-video__panel mcmsseo-tab-video__panel--video">
		<div class="mcmsseo-tab-video__data" data-url="<?php echo $tab_video_url ?>"></div>
	</div>
	<div class="mcmsseo-tab-video__panel mcmsseo-tab-video__panel--text">
		<?php

		// Don't show for Premium.
		if ( ! defined( 'MCMSSEO_PREMIUM_PLUGIN_FILE' ) ) :
			?>
			<div class="mcmsseo-tab-video__panel__textarea">
				<h3><?php _e( 'Need more help?', 'mandarincms-seo' ); ?></h3>
				<?php /* translators: %s expands to Ultimatum SEO */ ?>
				<p><?php printf( __( 'If you buy %s you\'ll get access to our support team and bonus features!', 'mandarincms-seo' ), 'Ultimatum SEO' ); ?></p>
				<?php /* translators: %s expands to Ultimatum SEO */ ?>
				<p><a href="https://jiiworks.net/seo-premium-vt"
				      target="_blank"><?php printf( __( 'Get %s &raquo;', 'mandarincms-seo' ), 'Ultimatum SEO' ); ?></a>
				</p>
			</div>
			<?php
		endif;
		?>
		<div class="mcmsseo-tab-video__panel__textarea">
			<?php /* translators: %s expands to Ultimatum SEO */ ?>
			<h3><?php printf( __( 'Want to be a %s Expert?', 'mandarincms-seo' ), 'Ultimatum SEO' ); ?></h3>
			<?php /* translators: %$1s expands to Ultimatum SEO */ ?>
			<p><?php printf( __( 'Follow our %1$s for MandarinCMS training and become a certified %1$s Expert!', 'mandarincms-seo' ), 'Ultimatum SEO' ); ?></p>
			<?php /* translators: %s expands to Ultimatum SEO for MandarinCMS */ ?>
			<p><a href="https://jiiworks.net/mandarincms-training-vt"
			      target="_blank"><?php printf( __( 'Enroll in the %s training &raquo;', 'mandarincms-seo' ), 'Ultimatum SEO for MandarinCMS' ); ?></a>
			</p>
		</div>
	</div>
	<?php

endif;
