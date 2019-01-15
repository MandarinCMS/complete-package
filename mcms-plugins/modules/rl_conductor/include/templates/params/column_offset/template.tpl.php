<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
/**
 * @var $param Vc_Column_Offset
 * @var $sizes Vc_Column_Offset::$size_types
 */
?>
<div class="vc_column-offset" data-column-offset="true">
	<?php if ( '1' === vc_settings()->get( 'not_responsive_css' ) ) :  ?>
		<div class="mcmsb_alert mcmsb_content_element vc_alert_rounded mcmsb_alert-warning">
			<div class="messagebox_text">
				<p><?php printf( __( 'Responsive design settings are currently disabled. You can enable them in RazorLeaf Conductor <a href="%s">settings page</a> by unchecking "Disable responsive content elements".', 'rl_conductor' ), admin_url( 'admin.php?page=vc-general' ) ) ?></p>
			</div>
		</div>
	<?php endif ?>
	<input name="<?php echo esc_attr( $settings['param_name'] ) ?>"
	       class="mcmsb_vc_param_value <?php echo esc_attr( $settings['param_name'] ) ?>
	<?php echo esc_attr( $settings['type'] ) ?> '_field" type="hidden" value="<?php echo esc_attr( $value ) ?>"/>
	<table class="vc_table vc_column-offset-table">
		<tr>
			<th>
				<?php _e( 'Device', 'rl_conductor' ) ?>
			</th>
			<th>
				<?php _e( 'Offset', 'rl_conductor' ) ?>
			</th>
			<th>
				<?php _e( 'Width', 'rl_conductor' ) ?>
			</th>
			<th>
				<?php _e( 'Hide on device?', 'rl_conductor' ) ?>
			</th>
		</tr>
		<?php foreach ( $sizes as $key => $size ) :  ?>
			<tr class="vc_size-<?php echo $key ?>">
				<td class="vc_screen-size vc_screen-size-<?php echo $key ?>">
					<span title="<?php echo $size ?>"><i class="vc-composer-icon vc-c-icon-layout-<?php echo $key ?>"></i></span>
				</td>
				<td>
					<?php echo $param->offsetControl( $key ) ?>
				</td>
				<td>
					<?php echo $param->sizeControl( $key ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" name="vc_hidden-<?php echo $key ?>"
						       value="yes"<?php echo in_array( 'vc_hidden-' . $key, $data ) ? ' checked="true"' : '' ?>
						       class="vc_column_offset_field">
					</label>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
</div>
<script type="text/javascript">
	window.VcI8nColumnOffsetParam = <?php echo json_encode(array(
			'inherit' => __( 'Inherit: ', 'rl_conductor' ),
			'inherit_default' => __( 'Inherit from default', 'rl_conductor' ),
		)) ?>;
</script>
