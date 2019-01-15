<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * JMCMSLayer loader.
 * @since 4.3
 */
class Vc_Vendor_Jmcmslayer implements Vc_Vendor_Interface {
	/**
	 * Dublicate jmcmslayer logic for editor, when used in frontend editor mode.
	 *
	 * @since 4.3
	 */
	public function load() {

		add_action( 'mcms_enqueue_scripts', array(
			&$this,
			'vc_load_iframe_jscss',
		) );
		add_filter( 'vc_front_render_shortcodes', array(
			&$this,
			'renderShortcodes',
		) );
		add_filter( 'vc_frontend_template_the_content', array(
			&$this,
			'wrapPlaceholder',
		) );

		// fix for #1065
		add_filter( 'vc_shortcode_content_filter_after', array(
			&$this,
			'renderShortcodesPreview',
		) );
	}

	/**
	 * @param $output
	 *
	 * @since 4.3
	 *
	 * @return mixed|string
	 */
	public function renderShortcodes( $output ) {
		$output = str_replace( '][jmcmslayer', '] [jmcmslayer', $output ); // fixes jmcmslayer shortcode regex..
		$data = JMCMS6_Shortcode::the_content_filter( $output );
		preg_match_all( '/(jmcmslayer-\d+)/', $data, $matches );
		$pairs = array_unique( $matches[0] );

		if ( count( $pairs ) > 0 ) {
			$id_zero = time();
			foreach ( $pairs as $pair ) {
				$data = str_replace( $pair, 'jmcmslayer-' . $id_zero ++, $data );
			}
		}

		return $data;
	}

	public function wrapPlaceholder( $content ) {
		add_shortcode( 'jmcmslayer', array( &$this, 'renderPlaceholder' ) );

		return $content;
	}

	public function renderPlaceholder() {
		return '<div class="vc_placeholder-jmcmslayer"></div>';
	}

	/**
	 * @param $output
	 *
	 * @since 4.3, due to #1065
	 *
	 * @return string
	 */
	public function renderShortcodesPreview( $output ) {
		$output = str_replace( '][jmcmslayer', '] [jmcmslayer', $output ); // fixes jmcmslayer shortcode regex..
		return $output;
	}

	/**
	 * @since 4.3
	 * @todo check it for preview mode (check is it needed)
	 */
	public function vc_load_iframe_jscss() {
		mcms_enqueue_script( 'vc_vendor_jmcmslayer', vc_asset_url( 'js/frontend_editor/vendors/modules/jmcmslayer.js' ), array( 'jquery' ), '1.0', true );
	}
}
