<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'EDITORS_DIR', 'navbar/class-vc-navbar.php' );
/** @var $post MCMS_Post */
$nav_bar = new Vc_Navbar( $post );
$nav_bar->render();
/** @var $editor Vc_Backend_Editor */
?>
	<style>
		#mcmsb_visual_composer {
			display: none;
		}
	</style>
	<div class="metabox-composer-content">
		<div id="visual_composer_content" class="mcmsb_main_sortable main_wrapper"></div>
		<?php require vc_path_dir( 'TEMPLATES_DIR', 'editors/partials/vc_welcome_block.tpl.php' ); ?>

	</div>
<?php

$mcmsb_vc_status = apply_filters( 'mcmsb_vc_js_status_filter', vc_get_param( 'mcmsb_vc_js_status', get_post_meta( $post->ID, '_mcmsb_vc_js_status', true ) ) );

if ( '' === $mcmsb_vc_status || ! isset( $mcmsb_vc_status ) ) {
	$mcmsb_vc_status = vc_user_access()
		->part( 'backend_editor' )
		->checkState( 'default' )
		->get() ? 'true' : 'false';
}

?>

	<input type="hidden" id="mcmsb_vc_js_status" name="mcmsb_vc_js_status" value="<?php echo esc_attr( $mcmsb_vc_status ); ?>"/>
	<input type="hidden" id="mcmsb_vc_loading" name="mcmsb_vc_loading"
	       value="<?php esc_attr_e( 'Loading, please wait...', 'rl_conductor' ) ?>"/>
	<input type="hidden" id="mcmsb_vc_loading_row" name="mcmsb_vc_loading_row"
	       value="<?php esc_attr_e( 'Crunching...', 'rl_conductor' ) ?>"/>
	<input type="hidden" name="vc_post_custom_css" id="vc_post-custom-css"
	       value="<?php echo esc_attr( $editor->post_custom_css ); ?>" autocomplete="off"/>

<?php vc_include_template( 'editors/partials/access-manager-js.tpl.php' );
