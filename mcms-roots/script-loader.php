<?php
/**
 * MandarinCMS scripts and styles default loader.
 *
 * Several constants are used to manage the loading, concatenating and compression of scripts and CSS:
 * define('SCRIPT_DEBUG', true); loads the development (non-minified) versions of all scripts and CSS, and disables compression and concatenation,
 * define('CONCATENATE_SCRIPTS', false); disables compression and concatenation of scripts and CSS,
 * define('COMPRESS_SCRIPTS', false); disables compression of scripts,
 * define('COMPRESS_CSS', false); disables compression of CSS,
 * define('ENFORCE_GZIP', true); forces gzip for compression (default is deflate).
 *
 * The globals $concatenate_scripts, $compress_scripts and $compress_css can be set by modules
 * to temporarily override the above settings. Also a compression test is run once and the result is saved
 * as option 'can_compress_scripts' (0/1). The test will run again if that option is deleted.
 *
 * @package MandarinCMS
 */

/** MandarinCMS Dependency Class */
require( BASED_TREE_URI . MCMSINC . '/class-mcms-dependency.php' );

/** MandarinCMS Dependencies Class */
require( BASED_TREE_URI . MCMSINC . '/class.mcms-dependencies.php' );

/** MandarinCMS Scripts Class */
require( BASED_TREE_URI . MCMSINC . '/class.mcms-scripts.php' );

/** MandarinCMS Scripts Functions */
require( BASED_TREE_URI . MCMSINC . '/functions.mcms-scripts.php' );

/** MandarinCMS Styles Class */
require( BASED_TREE_URI . MCMSINC . '/class.mcms-styles.php' );

/** MandarinCMS Styles Functions */
require( BASED_TREE_URI . MCMSINC . '/functions.mcms-styles.php' );

/**
 * Register all MandarinCMS scripts.
 *
 * Localizes some of them.
 * args order: `$scripts->add( 'handle', 'url', 'dependencies', 'query-string', 1 );`
 * when last arg === 1 queues the script for the footer
 *
 * @since 2.6.0
 *
 * @param MCMS_Scripts $scripts MCMS_Scripts object.
 */
function mcms_default_scripts( &$scripts ) {
	include( BASED_TREE_URI . MCMSINC . '/version.php' ); // include an unmodified $mcms_version

	$develop_src = false !== strpos( $mcms_version, '-src' );

	if ( ! defined( 'SCRIPT_DEBUG' ) ) {
		define( 'SCRIPT_DEBUG', $develop_src );
	}

	if ( ! $guessurl = site_url() ) {
		$guessed_url = true;
		$guessurl = mcms_guess_url();
	}

	$scripts->base_url = $guessurl;
	$scripts->content_url = defined('MCMS_CONTENT_URL')? MCMS_CONTENT_URL : '';
	$scripts->default_version = get_bloginfo( 'version' );
	$scripts->default_dirs = array('/mcms-admin/js/', '/mcms-roots/js/');

	$suffix = SCRIPT_DEBUG ? '' : '.min';
	$dev_suffix = $develop_src ? '' : '.min';

	$scripts->add( 'utils', "/mcms-roots/js/utils$suffix.js" );
	did_action( 'init' ) && $scripts->localize( 'utils', 'userSettings', array(
		'url' => (string) SITECOOKIEPATH,
		'uid' => (string) get_current_user_id(),
		'time' => (string) time(),
		'secure' => (string) ( 'https' === parse_url( site_url(), PHP_URL_SCHEME ) ),
	) );

	$scripts->add( 'common', "/mcms-admin/js/common$suffix.js", array('jquery', 'hoverIntent', 'utils'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'common', 'commonL10n', array(
		'warnDelete'   => __( "You are about to permanently delete these items from your site.\nThis action cannot be undone.\n 'Cancel' to stop, 'OK' to delete." ),
		'dismiss'      => __( 'Dismiss this notice.' ),
		'collapseMenu' => __( 'Collapse Main menu' ),
		'expandMenu'   => __( 'Expand Main menu' ),
	) );

	$scripts->add( 'mcms-a11y', "/mcms-roots/js/mcms-a11y$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'sack', "/mcms-roots/js/tw-sack$suffix.js", array(), '1.6.1', 1 );

	$scripts->add( 'quicktags', "/mcms-roots/js/quicktags$suffix.js", array(), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'quicktags', 'quicktagsL10n', array(
		'closeAllOpenTags'      => __( 'Close all open tags' ),
		'closeTags'             => __( 'close tags' ),
		'enterURL'              => __( 'Enter the URL' ),
		'enterImageURL'         => __( 'Enter the URL of the image' ),
		'enterImageDescription' => __( 'Enter a description of the image' ),
		'textdirection'         => __( 'text direction' ),
		'toggleTextdirection'   => __( 'Toggle Editor Text Direction' ),
		'dfw'                   => __( 'Distraction-free writing mode' ),
		'strong'          => __( 'Bold' ),
		'strongClose'     => __( 'Close bold tag' ),
		'em'              => __( 'Italic' ),
		'emClose'         => __( 'Close italic tag' ),
		'link'            => __( 'Insert link' ),
		'blockquote'      => __( 'Blockquote' ),
		'blockquoteClose' => __( 'Close blockquote tag' ),
		'del'             => __( 'Deleted text (strikethrough)' ),
		'delClose'        => __( 'Close deleted text tag' ),
		'ins'             => __( 'Inserted text' ),
		'insClose'        => __( 'Close inserted text tag' ),
		'image'           => __( 'Insert image' ),
		'ul'              => __( 'Bulleted list' ),
		'ulClose'         => __( 'Close bulleted list tag' ),
		'ol'              => __( 'Numbered list' ),
		'olClose'         => __( 'Close numbered list tag' ),
		'li'              => __( 'List item' ),
		'liClose'         => __( 'Close list item tag' ),
		'code'            => __( 'Code' ),
		'codeClose'       => __( 'Close code tag' ),
		'more'            => __( 'Insert Read More tag' ),
	) );

	$scripts->add( 'colorpicker', "/mcms-roots/js/colorpicker$suffix.js", array('prototype'), '3517m' );

	$scripts->add( 'editor', "/mcms-admin/js/editor$suffix.js", array('utils','jquery'), false, 1 );

	// Back-compat for old DFW. To-do: remove at the end of 2016.
	$scripts->add( 'mcms-fullscreen-stub', "/mcms-admin/js/mcms-fullscreen-stub$suffix.js", array(), false, 1 );

	$scripts->add( 'mcms-ajax-response', "/mcms-roots/js/mcms-ajax-response$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mcms-ajax-response', 'mcmsAjax', array(
		'noPerm' => __('Sorry, you are not allowed to do that.'),
		'broken' => __('Something went wrong.')
	) );

	$scripts->add( 'mcms-api-request', "/mcms-roots/js/api-request$suffix.js", array( 'jquery' ), false, 1 );
	// `mcmsApiSettings` is also used by `mcms-api`, which depends on this script.
	did_action( 'init' ) && $scripts->localize( 'mcms-api-request', 'mcmsApiSettings', array(
		'root'          => esc_url_raw( get_rest_url() ),
		'nonce'         => ( mcms_installing() && ! is_multisite() ) ? '' : mcms_create_nonce( 'mcms_rest' ),
		'versionString' => 'mcms/v2/',
	) );

	$scripts->add( 'mcms-pointer', "/mcms-roots/js/mcms-pointer$suffix.js", array( 'jquery-ui-widget', 'jquery-ui-position' ), '20111129a', 1 );
	did_action( 'init' ) && $scripts->localize( 'mcms-pointer', 'mcmsPointerL10n', array(
		'dismiss' => __('Dismiss'),
	) );

	$scripts->add( 'autosave', "/mcms-roots/js/autosave$suffix.js", array('heartbeat'), false, 1 );

	$scripts->add( 'heartbeat', "/mcms-roots/js/heartbeat$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'heartbeat', 'heartbeatSettings',
		/**
		 * Filters the Heartbeat settings.
		 *
		 * @since 3.6.0
		 *
		 * @param array $settings Heartbeat settings array.
		 */
		apply_filters( 'heartbeat_settings', array() )
	);

	$scripts->add( 'mcms-auth-check', "/mcms-roots/js/mcms-auth-check$suffix.js", array('heartbeat'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mcms-auth-check', 'authcheckL10n', array(
		'beforeunload' => __('Your session has expired. You can log in again from this page or go to the login page.'),

		/**
		 * Filters the authentication check interval.
		 *
		 * @since 3.6.0
		 *
		 * @param int $interval The interval in which to check a user's authentication.
		 *                      Default 3 minutes in seconds, or 180.
		 */
		'interval' => apply_filters( 'mcms_auth_check_interval', 3 * MINUTE_IN_SECONDS ),
	) );

	$scripts->add( 'mcms-lists', "/mcms-roots/js/mcms-lists$suffix.js", array( 'mcms-ajax-response', 'jquery-color' ), false, 1 );

	// MandarinCMS no longer uses or bundles Prototype or script.aculo.us. These are now pulled from an external source.
	$scripts->add( 'prototype', 'https://ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js', array(), '1.7.1');
	$scripts->add( 'scriptaculous-root', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js', array('prototype'), '1.9.0');
	$scripts->add( 'scriptaculous-builder', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/builder.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-dragdrop', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/dragdrop.js', array('scriptaculous-builder', 'scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-effects', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/effects.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-slider', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/slider.js', array('scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-sound', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/sound.js', array( 'scriptaculous-root' ), '1.9.0' );
	$scripts->add( 'scriptaculous-controls', 'https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/controls.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous', false, array('scriptaculous-dragdrop', 'scriptaculous-slider', 'scriptaculous-controls') );

	// not used in core, replaced by Jcrop.js
	$scripts->add( 'cropper', '/mcms-roots/js/crop/cropper.js', array('scriptaculous-dragdrop') );

	// jQuery
	$scripts->add( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.12.4' );
	$scripts->add( 'jquery-core', '/mcms-roots/js/jquery/jquery.js', array(), '1.12.4' );
	$scripts->add( 'jquery-migrate', "/mcms-roots/js/jquery/jquery-migrate$suffix.js", array(), '1.4.1' );

	// full jQuery UI
	$scripts->add( 'jquery-ui-core', "/mcms-roots/js/jquery/ui/core$dev_suffix.js", array('jquery'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-core', "/mcms-roots/js/jquery/ui/effect$dev_suffix.js", array('jquery'), '1.11.4', 1 );

	$scripts->add( 'jquery-effects-blind', "/mcms-roots/js/jquery/ui/effect-blind$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-bounce', "/mcms-roots/js/jquery/ui/effect-bounce$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-clip', "/mcms-roots/js/jquery/ui/effect-clip$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-drop', "/mcms-roots/js/jquery/ui/effect-drop$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-explode', "/mcms-roots/js/jquery/ui/effect-explode$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-fade', "/mcms-roots/js/jquery/ui/effect-fade$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-fold', "/mcms-roots/js/jquery/ui/effect-fold$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-highlight', "/mcms-roots/js/jquery/ui/effect-highlight$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-puff', "/mcms-roots/js/jquery/ui/effect-puff$dev_suffix.js", array('jquery-effects-core', 'jquery-effects-scale'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-pulsate', "/mcms-roots/js/jquery/ui/effect-pulsate$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-scale', "/mcms-roots/js/jquery/ui/effect-scale$dev_suffix.js", array('jquery-effects-core', 'jquery-effects-size'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-shake', "/mcms-roots/js/jquery/ui/effect-shake$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-size', "/mcms-roots/js/jquery/ui/effect-size$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-slide', "/mcms-roots/js/jquery/ui/effect-slide$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-transfer', "/mcms-roots/js/jquery/ui/effect-transfer$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );

	$scripts->add( 'jquery-ui-accordion', "/mcms-roots/js/jquery/ui/accordion$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-autocomplete', "/mcms-roots/js/jquery/ui/autocomplete$dev_suffix.js", array( 'jquery-ui-menu', 'mcms-a11y' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-button', "/mcms-roots/js/jquery/ui/button$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-datepicker', "/mcms-roots/js/jquery/ui/datepicker$dev_suffix.js", array('jquery-ui-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-dialog', "/mcms-roots/js/jquery/ui/dialog$dev_suffix.js", array('jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-button', 'jquery-ui-position'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-draggable', "/mcms-roots/js/jquery/ui/draggable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-droppable', "/mcms-roots/js/jquery/ui/droppable$dev_suffix.js", array('jquery-ui-draggable'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-menu', "/mcms-roots/js/jquery/ui/menu$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-mouse', "/mcms-roots/js/jquery/ui/mouse$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-position', "/mcms-roots/js/jquery/ui/position$dev_suffix.js", array('jquery'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-progressbar', "/mcms-roots/js/jquery/ui/progressbar$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-resizable', "/mcms-roots/js/jquery/ui/resizable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-selectable', "/mcms-roots/js/jquery/ui/selectable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-selectmenu', "/mcms-roots/js/jquery/ui/selectmenu$dev_suffix.js", array('jquery-ui-menu'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-slider', "/mcms-roots/js/jquery/ui/slider$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-sortable', "/mcms-roots/js/jquery/ui/sortable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-spinner', "/mcms-roots/js/jquery/ui/spinner$dev_suffix.js", array( 'jquery-ui-button' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-tabs', "/mcms-roots/js/jquery/ui/tabs$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-tooltip', "/mcms-roots/js/jquery/ui/tooltip$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-widget', "/mcms-roots/js/jquery/ui/widget$dev_suffix.js", array('jquery'), '1.11.4', 1 );

	// Strings for 'jquery-ui-autocomplete' live region messages
	did_action( 'init' ) && $scripts->localize( 'jquery-ui-autocomplete', 'uiAutocompleteL10n', array(
		'noResults' => __( 'No results found.' ),
		/* translators: Number of results found when using jQuery UI Autocomplete */
		'oneResult' => __( '1 result found. Use up and down arrow keys to navigate.' ),
		/* translators: %d: Number of results found when using jQuery UI Autocomplete */
		'manyResults' => __( '%d results found. Use up and down arrow keys to navigate.' ),
		'itemSelected' => __( 'Item selected.' ),
	) );

	// deprecated, not used in core, most functionality is included in jQuery 1.3
	$scripts->add( 'jquery-form', "/mcms-roots/js/jquery/jquery.form$suffix.js", array('jquery'), '4.2.1', 1 );

	// jQuery modules
	$scripts->add( 'jquery-color', "/mcms-roots/js/jquery/jquery.color.min.js", array('jquery'), '2.1.1', 1 );
	$scripts->add( 'schedule', '/mcms-roots/js/jquery/jquery.schedule.js', array('jquery'), '20m', 1 );
	$scripts->add( 'jquery-query', "/mcms-roots/js/jquery/jquery.query.js", array('jquery'), '2.1.7', 1 );
	$scripts->add( 'jquery-serialize-object', "/mcms-roots/js/jquery/jquery.serialize-object.js", array('jquery'), '0.2', 1 );
	$scripts->add( 'jquery-hotkeys', "/mcms-roots/js/jquery/jquery.hotkeys$suffix.js", array('jquery'), '0.0.2m', 1 );
	$scripts->add( 'jquery-table-hotkeys', "/mcms-roots/js/jquery/jquery.table-hotkeys$suffix.js", array('jquery', 'jquery-hotkeys'), false, 1 );
	$scripts->add( 'jquery-touch-punch', "/mcms-roots/js/jquery/jquery.ui.touch-punch.js", array('jquery-ui-widget', 'jquery-ui-mouse'), '0.2.2', 1 );

	// Not used any more, registered for backwards compatibility.
	$scripts->add( 'suggest', "/mcms-roots/js/jquery/suggest$suffix.js", array('jquery'), '1.1-20110113', 1 );

	// Masonry v2 depended on jQuery. v3 does not. The older jquery-masonry handle is a shiv.
	// It sets jQuery as a dependency, as the myskin may have been implicitly loading it this way.
	$scripts->add( 'imagesloaded', "/mcms-roots/js/imagesloaded.min.js", array(), '3.2.0', 1 );
	$scripts->add( 'masonry', "/mcms-roots/js/masonry.min.js", array( 'imagesloaded' ), '3.3.2', 1 );
	$scripts->add( 'jquery-masonry', "/mcms-roots/js/jquery/jquery.masonry$dev_suffix.js", array( 'jquery', 'masonry' ), '3.1.2b', 1 );

	$scripts->add( 'thickbox', "/mcms-roots/js/thickbox/thickbox.js", array('jquery'), '3.1-20121105', 1 );
	did_action( 'init' ) && $scripts->localize( 'thickbox', 'thickboxL10n', array(
		'next' => __('Next &gt;'),
		'prev' => __('&lt; Prev'),
		'image' => __('Image'),
		'of' => __('of'),
		'close' => __('Close'),
		'noiframes' => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
		'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
	) );

	$scripts->add( 'jcrop', "/mcms-roots/js/jcrop/jquery.Jcrop.min.js", array('jquery'), '0.9.12');

	$scripts->add( 'swfobject', "/mcms-roots/js/swfobject.js", array(), '2.2-20120417');

	// Error messages for Plupload.
	$uploader_l10n = array(
		'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
		'file_exceeds_size_limit' => __('%s exceeds the maximum upload size for this site.'),
		'zero_byte_file' => __('This file is empty. Please try another.'),
		'invalid_filetype' => __('Sorry, this file type is not permitted for security reasons.'),
		'not_an_image' => __('This file is not an image. Please try another.'),
		'image_memory_exceeded' => __('Memory exceeded. Please try another smaller file.'),
		'image_dimensions_exceeded' => __('This is larger than the maximum size. Please try another.'),
		'default_error' => __('An error occurred in the upload. Please try again later.'),
		'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
		'upload_limit_exceeded' => __('You may only upload 1 file.'),
		'http_error' => __('HTTP error.'),
		'upload_failed' => __('Upload failed.'),
		/* translators: 1: Opening link tag, 2: Closing link tag */
		'big_upload_failed' => __('Please try uploading this file with the %1$sbrowser uploader%2$s.'),
		'big_upload_queued' => __('%s exceeds the maximum upload size for the multi-file uploader when used in your browser.'),
		'io_error' => __('IO error.'),
		'security_error' => __('Security error.'),
		'file_cancelled' => __('File canceled.'),
		'upload_stopped' => __('Upload stopped.'),
		'dismiss' => __('Dismiss'),
		'crunching' => __('Crunching&hellip;'),
		'deleted' => __('moved to the trash.'),
		'error_uploading' => __('&#8220;%s&#8221; has failed to upload.')
	);

	$scripts->add( 'moxiejs', "/mcms-roots/js/plupload/moxie$suffix.js", array(), '1.3.5' );
	$scripts->add( 'plupload', "/mcms-roots/js/plupload/plupload$suffix.js", array( 'moxiejs' ), '2.1.9' );
	// Back compat handles:
	foreach ( array( 'all', 'html5', 'flash', 'silverlight', 'html4' ) as $handle ) {
		$scripts->add( "plupload-$handle", false, array( 'plupload' ), '2.1.1' );
	}

	$scripts->add( 'plupload-handlers', "/mcms-roots/js/plupload/handlers$suffix.js", array( 'plupload', 'jquery' ) );
	did_action( 'init' ) && $scripts->localize( 'plupload-handlers', 'pluploadL10n', $uploader_l10n );

	$scripts->add( 'mcms-plupload', "/mcms-roots/js/plupload/mcms-plupload$suffix.js", array( 'plupload', 'jquery', 'json2', 'media-models' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mcms-plupload', 'pluploadL10n', $uploader_l10n );

	// keep 'swfupload' for back-compat.
	$scripts->add( 'swfupload', '/mcms-roots/js/swfupload/swfupload.js', array(), '2201-20110113');
	$scripts->add( 'swfupload-all', false, array( 'swfupload' ), '2201' );
	$scripts->add( 'swfupload-handlers', "/mcms-roots/js/swfupload/handlers$suffix.js", array('swfupload-all', 'jquery'), '2201-20110524');
	did_action( 'init' ) && $scripts->localize( 'swfupload-handlers', 'swfuploadL10n', $uploader_l10n );

	$scripts->add( 'comment-reply', "/mcms-roots/js/comment-reply$suffix.js", array(), false, 1 );

	$scripts->add( 'json2', "/mcms-roots/js/json2$suffix.js", array(), '2015-05-03' );
	did_action( 'init' ) && $scripts->add_data( 'json2', 'conditional', 'lt IE 8' );

	$scripts->add( 'underscore', "/mcms-roots/js/underscore$dev_suffix.js", array(), '1.8.3', 1 );
	$scripts->add( 'backbone', "/mcms-roots/js/backbone$dev_suffix.js", array( 'underscore','jquery' ), '1.2.3', 1 );

	$scripts->add( 'mcms-util', "/mcms-roots/js/mcms-util$suffix.js", array('underscore', 'jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mcms-util', '_mcmsUtilSettings', array(
		'ajax' => array(
			'url' => admin_url( 'admin-ajax.php', 'relative' ),
		),
	) );

	$scripts->add( 'mcms-sanitize', "/mcms-roots/js/mcms-sanitize$suffix.js", array('jquery'), false, 1 );

	$scripts->add( 'mcms-backbone', "/mcms-roots/js/mcms-backbone$suffix.js", array('backbone', 'mcms-util'), false, 1 );

	$scripts->add( 'revisions', "/mcms-admin/js/revisions$suffix.js", array( 'mcms-backbone', 'jquery-ui-slider', 'hoverIntent' ), false, 1 );

	$scripts->add( 'imgareaselect', "/mcms-roots/js/imgareaselect/jquery.imgareaselect$suffix.js", array('jquery'), false, 1 );

	$scripts->add( 'mediaelement', false, array( 'jquery', 'mediaelement-core', 'mediaelement-migrate' ), '4.2.6-78496d1' );
	$scripts->add( 'mediaelement-core', "/mcms-roots/js/mediaelement/mediaelement-and-player$suffix.js", array(), '4.2.6-78496d1', 1 );
	$scripts->add( 'mediaelement-migrate', "/mcms-roots/js/mediaelement/mediaelement-migrate$suffix.js", array(), false, 1);

	did_action( 'init' ) && $scripts->add_inline_script( 'mediaelement-core', sprintf( 'var mejsL10n = %s;', mcms_json_encode( array(
		'language' => strtolower( strtok( is_admin() ? get_user_locale() : get_locale(), '_-' ) ),
		'strings'  => array(
			'mejs.install-flash'       => __( 'You are using a browser that does not have Flash player enabled or installed. Please turn on your Flash player module or download the latest version from https://get.adobe.com/flashplayer/' ),
			'mejs.fullscreen-off'      => __( 'Turn off Fullscreen' ),
			'mejs.fullscreen-on'       => __( 'Go Fullscreen' ),
			'mejs.download-video'      => __( 'Download Video' ),
			'mejs.fullscreen'          => __( 'Fullscreen' ),
			'mejs.time-jump-forward'   => array( __( 'Jump forward 1 second' ), __( 'Jump forward %1 seconds' ) ),
			'mejs.loop'                => __( 'Toggle Loop' ),
			'mejs.play'                => __( 'Play' ),
			'mejs.pause'               => __( 'Pause' ),
			'mejs.close'               => __( 'Close' ),
			'mejs.time-slider'         => __( 'Time Slider' ),
			'mejs.time-help-text'      => __( 'Use Left/Right Arrow keys to advance one second, Up/Down arrows to advance ten seconds.' ),
			'mejs.time-skip-back'      => array( __( 'Skip back 1 second' ), __( 'Skip back %1 seconds' ) ),
			'mejs.captions-subtitles'  => __( 'Captions/Subtitles' ),
			'mejs.captions-chapters'   => __( 'Chapters' ),
			'mejs.none'                => __( 'None' ),
			'mejs.mute-toggle'         => __( 'Mute Toggle' ),
			'mejs.volume-help-text'    => __( 'Use Up/Down Arrow keys to increase or decrease volume.' ),
			'mejs.unmute'              => __( 'Unmute' ),
			'mejs.mute'                => __( 'Mute' ),
			'mejs.volume-slider'       => __( 'Volume Slider' ),
			'mejs.video-player'        => __( 'Video Player' ),
			'mejs.audio-player'        => __( 'Audio Player' ),
			'mejs.ad-skip'             => __( 'Skip ad' ),
			'mejs.ad-skip-info'        => array( __( 'Skip in 1 second' ), __( 'Skip in %1 seconds' ) ),
			'mejs.source-chooser'      => __( 'Source Chooser' ),
			'mejs.stop'                => __( 'Stop' ),
			'mejs.speed-rate'          => __( 'Speed Rate' ),
			'mejs.live-broadcast'      => __( 'Live Broadcast' ),
			'mejs.afrikaans'           => __( 'Afrikaans' ),
			'mejs.albanian'            => __( 'Albanian' ),
			'mejs.arabic'              => __( 'Arabic' ),
			'mejs.belarusian'          => __( 'Belarusian' ),
			'mejs.bulgarian'           => __( 'Bulgarian' ),
			'mejs.catalan'             => __( 'Catalan' ),
			'mejs.chinese'             => __( 'Chinese' ),
			'mejs.chinese-simplified'  => __( 'Chinese (Simplified)' ),
			'mejs.chinese-traditional' => __( 'Chinese (Traditional)' ),
			'mejs.croatian'            => __( 'Croatian' ),
			'mejs.czech'               => __( 'Czech' ),
			'mejs.danish'              => __( 'Danish' ),
			'mejs.dutch'               => __( 'Dutch' ),
			'mejs.english'             => __( 'English' ),
			'mejs.estonian'            => __( 'Estonian' ),
			'mejs.filipino'            => __( 'Filipino' ),
			'mejs.finnish'             => __( 'Finnish' ),
			'mejs.french'              => __( 'French' ),
			'mejs.galician'            => __( 'Galician' ),
			'mejs.german'              => __( 'German' ),
			'mejs.greek'               => __( 'Greek' ),
			'mejs.haitian-creole'      => __( 'Haitian Creole' ),
			'mejs.hebrew'              => __( 'Hebrew' ),
			'mejs.hindi'               => __( 'Hindi' ),
			'mejs.hungarian'           => __( 'Hungarian' ),
			'mejs.icelandic'           => __( 'Icelandic' ),
			'mejs.indonesian'          => __( 'Indonesian' ),
			'mejs.irish'               => __( 'Irish' ),
			'mejs.italian'             => __( 'Italian' ),
			'mejs.japanese'            => __( 'Japanese' ),
			'mejs.korean'              => __( 'Korean' ),
			'mejs.latvian'             => __( 'Latvian' ),
			'mejs.lithuanian'          => __( 'Lithuanian' ),
			'mejs.macedonian'          => __( 'Macedonian' ),
			'mejs.malay'               => __( 'Malay' ),
			'mejs.maltese'             => __( 'Maltese' ),
			'mejs.norwegian'           => __( 'Norwegian' ),
			'mejs.persian'             => __( 'Persian' ),
			'mejs.polish'              => __( 'Polish' ),
			'mejs.portuguese'          => __( 'Portuguese' ),
			'mejs.romanian'            => __( 'Romanian' ),
			'mejs.russian'             => __( 'Russian' ),
			'mejs.serbian'             => __( 'Serbian' ),
			'mejs.slovak'              => __( 'Slovak' ),
			'mejs.slovenian'           => __( 'Slovenian' ),
			'mejs.spanish'             => __( 'Spanish' ),
			'mejs.swahili'             => __( 'Swahili' ),
			'mejs.swedish'             => __( 'Swedish' ),
			'mejs.tagalog'             => __( 'Tagalog' ),
			'mejs.thai'                => __( 'Thai' ),
			'mejs.turkish'             => __( 'Turkish' ),
			'mejs.ukrainian'           => __( 'Ukrainian' ),
			'mejs.vietnamese'          => __( 'Vietnamese' ),
			'mejs.welsh'               => __( 'Welsh' ),
			'mejs.yiddish'             => __( 'Yiddish' ),
			),
		) ) ), 'before' );


	$scripts->add( 'mediaelement-vimeo', "/mcms-roots/js/mediaelement/renderers/vimeo.min.js", array('mediaelement'), '4.2.6-78496d1', 1 );
	$scripts->add( 'mcms-mediaelement', "/mcms-roots/js/mediaelement/mcms-mediaelement$suffix.js", array('mediaelement'), false, 1 );
	$mejs_settings = array(
		'modulePath'    => includes_url( 'js/mediaelement/', 'relative' ),
		'classPrefix'   => 'mejs-',
		'stretching'    => 'responsive',
	);
	did_action( 'init' ) && $scripts->localize( 'mediaelement', '_mcmsmejsSettings',
		/**
		 * Filters the MediaElement configuration settings.
		 *
		 * @since 4.4.0
		 *
		 * @param array $mejs_settings MediaElement settings array.
		 */
		apply_filters( 'mejs_settings', $mejs_settings )
	);

	$scripts->add( 'mcms-codemirror', '/mcms-roots/js/codemirror/codemirror.min.js', array(), '5.29.1-alpha-ee20357' );
	$scripts->add( 'csslint', '/mcms-roots/js/codemirror/csslint.js', array(), '1.0.5' );
	$scripts->add( 'jshint', '/mcms-roots/js/codemirror/jshint.js', array(), '2.9.5.999' );
	$scripts->add( 'jsonlint', '/mcms-roots/js/codemirror/jsonlint.js', array(), '1.6.2' );
	$scripts->add( 'htmlhint', '/mcms-roots/js/codemirror/htmlhint.js', array(), '0.9.14-xmcms' );
	$scripts->add( 'htmlhint-kses', '/mcms-roots/js/codemirror/htmlhint-kses.js', array( 'htmlhint' ) );
	$scripts->add( 'code-editor', "/mcms-admin/js/code-editor$suffix.js", array( 'jquery', 'mcms-codemirror', 'underscore' ) );
	$scripts->add( 'mcms-myskin-module-editor', "/mcms-admin/js/myskin-module-editor$suffix.js", array( 'mcms-util', 'mcms-sanitize', 'jquery', 'jquery-ui-core', 'mcms-a11y', 'underscore' ) );
	did_action( 'init' ) && $scripts->add_inline_script( 'mcms-myskin-module-editor', sprintf( 'mcms.myskinModuleEditor.l10n = %s;', mcms_json_encode( array(
		'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.' ),
		'saveError' => __( 'Something went wrong. Your change may not have been saved. Please try again. There is also a chance that you may need to manually fix and upload the file over FTP.' ),
		'lintError' => array(
			/* translators: %d: error count */
			'singular' => _n( 'There is %d error which must be fixed before you can update this file.', 'There are %d errors which must be fixed before you can update this file.', 1 ),
			/* translators: %d: error count */
			'plural' => _n( 'There is %d error which must be fixed before you can update this file.', 'There are %d errors which must be fixed before you can update this file.', 2 ), // @todo This is lacking, as some languages have a dedicated dual form. For proper handling of plurals in JS, see #20491.
		),
	) ) ) );

	$scripts->add( 'mcms-playlist', "/mcms-roots/js/mediaelement/mcms-playlist$suffix.js", array( 'mcms-util', 'backbone', 'mediaelement' ), false, 1 );

	$scripts->add( 'zxcvbn-async', "/mcms-roots/js/zxcvbn-async$suffix.js", array(), '1.0' );
	did_action( 'init' ) && $scripts->localize( 'zxcvbn-async', '_zxcvbnSettings', array(
		'src' => empty( $guessed_url ) ? includes_url( '/js/zxcvbn.min.js' ) : $scripts->base_url . '/mcms-roots/js/zxcvbn.min.js',
	) );

	$scripts->add( 'password-strength-meter', "/mcms-admin/js/password-strength-meter$suffix.js", array( 'jquery', 'zxcvbn-async' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'password-strength-meter', 'pwsL10n', array(
		'unknown'  => _x( 'Password strength unknown', 'password strength' ),
		'short'    => _x( 'Very weak', 'password strength' ),
		'bad'      => _x( 'Weak', 'password strength' ),
		'good'     => _x( 'Medium', 'password strength' ),
		'strong'   => _x( 'Strong', 'password strength' ),
		'mismatch' => _x( 'Mismatch', 'password mismatch' ),
	) );

	$scripts->add( 'user-profile', "/mcms-admin/js/user-profile$suffix.js", array( 'jquery', 'password-strength-meter', 'mcms-util' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'user-profile', 'userProfileL10n', array(
		'warn'     => __( 'Your new password has not been saved.' ),
		'warnWeak' => __( 'Confirm use of weak password' ),
		'show'     => __( 'Show' ),
		'hide'     => __( 'Hide' ),
		'cancel'   => __( 'Cancel' ),
		'ariaShow' => esc_attr__( 'Show password' ),
		'ariaHide' => esc_attr__( 'Hide password' ),
	) );

	$scripts->add( 'language-chooser', "/mcms-admin/js/language-chooser$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'user-suggest', "/mcms-admin/js/user-suggest$suffix.js", array( 'jquery-ui-autocomplete' ), false, 1 );

	$scripts->add( 'admin-bar', "/mcms-roots/js/admin-bar$suffix.js", array(), false, 1 );

	$scripts->add( 'mcmslink', "/mcms-roots/js/mcmslink$suffix.js", array( 'jquery', 'mcms-a11y' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mcmslink', 'mcmsLinkL10n', array(
		'title' => __('Insert/edit link'),
		'update' => __('Update'),
		'save' => __('Add Link'),
		'noTitle' => __('(no title)'),
		'noMatchesFound' => __('No results found.'),
		'linkSelected' => __( 'Link selected.' ),
		'linkInserted' => __( 'Link inserted.' ),
	) );

	$scripts->add( 'mcmsdialogs', "/mcms-roots/js/mcmsdialog$suffix.js", array( 'jquery-ui-dialog' ), false, 1 );

	$scripts->add( 'word-count', "/mcms-admin/js/word-count$suffix.js", array(), false, 1 );

	$scripts->add( 'media-upload', "/mcms-admin/js/media-upload$suffix.js", array( 'thickbox', 'shortcode' ), false, 1 );

	$scripts->add( 'hoverIntent', "/mcms-roots/js/hoverIntent$suffix.js", array('jquery'), '1.8.1', 1 );

	$scripts->add( 'customize-base',     "/mcms-roots/js/customize-base$suffix.js",     array( 'jquery', 'json2', 'underscore' ), false, 1 );
	$scripts->add( 'customize-loader',   "/mcms-roots/js/customize-loader$suffix.js",   array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-preview',  "/mcms-roots/js/customize-preview$suffix.js",  array( 'mcms-a11y', 'customize-base' ), false, 1 );
	$scripts->add( 'customize-models',   "/mcms-roots/js/customize-models.js", array( 'underscore', 'backbone' ), false, 1 );
	$scripts->add( 'customize-views',    "/mcms-roots/js/customize-views.js",  array( 'jquery', 'underscore', 'imgareaselect', 'customize-models', 'media-editor', 'media-views' ), false, 1 );
	$scripts->add( 'customize-controls', "/mcms-admin/js/customize-controls$suffix.js", array( 'customize-base', 'mcms-a11y', 'mcms-util', 'jquery-ui-core' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'customize-controls', '_mcmsCustomizeControlsL10n', array(
		'activate'           => __( 'Activate &amp; Publish' ),
		'save'               => __( 'Save &amp; Publish' ), // @todo Remove as not required.
		'publish'            => __( 'Publish' ),
		'published'          => __( 'Published' ),
		'saveDraft'          => __( 'Save Draft' ),
		'draftSaved'         => __( 'Draft Saved' ),
		'updating'           => __( 'Updating' ),
		'schedule'           => _x( 'Schedule', 'customizer changeset action/button label' ),
		'scheduled'          => _x( 'Scheduled', 'customizer changeset status' ),
		'invalid'            => __( 'Invalid' ),
		'saveBeforeShare'    => __( 'Please save your changes in order to share the preview.' ),
		'futureDateError'    => __( 'You must supply a future date to schedule.' ),
		'saveAlert'          => __( 'The changes you made will be lost if you navigate away from this page.' ),
		'saved'              => __( 'Saved' ),
		'cancel'             => __( 'Cancel' ),
		'close'              => __( 'Close' ),
		'action'             => __( 'Action' ),
		'discardChanges'     => __( 'Discard changes' ),
		'cheatin'            => __( 'Something went wrong.' ),
		'notAllowedHeading'  => __( 'You need a higher level of permission.' ),
		'notAllowed'         => __( 'Sorry, you are not allowed to customize this site.' ),
		'previewIframeTitle' => __( 'Site Preview' ),
		'loginIframeTitle'   => __( 'Session expired' ),
		'collapseSidebar'    => _x( 'Hide Controls', 'label for hide controls button without length constraints' ),
		'expandSidebar'      => _x( 'Show Controls', 'label for hide controls button without length constraints' ),
		'untitledBlogName'   => __( '(Untitled)' ),
		'unknownRequestFail' => __( 'Looks like something&#8217;s gone wrong. Wait a couple seconds, and then try again.' ),
		'myskinDownloading'   => __( 'Downloading your new myskin&hellip;' ),
		'myskinPreviewWait'   => __( 'Setting up your live preview. This may take a bit.' ),
		'revertingChanges'   => __( 'Reverting unpublished changes&hellip;' ),
		'trashConfirm'       => __( 'Are you sure you&#8217;d like to discard your unpublished changes?' ),
		/* translators: %s: Display name of the user who has taken over the changeset in customizer. */
		'takenOverMessage'   => __( '%s has taken over and is currently customizing.' ),
		/* translators: %s: URL to the Customizer to load the autosaved version */
		'autosaveNotice'     => __( 'There is a more recent autosave of your changes than the one you are previewing. <a href="%s">Restore the autosave</a>' ),
		'videoHeaderNotice'  => __( 'This myskin doesn&#8217;t support video headers on this page. Navigate to the front page or another page that supports video headers.' ),
		// Used for overriding the file types allowed in plupload.
		'allowedFiles'       => __( 'Allowed Files' ),
		'customCssError'     => array(
			/* translators: %d: error count */
			'singular' => _n( 'There is %d error which must be fixed before you can save.', 'There are %d errors which must be fixed before you can save.', 1 ),
			/* translators: %d: error count */
			'plural'   => _n( 'There is %d error which must be fixed before you can save.', 'There are %d errors which must be fixed before you can save.', 2 ), // @todo This is lacking, as some languages have a dedicated dual form. For proper handling of plurals in JS, see #20491.
		),
		'pageOnFrontError' => __( 'Homepage and posts page must be different.' ),
		'saveBlockedError' => array(
			/* translators: %s: number of invalid settings */
			'singular' => _n( 'Unable to save due to %s invalid setting.', 'Unable to save due to %s invalid settings.', 1 ),
			/* translators: %s: number of invalid settings */
			'plural'   => _n( 'Unable to save due to %s invalid setting.', 'Unable to save due to %s invalid settings.', 2 ), // @todo This is lacking, as some languages have a dedicated dual form. For proper handling of plurals in JS, see #20491.
		),
		'scheduleDescription' => __( 'Schedule your customization changes to publish ("go live") at a future date.' ),
		'myskinPreviewUnavailable' => __( 'Sorry, you can&#8217;t preview new myskins when you have changes scheduled or saved as a draft. Please publish your changes, or wait until they publish to preview new myskins.' ),
		'myskinInstallUnavailable' => sprintf(
			/* translators: %s: URL to Add MySkins admin screen */
			__( 'You won&#8217;t be able to install new myskins from here yet since your install requires SFTP credentials. For now, please <a href="%s">add myskins in the admin</a>.' ),
			esc_url( admin_url( 'myskin-install.php' ) )
		),
		'publishSettings' => __( 'Publish Settings' ),
		'invalidDate'     => __( 'Invalid date.' ),
		'invalidValue'    => __( 'Invalid value.' ),
	) );
	$scripts->add( 'customize-selective-refresh', "/mcms-roots/js/customize-selective-refresh$suffix.js", array( 'jquery', 'mcms-util', 'customize-preview' ), false, 1 );

	$scripts->add( 'customize-widgets', "/mcms-admin/js/customize-widgets$suffix.js", array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-droppable', 'mcms-backbone', 'customize-controls' ), false, 1 );
	$scripts->add( 'customize-preview-widgets', "/mcms-roots/js/customize-preview-widgets$suffix.js", array( 'jquery', 'mcms-util', 'customize-preview', 'customize-selective-refresh' ), false, 1 );

	$scripts->add( 'customize-nav-menus', "/mcms-admin/js/customize-nav-menus$suffix.js", array( 'jquery', 'mcms-backbone', 'customize-controls', 'accordion', 'nav-menu' ), false, 1 );
	$scripts->add( 'customize-preview-nav-menus', "/mcms-roots/js/customize-preview-nav-menus$suffix.js", array( 'jquery', 'mcms-util', 'customize-preview', 'customize-selective-refresh' ), false, 1 );

	$scripts->add( 'mcms-custom-header', "/mcms-roots/js/mcms-custom-header$suffix.js", array( 'mcms-a11y' ), false, 1 );

	$scripts->add( 'accordion', "/mcms-admin/js/accordion$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'shortcode', "/mcms-roots/js/shortcode$suffix.js", array( 'underscore' ), false, 1 );
	$scripts->add( 'media-models', "/mcms-roots/js/media-models$suffix.js", array( 'mcms-backbone' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'media-models', '_mcmsMediaModelsL10n', array(
		'settings' => array(
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			'post' => array( 'id' => 0 ),
		),
	) );

	$scripts->add( 'mcms-embed', "/mcms-roots/js/mcms-embed$suffix.js" );

	// To enqueue media-views or media-editor, call mcms_enqueue_media().
	// Both rely on numerous settings, styles, and templates to operate correctly.
	$scripts->add( 'media-views',  "/mcms-roots/js/media-views$suffix.js",  array( 'utils', 'media-models', 'mcms-plupload', 'jquery-ui-sortable', 'mcms-mediaelement', 'mcms-api-request' ), false, 1 );
	$scripts->add( 'media-editor', "/mcms-roots/js/media-editor$suffix.js", array( 'shortcode', 'media-views' ), false, 1 );
	$scripts->add( 'media-audiovideo', "/mcms-roots/js/media-audiovideo$suffix.js", array( 'media-editor' ), false, 1 );
	$scripts->add( 'mce-view', "/mcms-roots/js/mce-view$suffix.js", array( 'shortcode', 'jquery', 'media-views', 'media-audiovideo' ), false, 1 );

	$scripts->add( 'mcms-api', "/mcms-roots/js/mcms-api$suffix.js", array( 'jquery', 'backbone', 'underscore', 'mcms-api-request' ), false, 1 );

	if ( is_admin() ) {
		$scripts->add( 'admin-tags', "/mcms-admin/js/tags$suffix.js", array( 'jquery', 'mcms-ajax-response' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-tags', 'tagsl10n', array(
			'noPerm' => __('Sorry, you are not allowed to do that.'),
			'broken' => __('Something went wrong.')
		));

		$scripts->add( 'admin-comments', "/mcms-admin/js/edit-comments$suffix.js", array('mcms-lists', 'quicktags', 'jquery-query'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-comments', 'adminCommentsL10n', array(
			'hotkeys_highlight_first' => isset($_GET['hotkeys_highlight_first']),
			'hotkeys_highlight_last' => isset($_GET['hotkeys_highlight_last']),
			'replyApprove' => __( 'Approve and Reply' ),
			'reply' => __( 'Reply' ),
			'warnQuickEdit' => __( "Are you sure you want to edit this comment?\nThe changes you made will be lost." ),
			'warnCommentChanges' => __( "Are you sure you want to do this?\nThe comment changes you made will be lost." ),
			'docTitleComments' => __( 'Comments' ),
			/* translators: %s: comments count */
			'docTitleCommentsCount' => __( 'Comments (%s)' ),
		) );

		$scripts->add( 'xfn', "/mcms-admin/js/xfn$suffix.js", array('jquery'), false, 1 );
		did_action( 'init' ) && $scripts->localize(
			'xfn', 'privacyToolsL10n', array(
				'noDataFound'     => __( 'No personal data was found for this user.' ),
				'foundAndRemoved' => __( 'All of the personal data found for this user was erased.' ),
				'noneRemoved'     => __( 'Personal data was found for this user but was not erased.' ),
				'someNotRemoved'  => __( 'Personal data was found for this user but some of the personal data found was not erased.' ),
				'removalError'    => __( 'An error occurred while attempting to find and erase personal data.' ),
				'noExportFile'    => __( 'No personal data export file was generated.' ),
				'exportError'     => __( 'An error occurred while attempting to export personal data.' ),
			)
		);

		$scripts->add( 'postbox', "/mcms-admin/js/postbox$suffix.js", array('jquery-ui-sortable'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'postbox', 'postBoxL10n', array(
			'postBoxEmptyString' => __( 'Drag boxes here' ),
		) );

		$scripts->add( 'tags-box', "/mcms-admin/js/tags-box$suffix.js", array( 'jquery', 'tags-suggest' ), false, 1 );

		$scripts->add( 'tags-suggest', "/mcms-admin/js/tags-suggest$suffix.js", array( 'jquery-ui-autocomplete', 'mcms-a11y' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'tags-suggest', 'tagsSuggestL10n', array(
			'tagDelimiter' => _x( ',', 'tag delimiter' ),
			'removeTerm'   => __( 'Remove term:' ),
			'termSelected' => __( 'Term selected.' ),
			'termAdded'    => __( 'Term added.' ),
			'termRemoved'  => __( 'Term removed.' ),
		) );

		$scripts->add( 'post', "/mcms-admin/js/post$suffix.js", array( 'suggest', 'mcms-lists', 'postbox', 'tags-box', 'underscore', 'word-count', 'mcms-a11y' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'post', 'postL10n', array(
			'ok' => __('OK'),
			'cancel' => __('Cancel'),
			'publishOn' => __('Publish on:'),
			'publishOnFuture' =>  __('Schedule for:'),
			'publishOnPast' => __('Published on:'),
			/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
			'dateFormat' => __('%1$s %2$s, %3$s @ %4$s:%5$s'),
			'showcomm' => __('Show more comments'),
			'endcomm' => __('No more comments found.'),
			'publish' => __('Publish'),
			'schedule' => _x('Schedule', 'post action/button label'),
			'update' => __('Update'),
			'savePending' => __('Save as Pending'),
			'saveDraft' => __('Save Draft'),
			'private' => __('Private'),
			'public' => __('Public'),
			'publicSticky' => __('Public, Sticky'),
			'password' => __('Password Protected'),
			'privatelyPublished' => __('Privately Published'),
			'published' => __('Published'),
			'saveAlert' => __('The changes you made will be lost if you navigate away from this page.'),
			'savingText' => __('Saving Draft&#8230;'),
			'permalinkSaved' => __( 'Permalink saved' ),
		) );

		$scripts->add( 'editor-expand', "/mcms-admin/js/editor-expand$suffix.js", array( 'jquery', 'underscore' ), false, 1 );

		$scripts->add( 'link', "/mcms-admin/js/link$suffix.js", array( 'mcms-lists', 'postbox' ), false, 1 );

		$scripts->add( 'comment', "/mcms-admin/js/comment$suffix.js", array( 'jquery', 'postbox' ) );
		$scripts->add_data( 'comment', 'group', 1 );
		did_action( 'init' ) && $scripts->localize( 'comment', 'commentL10n', array(
			'submittedOn' => __( 'Submitted on:' ),
			/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
			'dateFormat' => __( '%1$s %2$s, %3$s @ %4$s:%5$s' )
		) );

		$scripts->add( 'admin-gallery', "/mcms-admin/js/gallery$suffix.js", array( 'jquery-ui-sortable' ) );

		$scripts->add( 'admin-widgets', "/mcms-admin/js/widgets$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), false, 1 );
		did_action( 'init' ) && $scripts->add_inline_script( 'admin-widgets', sprintf( 'mcmsWidgets.l10n = %s;', mcms_json_encode( array(
			'save' => __( 'Save' ),
			'saved' => __( 'Saved' ),
			'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.' ),
		) ) ) );

		$scripts->add( 'media-widgets', "/mcms-admin/js/widgets/media-widgets$suffix.js", array( 'jquery', 'media-models', 'media-views', 'mcms-api-request' ) );
		$scripts->add_inline_script( 'media-widgets', 'mcms.mediaWidgets.init();', 'after' );

		$scripts->add( 'media-audio-widget', "/mcms-admin/js/widgets/media-audio-widget$suffix.js", array( 'media-widgets', 'media-audiovideo' ) );
		$scripts->add( 'media-image-widget', "/mcms-admin/js/widgets/media-image-widget$suffix.js", array( 'media-widgets' ) );
		$scripts->add( 'media-gallery-widget', "/mcms-admin/js/widgets/media-gallery-widget$suffix.js", array( 'media-widgets' ) );
		$scripts->add( 'media-video-widget', "/mcms-admin/js/widgets/media-video-widget$suffix.js", array( 'media-widgets', 'media-audiovideo', 'mcms-api-request' ) );
		$scripts->add( 'text-widgets', "/mcms-admin/js/widgets/text-widgets$suffix.js", array( 'jquery', 'backbone', 'editor', 'mcms-util', 'mcms-a11y' ) );
		$scripts->add( 'custom-html-widgets', "/mcms-admin/js/widgets/custom-html-widgets$suffix.js", array( 'jquery', 'backbone', 'mcms-util', 'jquery-ui-core', 'mcms-a11y' ) );

		$scripts->add( 'myskin', "/mcms-admin/js/myskin$suffix.js", array( 'mcms-backbone', 'mcms-a11y', 'customize-base' ), false, 1 );

		$scripts->add( 'inline-edit-post', "/mcms-admin/js/inline-edit-post$suffix.js", array( 'jquery', 'tags-suggest', 'mcms-a11y' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-post', 'inlineEditL10n', array(
			'error'      => __( 'Error while saving the changes.' ),
			'ntdeltitle' => __( 'Remove From Bulk Edit' ),
			'notitle'    => __( '(no title)' ),
			'comma'      => trim( _x( ',', 'tag delimiter' ) ),
			'saved'      => __( 'Changes saved.' ),
		) );

		$scripts->add( 'inline-edit-tax', "/mcms-admin/js/inline-edit-tax$suffix.js", array( 'jquery', 'mcms-a11y' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-tax', 'inlineEditL10n', array(
			'error' => __( 'Error while saving the changes.' ),
			'saved' => __( 'Changes saved.' ),
		) );

		$scripts->add( 'module-install', "/mcms-admin/js/module-install$suffix.js", array( 'jquery', 'jquery-ui-core', 'thickbox' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'module-install', 'moduleinstallL10n', array(
			'module_information' => __( 'Module:' ),
			'module_modal_label' => __( 'Module details' ),
			'ays' => __('Are you sure you want to install this module?')
		) );

		$scripts->add( 'updates', "/mcms-admin/js/updates$suffix.js", array( 'jquery', 'mcms-util', 'mcms-a11y' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'updates', '_mcmsUpdatesSettings', array(
			'ajax_nonce' => mcms_create_nonce( 'updates' ),
			'l10n'       => array(
				/* translators: %s: Search string */
				'searchResults'              => __( 'Search results for &#8220;%s&#8221;' ),
				'searchResultsLabel'         => __( 'Search Results' ),
				'noModules'                  => __( 'You do not appear to have any modules available at this time.' ),
				'noItemsSelected'            => __( 'Please select at least one item to perform this action on.' ),
				'updating'                   => __( 'Updating...' ), // No ellipsis.
				'moduleUpdated'              => _x( 'Updated!', 'module' ),
				'myskinUpdated'               => _x( 'Updated!', 'myskin' ),
				'update'                     => __( 'Update' ),
				'updateNow'                  => __( 'Update Now' ),
				/* translators: %s: Module name and version */
				'moduleUpdateNowLabel'       => _x( 'Update %s now', 'module' ),
				'updateFailedShort'          => __( 'Update Failed!' ),
				/* translators: %s: Error string for a failed update */
				'updateFailed'               => __( 'Update Failed: %s' ),
				/* translators: %s: Module name and version */
				'moduleUpdatingLabel'        => _x( 'Updating %s...', 'module' ), // No ellipsis.
				/* translators: %s: Module name and version */
				'moduleUpdatedLabel'         => _x( '%s updated!', 'module' ),
				/* translators: %s: Module name and version */
				'moduleUpdateFailedLabel'    => _x( '%s update failed', 'module' ),
				/* translators: Accessibility text */
				'updatingMsg'                => __( 'Updating... please wait.' ), // No ellipsis.
				/* translators: Accessibility text */
				'updatedMsg'                 => __( 'Update completed successfully.' ),
				/* translators: Accessibility text */
				'updateCancel'               => __( 'Update canceled.' ),
				'beforeunload'               => __( 'Updates may not complete if you navigate away from this page.' ),
				'installNow'                 => __( 'Install Now' ),
				/* translators: %s: Module name */
				'moduleInstallNowLabel'      => _x( 'Install %s now', 'module' ),
				'installing'                 => __( 'Installing...' ),
				'moduleInstalled'            => _x( 'Installed!', 'module' ),
				'myskinInstalled'             => _x( 'Installed!', 'myskin' ),
				'installFailedShort'         => __( 'Installation Failed!' ),
				/* translators: %s: Error string for a failed installation */
				'installFailed'              => __( 'Installation failed: %s' ),
				/* translators: %s: Module name and version */
				'moduleInstallingLabel'      => _x( 'Installing %s...', 'module' ), // no ellipsis
				/* translators: %s: MySkin name and version */
				'myskinInstallingLabel'       => _x( 'Installing %s...', 'myskin' ), // no ellipsis
				/* translators: %s: Module name and version */
				'moduleInstalledLabel'       => _x( '%s installed!', 'module' ),
				/* translators: %s: MySkin name and version */
				'myskinInstalledLabel'        => _x( '%s installed!', 'myskin' ),
				/* translators: %s: Module name and version */
				'moduleInstallFailedLabel'   => _x( '%s installation failed', 'module' ),
				/* translators: %s: MySkin name and version */
				'myskinInstallFailedLabel'    => _x( '%s installation failed', 'myskin' ),
				'installingMsg'              => __( 'Installing... please wait.' ),
				'installedMsg'               => __( 'Installation completed successfully.' ),
				/* translators: %s: Activation URL */
				'importerInstalledMsg'       => __( 'Importer installed successfully. <a href="%s">Run importer</a>' ),
				/* translators: %s: MySkin name */
				'aysDelete'                  => __( 'Are you sure you want to delete %s?' ),
				/* translators: %s: Module name */
				'aysDeleteUninstall'         => __( 'Are you sure you want to delete %s and its data?' ),
				'aysBulkDelete'              => __( 'Are you sure you want to delete the selected modules and their data?' ),
				'aysBulkDeleteMySkins'        => __( 'Caution: These myskins may be active on other sites in the network. Are you sure you want to proceed?' ),
				'deleting'                   => __( 'Deleting...' ),
				/* translators: %s: Error string for a failed deletion */
				'deleteFailed'               => __( 'Deletion failed: %s' ),
				'moduleDeleted'              => _x( 'Deleted!', 'module' ),
				'myskinDeleted'               => _x( 'Deleted!', 'myskin' ),
				'livePreview'                => __( 'Live Preview' ),
				'activateModule'             => is_network_admin() ? __( 'Network Activate' ) : __( 'Activate' ),
				'activateMySkin'              => is_network_admin() ? __( 'Network Enable' ) : __( 'Activate' ),
				/* translators: %s: Module name */
				'activateModuleLabel'        => is_network_admin() ? _x( 'Network Activate %s', 'module' ) : _x( 'Activate %s', 'module' ),
				/* translators: %s: MySkin name */
				'activateMySkinLabel'         => is_network_admin() ? _x( 'Network Activate %s', 'myskin' ) : _x( 'Activate %s', 'myskin' ),
				'activateImporter'           => __( 'Run Importer' ),
				/* translators: %s: Importer name */
				'activateImporterLabel'      => __( 'Run %s' ),
				'unknownError'               => __( 'Something went wrong.' ),
				'connectionError'            => __( 'Connection lost or the server is busy. Please try again later.' ),
				'nonceError'                 => __( 'An error has occurred. Please reload the page and try again.' ),
				'modulesFound'               => __( 'Number of modules found: %d' ),
				'noModulesFound'             => __( 'No modules found. Try a different search.' ),
			),
		) );

		$scripts->add( 'farbtastic', '/mcms-admin/js/farbtastic.js', array('jquery'), '1.2' );

		$scripts->add( 'iris', '/mcms-admin/js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), '1.0.7', 1 );
		$scripts->add( 'mcms-color-picker', "/mcms-admin/js/color-picker$suffix.js", array( 'iris' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'mcms-color-picker', 'mcmsColorPickerL10n', array(
			'clear'            => __( 'Clear' ),
			'clearAriaLabel'   => __( 'Clear color' ),
			'defaultString'    => __( 'Default' ),
			'defaultAriaLabel' => __( 'Select default color' ),
			'pick'             => __( 'Select Color' ),
			'defaultLabel'     => __( 'Color value' ),
		) );

		$scripts->add( 'dashboard', "/mcms-admin/js/dashboard$suffix.js", array( 'jquery', 'admin-comments', 'postbox', 'mcms-util', 'mcms-a11y' ), false, 1 );

		$scripts->add( 'list-revisions', "/mcms-roots/js/mcms-list-revisions$suffix.js" );

		$scripts->add( 'media-grid', "/mcms-roots/js/media-grid$suffix.js", array( 'media-editor' ), false, 1 );
		$scripts->add( 'media', "/mcms-admin/js/media$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'media', 'attachMediaBoxL10n', array(
			'error' => __( 'An error has occurred. Please reload the page and try again.' ),
		));

		$scripts->add( 'image-edit', "/mcms-admin/js/image-edit$suffix.js", array('jquery', 'json2', 'imgareaselect'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'image-edit', 'imageEditL10n', array(
			'error' => __( 'Could not load the preview image. Please reload the page and try again.' )
		));

		$scripts->add( 'set-post-thumbnail', "/mcms-admin/js/set-post-thumbnail$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'set-post-thumbnail', 'setPostThumbnailL10n', array(
			'setThumbnail' => __( 'Use as featured image' ),
			'saving' => __( 'Saving...' ), // no ellipsis
			'error' => __( 'Could not set that as the thumbnail image. Try a different attachment.' ),
			'done' => __( 'Done' )
		) );

		// Navigation Menus
		$scripts->add( 'nav-menu', "/mcms-admin/js/nav-menu$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'mcms-lists', 'postbox', 'json2' ) );
		did_action( 'init' ) && $scripts->localize( 'nav-menu', 'navMenuL10n', array(
			'noResultsFound' => __( 'No results found.' ),
			'warnDeleteMenu' => __( "You are about to permanently delete this menu. \n 'Cancel' to stop, 'OK' to delete." ),
			'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.' ),
			'untitled' => _x( '(no label)', 'missing menu item navigation label' )
		) );

		$scripts->add( 'custom-header', "/mcms-admin/js/custom-header.js", array( 'jquery-masonry' ), false, 1 );
		$scripts->add( 'custom-background', "/mcms-admin/js/custom-background$suffix.js", array( 'mcms-color-picker', 'media-views' ), false, 1 );
		$scripts->add( 'media-gallery', "/mcms-admin/js/media-gallery$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'svg-painter', '/mcms-admin/js/svg-painter.js', array( 'jquery' ), false, 1 );
	}
}

/**
 * Assign default styles to $styles object.
 *
 * Nothing is returned, because the $styles parameter is passed by reference.
 * Meaning that whatever object is passed will be updated without having to
 * reassign the variable that was passed back to the same value. This saves
 * memory.
 *
 * Adding default styles is not the only task, it also assigns the base_url
 * property, the default version, and text direction for the object.
 *
 * @since 2.6.0
 *
 * @param MCMS_Styles $styles
 */
function mcms_default_styles( &$styles ) {
	include( BASED_TREE_URI . MCMSINC . '/version.php' ); // include an unmodified $mcms_version

	if ( ! defined( 'SCRIPT_DEBUG' ) )
		define( 'SCRIPT_DEBUG', false !== strpos( $mcms_version, '-src' ) );

	if ( ! $guessurl = site_url() )
		$guessurl = mcms_guess_url();

	$styles->base_url = $guessurl;
	$styles->content_url = defined('MCMS_CONTENT_URL')? MCMS_CONTENT_URL : '';
	$styles->default_version = get_bloginfo( 'version' );
	$styles->text_direction = function_exists( 'is_rtl' ) && is_rtl() ? 'rtl' : 'ltr';
	$styles->default_dirs = array('/mcms-admin/', '/mcms-roots/css/');

	// Open Sans is no longer used by core, but may be relied upon by myskins and modules.
	$open_sans_font_url = '';

	/* translators: If there are characters in your language that are not supported
	 * by Open Sans, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language,
		 * translate this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)' );

		if ( 'cyrillic' == $subset ) {
			$subsets .= ',cyrillic,cyrillic-ext';
		} elseif ( 'greek' == $subset ) {
			$subsets .= ',greek,greek-ext';
		} elseif ( 'vietnamese' == $subset ) {
			$subsets .= ',vietnamese';
		}

		// Hotlink Open Sans, for now
		$open_sans_font_url = "https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=$subsets";
	}

	// Register a stylesheet for the selected admin color scheme.
	$styles->add( 'colors', true, array( 'mcms-admin', 'buttons' ) );

	$suffix = SCRIPT_DEBUG ? '' : '.min';

	// Admin CSS
	$styles->add( 'common',              "/mcms-admin/css/common$suffix.css" );
	$styles->add( 'forms',               "/mcms-admin/css/forms$suffix.css" );
	$styles->add( 'admin-menu',          "/mcms-admin/css/admin-menu$suffix.css" );
	$styles->add( 'dashboard',           "/mcms-admin/css/dashboard$suffix.css" );
	$styles->add( 'list-tables',         "/mcms-admin/css/list-tables$suffix.css" );
	$styles->add( 'edit',                "/mcms-admin/css/edit$suffix.css" );
	$styles->add( 'revisions',           "/mcms-admin/css/revisions$suffix.css" );
	$styles->add( 'media',               "/mcms-admin/css/media$suffix.css" );
	$styles->add( 'myskins',              "/mcms-admin/css/myskins$suffix.css" );
	$styles->add( 'about',               "/mcms-admin/css/about$suffix.css" );
	$styles->add( 'nav-menus',           "/mcms-admin/css/nav-menus$suffix.css" );
	$styles->add( 'widgets',             "/mcms-admin/css/widgets$suffix.css", array( 'mcms-pointer' ) );
	$styles->add( 'site-icon',           "/mcms-admin/css/site-icon$suffix.css" );
	$styles->add( 'l10n',                "/mcms-admin/css/l10n$suffix.css" );
	$styles->add( 'code-editor',         "/mcms-admin/css/code-editor$suffix.css", array( 'mcms-codemirror' ) );

	$styles->add( 'mcms-admin', false, array( 'dashicons', 'common', 'forms', 'admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions', 'media', 'myskins', 'about', 'nav-menus', 'widgets', 'site-icon', 'l10n' ) );

	$styles->add( 'login',               "/mcms-admin/css/login$suffix.css", array( 'dashicons', 'buttons', 'forms', 'l10n' ) );
	$styles->add( 'install',             "/mcms-admin/css/install$suffix.css", array( 'buttons' ) );
	$styles->add( 'mcms-color-picker',     "/mcms-admin/css/color-picker$suffix.css" );
	$styles->add( 'customize-controls',  "/mcms-admin/css/customize-controls$suffix.css", array( 'mcms-admin', 'colors', 'ie', 'imgareaselect' ) );
	$styles->add( 'customize-widgets',   "/mcms-admin/css/customize-widgets$suffix.css", array( 'mcms-admin', 'colors' ) );
	$styles->add( 'customize-nav-menus', "/mcms-admin/css/customize-nav-menus$suffix.css", array( 'mcms-admin', 'colors' ) );

	$styles->add( 'ie', "/mcms-admin/css/ie$suffix.css" );
	$styles->add_data( 'ie', 'conditional', 'lte IE 7' );

	// Common dependencies
	$styles->add( 'buttons',   "/mcms-roots/css/buttons$suffix.css" );
	$styles->add( 'dashicons', "/mcms-roots/css/dashicons$suffix.css" );

	// Includes CSS
	$styles->add( 'admin-bar',            "/mcms-roots/css/admin-bar$suffix.css", array( 'dashicons' ) );
	$styles->add( 'mcms-auth-check',        "/mcms-roots/css/mcms-auth-check$suffix.css", array( 'dashicons' ) );
	$styles->add( 'editor-buttons',       "/mcms-roots/css/editor$suffix.css", array( 'dashicons' ) );
	$styles->add( 'media-views',          "/mcms-roots/css/media-views$suffix.css", array( 'buttons', 'dashicons', 'mcms-mediaelement' ) );
	$styles->add( 'mcms-pointer',           "/mcms-roots/css/mcms-pointer$suffix.css", array( 'dashicons' ) );
	$styles->add( 'customize-preview',    "/mcms-roots/css/customize-preview$suffix.css", array( 'dashicons' ) );
	$styles->add( 'mcms-embed-template-ie', "/mcms-roots/css/mcms-embed-template-ie$suffix.css" );
	$styles->add_data( 'mcms-embed-template-ie', 'conditional', 'lte IE 8' );

	// External libraries and friends
	$styles->add( 'imgareaselect',       '/mcms-roots/js/imgareaselect/imgareaselect.css', array(), '0.9.8' );
	$styles->add( 'mcms-jquery-ui-dialog', "/mcms-roots/css/jquery-ui-dialog$suffix.css", array( 'dashicons' ) );
	$styles->add( 'mediaelement',        "/mcms-roots/js/mediaelement/mediaelementplayer-legacy.min.css", array(), '4.2.6-78496d1' );
	$styles->add( 'mcms-mediaelement',     "/mcms-roots/js/mediaelement/mcms-mediaelement$suffix.css", array( 'mediaelement' ) );
	$styles->add( 'thickbox',            '/mcms-roots/js/thickbox/thickbox.css', array( 'dashicons' ) );
	$styles->add( 'mcms-codemirror',       '/mcms-roots/js/codemirror/codemirror.min.css', array(), '5.29.1-alpha-ee20357' );

	// Deprecated CSS
	$styles->add( 'deprecated-media', "/mcms-admin/css/deprecated-media$suffix.css" );
	$styles->add( 'farbtastic',       "/mcms-admin/css/farbtastic$suffix.css", array(), '1.3u1' );
	$styles->add( 'jcrop',            "/mcms-roots/js/jcrop/jquery.Jcrop.min.css", array(), '0.9.12' );
	$styles->add( 'colors-fresh', false, array( 'mcms-admin', 'buttons' ) ); // Old handle.
	$styles->add( 'open-sans', $open_sans_font_url ); // No longer used in core as of 4.6

	// RTL CSS
	$rtl_styles = array(
		// mcms-admin
		'common', 'forms', 'admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions', 'media', 'myskins', 'about', 'nav-menus',
		'widgets', 'site-icon', 'l10n', 'install', 'mcms-color-picker', 'customize-controls', 'customize-widgets', 'customize-nav-menus', 'customize-preview',
		'ie', 'login',
		// mcms-roots
		'buttons', 'admin-bar', 'mcms-auth-check', 'editor-buttons', 'media-views', 'mcms-pointer',
		'mcms-jquery-ui-dialog',
		// deprecated
		'deprecated-media', 'farbtastic',
	);

	foreach ( $rtl_styles as $rtl_style ) {
		$styles->add_data( $rtl_style, 'rtl', 'replace' );
		if ( $suffix ) {
			$styles->add_data( $rtl_style, 'suffix', $suffix );
		}
	}
}

/**
 * Reorder JavaScript scripts array to place prototype before jQuery.
 *
 * @since 2.3.1
 *
 * @param array $js_array JavaScript scripts array
 * @return array Reordered array, if needed.
 */
function mcms_prototype_before_jquery( $js_array ) {
	if ( false === $prototype = array_search( 'prototype', $js_array, true ) )
		return $js_array;

	if ( false === $jquery = array_search( 'jquery', $js_array, true ) )
		return $js_array;

	if ( $prototype < $jquery )
		return $js_array;

	unset($js_array[$prototype]);

	array_splice( $js_array, $jquery, 0, 'prototype' );

	return $js_array;
}

/**
 * Load localized data on print rather than initialization.
 *
 * These localizations require information that may not be loaded even by init.
 *
 * @since 2.5.0
 */
function mcms_just_in_time_script_localization() {

	mcms_localize_script( 'autosave', 'autosaveL10n', array(
		'autosaveInterval' => AUTOSAVE_INTERVAL,
		'blog_id' => get_current_blog_id(),
	) );

	mcms_localize_script( 'mce-view', 'mceViewL10n', array(
		'shortcodes' => ! empty( $GLOBALS['shortcode_tags'] ) ? array_keys( $GLOBALS['shortcode_tags'] ) : array()
	) );

	mcms_localize_script( 'word-count', 'wordCountL10n', array(
		/*
		 * translators: If your word count is based on single characters (e.g. East Asian characters),
		 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
		 * Do not translate into your own language.
		 */
		'type' => _x( 'words', 'Word count type. Do not translate!' ),
		'shortcodes' => ! empty( $GLOBALS['shortcode_tags'] ) ? array_keys( $GLOBALS['shortcode_tags'] ) : array()
	) );
}

/**
 * Localizes the jQuery UI datepicker.
 *
 * @since 4.6.0
 *
 * @link https://api.jqueryui.com/datepicker/#options
 *
 * @global MCMS_Locale $mcms_locale The MandarinCMS date and time locale object.
 */
function mcms_localize_jquery_ui_datepicker() {
	global $mcms_locale;

	if ( ! mcms_script_is( 'jquery-ui-datepicker', 'enqueued' ) ) {
		return;
	}

	// Convert the PHP date format into jQuery UI's format.
	$datepicker_date_format = str_replace(
		array(
			'd', 'j', 'l', 'z', // Day.
			'F', 'M', 'n', 'm', // Month.
			'Y', 'y'            // Year.
		),
		array(
			'dd', 'd', 'DD', 'o',
			'MM', 'M', 'm', 'mm',
			'yy', 'y'
		),
		get_option( 'date_format' )
	);

	$datepicker_defaults = mcms_json_encode( array(
		'closeText'       => __( 'Close' ),
		'currentText'     => __( 'Today' ),
		'monthNames'      => array_values( $mcms_locale->month ),
		'monthNamesShort' => array_values( $mcms_locale->month_abbrev ),
		'nextText'        => __( 'Next' ),
		'prevText'        => __( 'Previous' ),
		'dayNames'        => array_values( $mcms_locale->weekday ),
		'dayNamesShort'   => array_values( $mcms_locale->weekday_abbrev ),
		'dayNamesMin'     => array_values( $mcms_locale->weekday_initial ),
		'dateFormat'      => $datepicker_date_format,
		'firstDay'        => absint( get_option( 'start_of_week' ) ),
		'isRTL'           => $mcms_locale->is_rtl(),
	) );

	mcms_add_inline_script( 'jquery-ui-datepicker', "jQuery(document).ready(function(jQuery){jQuery.datepicker.setDefaults({$datepicker_defaults});});" );
}

/**
 * Localizes community events data that needs to be passed to dashboard.js.
 *
 * @since 4.8.0
 */
function mcms_localize_community_events() {
	if ( ! mcms_script_is( 'dashboard' ) ) {
		return;
	}

	require_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-community-events.php' );

	$user_id            = get_current_user_id();
	$saved_location     = get_user_option( 'community-events-location', $user_id );
	$saved_ip_address   = isset( $saved_location['ip'] ) ? $saved_location['ip'] : false;
	$current_ip_address = MCMS_Community_Events::get_unsafe_client_ip();

	/*
	 * If the user's location is based on their IP address, then update their
	 * location when their IP address changes. This allows them to see events
	 * in their current city when travelling. Otherwise, they would always be
	 * shown events in the city where they were when they first loaded the
	 * Dashboard, which could have been months or years ago.
	 */
	if ( $saved_ip_address && $current_ip_address && $current_ip_address !== $saved_ip_address ) {
		$saved_location['ip'] = $current_ip_address;
		update_user_option( $user_id, 'community-events-location', $saved_location, true );
	}

	$events_client = new MCMS_Community_Events( $user_id, $saved_location );

	mcms_localize_script( 'dashboard', 'communityEventsData', array(
		'nonce' => mcms_create_nonce( 'community_events' ),
		'cache' => $events_client->get_cached_events(),

		'l10n' => array(
			'enter_closest_city' => __( 'Enter your closest city to find nearby events.' ),
			'error_occurred_please_try_again' => __( 'An error occurred. Please try again.' ),
			'attend_event_near_generic' => __( 'Attend an upcoming event near you.' ),

			/*
			 * These specific examples were chosen to highlight the fact that a
			 * state is not needed, even for cities whose name is not unique.
			 * It would be too cumbersome to include that in the instructions
			 * to the user, so it's left as an implication.
			 */
			/* translators: %s is the name of the city we couldn't locate.
			 * Replace the examples with cities related to your locale. Test that
			 * they match the expected location and have upcoming events before
			 * including them. If no cities related to your locale have events,
			 * then use cities related to your locale that would be recognizable
			 * to most users. Use only the city name itself, without any region
			 * or country. Use the endonym (native locale name) instead of the
			 * English name if possible.
			 */
			'could_not_locate_city' => __( 'We couldn&#8217;t locate %s. Please try another nearby city. For example: Kansas City; Springfield; Portland.' ),

			// This one is only used with mcms.a11y.speak(), so it can/should be more brief.
			/* translators: %s: the name of a city */
			'city_updated' => __( 'City updated. Listing events near %s.' ),
		)
	) );
}

/**
 * Administration Screen CSS for changing the styles.
 *
 * If installing the 'mcms-admin/' directory will be replaced with './'.
 *
 * The $_mcms_admin_css_colors global manages the Administration Screens CSS
 * stylesheet that is loaded. The option that is set is 'admin_color' and is the
 * color and key for the array. The value for the color key is an object with
 * a 'url' parameter that has the URL path to the CSS file.
 *
 * The query from $src parameter will be appended to the URL that is given from
 * the $_mcms_admin_css_colors array value URL.
 *
 * @since 2.6.0
 * @global array $_mcms_admin_css_colors
 *
 * @param string $src    Source URL.
 * @param string $handle Either 'colors' or 'colors-rtl'.
 * @return string|false URL path to CSS stylesheet for Administration Screens.
 */
function mcms_style_loader_src( $src, $handle ) {
	global $_mcms_admin_css_colors;

	if ( mcms_installing() )
		return preg_replace( '#^mcms-admin/#', './', $src );

	if ( 'colors' == $handle ) {
		$color = get_user_option('admin_color');

		if ( empty($color) || !isset($_mcms_admin_css_colors[$color]) )
			$color = 'fresh';

		$color = $_mcms_admin_css_colors[$color];
		$url = $color->url;

		if ( ! $url ) {
			return false;
		}

		$parsed = parse_url( $src );
		if ( isset($parsed['query']) && $parsed['query'] ) {
			mcms_parse_str( $parsed['query'], $qv );
			$url = add_query_arg( $qv, $url );
		}

		return $url;
	}

	return $src;
}

/**
 * Prints the script queue in the HTML head on admin pages.
 *
 * Postpones the scripts that were queued for the footer.
 * print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8.0
 *
 * @see mcms_print_scripts()
 *
 * @global bool $concatenate_scripts
 *
 * @return array
 */
function print_head_scripts() {
	global $concatenate_scripts;

	if ( ! did_action('mcms_print_scripts') ) {
		/** This action is documented in mcms-roots/functions.mcms-scripts.php */
		do_action( 'mcms_print_scripts' );
	}

	$mcms_scripts = mcms_scripts();

	script_concat_settings();
	$mcms_scripts->do_concat = $concatenate_scripts;
	$mcms_scripts->do_head_items();

	/**
	 * Filters whether to print the head scripts.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the head scripts. Default true.
	 */
	if ( apply_filters( 'print_head_scripts', true ) ) {
		_print_scripts();
	}

	$mcms_scripts->reset();
	return $mcms_scripts->done;
}

/**
 * Prints the scripts that were queued for the footer or too late for the HTML head.
 *
 * @since 2.8.0
 *
 * @global MCMS_Scripts $mcms_scripts
 * @global bool       $concatenate_scripts
 *
 * @return array
 */
function print_footer_scripts() {
	global $mcms_scripts, $concatenate_scripts;

	if ( ! ( $mcms_scripts instanceof MCMS_Scripts ) ) {
		return array(); // No need to run if not instantiated.
	}
	script_concat_settings();
	$mcms_scripts->do_concat = $concatenate_scripts;
	$mcms_scripts->do_footer_items();

	/**
	 * Filters whether to print the footer scripts.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the footer scripts. Default true.
	 */
	if ( apply_filters( 'print_footer_scripts', true ) ) {
		_print_scripts();
	}

	$mcms_scripts->reset();
	return $mcms_scripts->done;
}

/**
 * Print scripts (internal use only)
 *
 * @ignore
 *
 * @global MCMS_Scripts $mcms_scripts
 * @global bool       $compress_scripts
 */
function _print_scripts() {
	global $mcms_scripts, $compress_scripts;

	$zip = $compress_scripts ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( $concat = trim( $mcms_scripts->concat, ', ' ) ) {

		if ( !empty($mcms_scripts->print_code) ) {
			echo "\n<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n"; // not needed in HTML 5
			echo $mcms_scripts->print_code;
			echo "/* ]]> */\n";
			echo "</script>\n";
		}

		$concat = str_split( $concat, 128 );
		$concat = 'load%5B%5D=' . implode( '&load%5B%5D=', $concat );

		$src = $mcms_scripts->base_url . "/mcms-admin/load-scripts.php?c={$zip}&" . $concat . '&ver=' . $mcms_scripts->default_version;
		echo "<script type='text/javascript' src='" . esc_attr($src) . "'></script>\n";
	}

	if ( !empty($mcms_scripts->print_html) )
		echo $mcms_scripts->print_html;
}

/**
 * Prints the script queue in the HTML head on the front end.
 *
 * Postpones the scripts that were queued for the footer.
 * mcms_print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8.0
 *
 * @global MCMS_Scripts $mcms_scripts
 *
 * @return array
 */
function mcms_print_head_scripts() {
	if ( ! did_action('mcms_print_scripts') ) {
		/** This action is documented in mcms-roots/functions.mcms-scripts.php */
		do_action( 'mcms_print_scripts' );
	}

	global $mcms_scripts;

	if ( ! ( $mcms_scripts instanceof MCMS_Scripts ) ) {
		return array(); // no need to run if nothing is queued
	}
	return print_head_scripts();
}

/**
 * Private, for use in *_footer_scripts hooks
 *
 * @since 3.3.0
 */
function _mcms_footer_scripts() {
	print_late_styles();
	print_footer_scripts();
}

/**
 * Hooks to print the scripts and styles in the footer.
 *
 * @since 2.8.0
 */
function mcms_print_footer_scripts() {
	/**
	 * Fires when footer scripts are printed.
	 *
	 * @since 2.8.0
	 */
	do_action( 'mcms_print_footer_scripts' );
}

/**
 * Wrapper for do_action('mcms_enqueue_scripts')
 *
 * Allows modules to queue scripts for the front end using mcms_enqueue_script().
 * Runs first in mcms_head() where all is_home(), is_page(), etc. functions are available.
 *
 * @since 2.8.0
 */
function mcms_enqueue_scripts() {
	/**
	 * Fires when scripts and styles are enqueued.
	 *
	 * @since 2.8.0
	 */
	do_action( 'mcms_enqueue_scripts' );
}

/**
 * Prints the styles queue in the HTML head on admin pages.
 *
 * @since 2.8.0
 *
 * @global bool $concatenate_scripts
 *
 * @return array
 */
function print_admin_styles() {
	global $concatenate_scripts;

	$mcms_styles = mcms_styles();

	script_concat_settings();
	$mcms_styles->do_concat = $concatenate_scripts;
	$mcms_styles->do_items(false);

	/**
	 * Filters whether to print the admin styles.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the admin styles. Default true.
	 */
	if ( apply_filters( 'print_admin_styles', true ) ) {
		_print_styles();
	}

	$mcms_styles->reset();
	return $mcms_styles->done;
}

/**
 * Prints the styles that were queued too late for the HTML head.
 *
 * @since 3.3.0
 *
 * @global MCMS_Styles $mcms_styles
 * @global bool      $concatenate_scripts
 *
 * @return array|void
 */
function print_late_styles() {
	global $mcms_styles, $concatenate_scripts;

	if ( ! ( $mcms_styles instanceof MCMS_Styles ) ) {
		return;
	}

	script_concat_settings();
	$mcms_styles->do_concat = $concatenate_scripts;
	$mcms_styles->do_footer_items();

	/**
	 * Filters whether to print the styles queued too late for the HTML head.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $print Whether to print the 'late' styles. Default true.
	 */
	if ( apply_filters( 'print_late_styles', true ) ) {
		_print_styles();
	}

	$mcms_styles->reset();
	return $mcms_styles->done;
}

/**
 * Print styles (internal use only)
 *
 * @ignore
 * @since 3.3.0
 *
 * @global bool $compress_css
 */
function _print_styles() {
	global $compress_css;

	$mcms_styles = mcms_styles();

	$zip = $compress_css ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( $concat = trim( $mcms_styles->concat, ', ' ) ) {
		$dir = $mcms_styles->text_direction;
		$ver = $mcms_styles->default_version;

		$concat = str_split( $concat, 128 );
		$concat = 'load%5B%5D=' . implode( '&load%5B%5D=', $concat );

		$href = $mcms_styles->base_url . "/mcms-admin/load-styles.php?c={$zip}&dir={$dir}&" . $concat . '&ver=' . $ver;
		echo "<link rel='stylesheet' href='" . esc_attr($href) . "' type='text/css' media='all' />\n";

		if ( !empty($mcms_styles->print_code) ) {
			echo "<style type='text/css'>\n";
			echo $mcms_styles->print_code;
			echo "\n</style>\n";
		}
	}

	if ( !empty($mcms_styles->print_html) )
		echo $mcms_styles->print_html;
}

/**
 * Determine the concatenation and compression settings for scripts and styles.
 *
 * @since 2.8.0
 *
 * @global bool $concatenate_scripts
 * @global bool $compress_scripts
 * @global bool $compress_css
 */
function script_concat_settings() {
	global $concatenate_scripts, $compress_scripts, $compress_css;

	$compressed_output = ( ini_get('zlib.output_compression') || 'ob_gzhandler' == ini_get('output_handler') );

	if ( ! isset($concatenate_scripts) ) {
		$concatenate_scripts = defined('CONCATENATE_SCRIPTS') ? CONCATENATE_SCRIPTS : true;
		if ( ( ! is_admin() && ! did_action( 'login_init' ) ) || ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) )
			$concatenate_scripts = false;
	}

	if ( ! isset($compress_scripts) ) {
		$compress_scripts = defined('COMPRESS_SCRIPTS') ? COMPRESS_SCRIPTS : true;
		if ( $compress_scripts && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_scripts = false;
	}

	if ( ! isset($compress_css) ) {
		$compress_css = defined('COMPRESS_CSS') ? COMPRESS_CSS : true;
		if ( $compress_css && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_css = false;
	}
}
