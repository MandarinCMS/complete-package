<?php get_header(); ?>
<div class="mh-wrapper mh-clearfix">
	<div id="main-content" class="mh-loop mh-content" role="main"><?php
		jmd_before_page_content();
		if (have_posts()) { ?>
			<header class="page-header">
				<h1 class="page-title">
					<?php printf(esc_html__('Search Results for %s', 'jmd-magandanow-new'), '<span>' . get_search_query() . '</span>'); ?>
				</h1>
			</header><?php
			jmd_magandanow_new_loop_layout();
			jmd_magandanow_new_pagination();
		} else {
			get_template_part('content', 'none');
		} ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>