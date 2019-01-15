<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform                = Ultimatum_Form::get_instance();
$yform->currentoption = 'mcmsseo_permalinks';

echo '<h2>', __( 'Change URLs', 'mandarincms-seo' ), '</h2>';

$remove_buttons = array( __( 'Keep', 'mandarincms-seo' ), __( 'Remove', 'mandarincms-seo' ) );
/* translators: %s expands to <code>/category/</code> */
$yform->light_switch(
	'stripcategorybase',
	sprintf( __( 'Strip the category base (usually %s) from the category URL.', 'mandarincms-seo' ), '<code>/category/</code>' ),
	$remove_buttons,
	false
);

$redirect_buttons = array( __( 'No redirect', 'mandarincms-seo' ), __( 'Redirect', 'mandarincms-seo' ) );
$yform->light_switch( 'redirectattachment', __( 'Redirect attachment URLs to parent post URL.', 'mandarincms-seo' ), $redirect_buttons );
echo '<p>' . __( 'Attachments to posts are stored in the database as posts, this means they\'re accessible under their own URLs if you do not redirect them, enabling this will redirect them to the post they were attached to.', 'mandarincms-seo' ) . '</p>';

echo '<h2>', __( 'Clean up permalinks', 'mandarincms-seo' ), '</h2>';
$yform->light_switch( 'cleanslugs', __( 'Stop words in slugs.', 'mandarincms-seo' ), $remove_buttons, false );
echo '<p>' . __( 'This helps you to create cleaner URLs by automatically removing the stopwords from them.', 'mandarincms-seo' ) . '</p>';

/* translators: %s expands to <code>?replytocom</code> */
$yform->light_switch( 'cleanreplytocom', sprintf( __( 'Remove the %s variables.', 'mandarincms-seo' ), '<code>?replytocom</code>' ), $remove_buttons, false );
echo '<p>' . __( 'This prevents threaded replies from working when the user has JavaScript disabled, but on a large site can mean a <em>huge</em> improvement in crawl efficiency for search engines when you have a lot of comments.', 'mandarincms-seo' ) . '</p>';

$options = MCMSSEO_Options::get_all();
if ( substr( get_option( 'permalink_structure' ), -1 ) !== '/' && $options['trailingslash'] ) {
	$yform->light_switch( 'trailingslash', __( 'Enforce a trailing slash on all category and tag URLs', 'mandarincms-seo' ) );
	echo '<p><strong>' . __( 'Note: this feature has been deprecated, as the SEO value is close to 0 these days. If you disable it you will not be able to put it back on.', 'mandarincms-seo' ) . '</strong></p>';
	/* translators: %1$s expands to <code>.html</code>, %2$s expands to <code>/</code> */
	echo '<p>' . sprintf( __( 'If you choose a permalink for your posts with %1$s, or anything else but a %2$s at the end, this will force MandarinCMS to add a trailing slash to non-post pages nonetheless.', 'mandarincms-seo' ), '<code>.html</code>', '<code>/</code>' ) . '</p>';
}

$yform->light_switch( 'cleanpermalinks', __( 'Redirect ugly URLs to clean permalinks. (Not recommended in many cases!)', 'mandarincms-seo' ), $redirect_buttons );
echo '<p>' . __( 'People make mistakes in their links towards you sometimes, or unwanted parameters are added to the end of your URLs, this allows you to redirect them all away. Please note that while this is a feature that is actively maintained, it is known to break several modules, and should for that reason be the first feature you disable when you encounter issues after installing this module.', 'mandarincms-seo' ) . '</p>';

echo '<div id="cleanpermalinksdiv">';
$yform->light_switch( 'cleanpermalink-googlesitesearch', __( 'Prevent cleaning out Google Site Search URLs.', 'mandarincms-seo' ) );
echo '<p>' . __( 'Google Site Search URLs look weird, and ugly, but if you\'re using Google Site Search, you probably do not want them cleaned out.', 'mandarincms-seo' ) . '</p>';

$yform->light_switch( 'cleanpermalink-googlecampaign', __( 'Prevent cleaning out Google Analytics Campaign & Google AdWords Parameters.', 'mandarincms-seo' ) );
/* translators: %s expands to <code>?utm_</code> */
echo '<p>' . sprintf( __( 'If you use Google Analytics campaign parameters starting with %s, check this box. However, you\'re advised not to use these. Instead, use the version with a hash.', 'mandarincms-seo' ), '<code>?utm_</code>' ) . '</p>';

$yform->textinput( 'cleanpermalink-extravars', __( 'Other variables not to clean', 'mandarincms-seo' ) );
echo '<p>' . __( 'You might have extra variables you want to prevent from cleaning out, add them here, comma separated.', 'mandarincms-seo' ) . '</p>';
echo '</div>';
