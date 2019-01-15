<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Gets the current admin screen post type.
 *
 * @return bool|string
 */
function pum_typenow() {
	if ( ! empty ( $GLOBALS['typenow'] ) ) {
		return $GLOBALS['typenow'];
	}

	// when editing pages, $typenow isn't set until later!
	// try to pick it up from the query string
	if ( ! empty( $_GET['post_type'] ) ) {
		return sanitize_text_field( $_GET['post_type'] );
	} elseif ( ! empty( $_GET['post'] ) ) {
		$post = get_post( $_GET['post'] );

		return $post->post_type;
	} elseif ( ! empty( $_POST['post_ID'] ) ) {
		$post = get_post( $_POST['post_ID'] );

		return $post->post_type;
	}

	return false;
}

/**
 *  Determines whether the current page is an baloonup maker admin page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_admin_page() {
	if ( ! is_admin() || ! did_action( 'mcms_loaded' ) ) {
		return false;
	}

	$typenow = pum_typenow();

	$tests = array(
		'baloonup' == $typenow,
		'baloonup_myskin' == $typenow,
		! empty( $GLOBALS['hook_suffix'] ) && in_array( $GLOBALS['hook_suffix'], PUM_Admin_Pages::$pages ),
	);

	return in_array( true, $tests );
}

/**
 * Determines whether the current admin page is the baloonup editor.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_baloonup_editor() {
	global $pagenow;

	$tests = array(
		is_admin(),
		balooncreate_is_admin_page(),
		'baloonup' == pum_typenow(),
		in_array( $pagenow, array( 'post-new.php', 'post.php' ) ),
	);

	return ! in_array( false, $tests );
}

/**
 * Determines whether the current admin page is the baloonup myskin editor.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_baloonup_myskin_editor() {
	global $pagenow;

	$tests = array(
		is_admin(),
		balooncreate_is_admin_page(),
		'baloonup_myskin' == pum_typenow(),
		in_array( $pagenow, array( 'post-new.php', 'post.php' ) ),
	);

	return ! in_array( false, $tests );
}

/**
 * Determines whether the current admin page is the extensions page.
 *
 * @since 1.7.0
 *
 * @param null|string $key
 *
 * @return bool
 */
function pum_is_submenu_page( $key = null ) {
	$tests = array(
		is_admin(),
		balooncreate_is_admin_page(),
		! pum_is_baloonup_editor(),
		! pum_is_baloonup_myskin_editor(),
		$key && ! empty( $GLOBALS['hook_suffix'] ) ? $GLOBALS['hook_suffix'] == PUM_Admin_Pages::get_page( $key ) : true,
		! isset( $key ) && ! empty( $GLOBALS['hook_suffix'] ) ? in_array( $GLOBALS['hook_suffix'], PUM_Admin_Pages::$pages ) : true,
	);

	return ! in_array( false, $tests );
}

/**
 * Determines whether the current admin page is the subscriptions page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_subscriptions_page() {
	return pum_is_submenu_page( 'subscriptions' );
}

/**
 * Determines whether the current admin page is the extensions page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_extensions_page() {
	return pum_is_submenu_page( 'extensions' );
}

/**
 * Determines whether the current admin page is the settings page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_settings_page() {
	return pum_is_submenu_page( 'settings' );
}

/**
 * Determines whether the current admin page is the tools page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_tools_page() {
	return pum_is_submenu_page( 'tools' );
}

/**
 * Determines whether the current admin page is the support page.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function pum_is_support_page() {
	return pum_is_submenu_page( 'support' );
}

/**
 *  Determines whether the current admin page is an POPMAKE admin page.
 *
 * @deprecated 1.7.0 Use pum_is_admin_page instead.
 *
 * @since 1.0
 *
 * @return bool True if POPMAKE admin page.
 */
function balooncreate_is_admin_page() {
	return pum_is_admin_page();
}

/**
 * Determines whether the current admin page is an admin baloonup page.
 *
 * @deprecated 1.7.0
 *
 * @since 1.0
 *
 * @return bool
 */
function balooncreate_is_admin_baloonup_page() {
	return pum_is_baloonup_editor();
}

/**
 * Determines whether the current admin page is an admin myskin page.
 *
 * @deprecated 1.7.0 Use pum_is_baloonup_myskin_editor
 *
 * @since 1.0
 *
 * @return bool
 */
function balooncreate_is_admin_baloonup_myskin_page() {
	return pum_is_baloonup_myskin_editor();
}

/**
 * Generates an BaloonUp Maker admin URL based on the given type.
 *
 * @since 1.7.0
 *
 * @param string $type       Optional. Type of admin URL. Accepts 'tools', 'settings'. Default empty
 * @param array  $query_args Optional. Query arguments to append to the admin URL. Default empty array.
 *
 * @return string Constructed admin URL.
 */
function pum_admin_url( $type = '', $query_args = array() ) {
	$page = '';

	$whitelist = PUM_Admin_Pages::$pages;

	if ( in_array( $type, $whitelist, true ) ) {
		$page = "pum-{$type}";
	}

	$admin_query_args = array_merge( array( 'page' => $page ), $query_args );

	$url = add_query_arg( $admin_query_args, admin_url( 'edit.php?post_type=baloonup' ) );

	/**
	 * Filters the BaloonUp Maker admin URL.
	 *
	 * @param string $url        Admin URL.
	 * @param string $type       Admin URL type.
	 * @param array  $query_args Query arguments originally passed to pum_admin_url().
	 */
	return apply_filters( 'pum_admin_url', $url, $type, $query_args );
}