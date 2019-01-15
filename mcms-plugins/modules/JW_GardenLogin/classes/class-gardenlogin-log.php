<?php

/**
 * Log file to know more about users website environment.
 * helps in debugging and providing support.
 *
 * @package    GardenLogin
 * @since      1.0.19
 */

class GardenLogin_Log_Info {

	/**
	 * Returns the module & system information.
	 * @access public
	 * @return string
	 */
	public static function get_sysinfo() {

		global $mcmsdb;
		$gardenlogin_setting = get_option( 'gardenlogin_setting' );
		$gardenlogin_config 	= get_option( 'gardenlogin_customization' );
		$session_expiration = ( isset( $gardenlogin_setting['session_expiration'] ) && '0' != $gardenlogin_setting['session_expiration'] ) ? $gardenlogin_setting['session_expiration'] . ' Minute' : 'Not Set';
		$login_order 	= isset( $gardenlogin_setting['login_order'] ) ? $gardenlogin_setting['login_order'] : 'Default';
		$customization 			= isset( $gardenlogin_config ) ? print_r( $gardenlogin_config, true ) : 'No customization yet';

		$html = '### Begin System Info ###' . "\n\n";

		// Basic site info
		$html .= '-- MandarinCMS Configuration --' . "\n\n";
		$html .= 'Site URL:                 ' . site_url() . "\n";
		$html .= 'Home URL:                 ' . home_url() . "\n";
		$html .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";
		$html .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$html .= 'Language:                 ' . get_locale() . "\n";
		$html .= 'Table Prefix:             ' . 'Length: ' . strlen( $mcmsdb->prefix ) . "\n";
		$html .= 'MCMS_DEBUG:                 ' . ( defined( 'MCMS_DEBUG' ) ? MCMS_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
		$html .= 'Memory Limit:             ' . MCMS_MEMORY_LIMIT . "\n";

		// Module Configuration
		$html .= "\n" . '-- GardenLogin Configuration --' . "\n\n";
		$html .= 'Module Version:           ' . LOGINPRESS_VERSION . "\n";
		$html .= 'Expiration:           	  ' . $session_expiration . "\n";
		$html .= 'Login Order:              ' . ucfirst( $login_order ) . "\n";
		$html .= 'Total Customized Fields:  ' . count( $gardenlogin_config ) . "\n";
		$html .= 'Customization Detail:     ' . $customization . "\n";

		// Pro Module Configuration
		if ( class_exists( 'GardenLogin_Pro' ) ) {

			$enable_repatcha = ( isset( $gardenlogin_setting['enable_repatcha'] ) ) ? $gardenlogin_setting['enable_repatcha'] : 'Off';
			$gardenlogin_preset	= get_option( 'customize_presets_settings', 'default1' );

			$html .= "\n" . '-- GardenLogin Pro Configuration --' . "\n\n";
			$html .= 'Module Version:           ' . LOGINPRESS_PRO_VERSION . "\n";
			$html .= 'GardenLogin Template:      ' . $gardenlogin_preset . "\n";
			$html .= 'Google Repatcha Status:   ' . $enable_repatcha . "\n";

			if ( 'on' == $enable_repatcha ) {
				$site_key = ( isset( $gardenlogin_setting['site_key'] ) ) ? $gardenlogin_setting['site_key'] : 'Not Set';
				$secret_key = ( isset( $gardenlogin_setting['secret_key'] ) ) ? $gardenlogin_setting['secret_key'] : 'Not Set';
				$captcha_myskin = ( isset( $gardenlogin_setting['captcha_myskin'] ) ) ? $gardenlogin_setting['captcha_myskin'] : 'Light';
				$captcha_language = ( isset( $gardenlogin_setting['captcha_language'] ) ) ? $gardenlogin_setting['captcha_language'] : 'English (US)';
				$captcha_enable_on = ( isset( $gardenlogin_setting['captcha_enable'] ) ) ? $gardenlogin_setting['captcha_enable'] : 'Not Set';

				$html .= 'Repatcha Site Key:        ' . $site_key . "\n";
				$html .= 'Repatcha Secret Key:      ' . $secret_key . "\n";
				$html .= 'Repatcha MySkin Used:      ' . $captcha_myskin . "\n";
				$html .= 'Repatcha Language Used:   ' . $captcha_language . "\n";
				if ( is_array( $captcha_enable_on ) ) {
					foreach ( $captcha_enable_on as $key ) {
						$html .= 'Repatcha Enable On:       ' . ucfirst( str_replace( "_", " ", $key ) )  . "\n";
					}
				}
			}
		}
		// Server Configuration
		$html .= "\n" . '-- Server Configuration --' . "\n\n";
		$html .= 'Operating System:         ' . php_uname( 's' ) . "\n";
		$html .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$html .= 'MySQL Version:            ' . $mcmsdb->db_version() . "\n";

		$html .= 'Server Software:          ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

		// PHP configs... now we're getting to the important stuff
		$html .= "\n" . '-- PHP Configuration --' . "\n\n";
		// $html .= 'Safe Mode:                ' . ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' . "\n" );
		$html .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$html .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$html .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$html .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$html .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$html .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		// MandarinCMS active modules
		$html .= "\n" . '-- MandarinCMS Active Modules --' . "\n\n";
		$modules = get_modules();
		$active_modules = get_option( 'active_modules', array() );
		foreach( $modules as $module_path => $module ) {
			if( !in_array( $module_path, $active_modules ) )
				continue;
			$html .= $module['Name'] . ': ' . $module['Version'] . "\n";
		}

		// MandarinCMS inactive modules
		$html .= "\n" . '-- MandarinCMS Inactive Modules --' . "\n\n";
		foreach( $modules as $module_path => $module ) {
			if( in_array( $module_path, $active_modules ) )
				continue;
			$html .= $module['Name'] . ': ' . $module['Version'] . "\n";
		}

		if( is_multisite() ) {
			// MandarinCMS Multisite active modules
			$html .= "\n" . '-- Network Active Modules --' . "\n\n";
			$modules = mcms_get_active_network_modules();
			$active_modules = get_site_option( 'active_sitewide_modules', array() );
			foreach( $modules as $module_path ) {
				$module_base = module_basename( $module_path );
				if( !array_key_exists( $module_base, $active_modules ) )
					continue;
				$module  = get_module_data( $module_path );
				$html .= $module['Name'] . ': ' . $module['Version'] . "\n";
			}
		}

		$html .= "\n" . '### End System Info ###';
		return $html;
	}
} // End of Class.
