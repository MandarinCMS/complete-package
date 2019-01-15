<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * MCMSBakery RazorLeaf Conductor shortcodes
 *
 * @package MCMSBakeryVisualComposer
 *
 */
class MCMSBakeryShortCode_VC_Wp_Search extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Meta extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Recentcomments extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Calendar extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Pages extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Tagcloud extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Custommenu extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Text extends MCMSBakeryShortCode {
	/**
	 * This actually fixes #1537 by converting 'text' to 'content'
	 * @since 4.4
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public static function convertTextAttributeToContent( $atts ) {
		if ( isset( $atts['text'] ) ) {
			if ( ! isset( $atts['content'] ) || empty( $atts['content'] ) ) {
				$atts['content'] = $atts['text'];
			}
		}

		return $atts;
	}
}

class MCMSBakeryShortCode_VC_Wp_Posts extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Links extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Categories extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Archives extends MCMSBakeryShortCode {
}

class MCMSBakeryShortCode_VC_Wp_Rss extends MCMSBakeryShortCode {
}
