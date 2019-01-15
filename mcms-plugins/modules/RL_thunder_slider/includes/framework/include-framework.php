<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

$folderIncludes = dirname(__FILE__)."/";

require_once($folderIncludes . 'functions.class.php');
require_once($folderIncludes . 'functions-mandarincms.class.php');
require_once($folderIncludes . 'db.class.php');
require_once($folderIncludes . 'cssparser.class.php');
require_once($folderIncludes . 'mcmsml.class.php');
require_once($folderIncludes . 'woocommerce.class.php');
require_once($folderIncludes . 'em-integration.class.php');
require_once($folderIncludes . 'aq-resizer.class.php');
require_once($folderIncludes . 'module-update.class.php');
require_once($folderIncludes . 'addon-admin.class.php');
require_once($folderIncludes . 'colorpicker.class.php');
require_once($folderIncludes . 'loadbalancer.class.php');
?>