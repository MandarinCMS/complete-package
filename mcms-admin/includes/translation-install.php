<?php
/**
 * MandarinCMS Translation Installation Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */


/**
 * Retrieve translations from MandarinCMS Translation API.
 *
 * @since 4.0.0
 *
 * @param string       $type Type of translations. Accepts 'modules', 'myskins', 'core'.
 * @param array|object $args Translation API arguments. Optional.
 * @return object|MCMS_Error On success an object of translations, MCMS_Error on failure.
 */
function translations_api( $type, $args = null ) {
	include( BASED_TREE_URI . MCMSINC . '/version.php' ); // include an unmodified $mcms_version

	if ( ! in_array( $type, array( 'modules', 'myskins', 'core' ) ) ) {
		return	new MCMS_Error( 'invalid_type', __( 'Invalid translation type.' ) );
	}

	/**
	 * Allows a module to override the MandarinCMS.org Translation Installation API entirely.
	 *
	 * @since 4.0.0
	 *
	 * @param bool|array  $result The result object. Default false.
	 * @param string      $type   The type of translations being requested.
	 * @param object      $args   Translation API arguments.
	 */
	$res = apply_filters( 'translations_api', false, $type, $args );

	if ( false === $res ) {
		$url = $http_url = 'http://api.mandarincms.com/translations/' . $type . '/1.0/';
		if ( $ssl = mcms_http_supports( array( 'ssl' ) ) ) {
			$url = set_url_scheme( $url, 'https' );
		}

		$options = array(
			'timeout' => 3,
			'body' => array(
				'mcms_version' => $mcms_version,
				'locale'     => get_locale(),
				'version'    => $args['version'], // Version of module, myskin or core
			),
		);

		if ( 'core' !== $type ) {
			$options['body']['slug'] = $args['slug']; // Module or myskin slug
		}

		$request = mcms_remote_post( $url, $options );

		if ( $ssl && is_mcms_error( $request ) ) {
			trigger_error(
				sprintf(
					/* translators: %s: support forums URL */
					__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
					__( 'https://mandarincms.com/support/' )
				) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
				headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
			);

			$request = mcms_remote_post( $http_url, $options );
		}

		if ( is_mcms_error( $request ) ) {
			$res = new MCMS_Error( 'translations_api_failed',
				sprintf(
					/* translators: %s: support forums URL */
					__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
					__( 'https://mandarincms.com/support/' )
				),
				$request->get_error_message()
			);
		} else {
			$res = json_decode( mcms_remote_retrieve_body( $request ), true );
			if ( ! is_object( $res ) && ! is_array( $res ) ) {
				$res = new MCMS_Error( 'translations_api_failed',
					sprintf(
						/* translators: %s: support forums URL */
						__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://mandarincms.com/support/' )
					),
					mcms_remote_retrieve_body( $request )
				);
			}
		}
	}

	/**
	 * Filters the Translation Installation API response results.
	 *
	 * @since 4.0.0
	 *
	 * @param object|MCMS_Error $res  Response object or MCMS_Error.
	 * @param string          $type The type of translations being requested.
	 * @param object          $args Translation API arguments.
	 */
	return apply_filters( 'translations_api_result', $res, $type, $args );
}

/**
 * Get available translations from the MandarinCMS.org API.
 *
 * @since 4.0.0
 *
 * @see translations_api()
 *
 * @return array Array of translations, each an array of data. If the API response results
 *               in an error, an empty array will be returned.
 */
function mcms_get_available_translations() {
	if ( ! mcms_installing() && false !== ( $translations = get_site_transient( 'available_translations' ) ) ) {
		return $translations;
	}

	include( BASED_TREE_URI . MCMSINC . '/version.php' ); // include an unmodified $mcms_version

	$api = translations_api( 'core', array( 'version' => $mcms_version ) );

	if ( is_mcms_error( $api ) || empty( $api['translations'] ) ) {
		return array();
	}

	$translations = array();
	// Key the array with the language code for now.
	foreach ( $api['translations'] as $translation ) {
		$translations[ $translation['language'] ] = $translation;
	}

	if ( ! defined( 'MCMS_INSTALLING' ) ) {
		set_site_transient( 'available_translations', $translations, 3 * HOUR_IN_SECONDS );
	}

	return $translations;
}

/**
 * Output the select form for the language selection on the installation screen.
 *
 * @since 4.0.0
 *
 * @global string $mcms_local_package
 *
 * @param array $languages Array of available languages (populated via the Translation API).
 */
function mcms_install_language_form( $languages ) {
	global $mcms_local_package;

	$installed_languages = get_available_languages();

	echo "<label class='screen-reader-text' for='language'>Select a default language</label>\n";
	echo "<select size='14' name='language' id='language'>\n";
	echo '<option value="" lang="en" selected="selected" data-continue="Continue" data-installed="1">English (United States)</option>';
	echo "\n";

	if ( ! empty( $mcms_local_package ) && isset( $languages[ $mcms_local_package ] ) ) {
		if ( isset( $languages[ $mcms_local_package ] ) ) {
			$language = $languages[ $mcms_local_package ];
			printf( '<option value="%s" lang="%s" data-continue="%s"%s>%s</option>' . "\n",
				esc_attr( $language['language'] ),
				esc_attr( current( $language['iso'] ) ),
				esc_attr( $language['strings']['continue'] ),
				in_array( $language['language'], $installed_languages ) ? ' data-installed="1"' : '',
				esc_html( $language['native_name'] ) );

			unset( $languages[ $mcms_local_package ] );
		}
	}

	foreach ( $languages as $language ) {
		printf( '<option value="%s" lang="%s" data-continue="%s"%s>%s</option>' . "\n",
			esc_attr( $language['language'] ),
			esc_attr( current( $language['iso'] ) ),
			esc_attr( $language['strings']['continue'] ),
			in_array( $language['language'], $installed_languages ) ? ' data-installed="1"' : '',
			esc_html( $language['native_name'] ) );
	}
	echo "</select>\n";
	echo '<p class="step"><span class="spinner"></span><input id="language-continue" type="submit" class="button button-primary button-large" value="Continue" /></p>';
}

/**
 * Download a language pack.
 *
 * @since 4.0.0
 *
 * @see mcms_get_available_translations()
 *
 * @param string $download Language code to download.
 * @return string|bool Returns the language code if successfully downloaded
 *                     (or already installed), or false on failure.
 */
function mcms_download_language_pack( $download ) {
	// Check if the translation is already installed.
	if ( in_array( $download, get_available_languages() ) ) {
		return $download;
	}

	if ( ! mcms_is_file_mod_allowed( 'download_language_pack' ) ) {
		return false;
	}

	// Confirm the translation is one we can download.
	$translations = mcms_get_available_translations();
	if ( ! $translations ) {
		return false;
	}
	foreach ( $translations as $translation ) {
		if ( $translation['language'] === $download ) {
			$translation_to_load = true;
			break;
		}
	}

	if ( empty( $translation_to_load ) ) {
		return false;
	}
	$translation = (object) $translation;

	require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
	$skin = new Automatic_Upgrader_Skin;
	$upgrader = new Language_Pack_Upgrader( $skin );
	$translation->type = 'core';
	$result = $upgrader->upgrade( $translation, array( 'clear_update_cache' => false ) );

	if ( ! $result || is_mcms_error( $result ) ) {
		return false;
	}

	return $translation->language;
}

/**
 * Check if MandarinCMS has access to the filesystem without asking for
 * credentials.
 *
 * @since 4.0.0
 *
 * @return bool Returns true on success, false on failure.
 */
function mcms_can_install_language_pack() {
	if ( ! mcms_is_file_mod_allowed( 'can_install_language_pack' ) ) {
		return false;
	}

	require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
	$skin = new Automatic_Upgrader_Skin;
	$upgrader = new Language_Pack_Upgrader( $skin );
	$upgrader->init();

	$check = $upgrader->fs_connect( array( MCMS_CONTENT_DIR, MCMS_LANG_DIR ) );

	if ( ! $check || is_mcms_error( $check ) ) {
		return false;
	}

	return true;
}
