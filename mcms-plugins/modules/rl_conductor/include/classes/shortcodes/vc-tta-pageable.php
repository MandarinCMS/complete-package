<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

VcShortcodeAutoloader::getInstance()->includeClass( 'MCMSBakeryShortCode_VC_Tta_Tabs' );

class MCMSBakeryShortCode_VC_Tta_Pageable extends MCMSBakeryShortCode_VC_Tta_Tabs {

	public $layout = 'tabs';

	public function getTtaContainerClasses() {
		$classes = parent::getTtaContainerClasses();

		$classes .= ' vc_tta-o-non-responsive';

		return $classes;
	}

	public function getTtaGeneralClasses() {
		$classes = parent::getTtaGeneralClasses();

		$classes .= ' vc_tta-pageable';

		// tabs have pagination on opposite side of tabs. pageable should behave normally
		if ( false !== strpos( $classes, 'vc_tta-tabs-position-top' ) ) {
			$classes = str_replace( 'vc_tta-tabs-position-top', 'vc_tta-tabs-position-bottom', $classes );
		} else {
			$classes = str_replace( 'vc_tta-tabs-position-bottom', 'vc_tta-tabs-position-top', $classes );

		}

		return $classes;
	}

	/**
	 * Disable all tabs
	 *
	 * @param $atts
	 * @param $content
	 *
	 * @return string
	 */
	public function getParamTabsList( $atts, $content ) {
		return '';
	}
}
