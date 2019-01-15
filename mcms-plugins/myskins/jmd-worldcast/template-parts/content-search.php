<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-hierarchy/
 *
 * @package JMD_MandarinCMS
 */

?>

<article id="post-<?php the_ID(); ?>" class="blog-post-wrap feed-item">
	<div class="blog-post-inner">
		<div class="blog-post-image">
			<?php if ( has_post_thumbnail()) : ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php the_post_thumbnail('jmd-worldcast-blog-post'); ?>
					<?php if ( has_post_format( 'video' )) : ?>
						<span class="video-label"></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
			<div class="categories-wrap">
				<?php the_category(); ?>
			</div>
		</div>
		<div class="blog-post-content">
			<h2>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			</h2>
			<div class="date"><?php the_time('F j, Y'); ?></div>
		</div>
	</div>
</article><!-- #post-<?php the_ID(); ?> -->
