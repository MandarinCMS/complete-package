<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.mandarincms.org/Creating_an_Error_404_Page
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
							<section class="error-404 not-found">
								<header class="page-header">
									<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'jmd-worldcast' ); ?></h1>
								</header><!-- .page-header -->

								<div class="page-content">
									<p><?php esc_html_e( 'It looks like nothing was found at this location. Try one of the links below or a search?', 'jmd-worldcast' ); ?></p>

									<?php
										get_search_form();
										the_widget( 'MCMS_Widget_Recent_Posts' );
									?>
								</div><!-- .page-content -->
							</section><!-- .error-404 -->

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
