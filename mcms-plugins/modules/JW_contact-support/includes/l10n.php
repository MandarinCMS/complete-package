<?php

function mcmscf7_l10n() {
	static $l10n = array();

	if ( ! empty( $l10n ) ) {
		return $l10n;
	}

	if ( ! is_admin() ) {
		return $l10n;
	}

	require_once( BASED_TREE_URI . 'mcms-admin/includes/translation-install.php' );

	$api = translations_api( 'modules', array(
		'slug' => 'jw-contact-support',
		'version' => MCMSCF7_VERSION,
	) );

	if ( is_mcms_error( $api ) || empty( $api['translations'] ) ) {
		return $l10n;
	}

	foreach ( (array) $api['translations'] as $translation ) {
		if ( ! empty( $translation['language'] )
		&& ! empty( $translation['english_name'] ) ) {
			$l10n[$translation['language']] = $translation['english_name'];
		}
	}

	return $l10n;
}

function mcmscf7_is_valid_locale( $locale ) {
	$pattern = '/^[a-z]{2,3}(?:_[a-zA-Z_]{2,})?$/';
	return (bool) preg_match( $pattern, $locale );
}

function mcmscf7_is_rtl( $locale = '' ) {
	static $rtl_locales = array(
		'ar' => 'Arabic',
		'ary' => 'Moroccan Arabic',
		'azb' => 'South Azerbaijani',
		'fa_IR' => 'Persian',
		'haz' => 'Hazaragi',
		'he_IL' => 'Hebrew',
		'ps' => 'Pashto',
		'ug_CN' => 'Uighur',
	);

	if ( empty( $locale ) && function_exists( 'is_rtl' ) ) {
		return is_rtl();
	}

	if ( empty( $locale ) ) {
		$locale = get_locale();
	}

	return isset( $rtl_locales[$locale] );
}

function mcmscf7_load_textdomain( $locale = null ) {
	global $l10n;

	$domain = 'jw-contact-support';

	if ( ( is_admin() ? get_user_locale() : get_locale() ) === $locale ) {
		$locale = null;
	}

	if ( empty( $locale ) ) {
		if ( is_textdomain_loaded( $domain ) ) {
			return true;
		} else {
			return load_module_textdomain( $domain, false, $domain . '/languages' );
		}
	} else {
		$mo_orig = $l10n[$domain];
		unload_textdomain( $domain );

		$mofile = $domain . '-' . $locale . '.mo';
		$path = MCMS_MODULE_DIR . '/' . $domain . '/languages';

		if ( $loaded = load_textdomain( $domain, $path . '/'. $mofile ) ) {
			return $loaded;
		} else {
			$mofile = MCMS_LANG_DIR . '/modules/' . $mofile;
			return load_textdomain( $domain, $mofile );
		}

		$l10n[$domain] = $mo_orig;
	}

	return false;
}
