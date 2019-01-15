<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<p>';
/* translators: %1$s / %2$s: links to an article about duplicate content on jiiworks.net */
printf( __( 'If you\'re running a one author blog, the author archive will be exactly the same as your homepage. This is what\'s called a %1$sduplicate content problem%2$s.', 'mandarincms-seo' ), '<a href="https://jiiworks.net/duplicate-content">', '</a>' );
echo ' ';
/* translators: %s expands to <code>noindex, follow</code> */
printf( __( 'If this is the case on your site, you can choose to either disable it (which makes it redirect to the homepage), or to add %s to it so it doesn\'t show up in the search results.', 'mandarincms-seo' ), '<code>noindex,follow</code>' );
echo ' ';
echo __( 'Note that links to archives might be still output by your myskin and you would need to remove them separately.', 'mandarincms-seo' );
echo ' ';
_e( 'Date-based archives could in some cases also be seen as duplicate content.', 'mandarincms-seo' );
echo '</p>';

echo "<div id='author-archives-titles-metas'>";
echo '<h2>' . esc_html__( 'Author archives settings', 'mandarincms-seo' ) . '</h2>';
$yform->toggle_switch( 'disable-author', array(
	'off' => __( 'Enabled', 'mandarincms-seo' ),
	'on'  => __( 'Disabled', 'mandarincms-seo' ),
), __( 'Author archives', 'mandarincms-seo' ) );

echo "<div id='author-archives-titles-metas-content' class='archives-titles-metas-content'>";
$yform->textinput( 'title-author-mcmsseo', __( 'Title template', 'mandarincms-seo' ), 'template author-template' );
$yform->textarea( 'metadesc-author-mcmsseo', __( 'Meta description template', 'mandarincms-seo' ), array( 'class' => 'template author-template' ) );
if ( $options['usemetakeywords'] === true ) {
	$yform->textinput( 'metakey-author-mcmsseo', __( 'Meta keywords template', 'mandarincms-seo' ) );
}
$yform->toggle_switch( 'noindex-author-mcmsseo', $index_switch_values, __( 'Meta Robots', 'mandarincms-seo' ) );
echo '</div>';
echo '</div>';

echo '<br/>';

echo "<div id='date-archives-titles-metas'>";
echo '<h2>' . esc_html__( 'Date archives settings', 'mandarincms-seo' ) . '</h2>';
$yform->toggle_switch( 'disable-date', array(
	'off' => __( 'Enabled', 'mandarincms-seo' ),
	'on'  => __( 'Disabled', 'mandarincms-seo' ),
), __( 'Date archives', 'mandarincms-seo' ) );

echo "<div id='date-archives-titles-metas-content' class='archives-titles-metas-content'>";
$yform->textinput( 'title-archive-mcmsseo', __( 'Title template', 'mandarincms-seo' ), 'template date-template' );
$yform->textarea( 'metadesc-archive-mcmsseo', __( 'Meta description template', 'mandarincms-seo' ), array( 'class' => 'template date-template' ) );
$yform->toggle_switch( 'noindex-archive-mcmsseo', $index_switch_values, __( 'Meta Robots', 'mandarincms-seo' ) );
echo '</div>';
echo '</div>';

echo '<br/>';

echo '<div id="special-pages-titles-metas">';
echo '<h2>' . esc_html__( 'Special Pages', 'mandarincms-seo' ) . '</h2>';
/* translators: %s expands to <code>noindex, follow</code> */
echo '<p>' . sprintf( __( 'These pages will be %s by default, so they will never show up in search results.', 'mandarincms-seo' ), '<code>noindex, follow</code>' ) . '</p>';
echo '<p><strong>' . __( 'Search pages', 'mandarincms-seo' ) . '</strong><br/>';
$yform->textinput( 'title-search-mcmsseo', __( 'Title template', 'mandarincms-seo' ), 'template search-template' );
echo '</p>';
echo '<p><strong>' . __( '404 pages', 'mandarincms-seo' ) . '</strong><br/>';
$yform->textinput( 'title-404-mcmsseo', __( 'Title template', 'mandarincms-seo' ), 'template error404-template' );
echo '</p>';
echo '</div>';
