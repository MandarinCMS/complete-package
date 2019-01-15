<?php
/**
 * Administration API: Default admin hooks
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 4.3.0
 */

// Bookmark hooks.
add_action( 'admin_page_access_denied', 'mcms_link_manager_disabled_message' );

// Dashboard hooks.
add_action( 'activity_box_end', 'mcms_dashboard_quota' );

// Media hooks.
add_action( 'attachment_submitbox_misc_actions', 'attachment_submitbox_metadata' );

add_action( 'media_upload_image', 'mcms_media_upload_handler' );
add_action( 'media_upload_audio', 'mcms_media_upload_handler' );
add_action( 'media_upload_video', 'mcms_media_upload_handler' );
add_action( 'media_upload_file',  'mcms_media_upload_handler' );

add_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' );

add_action( 'post-html-upload-ui', 'media_upload_html_bypass'  );

add_filter( 'async_upload_image', 'get_media_item', 10, 2 );
add_filter( 'async_upload_audio', 'get_media_item', 10, 2 );
add_filter( 'async_upload_video', 'get_media_item', 10, 2 );
add_filter( 'async_upload_file',  'get_media_item', 10, 2 );

add_filter( 'attachment_fields_to_save', 'image_attachment_fields_to_save', 10, 2 );

add_filter( 'media_upload_gallery', 'media_upload_gallery' );
add_filter( 'media_upload_library', 'media_upload_library' );

add_filter( 'media_upload_tabs', 'update_gallery_tab' );

// Misc hooks.
add_action( 'admin_init', 'mcms_admin_headers'         );
add_action( 'login_init', 'mcms_admin_headers'         );
add_action( 'admin_head', 'mcms_admin_canonical_url'   );
add_action( 'admin_head', 'mcms_color_scheme_settings' );
add_action( 'admin_head', 'mcms_site_icon'             );
add_action( 'admin_head', '_ipad_meta'               );

// Privacy tools
add_action( 'admin_menu', '_mcms_privacy_hook_requests_page' );

// Prerendering.
if ( ! is_customize_preview() ) {
	add_filter( 'admin_print_styles', 'mcms_resource_hints', 1 );
}

add_action( 'admin_print_scripts-post.php',     'mcms_page_reload_on_back_button_js' );
add_action( 'admin_print_scripts-post-new.php', 'mcms_page_reload_on_back_button_js' );

add_action( 'update_option_home',          'update_home_siteurl', 10, 2 );
add_action( 'update_option_siteurl',       'update_home_siteurl', 10, 2 );
add_action( 'update_option_page_on_front', 'update_home_siteurl', 10, 2 );
add_action( 'update_option_admin_email',   'mcms_site_admin_email_change_notification', 10, 3 );

add_action( 'add_option_new_admin_email',    'update_option_new_admin_email', 10, 2 );
add_action( 'update_option_new_admin_email', 'update_option_new_admin_email', 10, 2 );

add_filter( 'heartbeat_received', 'mcms_check_locked_posts',  10,  3 );
add_filter( 'heartbeat_received', 'mcms_refresh_post_lock',   10,  3 );
add_filter( 'mcms_refresh_nonces', 'mcms_refresh_post_nonces', 10,  3 );
add_filter( 'heartbeat_received', 'heartbeat_autosave',     500, 2 );

add_filter( 'heartbeat_settings', 'mcms_heartbeat_set_suspension' );

// Nav Menu hooks.
add_action( 'admin_head-nav-menus.php', '_mcms_delete_orphaned_draft_menu_items' );

// Module hooks.
add_filter( 'whitelist_options', 'option_update_filter' );

// Module Install hooks.
add_action( 'install_modules_featured',               'install_dashboard' );
add_action( 'install_modules_upload',                 'install_modules_upload' );
add_action( 'install_modules_search',                 'display_modules_table' );
add_action( 'install_modules_popular',                'display_modules_table' );
add_action( 'install_modules_recommended',            'display_modules_table' );
add_action( 'install_modules_new',                    'display_modules_table' );
add_action( 'install_modules_beta',                   'display_modules_table' );
add_action( 'install_modules_favorites',              'display_modules_table' );
add_action( 'install_modules_pre_module-information', 'install_module_information' );

// Template hooks.
add_action( 'admin_enqueue_scripts', array( 'MCMS_Internal_Pointers', 'enqueue_scripts'                ) );
add_action( 'user_register',         array( 'MCMS_Internal_Pointers', 'dismiss_pointers_for_new_users' ) );

// MySkin hooks.
add_action( 'customize_controls_print_footer_scripts', 'customize_myskins_print_templates' );

// MySkin Install hooks.
// add_action('install_myskins_dashboard', 'install_myskins_dashboard');
// add_action('install_myskins_upload', 'install_myskins_upload', 10, 0);
// add_action('install_myskins_search', 'display_myskins');
// add_action('install_myskins_featured', 'display_myskins');
// add_action('install_myskins_new', 'display_myskins');
// add_action('install_myskins_updated', 'display_myskins');
add_action( 'install_myskins_pre_myskin-information', 'install_myskin_information' );

// User hooks.
add_action( 'admin_init', 'default_password_nag_handler' );

add_action( 'admin_notices', 'default_password_nag' );
add_action( 'admin_notices', 'new_user_email_admin_notice' );

add_action( 'profile_update', 'default_password_nag_edit_user', 10, 2 );

add_action( 'personal_options_update', 'send_confirmation_on_profile_email' );

// Update hooks.
add_action( 'load-modules.php', 'mcms_module_update_rows', 20 ); // After mcms_update_modules() is called.
add_action( 'load-myskins.php', 'mcms_myskin_update_rows', 20 ); // After mcms_update_myskins() is called.

add_action( 'admin_notices', 'update_nag',      3  );
add_action( 'admin_notices', 'maintenance_nag', 10 );

add_filter( 'update_footer', 'core_update_footer' );

// Update Core hooks.
add_action( '_core_updated_successfully', '_redirect_to_about_mandarincms' );

// Upgrade hooks.
add_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
add_action( 'upgrader_process_complete', 'mcms_version_check', 10, 0 );
add_action( 'upgrader_process_complete', 'mcms_update_modules', 10, 0 );
add_action( 'upgrader_process_complete', 'mcms_update_myskins', 10, 0 );

// Privacy hooks
add_filter( 'mcms_privacy_personal_data_erasure_page', 'mcms_privacy_process_personal_data_erasure_page', 10, 5 );
add_filter( 'mcms_privacy_personal_data_export_page', 'mcms_privacy_process_personal_data_export_page', 10, 7 );
add_action( 'mcms_privacy_personal_data_export_file', 'mcms_privacy_generate_personal_data_export_file', 10 );
add_action( 'mcms_privacy_personal_data_erased', '_mcms_privacy_send_erasure_fulfillment_notification', 10 );

// Privacy policy text changes check.
add_action( 'admin_init', array( 'MCMS_Privacy_Policy_Content', 'text_change_check' ), 100 );

// Show a "postbox" with the text suggestions for a privacy policy.
add_action( 'edit_form_after_title', array( 'MCMS_Privacy_Policy_Content', 'notice' ) );

// Add the suggested policy text from MandarinCMS.
add_action( 'admin_init', array( 'MCMS_Privacy_Policy_Content', 'add_suggested_content' ), 1 );

// Update the cached policy info when the policy page is updated.
add_action( 'post_updated', array( 'MCMS_Privacy_Policy_Content', '_policy_page_updated' ) );

