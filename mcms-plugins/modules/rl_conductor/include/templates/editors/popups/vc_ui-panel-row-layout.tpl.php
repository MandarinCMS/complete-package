<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
?>
<div class="vc_ui-font-open-sans vc_ui-panel-window vc_media-xs vc_ui-panel" data-vc-panel=".vc_ui-panel-header-header" data-vc-ui-element="panel-row-layout" id="vc_ui-panel-row-layout">
	<div class="vc_ui-panel-window-inner">
		<?php vc_include_template('editors/popups/vc_ui-header.tpl.php', array(
			'title' => __( 'Row Layout', 'rl_conductor' ),
			'controls' => array( 'minimize', 'close' ),
			'header_css_class' => 'vc_ui-row-layout-header-container',
		)); ?>
		<div class="vc_ui-panel-content-container">
			<div class="vc_ui-panel-content vc_properties-list vc_edit_form_elements" data-vc-ui-element="panel-content">
				<div class="vc_row vc_ui-flex-row">
					<div class="vc_col-sm-12 vc_column vc_layout-panel-switcher">
						<div class="mcmsb_element_label"><?php _e( 'Row layout', 'rl_conductor' ) ?></div>
						<?php foreach ( $vc_row_layouts as $layout ) :  ?>
							<a data-vc-ui-element="button-layout" class="vc_layout-btn" <?php echo 'data-cells="' . $layout['cells']
							                                   . '" data-cells-mask="' . $layout['mask']
							                                   . '" title="' . $layout['title'] ?>">
								<i class="vc-composer-icon vc-c-icon-<?php echo $layout['icon_class'] ?>"></i>
							</a>
						<?php endforeach ?>
						<span
							class="vc_description vc_clearfix"><?php _e( 'Select row layout from predefined options.', 'rl_conductor' ); ?></span>
					</div>
					<div class="vc_col-sm-12 vc_column">
						<div class="mcmsb_element_label"><?php _e( 'Enter custom layout for your row', 'rl_conductor' ) ?></div>
						<div class="edit_form_line">
							<input name="padding" class="mcmsb-textinput vc_row_layout" type="text" value="" id="vc_row-layout">
							<span class="vc_general vc_ui-button vc_ui-button-size-sm vc_ui-button-action vc_ui-button-shape-rounded vc_ui-button-update-layout" data-vc-ui-element="button-update-layout"><?php _e( 'Update', 'rl_conductor' ) ?></span>
					<span
						class="vc_description vc_clearfix"><?php _e( 'Change particular row layout manually by specifying number of columns and their size value.', 'rl_conductor' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
