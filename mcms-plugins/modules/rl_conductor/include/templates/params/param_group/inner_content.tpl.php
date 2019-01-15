<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
return '
<div class="vc_controls vc_controls-row vc_clearfix vc_param_group-controls">
	<a class="vc_control column_move vc_move-param" href="#" title="' . __( 'Drag row to reorder', 'rl_conductor' ) . '" data-vc-control="move"><i class="vc-composer-icon vc-c-icon-dragndrop"></i></a>
	<span class="vc_param-group-admin-labels"></span>
	<span class="vc_row_edit_clone_delete">
		<a class="vc_control column_delete vc_delete-param" href="#" title="' . __( 'Delete this param', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-delete_empty"></i></a>
		<a class="vc_control column_clone" href="#" title="' . __( 'Clone this row', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-content_copy"></i></a>
		<a class="vc_control column_toggle" href="#" title="' . __( 'Toggle row', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-arrow_drop_down"></i></a>
	</span>
</div>
<div class="mcmsb_element_wrapper">
	<div class="vc_row vc_row-fluid mcmsb_row_container">
		<div class="mcmsb_vc_column mcmsb_sortable vc_col-sm-12 mcmsb_content_holder vc_empty-column">
			<div class="mcmsb_element_wrapper">
				<div class="vc_fields vc_clearfix">
					%content%
				</div>
			</div>
		</div>
	</div>
</div>';
