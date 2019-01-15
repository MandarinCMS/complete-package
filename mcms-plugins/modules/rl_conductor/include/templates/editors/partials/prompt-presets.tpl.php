<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

?>
<form class="vc_ui-prompt vc_ui-prompt-presets">
	<div class="vc_ui-prompt-controls">
		<button type="button" class="vc_general vc_ui-control-button vc_ui-prompt-close">
			<i class="vc-composer-icon vc-c-icon-close"></i>
		</button>
	</div>
	<div class="vc_ui-prompt-title">
		<label for="prompt_title" class="mcmsb_element_label"><?php _e( 'Preset Title', 'rl_conductor' ) ?></label>
	</div>
	<div class="vc_ui-prompt-content">
		<div class="vc_ui-prompt-column">
			<div class="mcmsb_el_type_textfield vc_wrapper-param-type-textfield vc_properties-list">
				<div class="edit_form_line">
					<input name="title" id="prompt_title" class="mcmsb_vc_param_value mcmsb-textinput h4 textfield"
						type="text" value="" data-vc-disable-empty="#vc_ui-save-preset-btn">
					<span
						class="vc_description vc_clearfix"><?php _e( 'Enter element preset title.', 'rl_conductor' ) ?></span>
				</div>
			</div>
		</div>
		<div class="vc_ui-prompt-column">
			<button type="buttom"
				class="vc_general vc_ui-button vc_ui-button-size-sm vc_ui-button-action vc_ui-button-shape-rounded" id="vc_ui-save-preset-btn" disabled><?php _e( 'Save changes', 'rl_conductor' ) ?></button>
		</div>
	</div>
</form>
