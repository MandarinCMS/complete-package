<?php if( !defined( 'BASED_TREE_URI') ) exit(); ?>
<input type="hidden" id="sliderid" value="<?php echo $sliderID; ?>"></input>

<?php
$is_edit = true;
require self::getPathTemplate('slider-main-options');
?>

<script type="text/javascript">
	var g_jsonTaxWithCats = <?php echo $jsonTaxWithCats?>;

	jQuery(document).ready(function(){			
		ThunderSliderAdmin.initEditSliderView();
	});
</script>