<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

?>
<div class="vc_ui-dropdown" data-vc-ui-element="settings-dropdown" data-vc-action="dropdown"
	data-vc-content=".vc_ui-dropdown-content">
	<div class="vc_ui-dropdown-trigger">
		<button class="vc_general vc_ui-control-button vc_ui-settings-button" type="button" title="<?php _e( 'Element Settings', 'rl_conductor' ); ?>"
			data-vc-ui-element="settings-dropdown-button"
			data-vc-accordion
			data-vc-container=".vc_ui-dropdown" data-vc-target=".vc_ui-dropdown">
			<i class="vc-composer-icon vc-c-icon-cog"> </i>
			<i class="vc-composer-icon vc-c-icon-check"> </i>
		</button>
	</div>
	<div class="vc_ui-dropdown-content" data-vc-ui-element="settings-dropdown-list">
	</div>
</div>
