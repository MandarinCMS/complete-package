<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-hierarchy/#single-post
 *
 * @package JMD_MandarinCMS
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="page-content">
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-md-push-3">
							<?php
								while ( have_posts() ) : the_post();

									get_template_part( 'template-parts/content', get_post_type() );

									// If comments are open or we have at least one comment, load up the comment template.
									if ( comments_open() || get_comments_number() ) :
										comments_template();
									endif;

								endwhile; // End of the loop.
								?>
						</div>
						<?php 
							get_sidebar(); 
							get_template_part( 'template-parts/sidebar', 'right' );
						?>
					</div>
				</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer();
