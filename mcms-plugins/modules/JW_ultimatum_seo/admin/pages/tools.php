<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$tool_page = (string) filter_input( INPUT_GET, 'tool' );

$yform = Ultimatum_Form::get_instance();
$yform->admin_header( false );

if ( '' === $tool_page ) {

	$tools = array(
		'bulk-editor' => array(
			'title' => __( 'Bulk editor', 'mandarincms-seo' ),
			'desc' => __( 'This tool allows you to quickly change titles and descriptions of your posts and pages without having to go into the editor for each page.', 'mandarincms-seo' ),
		),
		'import-export' => array(
			'title' => __( 'Import and Export', 'mandarincms-seo' ),
			'desc' => __( 'Import settings from other SEO modules and export your settings for re-use on (another) blog.', 'mandarincms-seo' ),
		),
	);
	if ( MCMSSEO_Utils::allow_system_file_edit() === true && ! is_multisite() ) {
		$tools['file-editor'] = array(
			'title' => __( 'File editor', 'mandarincms-seo' ),
			'desc' => __( 'This tool allows you to quickly change important files for your SEO, like your robots.txt and, if you have one, your .htaccess file.', 'mandarincms-seo' ),
		);
	}

	/*
		Temporary disabled. See: https://github.com/Ultimatum/mandarincms-seo/issues/4532

		$tools['recalculate'] = array(
			'href'    => '#TB_inline?width=300&height=150&inlineId=mcmsseo_recalculate',
			'attr'    => "id='mcmsseo_recalculate_link' class='thickbox'",
			'title'   => __( 'Recalculate SEO scores', 'mandarincms-seo' ),
			'desc'    => __( 'Recalculate SEO scores for all pieces of content with a focus keyword.', 'mandarincms-seo' ),
		);

		if ( filter_input( INPUT_GET, 'recalculate' ) === '1' ) {
			$tools['recalculate']['attr'] .= "data-open='open'";
		}
	*/

	/* translators: %1$s expands to Ultimatum SEO */
	echo '<p>', sprintf( __( '%1$s comes with some very powerful built-in tools:', 'mandarincms-seo' ), 'Ultimatum SEO' ), '</p>';

	asort( $tools );

	echo '<ul class="ul-disc">';

	$admin_url = admin_url( 'admin.php?page=mcmsseo_tools' );

	foreach ( $tools as $slug => $tool ) {
		$href = ( ! empty( $tool['href'] ) ) ? $admin_url . $tool['href'] : add_query_arg( array( 'tool' => $slug ) , $admin_url );
		$attr = ( ! empty( $tool['attr'] ) ) ? $tool['attr'] : '';

		echo '<li>';
		echo '<strong><a href="' , esc_attr( $href ) , '" ' , $attr , '>', esc_html( $tool['title'] ), '</a></strong><br/>';
		echo $tool['desc'];
		echo '</li>';
	}
	echo '</ul>';

	echo '<input type="hidden" id="mcmsseo_recalculate_nonce" name="mcmsseo_recalculate_nonce" value="' . mcms_create_nonce( 'mcmsseo_recalculate' ) . '" />';

}
else {
	echo '<a href="', admin_url( 'admin.php?page=mcmsseo_tools' ), '">', __( '&laquo; Back to Tools page', 'mandarincms-seo' ), '</a>';

	$tool_pages = array( 'bulk-editor', 'import-export' );

	if ( MCMSSEO_Utils::allow_system_file_edit() === true && ! is_multisite() ) {
		$tool_pages[] = 'file-editor';
	}

	if ( in_array( $tool_page, $tool_pages ) ) {
		require_once MCMSSEO_PATH . 'admin/views/tool-' . $tool_page . '.php';
	}
}

$yform->admin_footer( false );
