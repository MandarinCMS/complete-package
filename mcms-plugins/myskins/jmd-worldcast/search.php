<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-hierarchy/#search-result
 *
 * @package JMD_MandarinCMS
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="page-content">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<h1 class="page-title">
								<?php 
									/* translators: %s: search query */
									printf( esc_html__( 'Search Results for: %s', 'jmd-worldcast' ), '<span>' . get_search_query() . '</span>' ); 
								?>	
							</h1>
						</div>
						<div class="col-md-6 col-md-push-3">
							<?php if ( have_posts() ) : ?>

							<?php if ( is_home() && ! is_front_page() ) : ?>
								<header>
									<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
								</header>
							<?php endif; ?>

							<?php
								while ( have_posts() ) : the_post();

									get_template_part( 'template-parts/content', 'search' );

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

<?php get_footer();