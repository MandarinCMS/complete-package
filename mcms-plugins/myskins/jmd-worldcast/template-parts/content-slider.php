<?php
/**
 * The slider containing recent posts
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-files/#template-partials
 *
 * @package JMD_MandarinCMS
 */
?>

<div class="main-slider-wrap">
	<div class="swiper-container home-slider">
		<div class="swiper-wrapper">

			<?php 
				$the_query = new MCMS_Query( array( 'posts_per_page' => 5 ) );
				
				while ($the_query -> have_posts()) : $the_query -> the_post(); ?>
					<div class="swiper-slide main-slide">
						<div class="main-slide-top">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
								<span class="slide-thumbnail"><?php the_post_thumbnail( 'full' ); ?></span>
								<?php if ( has_post_format( 'video' )) : ?>
									<span class="video-label"></span>
								<?php endif; ?>
							</a>
							<div class="categories-wrap">
								<?php the_category(); ?>
							</div>
						</div>
						<div class="main-slide-content">
							<h3>
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="main-slide-date"><?php the_time('F j, Y'); ?></div>
						</div>
					</div>
				<?php endwhile; ?>
				<?php mcms_reset_postdata(); ?>
		</div>
		<div class="slide-button slide-next"></div>
		<div class="slide-button slide-prev"></div>
	</div>
</div>