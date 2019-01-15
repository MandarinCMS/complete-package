<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
$saveAsTemplateElements = apply_filters( 'vc_popup_save_as_template_elements', array(
	'vc_row',
	'vc_section',
) );
?>
<div class="vc_ui-list-bar-group">
	<?php if ( in_array( $shortcode_name, $saveAsTemplateElements ) && vc_user_access()->part( 'templates' )->can()->get() ) : ?>
		<ul class="vc_ui-list-bar">
			<li class="vc_ui-list-bar-item">
				<button type="button" class="vc_ui-list-bar-item-trigger" data-vc-save-template>
					<?php _e( 'Save as template', 'rl_conductor' ) ?>
				</button>
			</li>
		</ul>
	<?php endif; ?>
	<?php if ( vc_user_access()->part( 'presets' )->can()->get() ) : ?>
		<ul class="vc_ui-list-bar">
			<li class="vc_ui-list-bar-item">
				<button type="button" class="vc_ui-list-bar-item-trigger" data-vc-save-settings-preset>
					<?php _e( 'Save as preset', 'rl_conductor' ) ?>
				</button>
			</li>
			<li class="vc_ui-list-bar-item">
				<button type="button" class="vc_ui-list-bar-item-trigger" data-vc-save-default-settings-preset>
					<?php _e( 'Set as default', 'rl_conductor' ) ?>
				</button>
			</li>
			<?php if ( $default_id ) : ?>
				<li class="vc_ui-list-bar-item">
					<button type="button" class="vc_ui-list-bar-item-trigger" data-vc-restore-default-settings-preset>
						<?php _e( 'Restore default', 'rl_conductor' ) ?>
					</button>
				</li>
			<?php endif ?>
		</ul>
	<?php endif; ?>
	<ul class="vc_ui-list-bar">
		<li class="vc_ui-list-bar-item">
			<button type="button" class="vc_ui-list-bar-item-trigger" data-vc-view-settings-preset disabled>
				<?php _e( 'View presets', 'rl_conductor' ) ?>
			</button>
		</li>
	</ul>
	<script>
		window.vc_presets_data = {
			"presets": <?php echo json_encode( $list_presets ); ?>,
			"presetsCount": <?php echo count( $list_presets[0] ) + count( $list_presets[1] ); ?>,
			"defaultId": <?php echo (int) $default_id; ?>,
			"can": <?php echo (int) vc_user_access()->part( 'presets' )->can()->get(); ?>,
			"defaultTitle": "<?php echo esc_attr__( 'Untitled', 'rl_conductor' ); ?>"
		}
	</script>
</div>
