<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_action('balooncreate_baloonup_myskin_content_meta_box_fields', 'balooncreate_baloonup_myskin_content_meta_box_field_description', 0);
function balooncreate_baloonup_myskin_content_meta_box_field_description( $baloonup_myskin_id ) { ?>
	</tbody></table><p><?php _e( 'mySkin the content inside the baloonups.', 'baloonup-maker' ); ?></p><table class="form-table"><tbody><?php
}

add_action('balooncreate_baloonup_myskin_content_meta_box_fields', 'balooncreate_baloonup_myskin_content_meta_box_field_font', 10);
function balooncreate_baloonup_myskin_content_meta_box_field_font( $baloonup_myskin_id ) { ?>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_content_font_color"><strong class="title"><?php _e( 'Font', 'baloonup-maker' );?></strong></label>
		</th>
		<td></td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_content_font_color"><?php _e( 'Color', 'baloonup-maker' );?></label>
		</th>
		<td>
			<input type="text" name="baloonup_myskin_content_font_color" id="baloonup_myskin_content_font_color" value="<?php esc_attr_e(balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id, 'font_color' ))?>" class="pum-color-picker" />
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="baloonup_myskin_content_font_family"><?php _e( 'Family', 'baloonup-maker' );?></label>
		</th>
		<td>
			<select name="baloonup_myskin_content_font_family" id="baloonup_myskin_content_font_family" class="font-family">
			<?php foreach( apply_filters( 'balooncreate_font_family_options', array() ) as $option => $value ) : ?>
				<option value="<?php echo $value;?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id, 'font_family' ) ? ' selected="selected"' : '';?>
					<?php echo $value == '' ? ' class="bold"' : '';?>
				><?php echo $option;?></option>
			<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row font-weight-only">
			<label for="baloonup_myskin_content_font_weight"><?php _e( 'Weight', 'baloonup-maker' );?></label>
		</th>
		<td>
			<select name="baloonup_myskin_content_font_weight" id="baloonup_myskin_content_font_weight" class="font-weight">
			<?php foreach(apply_filters('balooncreate_font_weight_options', array()) as $option => $value) : ?>
				<option
					value="<?php echo $value;?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id, 'font_weight') ? ' selected="selected"' : '';?>
					<?php echo $value == '' ? ' class="bold"' : '';?>
				><?php echo $option;?></option>
			<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row font-style-only">
			<label for="baloonup_myskin_content_font_style"><?php _e( 'Style', 'baloonup-maker' );?></label>
		</th>
		<td>
			<select name="baloonup_myskin_content_font_style" id="baloonup_myskin_content_font_style" class="font-style">
			<?php foreach(apply_filters('balooncreate_font_style_options', array()) as $option => $value) : ?>
				<option
					value="<?php echo $value;?>"
					<?php echo $value == balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id, 'font_style') ? ' selected="selected"' : '';?>
					<?php echo $value == '' ? ' class="bold"' : '';?>
				><?php echo $option;?></option>
			<?php endforeach ?>
			</select>
		</td>
	</tr><?php
}