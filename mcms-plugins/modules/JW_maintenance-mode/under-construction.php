<?php
/*
  Module Name: JW Maintenance Mode
  Description: Put your site behind a great looking under construction, coming soon, maintenance mode or landing page.
  Version: 3.0
  Text Domain: JW_maintenance-mode
  Domain Path: lang

  Copyright 2015 - 2018  Web factory Ltd  (email: jiicp@jiiworks.net)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// this is an include only MCMS file
if (!defined('BASED_TREE_URI')) {
  die;
}


define('JIICP_MODULE_DIR', module_dir_path(__FILE__));
define('JIICP_MODULE_URL', module_dir_url(__FILE__));
define('JIICP_OPTIONS_KEY', 'jiicp_options');
define('JIICP_META_KEY', 'jiicp_meta');
define('JIICP_POINTERS_KEY', 'jiicp_pointers');
define('JIICP_NOTICES_KEY', 'jiicp_notices');
define('JIICP_SURVEYS_KEY', 'jiicp_surveys');


require_once JIICP_MODULE_DIR . 'jiicp-license.php';


// main module class
class JIICP {
  static $version = 0;
  static $licensing_servers = array('#https://license1.jiiworks.net/', '#https://license2.jiiworks.net/');


  // get module version from header
  static function get_module_version() {
    $module_data = get_file_data(__FILE__, array('version' => 'Version'), 'module');
    self::$version = $module_data['version'];

    return $module_data['version'];
  } // get_module_version


  // hook things up
  static function init() {
    // check if minimal required MCMS version is present
    if (false === self::check_mcms_version(4.0)) {
      return false;
    }

    if (is_admin()) {
      // if the module was updated from ver < 1.20 upgrade settings array
      self::maybe_upgrade();

      // add JIICP menu to admin tools menu group
      add_action('admin_menu', array(__CLASS__, 'admin_menu'));

      // settings registration
      add_action('admin_init', array(__CLASS__, 'register_settings'));

      // aditional links in module description
      add_filter('module_action_links_' . module_basename(__FILE__),
                            array(__CLASS__, 'module_action_links'));
      add_filter('module_row_meta', array(__CLASS__, 'module_meta_links'), 10, 2);
      add_filter('admin_footer_text', array(__CLASS__, 'admin_footer_text'));

      // manages admin header notifications
      add_action('admin_notices', array(__CLASS__, 'admin_notices'));
      add_action('admin_action_jiicp_dismiss_notice', array(__CLASS__, 'dismiss_notice'));
      add_action('admin_action_jiicp_change_status', array(__CLASS__, 'change_status'));
      add_action('admin_action_jiicp_reset_settings', array(__CLASS__, 'reset_settings'));

      // enqueue admin scripts
      add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));

      // AJAX endpoints
      add_action('mcms_ajax_jiicp_dismiss_pointer', array(__CLASS__, 'dismiss_pointer_ajax'));
      add_action('mcms_ajax_jiicp_dismiss_survey', array(__CLASS__, 'dismiss_survey_ajax'));
      add_action('mcms_ajax_jiicp_submit_survey', array(__CLASS__, 'submit_survey_ajax'));
      add_action('mcms_ajax_jiicp_submit_earlybird', array(__CLASS__, 'submit_earlybird_ajax'));
      add_action('mcms_ajax_jiicp_submit_support_message', array(__CLASS__, 'submit_support_message_ajax'));

      // uninstall survey on modules page
      add_action('admin_footer-modules.php', array(__CLASS__, 'footer_modules'));
    } else {
      // main module logic
      add_action('mcms', array(__CLASS__, 'display_construction_page'), 0, 1);

      // show under construction notice on login form
      add_filter('login_message', array(__CLASS__, 'login_message'));

      // disable feeds
      add_action('do_feed_rdf', array(__CLASS__, 'disable_feed'), 0, 1);
      add_action('do_feed_rss', array(__CLASS__, 'disable_feed'), 0, 1);
      add_action('do_feed_rss2', array(__CLASS__, 'disable_feed'), 0, 1);
      add_action('do_feed_atom', array(__CLASS__, 'disable_feed'), 0, 1);

      add_action('mcms_footer', array(__CLASS__, 'whitelisted_notice'));
    } // if not admin

    // admin bar notice for frontend & backend
    add_action('mcms_before_admin_bar_render', array(__CLASS__, 'admin_bar'));
    add_action('mcms_head', array(__CLASS__, 'admin_bar_style'));
    add_action('admin_head', array(__CLASS__, 'admin_bar_style'));

    JIICP_license::init();
  } // init


  // check if user has the minimal MCMS version required by JIICP
  static function check_mcms_version($min_version) {
    if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
        add_action('admin_notices', array(__CLASS__, 'notice_min_mcms_version'));
      return false;
    } else {
      return true;
    }
  } // check_mcms_version


  // display error message if MCMS version is too low
  static function notice_min_mcms_version() {
    echo '<div class="error"><p>' . sprintf(__('MaintenanceMode module <b>requires MandarinCMS version 4.0</b> or higher to function properly. You are using MandarinCMS version %s. Please <a href="%s">update it</a>.', 'JW_maintenance-mode'), get_bloginfo('version'), admin_url('update-core.php')) . '</p></div>';
  } // notice_min_mcms_version_error


  // some things have to be loaded earlier
  static function modules_loaded() {
    self::get_module_version();

    load_module_textdomain('JW_maintenance-mode');
  } // modules_loaded


  // activate doesn't get fired on upgrades so we have to compensate
  public static function maybe_upgrade() {
    $meta = self::get_meta();
    $options = self::get_options();

    // added in v1.70 to rename roles to whitelisted_roles
    if (isset($options['roles'])) {
      $options['whitelisted_roles'] = $options['roles'];
      unset($options['roles']);
      update_option(JIICP_OPTIONS_KEY, $options);
    }

    // check if we need to convert options from the old format to new, or maybe it is already done
    if (isset($meta['options_ver']) && $meta['options_ver'] == self::$version) {
      return;
    }

    if (get_option('set_size') || get_option('set_tweet') || get_option('set_fb') || get_option('set_font') || get_option('set_msg') || get_option('set_opt') || get_option('set_admin')) {
      // convert old options to new
      $options['status'] = (get_option('set_opt') === 'Yes')? '1': '0';
      $options['content'] = trim(get_option('set_msg'));
      $options['whitelisted_roles'] = (get_option('set_admin') === 'No')? array('administrator'): array();
      $options['social_facebook'] = trim(get_option('set_fb'));
      $options['social_twitter'] = trim(get_option('set_tweet'));
      update_option(JIICP_OPTIONS_KEY, $options);

      delete_option('set_size');
      delete_option('set_tweet');
      delete_option('set_fb');
      delete_option('set_font');
      delete_option('set_msg');
      delete_option('set_opt');
      delete_option('set_admin');

      self::reset_pointers();
    }

    // we update only once
    $meta['options_ver'] = self::$version;
    update_option(JIICP_META_KEY, $meta);
  } // maybe_upgrade


  // get module's options
  static function get_options() {
    $options = get_option(JIICP_OPTIONS_KEY, array());

    if (!is_array($options)) {
      $options = array();
    }
    $options = array_merge(self::default_options(), $options);

    return $options;
  } // get_options


  // get module's meta data
  static function get_meta() {
    $meta = get_option(JIICP_META_KEY, array());

    if (!is_array($meta) || empty($meta)) {
      $meta['first_version'] = self::get_module_version();
      $meta['first_install'] = time();
      update_option(JIICP_META_KEY, $meta);
    }

    return $meta;
  } // get_meta


  // fetch and display the construction page if it's enabled or preview requested
  static function display_construction_page() {
    $options = self::get_options();
    $request_uri = trailingslashit(strtolower(@parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

    // just to be on the safe side
    if (defined('DOING_CRON') && DOING_CRON) {
      return false;
    }
    if (defined('DOING_AJAX') && DOING_AJAX) {
      return false;
    }
    if (defined('MCMS_CLI') && MCMS_CLI) {
      return false;
    }

    // some URLs have to be accessible at all times
    if ($request_uri == '/mcms-admin/' ||
        $request_uri == '/feed/' ||
        $request_uri == '/feed/rss/' ||
        $request_uri == '/feed/rss2/' ||
        $request_uri == '/feed/rdf/' ||
        $request_uri == '/feed/atom/' ||
        $request_uri == '/admin/' ||
        $request_uri == '/signin.php') {
      return;
    }

    if (true == self::is_construction_mode_enabled(false)
        || (is_user_logged_in() && isset($_GET['jiicp_preview']))) {
      header(self::mcms_get_server_protocol() . ' 200 OK');
      if ($options['end_date'] && $options['end_date'] != '0000-00-00 00:00') {
        header('Retry-After: ' . date('D, d M Y H:i:s T', strtotime($options['end_date'])));
      } else {
        header('Retry-After: ' . DAY_IN_SECONDS);
      }

      $myskins = self::get_myskins();
      if (!empty($_GET['myskin']) && substr($_GET['myskin'], 5) != '_pro_' && !empty($myskins[$_GET['myskin']])) {
        $myskin = $_GET['myskin'];
      } else {
        $myskin = $options['myskin'];
      }

      echo self::get_template($myskin);
      exit;
    }
  } // display_construction_page


  // keeping compatibility with MCMS < v4.4
  static function mcms_get_server_protocol() {
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    if (!in_array($protocol, array('HTTP/1.1', 'HTTP/2', 'HTTP/2.0'))) {
        $protocol = 'HTTP/1.0';
    }

    return $protocol;
  } // mcms_get_server_protocol


  // disables feed if necessary
  static function disable_feed() {
    if (true == self::is_construction_mode_enabled(false)) {
      echo '<?xml version="1.0" encoding="UTF-8" ?><status>Service unavailable.</status>';
      exit;
    }
  } // disable_feed


  // enqueue CSS and JS scripts in admin
  static function admin_enqueue_scripts($hook) {
    $surveys = get_option(JIICP_SURVEYS_KEY);
    $meta = self::get_meta();
    $pointers = get_option(JIICP_POINTERS_KEY);

    // auto remove welcome pointer when options are opened
    if (isset($pointers['welcome']) && 'settings_page_jiicp' == $hook) {
      unset($pointers['welcome']);
      update_option(JIICP_POINTERS_KEY, $pointers);
    }

    // survey is shown min 5min after install
    // no active surveys at the moment
    if (0 && empty($surveys['usage']) && time() - $meta['first_install'] > 300) {
      $open_survey = true;
    } else {
      $open_survey = false;
    }

    $promo = self::is_promo_active();
    if ($promo == 'welcome') {
      $countdown = $meta['first_install'] + HOUR_IN_SECONDS;
    } else {
      $countdown = 0;
    }

    $js_localize = array('undocumented_error' => __('An undocumented error has occured. Please refresh the page and try again.', 'JW_maintenance-mode'),
                         'module_name' => __('MaintenanceModePage', 'JW_maintenance-mode'),
                         'settings_url' => admin_url('options-general.php?page=jiicp'),
                         'whitelisted_users_placeholder' => __('Select whitelisted user(s)', 'JW_maintenance-mode'),
                         'open_survey' => $open_survey,
                         'promo_countdown' => $countdown,
                         'is_activated' => JIICP_license::is_activated(),
                         'dialog_upsell_title' => '<img alt="' . __('MaintenanceModePage PRO', 'JW_maintenance-mode') . '" title="' . __('MaintenanceModePage PRO', 'JW_maintenance-mode') . '" src="' . JIICP_MODULE_URL . 'images/jiicp_pro_logo_white.png' . '">',
                         'nonce_dismiss_survey' => mcms_create_nonce('jiicp_dismiss_survey'),
                         'nonce_submit_survey' => mcms_create_nonce('jiicp_submit_survey'),
                         'nonce_submit_earlybird' => mcms_create_nonce('jiicp_submit_earlybird'),
                         'nonce_submit_support_message' => mcms_create_nonce('jiicp_submit_support_message'),
                         'deactivate_confirmation' => __('Are you sure you want to deactivate MaintenanceMode module?' . "\n" . 'If you are removing it because of a problem please contact our support. They will be more than happy to help.', 'JW_maintenance-mode'));

    if ('settings_page_jiicp' == $hook) {
      mcms_enqueue_style('mcms-jquery-ui-dialog');
      mcms_enqueue_style('jiicp-select2', JIICP_MODULE_URL . 'css/select2.min.css', array(), self::$version);
      mcms_enqueue_style('jiicp-admin', JIICP_MODULE_URL . 'css/jiicp-admin.css', array(), self::$version);

      mcms_enqueue_script('jquery-ui-tabs');
      mcms_enqueue_script('jquery-ui-dialog');
      mcms_enqueue_script('jiicp-jquery-modules', JIICP_MODULE_URL . 'js/jiicp-jquery-modules.js', array('jquery'), self::$version, true);
      mcms_enqueue_script('jiicp-select2', JIICP_MODULE_URL . 'js/select2.min.js', array(), self::$version, true);
      mcms_enqueue_script('jiicp-admin', JIICP_MODULE_URL . 'js/jiicp-admin.js', array('jquery'), self::$version, true);
      mcms_localize_script('jiicp-admin', 'jiicp', $js_localize);

      // fix for agressive modules
      mcms_dequeue_style('uiStyleSheet');
      mcms_dequeue_style('mcmscufpnAdmin' );
      mcms_dequeue_style('unifStyleSheet' );
      mcms_dequeue_style('mcmscufpn_codemirror');
      mcms_dequeue_style('mcmscufpn_codemirrorMySkin');
      mcms_dequeue_style('collapse-admin-css');
      mcms_dequeue_style('jquery-ui-css');
      mcms_dequeue_style('tribe-common-admin');
      mcms_dequeue_style('file-manager__jquery-ui-css');
      mcms_dequeue_style('file-manager__jquery-ui-css-myskin');
      mcms_dequeue_style('mcmsmegmaps-jqueryui');
      mcms_dequeue_style('mcms-botwatch-css');
    }

    // disabled - regular deactivation is back
    if (false && 'modules.php' == $hook) {
      mcms_enqueue_style('mcms-jquery-ui-dialog');
      mcms_enqueue_style('jiicp-admin-modules', JIICP_MODULE_URL . 'css/jiicp-admin-modules.css', array(), self::$version);

      mcms_enqueue_script('jquery-ui-dialog');
      mcms_enqueue_script('jiicp-admin-modules', JIICP_MODULE_URL . 'js/jiicp-admin-modules.js', array('jquery'), self::$version, true);
      mcms_localize_script('jiicp-admin-modules', 'jiicp', $js_localize);
    }

    if ($pointers) {
      $pointers['_nonce_dismiss_pointer'] = mcms_create_nonce('jiicp_dismiss_pointer');
      mcms_enqueue_script('mcms-pointer');
      mcms_enqueue_script('jiicp-pointers', modules_url('js/jiicp-admin-pointers.js', __FILE__), array('jquery'), self::$version, true);
      mcms_enqueue_style('mcms-pointer');
      mcms_localize_script('mcms-pointer', 'jiicp_pointers', $pointers);
      mcms_localize_script('jquery', 'jiicp', $js_localize);
    }
  } // admin_enqueue_scripts


  // permanently dismiss a pointer
  static function dismiss_pointer_ajax() {
    check_ajax_referer('jiicp_dismiss_pointer');

    $pointers = get_option(JIICP_POINTERS_KEY);
    $pointer = trim($_POST['pointer']);

    if (empty($pointers) || empty($pointers[$pointer])) {
      mcms_send_json_error();
    }

    unset($pointers[$pointer]);
    update_option(JIICP_POINTERS_KEY, $pointers);

    mcms_send_json_success();
  } // dismiss_pointer_ajax


  // permanently dismiss a survey
  static function dismiss_survey_ajax() {
    check_ajax_referer('jiicp_dismiss_survey');

    $surveys = get_option(JIICP_SURVEYS_KEY, array());
    $survey = trim($_POST['survey']);

    $surveys[$survey] = -1;
    update_option(JIICP_SURVEYS_KEY, $surveys);

    mcms_send_json_success();
  } // dismiss_survey_ajax


  // send support message
  static function submit_support_message_ajax() {
    check_ajax_referer('jiicp_submit_support_message');

    $options = self::get_options();

    $email = sanitize_text_field($_POST['support_email']);
    if (!is_email($email)) {
      mcms_send_json_error(__('Please double-check your email address.', 'JW_maintenance-mode'));
    }

    $message = stripslashes(sanitize_text_field($_POST['support_message']));
    $subject = 'JIICP Support';
    $body = $message;
    if (!empty($_POST['support_info'])) {
      $myskin = mcms_get_myskin();
      $body .= "\r\n\r\nSite details:\r\n";
      $body .= '  MandarinCMS version: ' . get_bloginfo('version') . "\r\n";
      $body .= '  JIICP version: ' . self::$version . "\r\n";
      $body .= '  PHP version: ' . PHP_VERSION . "\r\n";
      $body .= '  Site URL: ' . get_bloginfo('url') . "\r\n";
      $body .= '  MandarinCMS URL: ' . get_bloginfo('mcmsurl') . "\r\n";
      $body .= '  MySkin: ' . $myskin->get('Name') . ' v' . $myskin->get('Version') . "\r\n";
      $body .= '  Options: ' . "\r\n" . serialize($options) . "\r\n";
    }
    $headers = 'From: ' . $email . "\r\n" . 'Reply-To: ' . $email;

    if (true === mcms_mail('jiicp@jiiworks.net', $subject, $body, $headers)) {
      mcms_send_json_success();
    } else {
      mcms_send_json_error(__('Something is not right with your mcms_mail() function. Please email as at jiicp@jiiworks.net.', 'JW_maintenance-mode'));
    }
  } // submit_support_message


  // submit survey
  static function submit_survey_ajax() {
    check_ajax_referer('jiicp_submit_survey');

    $options = self::get_options();
    $meta = self::get_meta();
    $surveys = get_option(JIICP_SURVEYS_KEY);

    $vars = mcms_parse_args($_POST, array('survey' => '', 'answers' => '', 'custom_answer' => $options['myskin'], 'emailme' => ''));
    $vars['answers'] = trim($vars['answers'], ',');
    $vars['custom_answer'] = trim(strip_tags($vars['custom_answer']));

    $vars['custom_answer'] .= '; ' . date('Y-m-d H:i:s', $meta['first_install']);
    $vars['custom_answer'] = trim($vars['custom_answer'], ' ;');

    if (empty($vars['survey']) || empty($vars['answers'])) {
      mcms_send_json_error();
    }

    $request_params = array('sslverify' => false, 'timeout' => 15, 'redirection' => 2);
    $request_args = array('action' => 'submit_survey',
                          'survey' => $vars['survey'],
                          'email' => $vars['emailme'],
                          'answers' => $vars['answers'],
                          'custom_answer' => $vars['custom_answer'],
                          'first_version' => $meta['first_version'],
                          'version' => JIICP::$version,
                          'codebase' => 'free',
                          'site' => get_home_url());

    $url = add_query_arg($request_args, self::$licensing_servers[0]);
    $response = mcms_remote_get(esc_url_raw($url), $request_params);

    if (is_mcms_error($response) || !mcms_remote_retrieve_body($response)) {
      $url = add_query_arg($request_args, self::$licensing_servers[1]);
      $response = mcms_remote_get(esc_url_raw($url), $request_params);
    }

    $surveys[$vars['survey']] = time();
    update_option(JIICP_SURVEYS_KEY, $surveys);

    mcms_send_json_success();
  } // submit_survey_ajax


  // submit earlybird email
  static function submit_earlybird_ajax() {
    check_ajax_referer('jiicp_submit_earlybird');

    $options = self::get_options();
    $meta = self::get_meta();

    $vars = mcms_parse_args($_POST, array('type' => '', 'email' => ''));

    if (empty($vars['email']) || empty($vars['type'])) {
      mcms_send_json_error(__('Please tell us your email and how you use JIICP.', 'JW_maintenance-mode'));
    }

    $request_params = array('sslverify' => false, 'timeout' => 15, 'redirection' => 2);
    $request_args = array('action' => 'submit_survey',
                          'survey' => 'earlybird',
                          'email' => $vars['email'],
                          'answers' => $vars['type'],
                          'custom_answer' => $options['myskin'] . '; ' . date('Y-m-d H:i:s', $meta['first_install']),
                          'first_version' => $meta['first_version'],
                          'version' => JIICP::$version,
                          'codebase' => 'free',
                          'site' => get_home_url());

    $url = add_query_arg($request_args, self::$licensing_servers[0]);
    $response = mcms_remote_get(esc_url_raw($url), $request_params);

    if (is_mcms_error($response) || !mcms_remote_retrieve_body($response)) {
      $url = add_query_arg($request_args, self::$licensing_servers[1]);
      $response = mcms_remote_get(esc_url_raw($url), $request_params);
    }

    mcms_send_json_success();
  } // submit_earlybird_ajax


  // encode email for frontend use
  static function encode_email($email) {
    $len = strlen($email);
    $out = '';

    for ($i = 0; $i < $len; $i++) {
      $out .= '&#'. ord($email[$i]) . ';';
    }

    return $out;
  } // encode_email


  // parse shortcode alike variables
  static function parse_vars($string) {
    $org_string = $string;

    $vars = array('site-title' => get_bloginfo('name'),
                  'site-tagline' => get_bloginfo('description'),
                  'site-description' => get_bloginfo('description'),
                  'site-url' => trailingslashit(get_home_url()),
                  'mcms-url' => trailingslashit(get_site_url()),
                  'site-login-url' => get_site_url() . '/signin.php');

    foreach ($vars as $var_name => $var_value) {
      $var_name = '[' . $var_name . ']';
      $string = str_ireplace($var_name, $var_value, $string);
    }

    $string = apply_filters('jiicp_parse_vars', $string, $org_string, $vars);

    return $string;
  } // parse_vars


  // generate HTML from social icons
  static function generate_social_icons($options, $template_id) {
    $out = '';

    if (!empty($options['social_facebook'])) {
      $out .= '<a title="Facebook" href="' . $options['social_facebook'] . '" target="_blank"><i class="fa fa-facebook-square fa-3x"></i></a>';
    }
    if (!empty($options['social_twitter'])) {
      $out .= '<a title="Twitter" href="' . $options['social_twitter'] . '" target="_blank"><i class="fa fa-twitter-square fa-3x"></i></a>';
    }
    if (!empty($options['social_google'])) {
      $out .= '<a title="Google+" href="' . $options['social_google'] . '" target="_blank"><i class="fa fa-google-plus-square fa-3x"></i></a>';
    }
    if (!empty($options['social_linkedin'])) {
      $out .= '<a title="LinkedIn" href="' . $options['social_linkedin'] . '" target="_blank"><i class="fa fa-linkedin-square fa-3x"></i></a>';
    }
    if (!empty($options['social_youtube'])) {
      $out .= '<a title="YouTube" href="' . $options['social_youtube'] . '" target="_blank"><i class="fa fa-youtube-square fa-3x"></i></a>';
    }
    if (!empty($options['social_vimeo'])) {
      $out .= '<a title="Vimeo" href="' . $options['social_vimeo'] . '" target="_blank"><i class="fa fa-vimeo-square fa-3x"></i></a>';
    }
    if (!empty($options['social_pinterest'])) {
      $out .= '<a title="Pinterest" href="' . $options['social_pinterest'] . '" target="_blank"><i class="fa fa-pinterest-square fa-3x"></i></a>';
    }
    if (!empty($options['social_dribbble'])) {
      $out .= '<a title="Dribbble" href="' . $options['social_dribbble'] . '" target="_blank"><i class="fa fa-dribbble fa-3x"></i></a>';
    }
    if (!empty($options['social_behance'])) {
      $out .= '<a title="Behance" href="' . $options['social_behance'] . '" target="_blank"><i class="fa fa-behance-square fa-3x"></i></a>';
    }
    if (!empty($options['social_instagram'])) {
      $out .= '<a title="Instagram" href="' . $options['social_instagram'] . '" target="_blank"><i class="fa fa-instagram fa-3x"></i></a>';
    }
    if (!empty($options['social_tumblr'])) {
      $out .= '<a title="Tumblr" href="' . $options['social_tumblr'] . '" target="_blank"><i class="fa fa-tumblr-square fa-3x"></i></a>';
    }
    if (!empty($options['social_vk'])) {
      $out .= '<a title="VK" href="' . $options['social_vk'] . '" target="_blank"><i class="fa fa-vk fa-3x"></i></a>';
    }
    if (!empty($options['social_skype'])) {
      $out .= '<a title="Skype" href="skype:' . $options['social_skype'] . '?chat"><i class="fa fa-skype fa-3x"></i></a>';
    }
    if (!empty($options['social_whatsapp'])) {
      $out .= '<a title="WhatsApp" href="whatsapp:' . $options['social_whatsapp'] . '"><i class="fa fa-whatsapp fa-3x"></i></a>';
    }
    if (!empty($options['social_telegram'])) {
      $out .= '<a title="Telegram" href="' . $options['social_telegram'] . '"><i class="fa fa-telegram fa-3x"></i></a>';
    }
    if (!empty($options['social_email'])) {
      $out .= '<a title="Email" href="mailto:' . self::encode_email($options['social_email']) . '"><i class="fa fa-envelope fa-3x"></i></a>';
    }
    if (!empty($options['social_phone'])) {
      $out .= '<a title="Phone" href="tel:' . $options['social_phone'] . '"><i class="fa fa-phone-square fa-3x"></i></a>';
    }

    return $out;
  } // generate_social_icons


  // shortcode for inserting things in header
  static function generate_head($options, $template_id) {
    $out = '';

    $out .= '<link rel="stylesheet" href="' . trailingslashit(JIICP_MODULE_URL . 'myskins/css') . 'bootstrap.min.css?v=' . self::$version . '" type="text/css">' . "\n";
    $out .= '<link rel="stylesheet" href="' . trailingslashit(JIICP_MODULE_URL . 'myskins/css') . 'common.css?v=' . self::$version . '" type="text/css">' . "\n";
    $out .= '<link rel="stylesheet" href="' . trailingslashit(JIICP_MODULE_URL . 'myskins/' . $template_id) . 'style.css?v=' . self::$version . '" type="text/css">' . "\n";
    $out .= '<link rel="stylesheet" href="' . trailingslashit(JIICP_MODULE_URL . 'myskins/css') . 'font-awesome.min.css?v=' . self::$version . '" type="text/css">' . "\n";

    $out .= '<link rel="shortcut icon" type="image/png" href="' . trailingslashit(JIICP_MODULE_URL . 'myskins/images') . 'favicon.png" />';

    if (!empty($options['ga_tracking_id'])) {
      $out .= "
      <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        ga('create', '{$options['ga_tracking_id']}', 'auto');
        ga('send', 'pageview');
      </script>";
    }

    if (!empty($options['custom_css'])) {
      $out .= "\n" . '<style type="text/css">' . $options['custom_css'] . '</style>';
    }

    $out = apply_filters('jiicp_head', $out, $options, $template_id);

    return trim($out);
  } // generate_head


  // shortcode for inserting things in footer
  static function generate_footer($options, $template_id) {
    $out = '';

    // todo translate
    if ($options['linkback'] == '1') {
      $tmp = md5(get_site_url());
      if ($tmp[0] < '4') {
        $out .= '<p id="linkback">Create stunning <a href="' . self::generate_web_link('show-love-1')  . '" target="_blank">under construction pages for MandarinCMS</a>. Completely free.</p>';
      } elseif ($tmp[0] < '8') {
        $out .= '<p id="linkback">Create a <a href="' . self::generate_web_link('show-love-2')  . '" target="_blank">free under construction page for MandarinCMS</a> like this one in under a minute.</p>';
      } elseif ($tmp[0] < 'c') {
        $out .= '<p id="linkback">Join more than 100,000 happy people using the <a href="https://mandarincms.org/modules/JW_maintenance-mode/" target="_blank">free Maintenance Page module for MandarinCMS</a>.</p>';
      } else {
        $out .= '<p id="linkback">Create free <a href="' . self::generate_web_link('show-love-3')  . '" target="_blank">maintenance mode pages for MandarinCMS</a>.</p>';
      }
    }

    if ($options['login_button'] == '1') {
      if (is_user_logged_in()) {
        $out .= '<div id="login-button" class="loggedin">';
        $out .= '<a title="' . __('Open MandarinCMS admin', 'JW_maintenance-mode') . '" href="' . get_site_url() . '/mcms-admin/"><i class="fa fa-mandarincms fa-2x" aria-hidden="true"></i></a>';
      } else {
        $out .= '<div id="login-button" class="loggedout">';
        $out .= '<a title="' . __('Log in to MandarinCMS admin', 'JW_maintenance-mode') . '" href="' . get_site_url() . '/signin.php"><i class="fa fa-mandarincms fa-2x" aria-hidden="true"></i></a>';
      }
      $out .= '</div>';
    }

    $out = apply_filters('jiicp_footer', $out, $options, $template_id);

    return $out;
  } // generate_footer


  // returnes parsed template
  static function get_template($template_id) {
    $vars = array();
    $options = self::get_options();

    $vars['version'] = self::$version;
    $vars['site-url'] = trailingslashit(get_home_url());
    $vars['mcms-url'] = trailingslashit(get_site_url());
    $vars['myskin-url'] = trailingslashit(JIICP_MODULE_URL . 'myskins/' . $template_id);
    $vars['myskin-url-common'] = trailingslashit(JIICP_MODULE_URL . 'myskins');
    $vars['title'] = self::parse_vars($options['title']);
    $vars['generator'] = __('Free MaintenanceModePage module for MandarinCMS', 'JW_maintenance-mode');
    $vars['heading1'] = self::parse_vars($options['heading1']);
    $vars['content'] = nl2br(self::parse_vars($options['content']));
    $vars['description'] = self::parse_vars($options['description']);
    $vars['social-icons'] = self::generate_social_icons($options, $template_id);
    $vars['head'] = self::generate_head($options, $template_id);
    $vars['footer'] = self::generate_footer($options, $template_id);

    $vars = apply_filters('jiicp_get_template_vars', $vars, $template_id, $options);

    ob_start();
    require JIICP_MODULE_DIR . 'myskins/' . $template_id . '/index.php';
    $template = ob_get_clean();

    foreach ($vars as $var_name => $var_value) {
      $var_name = '[' . $var_name . ']';
      $template = str_ireplace($var_name, $var_value, $template);
    }

    $template = apply_filters('jiicp_get_template', $template, $vars, $options);

    return $template;
  } // get_template


  // checks if construction mode is enabled for the current visitor
  static function is_construction_mode_enabled($settings_only = false) {
    $options = self::get_options();
    $current_user = mcms_get_current_user();

    $override_status = apply_filters('jiicp_is_construction_mode_enabled', null, $options);
    if (is_bool($override_status)) {
      return $override_status;
    }

    // just check if it's generally enabled
    if ($settings_only) {
      if ($options['status']) {
        return true;
      } else {
        return false;
      }
    } else {
      // check if enabled for current user
      if (!$options['status']) {
        return false;
      } elseif (self::user_has_role($options['whitelisted_roles'])) {
        return false;
      } elseif (in_array($current_user->ID, $options['whitelisted_users'])) {
        return false;
      } elseif (strlen($options['end_date']) === 16 && $options['end_date'] !== '0000-00-00 00:00' && $options['end_date'] < current_time('mysql')) {
        return false;
      } else {
        return true;
      }
    }
  } // is_construction_mode_enabled


  // check if user has the specified role
  static function user_has_role($roles) {
    $current_user = mcms_get_current_user();

    if ($current_user->roles) {
      $user_role = $current_user->roles[0];
    } else {
      $user_role = 'guest';
    }

    return in_array($user_role, $roles);
  } // user_has_role


  // frontend notification when JIICP is enabled but current user is whitelisted
  static function whitelisted_notice() {
    $notices = get_option(JIICP_NOTICES_KEY);
    $dismiss_url = add_query_arg(array('action' => 'jiicp_dismiss_notice', 'notice' => 'whitelisted', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

    if (empty($notices['dismiss_whitelisted']) &&
        is_user_logged_in() &&
        self::is_construction_mode_enabled(true) &&
        !self::is_construction_mode_enabled(false))
        // keeping everything inline due to minimal CSS
        echo '<div style="background-color: #333; line-height: 140%; font-size: 14px; position: fixed; display: block; top: 50px; z-index: 99999; color: #fefefe; padding: 20px 35px 20px 20px; width: 500px; border: thin solid #fefefe; left: -1px;"><a style="color: #ea1919; font-weight: 900; text-decoration: none; position: absolute; top: 7px; right: 10px;" href="' . $dismiss_url . '" alt="Dismiss notice" onclick="window.location.href = \'' . $dismiss_url . '\'; return false;" title="Dismiss notice">X</a><b>' . __('<b>Maintenance Mode is enabled</b> but you are whitelisted so you see the normal site.', 'JW_maintenance-mode') . '<br><a href="' . get_home_url() . '/?jiicp_preview" style="text-decoration: underline; color: #fefefe;">' . __('Preview MaintenanceModePage', 'JW_maintenance-mode') . '</a><br><a href="' . admin_url('options-general.php?page=jiicp') . '" style="text-decoration: underline; color: #fefefe;">' . __('Configure MaintenanceModePage', 'JW_maintenance-mode') . '</a></div>';
  } // whitelisted_notification


  // displays various notices in admin header
  static function admin_notices() {
    $notices = get_option(JIICP_NOTICES_KEY);
    $options = self::get_options();
    $meta = self::get_meta();
    $current_user = mcms_get_current_user();
    $shown = false;
    $promo = self::is_promo_active();

    $name = '';
    if (!empty($current_user->user_firstname)) {
      $name = ' ' . $current_user->user_firstname;
    }

    // pro activated - update
    if (self::is_module_page() && JIICP_license::is_activated()) {
      $module = module_basename(__FILE__);
      $update_url = mcms_nonce_url(admin_url('update.php?action=upgrade-module&amp;module=' . urlencode($module)), 'upgrade-module_' . $module);
      echo '<div id="jiicp_update_pro" class="notice-error notice">';
      echo '<p class="center">Thank you for purchasing MaintenanceModePage PRO! <b>Your license has been verified and activated.</b></p><p>Please <b>click the button below</b> to update module files to PRO version.</p>';
      echo '<p><a href="' . esc_url($update_url) . '" class="button button-primary">Update MaintenanceModePage files to PRO</a></p>';
      echo '</div>';
      $shown = false;

      return;
    }

    // todo translate
    // ask for rating
    // disabled
    if (false && empty($notices['dismiss_rate']) &&
        (time() - $meta['first_install']) > (DAY_IN_SECONDS * 1.0)) {
      $rate_url = 'https://mandarincms.org/support/module/JW_maintenance-mode/reviews/?filter=5&rate=5#new-post';
      $dismiss_url = add_query_arg(array('action' => 'jiicp_dismiss_notice', 'notice' => 'rate', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

      echo '<div id="jiicp_rate_notice" class="notice-info notice"><p>Hi' . $name . '!<br>We saw you\'ve been using the <b class="jiicp-logo" style="font-weight: bold;">MaintenanceModePage</b> module for a few days (that\'s awesome!) and wanted to ask for your help to <b>make the module better</b>.<br>We just need a minute of your time to rate the module. It helps us out a lot!';

      echo '<br><a target="_blank" href="' . esc_url($rate_url) . '" style="vertical-align: baseline; margin-top: 15px;" class="button-primary">' . __('Help make the module better by rating it', 'JW_maintenance-mode') . '</a>';
      echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">' . __('I\'ve already rated the module', 'JW_maintenance-mode') . '</a>';
      echo '<br><br><b>' . __('Thank you very much! The JIICP team', 'JW_maintenance-mode') . '</b>';
      echo '</p></div>';
      $shown = true;
    }

    // end date in past
    if (self::is_module_page() && self::is_construction_mode_enabled(true) && !empty($options['end_date']) && $options['end_date'] != '0000-00-00 00:00' && $options['end_date'] < current_time('mysql')) {
      echo '<div id="jiicp_end_date_notice" class="notice-error notice"><p>Under construction mode is enabled but the <a href="#end_date" class="change_tab" data-tab="0">end date</a> is set to a past date so the <b>under construction page will not be shown</b>. Either move the <a href="#end_date" class="change_tab" data-tab="0">end date</a> to a future date or disable it.</p></div>';
      $shown = true;
    }

    // ask for translation
    // disabled
    if (false && self::is_module_page() &&
        empty($notices['dismiss_translate']) &&
        (time() - $meta['first_install']) > (DAY_IN_SECONDS * 1)) {
      $translate_url = self::generate_web_link('translate-notification', 'translate-the-module/');
      $dismiss_url = add_query_arg(array('action' => 'jiicp_dismiss_notice', 'notice' => 'translate', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

      echo '<div id="jiicp_rate_notice" class="notice-info notice"><p>Hi' . $name . ',<br>Help us translate JIICP into your language and <b>get a PRO license for free</b>!<br>We want to make <b class="jiicp-logo" style="font-weight: bold;">MaintenanceModePage</b> accessible to as many users as possible by translating it into their language. And we need your help!';

      echo '<br><a target="_blank" href="' . esc_url($translate_url) . '" style="vertical-align: baseline; margin-top: 15px;" class="button-primary">' . __('Translate JIICP into your language &amp; get a PRO license for free', 'JW_maintenance-mode') . '</a>';
      echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">' . __('I\'m not interested (remove this notice)', 'JW_maintenance-mode') . '</a>';
      echo '</p></div>';
      $shown = true;
    }

    // promo for old users
    if (self::is_module_page() &&
        empty($notices['dismiss_olduser']) &&
        !$shown && $promo == 'olduser') {
      $dismiss_url = add_query_arg(array('action' => 'jiicp_dismiss_notice', 'notice' => 'olduser', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

      echo '<div id="jiicp_rate_notice" class="notice-info notice"><p>Hi' . $name . ',<br>';
      echo 'We have a <a class="open-jiicp-upsell" data-pro-ad="notification-olduser-text" href="#">special offer</a> only for <b>users like you</b> who\'ve been using the MaintenanceModePage for a long period of time: a <b>special DISCOUNT</b> on our most popular lifetime licenses!<br>No nonsense! Pay once and use the module forever.<br><a class="open-jiicp-upsell" data-pro-ad="notification-olduser-text" href="#">Upgrade now</a> to <b>PRO</b> &amp; get more than 50 extra features, premium myskins and 400,000+ images.</p>';

      echo '<a href="#" style="vertical-align: baseline; margin-top: 15px;" class="button-primary open-jiicp-upsell" data-pro-ad="notification-olduser-button">Upgrade to <b>PRO</b> now with a <b>SPECIAL DISCOUNT</b></a>';
      echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '"><small>' . __('I\'m not interested (remove this notice)', 'JW_maintenance-mode') . '</small></a>';
      echo '</p></div>';
      $shown = true;
    }
  } // notices


  // handle dismiss button for notices
  static function dismiss_notice() {
    if (empty($_GET['notice'])) {
      mcms_redirect(admin_url());
      exit;
    }

    $notices = get_option(JIICP_NOTICES_KEY, array());

    if ($_GET['notice'] == 'rate') {
      $notices['dismiss_rate'] = true;
    }

    if ($_GET['notice'] == 'translate') {
      $notices['dismiss_translate'] = true;
    }

    if ($_GET['notice'] == 'whitelisted') {
      $notices['dismiss_whitelisted'] = true;
    }

    if ($_GET['notice'] == 'olduser') {
      $notices['dismiss_olduser'] = true;
    }

    update_option(JIICP_NOTICES_KEY, $notices);

    if (!empty($_GET['redirect'])) {
      mcms_redirect($_GET['redirect']);
    } else {
      mcms_redirect(admin_url());
    }

    exit;
  } // dismiss_notice


  // reset all settings to default values
  static function reset_settings() {
    $options = self::default_options();
    update_option(JIICP_OPTIONS_KEY, $options);

    if (!empty($_GET['redirect'])) {
      mcms_redirect($_GET['redirect']);
    } else {
      mcms_redirect(admin_url());
    }

    exit;
  } // reset_settings


  // change status via admin bar
  static function change_status() {
    if (empty($_GET['new_status'])) {
      mcms_redirect(admin_url());
      exit;
    }

    $options = self::get_options();

    if ($_GET['new_status'] == 'enabled') {
      $options['status'] = '1';
    } else {
      $options['status'] = '0';
    }

    update_option(JIICP_OPTIONS_KEY, $options);

    if (!empty($_GET['redirect'])) {
      mcms_redirect($_GET['redirect']);
    } else {
      mcms_redirect(admin_url());
    }

    exit;
  } // change_status


  static function admin_bar_style() {
    // admin bar has to be anabled, user an admin and custom filter true
    if (false === is_admin_bar_showing() || false === current_user_can('administrator') || false === apply_filters('jiicp_show_admin_bar', true)) {
      return;
    }

    // no sense in loading a new CSS file for 2 lines of CSS
    $custom_css = '<style type="text/css">#mcmsadminbar ul li#mcms-admin-bar-jiicp-info { padding: 5px 0; } #mcmsadminbar ul li#mcms-admin-bar-jiicp-settings, #mcmsadminbar ul li#mcms-admin-bar-jiicp-status { padding-bottom: 2px; } #mcmsadminbar i.jiicp-status-dot { font-size: 17px; margin-top: -7px; color: #02ca02; height: 17px; display: inline-block; } #mcmsadminbar i.jiicp-status-dot-enabled { color: #87c826; } #mcmsadminbar i.jiicp-status-dot-disabled { color: #ea1919; } #mcmsadminbar #jiicp-status-wrapper { display: inline; border: 1px solid rgba(240,245,250,.7); padding: 0; margin: 0 0 0 5px; background: rgb(35, 40, 45); } #mcmsadminbar .jiicp-status-btn { padding: 0 7px; color: #fff; } #mcmsadminbar #jiicp-status-wrapper.off #jiicp-status-off { background: #ea1919;} #mcmsadminbar #jiicp-status-wrapper.on #jiicp-status-on { background: #66b317; }#mcms-admin-bar-JW_maintenance-mode img.logo { height: 17px; margin-bottom: 4px; padding-right: 3px; } body.mcms-admin #mcms-admin-bar-JW_maintenance-mode img.logo { margin-bottom: -4px; }</style>';

    echo $custom_css;
  } // admin_bar_style


  // add admin bar menu and status
  static function admin_bar() {
    global $mcms_admin_bar;

    // only show to admins
    if (false === current_user_can('administrator') || false === apply_filters('jiicp_show_admin_bar', true)) {
      return;
    }

    if (self::is_construction_mode_enabled(true)) {
      $main_label = '<img style="height: 17px; margin-bottom: -4px; padding-right: 3px;" src="' . JIICP_MODULE_URL . 'images/jiicp_icon.png" alt="' . __('Under construction mode is enabled', 'JW_maintenance-mode') . '" title="' . __('Under construction mode is enabled', 'JW_maintenance-mode') . '"> <span class="ab-label">' . __('MaintenanceMode', 'JW_maintenance-mode') . ' <i class="jiicp-status-dot jiicp-status-dot-enabled">&#9679;</i></span>';
      $class = 'jiicp-enabled';
      $action_url = add_query_arg(array('action' => 'jiicp_change_status', 'new_status' => 'disabled', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));
      $action = __('Maintenance Mode', 'JW_maintenance-mode');
      $action .= '<a href="' . $action_url . '" id="jiicp-status-wrapper" class="on"><span id="jiicp-status-off" class="jiicp-status-btn">OFF</span><span id="jiicp-status-on" class="jiicp-status-btn">ON</span></a>';
    } else {
      $main_label = '<img style="height: 17px; margin-bottom: -4px; padding-right: 3px;" src="' . JIICP_MODULE_URL . 'images/jiicp_icon.png" alt="' . __('Under construction mode is disabled', 'JW_maintenance-mode') . '" title="' . __('Under construction mode is disabled', 'JW_maintenance-mode') . '"> <span class="ab-label">' . __('MaintenanceMode', 'JW_maintenance-mode') . ' <i class="jiicp-status-dot jiicp-status-dot-disabled">&#9679;</i></span>';
      $class = 'jiicp-disabled';
      $action_url = add_query_arg(array('action' => 'jiicp_change_status', 'new_status' => 'enabled', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));
      $action = __('Maintenance Mode', 'JW_maintenance-mode');
      $action .= '<a href="' . $action_url . '" id="jiicp-status-wrapper" class="off"><span id="jiicp-status-off" class="jiicp-status-btn">OFF</span><span id="jiicp-status-on" class="jiicp-status-btn">ON</span></a>';
    }

    $mcms_admin_bar->add_menu(array(
      'parent' => '',
      'id'     => 'JW_maintenance-mode',
      'title'  => $main_label,
      'href'   => admin_url('options-general.php?page=jiicp'),
      'meta'   => array('class' => $class)
    ));
    $mcms_admin_bar->add_node( array(
      'id'    => 'jiicp-status',
      'title' => $action,
      'href'  => false,
      'parent'=> 'JW_maintenance-mode'
    ));
    $mcms_admin_bar->add_node( array(
      'id'     => 'jiicp-preview',
      'title'  => __('Preview', 'JW_maintenance-mode'),
      'meta'   => array('target' => 'blank'),
      'href'   => get_home_url() . '/?jiicp_preview',
      'parent' => 'JW_maintenance-mode'
    ));
    $mcms_admin_bar->add_node( array(
      'id'     => 'jiicp-settings',
      'title'  => __('Settings', 'JW_maintenance-mode'),
      'href'   => admin_url('options-general.php?page=jiicp'),
      'parent' => 'JW_maintenance-mode'
    ));
  } // admin_bar


  // show under construction notice on MCMS login form
  static function login_message($message) {
    if (self::is_construction_mode_enabled(true)) {
      $message .= '<div class="message">' . __('Maintenance Mode is <b>enabled</b>.', 'JW_maintenance-mode') . '</div>';
    }

    return $message;
  } // login_notice


  // add settings link to modules page
  static function module_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=jiicp') . '" title="' . __('MaintenanceMode Settings', 'JW_maintenance-mode') . '">' . __('Settings', 'JW_maintenance-mode') . '</a>';
    

    array_unshift($links, $pro_link);
    array_unshift($links, $settings_link);

    if (!empty($links['deactivate'])) {
      $links['deactivate'] = str_replace('href=',' data-JW_maintenance-mode="true" href=', $links['deactivate']);
    }

    return $links;
  } // module_action_links


  // add links to module's description in modules table
  static function module_meta_links($links, $file) {
    return $links;
  } // module_meta_links


  // additional powered by text in admin footer; only on JIICP page
  static function admin_footer_text($text) {
    if (!self::is_module_page()) {
      return $text;
    }

    $text = '<i><a href="' . self::generate_web_link('admin-footer') . '" title="' . __('Visit JIICP\'s site for more info', 'JW_maintenance-mode') . '" target="_blank">' . __('MaintenanceModePage', 'JW_maintenance-mode') . '</a> v' . self::$version . ' by <a href="https://www.jiiworks.net/" title="' . __('Visit our site to get more great modules', 'JW_maintenance-mode') . '" target="_blank">' . __('WebFactory Ltd', 'JW_maintenance-mode') . '</a>.</i> '. $text;

    return $text;
  } // admin_footer_text


  // test if we're on module's page
  static function is_module_page() {
    $current_screen = get_current_screen();

    if ($current_screen->id == 'settings_page_jiicp') {
      return true;
    } else {
      return false;
    }
  } // is_module_page


  // create the admin menu item
  static function admin_menu() {
    add_options_page(__('MaintenanceMode', 'JW_maintenance-mode'), __('MaintenanceMode', 'JW_maintenance-mode'), 'manage_options', 'jiicp', array(__CLASS__, 'main_page'));
  } // admin_menu


  // all settings are saved in one option
  static function register_settings() {
    register_setting(JIICP_OPTIONS_KEY, JIICP_OPTIONS_KEY, array(__CLASS__, 'sanitize_settings'));
  } // register_settings


  // set default settings
  static function default_options() {
    $defaults = array('status' => '0',
                      'license_key' => '',
                      'license_active' => false,
                      'license_expires' => '1900-01-01',
                      'license_type' => '',
                      'end_date' => '',
                      'ga_tracking_id' => '',
                      'myskin' => 'mad_designer',
                      'custom_css' => '',
                      'title' => '[site-title] is under construction',
                      'description' => '[site-tagline]',
                      'heading1' => __('Sorry, we\'re doing some work on the site', 'JW_maintenance-mode'),
                      'content' => __('Thank you for being patient. We are doing some work on the site and will be back shortly.', 'JW_maintenance-mode'),
                      'social_facebook' => '',
                      'social_twitter' => '',
                      'social_google' => '',
                      'social_linkedin' => '',
                      'social_youtube' => '',
                      'social_vimeo' => '',
                      'social_pinterest' => '',
                      'social_dribbble' => '',
                      'social_behance' => '',
                      'social_instagram' => '',
                      'social_tumblr' => '',
                      'social_vk' => '',
                      'social_email' => '',
                      'social_phone' => '',
                      'social_skype' => '',
                      'social_telegram' => '',
                      'social_whatsapp' => '',
                      'login_button' => '1',
                      'linkback' => '0',
                      'whitelisted_roles' => array('administrator'),
                      'whitelisted_users' => array()
                      );

    return $defaults;
  } // default_options


  // sanitize settings on save
  static function sanitize_settings($options) {
    $old_options = self::get_options();

    foreach ($options as $key => $value) {
      switch ($key) {
        case 'title':
        case 'description':
        case 'heading1':
        case 'content':
        case 'custom_css':
        case 'social_facebook':
        case 'social_twitter':
        case 'social_google':
        case 'social_linkedin':
        case 'social_youtube':
        case 'social_vimeo':
        case 'social_pinterest':
        case 'social_dribbble':
        case 'social_behance':
        case 'social_instagram':
        case 'social_tumblr':
        case 'social_vk':
        case 'social_email':
        case 'social_phone':
        case 'social_telegram':
        case 'social_whatsapp':
        case 'license_key':
          $options[$key] = trim($value);
        break;
        case 'ga_tracking_id':
          $options[$key] = substr(strtoupper(trim($value)), 0, 15);
        break;
        case 'end_date':
          $options[$key] = substr(trim($value), 0, 16);
        break;
      } // switch
    } // foreach

    $options['whitelisted_roles'] = empty($options['whitelisted_roles'])? array(): $options['whitelisted_roles'];
    $options['whitelisted_users'] = empty($options['whitelisted_users'])? array(): $options['whitelisted_users'];
    $options = self::check_var_isset($options, array('status' => 0, 'linkback' => 0, 'login_button' => 0));

    if (empty($options['end_date_toggle'])) {
      $options['end_date'] = '';
    }
    if ($options['end_date'] == '0000-00-00 00:00') {
      $options['end_date'] = '';
    }
    unset($options['end_date_toggle']);

    if (empty($options['ga_tracking_toggle'])) {
      $options['ga_tracking_id'] = '';
    }
    if (!empty($options['ga_tracking_id']) && preg_match('/^UA-\d{3,}-\d{1,3}$/', $options['ga_tracking_id']) === 0) {
      add_settings_error('jiicp', 'ga_tracking_id', __('Please enter a valid Google Analytics Tracking ID or disable tracking.', 'JW_maintenance-mode'));
    }
    unset($options['ga_tracking_toggle']);

    if (!empty($_POST['license-submit'])) {
      if (empty($options['license_key'])) {
        $options['license_type'] = '';
        $options['license_expires'] = '1900-01-01';
        $options['license_active'] = false;
        $options['license_key'] = '';
        add_settings_error(JIICP_OPTIONS_KEY, 'license_key', __('License key saved.', 'JW_maintenance-mode'), 'updated');
      } else {
        $tmp = JIICP_license::validate_license_key($options['license_key']);
        if ($tmp['success']) {
          $options['license_type'] = $tmp['license_type'];
          $options['license_expires'] = $tmp['license_expires'];
          $options['license_active'] = $tmp['license_active'];
          if ($tmp['license_active']) {
            add_settings_error(JIICP_OPTIONS_KEY, 'license_key', __('License key saved and activated!', 'JW_maintenance-mode'), 'updated');
            set_site_transient('update_modules', null);
          } else {
            add_settings_error(JIICP_OPTIONS_KEY, 'license_key', 'License not active. ' . $tmp['error'], 'error');
          }
        } else {
          add_settings_error(JIICP_OPTIONS_KEY, 'license_key', 'Unable to contact licensing server. Please try again in a few moments.', 'error');
        }
      }
    } // update license

    // empty cache in 3rd party modules
    if ($options != $old_options) {
      $notices = get_option(JIICP_NOTICES_KEY);
      unset($notices['dismiss_whitelisted']);
      update_option(JIICP_NOTICES_KEY, $notices);

      if (function_exists('w3tc_pgcache_flush')) {
        w3tc_pgcache_flush();
      }
      if (function_exists('mcms_cache_clear_cache')) {
        mcms_cache_clear_cache();
      }
      if (class_exists('Endurance_Page_Cache')) {
        $epc = new Endurance_Page_Cache;
        $epc->purge_all();
      }
      if (class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
        SG_CachePress_Supercacher::purge_cache(true);
      }
      if (isset($GLOBALS['mcms_fastest_cache']) && method_exists($GLOBALS['mcms_fastest_cache'], 'deleteCache')) {
        $GLOBALS['mcms_fastest_cache']->deleteCache(true);
      }
    }

    return array_merge($old_options, $options);
  } // sanitize_settings


  // checkbox helper function
  static function checked($value, $current, $echo = false) {
    $out = '';

    if (!is_array($current)) {
      $current = (array) $current;
    }

    if (in_array($value, $current)) {
      $out = ' checked="checked" ';
    }

    if ($echo) {
      echo $out;
    } else {
      return $out;
    }
  } // checked


  // helper function for saving options, mostly checkboxes
  static function check_var_isset($values, $variables) {
    foreach ($variables as $key => $value) {
      if (!isset($values[$key])) {
        $values[$key] = $value;
      }
    }

    return $values;
  } // check_var_isset


  // helper function for creating dropdowns
  static function create_select_options($options, $selected = null, $output = true) {
    $out = "\n";

    if(!is_array($selected)) {
      $selected = array($selected);
    }

    foreach ($options as $tmp) {
      $data = '';
      if (isset($tmp['disabled'])) {
        $data .= ' disabled="disabled" ';
      }
      if (in_array($tmp['val'], $selected)) {
        $out .= "<option selected=\"selected\" value=\"{$tmp['val']}\"{$data}>{$tmp['label']}&nbsp;</option>\n";
      } else {
        $out .= "<option value=\"{$tmp['val']}\"{$data}>{$tmp['label']}&nbsp;</option>\n";
      }
    } // foreach

    if ($output) {
      echo $out;
    } else {
      return $out;
    }
  } // create_select_options


  // helper function to generate tagged buy links
  static function generate_web_link($placement = '', $page = '/', $params = array(), $anchor = '') {
    $base_url = 'https://jiiworks.net';

    if ('/' != $page) {
      $page = '/' . trim($page, '/') . '/';
    }
    if ($page == '//') {
      $page = '/';
    }

    $parts = array_merge(array('utm_source' => 'jiicp-free', 'utm_medium' => 'module', 'utm_content' => $placement, 'utm_campaign' => 'jiicp-free-v' . self::$version), $params);

    if (!empty($anchor)) {
      $anchor = '#' . trim($anchor, '#');
    }

    $out = $base_url . $page . '?' . http_build_query($parts, '', '&amp;') . $anchor;

    return $out;
  } // generate_web_link


  // first, main tab content
  static function tab_main() {
    $options = self::get_options();
    $default_options = self::default_options();

    echo '<div class="jiicp-tab-content">';
    echo '<table class="form-table">';

    echo '<tr valign="top">
    <th scope="row"><label for="status">' . __('Maintenance Mode', 'JW_maintenance-mode') . '</label></th>
    <td>';

    echo '<div class="toggle-wrapper" id="main-status">
      <input type="checkbox" id="status" ' . self::checked(1, $options['status']) . ' type="checkbox" value="1" name="' . JIICP_OPTIONS_KEY . '[status]">
      <label for="status" class="toggle"><span class="toggle_handler"></span></label>
    </div>';

    echo '<p class="description">' . __('By enabling construction mode users will not be able to access the site\'s content. They will only see the under construction page. To configure exceptions set <a class="change_tab" data-tab="3" href="#whitelisted-roles">whitelisted user roles</a>.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="search_engines">' . __('Prevent Search Engines from Indexing the Temporary Site', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="search_engines" type="checkbox" value="1" class="skip-save open-jiicp-upsell">
      <label for="search_engines" class="toggle"><span class="toggle_handler"></span></label>
    </div>';

    echo '<p class="description">' . __('While performing maintenance or having any temporary content displayed, it is favorable for SEO to prevent search engines from indexing the temporaray site. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="search_engines_text">PRO feature</a>.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="end_date_toggle">' . __('Automatic End Date &amp; Time', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="end_date_toggle" ' . self::checked(1, (empty($options['end_date']) || $options['end_date'] == '0000-00-00 00:00')? 0: 1) . ' type="checkbox" value="1" name="' . JIICP_OPTIONS_KEY . '[end_date_toggle]">
      <label for="end_date_toggle" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<div id="end_date_wrapper"><input id="end_date" type="text" class="datepicker" name="' . JIICP_OPTIONS_KEY . '[end_date]" value="' . esc_attr($options['end_date']) . '" placeholder="yyyy-mm-dd hh:mm"><span title="' . __('Open date & time picker', 'JW_maintenance-mode') . '" alt="' . __('Open date & time picker', 'JW_maintenance-mode') . '" class="show-datepicker dashicons dashicons-calendar-alt"></span>';
    echo '<p class="description">' . __('If enabled, construction mode will automatically stop showing on the selected date.
    This option will not "auto-enable" construction mode. Status has to be set to "On".', 'JW_maintenance-mode') . '</p></div>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="ga_tracking_id_toggle">' . __('Google Analytics Tracking', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="ga_tracking_id_toggle" ' . self::checked(1, empty($options['ga_tracking_id'])? 0: 1) . ' type="checkbox" value="1" name="' . JIICP_OPTIONS_KEY . '[ga_tracking_toggle]">
      <label for="ga_tracking_id_toggle" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<div id="ga_tracking_id_wrapper"><input id="ga_tracking_id" type="text" class="code" name="' . JIICP_OPTIONS_KEY . '[ga_tracking_id]" value="' . esc_attr($options['ga_tracking_id']) . '" placeholder="UA-xxxxxx-xx">';
    echo '<p class="description">' . __('Enter the unique tracking ID found in your GA tracking profile settings to track visits to pages.', 'JW_maintenance-mode') . '</p></div>';
    echo '</td></tr>';

    $reset_url = add_query_arg(array('action' => 'jiicp_reset_settings', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));
    echo '<tr valign="top">
    <th scope="row"><label for="">' . __('Reset Settings', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<a href="' . $reset_url . '" class="button button-secondary reset-settings">' . __('Reset all settings to default values', 'JW_maintenance-mode') . '</a>';
    echo '<p class="description">' . __('By resetting all settings to their default values any customizations you have done will be lost. There is no undo.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '</table>';
    echo '</div>';

    self::footer_buttons();
  } // tab_main


  static function tab_content() {
    $options = self::get_options();
    $default_options = self::default_options();

    echo '<div class="jiicp-tab-content">';
    echo '<table class="form-table">';

    // todo translate
    echo '<tr valign="top">
    <th scope="row"><label for="title">' . __('Title', 'JW_maintenance-mode') . '</label></th>
    <td><input type="text" id="title" class="regular-text" name="' . JIICP_OPTIONS_KEY . '[title]" value="' . esc_attr($options['title']) . '" />';
    echo '<p class="description">Page title. Default: ' . $default_options['title'] . '</p>';
    echo '<p><b>Available shortcodes:</b> (only active in UC myskins, not on the rest of the site)</p>
    <ul class="jiicp-list">
    <li><code>[site-title]</code> - blog title, as set in <a href="options-general.php">Options - General</a></li>
    <li><code>[site-tagline]</code> - blog tagline, as set in <a href="options-general.php">Options - General</a></li>
    <li><code>[site-url]</code> - site address (URL), as set in <a href="options-general.php">Options - General</a></li>
    <li><code>[mcms-url]</code> - MandarinCMS address (URL), as set in <a href="options-general.php">Options - General</a></li>
    <li><code>[site-login-url]</code> - URL to site login page</li>
    </ul>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="description">' . __('Description', 'JW_maintenance-mode') . '</label></th>
    <td><input id="description" type="text" class="large-text" name="' . JIICP_OPTIONS_KEY . '[description]" value="' . esc_attr($options['description']) . '" />';
    echo '<p class="description">Description meta tag (see above for available <a href="#title">shortcodes</a>). Default: ' . $default_options['description'] . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="heading1">' . __('Headline', 'JW_maintenance-mode') . '</label></th>
    <td><input id="heading1" type="text" class="large-text" name="' . JIICP_OPTIONS_KEY . '[heading1]" value="' . esc_attr($options['heading1']) . '" />';
    echo '<p class="description">Main heading/title (see above for available <a href="#title">shortcodes</a>). Default: ' . $default_options['heading1'] . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" id="content_wrap">
    <th scope="row"><label for="content">' . __('Content', 'JW_maintenance-mode') . '</label></th>
    <td>';
    mcms_editor($options['content'], 'content', array('tabfocus_elements' => 'insert-media-button,save-post', 'editor_height' => 250, 'resize' => 1, 'textarea_name' => JIICP_OPTIONS_KEY . '[content]', 'drag_drop_upload' => 1));
    echo '<p class="description">All HTML elements are allowed. Shortcodes are not parsed except <a href="#title">UC myskin ones</a>. Default: ' . $default_options['content'] . '</p>';
    echo '</td></tr>';

    echo '<tr>';
    echo '<th><label for="content_font">Content Font</label></th>';
    echo '<td><select class="skip-save open-jiicp-upsell" id="content_font">';
    echo '<option value="" selected="selected">MySkin Default</option>';
    echo '<option class="jiicp-promo" value="-1">ABeeZee</option><option class="jiicp-promo" value="-1">Abel</option><option class="jiicp-promo" value="-1">Abril Fatface</option><option class="jiicp-promo" value="-1">Aclonica</option><option class="jiicp-promo" value="-1">Acme</option><option class="jiicp-promo" value="-1">Actor</option><option class="jiicp-promo" value="-1">Adamina</option><option class="jiicp-promo" value="-1">Advent Pro</option><option class="jiicp-promo" value="-1">Aguafina Script</option><option class="jiicp-promo" value="-1">Akronim</option><option class="jiicp-promo" value="-1">Aladin</option><option class="jiicp-promo" value="-1">Aldrich</option><option class="jiicp-promo" value="-1">Alef</option><option class="jiicp-promo" value="-1">Alegreya</option><option class="jiicp-promo" value="-1">Alegreya SC</option><option class="jiicp-promo" value="-1">Alegreya Sans</option><option class="jiicp-promo" value="-1">Alegreya Sans SC</option><option class="jiicp-promo" value="-1">Alex Brush</option><option class="jiicp-promo" value="-1">Alfa Slab One</option><option class="jiicp-promo" value="-1">Alice</option><option class="jiicp-promo" value="-1">Alike</option><option class="jiicp-promo" value="-1">Alike Angular</option><option class="jiicp-promo" value="-1">Allan</option><option class="jiicp-promo" value="-1">Allerta</option><option class="jiicp-promo" value="-1">Allerta Stencil</option><option class="jiicp-promo" value="-1">Allura</option><option class="jiicp-promo" value="-1">Almendra</option><option class="jiicp-promo" value="-1">Almendra Display</option><option class="jiicp-promo" value="-1">Almendra SC</option><option class="jiicp-promo" value="-1">Amarante</option><option class="jiicp-promo" value="-1">Amaranth</option><option class="jiicp-promo" value="-1">Amatic SC</option><option class="jiicp-promo" value="-1">Amethysta</option><option class="jiicp-promo" value="-1">Anaheim</option><option class="jiicp-promo" value="-1">Andada</option><option class="jiicp-promo" value="-1">Andika</option><option class="jiicp-promo" value="-1">Angkor</option><option class="jiicp-promo" value="-1">Annie Use Your Telescope</option><option class="jiicp-promo" value="-1">Anonymous Pro</option><option class="jiicp-promo" value="-1">Antic</option><option class="jiicp-promo" value="-1">Antic Didone</option><option class="jiicp-promo" value="-1">Antic Slab</option><option class="jiicp-promo" value="-1">Anton</option><option class="jiicp-promo" value="-1">Arapey</option><option class="jiicp-promo" value="-1">Arbutus</option><option class="jiicp-promo" value="-1">Arbutus Slab</option><option class="jiicp-promo" value="-1">Architects Daughter</option><option class="jiicp-promo" value="-1">Archivo Black</option><option class="jiicp-promo" value="-1">Archivo Narrow</option><option class="jiicp-promo" value="-1">Arimo</option><option class="jiicp-promo" value="-1">Arizonia</option><option class="jiicp-promo" value="-1">Armata</option><option class="jiicp-promo" value="-1">Artifika</option><option class="jiicp-promo" value="-1">Arvo</option><option class="jiicp-promo" value="-1">Asap</option><option class="jiicp-promo" value="-1">Asset</option><option class="jiicp-promo" value="-1">Astloch</option><option class="jiicp-promo" value="-1">Asul</option><option class="jiicp-promo" value="-1">Atomic Age</option><option class="jiicp-promo" value="-1">Aubrey</option><option class="jiicp-promo" value="-1">Audiowide</option><option class="jiicp-promo" value="-1">Autour One</option><option class="jiicp-promo" value="-1">Average</option><option class="jiicp-promo" value="-1">Average Sans</option><option class="jiicp-promo" value="-1">Averia Gruesa Libre</option><option class="jiicp-promo" value="-1">Averia Libre</option><option class="jiicp-promo" value="-1">Averia Sans Libre</option><option class="jiicp-promo" value="-1">Averia Serif Libre</option><option class="jiicp-promo" value="-1">Bad Script</option><option class="jiicp-promo" value="-1">Balthazar</option><option class="jiicp-promo" value="-1">Bangers</option><option class="jiicp-promo" value="-1">Basic</option><option class="jiicp-promo" value="-1">Battambang</option><option class="jiicp-promo" value="-1">Baumans</option><option class="jiicp-promo" value="-1">Bayon</option><option class="jiicp-promo" value="-1">Belgrano</option><option class="jiicp-promo" value="-1">Belleza</option><option class="jiicp-promo" value="-1">BenchNine</option><option class="jiicp-promo" value="-1">Bentham</option><option class="jiicp-promo" value="-1">Berkshire Swash</option><option class="jiicp-promo" value="-1">Bevan</option><option class="jiicp-promo" value="-1">Bigelow Rules</option><option class="jiicp-promo" value="-1">Bigshot One</option><option class="jiicp-promo" value="-1">Bilbo</option><option class="jiicp-promo" value="-1">Bilbo Swash Caps</option><option class="jiicp-promo" value="-1">Bitter</option><option class="jiicp-promo" value="-1">Black Ops One</option><option class="jiicp-promo" value="-1">Bokor</option><option class="jiicp-promo" value="-1">Bonbon</option><option class="jiicp-promo" value="-1">Boogaloo</option><option class="jiicp-promo" value="-1">Bowlby One</option><option class="jiicp-promo" value="-1">Bowlby One SC</option><option class="jiicp-promo" value="-1">Brawler</option><option class="jiicp-promo" value="-1">Bree Serif</option><option class="jiicp-promo" value="-1">Bubblegum Sans</option><option class="jiicp-promo" value="-1">Bubbler One</option><option class="jiicp-promo" value="-1">Buda</option><option class="jiicp-promo" value="-1">Buenard</option><option class="jiicp-promo" value="-1">Butcherman</option><option class="jiicp-promo" value="-1">Butterfly Kids</option><option class="jiicp-promo" value="-1">Cabin</option><option class="jiicp-promo" value="-1">Cabin Condensed</option><option class="jiicp-promo" value="-1">Cabin Sketch</option><option class="jiicp-promo" value="-1">Caesar Dressing</option><option class="jiicp-promo" value="-1">Cagliostro</option><option class="jiicp-promo" value="-1">Calligraffitti</option><option class="jiicp-promo" value="-1">Cambo</option><option class="jiicp-promo" value="-1">Candal</option><option class="jiicp-promo" value="-1">Cantarell</option><option class="jiicp-promo" value="-1">Cantata One</option><option class="jiicp-promo" value="-1">Cantora One</option><option class="jiicp-promo" value="-1">Capriola</option><option class="jiicp-promo" value="-1">Cardo</option><option class="jiicp-promo" value="-1">Carme</option><option class="jiicp-promo" value="-1">Carrois Gothic</option><option class="jiicp-promo" value="-1">Carrois Gothic SC</option><option class="jiicp-promo" value="-1">Carter One</option><option class="jiicp-promo" value="-1">Caudex</option><option class="jiicp-promo" value="-1">Cedarville Cursive</option><option class="jiicp-promo" value="-1">Ceviche One</option><option class="jiicp-promo" value="-1">Changa One</option><option class="jiicp-promo" value="-1">Chango</option><option class="jiicp-promo" value="-1">Chau Philomene One</option><option class="jiicp-promo" value="-1">Chela One</option><option class="jiicp-promo" value="-1">Chelsea Market</option><option class="jiicp-promo" value="-1">Chenla</option><option class="jiicp-promo" value="-1">Cherry Cream Soda</option><option class="jiicp-promo" value="-1">Cherry Swash</option><option class="jiicp-promo" value="-1">Chewy</option><option class="jiicp-promo" value="-1">Chicle</option><option class="jiicp-promo" value="-1">Chivo</option><option class="jiicp-promo" value="-1">Cinzel</option><option class="jiicp-promo" value="-1">Cinzel Decorative</option><option class="jiicp-promo" value="-1">Clicker Script</option><option class="jiicp-promo" value="-1">Coda</option><option class="jiicp-promo" value="-1">Coda Caption</option><option class="jiicp-promo" value="-1">Codystar</option><option class="jiicp-promo" value="-1">Combo</option><option class="jiicp-promo" value="-1">Comfortaa</option><option class="jiicp-promo" value="-1">Coming Soon</option><option class="jiicp-promo" value="-1">Concert One</option><option class="jiicp-promo" value="-1">Condiment</option><option class="jiicp-promo" value="-1">Content</option><option class="jiicp-promo" value="-1">Contrail One</option><option class="jiicp-promo" value="-1">Convergence</option><option class="jiicp-promo" value="-1">Cookie</option><option class="jiicp-promo" value="-1">Copse</option><option class="jiicp-promo" value="-1">Corben</option><option class="jiicp-promo" value="-1">Courgette</option><option class="jiicp-promo" value="-1">Cousine</option><option class="jiicp-promo" value="-1">Coustard</option><option class="jiicp-promo" value="-1">Covered By Your Grace</option><option class="jiicp-promo" value="-1">Crafty Girls</option><option class="jiicp-promo" value="-1">Creepster</option><option class="jiicp-promo" value="-1">Crete Round</option><option class="jiicp-promo" value="-1">Crimson Text</option><option class="jiicp-promo" value="-1">Croissant One</option><option class="jiicp-promo" value="-1">Crushed</option><option class="jiicp-promo" value="-1">Cuprum</option><option class="jiicp-promo" value="-1">Cutive</option><option class="jiicp-promo" value="-1">Cutive Mono</option><option class="jiicp-promo" value="-1">Damion</option><option class="jiicp-promo" value="-1">Dancing Script</option><option class="jiicp-promo" value="-1">Dangrek</option><option class="jiicp-promo" value="-1">Dawning of a New Day</option><option class="jiicp-promo" value="-1">Days One</option><option class="jiicp-promo" value="-1">Delius</option><option class="jiicp-promo" value="-1">Delius Swash Caps</option><option class="jiicp-promo" value="-1">Delius Unicase</option><option class="jiicp-promo" value="-1">Della Respira</option><option class="jiicp-promo" value="-1">Denk One</option><option class="jiicp-promo" value="-1">Devonshire</option><option class="jiicp-promo" value="-1">Didact Gothic</option><option class="jiicp-promo" value="-1">Diplomata</option><option class="jiicp-promo" value="-1">Diplomata SC</option><option class="jiicp-promo" value="-1">Domine</option><option class="jiicp-promo" value="-1">Donegal One</option><option class="jiicp-promo" value="-1">Doppio One</option><option class="jiicp-promo" value="-1">Dorsa</option><option class="jiicp-promo" value="-1">Dosis</option><option class="jiicp-promo" value="-1">Dr Sugiyama</option><option class="jiicp-promo" value="-1">Droid Sans</option><option class="jiicp-promo" value="-1">Droid Sans Mono</option><option class="jiicp-promo" value="-1">Droid Serif</option><option class="jiicp-promo" value="-1">Duru Sans</option><option class="jiicp-promo" value="-1">Dynalight</option><option class="jiicp-promo" value="-1">EB Garamond</option><option class="jiicp-promo" value="-1">Eagle Lake</option><option class="jiicp-promo" value="-1">Eater</option><option class="jiicp-promo" value="-1">Economica</option><option class="jiicp-promo" value="-1">Ek Mukta</option><option class="jiicp-promo" value="-1">Electrolize</option><option class="jiicp-promo" value="-1">Elsie</option><option class="jiicp-promo" value="-1">Elsie Swash Caps</option><option class="jiicp-promo" value="-1">Emblema One</option><option class="jiicp-promo" value="-1">Emilys Candy</option><option class="jiicp-promo" value="-1">Engagement</option><option class="jiicp-promo" value="-1">Englebert</option><option class="jiicp-promo" value="-1">Enriqueta</option><option class="jiicp-promo" value="-1">Erica One</option><option class="jiicp-promo" value="-1">Esteban</option><option class="jiicp-promo" value="-1">Euphoria Script</option><option class="jiicp-promo" value="-1">Ewert</option><option class="jiicp-promo" value="-1">Exo</option><option class="jiicp-promo" value="-1">Exo 2</option><option class="jiicp-promo" value="-1">Expletus Sans</option><option class="jiicp-promo" value="-1">Fanwood Text</option><option class="jiicp-promo" value="-1">Fascinate</option><option class="jiicp-promo" value="-1">Fascinate Inline</option><option class="jiicp-promo" value="-1">Faster One</option><option class="jiicp-promo" value="-1">Fasthand</option><option class="jiicp-promo" value="-1">Fauna One</option><option class="jiicp-promo" value="-1">Federant</option><option class="jiicp-promo" value="-1">Federo</option><option class="jiicp-promo" value="-1">Felipa</option><option class="jiicp-promo" value="-1">Fenix</option><option class="jiicp-promo" value="-1">Finger Paint</option><option class="jiicp-promo" value="-1">Fira Mono</option><option class="jiicp-promo" value="-1">Fira Sans</option><option class="jiicp-promo" value="-1">Fjalla One</option><option class="jiicp-promo" value="-1">Fjord One</option><option class="jiicp-promo" value="-1">Flamenco</option><option class="jiicp-promo" value="-1">Flavors</option><option class="jiicp-promo" value="-1">Fondamento</option><option class="jiicp-promo" value="-1">Fontdiner Swanky</option><option class="jiicp-promo" value="-1">Forum</option><option class="jiicp-promo" value="-1">Francois One</option><option class="jiicp-promo" value="-1">Freckle Face</option><option class="jiicp-promo" value="-1">Fredericka the Great</option><option class="jiicp-promo" value="-1">Fredoka One</option><option class="jiicp-promo" value="-1">Freehand</option><option class="jiicp-promo" value="-1">Fresca</option><option class="jiicp-promo" value="-1">Frijole</option><option class="jiicp-promo" value="-1">Fruktur</option><option class="jiicp-promo" value="-1">Fugaz One</option><option class="jiicp-promo" value="-1">GFS Didot</option><option class="jiicp-promo" value="-1">GFS Neohellenic</option><option class="jiicp-promo" value="-1">Gabriela</option><option class="jiicp-promo" value="-1">Gafata</option><option class="jiicp-promo" value="-1">Galdeano</option><option class="jiicp-promo" value="-1">Galindo</option><option class="jiicp-promo" value="-1">Gentium Basic</option><option class="jiicp-promo" value="-1">Gentium Book Basic</option><option class="jiicp-promo" value="-1">Geo</option><option class="jiicp-promo" value="-1">Geostar</option><option class="jiicp-promo" value="-1">Geostar Fill</option><option class="jiicp-promo" value="-1">Germania One</option><option class="jiicp-promo" value="-1">Gilda Display</option><option class="jiicp-promo" value="-1">Give You Glory</option><option class="jiicp-promo" value="-1">Glass Antiqua</option><option class="jiicp-promo" value="-1">Glegoo</option><option class="jiicp-promo" value="-1">Gloria Hallelujah</option><option class="jiicp-promo" value="-1">Goblin One</option><option class="jiicp-promo" value="-1">Gochi Hand</option><option class="jiicp-promo" value="-1">Gorditas</option><option class="jiicp-promo" value="-1">Goudy Bookletter 1911</option><option class="jiicp-promo" value="-1">Graduate</option><option class="jiicp-promo" value="-1">Grand Hotel</option><option class="jiicp-promo" value="-1">Gravitas One</option><option class="jiicp-promo" value="-1">Great Vibes</option><option class="jiicp-promo" value="-1">Griffy</option><option class="jiicp-promo" value="-1">Gruppo</option><option class="jiicp-promo" value="-1">Gudea</option><option class="jiicp-promo" value="-1">Habibi</option><option class="jiicp-promo" value="-1">Hammersmith One</option><option class="jiicp-promo" value="-1">Hanalei</option><option class="jiicp-promo" value="-1">Hanalei Fill</option><option class="jiicp-promo" value="-1">Handlee</option><option class="jiicp-promo" value="-1">Hanuman</option><option class="jiicp-promo" value="-1">Happy Monkey</option><option class="jiicp-promo" value="-1">Headland One</option><option class="jiicp-promo" value="-1">Henny Penny</option><option class="jiicp-promo" value="-1">Herr Von Muellerhoff</option><option class="jiicp-promo" value="-1">Hind</option><option class="jiicp-promo" value="-1">Holtwood One SC</option><option class="jiicp-promo" value="-1">Homemade Apple</option><option class="jiicp-promo" value="-1">Homenaje</option><option class="jiicp-promo" value="-1">IM Fell DW Pica</option><option class="jiicp-promo" value="-1">IM Fell DW Pica SC</option><option class="jiicp-promo" value="-1">IM Fell Double Pica</option><option class="jiicp-promo" value="-1">IM Fell Double Pica SC</option><option class="jiicp-promo" value="-1">IM Fell English</option><option class="jiicp-promo" value="-1">IM Fell English SC</option><option class="jiicp-promo" value="-1">IM Fell French Canon</option><option class="jiicp-promo" value="-1">IM Fell French Canon SC</option><option class="jiicp-promo" value="-1">IM Fell Great Primer</option><option class="jiicp-promo" value="-1">IM Fell Great Primer SC</option><option class="jiicp-promo" value="-1">Iceberg</option><option class="jiicp-promo" value="-1">Iceland</option><option class="jiicp-promo" value="-1">Imprima</option><option class="jiicp-promo" value="-1">Inconsolata</option><option class="jiicp-promo" value="-1">Inder</option><option class="jiicp-promo" value="-1">Indie Flower</option><option class="jiicp-promo" value="-1">Inika</option><option class="jiicp-promo" value="-1">Irish Grover</option><option class="jiicp-promo" value="-1">Istok Web</option><option class="jiicp-promo" value="-1">Italiana</option><option class="jiicp-promo" value="-1">Italianno</option><option class="jiicp-promo" value="-1">Jacques Francois</option><option class="jiicp-promo" value="-1">Jacques Francois Shadow</option><option class="jiicp-promo" value="-1">Jim Nightshade</option><option class="jiicp-promo" value="-1">Jockey One</option><option class="jiicp-promo" value="-1">Jolly Lodger</option><option class="jiicp-promo" value="-1">Josefin Sans</option><option class="jiicp-promo" value="-1">Josefin Slab</option><option class="jiicp-promo" value="-1">Joti One</option><option class="jiicp-promo" value="-1">Judson</option><option class="jiicp-promo" value="-1">Julee</option><option class="jiicp-promo" value="-1">Julius Sans One</option><option class="jiicp-promo" value="-1">Junge</option><option class="jiicp-promo" value="-1">Jura</option><option class="jiicp-promo" value="-1">Just Another Hand</option><option class="jiicp-promo" value="-1">Just Me Again Down Here</option><option class="jiicp-promo" value="-1">Kalam</option><option class="jiicp-promo" value="-1">Kameron</option><option class="jiicp-promo" value="-1">Kantumruy</option><option class="jiicp-promo" value="-1">Karla</option><option class="jiicp-promo" value="-1">Karma</option><option class="jiicp-promo" value="-1">Kaushan Script</option><option class="jiicp-promo" value="-1">Kavoon</option><option class="jiicp-promo" value="-1">Kdam Thmor</option><option class="jiicp-promo" value="-1">Keania One</option><option class="jiicp-promo" value="-1">Kelly Slab</option><option class="jiicp-promo" value="-1">Kenia</option><option class="jiicp-promo" value="-1">Khmer</option><option class="jiicp-promo" value="-1">Kite One</option><option class="jiicp-promo" value="-1">Knewave</option><option class="jiicp-promo" value="-1">Kotta One</option><option class="jiicp-promo" value="-1">Koulen</option><option class="jiicp-promo" value="-1">Kranky</option><option class="jiicp-promo" value="-1">Kreon</option><option class="jiicp-promo" value="-1">Kristi</option><option class="jiicp-promo" value="-1">Krona One</option><option class="jiicp-promo" value="-1">La Belle Aurore</option><option class="jiicp-promo" value="-1">Lancelot</option><option class="jiicp-promo" value="-1">Lato</option><option class="jiicp-promo" value="-1">League Script</option><option class="jiicp-promo" value="-1">Leckerli One</option><option class="jiicp-promo" value="-1">Ledger</option><option class="jiicp-promo" value="-1">Lekton</option><option class="jiicp-promo" value="-1">Lemon</option><option class="jiicp-promo" value="-1">Libre Baskerville</option><option class="jiicp-promo" value="-1">Life Savers</option><option class="jiicp-promo" value="-1">Lilita One</option><option class="jiicp-promo" value="-1">Lily Script One</option><option class="jiicp-promo" value="-1">Limelight</option><option class="jiicp-promo" value="-1">Linden Hill</option><option class="jiicp-promo" value="-1">Lobster</option><option class="jiicp-promo" value="-1">Lobster Two</option><option class="jiicp-promo" value="-1">Londrina Outline</option><option class="jiicp-promo" value="-1">Londrina Shadow</option><option class="jiicp-promo" value="-1">Londrina Sketch</option><option class="jiicp-promo" value="-1">Londrina Solid</option><option class="jiicp-promo" value="-1">Lora</option><option class="jiicp-promo" value="-1">Love Ya Like A Sister</option><option class="jiicp-promo" value="-1">Loved by the King</option><option class="jiicp-promo" value="-1">Lovers Quarrel</option><option class="jiicp-promo" value="-1">Luckiest Guy</option><option class="jiicp-promo" value="-1">Lusitana</option><option class="jiicp-promo" value="-1">Lustria</option><option class="jiicp-promo" value="-1">Macondo</option><option class="jiicp-promo" value="-1">Macondo Swash Caps</option><option class="jiicp-promo" value="-1">Magra</option><option class="jiicp-promo" value="-1">Maiden Orange</option><option class="jiicp-promo" value="-1">Mako</option><option class="jiicp-promo" value="-1">Marcellus</option><option class="jiicp-promo" value="-1">Marcellus SC</option><option class="jiicp-promo" value="-1">Marck Script</option><option class="jiicp-promo" value="-1">Margarine</option><option class="jiicp-promo" value="-1">Marko One</option><option class="jiicp-promo" value="-1">Marmelad</option><option class="jiicp-promo" value="-1">Marvel</option><option class="jiicp-promo" value="-1">Mate</option><option class="jiicp-promo" value="-1">Mate SC</option><option class="jiicp-promo" value="-1">Maven Pro</option><option class="jiicp-promo" value="-1">McLaren</option><option class="jiicp-promo" value="-1">Meddon</option><option class="jiicp-promo" value="-1">MedievalSharp</option><option class="jiicp-promo" value="-1">Medula One</option><option class="jiicp-promo" value="-1">Megrim</option><option class="jiicp-promo" value="-1">Meie Script</option><option class="jiicp-promo" value="-1">Merienda</option><option class="jiicp-promo" value="-1">Merienda One</option><option class="jiicp-promo" value="-1">Merriweather</option><option class="jiicp-promo" value="-1">Merriweather Sans</option><option class="jiicp-promo" value="-1">Metal</option><option class="jiicp-promo" value="-1">Metal Mania</option><option class="jiicp-promo" value="-1">Metamorphous</option><option class="jiicp-promo" value="-1">Metrophobic</option><option class="jiicp-promo" value="-1">Michroma</option><option class="jiicp-promo" value="-1">Milonga</option><option class="jiicp-promo" value="-1">Miltonian</option><option class="jiicp-promo" value="-1">Miltonian Tattoo</option><option class="jiicp-promo" value="-1">Miniver</option><option class="jiicp-promo" value="-1">Miss Fajardose</option><option class="jiicp-promo" value="-1">Modern Antiqua</option><option class="jiicp-promo" value="-1">Molengo</option><option class="jiicp-promo" value="-1">Molle</option><option class="jiicp-promo" value="-1">Monda</option><option class="jiicp-promo" value="-1">Monofett</option><option class="jiicp-promo" value="-1">Monoton</option><option class="jiicp-promo" value="-1">Monsieur La Doulaise</option><option class="jiicp-promo" value="-1">Montaga</option><option class="jiicp-promo" value="-1">Montez</option><option class="jiicp-promo" value="-1">Montserrat</option><option class="jiicp-promo" value="-1">Montserrat Alternates</option><option class="jiicp-promo" value="-1">Montserrat Subrayada</option><option class="jiicp-promo" value="-1">Moul</option><option class="jiicp-promo" value="-1">Moulpali</option><option class="jiicp-promo" value="-1">Mountains of Christmas</option><option class="jiicp-promo" value="-1">Mouse Memoirs</option><option class="jiicp-promo" value="-1">Mr Bedfort</option><option class="jiicp-promo" value="-1">Mr Dafoe</option><option class="jiicp-promo" value="-1">Mr De Haviland</option><option class="jiicp-promo" value="-1">Mrs Saint Delafield</option><option class="jiicp-promo" value="-1">Mrs Sheppards</option><option class="jiicp-promo" value="-1">Muli</option><option class="jiicp-promo" value="-1">Mystery Quest</option><option class="jiicp-promo" value="-1">Neucha</option><option class="jiicp-promo" value="-1">Neuton</option><option class="jiicp-promo" value="-1">New Rocker</option><option class="jiicp-promo" value="-1">News Cycle</option><option class="jiicp-promo" value="-1">Niconne</option><option class="jiicp-promo" value="-1">Nixie One</option><option class="jiicp-promo" value="-1">Nobile</option><option class="jiicp-promo" value="-1">Nokora</option><option class="jiicp-promo" value="-1">Norican</option><option class="jiicp-promo" value="-1">Nosifer</option><option class="jiicp-promo" value="-1">Nothing You Could Do</option><option class="jiicp-promo" value="-1">Noticia Text</option><option class="jiicp-promo" value="-1">Noto Sans</option><option class="jiicp-promo" value="-1">Noto Serif</option><option class="jiicp-promo" value="-1">Nova Cut</option><option class="jiicp-promo" value="-1">Nova Flat</option><option class="jiicp-promo" value="-1">Nova Mono</option><option class="jiicp-promo" value="-1">Nova Oval</option><option class="jiicp-promo" value="-1">Nova Round</option><option class="jiicp-promo" value="-1">Nova Script</option><option class="jiicp-promo" value="-1">Nova Slim</option><option class="jiicp-promo" value="-1">Nova Square</option><option class="jiicp-promo" value="-1">Numans</option><option class="jiicp-promo" value="-1">Nunito</option><option class="jiicp-promo" value="-1">Odor Mean Chey</option><option class="jiicp-promo" value="-1">Offside</option><option class="jiicp-promo" value="-1">Old Standard TT</option><option class="jiicp-promo" value="-1">Oldenburg</option><option class="jiicp-promo" value="-1">Oleo Script</option><option class="jiicp-promo" value="-1">Oleo Script Swash Caps</option><option class="jiicp-promo" value="-1">Open Sans</option><option class="jiicp-promo" value="-1">Open Sans Condensed</option><option class="jiicp-promo" value="-1">Oranienbaum</option><option class="jiicp-promo" value="-1">Orbitron</option><option class="jiicp-promo" value="-1">Oregano</option><option class="jiicp-promo" value="-1">Orienta</option><option class="jiicp-promo" value="-1">Original Surfer</option><option class="jiicp-promo" value="-1">Oswald</option><option class="jiicp-promo" value="-1">Over the Rainbow</option><option class="jiicp-promo" value="-1">Overlock</option><option class="jiicp-promo" value="-1">Overlock SC</option><option class="jiicp-promo" value="-1">Ovo</option><option class="jiicp-promo" value="-1">Oxygen</option><option class="jiicp-promo" value="-1">Oxygen Mono</option><option class="jiicp-promo" value="-1">PT Mono</option><option class="jiicp-promo" value="-1">PT Sans</option><option class="jiicp-promo" value="-1">PT Sans Caption</option><option class="jiicp-promo" value="-1">PT Sans Narrow</option><option class="jiicp-promo" value="-1">PT Serif</option><option class="jiicp-promo" value="-1">PT Serif Caption</option><option class="jiicp-promo" value="-1">Pacifico</option><option class="jiicp-promo" value="-1">Paprika</option><option class="jiicp-promo" value="-1">Parisienne</option><option class="jiicp-promo" value="-1">Passero One</option><option class="jiicp-promo" value="-1">Passion One</option><option class="jiicp-promo" value="-1">Pathway Gothic One</option><option class="jiicp-promo" value="-1">Patrick Hand</option><option class="jiicp-promo" value="-1">Patrick Hand SC</option><option class="jiicp-promo" value="-1">Patua One</option><option class="jiicp-promo" value="-1">Paytone One</option><option class="jiicp-promo" value="-1">Peralta</option><option class="jiicp-promo" value="-1">Permanent Marker</option><option class="jiicp-promo" value="-1">Petit Formal Script</option><option class="jiicp-promo" value="-1">Petrona</option><option class="jiicp-promo" value="-1">Philosopher</option><option class="jiicp-promo" value="-1">Piedra</option><option class="jiicp-promo" value="-1">Pinyon Script</option><option class="jiicp-promo" value="-1">Pirata One</option><option class="jiicp-promo" value="-1">Plaster</option><option class="jiicp-promo" value="-1">Play</option><option class="jiicp-promo" value="-1">Playball</option><option class="jiicp-promo" value="-1">Playfair Display</option><option class="jiicp-promo" value="-1">Playfair Display SC</option><option class="jiicp-promo" value="-1">Podkova</option><option class="jiicp-promo" value="-1">Poiret One</option><option class="jiicp-promo" value="-1">Poller One</option><option class="jiicp-promo" value="-1">Poly</option><option class="jiicp-promo" value="-1">Pompiere</option><option class="jiicp-promo" value="-1">Pontano Sans</option><option class="jiicp-promo" value="-1">Port Lligat Sans</option><option class="jiicp-promo" value="-1">Port Lligat Slab</option><option class="jiicp-promo" value="-1">Prata</option><option class="jiicp-promo" value="-1">Preahvihear</option><option class="jiicp-promo" value="-1">Press Start 2P</option><option class="jiicp-promo" value="-1">Princess Sofia</option><option class="jiicp-promo" value="-1">Prociono</option><option class="jiicp-promo" value="-1">Prosto One</option><option class="jiicp-promo" value="-1">Puritan</option><option class="jiicp-promo" value="-1">Purple Purse</option><option class="jiicp-promo" value="-1">Quando</option><option class="jiicp-promo" value="-1">Quantico</option><option class="jiicp-promo" value="-1">Quattrocento</option><option class="jiicp-promo" value="-1">Quattrocento Sans</option><option class="jiicp-promo" value="-1">Questrial</option><option class="jiicp-promo" value="-1">Quicksand</option><option class="jiicp-promo" value="-1">Quintessential</option><option class="jiicp-promo" value="-1">Qwigley</option><option class="jiicp-promo" value="-1">Racing Sans One</option><option class="jiicp-promo" value="-1">Radley</option><option class="jiicp-promo" value="-1">Rajdhani</option><option class="jiicp-promo" value="-1">Raleway</option><option class="jiicp-promo" value="-1">Raleway Dots</option><option class="jiicp-promo" value="-1">Rambla</option><option class="jiicp-promo" value="-1">Rammetto One</option><option class="jiicp-promo" value="-1">Ranchers</option><option class="jiicp-promo" value="-1">Rancho</option><option class="jiicp-promo" value="-1">Rationale</option><option class="jiicp-promo" value="-1">Redressed</option><option class="jiicp-promo" value="-1">Reenie Beanie</option><option class="jiicp-promo" value="-1">Revalia</option><option class="jiicp-promo" value="-1">Ribeye</option><option class="jiicp-promo" value="-1">Ribeye Marrow</option><option class="jiicp-promo" value="-1">Righteous</option><option class="jiicp-promo" value="-1">Risque</option><option class="jiicp-promo" value="-1">Roboto</option><option class="jiicp-promo" value="-1">Roboto Condensed</option><option class="jiicp-promo" value="-1">Roboto Slab</option><option class="jiicp-promo" value="-1">Rochester</option><option class="jiicp-promo" value="-1">Rock Salt</option><option class="jiicp-promo" value="-1">Rokkitt</option><option class="jiicp-promo" value="-1">Romanesco</option><option class="jiicp-promo" value="-1">Ropa Sans</option><option class="jiicp-promo" value="-1">Rosario</option><option class="jiicp-promo" value="-1">Rosarivo</option><option class="jiicp-promo" value="-1">Rouge Script</option><option class="jiicp-promo" value="-1">Rubik Mono One</option><option class="jiicp-promo" value="-1">Rubik One</option><option class="jiicp-promo" value="-1">Ruda</option><option class="jiicp-promo" value="-1">Rufina</option><option class="jiicp-promo" value="-1">Ruge Boogie</option><option class="jiicp-promo" value="-1">Ruluko</option><option class="jiicp-promo" value="-1">Rum Raisin</option><option class="jiicp-promo" value="-1">Ruslan Display</option><option class="jiicp-promo" value="-1">Russo One</option><option class="jiicp-promo" value="-1">Ruthie</option><option class="jiicp-promo" value="-1">Rye</option><option class="jiicp-promo" value="-1">Sacramento</option><option class="jiicp-promo" value="-1">Sail</option><option class="jiicp-promo" value="-1">Salsa</option><option class="jiicp-promo" value="-1">Sanchez</option><option class="jiicp-promo" value="-1">Sancreek</option><option class="jiicp-promo" value="-1">Sansita One</option><option class="jiicp-promo" value="-1">Sarina</option><option class="jiicp-promo" value="-1">Satisfy</option><option class="jiicp-promo" value="-1">Scada</option><option class="jiicp-promo" value="-1">Schoolbell</option><option class="jiicp-promo" value="-1">Seaweed Script</option><option class="jiicp-promo" value="-1">Sevillana</option><option class="jiicp-promo" value="-1">Seymour One</option><option class="jiicp-promo" value="-1">Shadows Into Light</option><option class="jiicp-promo" value="-1">Shadows Into Light Two</option><option class="jiicp-promo" value="-1">Shanti</option><option class="jiicp-promo" value="-1">Share</option><option class="jiicp-promo" value="-1">Share Tech</option><option class="jiicp-promo" value="-1">Share Tech Mono</option><option class="jiicp-promo" value="-1">Shojumaru</option><option class="jiicp-promo" value="-1">Short Stack</option><option class="jiicp-promo" value="-1">Siemreap</option><option class="jiicp-promo" value="-1">Sigmar One</option><option class="jiicp-promo" value="-1">Signika</option><option class="jiicp-promo" value="-1">Signika Negative</option><option class="jiicp-promo" value="-1">Simonetta</option><option class="jiicp-promo" value="-1">Sintony</option><option class="jiicp-promo" value="-1">Sirin Stencil</option><option class="jiicp-promo" value="-1">Six Caps</option><option class="jiicp-promo" value="-1">Skranji</option><option class="jiicp-promo" value="-1">Slabo 13px</option><option class="jiicp-promo" value="-1">Slabo 27px</option><option class="jiicp-promo" value="-1">Slackey</option><option class="jiicp-promo" value="-1">Smokum</option><option class="jiicp-promo" value="-1">Smythe</option><option class="jiicp-promo" value="-1">Sniglet</option><option class="jiicp-promo" value="-1">Snippet</option><option class="jiicp-promo" value="-1">Snowburst One</option><option class="jiicp-promo" value="-1">Sofadi One</option><option class="jiicp-promo" value="-1">Sofia</option><option class="jiicp-promo" value="-1">Sonsie One</option><option class="jiicp-promo" value="-1">Sorts Mill Goudy</option><option class="jiicp-promo" value="-1">Source Code Pro</option><option class="jiicp-promo" value="-1">Source Sans Pro</option><option class="jiicp-promo" value="-1">Source Serif Pro</option><option class="jiicp-promo" value="-1">Special Elite</option><option class="jiicp-promo" value="-1">Spicy Rice</option><option class="jiicp-promo" value="-1">Spinnaker</option><option class="jiicp-promo" value="-1">Spirax</option><option class="jiicp-promo" value="-1">Squada One</option><option class="jiicp-promo" value="-1">Stalemate</option><option class="jiicp-promo" value="-1">Stalinist One</option><option class="jiicp-promo" value="-1">Stardos Stencil</option><option class="jiicp-promo" value="-1">Stint Ultra Condensed</option><option class="jiicp-promo" value="-1">Stint Ultra Expanded</option><option class="jiicp-promo" value="-1">Stoke</option><option class="jiicp-promo" value="-1">Strait</option><option class="jiicp-promo" value="-1">Sue Ellen Francisco</option><option class="jiicp-promo" value="-1">Sunshiney</option><option class="jiicp-promo" value="-1">Supermercado One</option><option class="jiicp-promo" value="-1">Suwannaphum</option><option class="jiicp-promo" value="-1">Swanky and Moo Moo</option><option class="jiicp-promo" value="-1">Syncopate</option><option class="jiicp-promo" value="-1">Tangerine</option><option class="jiicp-promo" value="-1">Taprom</option><option class="jiicp-promo" value="-1">Tauri</option><option class="jiicp-promo" value="-1">Teko</option><option class="jiicp-promo" value="-1">Telex</option><option class="jiicp-promo" value="-1">Tenor Sans</option><option class="jiicp-promo" value="-1">Text Me One</option><option class="jiicp-promo" value="-1">The Girl Next Door</option><option class="jiicp-promo" value="-1">Tienne</option><option class="jiicp-promo" value="-1">Tinos</option><option class="jiicp-promo" value="-1">Titan One</option><option class="jiicp-promo" value="-1">Titillium Web</option><option class="jiicp-promo" value="-1">Trade Winds</option><option class="jiicp-promo" value="-1">Trocchi</option><option class="jiicp-promo" value="-1">Trochut</option><option class="jiicp-promo" value="-1">Trykker</option><option class="jiicp-promo" value="-1">Tulpen One</option><option class="jiicp-promo" value="-1">Ubuntu</option><option class="jiicp-promo" value="-1">Ubuntu Condensed</option><option class="jiicp-promo" value="-1">Ubuntu Mono</option><option class="jiicp-promo" value="-1">Ultra</option><option class="jiicp-promo" value="-1">Uncial Antiqua</option><option class="jiicp-promo" value="-1">Underdog</option><option class="jiicp-promo" value="-1">Unica One</option><option class="jiicp-promo" value="-1">UnifrakturCook</option><option class="jiicp-promo" value="-1">UnifrakturMaguntia</option><option class="jiicp-promo" value="-1">Unkempt</option><option class="jiicp-promo" value="-1">Unlock</option><option class="jiicp-promo" value="-1">Unna</option><option class="jiicp-promo" value="-1">VT323</option><option class="jiicp-promo" value="-1">Vampiro One</option><option class="jiicp-promo" value="-1">Varela</option><option class="jiicp-promo" value="-1">Varela Round</option><option class="jiicp-promo" value="-1">Vast Shadow</option><option class="jiicp-promo" value="-1">Vibur</option><option class="jiicp-promo" value="-1">Vidaloka</option><option class="jiicp-promo" value="-1">Viga</option><option class="jiicp-promo" value="-1">Voces</option><option class="jiicp-promo" value="-1">Volkhov</option><option class="jiicp-promo" value="-1">Vollkorn</option><option class="jiicp-promo" value="-1">Voltaire</option><option class="jiicp-promo" value="-1">Waiting for the Sunrise</option><option class="jiicp-promo" value="-1">Wallpoet</option><option class="jiicp-promo" value="-1">Walter Turncoat</option><option class="jiicp-promo" value="-1">Warnes</option><option class="jiicp-promo" value="-1">Wellfleet</option><option class="jiicp-promo" value="-1">Wendy One</option><option class="jiicp-promo" value="-1">Wire One</option><option class="jiicp-promo" value="-1">Yanone Kaffeesatz</option><option class="jiicp-promo" value="-1">Yellowtail</option><option class="jiicp-promo" value="-1">Yeseva One</option><option class="jiicp-promo" value="-1">Yesteryear</option><option class="jiicp-promo" value="-1">Zeyada</option>';
    echo '</select>';
    echo '<p class="description">Choose one of 600+ beautiful Google fonts or use the default, myskin set one. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="content_font">PRO feature</a>.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="external_shortcodes">' . __('3rd Party Shortcode Support in Content', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="external_shortcodes" type="checkbox" value="1" class="skip-save open-jiicp-upsell">
      <label for="external_shortcodes" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p class="description">Enable if you have a 3rd party shortcode you\'d like to use on the under construction page. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="external_shortcodes">PRO feature</a>.</p>';
    echo '</td></tr>';

    echo '<tr valign="top" id="login_button_wrap">
    <th scope="row"><label for="login_button">' . __('Login Button', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="login_button" ' . self::checked(1, $options['login_button']) . ' type="checkbox" value="1" name="' . JIICP_OPTIONS_KEY . '[login_button]">
      <label for="login_button" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p class="description">Show a discrete link to the login form, or MCMS admin if you\'re logged in, in the lower right corner of the page.</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="linkback">' . __('Show Some Love', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="linkback" ' . self::checked(1, $options['linkback']) . ' type="checkbox" value="1" name="' . JIICP_OPTIONS_KEY . '[linkback]">
      <label for="linkback" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p class="description">Please help others learn about this free module by placing a small link in the footer. Thank you very much!</p>';
    echo '</td></tr>';


    echo '</table>';

    self::footer_buttons();

    echo '<h2 class="title">' . __('Social &amp; Contact Icons', 'JW_maintenance-mode') . '</h2>';

    echo '<table class="form-table" id="jiicp-social-icons">';
    echo '<tr valign="top">
    <th scope="row"><label for="social_facebook">' . __('Facebook Page', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_facebook" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_facebook]" value="' . esc_attr($options['social_facebook']) . '" placeholder="' . __('Facebook business or personal page URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Facebook page.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="social_twitter">' . __('Twitter Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_twitter" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_twitter]" value="' . esc_attr($options['social_twitter']) . '" placeholder="' . __('Twitter profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Twitter profile page.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="social_google">' . __('Google Page', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_google" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_google]" value="' . esc_attr($options['social_google']) . '" placeholder="' . __('Google+ page URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Google+ page.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="social_linkedin">' . __('LinkedIn Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_linkedin" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_linkedin]" value="' . esc_attr($options['social_linkedin']) . '" placeholder="' . __('LinkedIn profile page URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to LinkedIn profile page.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="social_youtube">' . __('YouTube Profile Page or Video', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_youtube" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_youtube]" value="' . esc_attr($options['social_youtube']) . '" placeholder="' . __('YouTube page or video URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to YouTube page or video.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_vimeo">' . __('Vimeo Profile Page or Video', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_vimeo" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_vimeo]" value="' . esc_attr($options['social_vimeo']) . '" placeholder="' . __('Vimeo page or video URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Vimeo page or video.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_pinterest">' . __('Pinterest Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_pinterest" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_pinterest]" value="' . esc_attr($options['social_pinterest']) . '" placeholder="' . __('Pinterest profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Pinterest profile.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_dribbble">' . __('Dribbble Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_dribbble" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_dribbble]" value="' . esc_attr($options['social_dribbble']) . '" placeholder="' . __('Dribbble profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Dribbble profile.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_behance">' . __('Behance Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_behance" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_behance]" value="' . esc_attr($options['social_behance']) . '" placeholder="' . __('Behance profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Behance profile.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_instagram">' . __('Instagram Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_instagram" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_instagram]" value="' . esc_attr($options['social_instagram']) . '" placeholder="' . __('Instagram profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to Instagram profile.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_vk">' . __('VK Profile', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_vk" type="url" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_vk]" value="' . esc_attr($options['social_vk']) . '" placeholder="' . __('VK profile URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix, to VK profile.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_telegram">' . __('Telegram Group, Channel or Account', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_telegram" type="text" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_telegram]" value="' . esc_attr($options['social_telegram']) . '" placeholder="' . __('Telegram group, channel or account URL', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Complete URL, with https prefix to Telegram group, channel or account.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_skype">' . __('Skype Username', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_skype" type="text" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_skype]" value="' . esc_attr($options['social_skype']) . '" placeholder="' . __('Skype username or account name', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Skype username or account name.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_whatsapp">' . __('WhatsApp Phone Number', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_whatsapp" type="text" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_whatsapp]" value="' . esc_attr($options['social_whatsapp']) . '" placeholder="' . __('+1-123-456-789', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('WhatsApp phone number in full international format.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_email">' . __('Email Address', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_email" type="email" class="regular-text code" name="' . JIICP_OPTIONS_KEY . '[social_email]" value="' . esc_attr($options['social_email']) . '" placeholder="' . __('name@domain.com', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Email will be encoded on the page to protect it from email address harvesters.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top" class="hidden">
    <th scope="row"><label for="social_phone">' . __('Phone Number', 'JW_maintenance-mode') . '</label></th>
    <td><input id="social_phone" type="tel" class="regular-text" name="' . JIICP_OPTIONS_KEY . '[social_phone]" value="' . esc_attr($options['social_phone']) . '" placeholder="' . __('+1-123-456-789', 'JW_maintenance-mode') . '">';
    echo '<p class="description">' . __('Phone number in full international format.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr><th colspan="2"><a id="show-social-icons" href="#" class="js-action">' . __('Show more Social &amp; Contact Icons', 'JW_maintenance-mode') . '</a></th></tr>';

    echo '</table>';
    echo '</div>';

    self::footer_buttons();
  } // tab_content


  static function get_myskins() {
    $myskins = array(
					#'_pro_relaxing-screensaver' => __('Fireworks Video Background', 'JW_maintenance-mode'),
                    'mad_designer' => __('Mad Designer', 'JW_maintenance-mode'),
                    'plain_text' => __('Plain Text', 'JW_maintenance-mode'),
                    #'_pro_mountain' => __('Mountain Peak', 'JW_maintenance-mode'),
                    'under_construction' => __('Maintenance', 'JW_maintenance-mode'),
                    'dark' => __('Things Went Dark', 'JW_maintenance-mode'),
                    #'_pro_watch-company' => __('The Watch Store', 'JW_maintenance-mode'),
                    'forklift' => __('Forklift at Work', 'JW_maintenance-mode'),
                    'under_construction_text' => __('Maintenance Text', 'JW_maintenance-mode'),
                    '_pro_cloud-business-inc' => __('Cloud Business', 'JW_maintenance-mode'),
                    'cyber_chick' => __('Cyber Chick', 'JW_maintenance-mode'),
                    'rocket' => __('Rocket Launch', 'JW_maintenance-mode'),
                    #'_pro_smoothie' => __('Green Smoothie Webinar', 'JW_maintenance-mode'),
                    'loader' => __('Loader at Work', 'JW_maintenance-mode'),
                    'cyber_chick_dark' => __('Cyber Chick Dark', 'JW_maintenance-mode'),
                    #'_pro_small-office' => __('Small Office', 'JW_maintenance-mode'),
                    'safe' => __('Safe', 'JW_maintenance-mode'),
                    'people' => __('People at Work', 'JW_maintenance-mode'),
                    #'_pro_custom' => __('Build Your Own Custom MySkin', 'JW_maintenance-mode'),
                    'windmill' => __('Windmill', 'JW_maintenance-mode'),
                    'sad_site' => __('Sad Site', 'JW_maintenance-mode'),
                    #'_pro-soothing-nature' => __('Soothing Nature', 'JW_maintenance-mode'),
                    'lighthouse' => __('Lighthouse', 'JW_maintenance-mode'),
                    'hot_air_baloon' => __('Hot Air Balloon', 'JW_maintenance-mode'),
                    #'_pro_simple-beige' => __('Simple Beige Text', 'JW_maintenance-mode'),
                    'people_2' => __('People at Work #2', 'JW_maintenance-mode'),
                    'rocket_2' => __('Rocket Launch #2', 'JW_maintenance-mode'),
                    #'_pro_travel-blog' => __('Travel Blog', 'JW_maintenance-mode'),
                    'light_bulb' => __('Light Bulb', 'JW_maintenance-mode'),
                    'ambulance' => __('Ambulance', 'JW_maintenance-mode'),
                    #'_pro_forest-in-the-fog' => __('Forest in the Fog', 'JW_maintenance-mode'),
                    'laptop' => __('Laptop', 'JW_maintenance-mode'),
                    'puzzles' => __('Puzzles', 'JW_maintenance-mode'),
                    #'_pro_sunset' => __('Sunset', 'JW_maintenance-mode'),
                    'iot' => __('Internet of Things', 'JW_maintenance-mode'),
                    'setup' => __('Setup', 'JW_maintenance-mode'),
                    #'_pro_fitness-studio' => __('Fitness Studio Landing Page', 'JW_maintenance-mode'),
                    'stop' => __('Stop', 'JW_maintenance-mode'),
                    'clock' => __('Clock', 'JW_maintenance-mode'),
                    #'_pro_simple-office-myskin' => __('Simple Office', 'JW_maintenance-mode'),
                    'bulldozer' => __('Bulldozer at Work', 'JW_maintenance-mode'),
                    'christmas' => __('Christmas Greetings', 'JW_maintenance-mode'),
                    #'_pro_pink-lips' => __('Pink Lips', 'JW_maintenance-mode'),
                    'hard_worker' => __('Hard Worker', 'JW_maintenance-mode'),
                    'closed' => __('Temporarily Closed', 'JW_maintenance-mode'),
                    #'_pro_animated-green' => __('Simple Green Animated', 'JW_maintenance-mode')
					);

    $myskins = apply_filters('jiicp_myskins', $myskins);

    return $myskins;
  } // get_myskins


  static function tab_design() {
    $options = self::get_options();
    $default_options = self::default_options();

    $img_path = JIICP_MODULE_URL . 'images/thumbnails/';
    $myskins = self::get_myskins();

    echo '<table class="form-table">';
    echo '<tr valign="top">
    <td colspan="2"><b style="margin-bottom: 10px; display: inline-block;">' . __('MySkin', 'JW_maintenance-mode') . '</b> (<a target="_blank" href="' . self::generate_web_link('myskins-browse-premium', 'templates') . '">browse 100+ premium myskins</a>)<br>';
    echo '<input type="hidden" id="myskin_id" name="' . JIICP_OPTIONS_KEY . '[myskin]" value="' . $options['myskin'] . '">';

    foreach ($myskins as $myskin_id => $myskin_name) {
      if ($myskin_id === $options['myskin']) {
        $class = ' active';
      } else {
        $class = '';
      }
      if (substr($myskin_id, 0, 4) == '_pro') {
        $myskin_id = substr($myskin_id, 5);
        echo '<div class="jiicp-thumb jiicp-thumb-pro" data-myskin-id="' . $myskin_id . '"><img src="' . $img_path . 'pro/' . $myskin_id . '.jpg" alt="' . $myskin_name . '" title="' . $myskin_name . '"><span>' . $myskin_name . '</span>';
        echo '<div class="buttons"><a href="#" data-pro-ad="activate_' . $myskin_id . '" class="open-jiicp-upsell button button-primary">Activate</a> ';
        if ($myskin_id != 'custom') {
          echo '<a href="https://templates.jiiworks.net/?jiicp_template_preview&template=' . $myskin_id . '&utm_source=jiicp-free&utm_medium=module&utm_content=design-preview-' . $myskin_id . '&utm_campaign=jiicp-free-v' . self::$version . '" class="button-secondary" target="_blank">Preview</a>';
        }
        echo '</div>';
        echo '<div class="ribbon"><i><span class="dashicons dashicons-star-filled"></span></i></div></div>';
      } else {
        echo '<div class="jiicp-thumb' . $class . '" data-myskin-id="' . $myskin_id . '"><img src="' . $img_path . $myskin_id . '.png" alt="' . $myskin_name . '" title="' . $myskin_name . '"><span>' . $myskin_name . '</span>';
        echo '<div class="buttons"><a href="#" class="button button-primary activate-myskin">Activate</a> <a href="' . get_home_url() . '/?jiicp_preview&myskin=' . $myskin_id . '" class="button-secondary" target="_blank">Preview</a></div>';
        echo '</div>';
      }
    } // foreach

    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="custom_css">' . __('Custom CSS', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<textarea data-autoresize="1" rows="3" id="custom_css" class="code large-text" name="' . JIICP_OPTIONS_KEY . '[custom_css]" placeholder=".selector { property-name: property-value; }">' . esc_textarea($options['custom_css']) . '</textarea>';
    echo '<p class="description">&lt;style&gt; tags will be added automatically. Do not include them in your code.<br>
    For RTL languages support add: <code>body { direction: rtl; }</code></p>';
    echo '</td></tr>';

    echo '</table>';

    self::footer_buttons();
  } // tab_design


  // markup & logic for access tab
  static function tab_access() {
    $options = self::get_options();
    $default_options = self::default_options();
    $roles = $users = array();

    $tmp_roles = get_editable_roles();
    foreach ($tmp_roles as $tmp_role => $details) {
      $name = translate_user_role($details['name']);
      $roles[] = array('val' => $tmp_role,  'label' => $name);
    }

    $tmp_users = get_users(array('fields' => array('id', 'display_name')));
    foreach ($tmp_users as $user) {
      $users[] = array('val' => $user->id, 'label' => $user->display_name);
    }

    echo '<div class="jiicp-tab-content">';
    echo '<table class="form-table">';

    echo '<tr valign="top">
    <th scope="row"><label for="whitelisted_ips">' . __('Whitelisted IP Addresses', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="whitelisted_ips" type="checkbox" value="1" class="skip-save open-jiicp-upsell">
      <label for="whitelisted_ips" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p>Listed IP addresses will not be affected by the under construction mode and their users will always see the "normal" site. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="whitelisted_ips">PRO feature</a>.';
    echo '<td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="access_links">' . __('Secret Direct Access Links', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="access_links" type="checkbox" value="1" class="skip-save open-jiicp-upsell">
      <label for="access_links" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p>The most flexible and user-friendly way (especially when working with clients) to give only selected visitors access to the "normal" site. Simply generate a new link, configure expiration options (time, number of visits or unique IPs) and share it with users to allow them access to the site. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="access_links">PRO feature</a>.';
    echo '<td></tr>';

    echo '<tr valign="top" id="whitelisted-roles">
    <th scope="row">' . __('Whitelisted User Roles', 'JW_maintenance-mode') . '</th>
    <td>';
    foreach ($roles as $tmp_role) {
      echo  '<input name="' . JIICP_OPTIONS_KEY . '[whitelisted_roles][]" id="roles-' . $tmp_role['val'] . '" ' . self::checked($tmp_role['val'], $options['whitelisted_roles'], false) . ' value="' . $tmp_role['val'] . '" type="checkbox" /> <label for="roles-' . $tmp_role['val'] . '">' . $tmp_role['label'] . '</label><br />';
    }
    echo '<p class="description">' . __('Selected user roles will <b>not</b> be affected by the under construction mode and will always see the "normal" site. Default: administrator.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="whitelisted_users">' . __('Whitelisted Users', 'JW_maintenance-mode') . '</label></th>
    <td><select id="whitelisted_users" class="select2" style="width: 50%; max-width: 300px;" name="' . JIICP_OPTIONS_KEY . '[whitelisted_users][]" multiple>';
    self::create_select_options($users, $options['whitelisted_users'], true);

    echo '</select><p class="description">' . __('Selected users (when logged in) will <b>not</b> be affected by the under construction mode and will always see the "normal" site.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr>';
    echo '<th><label for="url_rules">URL Based Rules</label></th>';
    echo '<td><select class="skip-save open-jiicp-upsell" id="url_rules">';
    echo '<option value="0">Disabled</option>';
    echo '<option class="jiicp-promo" value="-1">Listed URLs will NEVER be affected by JIICP</option>';
    echo '<option class="jiicp-promo" value="-1">ONLY listed URLs CAN BE affected by JIICP</option>';
    echo '</select>';
    echo '<p class="description">Use this option to set per URL rules and lock down the entire site except selected pages; or lock down just some pages and leave all others accessible to visitors. If second option is used all other access rules still apply. This is a <a href="#" class="open-jiicp-upsell" data-pro-ad="url_rules">PRO feature</a>.</p>';
    echo '</td>';
    echo '</tr>';


    echo '</table>';
    echo '</div>';

    self::footer_buttons();
  } // tab_access


  // support tab - FAQ and links
  static function tab_support() {
    $user = mcms_get_current_user();
    $myskin = mcms_get_myskin();
    $options = self::get_options();

    echo '<div id="tabs_support" class="ui-tabs jiicp-tabs-2nd-level">';
    echo '<ul>';
    echo '<li><a href="#tab_support_contact">' . __('Contact Support', 'JW_maintenance-mode') . '</a></li>';
    echo '<li><a href="#tab_support_faq">' . __('FAQ', 'JW_maintenance-mode') . '</a></li>';
    echo '</ul>';

    echo '<div style="display: none;" id="tab_support_faq" class="jiicp-tab-content">';

    echo '<p><b>Do you have any documentation?</b><br>Yes, <a href="' . self::generate_web_link('faq-documentation', 'documentation-free') . '" target="_blank">detailed documentation</a> with how-to guides is available on the modules\'s site.</p>';

    echo '<p><b>Do you have a video to help me get started?</b><br>We sure do! <a href="https://www.youtube.com/watch?v=RN4XABhK7_w" target="_blank">Getting started with the MaintenanceModePage module</a>. If that doesn\'t help we also have an <a href="https://www.youtube.com/watch?v=K3DF-NP6Fog" target="_blank">in-depth video walktrough</a>. In case you\'re still uncertain about something don\'t hesitate to contact our friendly support.</p>';

    echo '<p><b>How can I build a custom page or customize myskins?</b><br>If you know how to code custom CSS a lot can be accomplished by using the <a class="change_tab" data-tab="1" href="#custom_css">custom CSS</a> option. A much faster and easier solution is to check out <a href="#" data-pro-ad="faq-custom-css" class="open-jiicp-upsell">JIICP PRO</a> with an easy-to-use drag&amp;drop builder and dozens of pre-built myskins.</p>';

    echo '<p><b>How can I check if construction mode is really enabled on my site?</b><br>If the under construction status is green in the admin bar (the very top of the page, above this text), then it\'s enabled. But we made a tool specifically for these kinds of situations so you can double-check everything. <a href="' . self::generate_web_link('faq-tester', 'under-construction-tester/', array('url' => get_home_url())) . '" target="_blank">Run under construction mode tester</a>.</p>';

    echo '<p><b>How can I work on my site while construction mode is enabled?</b><br>Make sure your user role (probably admin) is selected under <a class="change_tab" data-tab="3" href="#whitelisted-roles">Access - Whitelisted User Roles</a> and open the site while logged in.</p>';

    echo '<p><b>How can I log in / access MandarinCMS admin after construction mode has been enabled?</b><br>Enable the <a class="change_tab" data-tab="2" href="#login_button_wrap">Login Button</a> option under Content, and a login link will be shown in the lower right corner of the under construction page.</p>';

    echo '<p><b>How do I add my logo to the page?</b><br>Head over to <a class="change_tab" data-tab="2" href="#content_wrap">Content</a> and click "Add Media". Upload/select the logo, position it as you see fit and add other content.</p>';

    echo '<p><b>I\'ve made changes to JIICP, but they are not visible. What do I do?</b><br>Click "Save Changes" one more time. Open your site and force refresh browser cache (Ctrl or Shift + F5). If that doesn\'t help it means you have a caching module installed. Purge/delete cache in that module or disable it.</p>';

    echo '<p><b>How can I get more designs? Where do I download them?</b><br>We update the module every 7-10 days and each update comes with at least one new myskin/design. There is no other way of getting more designs nor a place to download them.</p>';

    echo '<p><b>How can I edit designs?</b><br>There is an option to add <a class="change_tab" data-tab="1" href="#custom_css">custom CSS</a>. If you want more than that you will have to edit the source files located in <code>/JW_maintenance-mode/myskins/</code>.</p>';

    echo '<p><b>I have disabled JIICP but the under construction page is still visible. How do I remove it?</b><br>Open your site and force refresh browser cache (Ctrl or Shift + F5). If that doesn\'t help it means you have a caching module installed. Purge/delete cache in that module or disable it. If that fails too contact your hosting provider and ask to empty the site cache for you.</p>';

    echo '<p><b>I have disabled JIICP but the site\'s favicon is still the JIICP logo. How do I change/remove it?</b><br>Make sure your myskin has a favicon defined and empty all caches - browser and server ones. Open the site and force refresh browser cache (Ctrl or Shift + F5). If that doesn\'t help it means you have a caching module installed. Purge/delete cache in that module or disable it. If that fails too contact your hosting provider and ask to empty the site cache for you.</p>';

    echo '<p><b>JIICP is disabled but Twitter and Facebook still show it as my site\'s preview/thumbnail when I post the URL</b><br>Twitter and Facebook have their own cache which has to be refreshed. You can either wait and the problem will resolve itself in about a day or you can manually refresh the cache.<br>For Facebook open the <a href="https://developers.facebook.com/tools/debug/" target="_blank">Debugger</a>, input the URL, click "Debug". Once the results who up click "Scrape Again" to fetch the latest version of the page.<br>For Twitter, open the <a href="https://cards-dev.twitter.com/validator" target="_blank">Card validator</a>, enter the URL and click "Preview card". Latest version of the site should appear.</p>';
    echo '</div>'; // faq

    echo '<div style="display: none;" id="tab_support_contact" class="jiicp-tab-content">';
    echo '<p>' . __('Something is not working the way it\'s suppose to? Having problems activating JIICP? Contact our friendly support, they\'ll respond ASAP.<br>You can also contact us just to say hello ;)', 'JW_maintenance-mode') . '</p>';

    echo '<table class="form-table">';
    echo '<tr valign="top">
    <th scope="row"><label for="support_priority">Ticket Priority</label></th>
    <td>';
    echo '<select class="skip-save open-jiicp-upsell" id="support_priority" name="support_priority">';
    echo '<option value="0" selected>Low</option>';
    echo '<option value="-1" class="jiicp-promo">Normal</option>';
    echo '<option value="-1" class="jiicp-promo">High</option>';
    echo '</select>';
    echo '<p class="description">' . __('We reply to all tickets as fast as possible. However, <a data-pro-ad="support_priority" href="#" class="open-jiicp-upsell">PRO users</a> get to jump the queue.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="support_email">' . __('Your Email Address', 'JW_maintenance-mode') . '</label></th>
      <td><input id="support_email" type="text" class="regular-text skip-save" name="support_email" value="" placeholder="name@domain.com">';
    echo '<p class="description">' . __('We will reply to this email, so please, double-check it.', 'JW_maintenance-mode') . '</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="support_message">' . __('Message', 'JW_maintenance-mode') . '</label></th>
    <td><textarea rows="5" cols="75" id="support_message" class="skip-save" name="support_message" placeholder="Hi, I just wanted to ..."></textarea>';
    echo '<p class="description">Please be as descriptive as possible. It will help us to provide faster &amp; better support.</p>';
    echo '</td></tr>';

    echo '<tr valign="top">
    <th scope="row"><label for="support_info">' . __('Send Extra Site Info to Support Agents', 'JW_maintenance-mode') . '</label></th>
    <td>';
    echo '<div class="toggle-wrapper">
      <input type="checkbox" id="support_info" checked type="checkbox" value="1" name="support_info">
      <label for="support_info" class="toggle"><span class="toggle_handler"></span></label>
    </div>';
    echo '<p class="description">Our support agents need this info to provide faster &amp; better support. The following data will be added to your message;</p>';
    echo '<p>MandarinCMS version: <code>' . get_bloginfo('version') . '</code><br>';
    echo 'JIICP Version: <code>' . self::$version . '</code><br>';
    echo 'PHP Version: <code>' . PHP_VERSION . '</code><br>';
    echo 'Site URL: <code>' . get_bloginfo('url') . '</code><br>';
    echo 'MandarinCMS URL: <code>' . get_bloginfo('mcmsurl') . '</code><br>';
    echo 'MySkin: <code>' . $myskin->get('Name') . ' v' . $myskin->get('Version') . '</code><br>';
    echo 'JIICP Options: <i>all option values will be sent to support to ease debugging</i>';
    echo '</p></td></tr>';
    echo '</table>';
    echo '<a id="jiicp-send-support-message" href="#" class="js-action button button-primary"><span class="dashicons dashicons-update"></span>' . __('Send Message to Support', 'JW_maintenance-mode') . '</a>';
    echo '</table>';
    echo '</div>'; // contact

    echo '</div>'; // tabs
  } // tab_support
 
  static function tab_pro() {}


  // output the whole options page
  static function main_page() {
    if (!current_user_can('manage_options'))  {
      mcms_die('You do not have sufficient permissions to access this page.');
    }

    $options = self::get_options();
    $default_options = self::default_options();

    echo '<div class="wrap">
          <h1 class="jiicp-logo"><a href="' . admin_url('options-general.php?page=jiicp') . '"><img src="' . JIICP_MODULE_URL . 'images/jiicp_logo.png" class="rotate" alt="MaintenanceModePage" title="MaintenanceModePage"><img src="' . JIICP_MODULE_URL . 'images/jiicp_logo_2.png" class="jiicp-logo-text" alt="MaintenanceModePage" title="MaintenanceModePage"></a></h1>';

    echo '<form action="options.php" method="post" id="jiicp_form">';
    settings_fields(JIICP_OPTIONS_KEY);

    $tabs = array();
    $tabs[] = array('id' => 'jiicp_main', 'icon' => 'dashicons-admin-settings', 'class' => '', 'label' => __('Main', 'JW_maintenance-mode'), 'callback' => array(__CLASS__, 'tab_main'));
    $tabs[] = array('id' => 'jiicp_design', 'icon' => 'dashicons-admin-customizer', 'class' => '', 'label' => __('Design', 'JW_maintenance-mode'), 'callback' => array(__CLASS__, 'tab_design'));
    $tabs[] = array('id' => 'jiicp_content', 'icon' => 'dashicons-format-aside', 'class' => '', 'label' => __('Content', 'JW_maintenance-mode'), 'callback' => array(__CLASS__, 'tab_content'));
    $tabs[] = array('id' => 'jiicp_access', 'icon' => 'dashicons-shield', 'class' => '', 'label' => __('Access', 'JW_maintenance-mode'), 'callback' => array(__CLASS__, 'tab_access'));
    $tabs = apply_filters('jiicp_tabs', $tabs);

    echo '<div id="jiicp_tabs" class="ui-tabs" style="display: none;">';
    echo '<ul class="jiicp-main-tab">';
    foreach ($tabs as $tab) {
      if(!empty($tab['label'])){
          echo '<li><a href="#' . $tab['id'] . '" class="' . $tab['class'] . '"><span class="icon"><span class="dashicons ' . $tab['icon'] . '"></span></span><span class="label">' . $tab['label'] . '</span></a></li>';
      }
    }
    echo '</ul>';

    foreach ($tabs as $tab) {
      if(is_callable($tab['callback'])) {
        echo '<div style="display: none;" id="' . $tab['id'] . '">';
        call_user_func($tab['callback']);
        echo '</div>';
      }
    } // foreach

    echo '</div>'; // jiicp_tabs

    echo '</form>'; // jiicp_tabs
    echo '</div>'; // wrap

    $promo = self::is_promo_active();
    if ($promo == 'welcome') {
      $header = 'A <b>welcoming discount</b> has been applied to all packages! It\'s <b>time limited</b> and available for only another <b class="jiicp-countdown">59min 30sec</b>.';
      $products['agency'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'agency-lifetime-welcome', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>$51 OFF</u><br><del>$250</del> $199<br><small>Discount ends in <b class="jiicp-countdown">59min 30sec</b></small>');
      $products['pro-lifetime'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-lifetime-welcome', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>20% OFF</u><br><del>$69</del> $55<br><small>Discount ends in <b class="jiicp-countdown">59min 30sec</b></small>');
      $products['pro-yearly'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-yearly-welcome', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>20% OFF</u><br><del>$39</del> $31<small>/year</small><br><small>Discount ends in <b class="jiicp-countdown">59min 30sec</b></small>');
    } elseif ($promo == 'olduser') {
      $header = 'A special <b>discount for long-term users</b> has been applied to all packages!';
      $products['agency'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'agency-lifetime-olduser', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>$51 OFF</u><br><del>$250</del> $199');
      $products['pro-lifetime'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-lifetime-olduser', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>20% OFF</u><br><del>$69</del> $55');
      $products['pro-yearly'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-yearly-olduser', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW <u>20% OFF</u><br><del>$39</del> $31<small>/year</small>');
    } else {
      $header = '';
      $products['agency'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'agency-lifetime', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW<br>$250');
      $products['pro-lifetime'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-lifetime', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW<br>$69');
      $products['pro-yearly'] = array('link' => self::generate_web_link('pricing-table', 'buy/', array('p' => 'pro-yearly', 'r' => 'JIICP v' . self::$version)), 'price' => 'BUY NOW<br>$39 <small>/year</small>');
    }

    // upsell dialog
    echo '<div id="upsell-dialog" style="display: none;" title="MaintenanceModePage PRO"><span class="ui-helper-hidden-accessible"><input type="text"/></span>';
    echo '<div id="tabs_upsell" class="ui-tabs jiicp-tabs-2nd-level">';
    echo '<ul>';
    echo '<li><a href="#tab-upsell-buy">' . __('Get PRO', 'JW_maintenance-mode') . '</a></li>';
    echo '<li><a href="#tab-upsell-features">' . __('Features', 'JW_maintenance-mode') . '</a></li>';
    echo '</ul>';

    echo '<div class="upsell-tab" id="tab-upsell-features" style="display: none;">';
    echo '<div class="gmw-pro-feature">';
    echo '<span>Frictionless Drag &amp; Drop Builder</span>';
    echo '<p>Forget about complicated, cumbersome builders that have too many options! JIICP builder was purpose-built for the task at hand. Simple, user-friendly &amp; has only the options you need to build pages fast!</p>';
    echo '</div>';

    echo '<div class="gmw-pro-feature">';
    echo '<span>400,000+ Stunning Searchable Images</span>';
    echo '<p>There\'s nothing worse than googling for hours just to find that the perfect image you need is either copyrighted or too small. Enjoy a vast library of 4K+ sized images - categorized &amp; copyright free!</p>';
    echo '</div>';

    echo '<div class="gmw-pro-feature">';
    echo '<span>100+ Templates</span>';
    echo '<p>Building your own page from scratch is fun, but often you don\'t have time to do it! Use one of our purpose-built templates, change a few lines of text and you\'re ready to rock!</p>';
    echo '</div>';

    echo '<div class="gmw-pro-feature">';
    echo '<span>Affiliate & Traffic Tracking</span>';
    echo '<p>Having traffic is nice. Having targeted traffic is better! Generate tracked inbound links &amp; share them on social media or with your affiliates to pinpoint the best traffic sources.</p>';
    echo '</div>';

    echo '<div class="gmw-pro-feature">';
    echo '<span>Unlimited 3rd Party Integrations</span>';
    echo '<p>With our unique universal autoresponder support, you can integrate any email autoresponder or webinar system in a page within seconds. Or push data to Zapier to more than 1,000 applications.</p>';
    echo '</div>';

    echo '<div class="gmw-pro-feature">';
    echo '<span>Suitable for Agencies &amp; Webmasters</span>';
    echo '<p>Creating sites for others? We have your back! Our support &amp; licensing options are optimised for agencies. Unlimited sites license &amp; in-house, USA based support guarantee your peace of mind.</p>';
    echo '</div>';

    echo '<p class="upsell-footer">For a complete list of features, demos and screenshots visit <a href="' . self::generate_web_link('features-more-info') . '" target="_blank">jiiworks.net</a>. Already have a PRO license? <a href="#" class="go-to-license-key">Activate it</a>.</p>';

    echo '</div>'; // features tab
    echo '<div class="upsell-tab" id="tab-upsell-buy" style="display: none;">';
    if (!empty($header)) {
      echo '<div class="upsell-header">' . $header . '</div>';
    }
    echo '<table id="jiicp-pricing-table">
  <tbody>
    <tr>
      <td>
        <h3>Lifetime Unlimited<br>Agency License</h3>
      </td>
      <td>
        <h3>Lifetime<br>PRO License</h3>
      </td>
      <td>
        <h3>Yearly<br>PRO License</h3>
      </td>
    </tr>
    <tr>
      <td>One Time Payment</td>
      <td><span class="dashicons dashicons-yes"></span> One Time Payment</td>
      <td>Yearly Payment</td>
    </tr>
    <tr>
      <td>Unlimited Client &amp; Personal Sites</td>
      <td><span class="dashicons dashicons-yes"></span> 1 Personal or Client Site</td>
      <td>3 Personal Sites</td>
    </tr>
    <tr>
      <td>Lifetime Priority Support &amp; Updates</td>
      <td><span class="dashicons dashicons-yes"></span> Lifetime Support &amp; Updates</td>
      <td>1 Year of Support &amp; Updates</td>
    </tr>
    <tr style="display: none;">
      <td>400,000+ Hi-Res Images</td>
      <td><span class="dashicons dashicons-yes"></span> 400,000+ Hi-Res Images</td>
      <td>400,000+ Hi-Res Images</td>
    </tr>
    <tr>
      <td>50+ Templates + Drag&amp;Drop Builder</td>
      <td><span class="dashicons dashicons-yes"></span> 50+ Templates + Drag&amp;Drop Builder</td>
      <td>50+ Templates + Drag&amp;Drop Builder</td>
    </tr>
    <tr>
      <td>50+ Extra Templates = 100+ Templates</td>
      <td><span class="dashicons dashicons-no"></td>
      <td><span class="dashicons dashicons-no"></td>
    </tr>
    <tr style="display: none;">
      <td>Zapier Integration + Extra Modules</td>
      <td><span class="dashicons dashicons-no"></td>
      <td><span class="dashicons dashicons-no"></td>
    </tr>
    <tr>
      <td>
        <a data-href-org="' . $products['agency']['link'] . '" class="promo-button go-to-license-key" href="' . $products['agency']['link'] . '" target="_blank">' . $products['agency']['price'] . '</a>
        <span class="instant-download"><span class="dashicons dashicons-yes"></span> Secure payment<br><span class="dashicons dashicons-yes"></span> Instant activation from MandarinCMS admin<br><span class="dashicons dashicons-yes"></span> 100% No-Risk Money Back Guarantee</span>
      </td>
      <td>
        <a data-href-org="' . $products['pro-lifetime']['link'] . '" class="promo-button go-to-license-key" href="' . $products['pro-lifetime']['link'] . '" target="_blank">' . $products['pro-lifetime']['price'] . '</a>
        <span class="instant-download"><span class="dashicons dashicons-yes"></span> Secure payment<br><span class="dashicons dashicons-yes"></span> Instant activation from MandarinCMS admin<br><span class="dashicons dashicons-yes"></span> 100% No-Risk Money Back Guarantee</span>
      </td>
      <td>
        <a data-href-org="' . $products['pro-yearly']['link'] . '" class="promo-button go-to-license-key" href="' . $products['pro-yearly']['link'] . '" target="_blank">' . $products['pro-yearly']['price'] . '</a>
        <span class="instant-download"><span class="dashicons dashicons-yes"></span> Secure payment<br><span class="dashicons dashicons-yes"></span> Instant activation from MandarinCMS admin<br><span class="dashicons dashicons-yes"></span> 100% No-Risk Money Back Guarantee</span>
      </td>
    </tr>
  </tbody>
</table>';
    echo '<p class="upsell-footer">More pricing options &amp; details about packages are available on <a href="' . self::generate_web_link('pricing-table-more-info') . '" target="_blank">jiiworks.net</a>. Already have a PRO license? <a href="#" class="go-to-license-key">Activate it</a>.</p>';
    echo '</div>'; // pricing tab
    echo '</div>';

    echo '</div>'; // upsell-dialog
  } // main_page


  // tests if any of the promotions are active and if so returns the name
  static function is_promo_active() {
    $meta = self::get_meta();

    if ((time() - $meta['first_install']) < HOUR_IN_SECONDS) {
      return 'welcome';
    }

    if ((time() - $meta['first_install']) > DAY_IN_SECONDS * 45) {
      return 'olduser';
    }

    return false;
  } // is_promo_active


  // save and preview buttons
  static function footer_buttons() {
    echo '<p class="submit">';
    echo get_submit_button(__('Save Changes', 'JW_maintenance-mode'), 'primary large', 'submit', false);
    echo ' &nbsp; &nbsp; <a id="jiicp_preview" href="' . get_home_url() . '/?jiicp_preview" class="button button-large button-secondary" target="_blank">' . __('Preview', 'JW_maintenance-mode') . '</a>';
    echo '</p>';
  } // footer_buttons


  // markup for deactivate dialog
  static function footer_modules() {
    $support_link = admin_url('options-general.php?page=jiicp&urgent-support=true#support');

    echo '<div id="jiicp-deactivate-survey" style="display: none;" title="Please help us make JIICP better"><span class="ui-helper-hidden-accessible"><input type="text"/></span>';

    echo '<div class="question-wrapper-assistance" data-value="urgent">' .
         '<div class="question"><b>Something\'s not working? We offer URGENT assistance!</b><br><a href="' . $support_link . '" class="button">Send a priority ticket to our friendly support agents</a><br><small><i>average response time is under 20 minutes</i></small></div>' . '</div>';

    echo '<p class="textcenter"><br>We want to improve! Please tell us:<br><b>Why are you deactivating <span class="jiicp-logo">MaintenanceModePage</span>?</b></p>';

    $questions = array();
    $questions[] = '<div class="question-wrapper" data-value="temporary">' .
                   '<div class="question">It\'s a temporary deactivation, I\'m debugging something</div>' .
                   '</div>';

    $questions[] = '<div class="question-wrapper" data-value="not-working">' .
                   '<div class="question">Module is not working<div class="details">Please tell us what exactly is not working: <input type="text" class="normal-text jiicp-deactivation-details"></div></div>' .
                   '</div>';

    $questions[] = '<div class="question-wrapper" data-value="wrong-module">' .
                   '<div class="question">Module is not what I thought it is, I need a different module</div>' .
                   '</div>';

    $questions[] = '<div class="question-wrapper" data-value="site-live">' .
                   '<div class="question">It served its purpose - site is now live</div>' .
                   '</div>';

    $questions[] = '<div class="question-wrapper" data-value="missing-feature">' .
                   '<div class="question">It doesn\'t have all the features I need<div class="details">Please tell us what features are missing: <input type="text" class="normal-text jiicp-deactivation-details"></div></div>' .
                   '</div>';

    shuffle($questions);
    $questions[] = '<div class="question-wrapper" data-value="other">' .
                   '<div class="question">Something else<div class="details">Please tell us the reason: <input type="text" class="normal-text jiicp-deactivation-details"></div></div>' .
                   '</div>';
    echo implode(' ', $questions);


    $current_user = mcms_get_current_user();
    echo '<div class="footer">';
    echo '<a class="jiicp-cancel-deactivate js-action button-secondary button button-large" href="#">Cancel Deactivation</a> <a data-survey="deactivate" class="button-primary button button-large jiicp-deactivate" href="#">Continue with Deactivation</a>';
    echo '<br><br><a href="#" class="js-action jiicp-deactivate-direct"><small><i>Deactivate without providing feedback</i></small></a>';
    echo '</div>';

    echo '</div>';
  } // footer_modules


  // reset all pointers to default state - visible
  static function reset_pointers() {
    $pointers = array();

    $pointers['welcome'] = array('target' => '#menu-settings', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing the <b style="font-weight: 800; font-variant: small-caps;">MaintenanceModePage</b> module! Please open <a href="' . admin_url('options-general.php?page=jiicp'). '">Settings - MaintenanceMode</a> to create a beautiful under construction page.');
    $pointers['getting_started'] = array('target' => '.jiicp-main-tab li:nth-child(2)', 'edge' => 'top', 'align' => 'left', 'content' => 'Watch the short <a href="https://www.youtube.com/watch?v=RN4XABhK7_w" target="_blank">getting started video</a> to get you up to speed with JIICP in no time. If that doesn\'t answer your questions watch the longer <a href="https://www.youtube.com/watch?v=K3DF-NP6Fog" target="_blank">in-depth video walktrough</a>.<br>If you need the videos later, links are in the <a href="#" class="change_tab" data-tab="4">FAQ</a>.');

    update_option(JIICP_POINTERS_KEY, $pointers);
  } // reset_pointers


  // reset pointers on activation
  static function activate() {
    self::reset_pointers();
  } // activate

  // clean up on deactivation
  static function deactivate() {
    delete_option(JIICP_POINTERS_KEY);
    delete_option(JIICP_NOTICES_KEY);
  } // deactivate


  // clean up on uninstall
  static function uninstall() {
    delete_option(JIICP_OPTIONS_KEY);
    delete_option(JIICP_META_KEY);
    delete_option(JIICP_POINTERS_KEY);
    delete_option(JIICP_NOTICES_KEY);
  } // uninstall
} // class JIICP


// hook everything up
register_activation_hook(__FILE__, array('JIICP', 'activate'));
register_deactivation_hook(__FILE__, array('JIICP', 'deactivate'));
register_uninstall_hook(__FILE__, array('JIICP', 'uninstall'));
add_action('init', array('JIICP', 'init'));
add_action('modules_loaded', array('JIICP', 'modules_loaded'));
