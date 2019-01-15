<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_description', 0 );
function balooncreate_baloonup_myskin_close_meta_box_field_description( $baloonup_myskin_id ) { ?>
	</tbody></table>
	<p><?php _e( 'mySkin the close button for the baloonups.', 'baloonup-maker' ); ?></p><table class="form-table"><tbody><?php
}

add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_text', 10 );
function balooncreate_baloonup_myskin_close_meta_box_field_text( $baloonup_myskin_id ) { ?>
	<tr>
	<th scope="row">
		<label for="baloonup_myskin_close_text"><?php _e( 'Text', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" name="baloonup_myskin_close_text" id="baloonup_myskin_close_text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'text' ) ) ?>" />
		<p class="description"><?php _e( 'Enter the close button text.', 'baloonup-maker' ) ?></p>
	</td>
	</tr><?php
}

add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_fi_extension_promotion', 10 );
function balooncreate_baloonup_myskin_close_meta_box_field_fi_extension_promotion( $baloonup_myskin_id ) {
	if ( ! class_exists( 'BaloonUp_Maker_Forced_Interaction' ) && ! class_exists( 'PUM_Forced_Interaction' ) ) :
		?>
		<tr>
		<th colspan="2" class="pum-upgrade-tip">
			<img style="" src="<?php echo POPMAKE_URL; ?>/assets/images/upsell-icon-forced-interaction.png" />
			<?php printf(
				_x( 'Want to disable the close button? Check out %sForced Interaction%s!', '%s represent the opening & closing link html', 'baloonup-maker' ),
				'<a href="https://mcmsbaloonupmaker.com/extensions/forced-interaction/?utm_source=module-myskin-editor&utm_medium=text-link&utm_campaign=Upsell&utm_content=close-button-settings" target="_blank">',
				'</a>'
			); ?>
		</th>
		</tr><?php
	endif;
}

add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_padding', 20 );
function balooncreate_baloonup_myskin_close_meta_box_field_padding( $baloonup_myskin_id ) { ?>
	<tr>
	<th scope="row">
		<label for="baloonup_myskin_close_padding"><?php _e( 'Padding', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'padding' ) ) ?>" name="baloonup_myskin_close_padding" id="baloonup_myskin_close_padding" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_padding', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_padding', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_padding', 100 ) ); ?>" />
		<span class="range-value-unit regular-text">px</span>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_size', 30 );
function balooncreate_baloonup_myskin_close_meta_box_field_size( $baloonup_myskin_id ) { ?>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_height"><?php _e( 'Height', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'height' ) ) ?>" name="baloonup_myskin_close_height" id="baloonup_myskin_close_height" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_height', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_height', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_height', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
	<th scope="row">
		<label for="baloonup_myskin_close_width"><?php _e( 'Width', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'width' ) ) ?>" name="baloonup_myskin_close_width" id="baloonup_myskin_close_width" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_width', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_width', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_width', 100 ) ); ?>" />
		<span class="range-value-unit regular-text">px</span>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_location', 40 );
function balooncreate_baloonup_myskin_close_meta_box_field_location( $baloonup_myskin_id ) { ?>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_location"><?php _e( 'Location', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<select name="baloonup_myskin_close_location" id="baloonup_myskin_close_location">
				<?php foreach ( apply_filters( 'balooncreate_myskin_close_location_options', array() ) as $option => $value ) : ?>
					<option value="<?php echo $value; ?>"
						<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'location' ) ? ' selected="selected"' : ''; ?>
					><?php echo $option; ?></option>
				<?php endforeach ?>
			</select>
			<p class="description"><?php _e( 'Choose which corner the close button will be positioned.', 'baloonup-maker' ) ?></p>
		</td>
	</tr>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Position', 'baloonup-maker' ); ?></h3></th>
	</tr>
	<tr class="topright topleft">
		<th scope="row">
			<label for="baloonup_myskin_close_position_top"><?php _e( 'Top', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'position_top' ) ) ?>" name="baloonup_myskin_close_position_top" id="baloonup_myskin_close_position_top" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_position_offset', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_position_offset', - 100 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_position_offset', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr class="topleft bottomleft">
		<th scope="row">
			<label for="baloonup_myskin_close_position_left"><?php _e( 'Left', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'position_left' ) ) ?>" name="baloonup_myskin_close_position_left" id="baloonup_myskin_close_position_left" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_position_offset', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_position_offset', - 100 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_position_offset', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr class="bottomleft bottomright">
		<th scope="row">
			<label for="baloonup_myskin_close_position_bottom"><?php _e( 'Bottom', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'position_bottom' ) ) ?>" name="baloonup_myskin_close_position_bottom" id="baloonup_myskin_close_position_bottom" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_position_offset', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_position_offset', - 100 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_position_offset', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr class="topright bottomright">
	<th scope="row">
		<label for="baloonup_myskin_close_position_right"><?php _e( 'Right', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'position_right' ) ) ?>" name="baloonup_myskin_close_position_right" id="baloonup_myskin_close_position_right" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_position_offset', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_position_offset', - 100 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_position_offset', 100 ) ); ?>" />
		<span class="range-value-unit regular-text">px</span>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_font', 50 );
function balooncreate_baloonup_myskin_close_meta_box_field_font( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Font', 'baloonup-maker' ); ?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_font_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_close_font_color" id="baloonup_myskin_close_font_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'font_color' ) ) ?>" class="pum-color-picker" />
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_font_size"><?php _e( 'Size', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'font_size' ) ) ?>" name="baloonup_myskin_close_font_size" id="baloonup_myskin_close_font_size" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_font_size', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_font_size', 8 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_font_size', 32 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_line_height"><?php _e( 'Line Height', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'line_height' ) ) ?>" name="baloonup_myskin_close_line_height" id="baloonup_myskin_close_line_height" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_line_height', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_line_height', 8 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_line_height', 32 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_font_family"><?php _e( 'Family', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<select name="baloonup_myskin_close_font_family" id="baloonup_myskin_close_font_family" class="font-family">
				<?php foreach ( apply_filters( 'balooncreate_font_family_options', array() ) as $option => $value ) : ?>
					<option value="<?php echo $value; ?>"
						<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'font_family' ) ? ' selected="selected"' : ''; ?>
						<?php echo $value == '' ? ' class="bold"' : ''; ?>
					><?php echo $option; ?></option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_font_weight"><?php _e( 'Weight', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<select name="baloonup_myskin_close_font_weight" id="baloonup_myskin_close_font_weight" class="font-weight">
				<?php foreach ( apply_filters( 'balooncreate_font_weight_options', array() ) as $option => $value ) : ?>
					<option value="<?php echo $value; ?>"
						<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'font_weight' ) ? ' selected="selected"' : ''; ?>
						<?php echo $value == '' ? ' class="bold"' : ''; ?>
					><?php echo $option; ?></option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
	<th scope="row font-style-only">
		<label for="baloonup_myskin_close_font_style"><?php _e( 'Style', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<select name="baloonup_myskin_close_font_style" id="baloonup_myskin_close_font_style" class="font-style">
			<?php foreach ( apply_filters( 'balooncreate_font_style_options', array() ) as $option => $value ) : ?>
				<option value="<?php echo $value; ?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'font_style' ) ? ' selected="selected"' : ''; ?>
					<?php echo $value == '' ? ' class="bold"' : ''; ?>
				><?php echo $option; ?></option>
			<?php endforeach ?>
		</select>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_background', 60 );
function balooncreate_baloonup_myskin_close_meta_box_field_background( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Background', 'baloonup-maker' ); ?></ h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_background_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_close_background_color" id="baloonup_myskin_close_background_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'background_color' ) ) ?>" class="pum-color-picker background-color" />
		</td>
	</tr>
	<tr class="background-opacity">
	<th scope="row">
		<label for="baloonup_myskin_close_background_opacity"><?php _e( 'Opacity', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'background_opacity' ) ) ?>" name="baloonup_myskin_close_background_opacity" id="baloonup_myskin_close_background_opacity" class="pum-range-manual balooncreate-range-manual" step="1" min="0" max="100" data-force-minmax=true />
		<span class="range-value-unit regular-text">%</span>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_atb_extension_promotion', 70 );
function balooncreate_baloonup_myskin_close_meta_box_field_atb_extension_promotion( $baloonup_myskin_id ) { ?>
	<tr>
	<th colspan="2" class="pum-upgrade-tip">
		<img style="" src="<?php echo POPMAKE_URL; ?>/assets/images/upsell-icon-advanted-myskin-builder.png" /> <?php _e( 'Want to use background images?', 'baloonup-maker' ); ?>
		<a href="https://mcmsbaloonupmaker.com/extensions/advanced-myskin-builder/?utm_source=module-myskin-editor&utm_medium=text-link&utm_campaign=Upsell&utm_content=close-button-settings" target="_blank"><?php _e( 'Check out Advanced mySkin Builder!', 'baloonup-maker' ); ?></a>.
	</th>
	</tr><?php
}

add_action('balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_border', 80);
function balooncreate_baloonup_myskin_close_meta_box_field_border( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2">

			<h3 class="title"><?php _e( 'Border', 'baloonup-maker' ); ?></h3>
			<p
		</th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_border_radius"><?php _e( 'Radius', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'border_radius' ) ) ?>" name="baloonup_myskin_close_border_radius" id="baloonup_myskin_close_border_radius" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_border_radius', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_border_radius', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_border_radius', 28 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
			<p class="description"><?php _e( 'Choose a corner radius for your close button.', POPMAKE_SLUG ) ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_border_style"><?php _e( 'Style', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<select name="baloonup_myskin_close_border_style" id="baloonup_myskin_close_border_style" class="border-style">
				<?php foreach ( apply_filters( 'balooncreate_border_style_options', array() ) as $option => $value ) : ?>
					<option value="<?php echo $value; ?>"
						<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'border_style' ) ? ' selected="selected"' : ''; ?>
					><?php echo $option; ?></option>
				<?php endforeach ?>
			</select>
			<p class="description"><?php _e( 'Choose a border style for your close button.', 'baloonup-maker' ) ?></p>
		</td>
	</tr>
	<tr class="border-options">
		<th scope="row">
			<label for="baloonup_myskin_close_border_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_close_border_color" id="baloonup_myskin_close_border_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'border_color' ) ) ?>" class="pum-color-picker" />
		</td>
	</tr>
	<tr class="border-options">
	<th scope="row">
		<label for="baloonup_myskin_close_border_width"><?php _e( 'Thickness', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'border_width' ) ) ?>" name="baloonup_myskin_close_border_width" id="baloonup_myskin_close_border_width" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_border_width', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_border_width', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_border_width', 10 ) ); ?>" />
		<span class="range-value-unit regular-text">px</span>
	</td>
	</tr><?php
}


add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_boxshadow', 90 );
function balooncreate_baloonup_myskin_close_meta_box_field_boxshadow( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Drop Shadow', 'baloonup-maker' ); ?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_inset"><?php _e( 'Inset', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<select name="baloonup_myskin_close_boxshadow_inset" id="baloonup_myskin_close_boxshadow_inset">
				<?php foreach (
					array(
						__( 'No', 'baloonup-maker' )  => 'no',
						__( 'Yes', 'baloonup-maker' ) => 'yes',
					) as $option => $value
				) : ?>
					<option value="<?php echo $value; ?>"
						<?php echo $value == balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_inset' ) ? ' selected="selected"' : ''; ?>
					><?php echo $option; ?></option>
				<?php endforeach ?>
			</select>
			<p class="description"><?php _e( 'Set the box shadow to inset (inner shadow).', 'baloonup-maker' ) ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_horizontal"><?php _e( 'Horizontal Position', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_horizontal' ) ) ?>" name="baloonup_myskin_close_boxshadow_horizontal" id="baloonup_myskin_close_boxshadow_horizontal" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_boxshadow_horizontal', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_boxshadow_horizontal', - 50 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_boxshadow_horizontal', 50 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_vertical"><?php _e( 'Vertical Position', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_vertical' ) ) ?>" name="baloonup_myskin_close_boxshadow_vertical" id="baloonup_myskin_close_boxshadow_vertical" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_boxshadow_vertical', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_boxshadow_vertical', - 50 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_boxshadow_vertical', 50 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_blur"><?php _e( 'Blur Radius', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_blur' ) ) ?>" name="baloonup_myskin_close_boxshadow_blur" id="baloonup_myskin_close_boxshadow_blur" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_boxshadow_blur', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_boxshadow_blur', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_boxshadow_blur', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_spread"><?php _e( 'Spread', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_spread' ) ) ?>" name="baloonup_myskin_close_boxshadow_spread" id="baloonup_myskin_close_boxshadow_spread" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_boxshadow_spread', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_boxshadow_spread', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_boxshadow_spread', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_boxshadow_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_close_boxshadow_color" id="baloonup_myskin_close_boxshadow_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_color' ) ) ?>" class="pum-color-picker boxshadow-color" />
		</td>
	</tr>
	<tr>
	<th scope="row">
		<label for="baloonup_myskin_close_boxshadow_opacity"><?php _e( 'Opacity', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'boxshadow_opacity' ) ) ?>" name="baloonup_myskin_close_boxshadow_opacity" id="baloonup_myskin_close_boxshadow_opacity" class="pum-range-manual balooncreate-range-manual" step="1" min="0" max="100" data-force-minmax=true />
		<span class="range-value-unit regular-text">%</span>
	</td>
	</tr><?php
}

add_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', 'balooncreate_baloonup_myskin_close_meta_box_field_textshadow', 100 );
function balooncreate_baloonup_myskin_close_meta_box_field_textshadow( $baloonup_myskin_id ) { ?>
	<tr class="title-divider">
		<th colspan="2"><h3 class="title"><?php _e( 'Text Shadow', 'baloonup-maker' ); ?></h3></th>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_textshadow_horizontal"><?php _e( 'Horizontal Position', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'textshadow_horizontal' ) ) ?>" name="baloonup_myskin_close_textshadow_horizontal" id="baloonup_myskin_close_textshadow_horizontal" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_textshadow_horizontal', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_textshadow_horizontal', - 50 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_textshadow_horizontal', 50 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_textshadow_vertical"><?php _e( 'Vertical Position', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'textshadow_vertical' ) ) ?>" name="baloonup_myskin_close_textshadow_vertical" id="baloonup_myskin_close_textshadow_vertical" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_textshadow_vertical', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_textshadow_vertical', - 50 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_textshadow_vertical', 50 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_textshadow_blur"><?php _e( 'Blur Radius', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'textshadow_blur' ) ) ?>" name="baloonup_myskin_close_textshadow_blur" id="baloonup_myskin_close_textshadow_blur" class="pum-range-manual balooncreate-range-manual" step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_close_textshadow_blur', 1 ) ); ?>" min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_close_textshadow_blur', 0 ) ); ?>" max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_close_textshadow_blur', 100 ) ); ?>" />
			<span class="range-value-unit regular-text">px</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_close_textshadow_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_close_textshadow_color" id="baloonup_myskin_close_textshadow_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'textshadow_color' ) ) ?>" class="pum-color-picker textshadow-color" />
		</td>
	</tr>
	<tr>
	<th scope="row">
		<label for="baloonup_myskin_close_textshadow_opacity"><?php _e( 'Opacity', 'baloonup-maker' ); ?></label>
	</th>
	<td>
		<input type="text" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id, 'textshadow_opacity' ) ) ?>" name="baloonup_myskin_close_textshadow_opacity" id="baloonup_myskin_close_textshadow_opacity" class="pum-range-manual balooncreate-range-manual" step="1" min="0" max="100" data-force-minmax=true />
		<span class="range-value-unit regular-text">%</span>
	</td>
	</tr><?php
}