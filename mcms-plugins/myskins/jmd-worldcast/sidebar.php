<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-files/#template-partials
 *
 * @package JMD_MandarinCMS
 */
?>

<div class="col-md-3 col-md-pull-6">
	<aside id="sidebar-left" class="sidebar-wrap">
		<div class="sidebar-widget">
			<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-widget')) ?>
		</div>
	</aside>
</div><!-- #sidebar-left -->
