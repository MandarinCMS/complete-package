<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

 
if( !defined( 'BASED_TREE_URI') ) exit();

$is_edit = false;

require self::getPathTemplate('slider-main-options');
?>

<script type="text/javascript">
	var g_jsonTaxWithCats = <?php echo $jsonTaxWithCats?>;

	jQuery(document).ready(function(){
		ThunderSliderAdmin.initAddSliderView();
	});
</script>

