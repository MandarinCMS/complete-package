<?php
/**
 * All the functions and classes in this file are deprecated.
 * You shouldn't use them. The functions and classes will be
 * removed in a later version.
 */

function mcmscf7_add_shortcode( $tag, $func, $has_name = false ) {
	mcmscf7_deprecated_function( __FUNCTION__, '4.6', 'mcmscf7_add_form_tag' );

	return mcmscf7_add_form_tag( $tag, $func, $has_name );
}

function mcmscf7_remove_shortcode( $tag ) {
	mcmscf7_deprecated_function( __FUNCTION__, '4.6', 'mcmscf7_remove_form_tag' );

	return mcmscf7_remove_form_tag( $tag );
}

function mcmscf7_do_shortcode( $content ) {
	mcmscf7_deprecated_function( __FUNCTION__, '4.6',
		'mcmscf7_replace_all_form_tags' );

	return mcmscf7_replace_all_form_tags( $content );
}

function mcmscf7_scan_shortcode( $cond = null ) {
	mcmscf7_deprecated_function( __FUNCTION__, '4.6', 'mcmscf7_scan_form_tags' );

	return mcmscf7_scan_form_tags( $cond );
}

class MCMSCF7_ShortcodeManager {

	private static $form_tags_manager;

	private function __construct() {}

	public static function get_instance() {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::get_instance' );

		self::$form_tags_manager = MCMSCF7_FormTagsManager::get_instance();
		return new self;
	}

	public function get_scanned_tags() {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::get_scanned_tags' );

		return self::$form_tags_manager->get_scanned_tags();
	}

	public function add_shortcode( $tag, $func, $has_name = false ) {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::add' );

		return self::$form_tags_manager->add( $tag, $func, $has_name );
	}

	public function remove_shortcode( $tag ) {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::remove' );

		return self::$form_tags_manager->remove( $tag );
	}

	public function normalize_shortcode( $content ) {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::normalize' );

		return self::$form_tags_manager->normalize( $content );
	}

	public function do_shortcode( $content, $exec = true ) {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::replace_all' );

		if ( $exec ) {
			return self::$form_tags_manager->replace_all( $content );
		} else {
			return self::$form_tags_manager->scan( $content );
		}
	}

	public function scan_shortcode( $content ) {
		mcmscf7_deprecated_function( __METHOD__, '4.6',
			'MCMSCF7_FormTagsManager::scan' );

		return self::$form_tags_manager->scan( $content );
	}
}

class MCMSCF7_Shortcode extends MCMSCF7_FormTag {

	public function __construct( $tag ) {
		mcmscf7_deprecated_function( 'MCMSCF7_Shortcode', '4.6', 'MCMSCF7_FormTag' );

		parent::__construct( $tag );
	}
}
