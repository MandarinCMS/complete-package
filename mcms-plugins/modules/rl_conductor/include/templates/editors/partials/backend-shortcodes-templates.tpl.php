<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

global $current_user;
mcms_get_current_user();
require_once vc_path_dir( 'AUTOLOAD_DIR', 'class-vc-settings-presets.php' );

if ( vc_user_access()->part( 'presets' )->can()->get() ) {
	$vc_settings_presets = Vc_Settings_Preset::listDefaultSettingsPresets();
	$vc_vendor_settings_presets = Vc_Settings_Preset::listDefaultVendorSettingsPresets();
} else {
	$vc_settings_presets = array();
	$vc_vendor_settings_presets = array();
}
?>
<script type="text/javascript">
	var vc_user_mapper = <?php echo json_encode( MCMSBMap::getUserShortCodes() ) ?>,
		vc_mapper = <?php echo json_encode( MCMSBMap::getShortCodes() ) ?>,
		vc_vendor_settings_presets = <?php echo json_encode( $vc_vendor_settings_presets ) ?>,
		vc_settings_presets = <?php echo json_encode( $vc_settings_presets ) ?>,
		vc_roles = [], // @todo fix_roles check BC
		vc_frontend_enabled = <?php echo vc_enabled_frontend() ? 'true' : 'false' ?>,
		vc_mode = '<?php echo vc_mode() ?>',
		vcAdminNonce = '<?php echo vc_generate_nonce( 'vc-admin-nonce' ); ?>';
</script>

<?php vc_include_template( 'editors/partials/vc_settings-image-block.tpl.php' ) ?>

<?php foreach ( MCMSBMap::getShortCodes() as $sc_base => $el ) :  ?>
	<script type="text/html" id="vc_shortcode-template-<?php echo $sc_base ?>">
		<?php
		echo visual_composer()->getShortCode( $sc_base )->template();
		?>
	</script>
<?php endforeach ?>
<script type="text/html" id="vc_row-inner-element-template">
	<?php
	echo visual_composer()->getShortCode( 'vc_row_inner' )->template();
	?>
</script>
<script type="text/html" id="vc_settings-page-param-block">
	<div class="row-fluid mcmsb_el_type_<%= type %>">
		<div class="mcmsb_element_label"><%= heading %></div>
		<div class="edit_form_line">
			<%= form_element %>
		</div>
	</div>
</script>
