<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_action( 'balooncreate_baloonup_myskin_title_meta_box_fields', 'balooncreate_baloonup_myskin_title_meta_box_field_description', 0 );
function balooncreate_baloonup_myskin_title_meta_box_field_description( $baloonup_myskin_id ) { ?>
</tbody></table><p><?php _e( 'mySkin the title of the baloonups.', 'baloonup-maker' ); ?></p>
<table class="form-table">
	<tbody><?php
		}

		add_action( 'balooncreate_baloonup_myskin_title_meta_box_fields', 'balooncreate_baloonup_myskin_title_meta_box_field_font', 10 );
		function balooncreate_baloonup_myskin_title_meta_box_field_font( $baloonup_myskin_id ) {
			?>
			<tr class="title-divider">
			<th colspan="2"><h3 class="title"><?php _e( 'Font', 'baloonup-maker' ); ?></h3></th>
			</tr>
			<tr>
				<th scope="row">
					<label for="baloonup_myskin_title_font_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<input type="text" name="baloonup_myskin_title_font_color" id="baloonup_myskin_title_font_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'font_color' ) ) ?>" class="pum-color-picker"/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="baloonup_myskin_title_font_size"><?php _e( 'Size', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<input type="text"
					       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'font_size' ) ) ?>"
					       name="baloonup_myskin_title_font_size"
					       id="baloonup_myskin_title_font_size"
					       class="pum-range-manual balooncreate-range-manual"
					       step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_title_font_size', 1 ) ); ?>"
					       min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_title_font_size', 8 ) ); ?>"
					       max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_title_font_size', 32 ) ); ?>"
						/>
					<span class="range-value-unit regular-text">px</span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="baloonup_myskin_title_line_height"><?php _e( 'Line Height', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<input type="text"
					       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'line_height' ) ) ?>"
					       name="baloonup_myskin_title_line_height"
					       id="baloonup_myskin_title_line_height"
					       class="pum-range-manual balooncreate-range-manual"
					       step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_title_line_height', 1 ) ); ?>"
					       min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_title_line_height', 8 ) ); ?>"
					       max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_title_line_height', 32 ) ); ?>"
						/>
					<span class="range-value-unit regular-text">px</span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="baloonup_myskin_title_font_family"><?php _e( 'Family', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<select name="baloonup_myskin_title_font_family" id="baloonup_myskin_title_font_family" class="font-family">
						<?php foreach ( apply_filters( 'balooncreate_font_family_options', array() ) as $option => $value ) : ?>
							<option
								value="<?php echo $value; ?>"
								<?php echo $value == balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'font_family' ) ? ' selected="selected"' : ''; ?>
								<?php echo $value == '' ? ' class="bold"' : ''; ?>
								><?php echo $option; ?></option>
						<?php endforeach ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row font-weight-only">
					<label for="baloonup_myskin_title_font_weight"><?php _e( 'Weight', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<select name="baloonup_myskin_title_font_weight" id="baloonup_myskin_title_font_weight" class="font-weight">
						<?php foreach ( apply_filters( 'balooncreate_font_weight_options', array() ) as $option => $value ) : ?>
							<option
								value="<?php echo $value; ?>"
								<?php echo $value == balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'font_weight' ) ? ' selected="selected"' : ''; ?>
								><?php echo $option; ?></option>
						<?php endforeach ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row font-style-only">
					<label for="baloonup_myskin_title_font_style"><?php _e( 'Style', 'baloonup-maker' ); ?></label>
				</th>
				<td>
					<select name="baloonup_myskin_title_font_style" id="baloonup_myskin_title_font_style" class="font-style">
						<?php foreach ( apply_filters( 'balooncreate_font_style_options', array() ) as $option => $value ) : ?>
							<option
								value="<?php echo $value; ?>"
								<?php echo $value == balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'font_style' ) ? ' selected="selected"' : ''; ?>
								><?php echo $option; ?></option>
						<?php endforeach ?>
					</select>
				</td>
			</tr>
			<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_text_align"><?php _e( 'Align', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<select name="baloonup_myskin_title_text_align" id="baloonup_myskin_title_text_align">
					<?php foreach ( apply_filters( 'balooncreate_text_align_options', array() ) as $option => $value ) : ?>
						<option
							value="<?php echo $value; ?>"
							<?php echo $value == balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'text_align' ) ? ' selected="selected"' : ''; ?>
							><?php echo $option; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			</tr><?php
		}

		add_action( 'balooncreate_baloonup_myskin_title_meta_box_fields', 'balooncreate_baloonup_myskin_title_meta_box_field_textshadow', 20 );
		function balooncreate_baloonup_myskin_title_meta_box_field_textshadow( $baloonup_myskin_id )
		{
		?>
		<tr class="title-divider">
			<th colspan="2"><h3 class="title"><?php _e( 'Text Shadow', 'baloonup-maker' ); ?></h3></th>
		</tr>
		<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_textshadow_horizontal"><?php _e( 'Horizontal Position', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<input type="text"
				       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'textshadow_horizontal' ) ) ?>"
				       name="baloonup_myskin_title_textshadow_horizontal"
				       id="baloonup_myskin_title_textshadow_horizontal"
				       class="pum-range-manual balooncreate-range-manual"
				       step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_title_textshadow_horizontal', 1 ) ); ?>"
				       min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_title_textshadow_horizontal', - 50 ) ); ?>"
				       max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_title_textshadow_horizontal', 50 ) ); ?>"
					/>
				<span class="range-value-unit regular-text">px</span>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_textshadow_vertical"><?php _e( 'Vertical Position', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<input type="text"
				       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'textshadow_vertical' ) ) ?>"
				       name="baloonup_myskin_title_textshadow_vertical"
				       id="baloonup_myskin_title_textshadow_vertical"
				       class="pum-range-manual balooncreate-range-manual"
				       step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_title_textshadow_vertical', 1 ) ); ?>"
				       min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_title_textshadow_vertical', - 50 ) ); ?>"
				       max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_title_textshadow_vertical', 50 ) ); ?>"
					/>
				<span class="range-value-unit regular-text">px</span>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_textshadow_blur"><?php _e( 'Blur Radius', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<input type="text"
				       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'textshadow_blur' ) ) ?>"
				       name="baloonup_myskin_title_textshadow_blur"
				       id="baloonup_myskin_title_textshadow_blur"
				       class="pum-range-manual balooncreate-range-manual"
				       step="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_step_title_textshadow_blur', 1 ) ); ?>"
				       min="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_min_title_textshadow_blur', 0 ) ); ?>"
				       max="<?php esc_html_e( apply_filters( 'balooncreate_baloonup_myskin_max_title_textshadow_blur', 100 ) ); ?>"
					/>
				<span class="range-value-unit regular-text">px</span>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_textshadow_color"><?php _e( 'Color', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<input type="text" name="baloonup_myskin_title_textshadow_color" id="baloonup_myskin_title_textshadow_color" value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'textshadow_color' ) ) ?>" class="pum-color-picker textshadow-color"/>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="baloonup_myskin_title_textshadow_opacity"><?php _e( 'Opacity', 'baloonup-maker' ); ?></label>
			</th>
			<td>
				<input type="text"
				       value="<?php esc_attr_e( balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id, 'textshadow_opacity' ) ) ?>"
				       name="baloonup_myskin_title_textshadow_opacity"
				       id="baloonup_myskin_title_textshadow_opacity"
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