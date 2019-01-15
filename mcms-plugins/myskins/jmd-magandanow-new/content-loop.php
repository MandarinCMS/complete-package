<?php /* Loop Template used for index/archive/search */ ?>
<article <?php post_class('mh-loop-item mh-clearfix'); ?>>
	<figure class="mh-loop-thumb">
		<a href="<?php the_permalink(); ?>"><?php
			if (has_post_thumbnail()) {
				the_post_thumbnail('jmd-magandanow-new-medium');
			} else {
				echo '<img class="mh-image-placeholder" src="' . get_template_directory_uri() . '/images/placeholder-medium.png' . '" alt="' . esc_html__('No Image', 'jmd-magandanow-new') . '" />';
			} ?>
		</a>
	</figure>
	<div class="mh-loop-content mh-clearfix">
		<header class="mh-loop-header">
			<h3 class="entry-title mh-loop-title">
				<a href="<?php the_permalink(); ?>" rel="bookmark">
					<?php the_title(); ?>
				</a>
			</h3>
			<div class="mh-meta mh-loop-meta">
				<?php jmd_magandanow_new_loop_meta(); ?>
			</div>
		</header>
		<div class="mh-loop-excerpt">
			<?php the_excerpt(); ?>
		</div>
	</div>
</article>