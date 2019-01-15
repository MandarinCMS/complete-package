<?php /* Template for displaying author box content */
$jmd_author_box_ID = get_the_author_meta('ID');
$username = get_the_author_meta('display_name', $jmd_author_box_ID);
$userposts = count_user_posts($jmd_author_box_ID); ?>
<div class="mh-author-box mh-clearfix">
	<figure class="mh-author-box-avatar">
		<?php echo get_avatar($jmd_author_box_ID, 90); ?>
	</figure>
	<div class="mh-author-box-header">
		<span class="mh-author-box-name">
			<?php printf(esc_html__('About %s', 'jmd-magandanow-new'), $username); ?>
		</span>
		<?php if (!is_author()) { ?>
			<span class="mh-author-box-postcount">
				<a href="<?php echo esc_url(get_author_posts_url($jmd_author_box_ID)); ?>" title="<?php printf(esc_html__('More articles written by %s', 'jmd-magandanow-new'), $username); ?>'">
					<?php printf(esc_html(_n('%s Article', '%s Articles', $userposts, 'jmd-magandanow-new')), $userposts); ?>
				</a>
			</span>
		<?php } ?>
	</div>
	<?php if (get_the_author_meta('description', $jmd_author_box_ID)) { ?>
		<div class="mh-author-box-bio">
			<?php echo mcms_kses_post(get_the_author_meta('description', $jmd_author_box_ID)); ?>
		</div>
	<?php } ?>
</div>