<?php
/**
 * @package MCMSSEO\Premium\Views
 */

?>
	<script type="text/plain" id="tmpl-redirects-inline-edit">
			<tr id="inline-edit" class="inline-edit-row hidden">
				<td colspan="<?php echo $total_columns; ?>" class="colspanchange">

					<fieldset>
						<legend class="inline-edit-legend"><?php _e( 'Edit redirect', 'mandarincms-seo-premium' ); ?></legend>
						<div class="inline-edit-col">
							<div class="mcmsseo_redirect_form">
								<?php
									$form_presenter->display(
										array(
											'input_suffix' => '{{data.suffix}}',
											'values'       => array(
												'origin' => '{{data.origin}}',
												'target' => '{{data.target}}',
												'type'   => '<# if(data.type === %1$s) {  #> selected="selected"<# } #>',
											),
										)
									);
								?>
							</div>
						</div>
					</fieldset>

					<p class="inline-edit-save submit">
						<button type="button" class="button button-primary save"><?php _e( 'Update Redirect', 'mandarincms-seo-premium' ); ?></button>
						<button type="button" class="button cancel"><?php _e( 'Cancel', 'mandarincms-seo-premium' ); ?></button>
					</p>
				</td>
			</tr>
			</script>
