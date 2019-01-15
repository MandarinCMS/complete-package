<?php
/**
 * Add Link Administration Screen.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('manage_links') )
	mcms_die(__('Sorry, you are not allowed to add links to this site.'));

$title = __('Add New Link');
$parent_file = 'link-manager.php';

mcms_reset_vars( array('action', 'cat_id', 'link_id' ) );

mcms_enqueue_script('link');
mcms_enqueue_script('xfn');

if ( mcms_is_mobile() )
	mcms_enqueue_script( 'jquery-touch-punch' );

$link = get_default_link_to_edit();
include( BASED_TREE_URI . 'mcms-admin/edit-link-form.php' );

require( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
