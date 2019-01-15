<?php
// Exit if accessed directly

/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * @deprecated 1.7.0
 *
 * @param string $string
 *
 * @return string
 */
function balooncreate_get_label_singular( $string = '' ) {
	return '';
}

/**
 * @deprecated 1.7.0
 *
 * @param string $string
 *
 * @return string
 */
function balooncreate_get_label_plural( $string = '' ) {
	return '';
}

# region Actions
/**
 * Here to manage extensions (PUM-Videos) which used this filter to load their assets.
 *
 * @param null $baloonup_id
 */
function balooncreate_enqueue_scripts( $baloonup_id = null ) {
	$scripts_needed = apply_filters( 'balooncreate_enqueue_scripts', array(), $baloonup_id );
	foreach ( $scripts_needed as $script ) {
		if ( mcms_script_is( $script, 'registered' ) ) {
			mcms_enqueue_script( $script );
		}
	}
	$styles_needed = apply_filters( 'balooncreate_enqueue_styles', array(), $baloonup_id );
	foreach ( $styles_needed as $style ) {
		if ( mcms_style_is( $style, 'registered' ) ) {
			mcms_enqueue_style( $style );
		}
	}
}

add_action( 'balooncreate_preload_baloonup', 'balooncreate_enqueue_scripts' );
# endregion Ations

# region Filters

/**
 * Process deprecated filters.
 *
 * @param $settings
 * @param $baloonup_id
 *
 * @return mixed
 */
function pum_deprecated_balooncreate_settings_extensions_sanitize_filter( $settings = array() ) {
	if ( has_filter( 'balooncreate_settings_extensions_sanitize' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_settings_extensions_sanitize', '1.7.0', 'filter:pum_settings_sanitize' );
		/**
		 * @deprecated 1.7
		 *
		 * @param array $settings
		 * @param int   $baloonup_id
		 */
		$settings = apply_filters( 'balooncreate_settings_extensions_sanitize', $settings );
	}

	return $settings;
}

add_filter( 'pum_sanitize_settings', 'pum_deprecated_balooncreate_settings_extensions_sanitize_filter' );


/**
 * Process deprecated filters.
 *
 * @param $title
 * @param $baloonup_id
 *
 * @return mixed
 */
function pum_deprecated_get_the_baloonup_title_filter( $title, $baloonup_id ) {
	if ( has_filter( 'balooncreate_get_the_baloonup_title' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_get_the_baloonup_title', '1.7.0', 'filter:pum_baloonup_get_title' );
		/**
		 * @deprecated 1.7
		 *
		 * @param string $title
		 * @param int    $baloonup_id
		 */
		$title = apply_filters( 'balooncreate_get_the_baloonup_title', $title, $baloonup_id );
	}

	return $title;
}

add_filter( 'pum_baloonup_get_title', 'pum_deprecated_get_the_baloonup_title_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param $content
 * @param $baloonup_id
 *
 * @return mixed
 */
function pum_deprecated_get_the_baloonup_content_filter( $content, $baloonup_id ) {
	if ( has_filter( 'the_baloonup_content' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:the_baloonup_content', '1.7.0', 'filter:pum_baloonup_content' );
		/**
		 * @deprecated 1.7
		 *
		 * @param string $content
		 * @param int    $baloonup_id
		 */
		$content = apply_filters( 'the_baloonup_content', $content, $baloonup_id );
	}

	return $content;
}

add_filter( 'pum_baloonup_content', 'pum_deprecated_get_the_baloonup_content_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param $data_attr
 * @param $baloonup_id
 *
 * @return mixed
 */
function pum_deprecated_pum_baloonup_get_data_attr_filter( $data_attr = array(), $baloonup_id ) {
	if ( has_filter( 'pum_baloonup_get_data_attr' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:pum_baloonup_get_data_attr', '1.7.0', 'filter:pum_baloonup_data_attr' );
		/**
		 * @deprecated 1.7
		 *
		 * @param string $content
		 * @param int    $baloonup_id
		 */
		$data_attr = apply_filters( 'pum_baloonup_get_data_attr', $data_attr, $baloonup_id );
	}

	return $data_attr;
}

add_filter( 'pum_baloonup_data_attr', 'pum_deprecated_pum_baloonup_get_data_attr_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param int $myskin_id
 * @param int $baloonup_id
 *
 * @return int
 */
function pum_deprecated_get_the_baloonup_myskin_filter( $myskin_id, $baloonup_id ) {
	if ( has_filter( 'balooncreate_get_the_baloonup_myskin' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_get_the_baloonup_myskin', '1.7.0', 'filter:pum_baloonup_get_myskin_id' );
		/**
		 * @deprecated 1.7
		 *
		 * @param int $myskin_id
		 * @param int $baloonup_id
		 */
		$myskin_id = apply_filters( 'balooncreate_get_the_baloonup_myskin', $myskin_id, $baloonup_id );
	}

	return $myskin_id;
}

add_filter( 'pum_baloonup_get_myskin_id', 'pum_deprecated_get_the_baloonup_myskin_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param array $classes
 * @param int   $baloonup_id
 *
 * @return array
 */
function pum_deprecated_get_the_baloonup_classes_filter( $classes, $baloonup_id ) {
	if ( has_filter( 'balooncreate_get_the_baloonup_classes' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_get_the_baloonup_classes', '1.7.0', 'filter:pum_baloonup_container_classes' );
		/**
		 * @deprecated 1.7
		 *
		 * @param array $classes
		 * @param int   $baloonup_id
		 */
		$classes = apply_filters( 'balooncreate_get_the_baloonup_classes', $classes, $baloonup_id );
	}

	return $classes;
}

add_filter( 'pum_baloonup_container_classes', 'pum_deprecated_get_the_baloonup_classes_filter', 10, 2 );


/**
 * Process deprecated filters.
 *
 * @param array $classes
 * @param int   $baloonup_id
 *
 * @return array
 */
function pum_deprecated_pum_baloonup_get_classes_filter( $classes, $baloonup_id ) {
	if ( has_filter( 'pum_baloonup_get_classes' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:pum_baloonup_get_classes', '1.7.0', 'filter:pum_baloonup_container_classes' );
		/**
		 * @deprecated 1.7
		 *
		 * @param array $classes
		 * @param int   $baloonup_id
		 */
		$classes = apply_filters( 'pum_baloonup_get_classes', $classes, $baloonup_id );
	}

	return $classes;
}

add_filter( 'pum_baloonup_classes', 'pum_deprecated_pum_baloonup_get_classes_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param array $data_attr
 * @param int   $baloonup_id
 *
 * @return array
 */
function pum_deprecated_get_the_baloonup_data_attr_filter( $data_attr, $baloonup_id ) {
	if ( has_filter( 'balooncreate_get_the_baloonup_data_attr' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_get_the_baloonup_data_attr', '1.7.0', 'filter:pum_baloonup_data_attr' );
		/**
		 * @deprecated 1.7
		 *
		 * @param array $data_attr
		 * @param int   $baloonup_id
		 */
		$data_attr = apply_filters( 'balooncreate_get_the_baloonup_data_attr', $data_attr, $baloonup_id );
	}

	return $data_attr;
}

add_filter( 'pum_baloonup_data_attr', 'pum_deprecated_get_the_baloonup_data_attr_filter', 10, 2 );

/**
 * Process deprecated filters.
 *
 * @param bool $show
 * @param int  $baloonup_id
 *
 * @return array
 */
function pum_deprecated_show_close_button_filter( $show, $baloonup_id ) {
	if ( has_filter( 'balooncreate_show_close_button' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'filter:balooncreate_show_close_button', '1.7.0', 'filter:pum_baloonup_show_close_button' );
		/**
		 * @deprecated 1.7
		 *
		 * @param bool $show
		 * @param int  $baloonup_id
		 */
		$show = apply_filters( 'balooncreate_show_close_button', $show, $baloonup_id );
	}

	return $show;
}

add_filter( 'pum_baloonup_show_close_button', 'pum_deprecated_show_close_button_filter', 10, 2 );
# endregion Filters

# region Functions
/**
 * Returns the cookie fields used for cookie options.
 *
 * @deprecated 1.7.0 Use PUM_Cookies::instance()->cookie_fields() instead.
 *
 * @return array
 */
function pum_get_cookie_fields() {
	return PUM_Cookies::instance()->cookie_fields();
}

/**
 * Returns an array of args for registering coo0kies.
 *
 * @deprecated 1.7.0 Use PUM_Cookies::instance()->cookie_fields() instead.
 *
 * @return array
 */
function pum_get_cookies() {
	return PUM_Cookies::instance()->get_cookies();
}

/**
 * Returns the cookie fields used for trigger options.
 *
 * @deprecated v1.7.0 Use PUM_Triggers::instance()->cookie_fields() instead.
 *
 * @return array
 */
function pum_trigger_cookie_fields() {
	return PUM_Triggers::instance()->cookie_fields();
}

/**
 * Returns the cookie field used for trigger options.
 *
 * @deprecated v1.7.0 Use PUM_Triggers::instance()->cookie_field() instead.
 *
 * @return array
 */
function pum_trigger_cookie_field() {
	return PUM_Triggers::instance()->cookie_field();
}

/**
 * Returns an array of section labels for all triggers.
 *
 * @deprecated v1.7.0 Use PUM_Triggers::instance()->get_tabs() instead.
 *
 * @return array
 */
function pum_get_trigger_section_labels() {
	return PUM_Triggers::instance()->get_tabs();
}
# endregion Functions