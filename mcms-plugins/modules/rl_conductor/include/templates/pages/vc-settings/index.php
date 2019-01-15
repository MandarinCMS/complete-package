<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
?>
<div class="wrap vc_settings" id="mcmsb-js-composer-settings">
	<h2><?php _e( 'RazorLeaf Conductor Settings', 'rl_conductor' ); ?></h2>
	<?php settings_errors(); ?>
	<?php vc_include_template( '/pages/partials/_settings_tabs.php',
	array(
			'active_tab' => $active_page->getSlug(),
			'tabs' => $pages,
		) );
	?>
	<?php $active_page->render(); ?>
</div>
