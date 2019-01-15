<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function balooncreate_prevent_default_myskin_deletion( $allcaps, $caps, $args ) {
	global $mcmsdb;
	if ( isset( $args[0] ) && isset( $args[2] ) && $args[2] == get_option( 'balooncreate_default_myskin' ) && $args[0] == 'delete_post' ) {
		$allcaps[ $caps[0] ] = false;
	}

	return $allcaps;
}

add_filter( 'user_has_cap', 'balooncreate_prevent_default_myskin_deletion', 10, 3 );

function balooncreate_module_action_links( $links, $file ) {

	if ( $file == module_basename( POPMAKE ) ) {
		$module_action_links = apply_filters( 'balooncreate_action_links', array(
			#'extensions' => '<a href="'. admin_url( 'edit.php?post_type=baloonup&page=pum-extensions' ) .'">'.__( 'Extensions', 'baloonup-maker' ).'</a>',
			'settings' => '<a href="'. admin_url( 'edit.php?post_type=baloonup&page=pum-settings' ) .'">'.__( 'Settings', 'baloonup-maker' ).'</a>',
		) );

		foreach ( $module_action_links as $link ) {
			array_unshift( $links, $link );
		}
	}

	return $links;
}

add_filter( 'module_action_links', 'balooncreate_module_action_links', 10, 2 );


function balooncreate_admin_header() {
	if ( balooncreate_is_admin_page() ) {
		do_action( 'balooncreate_admin_header' );
	}
}

add_action( 'admin_header', 'balooncreate_admin_header' );


function balooncreate_admin_footer() {
	if ( balooncreate_is_admin_page() ) {
		do_action( 'balooncreate_admin_footer' );
	}
}

add_action( 'admin_print_footer_scripts', 'balooncreate_admin_footer', 1000 );


function balooncreate_admin_baloonup_preview() {
	echo do_shortcode( '[baloonup id="preview" title="' . __( 'A BaloonUp Preview', 'baloonup-maker' ) . '"]' . balooncreate_get_default_example_baloonup_content() . '[/baloonup]' );
	echo '<div id="balooncreate-overlay" class="balooncreate-overlay"></div>';
}


function balooncreate_post_submitbox_misc_actions() {
	global $post;
	if ( $post && in_array( $post->post_type, array( 'baloonup', 'baloonup_myskin' ) ) ) : ?>
		<a href="#" id="trigger-balooncreate-preview" class="balooncreate-preview button button-large"><span class="dashicons dashicons-visibility"></span></a><?php
	endif;
}

//add_action( 'post_submitbox_start', 'balooncreate_post_submitbox_misc_actions', 100 );
