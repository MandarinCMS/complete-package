<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * LayerSlider loader.
 * Adds layerSlider shortcode to visual composer and fixes issue in frontend editor
 *
 * @since 4.3
 */
class Vc_Vendor_Layerslider implements Vc_Vendor_Interface {
	/**
	 * @var int - used to detect id for layerslider in frontend
	 */
	protected static $instanceIndex = 1;

	/**
	 * Add layerslayer shortcode to visual composer, and add fix for ID in frontend editor
	 * @since 4.3
	 */
	public function load() {
		add_action( 'vc_after_mapping', array(
			&$this,
			'buildShortcode',
		) );

	}

	/**
	 * Add shortcode and filters for layerslider id
	 * @since 4.3
	 */
	public function buildShortcode() {

		vc_lean_map( 'layerslider_vc', array(
			$this,
			'addShortcodeSettings',
		) );

		// Load layer slider shortcode && change id
		add_filter( 'vc_layerslider_shortcode', array(
			&$this,
			'setId',
		) );
	}

	/**
	 * @since 4.3
	 *
	 * @param $output
	 *
	 * @return string
	 */
	public function setId( $output ) {
		return preg_replace( '/(layerslider_\d+)/', '$1_' . time() . '_' . self::$instanceIndex ++, $output );
	}

	/**
	 * Mapping settings for lean method.
	 *
	 * @since 4.9
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	public function addShortcodeSettings( $tag ) {
		$use_old = class_exists( 'LS_Sliders' );
		if ( ! class_exists( 'LS_Sliders' ) && defined( 'LS_ROOT_PATH' ) && false === strpos( LS_ROOT_PATH, '.php' ) ) {
			include_once LS_ROOT_PATH . '/classes/class.ls.sliders.php';
			$use_old = false;
		}
		if ( ! class_exists( 'LS_Sliders' ) ) {
			//again check is needed if some problem inside file "class.ls.sliders.php
			$use_old = true;
		}
		/**
		 * Filter to use old type of layerslider vendor.
		 * @since 4.4.2
		 */
		$use_old = apply_filters( 'vc_vendor_layerslider_old', $use_old ); // @since 4.4.2 hook to use old style return true.
		if ( $use_old ) {
			global $mcmsdb;
			$ls = $mcmsdb->get_results( '
  SELECT id, name, date_c
  FROM ' . $mcmsdb->prefix . "layerslider
  WHERE flag_hidden = '0' AND flag_deleted = '0'
  ORDER BY date_c ASC LIMIT 999
  " );
			$layer_sliders = array();
			if ( ! empty( $ls ) ) {
				foreach ( $ls as $slider ) {
					$layer_sliders[ $slider->name ] = $slider->id;
				}
			} else {
				$layer_sliders[ __( 'No sliders found', 'rl_conductor' ) ] = 0;
			}
		} else {
			$ls = LS_Sliders::find( array(
				'limit' => 999,
				'order' => 'ASC',
			) );
			$layer_sliders = array();
			if ( ! empty( $ls ) ) {
				foreach ( $ls as $slider ) {
					$layer_sliders[ $slider['name'] ] = $slider['id'];
				}
			} else {
				$layer_sliders[ __( 'No sliders found', 'rl_conductor' ) ] = 0;
			}
		}

		return array(
			'base' => $tag,
			'name' => __( 'Layer Slider', 'rl_conductor' ),
			'icon' => 'icon-mcmsb-layerslider',
			'category' => __( 'Content', 'rl_conductor' ),
			'description' => __( 'Place LayerSlider', 'rl_conductor' ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Widget title', 'rl_conductor' ),
					'param_name' => 'title',
					'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'LayerSlider ID', 'rl_conductor' ),
					'param_name' => 'id',
					'admin_label' => true,
					'value' => $layer_sliders,
					'save_always' => true,
					'description' => __( 'Select your LayerSlider.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
			),
		);
	}
}
