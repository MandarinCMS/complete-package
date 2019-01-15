<?php
/**
 * @package MCMSSEO\Deprecated
 */

/**
 * Get the value from the post custom values
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Meta::get_value()
 * @see        MCMSSEO_Meta::get_value()
 *
 * @param    string $val    Internal name of the value to get.
 * @param    int    $postid Post ID of the post to get the value for.
 *
 * @return    string
 */
function mcmsseo_get_value( $val, $postid = 0 ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Meta::get_value()' );

	return MCMSSEO_Meta::get_value( $val, $postid );
}

/**
 * Save a custom meta value
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Meta::set_value() or just use update_post_meta()
 * @see        MCMSSEO_Meta::set_value()
 *
 * @param    string $meta_key   The meta to change.
 * @param    mixed  $meta_value The value to set the meta to.
 * @param    int    $post_id    The ID of the post to change the meta for.
 *
 * @return    bool    whether the value was changed
 */
function mcmsseo_set_value( $meta_key, $meta_value, $post_id ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Meta::set_value()' );

	return MCMSSEO_Meta::set_value( $meta_key, $meta_value, $post_id );
}

/**
 * Retrieve an array of all the options the module uses. It can't use only one due to limitations of the options API.
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Options::get_option_names()
 * @see        MCMSSEO_Options::get_option_names()
 *
 * @return array of options.
 */
function get_mcmsseo_options_arr() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Options::get_option_names()' );

	return MCMSSEO_Options::get_option_names();
}

/**
 * Retrieve all the options for the SEO module in one go.
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Options::get_all()
 * @see        MCMSSEO_Options::get_all()
 *
 * @return array of options
 */
function get_mcmsseo_options() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Options::get_all()' );

	return MCMSSEO_Options::get_all();
}

/**
 * Used for imports, both in dashboard and import settings pages, this functions either copies
 * $old_metakey into $new_metakey or just plain replaces $old_metakey with $new_metakey
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Meta::replace_meta()
 * @see        MCMSSEO_Meta::replace_meta()
 *
 * @param string $old_metakey The old name of the meta value.
 * @param string $new_metakey The new name of the meta value, usually the Ultimatum SEO name.
 * @param bool   $replace     Whether to replace or to copy the values.
 */
function replace_meta( $old_metakey, $new_metakey, $replace = false ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Meta::replace_meta()' );
	MCMSSEO_Meta::replace_meta( $old_metakey, $new_metakey, $replace );
}

/**
 * Retrieve a taxonomy term's meta value.
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Taxonomy_Meta::get_term_meta()
 * @see        MCMSSEO_Taxonomy_Meta::get_term_meta()
 *
 * @param string|object $term     Term to get the meta value for.
 * @param string        $taxonomy Name of the taxonomy to which the term is attached.
 * @param string        $meta     Meta value to get.
 *
 * @return bool|mixed value when the meta exists, false when it does not
 */
function mcmsseo_get_term_meta( $term, $taxonomy, $meta ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Taxonomy_Meta::get_term_meta()' );
	return MCMSSEO_Taxonomy_Meta::get_term_meta( $term, $taxonomy, $meta );
}

/**
 * Throw a notice about an invalid custom taxonomy used
 *
 * @since      1.4.14
 * @deprecated 1.5.4 (removed)
 */
function mcmsseo_invalid_custom_taxonomy() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.4' );
}

/**
 * Retrieve a post's terms, comma delimited.
 *
 * @deprecated 1.5.4
 * @deprecated use MCMSSEO_Replace_Vars::get_terms()
 * @see        MCMSSEO_Replace_Vars::get_terms()
 *
 * @param int    $id            ID of the post to get the terms for.
 * @param string $taxonomy      The taxonomy to get the terms for this post from.
 * @param bool   $return_single If true, return the first term.
 *
 * @return string either a single term or a comma delimited string of terms.
 */
function mcmsseo_get_terms( $id, $taxonomy, $return_single = false ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.4', 'MCMSSEO_Replace_Vars::get_terms()' );
	$replacer = new MCMSSEO_Replace_Vars;

	return $replacer->get_terms( $id, $taxonomy, $return_single );
}

/**
 * Generate an HTML sitemap
 *
 * @deprecated 1.5.5.4
 * @deprecated use module Ultimatum SEO
 * @see        Ultimatum SEO
 *
 * @param array $atts The attributes passed to the shortcode.
 *
 * @return string
 */
function mcmsseo_sitemap_handler( $atts ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.5.4', 'Functionality has been discontinued after being in beta, it\'ll be available in the Ultimatum SEO module soon.' );

	return '';
}

add_shortcode( 'mcmsseo_sitemap', 'mcmsseo_sitemap_handler' );

/**
 * Strip out the shortcodes with a filthy regex, because people don't properly register their shortcodes.
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::strip_shortcode()
 * @see        MCMSSEO_Utils::strip_shortcode()
 *
 * @param string $text Input string that might contain shortcodes.
 *
 * @return string $text string without shortcodes
 */
function mcmsseo_strip_shortcode( $text ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::strip_shortcode()' );

	return MCMSSEO_Utils::strip_shortcode( $text );
}

/**
 * Do simple reliable math calculations without the risk of wrong results
 *
 * @see        http://floating-point-gui.de/
 * @see        the big red warning on http://php.net/language.types.float.php
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::calc()
 * @see        MCMSSEO_Utils::calc()
 *
 * In the rare case that the bcmath extension would not be loaded, it will return the normal calculation results
 *
 * @since      1.5.0
 *
 * @param    mixed  $number1   Scalar (string/int/float/bool).
 * @param    string $action    Calculation action to execute.
 * @param    mixed  $number2   Scalar (string/int/float/bool).
 * @param    bool   $round     Whether or not to round the result. Defaults to false.
 * @param    int    $decimals  Decimals for rounding operation. Defaults to 0.
 * @param    int    $precision Calculation precision. Defaults to 10.
 *
 * @return    mixed                Calculation Result or false if either or the numbers isn't scalar or
 *                                an invalid operation was passed
 */
function mcmsseo_calc( $number1, $action, $number2, $round = false, $decimals = 0, $precision = 10 ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::calc()' );

	return MCMSSEO_Utils::calc( $number1, $action, $number2, $round, $decimals, $precision );
}

/**
 * Check if the web server is running on Apache
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::is_apache()
 * @see        MCMSSEO_Utils::is_apache()
 *
 * @return bool
 */
function mcmsseo_is_apache() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::is_apache()' );

	return MCMSSEO_Utils::is_apache();
}

/**
 * Check if the web service is running on Nginx
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::is_nginx()
 * @see        MCMSSEO_Utils::is_nginx()
 *
 * @return bool
 */
function mcmsseo_is_nginx() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::is_nginx()' );

	return MCMSSEO_Utils::is_nginx();
}

/**
 * List all the available user roles
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::get_roles()
 * @see        MCMSSEO_Utils::get_roles()
 *
 * @return array $roles
 */
function mcmsseo_get_roles() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::get_roles()' );

	return MCMSSEO_Utils::get_roles();
}

/**
 * Check whether a url is relative
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::is_url_relative()
 * @see        MCMSSEO_Utils::is_url_relative()
 *
 * @param string $url URL input to check.
 *
 * @return bool
 */
function mcmsseo_is_url_relative( $url ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::is_url_relative()' );

	return MCMSSEO_Utils::is_url_relative( $url );
}

/**
 * Standardize whitespace in a string
 *
 * @deprecated 1.6.1
 * @deprecated use MCMSSEO_Utils::standardize_whitespace()
 * @see        MCMSSEO_Utils::standardize_whitespace()
 *
 * @since      1.6.0
 *
 * @param string $string String input to standardize.
 *
 * @return string
 */
function mcmsseo_standardize_whitespace( $string ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.6.1', 'MCMSSEO_Utils::standardize_whitespace()' );

	return MCMSSEO_Utils::standardize_whitespace( $string );
}

/**
 * Initialize sitemaps. Add sitemap & XSL rewrite rules and query vars
 *
 * @deprecated 2.4
 * @see MCMSSEO_Sitemaps_Router
 */
function mcmsseo_xml_sitemaps_init() {
	$options = get_option( 'mcmsseo_xml' );
	if ( $options['enablexmlsitemap'] !== true ) {
		return;
	}

	// Redirects sitemap.xml to sitemap_index.xml.
	add_action( 'template_redirect', 'mcmsseo_xml_redirect_sitemap', 0 );

	if ( ! is_object( $GLOBALS['mcms'] ) ) {
		return;
	}

	$GLOBALS['mcms']->add_query_var( 'sitemap' );
	$GLOBALS['mcms']->add_query_var( 'sitemap_n' );
	$GLOBALS['mcms']->add_query_var( 'xsl' );
	add_rewrite_rule( 'sitemap_index\.xml$', 'index.php?sitemap=1', 'top' );
	add_rewrite_rule( '([^/]+?)-sitemap([0-9]+)?\.xml$', 'index.php?sitemap=$matches[1]&sitemap_n=$matches[2]', 'top' );
	add_rewrite_rule( '([a-z]+)?-?sitemap\.xsl$', 'index.php?xsl=$matches[1]', 'top' );
}

/**
 * Redirect /sitemap.xml to /sitemap_index.xml
 *
 * @deprecated 2.4
 * @see MCMSSEO_Sitemaps_Router
 */
function mcmsseo_xml_redirect_sitemap() {
	$current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
	$current_url .= sanitize_text_field( $_SERVER['SERVER_NAME'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );

	// Must be 'sitemap.xml' and must be 404.
	if ( home_url( '/sitemap.xml' ) == $current_url && $GLOBALS['mcms_query']->is_404 ) {
		mcms_redirect( home_url( '/sitemap_index.xml' ), 301 );
		exit;
	}
}

/**
 * This invalidates our XML Sitemaps cache.
 *
 * @deprecated
 * @see MCMSSEO_Sitemaps_Cache
 *
 * @param string $type Type of sitemap to invalidate.
 */
function mcmsseo_invalidate_sitemap_cache( $type ) {
	MCMSSEO_Sitemaps_Cache::invalidate( $type );
}

/**
 * Invalidate XML sitemap cache for taxonomy / term actions
 *
 * @deprecated
 * @see MCMSSEO_Sitemaps_Cache
 *
 * @param int    $unused Unused term ID value.
 * @param string $type   Taxonomy to invalidate.
 */
function mcmsseo_invalidate_sitemap_cache_terms( $unused, $type ) {
	MCMSSEO_Sitemaps_Cache::invalidate( $type );
}

/**
 * Invalidate the XML sitemap cache for a post type when publishing or updating a post
 *
 * @deprecated
 * @see MCMSSEO_Sitemaps_Cache
 *
 * @param int $post_id Post ID to determine post type for invalidation.
 */
function mcmsseo_invalidate_sitemap_cache_on_save_post( $post_id ) {
	MCMSSEO_Sitemaps_Cache::invalidate_post( $post_id );
}

/**
 * Notify search engines of the updated sitemap.
 *
 * @deprecated
 * @see MCMSSEO_Sitemaps::ping_search_engines()
 *
 * @param string|null $sitemapurl Optional URL to make the ping for.
 */
function mcmsseo_ping_search_engines( $sitemapurl = null ) {
	MCMSSEO_Sitemaps::ping_search_engines( $sitemapurl );
}

/**
 * Create base URL for the sitemaps and applies filters
 *
 * @since 1.5.7
 *
 * @deprecated
 * @see MCMSSEO_Sitemaps_Router::get_base_url()
 *
 * @param string $page page to append to the base URL.
 *
 * @return string base URL (incl page) for the sitemaps
 */
function mcmsseo_xml_sitemaps_base_url( $page ) {
	return MCMSSEO_Sitemaps_Router::get_base_url( $page );
}
