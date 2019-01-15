<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

define( 'TAB_TITLE', __( 'Tab', 'rl_conductor' ) );
require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-column.php' );

class MCMSBakeryShortCode_VC_Tab extends MCMSBakeryShortCode_VC_Column {
	protected $controls_css_settings = 'tc vc_control-container';
	protected $controls_list = array( 'add', 'edit', 'clone', 'delete' );
	protected $predefined_atts = array(
		'tab_id' => '',
		'title' => '',
	);
	protected $controls_template_file = 'editors/partials/backend_controls_tab.tpl.php';

	public function __construct( $settings ) {
		parent::__construct( $settings );
	}

	public function customAdminBlockParams() {
		return ' id="tab-' . $this->atts['tab_id'] . '"';
	}

	public function mainHtmlBlockParams( $width, $i ) {
		$sortable = ( vc_user_access_check_shortcode_all( $this->shortcode ) ? 'mcmsb_sortable' : $this->nonDraggableClass );

		return 'data-element_type="' . $this->settings['base'] . '" class="mcmsb_' . $this->settings['base'] . ' ' . $sortable . ' mcmsb_content_holder"' . $this->customAdminBlockParams();
	}

	public function containerHtmlBlockParams( $width, $i ) {
		return 'class="mcmsb_column_container vc_container_for_children"';
	}

	public function getColumnControls( $controls, $extended_css = '' ) {
		return $this->getColumnControlsModular( $extended_css );
	}
}

/**
 * @param $settings
 * @param $value
 *
 * @since 4.4
 * @return string
 */
function vc_tab_id_settings_field( $settings, $value ) {
	return '<div class="my_param_block">'
	       . '<input name="' . $settings['param_name']
	       . '" class="mcmsb_vc_param_value mcmsb-textinput '
	       . $settings['param_name'] . ' ' . $settings['type'] . '_field" type="hidden" value="'
	       . $value . '" />'
	       . '<label>' . $value . '</label>'
	       . '</div>';
	// TODO: Add data-js-function to documentation
}

vc_add_shortcode_param( 'tab_id', 'vc_tab_id_settings_field' );
