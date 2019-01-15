<?php jmd_before_footer(); ?>
<?php jmd_magandanow_new_footer_widgets(); ?>
<div class="mh-copyright-wrap">
	<div class="mh-container mh-container-inner mh-clearfix">
		<p class="mh-copyright"><?php printf(esc_html__('Copyright &copy; %1$s | MandarinCMS MySkin by %2$s', 'jmd-magandanow-new'), date("Y"), '<a href="' . esc_url('https://www.mhmyskins.com/') . '" rel="nofollow">MH MySkins</a>'); ?></p>
	</div>
</div>
<?php jmd_after_footer(); ?>
<?php mcms_footer(); ?>
</body>
</html>