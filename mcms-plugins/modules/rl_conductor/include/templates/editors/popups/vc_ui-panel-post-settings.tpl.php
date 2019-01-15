<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
?>
<div class="vc_ui-font-open-sans vc_ui-panel-window vc_media-xs vc_ui-panel" data-vc-panel=".vc_ui-panel-header-header" data-vc-ui-element="panel-post-settings" id="vc_ui-panel-post-settings">
	<div class="vc_ui-panel-window-inner">
		<?php vc_include_template('editors/popups/vc_ui-header.tpl.php', array(
			'title' => __( 'Page Settings', 'rl_conductor' ),
			'controls' => array( 'minimize', 'close' ),
			'header_css_class' => 'vc_ui-post-settings-header-container',
			'content_template' => '',
		)); ?>
		<div class="vc_ui-panel-content-container">
			<div class="vc_ui-panel-content vc_properties-list vc_edit_form_elements" data-vc-ui-element="panel-content">
				<div class="vc_row">
					<div class="vc_col-sm-12 vc_column" id="vc_settings-title-container">
						<div class="mcmsb_element_label"><?php _e( 'Page title', 'rl_conductor' ) ?></div>
						<div class="edit_form_line">
							<input name="page_title" class="mcmsb-textinput vc_title_name" type="text" value=""
							       id="vc_page-title-field"
							       placeholder="<?php _e( 'Please enter page title', 'rl_conductor' ) ?>">
					<span
						class="vc_description"><?php printf( __( 'Change title of the current %s (Note: changes may not be displayed in a preview, but will take effect after saving page).', 'rl_conductor' ), get_post_type() ); ?></span>
						</div>
					</div>
					<div class="vc_col-sm-12 vc_column">
						<div class="mcmsb_element_label"><?php _e( 'Custom CSS settings', 'rl_conductor' ) ?></div>
						<div class="edit_form_line">
							<pre id="mcmsb_csseditor" class="mcmsb_content_element custom_css mcmsb_frontend"></pre>
					<span
						class="vc_description vc_clearfix"><?php _e( 'Enter custom CSS (Note: it will be outputted only on this particular page).', 'rl_conductor' ) ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- param window footer-->
		<?php vc_include_template('editors/popups/vc_ui-footer.tpl.php', array(
			'controls' => array(
				array(
					'name' => 'close',
					'label' => __( 'Close', 'rl_conductor' ),
				),
				array(
					'name' => 'save',
					'label' => __( 'Save changes', 'rl_conductor' ),
					'css_classes' => 'vc_ui-button-fw',
					'style' => 'action',
				),
			),
		)); ?>
	</div>
</div>
