<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'EDITORS_DIR', 'navbar/class-vc-navbar.php' );

/**
 * Renders navigation bar for Editors.
 */
class Vc_Navbar_Grid_Item extends Vc_Navbar {
	protected $controls = array(
		'templates',
		'save_backend',
		'preview_template',
		'animation_list',
		'preview_item_width',
		'edit',
	);

	/**
	 * @return string
	 */
	public function getControlTemplates() {
		return '<li><a href="javascript:;" class="vc_icon-btn vc_templates-button vc_navbar-border-right"  id="vc_templates-editor-button" title="'
		. __( 'Templates', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-add_template"></i></a></li>';
	}

	public function getControlPreviewTemplate() {
		return '<li class="vc_pull-right">'
		       . '<a href="#" class="vc_btn vc_btn-grey vc_btn-sm vc_navbar-btn" data-vc-navbar-control="preview">' . __( 'Preview', 'rl_conductor' ) . '</a>'
		       . '</li>';
	}

	public function getControlEdit() {
		return '<li class="vc_pull-right">'
		       . '<a data-vc-navbar-control="edit" class="vc_icon-btn vc_post-settings" title="'
		       . __( 'Grid element settings', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-cog"></i>'
		       . '</a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlSaveBackend() {
		return '<li class="vc_pull-right vc_save-backend">'
		       . '<a class="vc_btn vc_btn-sm vc_navbar-btn vc_btn-primary vc_control-save" id="mcmsb-save-post">' . __( 'Update', 'rl_conductor' ) . '</a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlPreviewItemWidth() {
		$output = '<li class="vc_pull-right vc_gitem-navbar-dropdown vc_gitem-navbar-preview-width" data-vc-grid-item="navbar_preview_width"><select data-vc-navbar-control="preview_width">';
		for ( $i = 1; $i <= 12; $i ++ ) {
			$output .= '<option value="' . esc_attr( $i ) . '">' . sprintf( __( '%s/12 width', 'rl_conductor' ), $i ) . '</option>';
		}
		$output .= '</select></li>';

		return $output;
	}

	public function getControlAnimationList() {
		VcShortcodeAutoloader::getInstance()->includeClass( 'MCMSBakeryShortCode_VC_Gitem_Animated_Block' );

		$output = '';

		$animations = MCMSBakeryShortCode_VC_Gitem_Animated_Block::animations();
		if ( is_array( $animations ) ) {
			$output .= '<li class="vc_pull-right vc_gitem-navbar-dropdown">'
			           . '<select data-vc-navbar-control="animation">';
			foreach ( $animations as $value => $key ) {
				$output .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
			}
			$output .= '</select></li>';
		}

		return $output;
	}
}
