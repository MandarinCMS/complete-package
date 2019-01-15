<?php
/**
 * @package MCMSSEO\Admin
 * @since      1.5.0
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$extensions = array(
	'seo-premium'     => (object) array(
		'url'       => 'https://jiiworks.net/mandarincms/modules/seo-premium/',
		'title'     => 'Ultimatum SEO',
		/* translators: %1$s expands to Ultimatum SEO */
		'desc'      => sprintf( __( 'The premium version of %1$s with more features & support.', 'mandarincms-seo' ), 'Ultimatum SEO' ),
		'installed' => false,
		'image'     => modules_url( 'images/extensions-premium-ribbon.png', MCMSSEO_FILE ),
		'benefits'  => array(),
	),
	'video-seo'       => (object) array(
		'url'       => 'https://jiiworks.net/mandarincms/modules/video-seo/',
		'title'     => 'Video SEO',
		'desc'      => __( 'Optimize your videos to show them off in search results and get more clicks!', 'mandarincms-seo' ),
		'installed' => false,
		'image'     => modules_url( 'images/extensions-video.png', MCMSSEO_FILE ),
		'benefits'  => array(
			__( 'Show your videos in Google Videos', 'mandarincms-seo' ),
			__( 'Enhance the experience of sharing posts with videos', 'mandarincms-seo' ),
			__( 'Make videos responsive through enabling fitvids.js', 'mandarincms-seo' ),
		),
	),
	'news-seo'        => (object) array(
		'url'       => 'https://jiiworks.net/mandarincms/modules/news-seo/',
		'title'     => 'News SEO',
		'desc'      => __( 'Are you in Google News? Increase your traffic from Google News by optimizing for it!', 'mandarincms-seo' ),
		'installed' => false,
		'image'     => modules_url( 'images/extensions-news.png', MCMSSEO_FILE ),
		'benefits'  => array(
			__( 'Optimize your site for Google News', 'mandarincms-seo' ),
			__( 'Immediately pings Google on the publication of a new post', 'mandarincms-seo' ),
			__( 'Creates XML News Sitemaps', 'mandarincms-seo' ),
		),
	),
	'local-seo'       => (object) array(
		'url'       => 'https://jiiworks.net/mandarincms/modules/local-seo/',
		'title'     => 'Local SEO',
		'desc'      => __( 'Rank better locally and in Google Maps, without breaking a sweat!', 'mandarincms-seo' ),
		'installed' => false,
		'image'     => modules_url( 'images/extensions-local.png', MCMSSEO_FILE ),
		'benefits'  => array(
			__( 'Get found by potential clients', 'mandarincms-seo' ),
			__( 'Easily insert Google Maps, a store locator, opening hours and more', 'mandarincms-seo' ),
			__( 'Improve the usability of your contact page', 'mandarincms-seo' ),
		),
	),
	'woocommerce-seo' => (object) array(
		'url'       => 'https://jiiworks.net/mandarincms/modules/ultimatum-woocommerce-seo/',
		'title'     => 'Ultimatum WooCommerce SEO',
		/* translators: %1$s expands to Ultimatum SEO */
		'desc'      => sprintf( __( 'Seamlessly integrate WooCommerce with %1$s and get extra features!', 'mandarincms-seo' ), 'Ultimatum SEO' ),
		'installed' => false,
		'image'     => modules_url( 'images/extensions-woo.png', MCMSSEO_FILE ),
		'benefits' => array(
			/* %1$s expands to Pinterest */
			sprintf( __( 'Improve sharing on Pinterest', 'mandarincms-seo' ) ),

			/* %1$s expands to Ultimatum, %2$s expands to WooCommerce */
			sprintf( __( 'Use %1$s breadcrumbs instead of %2$s ones', 'mandarincms-seo' ), 'Ultimatum', 'WooCommerce' ),

			/* %1$s expands to Ultimatum SEO, %2$s expands to WooCommerce */
			sprintf( __( 'A seamless integration between %1$s and %2$s', 'mandarincms-seo' ), 'Ultimatum SEO', 'WooCommerce' ),
		),
		'buy_button' => 'WooCommerce SEO',
	),
);

if ( class_exists( 'MCMSSEO_Premium' ) ) {
	$extensions['seo-premium']->installed = true;
}
if ( class_exists( 'mcmsseo_Video_Sitemap' ) ) {
	$extensions['video-seo']->installed = true;
}
if ( class_exists( 'MCMSSEO_News' ) ) {
	$extensions['news-seo']->installed = true;
}
if ( defined( 'MCMSSEO_LOCAL_VERSION' ) ) {
	$extensions['local-seo']->installed = true;
}
if ( ! class_exists( 'Woocommerce' ) ) {
	unset( $extensions['woocommerce-seo'] );
}
elseif ( class_exists( 'Ultimatum_WooCommerce_SEO' ) ) {
	$extensions['woocommerce-seo']->installed = true;
}

$utm_buy = '#utm_source=mandarincms-seo-config&utm_medium=button-buy&utm_campaign=extension-page-banners';
$utm_info = '#utm_source=mandarincms-seo-config&utm_medium=button-info&utm_campaign=extension-page-banners';

?>

<div class="wrap mcmsseo_table_page ultimatum">

	<h1 id="mcmsseo-title" class="ultimatum-h1"><?php
		/* translators: %1$s expands to Ultimatum SEO */
		printf( __( '%1$s Extensions', 'mandarincms-seo' ), 'Ultimatum SEO' );
		?></h1>

	<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">
		<a class="nav-tab" id="extensions-tab" href="#top#extensions"><?php _e( 'Extensions', 'mandarincms-seo' ); ?></a>
		<a class="nav-tab" id="licenses-tab" href="#top#licenses"><?php _e( 'Licenses', 'mandarincms-seo' ); ?></a>
	</h2>

	<div class="tabwrapper">
		<div id="extensions" class="mcmsseotab">
			<section class="ultimatum-seo-premium-extension">
				<?php
					$extension = $extensions['seo-premium'];
					$url = $extension->url;
				?>
				<h2><?php
					/* translators: %1$s expands to Ultimatum SEO */
					printf( __( '%1$s, take your optimization to the next level!', 'mandarincms-seo' ), '<span class="ultimatum-heading-highlight">' . $extension->title . '</span>' );
					?></h2>

				<ul class="ultimatum-seo-premium-benefits ultimatum-list--usp">
					<li class="ultimatum-seo-premium-benefits__item">
						<span class="ultimatum-seo-premium-benefits__title"><?php _e( 'Redirect manager', 'mandarincms-seo' ); ?></span>
						<span class="ultimatum-seo-premium-benefits__description"><?php _e( 'create and manage redirects from within your MandarinCMS install.', 'mandarincms-seo' ); ?></span>
					</li>
					<li class="ultimatum-seo-premium-benefits__item">
						<span class="ultimatum-seo-premium-benefits__title"><?php _e( 'Multiple focus keywords', 'mandarincms-seo' ); ?></span>
						<span class="ultimatum-seo-premium-benefits__description"><?php _e( 'optimize a single post for up to 5 keywords.', 'mandarincms-seo' ); ?></span>
					</li>
					<li class="ultimatum-seo-premium-benefits__item">
						<span class="ultimatum-seo-premium-benefits__title"><?php _e( 'Social previews', 'mandarincms-seo' ); ?></span>
						<span class="ultimatum-seo-premium-benefits__description"><?php _e( 'check what your Facebook or Twitter post will look like.', 'mandarincms-seo' ); ?></span>
					</li>
					<li class="ultimatum-seo-premium-benefits__item">
						<span class="ultimatum-seo-premium-benefits__title"><?php _e( 'Premium support', 'mandarincms-seo' ); ?></span>
						<span class="ultimatum-seo-premium-benefits__description"><?php _e( 'gain access to our 24/7 support team.', 'mandarincms-seo' ); ?></span>
					</li>
				</ul>

				<?php if ( $extension->installed ) : ?>
					<div class="ultimatum-button ultimatum-button--noarrow ultimatum-button--installed"><?php _e( 'Installed', 'mandarincms-seo' ); ?></div>
				<?php else : ?>
					<a target="_blank" href="<?php echo esc_url( $url . $utm_buy ); ?>" class="ultimatum-button default ultimatum-button--noarrow ultimatum-button-go-to"><?php
						/* translators: $1$s expands to Ultimatum SEO */
						printf( __( 'Buy %1$s', 'mandarincms-seo' ), $extension->title );
						?></a>
				<?php endif; ?>

				<a target="_blank" href="<?php echo esc_url( $url . $utm_info ); ?>" class="ultimatum-link--more-info"><?php
					/* translators: Text between %1$s and %2$s will only be shown to screen readers. %3$s expands to the product name. */
					printf(
						__( 'More information %1$sabout %3$s%2$s', 'mandarincms-seo' ),
						'<span class="screen-reader-text">',
						'</span>',
						$extension->title
					);
					?></a>

				<p><small class="ultimatum-money-back-guarantee"><?php _e( 'Comes with our 30-day no questions asked money back guarantee', 'mandarincms-seo' ); ?></small></p>
			</section>

			<hr class="ultimatum-hr" aria-hidden="true" />

			<section class="ultimatum-promo-extensions">
				<h2><?php
					/* %1$s expands to Ultimatum SEO */
					$ultimatum_seo_extensions = sprintf( __( '%1$s extensions', 'mandarincms-seo' ), 'Ultimatum SEO' );

					$ultimatum_seo_extensions = '<span class="ultimatum-heading-highlight">' . $ultimatum_seo_extensions . '</span>';

					/* translators: %1$s expands to Ultimatum SEO extensions */
					printf( __( '%1$s to optimize your site even further', 'mandarincms-seo' ), $ultimatum_seo_extensions );
					?></h2>

				<?php unset( $extensions['seo-premium'] ); ?>

				<?php foreach ( $extensions as $id => $extension ) : ?>
					<?php $url = $extension->url; ?>

					<section class="ultimatum-promoblock secondary ultimatum-promo-extension">
						<img alt="" width="280" height="147" src="<?php echo esc_attr( $extension->image ); ?>" />
						<h3><?php echo esc_html( $extension->title ); ?></h3>

						<ul class="ultimatum-list--usp">
							<?php foreach ( $extension->benefits as $benefit ) : ?>
								<li><?php echo esc_html( $benefit ); ?></li>
							<?php endforeach; ?>
						</ul>


						<?php if ( $extension->installed ) : ?>
							<div class="ultimatum-button ultimatum-button--noarrow ultimatum-button--installed"><?php _e( 'Installed', 'mandarincms-seo' ); ?></div>
						<?php else : ?>
							<a target="_blank" class="ultimatum-button default ultimatum-button--noarrow academy--secondary ultimatum-button-go-to" href="<?php echo esc_url( $url . $utm_buy ); ?>">
								<?php $product_name = isset( $extension->buy_button ) ? $extension->buy_button : $extension->title; ?>
								<?php /* translators: %s expands to the product name */ ?>
								<?php printf( __( 'Buy %s', 'mandarincms-seo' ), $product_name ); ?>
							</a>
						<?php endif; ?>

						<a target="_blank" class="ultimatum-link--more-info" href="<?php echo esc_url( $url . $utm_info ); ?>"><?php
							/* translators: Text between %1$s and %2$s will only be shown to screen readers. %3$s expands to the product name. */
							printf(
								__( 'More information %1$sabout %3$s%2$s', 'mandarincms-seo' ),
								'<span class="screen-reader-text">',
								'</span>',
								$extension->title
							);
							?></a>
					</section>
				<?php endforeach; ?>
			</section>
		</div>

		<div id="licenses" class="mcmsseotab">
			<?php

			/**
			 * Display license page
			 */
			settings_errors();
			if ( ! has_action( 'mcmsseo_licenses_forms' ) ) {
				echo '<div class="msg"><p>', __( 'This is where you would enter the license keys for one of our premium modules, should you activate one.', 'mandarincms-seo' ), '</p></div>';
			}
			else {
				do_action( 'mcmsseo_licenses_forms' );
			}
			?>
		</div>
	</div>

</div>
