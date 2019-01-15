<?php
/**
 * HTTP API: MCMS_HTTP_Proxy class
 *
 * @package MandarinCMS
 * @subpackage HTTP
 * @since 4.4.0
 */

/**
 * Core class used to implement HTTP API proxy support.
 *
 * There are caveats to proxy support. It requires that defines be made in the database-settings.php file to
 * enable proxy support. There are also a few filters that modules can hook into for some of the
 * constants.
 *
 * Please note that only BASIC authentication is supported by most transports.
 * cURL MAY support more methods (such as NTLM authentication) depending on your environment.
 *
 * The constants are as follows:
 * <ol>
 * <li>MCMS_PROXY_HOST - Enable proxy support and host for connecting.</li>
 * <li>MCMS_PROXY_PORT - Proxy port for connection. No default, must be defined.</li>
 * <li>MCMS_PROXY_USERNAME - Proxy username, if it requires authentication.</li>
 * <li>MCMS_PROXY_PASSWORD - Proxy password, if it requires authentication.</li>
 * <li>MCMS_PROXY_BYPASS_HOSTS - Will prevent the hosts in this list from going through the proxy.
 * You do not need to have localhost and the site host in this list, because they will not be passed
 * through the proxy. The list should be presented in a comma separated list, wildcards using * are supported, eg. *.mandarincms.com</li>
 * </ol>
 *
 * An example can be as seen below.
 *
 *     define('MCMS_PROXY_HOST', '192.168.84.101');
 *     define('MCMS_PROXY_PORT', '8080');
 *     define('MCMS_PROXY_BYPASS_HOSTS', 'localhost, www.example.com, *.mandarincms.com');
 *
 * @link https://core.trac.mandarincms.com/ticket/4011 Proxy support ticket in MandarinCMS.
 * @link https://core.trac.mandarincms.com/ticket/14636 Allow wildcard domains in MCMS_PROXY_BYPASS_HOSTS
 *
 * @since 2.8.0
 */
class MCMS_HTTP_Proxy {

	/**
	 * Whether proxy connection should be used.
	 *
	 * @since 2.8.0
	 *
	 * @use MCMS_PROXY_HOST
	 * @use MCMS_PROXY_PORT
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return defined('MCMS_PROXY_HOST') && defined('MCMS_PROXY_PORT');
	}

	/**
	 * Whether authentication should be used.
	 *
	 * @since 2.8.0
	 *
	 * @use MCMS_PROXY_USERNAME
	 * @use MCMS_PROXY_PASSWORD
	 *
	 * @return bool
	 */
	public function use_authentication() {
		return defined('MCMS_PROXY_USERNAME') && defined('MCMS_PROXY_PASSWORD');
	}

	/**
	 * Retrieve the host for the proxy server.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function host() {
		if ( defined('MCMS_PROXY_HOST') )
			return MCMS_PROXY_HOST;

		return '';
	}

	/**
	 * Retrieve the port for the proxy server.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function port() {
		if ( defined('MCMS_PROXY_PORT') )
			return MCMS_PROXY_PORT;

		return '';
	}

	/**
	 * Retrieve the username for proxy authentication.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function username() {
		if ( defined('MCMS_PROXY_USERNAME') )
			return MCMS_PROXY_USERNAME;

		return '';
	}

	/**
	 * Retrieve the password for proxy authentication.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function password() {
		if ( defined('MCMS_PROXY_PASSWORD') )
			return MCMS_PROXY_PASSWORD;

		return '';
	}

	/**
	 * Retrieve authentication string for proxy authentication.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function authentication() {
		return $this->username() . ':' . $this->password();
	}

	/**
	 * Retrieve header string for proxy authentication.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function authentication_header() {
		return 'Proxy-Authorization: Basic ' . base64_encode( $this->authentication() );
	}

	/**
	 * Whether URL should be sent through the proxy server.
	 *
	 * We want to keep localhost and the site URL from being sent through the proxy server, because
	 * some proxies can not handle this. We also have the constant available for defining other
	 * hosts that won't be sent through the proxy.
	 *
	 * @since 2.8.0
	 *
	 * @staticvar array|null $bypass_hosts
	 * @staticvar array      $wildcard_regex
	 *
	 * @param string $uri URI to check.
	 * @return bool True, to send through the proxy and false if, the proxy should not be used.
	 */
	public function send_through_proxy( $uri ) {
		/*
		 * parse_url() only handles http, https type URLs, and will emit E_WARNING on failure.
		 * This will be displayed on sites, which is not reasonable.
		 */
		$check = @parse_url($uri);

		// Malformed URL, can not process, but this could mean ssl, so let through anyway.
		if ( $check === false )
			return true;

		$home = parse_url( get_option('siteurl') );

		/**
		 * Filters whether to preempt sending the request through the proxy server.
		 *
		 * Returning false will bypass the proxy; returning true will send
		 * the request through the proxy. Returning null bypasses the filter.
		 *
		 * @since 3.5.0
		 *
		 * @param null   $override Whether to override the request result. Default null.
		 * @param string $uri      URL to check.
		 * @param array  $check    Associative array result of parsing the URI.
		 * @param array  $home     Associative array result of parsing the site URL.
		 */
		$result = apply_filters( 'pre_http_send_through_proxy', null, $uri, $check, $home );
		if ( ! is_null( $result ) )
			return $result;

		if ( 'localhost' == $check['host'] || ( isset( $home['host'] ) && $home['host'] == $check['host'] ) )
			return false;

		if ( !defined('MCMS_PROXY_BYPASS_HOSTS') )
			return true;

		static $bypass_hosts = null;
		static $wildcard_regex = array();
		if ( null === $bypass_hosts ) {
			$bypass_hosts = preg_split('|,\s*|', MCMS_PROXY_BYPASS_HOSTS);

			if ( false !== strpos(MCMS_PROXY_BYPASS_HOSTS, '*') ) {
				$wildcard_regex = array();
				foreach ( $bypass_hosts as $host )
					$wildcard_regex[] = str_replace( '\*', '.+', preg_quote( $host, '/' ) );
				$wildcard_regex = '/^(' . implode('|', $wildcard_regex) . ')$/i';
			}
		}

		if ( !empty($wildcard_regex) )
			return !preg_match($wildcard_regex, $check['host']);
		else
			return !in_array( $check['host'], $bypass_hosts );
	}
}
