<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

?>
<div id="vc_multi-properties-panel" class="vc_panel">
	<div class="vc_panel-heading">
		<a title="<?php _e( 'Close panel', 'rl_conductor' ); ?>" href="#" class="vc_close" data-dismiss="panel"
		   aria-hidden="true"><i class="icon"></i></a>
		<a title="<?php _e( 'Hide panel', 'rl_conductor' ); ?>" href="#" class="vc_transparent" data-transparent="panel"
		   aria-hidden="true"><i class="icon"></i></a>

		<h3 class="vc_panel-title"><?php _e( 'Edit Elements', 'rl_conductor' ) ?></h3>
	</div>
	<div class="vc_panel-body vc_properties-list">
	</div>
	<div class="vc_panel-footer">
		<button type="button" class="vc_btn vc_panel-btn-close"
		        data-dismiss="panel"><?php _e( 'Close', 'rl_conductor' ) ?></button>
		<button type="button" class="vc_btn vc_panel-btn-save vc_save"
		        data-save="true"><?php _e( 'Save Changes', 'rl_conductor' ) ?></button>
	</div>
</div>
