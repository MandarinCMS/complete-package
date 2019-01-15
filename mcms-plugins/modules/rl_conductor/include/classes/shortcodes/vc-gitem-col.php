<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-column.php' );

class MCMSBakeryShortCode_VC_Gitem_Col extends MCMSBakeryShortCode_VC_Column {
	public $nonDraggableClass = 'vc-non-draggable-column';
	public function mainHtmlBlockParams( $width, $i ) {
		$sortable = ( vc_user_access_check_shortcode_all( $this->shortcode ) ? ' mcmsb_sortable ' : ' '. $this->nonDraggableClass . ' ' );

		return 'data-element_type="' . $this->settings['base'] . '" data-vc-column-width="'
		       . mcmsb_vc_get_column_width_indent( $width[ $i ] )
		       . '" class="mcmsb_vc_column mcmsb_' . $this->settings['base'] . $sortable
		       . $this->templateWidth() . ' mcmsb_content_holder"'
		       . $this->customAdminBlockParams();
	}

	public function outputEditorControlAlign() {
		$alignment = array(
			array( 'name' => 'left', 'label' => __( 'Left', 'rl_conductor' ) ),
			array( 'name' => 'center', 'label' => __( 'Center', 'rl_conductor' ) ),
			array( 'name' => 'right', 'label' => __( 'Right', 'rl_conductor' ) ),
		);
		$output = '<span class="vc_control vc_control-align"><span class="vc_control-wrap">';
		foreach ( $alignment as $data ) {
			$attr = esc_attr( $data['name'] );
			$output .= '<a href="#" data-vc-control-btn="align" data-vc-align="' . $attr . '" class="vc_control'
			           . ' vc_control-align-' . $attr . '" title="' . esc_html( $data['label'] )
			           . '"><i class="vc_icon vc_icon-align-' . $attr . '"></i></a>';
		}

		return $output . '</span></span>';
	}
}
