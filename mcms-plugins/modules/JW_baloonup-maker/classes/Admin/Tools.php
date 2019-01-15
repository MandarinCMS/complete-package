<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function pum_extension_has_beta_support() {}

/**
 * Class PUM_Admin_Tools
 */
class PUM_Admin_Tools {

	/**
	 * @var array
	 */
	public static $notices = array();

	/**
	 *
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_action( 'admin_init', array( __CLASS__, 'emodal_process_import' ) );
		add_action( 'balooncreate_save_enabled_betas', array( __CLASS__, 'save_enabled_betas' ) );
		add_action( 'pum_tools_page_tab_import', array( __CLASS__, 'emodal_v2_import_button' ) );
		add_action( 'pum_tools_page_tab_system_info', array( __CLASS__, 'sysinfo_display' ) );
		add_action( 'balooncreate_baloonup_sysinfo', array( __CLASS__, 'sysinfo_download' ) );
		add_action( 'pum_tools_page_tab_betas', array( __CLASS__, 'betas_display' ) );
	}

	// display default admin notice

	/**
	 * Displays any saved admin notices.
	 */
	public static function notices() {

		if ( isset( $_GET['imported'] ) ) { ?>
			<div class="updated">
				<p><?php _e( 'Successfully Imported your myskins &amp; modals from Easy Modal.' ); ?></p>
			</div>
			<?php
		}


		if ( isset( $_GET['success'] ) && get_option( 'pum_settings_admin_notice' ) ) {
			self::$notices[] = array(
				'type'    => $_GET['success'] ? 'success' : 'error',
				'message' => get_option( 'pum_settings_admin_notice' ),
			);

			delete_option( 'pum_settings_admin_notice' );
		}

		if ( ! empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) { ?>
				<div class="notice notice-<?php esc_attr_e( $notice['type'] ); ?> is-dismissible">
					<p><strong><?php esc_html_e( $notice['message'] ); ?></strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'baloonup-maker' ); ?></span>
					</button>
				</div>
			<?php }
		}
	}

	/**
	 * Render settings page with tabs.
	 */
	public static function page() {

		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], self::tabs() ) ? $_GET['tab'] : 'system_info';

		?>

		<div class="wrap">

			<form id="pum-tools" method="post" action="">

				<?php mcms_nonce_field( basename( __FILE__ ), 'pum_tools_nonce' ); ?>

				<button class="right top button-primary"><?php _e( 'Save', 'baloonup-maker' ); ?></button>

				<h1><?php _e( 'BaloonUp Maker Tools', 'baloonup-maker' ); ?></h1>

				<h2 id="balooncreate-tabs" class="nav-tab-wrapper"><?php
					foreach ( self::tabs() as $tab_id => $tab_name ) {
						$tab_url = add_query_arg( array(
							'tools-updated' => false,
							'tab'           => $tab_id,
						) );

						printf( '<a href="%s" title="%s" class="nav-tab %s">%s</a>', esc_url( $tab_url ), esc_attr( $tab_name ), $active_tab == $tab_id ? ' nav-tab-active' : '', esc_html( $tab_name ) );
					} ?>
				</h2>

				<div id="tab_container">
					<?php do_action( 'pum_tools_page_tab_' . $active_tab ); ?>

					<?php do_action( 'balooncreate_tools_page_tab_' . $active_tab ); ?>
				</div>

			</form>
		</div>
		<?php
	}


	/**
	 * Tabs & labels
	 *
	 * @since 1.0
	 * @return array $tabs
	 */
	public static function tabs() {
		static $tabs;

		if ( ! isset( $tabs ) ) {
			$tabs = apply_filters( 'pum_tools_tabs', array(
				'betas'       => __( 'Beta Versions', 'baloonup-maker' ),
				'system_info' => __( 'System Info', 'baloonup-maker' ),
				'import'      => __( 'Import / Export', 'baloonup-maker' ),
			) );

			/** @deprecated 1.7.0 */
			$tabs = apply_filters( 'balooncreate_tools_tabs', $tabs );
		}

		if ( count( self::get_beta_enabled_extensions() ) == 0 ) {
			//unset( $tabs['betas'] );
		}

		return $tabs;
	}

	/**
	 * Return an array of all extensions with beta support
	 *
	 * Extensions should be added as 'extension-slug' => 'Extension Name'
	 *
	 * @since       1.5
	 * @return      array $extensions The array of extensions
	 */
	public static function get_beta_enabled_extensions() {
		return apply_filters( 'pum_beta_enabled_extensions', array() );
	}

	/**
	 * @return int|null|string
	 */
	public static function get_active_tab() {
		$tabs = self::tabs();

		return isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? sanitize_text_field( $_GET['tab'] ) : key( $tabs );
	}

	/**
	 * Display the system info tab
	 *
	 * @since       1.3.0
	 */
	public static function sysinfo_display() { ?>
		<form action="" method="post">
			<textarea style="min-height: 350px; width: 100%; display: block;" readonly="readonly"
			          onclick="this.focus(); this.select()" id="system-info-textarea" name="balooncreate-sysinfo"
			          title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'baloonup-maker' ); ?>"><?php echo self::sysinfo_text(); ?></textarea>
			<p class="submit">
				<input type="hidden" name="balooncreate_action" value="baloonup_sysinfo"/>
				<?php submit_button( 'Download System Info File', 'primary', 'balooncreate-download-sysinfo', false ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Get system info
	 *
	 * @since       1.5
	 *
	 * @return      string $return A string containing the info to output
	 */
	public static function sysinfo_text() {
		global $mcmsdb;

		if ( ! class_exists( 'Browser' ) ) {
			require_once POPMAKE_DIR . 'includes/libs/browser.php';
		}

		$browser = new Browser();

		// Get myskin info
		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$myskin_data = get_myskin_data( get_stylesheet_directory() . '/style.css' );
			$myskin      = $myskin_data['Name'] . ' ' . $myskin_data['Version'];
		} else {
			$myskin_data = mcms_get_myskin();
			$myskin      = $myskin_data->Name . ' ' . $myskin_data->Version;
		}

		// Try to identify the hosting provider
		$host = balooncreate_get_host();

		$return = '### Begin System Info ###' . "\n\n";

		// Start with the basics...
		$return .= '-- Site Info' . "\n\n";
		$return .= 'Site URL:                 ' . site_url() . "\n";
		$return .= 'Home URL:                 ' . home_url() . "\n";
		$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_site_info', $return );

		// Can we determine the site's host?
		if ( $host ) {
			$return .= "\n" . '-- Hosting Provider' . "\n\n";
			$return .= 'Host:                     ' . $host . "\n";

			$return = apply_filters( 'balooncreate_sysinfo_after_host_info', $return );
		}

		// The local users' browser information, handled by the Browser class
		$return .= "\n" . '-- User Browser' . "\n\n";
		$return .= $browser;

		$return = apply_filters( 'balooncreate_sysinfo_after_user_browser', $return );

		// MandarinCMS configuration
		$return .= "\n" . '-- MandarinCMS Configuration' . "\n\n";
		$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$return .= 'Language:                 ' . ( defined( 'MCMSLANG' ) && MCMSLANG ? MCMSLANG : 'en_US' ) . "\n";
		$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
		$return .= 'Active mySkin:             ' . $myskin . "\n";
		$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

		// Only show page specs if frontpage is set to 'page'
		if ( get_option( 'show_on_front' ) == 'page' ) {
			$front_page_id = get_option( 'page_on_front' );
			$blog_page_id  = get_option( 'page_for_posts' );

			$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
			$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
		}

		// Make sure mcms_remote_post() is working
		$request['cmd'] = '_notify-validate';

		$params = array(
			'sslverify'  => false,
			'timeout'    => 60,
			'user-agent' => 'POPMAKE/' . POPMAKE_VERSION,
			'body'       => $request,
		);

		$response = mcms_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

		if ( ! is_mcms_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
			$MCMS_REMOTE_POST = 'mcms_remote_post() works';
		} else {
			$MCMS_REMOTE_POST = 'mcms_remote_post() does not work';
		}

		$return .= 'Remote Post:              ' . $MCMS_REMOTE_POST . "\n";
		$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $mcmsdb->prefix ) . '   Status: ' . ( strlen( $mcmsdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
		$return .= 'MCMS_DEBUG:                 ' . ( defined( 'MCMS_DEBUG' ) ? MCMS_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
		$return .= 'Memory Limit:             ' . MCMS_MEMORY_LIMIT . "\n";
		$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_mandarincms_config', $return );

		// BaloonUp Maker configuration
		$return .= "\n" . '-- BaloonUp Maker Configuration' . "\n\n";
		$return .= 'Version:                  ' . POPMAKE_VERSION . "\n";
		$return .= 'Upgraded From:            ' . get_option( 'balooncreate_version_upgraded_from', 'None' ) . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_balooncreate_config', $return );

		// Must-use modules
		$mumodules = get_mu_modules();
		if ( $mumodules && count( $mumodules ) ) {
			$return .= "\n" . '-- Must-Use Modules' . "\n\n";

			foreach ( $mumodules as $module => $module_data ) {
				$return .= $module_data['Name'] . ': ' . $module_data['Version'] . "\n";
			}

			$return = apply_filters( 'balooncreate_sysinfo_after_mandarincms_mu_modules', $return );
		}

		// MandarinCMS active modules
		$return .= "\n" . '-- MandarinCMS Active Modules' . "\n\n";

		$modules        = get_modules();
		$active_modules = get_option( 'active_modules', array() );

		foreach ( $modules as $module_path => $module ) {
			if ( ! in_array( $module_path, $active_modules ) ) {
				continue;
			}

			$return .= $module['Name'] . ': ' . $module['Version'] . "\n";
		}

		$return = apply_filters( 'balooncreate_sysinfo_after_mandarincms_modules', $return );

		// MandarinCMS inactive modules
		$return .= "\n" . '-- MandarinCMS Inactive Modules' . "\n\n";

		foreach ( $modules as $module_path => $module ) {
			if ( in_array( $module_path, $active_modules ) ) {
				continue;
			}

			$return .= $module['Name'] . ': ' . $module['Version'] . "\n";
		}

		$return = apply_filters( 'balooncreate_sysinfo_after_mandarincms_modules_inactive', $return );

		if ( is_multisite() ) {
			// MandarinCMS Multisite active modules
			$return .= "\n" . '-- Network Active Modules' . "\n\n";

			$modules        = mcms_get_active_network_modules();
			$active_modules = get_site_option( 'active_sitewide_modules', array() );

			foreach ( $modules as $module_path ) {
				$module_base = module_basename( $module_path );

				if ( ! array_key_exists( $module_base, $active_modules ) ) {
					continue;
				}

				$module = get_module_data( $module_path );
				$return .= $module['Name'] . ': ' . $module['Version'] . "\n";
			}

			$return = apply_filters( 'balooncreate_sysinfo_after_mandarincms_ms_modules', $return );
		}

		// Server configuration (really just versioning)
		$return .= "\n" . '-- Webserver Configuration' . "\n\n";
		$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$return .= 'MySQL Version:            ' . $mcmsdb->db_version() . "\n";
		$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_webserver_config', $return );

		// PHP configs... now we're getting to the important stuff
		$return .= "\n" . '-- PHP Configuration' . "\n\n";
		$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
		$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_php_config', $return );

		// PHP extensions and such
		$return .= "\n" . '-- PHP Extensions' . "\n\n";
		$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
		$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
		$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
		$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

		$return = apply_filters( 'balooncreate_sysinfo_after_php_ext', $return );

		// Session stuff
		$return .= "\n" . '-- Session Configuration' . "\n\n";
		$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

		// The rest of this is only relevant is session is enabled
		if ( isset( $_SESSION ) ) {
			$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
			$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
			$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
			$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
			$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
		}

		$return = apply_filters( 'balooncreate_sysinfo_after_session_config', $return );

		$return .= "\n" . '### End System Info ###';

		return $return;
	}

	/**
	 * Generates a System Info download file
	 *
	 * @since       1.5
	 */
	public static function sysinfo_download() {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="balooncreate-system-info.txt"' );

		echo mcms_strip_all_tags( $_POST['balooncreate-sysinfo'] );
		exit;
	}

	/**
	 * Add a button to import easy modal data.
	 */
	public static function emodal_v2_import_button() { ?>
		<button id="balooncreate_emodal_v2_import" name="balooncreate_emodal_v2_import" class="button button-large">
			<?php _e( 'Import From Easy Modal v2', 'baloonup-maker' ); ?>
		</button>
		<?php
	}

	/**
	 * Process em import.
	 */
	public static function emodal_process_import() {
		if ( ! isset( $_REQUEST['balooncreate_emodal_v2_import'] ) ) {
			return;
		}
		balooncreate_emodal_v2_import();
		mcms_redirect( admin_url( 'edit.php?post_type=baloonup&page=pum-tools&imported=1' ), 302 );
	}

	/**
	 * Save enabled betas
	 *
	 * @since       1.5
	 */
	public static function save_enabled_betas() {
		if ( ! mcms_verify_nonce( $_POST['pum_save_betas_nonce'], 'pum_save_betas_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( $_POST['enabled_betas'] ) ) {
			$enabled_betas = array_filter( array_map( array(
				__CLASS__,
				'enabled_betas_sanitize_value',
			), $_POST['enabled_betas'] ) );
			PUM_Options::update( 'enabled_betas', $enabled_betas );
		} else {
			PUM_Options::delete( 'enabled_betas' );
		}
	}

	/**
	 * Sanitize the supported beta values by making them booleans
	 *
	 * @since 1.5
	 *
	 * @param mixed $value The value being sent in, determining if beta support is enabled.
	 *
	 * @return bool
	 */
	public static function enabled_betas_sanitize_value( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Display beta opt-ins
	 *
	 * @since       1.3
	 */
	public static function betas_display() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$has_beta = self::get_beta_enabled_extensions();

		do_action( 'pum_tools_betas_before' );
		?>

		<div class="postbox pum-beta-support">
			<h3><span><?php _e( 'Enable Beta Versions', 'baloonup-maker' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Checking any of the below checkboxes will opt you in to receive pre-release update notifications. You can opt-out at any time. Pre-release updates do not install automatically, you will still have the opportunity to ignore update notifications.', 'baloonup-maker' ); ?></p>
				<table class="form-table pum-beta-support">
					<tbody>
					<?php foreach ( $has_beta as $slug => $product ) : ?>
						<tr>
							<?php $checked = self::extension_has_beta_support( $slug ); ?>
							<th scope="row"><?php echo esc_html( $product ); ?></th>
							<td>
								<input type="checkbox" name="enabled_betas[<?php echo esc_attr( $slug ); ?>]"
								       id="enabled_betas[<?php echo esc_attr( $slug ); ?>]"<?php echo checked( $checked, true, false ); ?>
								       value="1"/>
								<label
									for="enabled_betas[<?php echo esc_attr( $slug ); ?>]"><?php printf( __( 'Get updates for pre-release versions of %s', 'baloonup-maker' ), $product ); ?></label>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="balooncreate_action" value="save_enabled_betas"/>
				<?php mcms_nonce_field( 'pum_save_betas_nonce', 'pum_save_betas_nonce' ); ?>
				<?php submit_button( __( 'Save', 'baloonup-maker' ), 'secondary', 'submit', false ); ?>
			</div>
		</div>

		<?php
		do_action( 'pum_tools_betas_after' );
	}

	/**
	 * Check if a given extensions has beta support enabled
	 *
	 * @since       1.5
	 *
	 * @param       string $slug The slug of the extension to check
	 *
	 * @return      bool True if enabled, false otherwise
	 */
	public static function extension_has_beta_support( $slug ) {
		$enabled_betas = PUM_Options::get( 'enabled_betas', array() );
		$return        = false;

		if ( array_key_exists( $slug, $enabled_betas ) ) {
			$return = true;
		}

		return $return;
	}


}
