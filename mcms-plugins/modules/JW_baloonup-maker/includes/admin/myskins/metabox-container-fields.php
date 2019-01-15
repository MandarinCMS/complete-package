<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_description', 0);
function balooncreate_baloonup_myskin_container_meta_box_field_description( $baloonup_myskin_id ) { ?>
	</tbody></table><p><?php _e( 'mySkin the container inside the baloonups.', 'baloonup-maker' ); ?></p><table class="form-table"><tbody><?php
}

add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_padding', 10);
function balooncreate_baloonup_myskin_container_meta_box_field_padding( $baloonup_myskin_id ) { ?>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_padding"><?php _e( 'Padding', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'padding' ))?>"
				name="baloonup_myskin_container_padding"
				id="baloonup_myskin_container_padding"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_step_container_padding', 1));?>"
				min="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_min_container_padding', 0));?>"
				max="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_max_container_padding', 100));?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr><?php
}


add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_background', 20);
function balooncreate_baloonup_myskin_container_meta_box_field_background( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2">
			<h3 class="title"><?php _e( 'Background', 'baloonup-maker' );?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_background_color"><?php _e( 'Color', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_container_background_color" id="baloonup_myskin_container_background_color" value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'background_color' ))?>" class="pum-color-picker background-color" />
		</td>
	</tr>
	<tr class="background-opacity">
		<th scope="row">
			<label for="baloonup_myskin_container_background_opacity"><?php _e( 'Opacity', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'background_opacity' ))?>"
				name="baloonup_myskin_container_background_opacity"
				id="baloonup_myskin_container_background_opacity"
				class="pum-range-manual balooncreate-range-manual"
				step="1"
				min="0"
				max="100"
				data-force-minmax=true
			/>
			<span class="range-value-unit regular-text">%</span>
		</td>
	</tr><?php
}

add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_atb_extension_promotion', 30);
function balooncreate_baloonup_myskin_container_meta_box_field_atb_extension_promotion( $baloonup_myskin_id ) { ?>
	<tr>
		<th colspan="2" class="pum-upgrade-tip">
			<img style="" src="<?php echo POPMAKE_URL;?>/assets/images/upsell-icon-advanted-myskin-builder.png"/> <?php _e( 'Want to use background images?', 'baloonup-maker' ); ?> <a href="https://mcmsbaloonupmaker.com/extensions/advanced-myskin-builder/?utm_source=module-myskin-editor&utm_medium=text-link&utm_campaign=Upsell&utm_content=container-settings" target="_blank"><?php _e( 'Check out Advanced mySkin Builder!', 'baloonup-maker' ); ?></a>.
		</th>
	</tr><?php
}

add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_border', 40);
function balooncreate_baloonup_myskin_container_meta_box_field_border( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Border', 'baloonup-maker' );?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_border_radius"><?php _e( 'Radius', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'border_radius' ))?>"
				name="baloonup_myskin_container_border_radius"
				id="baloonup_myskin_container_border_radius"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_step_container_border_radius', 1));?>"
				min="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_min_container_border_radius', 0));?>"
				max="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_max_container_border_radius', 80));?>"
			/>
			<span class="range-value-unit regular-text">px</span>
			<p class="description"><?php _e('Choose a corner radius for your container button.', 'baloonup-maker' )?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_border_style"><?php _e( 'Style', 'baloonup-maker' );?></label>
		</th>
		<td>
			<select name="baloonup_myskin_container_border_style" id="baloonup_myskin_container_border_style" class="border-style">
			<?php foreach(apply_filters('balooncreate_border_style_options', array()) as $option => $value) : ?>
				<option
					value="<?php echo $value;?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'border_style') ? ' selected="selected"' : '';?>
				><?php echo $option;?></option>
			<?php endforeach ?>
			</select>
			<p class="description"><?php _e( 'Choose a border style for your container button.', 'baloonup-maker' )?></p>
		</td>
	</tr>
	<tr class="border-options">
		<th scope="row">
			<label for="baloonup_myskin_container_border_color"><?php _e( 'Color', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_container_border_color" id="baloonup_myskin_container_border_color" value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'border_color'))?>" class="pum-color-picker" />
		</td>
	</tr>
	<tr class="border-options">
		<th scope="row">
			<label for="baloonup_myskin_container_border_width"><?php _e( 'Thickness', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'border_width' ))?>"
				name="baloonup_myskin_container_border_width"
				id="baloonup_myskin_container_border_width"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_step_container_border_width', 1));?>"
				min="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_min_container_border_width', 0));?>"
				max="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_max_container_border_width', 5));?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr><?php
}

add_action('balooncreate_baloonup_myskin_container_meta_box_fields', 'balooncreate_baloonup_myskin_container_meta_box_field_boxshadow', 50);
function balooncreate_baloonup_myskin_container_meta_box_field_boxshadow( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Drop Shadow', 'baloonup-maker' );?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_inset"><?php _e( 'Inset', 'baloonup-maker' );?></label>
		</th>
		<td>
			<select name="baloonup_myskin_container_boxshadow_inset" id="baloonup_myskin_container_boxshadow_inset">
			<?php foreach(array(
				__('No', 'baloonup-maker' ) => 'no',
				__('Yes', 'baloonup-maker' ) => 'yes'
			) as $option => $value) : ?>
				<option
					value="<?php echo $value;?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_inset') ? ' selected="selected"' : '';?>
				><?php echo $option;?></option>
			<?php endforeach ?>
			</select>
			<p class="description"><?php _e( 'Set the box shadow to inset (inner shadow).', 'baloonup-maker' )?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_horizontal"><?php _e( 'Horizontal Position', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_horizontal' ))?>"
				name="baloonup_myskin_container_boxshadow_horizontal"
				id="baloonup_myskin_container_boxshadow_horizontal"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_step_container_boxshadow_horizontal', 1));?>"
				min="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_min_container_boxshadow_horizontal', -50));?>"
				max="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_max_container_boxshadow_horizontal', 50));?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_vertical"><?php _e( 'Vertical Position', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_vertical' ))?>"
				name="baloonup_myskin_container_boxshadow_vertical"
				id="baloonup_myskin_container_boxshadow_vertical"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_step_container_boxshadow_vertical', 1));?>"
				min="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_min_container_boxshadow_vertical', -50));?>"
				max="<?php esc_html_e(apply_filters('balooncreate_baloonup_myskin_max_container_boxshadow_vertical', 50));?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_blur"><?php _e( 'Blur Radius', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_blur' ) ); ?>"
				name="baloonup_myskin_container_boxshadow_blur"
				id="baloonup_myskin_container_boxshadow_blur"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_container_boxshadow_blur', 1 ) );?>"
				min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_container_boxshadow_blur', 0 ) );?>"
				max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_container_boxshadow_blur', 100 ) );?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_spread"><?php _e( 'Spread', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_spread' ) ); ?>"
				name="baloonup_myskin_container_boxshadow_spread"
				id="baloonup_myskin_container_boxshadow_spread"
				class="pum-range-manual balooncreate-range-manual"
				step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_container_boxshadow_spread', 1 ) );?>"
				min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_container_boxshadow_spread', -100 ) );?>"
				max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_container_boxshadow_spread', 100 ) );?>"
			/>
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_color"><?php _e( 'Color', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_container_boxshadow_color" id="baloonup_myskin_container_boxshadow_color" value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_color'))?>" class="pum-color-picker boxshadow-color" />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_container_boxshadow_opacity"><?php _e( 'Opacity', 'baloonup-maker' );?></label> 
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id, 'boxshadow_opacity' ) ); ?>"
				name="baloonup_myskin_container_boxshadow_opacity"
				id="baloonup_myskin_container_boxshadow_opacity"
				class="pum-range-manual balooncreate-range-manual"
				step="1"
				min="0"
				max="100"
				data-force-minmax=true
			/>
			<span class="range-value-unit regular-text">%</span>
		</td>
	</tr><?php
}
