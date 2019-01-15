<?php
/**
 * MandarinCMS Credits Administration API.
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * Retrieve the contributor credits.
 *
 * @since 3.2.0
 *
 * @return array|false A list of all of the contributors, or false on error.
 */
function mcms_credits() {
	// include an unmodified $mcms_version
	include( BASED_TREE_URI . MCMSINC . '/version.php' );

	$locale = get_user_locale();

	$results = get_site_transient( 'mandarincms_credits_' . $locale );

	if ( ! is_array( $results )
		|| false !== strpos( $mcms_version, '-' )
		|| ( isset( $results['data']['version'] ) && strpos( $mcms_version, $results['data']['version'] ) !== 0 )
	) {
		$url = "http://api.mandarincms.com/core/credits/1.1/?version={$mcms_version}&locale={$locale}";
		$options = array( 'user-agent' => 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' ) );

		if ( mcms_http_supports( array( 'ssl' ) ) ) {
			$url = set_url_scheme( $url, 'https' );
		}

		$response = mcms_remote_get( $url, $options );

		if ( is_mcms_error( $response ) || 200 != mcms_remote_retrieve_response_code( $response ) )
			return false;

		$results = json_decode( mcms_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) )
			return false;

		set_site_transient( 'mandarincms_credits_' . $locale, $results, DAY_IN_SECONDS );
	}

	return $results;
}

/**
 * Retrieve the link to a contributor's MandarinCMS.org profile page.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string $display_name  The contributor's display name (passed by reference).
 * @param string $username      The contributor's username.
 * @param string $profiles      URL to the contributor's MandarinCMS.org profile page.
 */
function _mcms_credits_add_profile_link( &$display_name, $username, $profiles ) {
	$display_name = '<a href="' . esc_url( sprintf( $profiles, $username ) ) . '">' . esc_html( $display_name ) . '</a>';
}

/**
 * Retrieve the link to an external library used in MandarinCMS.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string $data External library data (passed by reference).
 */
function _mcms_credits_build_object_link( &$data ) {
	$data = '<a href="' . esc_url( $data[1] ) . '">' . esc_html( $data[0] ) . '</a>';
}
