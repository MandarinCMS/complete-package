<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-media-grid.php' );

class MCMSBakeryShortCode_VC_Masonry_Media_Grid extends MCMSBakeryShortCode_VC_Media_Grid {

	public function shortcodeScripts() {
		parent::shortcodeScripts();
		mcms_register_script( 'vc_masonry', vc_asset_url( 'lib/bower/masonry/dist/masonry.pkgd.min.js' ),
			array(), MCMSB_VC_VERSION, true
		);

	}

	public function enqueueScripts() {
		mcms_enqueue_script( 'vc_masonry' );
		parent::enqueueScripts();
	}

	public function buildGridSettings() {
		parent::buildGridSettings();
		$this->grid_settings['style'] .= '-masonry';
	}

	protected function contentAllMasonry( $grid_style, $settings, $content ) {
		return parent::contentAll( $grid_style, $settings, $content );
	}

	protected function contentLazyMasonry( $grid_style, $settings, $content ) {
		return parent::contentLazy( $grid_style, $settings, $content );
	}

	protected function contentLoadMoreMasonry( $grid_style, $settings, $content ) {
		return parent::contentLoadMore( $grid_style, $settings, $content );
	}
}
