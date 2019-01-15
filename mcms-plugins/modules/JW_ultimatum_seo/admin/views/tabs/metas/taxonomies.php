<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
	foreach ( $taxonomies as $tax ) {
		// Explicitly hide all the core taxonomies we never want to do stuff for.
		if ( in_array( $tax->name, array( 'link_category', 'nav_menu' ) ) ) {
			continue;
		}

		echo '<h2>' . esc_html( ucfirst( $tax->labels->name ) ) . '</h2>';
		if ( $tax->name === 'post_format' ) {
			$yform->light_switch(
				'disable-post_format',
				__( 'Format-based archives', 'mandarincms-seo' ),
				array( __( 'Enabled', 'mandarincms-seo' ), __( 'Disabled', 'mandarincms-seo' ) ),
				false
			);
		}
		echo "<div id='" . esc_attr( $tax->name ) . "-titles-metas'>";
		$yform->textinput( 'title-tax-' . $tax->name, __( 'Title template', 'mandarincms-seo' ), 'template taxonomy-template' );
		$yform->textarea( 'metadesc-tax-' . $tax->name, __( 'Meta description template', 'mandarincms-seo' ), array( 'class' => 'template taxonomy-template' ) );
		if ( $options['usemetakeywords'] === true ) {
			$yform->textinput( 'metakey-tax-' . $tax->name, __( 'Meta keywords template', 'mandarincms-seo' ) );
		}
		$yform->toggle_switch( 'noindex-tax-' . $tax->name, $index_switch_values, __( 'Meta Robots', 'mandarincms-seo' ) );
		if ( $tax->name !== 'post_format' ) {
			/* translators: %1$s expands to Ultimatum SEO */
			$yform->toggle_switch( 'hideeditbox-tax-' . $tax->name,
				array(
					'off' => __( 'Show', 'mandarincms-seo' ),
					'on'  => __( 'Hide', 'mandarincms-seo' ),
					/* translators: %1$s expands to Ultimatum SEO */
				), sprintf( __( '%1$s Meta Box', 'mandarincms-seo' ), 'Ultimatum SEO' ) );
		}
		/**
		 * Allow adding custom checkboxes to the admin meta page - Taxonomies tab
		 *
		 * @api  MCMSSEO_Admin_Pages  $yform  The MCMSSEO_Admin_Pages object
		 * @api  Object             $tax    The taxonomy
		 */
		do_action( 'mcmsseo_admin_page_meta_taxonomies', $yform, $tax );
		echo '<br/><br/>';
		echo '</div>';
	}
	unset( $tax );
}
unset( $taxonomies );
