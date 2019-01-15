<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function balooncreate_baloonup_myskin_overlay_meta_box_field_description( $baloonup_myskin_id ) { ?>
	</tbody></table><p><?php _e( 'mySkin the overlay behind the baloonups.', 'baloonup-maker' ); ?></p><table class="form-table"><tbody><?php
}
add_action('balooncreate_baloonup_myskin_overlay_meta_box_fields', 'balooncreate_baloonup_myskin_overlay_meta_box_field_description', 0);

function balooncreate_baloonup_myskin_overlay_meta_box_field_background( $baloonup_myskin_id ) { ?>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_overlay_background_color"><?php _e( 'Color', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_overlay_background_color" id="baloonup_myskin_overlay_background_color" value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_overlay( $baloonup_myskin_id, 'background_color'))?>" class="pum-color-picker background-color" />
			<p class="description"><?php _e( 'Choose the overlay color.', 'baloonup-maker' )?></p>
		</td>
	</tr>
	<tr class="background-opacity">
		<th scope="row">
			<label for="baloonup_myskin_overlay_background_opacity"><?php _e( 'Opacity', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text"
				value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_overlay( $baloonup_myskin_id, 'background_opacity' ))?>"
				name="baloonup_myskin_overlay_background_opacity"
				id="baloonup_myskin_overlay_background_opacity"
				class="pum-range-manual balooncreate-range-manual"
				step="1"
				min="0"
				max="100"
				data-force-minmax=true
			/>
			<span class="range-value-unit regular-text">%</span>
			<p class="description"><?php _e('The opacity value for the overlay.',POPMAKE_SLUG)?></p>
		</td>
	</tr><?php
}
add_action('balooncreate_baloonup_myskin_overlay_meta_box_fields', 'balooncreate_baloonup_myskin_overlay_meta_box_field_background', 10);

function balooncreate_baloonup_myskin_overlay_meta_box_field_atb_extension_promotion( $baloonup_myskin_id ) { ?>
	<tr>
		<th colspan="2" class="pum-upgrade-tip">
			<img style="" src="<?php echo POPMAKE_URL;?>/assets/images/upsell-icon-advanted-myskin-builder.png"/> <?php _e( 'Want to use background images?', 'baloonup-maker' ); ?> <a href="https://mcmsbaloonupmaker.com/extensions/advanced-myskin-builder/?utm_source=module-myskin-editor&utm_medium=text-link&utm_campaign=Upsell&utm_content=overlay-settings" target="_blank"><?php _e( 'Check out Advanced mySkin Builder!', 'baloonup-maker' ); ?></a>.
		</th>
	</tr><?php
}
add_action('balooncreate_baloonup_myskin_overlay_meta_box_fields', 'balooncreate_baloonup_myskin_overlay_meta_box_field_atb_extension_promotion', 20);