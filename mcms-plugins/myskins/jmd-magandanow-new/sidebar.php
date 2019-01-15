<aside class="mh-widget-col-1 mh-sidebar" itemscope="itemscope" itemtype="http://schema.org/MCMSSideBar"><?php
	if (is_active_sidebar('sidebar')) {
		dynamic_sidebar('sidebar');
	} else { ?>
		<div class="mh-widget mh-sidebar-empty">
			<h4 class="mh-widget-title">
				<span class="mh-widget-title-inner">
					<?php esc_html_e('Sidebar', 'jmd-magandanow-new') ?>
				</span>
			</h4>
			<div class="textwidget">
				<?php printf(esc_html__('Please navigate to %1$1s in your MandarinCMS dashboard and add some widgets into the %2$2s widget area.', 'jmd-magandanow-new'), '<strong>' . esc_html__('Appearance &#8594; Widgets', 'jmd-magandanow-new') . '</strong>', '<em>' . esc_html__('Sidebar', 'jmd-magandanow-new') . '</em>'); ?>
			</div>
		</div><?php
	} ?>
</aside>