<?php
/**
 * The main template file
 *
 * This is the most generic template file in a MandarinCMS myskin
 * and one of the two required files for a myskin (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-hierarchy/
 *
 * @package JMD_MandarinCMS
 */

get_header();
?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="page-content">
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-md-push-3">
							<?php get_template_part( 'template-parts/content', 'slider' ); ?>

							<?php
								if ( have_posts() ) : 
							?>

							<?php if ( is_home() && ! is_front_page() ) : ?>
								<header>
									<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
								</header>
							<?php endif; ?>

							<?php
								$x = 0;
								while ( have_posts() ) : the_post();

									get_template_part( 'template-parts/content', 'archive' );
									if($x == 1) {
										echo '<div class="clear_b"></div>';
										$x = -1;
									}
									$x++;

								endwhile; 

								the_posts_pagination( array(
									'prev_text'          => __( '&#8592', 'jmd-worldcast' ),
									'next_text'          => __( '&#8594', 'jmd-worldcast' ),
									'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'jmd-worldcast' ) . ' </span>',
								) );

								else :
									get_template_part( 'template-parts/content', 'none' );

								endif;
							?>
						</div>
						<?php 
							get_sidebar(); 
							get_template_part( 'template-parts/sidebar', 'right' );
						?>
					</div>
				</div>
			</div>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();