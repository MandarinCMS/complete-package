<?php /* Comments Template */
if (post_password_required()) {
	return;
}
if (have_comments()) {
	$comments_by_type = separate_comments($comments);
	if (!empty($comments_by_type['comment'])) {
		$comment_count = count($comments_by_type['comment']); ?>
		<div id="mh-comments" class="mh-comments-wrap">
			<h4 class="mh-widget-title">
				<span class="mh-widget-title-inner">
					<?php printf(esc_html(_n('%d Comment', '%d Comments', $comment_count, 'jmd-magandanow-new')), number_format_i18n($comment_count)); ?>
				</span>
			</h4>
			<ol class="commentlist mh-comment-list">
				<?php echo mcms_list_comments('callback=jmd_magandanow_new_comments&type=comment'); ?>
			</ol>
		</div><?php
	}
	if (get_comments_number() > get_option('comments_per_page')) { ?>
		<nav class="mh-comments-pagination">
			<?php paginate_comments_links(array('prev_text' => esc_html__('&laquo;', 'jmd-magandanow-new'), 'next_text' => esc_html__('&raquo;', 'jmd-magandanow-new'))); ?>
		</nav><?php
	}
	if (!empty($comments_by_type['pings'])) {
		$pings = $comments_by_type['pings'];
		$ping_count = count($comments_by_type['pings']); ?>
		<h4 class="mh-widget-title">
			<span class="mh-widget-title-inner">
				<?php printf(esc_html(_n('%d Trackback / Pingback', '%d Trackbacks / Pingbacks', $ping_count, 'jmd-magandanow-new')), number_format_i18n($ping_count)); ?>
			</span>
		</h4>
		<ol class="pinglist mh-ping-list">
        	<?php foreach ($pings as $ping) { ?>
				<li id="comment-<?php comment_ID() ?>" <?php comment_class('mh-ping-item'); ?>>
					<?php echo '<i class="fa fa-link"></i>' . get_comment_author_link($ping); ?>
				</li>
			<?php } ?>
        </ol><?php
	}
	if (!comments_open()) { ?>
		<p class="mh-no-comments">
			<?php esc_html_e('Comments are closed.', 'jmd-magandanow-new'); ?>
		</p><?php
	}
} else {
	if (comments_open()) { ?>
		<h4 id="mh-comments" class="mh-widget-title mh-comment-form-title">
			<span class="mh-widget-title-inner">
				<?php esc_html_e('Be the first to comment', 'jmd-magandanow-new'); ?>
			</span>
		</h4><?php
	}
}
if (comments_open()) {
	comment_form(array(
		'title_reply' => esc_html__('Leave a Reply', 'jmd-magandanow-new'),
        'comment_notes_before' => '<p class="comment-notes">' . esc_html__('Your email address will not be published.', 'jmd-magandanow-new') . '</p>',
        'comment_notes_after'  => '',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__('Comment', 'jmd-magandanow-new') . '</label><br/><textarea id="comment" name="comment" cols="45" rows="5" aria-required="true"></textarea></p>'
	));
}

?>