<?php 
if( !defined( 'BASED_TREE_URI') && !defined('MCMS_UNINSTALL_PLUGIN') )
	exit();

/**
 * disable deletion of anything 
 * @since 5.0
 
$currentFile = __FILE__;
$currentFolder = dirname($currentFile);
require_once $currentFolder . '/inc_php/globals.class.php';

global $mcmsdb;
$tableSliders = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_SLIDERS_NAME;
$tableSlides = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_SLIDES_NAME;
$tableSettings = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_SETTINGS_NAME;
$tableCss = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_CSS_NAME;
$tableAnims = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_LAYER_ANIMS_NAME;
$tableStaticSlides = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_STATIC_SLIDES_NAME;

$mcmsdb->query( "DROP TABLE $tableSliders" );
$mcmsdb->query( "DROP TABLE $tableSlides" );
$mcmsdb->query( "DROP TABLE $tableSettings" );
$mcmsdb->query( "DROP TABLE $tableCss" );
$mcmsdb->query( "DROP TABLE $tableAnims" );
$mcmsdb->query( "DROP TABLE $tableStaticSlides" );

//deactivate activation if module was activated

delete_option('thunderslider-latest-version');
delete_option('thunderslider-update-check-short');
delete_option('thunderslider-update-check');
delete_option('thunderslider_update_info');
delete_option('thunderslider-code');
delete_option('thunderslider-valid');
delete_option('thunderslider-valid-notice');
*/

//needs to be deleted so that everything gets checked at a new installation
delete_option('thunderslider_table_version');
delete_option('thunderslider_checktables');
delete_option('rs_public_version');
?>