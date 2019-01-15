<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-gallery.php' );

class MCMSBakeryShortCode_VC_images_carousel extends MCMSBakeryShortCode_VC_gallery {
	protected static $carousel_index = 1;

	public function __construct( $settings ) {
		parent::__construct( $settings );
		$this->jsCssScripts();
	}

	public function jsCssScripts() {
		mcms_register_script( 'vc_transition_bootstrap_js', vc_asset_url( 'lib/vc_carousel/js/transition.min.js' ), array(), MCMSB_VC_VERSION, true );
		mcms_register_script( 'vc_carousel_js', vc_asset_url( 'lib/vc_carousel/js/vc_carousel.min.js' ), array( 'vc_transition_bootstrap_js' ), MCMSB_VC_VERSION, true );
		mcms_register_style( 'vc_carousel_css', vc_asset_url( 'lib/vc_carousel/css/vc_carousel.min.css' ), array(), MCMSB_VC_VERSION );
	}

	public static function getCarouselIndex() {
		return self::$carousel_index ++ . '-' . time();
	}

	protected function getSliderWidth( $size ) {
		global $_mcms_additional_image_sizes;
		$width = '100%';
		if ( in_array( $size, get_intermediate_image_sizes() ) ) {
			if ( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$width = get_option( $size . '_size_w' ) . 'px';
			} else {
				if ( isset( $_mcms_additional_image_sizes ) && isset( $_mcms_additional_image_sizes[ $size ] ) ) {
					$width = $_mcms_additional_image_sizes[ $size ]['width'] . 'px';
				}
			}
		} else {
			preg_match_all( '/\d+/', $size, $matches );
			if ( count( $matches[0] ) > 1 ) {
				$width = $matches[0][0] . 'px';
			}
		}

		return $width;
	}
}
