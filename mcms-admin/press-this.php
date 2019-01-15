<?php
/**
 * Press This Display and Handler.
 *
 * @package MandarinCMS
 * @subpackage Press_This
 */

define( 'IFRAME_REQUEST' , true );

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

function mcms_load_press_this() {
	$module_slug = 'press-this';
	$module_file = 'press-this/press-this-module.php';

	if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
		mcms_die(
			__( 'Sorry, you are not allowed to create posts as this user.' ),
			__( 'You need a higher level of permission.' ),
			403
		);
	} elseif ( is_module_active( $module_file ) ) {
		include( MCMS_PLUGIN_DIR . '/press-this/class-mcms-press-this-module.php' );
		$mcms_press_this = new MCMS_Press_This_Module();
		$mcms_press_this->html();
	} elseif ( current_user_can( 'activate_modules' ) ) {
		if ( file_exists( MCMS_PLUGIN_DIR . '/' . $module_file ) ) {
			$url = mcms_nonce_url( add_query_arg( array(
				'action' => 'activate',
				'module' => $module_file,
				'from'   => 'press-this',
			), admin_url( 'modules.php' ) ), 'activate-module_' . $module_file );
			$action = sprintf(
				'<a href="%1$s" aria-label="%2$s">%2$s</a>',
				esc_url( $url ),
				__( 'Activate Press This' )
			);
		} else {
			if ( is_main_site() ) {
				$url = mcms_nonce_url( add_query_arg( array(
					'action' => 'install-module',
					'module' => $module_slug,
					'from'   => 'press-this',
				), self_admin_url( 'update.php' ) ), 'install-module_' . $module_slug );
				$action = sprintf(
					'<a href="%1$s" class="install-now" data-slug="%2$s" data-name="%2$s" aria-label="%3$s">%3$s</a>',
					esc_url( $url ),
					esc_attr( $module_slug ),
					__( 'Install Now' )
				);
			} else {
				$action = sprintf(
					/* translators: URL to mcms-admin/press-this.php */
					__( 'Press This is not installed. Please install Press This from <a href="%s">the main site</a>.' ),
					get_admin_url( get_current_network_id(), 'press-this.php' )
				);
			}
		}
		mcms_die(
			__( 'The Press This module is required.' ) . '<br />' . $action,
			__( 'Installation Required' ),
			200
		);
	} else {
		mcms_die(
			__( 'Press This is not available. Please contact your site administrator.' ),
			__( 'Installation Required' ),
			200
		);
	}
}

mcms_load_press_this();
