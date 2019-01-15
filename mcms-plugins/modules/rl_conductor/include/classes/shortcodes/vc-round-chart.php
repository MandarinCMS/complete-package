<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_Vc_Round_Chart extends MCMSBakeryShortCode {
	public function __construct( $settings ) {
		parent::__construct( $settings );
		$this->jsScripts();
	}

	public function jsScripts() {
		mcms_register_script( 'waypoints', vc_asset_url( 'lib/waypoints/waypoints.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'ChartJS', vc_asset_url( 'lib/bower/chartjs/Chart.min.js' ), array(), MCMSB_VC_VERSION, true );
		mcms_register_script( 'vc_round_chart', vc_asset_url( 'lib/vc_round_chart/vc_round_chart.min.js' ), array(
			'jquery',
			'waypoints',
			'ChartJS',
		), MCMSB_VC_VERSION, true );
	}
}
