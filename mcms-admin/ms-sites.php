<?php
/**
 * Multisite sites administration panel.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.0.0
 */

require_once( dirname( __FILE__ ) . '/admin.php' );

mcms_redirect( network_admin_url('sites.php') );
exit;
