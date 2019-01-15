<?php if( !defined( 'BASED_TREE_URI') ) exit(); ?>

<div id="dialog_copy_move" data-textclose="<?php _e("Close",'thunderslider')?>" data-textupdate="<?php _e("Do It!",'thunderslider')?>" title="<?php _e("Copy / move slide",'thunderslider')?>" style="display:none">
	
	<br>
	
	<?php _e("Choose Slider",'thunderslider')?>:
	<?php echo $selectSliders; ?>
	
	<br><br>
	
	<?php _e("Choose Operation",'thunderslider')?>:
	
	<input type="radio" id="radio_copy" value="copy" name="copy_move_operation" checked />
	<label for="radio_copy" style="cursor:pointer;"><?php _e("Copy",'thunderslider')?></label>
	&nbsp; &nbsp;
	<input type="radio" id="radio_move" value="move" name="copy_move_operation" />
	<label for="radio_move" style="cursor:pointer;"><?php _e("Move",'thunderslider')?></label>		
	
</div>