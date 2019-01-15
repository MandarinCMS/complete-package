<?php
/**
 * The header for our myskin
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-files/#template-partials
 *
 * @package JMD_MandarinCMS
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viemcmsort" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php mcms_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site-wrapper">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'jmd-worldcast' ); ?></a>
	<header id="masthead" class="header">
		<div class="header-inner">
			<div class="container">
				<nav class="site-navigation">
					<?php
						mcms_nav_menu( array(
							'myskin_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
						));
					?>
				</nav>
				<div class="social-wrap">
					<?php $social_twitter = get_myskin_mod('social_twitter');
						if (!empty($social_twitter)) { ?>
							<a href="<?php echo esc_url($social_twitter); ?>" target="_blank">
								<i class="fa fa-twitter" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_facebook = get_myskin_mod('social_facebook');
						if (!empty($social_facebook)) { ?>
							<a href="<?php echo esc_url($social_facebook); ?>" target="_blank">
								<i class="fa fa-facebook" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_google = get_myskin_mod('social_google');
						if (!empty($social_google)) { ?>
							<a href="<?php echo esc_url($social_google); ?>" target="_blank">
								<i class="fa fa-google-plus" aria-hidden="true"></i>
							</a>
					<?php } ?>

					<?php $social_instagram = get_myskin_mod('social_instagram');
						if (!empty($social_instagram)) { ?>
							<a href="<?php echo esc_url($social_instagram); ?>" target="_blank">
								<i class="fa fa-instagram" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_pinterest = get_myskin_mod('social_pinterest');
						if (!empty($social_pinterest)) { ?>
							<a href="<?php echo esc_url($social_pinterest); ?>" target="_blank">
								<i class="fa fa-pinterest" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_vimeo = get_myskin_mod('social_vimeo');
						if (!empty($social_vimeo)) { ?>
							<a href="<?php echo esc_url($social_vimeo); ?>" target="_blank">
								<i class="fa fa-vimeo" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_youtube = get_myskin_mod('social_youtube');
						if (!empty($social_youtube)) { ?>
							<a href="<?php echo esc_url($social_youtube); ?>" target="_blank">
								<i class="fa fa-youtube" aria-hidden="true"></i>
							</a>
					<?php } ?>
					<?php $social_linkedin = get_myskin_mod('social_linkedin');
						if (!empty($social_linkedin)) { ?>
							<a href="<?php echo esc_url($social_linkedin); ?>" target="_blank">
								<i class="fa fa-linkedin" aria-hidden="true"></i>
							</a>
					<?php } ?>
				</div>
				<a id="touch-menu" class="mobile-menu" href="#"><span></span></a>
			</div>
		</div>
	</header>
	<div class="main-page">
		<div class="top-ads-wrap">
			<div class="container">
				<div class="row">
					<div class="col-md-9 col-md-push-3">
						<div class="top-ads-block">
							<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('ads-widget1')) ?>
						</div>
					</div>
					<div class="col-md-3 col-md-pull-9">
						<div class="site-branding header-site-branding">
							<div class="logo-wrap">
								<?php the_custom_logo(); ?>
							</div>
							<?php
								if ( is_front_page() && is_home() ) : ?>
									<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
								<?php else : ?>
									<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
								<?php
								endif;

								$description = get_bloginfo( 'description', 'display' );
								if ( esc_attr($description) || is_customize_preview() ) : ?>
									<p class="site-description"><?php echo esc_html($description); /* MCMSCS: xss ok. */ ?></p>
								<?php
								endif; ?>
						</div><!-- .site-branding -->
					</div>
				</div>
			</div>
		</div>
		<div id="content" class="site-content">