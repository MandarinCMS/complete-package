<?php
/**
 * @package MCMSSEO\Internals
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Adds an SEO admin bar menu with several options. If the current user is an admin he can also go straight to several settings menu's from here.
 */
function mcmsseo_admin_bar_menu() {
	// If the current user can't write posts, this is all of no use, so let's not output an admin menu.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$options = MCMSSEO_Options::get_options( array( 'mcmsseo', 'mcmsseo_ms' ) );

	if ( $options['enable_admin_bar_menu'] !== true ) {
		return;
	}

	global $mcms_admin_bar, $post;

	// Determine is user is admin or network admin.
	$user_is_admin_or_networkadmin = current_user_can( 'manage_options' );
	if ( ! $user_is_admin_or_networkadmin && is_multisite() ) {
		$user_is_admin_or_networkadmin = ( $options['access'] === 'superadmin' && is_super_admin() );
	}

	$focuskw = '';
	$score   = '';
	// By default, the top level menu item has no link.
	$seo_url = '';
	// By default, make the no-link top level menu item focusable.
	$top_level_link_tabindex = '0';

	$analysis_seo = new MCMSSEO_Metabox_Analysis_SEO();
	$analysis_readability = new MCMSSEO_Metabox_Analysis_Readability();

	if ( ( is_singular() || ( is_admin() && MCMSSEO_Metabox::is_post_edit( $GLOBALS['pagenow'] ) ) ) && isset( $post ) && is_object( $post ) && apply_filters( 'mcmsseo_use_page_analysis', true ) === true
	) {
		$focuskw = MCMSSEO_Meta::get_value( 'focuskw', $post->ID );

		if ( $analysis_seo->is_enabled() ) {
			$score = mcmsseo_adminbar_seo_score();
		}
		elseif ( $analysis_readability->is_enabled() ) {
			$score = mcmsseo_adminbar_content_score();
		}
	}

	if ( is_category() || is_tag() || (MCMSSEO_Taxonomy::is_term_edit( $GLOBALS['pagenow'] ) && ! MCMSSEO_Taxonomy::is_term_overview( $GLOBALS['pagenow'] ) )  || is_tax() ) {
		if ( $analysis_seo->is_enabled() ) {
			$score = mcmsseo_tax_adminbar_seo_score();
		}
		elseif ( $analysis_readability->is_enabled() ) {
			$score = mcmsseo_tax_adminbar_content_score();
		}
	}

	// Never display notifications for network admin.
	$counter = '';

	// Set the top level menu item content for admins and network admins.
	if ( $user_is_admin_or_networkadmin ) {

		// Link the top level menu item to the Ultimatum Dashboard page.
		$seo_url = get_admin_url( null, 'admin.php?page=' . MCMSSEO_Admin::PAGE_IDENTIFIER );
		// Since admins will get a real link, there's no need for a tabindex attribute.
		$top_level_link_tabindex = false;

		if ( '' === $score ) {

			// Notification information.
			$notification_center     = Ultimatum_Notification_Center::get();
			$notification_count      = $notification_center->get_notification_count();
			$new_notifications       = $notification_center->get_new_notifications();
			$new_notifications_count = count( $new_notifications );

			if ( $notification_count > 0 ) {
				// Always show Alerts page when clicking on the main link.
				/* translators: %s: number of notifications */
				$counter_screen_reader_text = sprintf( _n( '%s notification', '%s notifications', $notification_count, 'mandarincms-seo' ), number_format_i18n( $notification_count ) );
				$counter = sprintf( ' <div class="mcms-core-ui mcms-ui-notification ultimatum-issue-counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $notification_count, $counter_screen_reader_text );
			}

			if ( $new_notifications_count ) {
				$notification = sprintf(
					/* translators: %d resolves to the number of alerts being added. */
					_n( 'You have a new issue concerning your SEO!', 'You have %d new issues concerning your SEO!', $new_notifications_count, 'mandarincms-seo' ),
					$new_notifications_count
				);
				$counter .= '<div class="ultimatum-issue-added">' . $notification . '</div>';
			}
		}
	}

	// Ultimatum Icon.
	$icon_svg = MCMSSEO_Utils::get_icon_svg();
	$title = '<div id="ultimatum-ab-icon" class="ab-item ultimatum-logo svg" style="background-image: url(\''.$icon_svg.'\');"><span class="screen-reader-text">' . __( 'SEO', 'mandarincms-seo' ) . '</span></div>';

	$mcms_admin_bar->add_menu( array(
		'id'    => 'mcmsseo-menu',
		'title' => $title . $score . $counter,
		'href'  => $seo_url,
		'meta'   => array( 'tabindex' => $top_level_link_tabindex ),
	) );
	if ( ! empty( $notification_count ) ) {
		$mcms_admin_bar->add_menu( array(
			'parent' => 'mcmsseo-menu',
			'id'     => 'mcmsseo-notifications',
			'title'  => __( 'Notifications', 'mandarincms-seo' ) . $counter,
			'href'   => $seo_url,
			'meta'   => array( 'tabindex' => $top_level_link_tabindex ),
		) );
	}
	$mcms_admin_bar->add_menu( array(
		'parent' => 'mcmsseo-menu',
		'id'     => 'mcmsseo-kwresearch',
		'title'  => __( 'Keyword Research', 'mandarincms-seo' ),
		'meta'   => array( 'tabindex' => '0' ),
	) );
	$mcms_admin_bar->add_menu( array(
		'parent' => 'mcmsseo-kwresearch',
		'id'     => 'mcmsseo-adwordsexternal',
		'title'  => __( 'AdWords External', 'mandarincms-seo' ),
		'href'   => 'http://adwords.google.com/keywordplanner',
		'meta'   => array( 'target' => '_blank' ),
	) );
	$mcms_admin_bar->add_menu( array(
		'parent' => 'mcmsseo-kwresearch',
		'id'     => 'mcmsseo-googleinsights',
		'title'  => __( 'Google Trends', 'mandarincms-seo' ),
		'href'   => 'https://www.google.com/trends/explore#q=' . urlencode( $focuskw ),
		'meta'   => array( 'target' => '_blank' ),
	) );
	$mcms_admin_bar->add_menu( array(
		'parent' => 'mcmsseo-kwresearch',
		'id'     => 'mcmsseo-wordtracker',
		'title'  => __( 'SEO Book', 'mandarincms-seo' ),
		'href'   => 'http://tools.seobook.com/keyword-tools/seobook/?keyword=' . urlencode( $focuskw ),
		'meta'   => array( 'target' => '_blank' ),
	) );

	if ( ! is_admin() ) {
		$url = MCMSSEO_Frontend::get_instance()->canonical( false );

		if ( is_string( $url ) ) {
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-menu',
				'id'     => 'mcmsseo-analysis',
				'title'  => __( 'Analyze this page', 'mandarincms-seo' ),
				'meta'   => array( 'tabindex' => '0' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-inlinks-ose',
				'title'  => __( 'Check Inlinks (OSE)', 'mandarincms-seo' ),
				'href'   => '//moz.com/researchtools/ose/links?site=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-kwdensity',
				'title'  => __( 'Check Keyword Density', 'mandarincms-seo' ),
				// HTTPS not available.
				'href'   => 'http://www.zippy.co.uk/keyworddensity/index.php?url=' . urlencode( $url ) . '&keyword=' . urlencode( $focuskw ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-cache',
				'title'  => __( 'Check Google Cache', 'mandarincms-seo' ),
				'href'   => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-header',
				'title'  => __( 'Check Headers', 'mandarincms-seo' ),
				'href'   => '//quixapp.com/headers/?r=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-structureddata',
				'title'  => __( 'Google Structured Data Test', 'mandarincms-seo' ),
				'href'   => 'https://search.google.com/structured-data/testing-tool#url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-facebookdebug',
				'title'  => __( 'Facebook Debugger', 'mandarincms-seo' ),
				'href'   => '//developers.facebook.com/tools/debug/og/object?q=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-pinterestvalidator',
				'title'  => __( 'Pinterest Rich Pins Validator', 'mandarincms-seo' ),
				'href'   => 'https://developers.pinterest.com/tools/url-debugger/?link=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-htmlvalidation',
				'title'  => __( 'HTML Validator', 'mandarincms-seo' ),
				'href'   => '//validator.w3.org/check?uri=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-cssvalidation',
				'title'  => __( 'CSS Validator', 'mandarincms-seo' ),
				'href'   => '//jigsaw.w3.org/css-validator/validator?uri=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-pagespeed',
				'title'  => __( 'Google Page Speed Test', 'mandarincms-seo' ),
				'href'   => '//developers.google.com/speed/pagespeed/insights/?url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-microsoftedge',
				'title'  => __( 'Microsoft Edge Site Scan', 'mandarincms-seo' ),
				'href'   => 'https://developer.microsoft.com/en-us/microsoft-edge/tools/staticscan/?url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-analysis',
				'id'     => 'mcmsseo-google-mobile-friendly',
				'title'  => __( 'Mobile-Friendly Test', 'mandarincms-seo' ),
				'href'   => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
		}
	}

	// @todo: add links to bulk title and bulk description edit pages.
	if ( $user_is_admin_or_networkadmin ) {

		$advanced_settings = mcmsseo_advanced_settings_enabled( $options );

		$mcms_admin_bar->add_menu( array(
			'parent' => 'mcmsseo-menu',
			'id'     => 'mcmsseo-settings',
			'title'  => __( 'SEO Settings', 'mandarincms-seo' ),
			'meta'   => array( 'tabindex' => '0' ),
		) );
		$mcms_admin_bar->add_menu( array(
			'parent' => 'mcmsseo-settings',
			'id'     => 'mcmsseo-general',
			'title'  => __( 'Dashboard', 'mandarincms-seo' ),
			'href'   => admin_url( 'admin.php?page=mcmsseo_dashboard' ),
		) );
		if ( $advanced_settings ) {
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-settings',
				'id'     => 'mcmsseo-titles',
				'title'  => __( 'Titles &amp; Metas', 'mandarincms-seo' ),
				'href'   => admin_url( 'admin.php?page=mcmsseo_titles' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-settings',
				'id'     => 'mcmsseo-social',
				'title'  => __( 'Social', 'mandarincms-seo' ),
				'href'   => admin_url( 'admin.php?page=mcmsseo_social' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-settings',
				'id'     => 'mcmsseo-xml',
				'title'  => __( 'XML Sitemaps', 'mandarincms-seo' ),
				'href'   => admin_url( 'admin.php?page=mcmsseo_xml' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-settings',
				'id'     => 'mcmsseo-mcmsseo-advanced',
				'title'  => __( 'Advanced', 'mandarincms-seo' ),
				'href'   => admin_url( 'admin.php?page=mcmsseo_advanced' ),
			) );
			$mcms_admin_bar->add_menu( array(
				'parent' => 'mcmsseo-settings',
				'id'     => 'mcmsseo-tools',
				'title'  => __( 'Tools', 'mandarincms-seo' ),
				'href'   => admin_url( 'admin.php?page=mcmsseo_tools' ),
			) );
		}
		$mcms_admin_bar->add_menu( array(
			'parent' => 'mcmsseo-settings',
			'id'     => 'mcmsseo-search-console',
			'title'  => __( 'Search Console', 'mandarincms-seo' ),
			'href'   => admin_url( 'admin.php?page=mcmsseo_search_console' ),
		) );
		
	}

}

/**
 * Returns the SEO score element for the admin bar.
 *
 * @return string
 */
function mcmsseo_adminbar_seo_score() {
	$rating = MCMSSEO_Meta::get_value( 'linkdex', get_the_ID() );

	return mcmsseo_adminbar_score( $rating );
}

/**
 * Returns the content score element for the adminbar.
 *
 * @return string
 */
function mcmsseo_adminbar_content_score() {
	$rating = MCMSSEO_Meta::get_value( 'content_score', get_the_ID() );

	return mcmsseo_adminbar_score( $rating );
}

/**
 * Returns the SEO score element for the adminbar.
 *
 * @return string
 */
function mcmsseo_tax_adminbar_seo_score() {
	$rating = 0;

	if ( is_tax() || is_category() || is_tag() ) {
		$rating = MCMSSEO_Taxonomy_Meta::get_meta_without_term( 'linkdex' );
	}

	return mcmsseo_adminbar_score( $rating );
}

/**
 * Returns the Content score element for the adminbar.
 *
 * @return string
 */
function mcmsseo_tax_adminbar_content_score() {
	$rating = 0;

	if ( is_tax() || is_category() || is_tag() ) {
		$rating = MCMSSEO_Taxonomy_Meta::get_meta_without_term( 'content_score' );
	}

	return mcmsseo_adminbar_score( $rating );
}

/**
 * Takes The SEO score and makes the score icon for the adminbar with it.
 *
 * @param int $score The 0-100 rating of the score. Can be either SEO score or content score.
 *
 * @return string $score_adminbar_element
 */
function mcmsseo_adminbar_score( $score ) {
	$score = MCMSSEO_Utils::translate_score( $score );

	$score_adminbar_element = '<div class="mcmsseo-score-icon adminbar-seo-score '. $score .'"><span class="adminbar-seo-score-text screen-reader-text"></span></div>';
	return $score_adminbar_element;
}

add_action( 'admin_bar_menu', 'mcmsseo_admin_bar_menu', 95 );

/**
 * Enqueue CSS to format the Ultimatum SEO adminbar item.
 */
function mcmsseo_admin_bar_style() {

	if ( ! is_admin_bar_showing() ) {
		return;
	}

	$asset_manager = new MCMSSEO_Admin_Asset_Manager();
	$asset_manager->register_assets();
	$asset_manager->enqueue_style( 'adminbar' );
}

add_action( 'mcms_enqueue_scripts', 'mcmsseo_admin_bar_style' );
add_action( 'admin_enqueue_scripts', 'mcmsseo_admin_bar_style' );

/**
 * Allows editing of the meta fields through weblog editors like Marsedit.
 *
 * @param array $allcaps Capabilities that must all be true to allow action.
 * @param array $cap     Array of capabilities to be checked, unused here.
 * @param array $args    List of arguments for the specific cap to be checked.
 *
 * @return array $allcaps
 */
function allow_custom_field_edits( $allcaps, $cap, $args ) {
	// $args[0] holds the capability.
	// $args[2] holds the post ID.
	// $args[3] holds the custom field.
	// Make sure the request is to edit or add a post meta (this is usually also the second value in $cap,
	// but this is safer to check).
	if ( in_array( $args[0], array( 'edit_post_meta', 'add_post_meta' ) ) ) {
		// Only allow editing rights for users who have the rights to edit this post and make sure
		// the meta value starts with _ultimatum_mcmsseo (MCMSSEO_Meta::$meta_prefix).
		if ( ( isset( $args[2] ) && current_user_can( 'edit_post', $args[2] ) ) && ( ( isset( $args[3] ) && $args[3] !== '' ) && strpos( $args[3], MCMSSEO_Meta::$meta_prefix ) === 0 ) ) {
			$allcaps[ $args[0] ] = true;
		}
	}

	return $allcaps;
}

add_filter( 'user_has_cap', 'allow_custom_field_edits', 0, 3 );

/**
 * Detects if the advanced settings are enabled.
 *
 * @param array $mcmsseo_options The mcmsseo settings.
 *
 * @returns boolean True if the advanced settings are enabled, false if not.
 */
function mcmsseo_advanced_settings_enabled( $mcmsseo_options ) {
	return ( $mcmsseo_options['enable_setting_pages'] === true );
}

/********************** DEPRECATED FUNCTIONS **********************/

/**
 * Set the default settings.
 *
 * @deprecated 1.5.0
 * @deprecated use MCMSSEO_Options::initialize()
 * @see        MCMSSEO_Options::initialize()
 */
function mcmsseo_defaults() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.0', 'MCMSSEO_Options::initialize()' );
	MCMSSEO_Options::initialize();
}

/**
 * Translates a decimal analysis score into a textual one.
 *
 * @deprecated 1.5.6.1
 * @deprecated use MCMSSEO_Utils::translate_score()
 * @see        MCMSSEO_Utils::translate_score()
 *
 * @param int  $val       The decimal score to translate.
 * @param bool $css_value Whether to return the i18n translated score or the CSS class value.
 *
 * @return string
 */
function mcmsseo_translate_score( $val, $css_value = true ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.6.1', 'MCMSSEO_Utils::translate_score()' );

	return MCMSSEO_Utils::translate_score();
}

/**
 * Check whether file editing is allowed for the .htaccess and robots.txt files
 *
 * @deprecated 1.5.6.1
 * @deprecated use MCMSSEO_Utils::allow_system_file_edit()
 * @see        MCMSSEO_Utils::allow_system_file_edit()
 *
 * @internal   current_user_can() checks internally whether a user is on mcms-ms and adjusts accordingly.
 *
 * @return bool
 */
function mcmsseo_allow_system_file_edit() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 1.5.6.1', 'MCMSSEO_Utils::allow_system_file_edit()' );

	return MCMSSEO_Utils::allow_system_file_edit();
}

/**
 * Test whether force rewrite should be enabled or not.
 *
 * @deprecated 3.3
 *
 * @return void
 */
function mcmsseo_title_test() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 3.3.0' );
}

/**
 * Test whether the active myskin contains a <meta> description tag.
 *
 * @since 1.4.14 Moved from dashboard.php and adjusted - see changelog
 *
 * @deprecated 3.3
 *
 * @return void
 */
function mcmsseo_description_test() {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 3.3.0' );
}

/**
 * Check if the current myskin was updated and if so, test the updated myskin
 * for the title and meta description tag
 *
 * @since    1.4.14
 *
 * @deprecated 3.3
 *
 * @param MCMS_Upgrader $upgrader_object Upgrader object instance.
 * @param array       $context_array   Context data array.
 * @param mixed       $myskins          Optional myskins set.
 *
 * @return  void
 */
function mcmsseo_upgrader_process_complete( $upgrader_object, $context_array, $myskins = null ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 3.3.0' );
}

/**
 * Abuse a filter to check if the current myskin was updated and if so, test the updated myskin
 * for the title and meta description tag
 *
 * @since 1.4.14
 * @deprecated 3.3
 *
 * @param   array           $update_actions Updated actions set.
 * @param   MCMS_MySkin|string $updated_myskin  MySkin object instance or stylesheet name.
 */
function mcmsseo_update_myskin_complete_actions( $update_actions, $updated_myskin ) {
	_deprecated_function( __FUNCTION__, 'MCMSSEO 3.3.0' );
}
