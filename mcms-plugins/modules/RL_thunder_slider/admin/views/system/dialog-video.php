<?php if( !defined( 'BASED_TREE_URI') ) exit(); ?>

<!-- //Youtube dialog: -->
<div id="dialog_video" class="dialog-video" title="<?php _e('Add Video Layer', 'thunderslider'); ?>" style="display:none">
	
	<form id="video_dialog_form" name="video_dialog_form" onkeypress="return event.keyCode != 13;">
		<div id="video_content" style="display:none"></div>

		<div id="video-dialog-wrap">
			<div id="video_dialog_tabs" class="box-closed tp-accordion disabled" style="background:#fff">
				<ul class="rs-layer-settings-tabs">
					<li class="selected" data-content="#rs-video-source" id="reset_video_dialog_tab"><i style="height:45px" class="rs-mini-layer-icon eg-icon-export rs-toolbar-icon"></i><?php _e('Source', 'thunderslider'); ?></li>
					<li class="rs-hide-on-audio" data-content="#rs-video-size"><i style="height:45px; font-size:16px" class="rs-mini-layer-icon eg-icon-resize-full-alt rs-toolbar-icon"></i><?php _e('Sizing', 'thunderslider'); ?></li>
					<li class="" data-content="#rs-video-settings"><i style="height:45px; font-size:16px" class="rs-mini-layer-icon eg-icon-cog rs-toolbar-icon"></i><?php _e('Settings', 'thunderslider'); ?></li>
					<li class="rs-hide-on-audio" data-content="#rs-video-thumbnails"><i style="height:45px; font-size:16px" class="rs-mini-layer-icon eg-icon-eye rs-toolbar-icon"></i><?php _e('Poster/Mobile Visibility', 'thunderslider'); ?></li>
					<li class="" data-content="#rs-video-arguments"><i style="height:45px; font-size:16px" class="rs-mini-layer-icon eg-icon-th rs-toolbar-icon"></i><?php _e('Arguments', 'thunderslider'); ?></li>
				</ul>
				<div style="clear:both"></div>
			</div>
			
		</div>
		
		<div id="rs-video-source">
			<!-- Type chooser -->
			<div id="video_type_chooser" class="video-type-chooser" style="margin-bottom:25px">
				<label><?php _e('Choose video type', 'thunderslider'); ?></label>
				<input type="radio" checked id="video_radio_youtube" name="video_select">
				<span for="video_radio_youtube"><?php _e('YouTube', 'thunderslider'); ?></span>
				<input type="radio" id="video_radio_vimeo" name="video_select" style="margin-left:20px">
				<span for="video_radio_vimeo"><?php _e('Vimeo', 'thunderslider'); ?></span>
				<input type="radio" id="video_radio_html5" name="video_select" style="margin-left:20px">
				<span for="video_radio_html5"><?php _e('HTML5', 'thunderslider'); ?></span>
				
				<span class="rs-show-when-youtube-stream" style="display: none;">
					<input type="radio" id="video_radio_streamyoutube" name="video_select" style="margin-left:20px">
					<span for="video_radio_streamyoutube"><?php _e('From Stream', 'thunderslider'); ?></span>
				</span>
				<span class="rs-show-when-vimeo-stream" style="display: none;">
					<input type="radio" id="video_radio_streamvimeo" name="video_select" style="margin-left:20px">
					<span for="video_radio_streamvimeo"><?php _e('From Stream', 'thunderslider'); ?></span>
				</span>
				<span class="rs-show-when-instagram-stream" style="display: none;">
					<input type="radio" id="video_radio_streaminstagram" name="video_select" style="margin-left:20px">
					<span for="video_radio_streaminstagram"><?php _e('From Stream', 'thunderslider'); ?></span>
				</span>
				
				<input type="radio" checked id="video_radio_audio" name="video_select" style="display: none;">
			</div>
			

			<!-- Vimeo block -->		
			<div id="video_block_vimeo" class="video-select-block" style="display:none;" >
				<label><?php _e('Vimeo ID or URL', 'thunderslider'); ?></label>
				<input type="text" id="vimeo_id" value="">
				<input type="button" style="vertical-align:middle" id="button_vimeo_search" class="button-regular video_search_button" value="search">
				<span class="video_example"><?php _e('example: 30300114', 'thunderslider'); ?></span>		
				<img id="vimeo_loader" src="<?php echo RS_PLUGIN_URL; ?>/admin/assets/images/loader.gif" style="display:none">
			</div>
			
			<!-- Youtube block -->		
			<div id="video_block_youtube" class="video-select-block">
				<label><?php _e('YouTube ID or URL', 'thunderslider'); ?></label>
				<input type="text" id="youtube_id" value="">
				<input type="button" style="vertical-align:middle" id="button_youtube_search" class="button-regular video_search_button" value="search">
				<span class="video_example"><?php _e('example', 'thunderslider'); ?>: <?php echo ThunderSliderGlobals::YOUTUBE_EXAMPLE_ID; ?></span>
				<img id="youtube_loader" src="<?php echo RS_PLUGIN_URL; ?>/admin/assets/images/loader.gif" style="display:none">
			</div>
			
			<!-- Html 5 block -->		
			<div id="video_block_html5" class="video-select-block" style="display:none;">
				<label><?php _e('Poster Image Url', 'thunderslider'); ?></label>
				<input style="width:330px" type="text" id="html5_url_poster" name="html5_url_poster" value="">
				<span class="imgsrcchanger-div" style="margin-left:20px;">
					<a href="javascript:void(0)" class="button-image-select-html5-video button-primary revblue" ><?php _e('Choose from Library', 'thunderslider'); ?></a>
				</span>
				<span class="video_example">&nbsp;</span>
				
		
				<label><?php _e('Video MP4 Url', 'thunderslider'); ?></label>
				<input style="width:330px" type="text" id="html5_url_mp4" name="html5_url_mp4" value="">
				<span class="vidsrcchanger-div" style="margin-left:20px;">
					<a href="javascript:void(0)" data-inptarget="html5_url_mp4" class="button_change_video button-primary revblue" ><?php _e('Choose from Library', 'thunderslider'); ?></a>
				</span>
				<span class="video_example"><?php _e("example",'thunderslider'); ?>: http://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4</span>
		
				<label><?php _e('Video WEBM Url', 'thunderslider'); ?></label>
				<input style="width:330px" type="text" id="html5_url_webm" name="html5_url_webm" value="">
				<span class="vidsrcchanger-div" style="margin-left:20px;">
					<a href="javascript:void(0)" data-inptarget="html5_url_webm" class="button_change_video button-primary revblue" ><?php _e('Choose from Library', 'thunderslider'); ?></a>
				</span>
				<span class="video_example"><?php _e('example','thunderslider'); ?>: http://clips.vorwaerts-gmbh.de/big_buck_bunny.webm</span>
		
				<label><?php _e('Video OGV Url', 'thunderslider'); ?></label>
				<input style="width:330px" type="text" id="html5_url_ogv" name="html5_url_ogv" value="">
				<span class="vidsrcchanger-div" style="margin-left:20px;">
					<a href="javascript:void(0)" data-inptarget="html5_url_ogv" class="button_change_video button-primary revblue" ><?php _e('Choose from Library', 'thunderslider'); ?></a>
				</span>
				<span class="video_example"><?php _e('example', 'thunderslider'); ?>: http://clips.vorwaerts-gmbh.de/big_buck_bunny.ogv</span>
				
			</div>
			
			<div id="video_block_audio" class="video-select-block" style="display:none;" >
				<label><?php _e('Audio Url', 'thunderslider'); ?></label>
				<input style="width:330px" type="text" id="html5_url_audio" name="html5_url_audio" value="">
				<span class="vidsrcchanger-div" style="margin-left:20px;">
					<a href="javascript:void(0)" data-inptarget="html5_url_audio" class="button_change_video button-primary revblue" ><?php _e('Choose from Library', 'thunderslider'); ?></a>
				</span>
			</div>
		</div>


		<div id="rs-video-size"  style="display:none">
			<!-- Video Sizing -->
			<div id="video_size_wrapper" class="youtube-inputs-wrapper">
				<label for="input_video_fullwidth"><?php _e('Full Screen:', 'thunderslider'); ?></label>	
				<input type="checkbox" class="tp-moderncheckbox rs-staticcustomstylechange tipsy_enabled_top" id="input_video_fullwidth">
				<div class="clearfix mb10"></div>
			</div>
			
			<label for="input_video_cover" class="video-label"><?php _e('Force Cover:', 'thunderslider'); ?></label>
			<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox mb10" id="input_video_cover">
			
			<div id="fullscreenvideofun1" class="video-settings-line mb10">
				<label for="input_video_dotted_overlay" class="video-label" id="input_video_dotted_overlay_lbl">
					<?php _e('Dotted Overlay:', 'thunderslider'); ?>
				</label>				
				<select id="input_video_dotted_overlay" style="width:100px">
					<option value="none"><?php _e('none','thunderslider'); ?></option>
					<option value="twoxtwo"><?php _e('2 x 2 Black','thunderslider'); ?></option>
					<option value="twoxtwowhite"><?php _e('2 x 2 White','thunderslider'); ?></option>
					<option value="threexthree"><?php _e('3 x 3 Black','thunderslider'); ?></option>
					<option value="threexthreewhite"><?php _e('3 x 3 White','thunderslider'); ?></option>
				</select>
				<div class="clearfix mb10"></div>
				<label for="input_video_ratio" class="video-label" id="input_video_ratio_lbl">
					<?php _e('Aspect Ratio:', 'thunderslider'); ?>
				</label>				
				<select id="input_video_ratio" style="width:100px">
					<option value="16:9"><?php _e('16:9','thunderslider'); ?></option>
					<option value="4:3"><?php _e('4:3','thunderslider'); ?></option>
				</select>
			</div>
			<div id="video_full_screen_settings" class="video-settings-line">
				<div class="mb10">
					<label for="input_video_leave_fs_on_pause"><?php _e('Leave Full Screen on Pause/End:', 'thunderslider'); ?></label>
					<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_leave_fs_on_pause">
				</div>
			</div>
		</div>
		
		<div id="rs-video-settings" style="display:none">
			<div class="mb10">
				<label for="input_video_loop"><?php _e("Loop:",'thunderslider'); ?></label>
				<?php /* <input type="checkbox" class="checkbox_video_dialog  mtop_13" id="input_video_loop" > */ ?>
				<select id="input_video_loop" style="width: 200px;">
					<option value="none"><?php _e('Disable', 'thunderslider'); ?></option>
					<option class="rs-hide-on-audio" value="loop"><?php _e('Loop, Slide is paused', 'thunderslider'); ?></option>
					<option class="rs-hide-on-audio" value="loopandnoslidestop"><?php _e('Loop, Slide does not stop', 'thunderslider'); ?></option>
					<option class="rs-show-on-audio" value="loopandnoslidestop"><?php _e('Loop Segment', 'thunderslider'); ?></option>
				</select>
			</div>

			<div class="mb10">
				<label for="input_video_autoplay"><?php _e('Autoplay:', 'thunderslider'); ?></label>
				<select id="select_video_autoplay">
					<option value="false"><?php _e('Off', 'thunderslider'); ?></option>
					<option value="true"><?php _e('On', 'thunderslider'); ?></option>
					<option value="1sttime"><?php _e('On 1st Time', 'thunderslider'); ?></option>
					<option value="no1sttime"><?php _e('Not on 1st Time', 'thunderslider'); ?></option>
				</select>
			</div>

			<div class="mb10">
				<label for="input_video_stopallvideo"><?php _e('Stop Other Media:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_stopallvideo" >
			</div>

			<div class="mb10 hide-for-vimeo rs-hide-on-audio">
				<label for="input_video_allowfullscreen"><?php _e('Allow FullScreen:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_allowfullscreen" >
			</div>

			<div class="mb10">	
				<label for="input_video_nextslide"><?php _e('Next Slide On End:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_nextslide" >
			</div>

			<div class="mb10">
				<label for="input_video_force_rewind"><?php _e('Rewind at Slide Start:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_force_rewind" >
			</div>

			<div class="mb10">
				<label for="input_video_control"><?php _e('Hide Controls:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_control" >
				<span style="vertical-align:middle; margin-left:15px; display:none" class="description hidecontroldepend"><?php _e('Layer Action may needed to start/stop Video', 'thunderslider'); ?></span>
			</div>

			<script>
				jQuery('#input_video_control').on('change',function() {
					if (jQuery(this).attr('checked')==="checked") 
						jQuery('.hidecontroldepend').show();
					else
						jQuery('.hidecontroldepend').hide();
				})
			</script>

			<div class="mb10 rs-hide-on-audio">
				<label for="input_video_mute"><?php _e('Mute:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_mute" >
			</div>

			<div class="mb10 video-volume">
				<label for="input_video_volume"><?php _e('Volume (0 - 100):', 'thunderslider'); ?></label>
				<input type="text" class="input_video_dialog" style="width: 50px;" id="input_video_volume" >
			</div>
			
			<div class="mb10">
				<span class="rs-hide-on-audio"><label for="input_video_start_at"><?php _e('Start at:', 'thunderslider'); ?></label></span>
				<span class="rs-show-on-audio"><label for="input_video_start_at"><?php _e('Segment Start:', 'thunderslider'); ?></label></span>
				<input type="text" id="input_video_start_at" style="width: 50px;"> <?php _e('i.e.: 0:17', 'thunderslider'); ?>
			</div>
			
			<div class="mb10">
				<span class="rs-hide-on-audio"><label for="input_video_end_at"><?php _e('End at:', 'thunderslider'); ?></label></span>
				<span class="rs-show-on-audio"><label for="input_video_end_at"><?php _e('Segment End:', 'thunderslider'); ?></label></span>
				<input type="text" id="input_video_end_at" style="width: 50px;"> <?php _e('i.e.: 2:41', 'thunderslider'); ?>
			</div>
			
			<div class="mb10 rs-hide-on-audio">
				<label for="input_video_show_cover_pause"><?php _e('Show Cover at Pause:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_show_cover_pause" >
			</div>
			
			<div class="mb10 rs-show-on-audio">
				<label for="input_video_show_visibility"><?php _e('Invisible on Frontend:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_show_visibility" >
			</div>
			
			<div id="rev-youtube-options" class="video-settings-line mb10 rs-hide-on-audio">
				<div class="mb10">
					<label for="input_video_speed"><?php _e('Video Speed:', 'thunderslider'); ?></label>
					<select id="input_video_speed" style="width:75px">
						<option value="0.25"><?php _e('0.25', 'thunderslider'); ?></option>
						<option value="0.50"><?php _e('0.50', 'thunderslider'); ?></option>
						<option value="1"><?php _e('1', 'thunderslider'); ?></option>
						<option value="1.5"><?php _e('1.5', 'thunderslider'); ?></option>
						<option value="2"><?php _e('2', 'thunderslider'); ?></option>
					</select>
				</div>
			</div>
			
			<!-- JASON -->
			<div id="rev-playsinline-options" class="video-settings-line mb10 rs-hide-on-audio">
				<div class="mb10">
					<label for="input_video_play_inline"><?php _e('Play Video Inline:', 'thunderslider'); ?></label>
					<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_play_inline" >
				</div>
			</div>
			
			<div class="mb10 rs-show-on-audio" style="display: none">
				<div class="mb10">
					<label for="input_audio_preload" class="video-label">
						<?php _e("Audio Preload:",'thunderslider')?>
					</label>
					<select id="input_audio_preload" style="width:200px">
						<option value="none"><?php _e('Disable', 'thunderslider'); ?></option>
						<option value="metadata"><?php _e('Metadata', 'thunderslider'); ?></option>
						<option value="progress"><?php _e('Progress', 'thunderslider'); ?></option>
						<option value="canplay"><?php _e('Can Play', 'thunderslider'); ?></option>
						<option value="canplaythrough"><?php _e('Can Play Through', 'thunderslider'); ?></option>
					</select>
				</div>
				<div class="mb10">
					<label for="input_audio_preload" class="video-label">
						<?php _e("Ignore Preload after ",'thunderslider'); ?>
					</label>
					<select id="input_video_preload_wait">
						<option value="0"><?php _e('0', 'thunderslider'); ?></option>
						<option value="1"><?php _e('1', 'thunderslider'); ?></option>
						<option value="2"><?php _e('2', 'thunderslider'); ?></option>
						<option value="3"><?php _e('3', 'thunderslider'); ?></option>
						<option value="4"><?php _e('4', 'thunderslider'); ?></option>
						<option value="5"><?php _e('5', 'thunderslider'); ?></option>
						<option value="6"><?php _e('6', 'thunderslider'); ?></option>
						<option value="7"><?php _e('7', 'thunderslider'); ?></option>
						<option value="8"><?php _e('8', 'thunderslider'); ?></option>
						<option value="9"><?php _e('9', 'thunderslider'); ?></option>
						<option value="10"><?php _e('10', 'thunderslider'); ?></option>
					</select><?php _e(" seconds",'thunderslider'); ?>
				</div>
			</div>
			
			<div id="rev-html5-options" style="display: none; mb10">
			
				<div class="mb10">
					<label for="input_video_preload" class="video-label">
						<?php _e("Video Preload:",'thunderslider')?>
					</label>
					<select id="input_video_preload" style="width:200px">
						<option value="auto"><?php _e('Auto', 'thunderslider'); ?></option>
						<option value="none"><?php _e('Disable', 'thunderslider'); ?></option>
						<option value="metadata"><?php _e('Metadata', 'thunderslider'); ?></option>
					</select>
				</div>
				
				<div class="mb10">
					<label for="input_video_large_controls"><?php _e('Large Controls:', 'thunderslider'); ?></label>
					<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_video_large_controls" >
				</div>
			</div>		
		</div>

		<div id="rs-video-thumbnails" style="display:none">
			<div id="preview-image-video-wrap" class="mb10">
				<label><?php _e('Poster Image', 'thunderslider'); ?></label>
				<input type="text" class="checkbox_video_dialog " id="input_video_preview">
				<input type="button" id="" class="button-image-select-video button-primary revblue" value="<?php _e('Image Library', 'thunderslider'); ?>">
				<input type="button" id="" class="button-image-select-video-default button-primary revblue" value="<?php _e('Video Thumbnail', 'thunderslider'); ?>">
				<input type="button" id="" class="button-image-remove-video button-primary revblue" value="<?php _e('Remove', 'thunderslider'); ?>">
				<div class="clear"></div>			
			</div>

			<div class="mb10">
				<label for="input_disable_on_mobile"><?php _e('Disable Video and Show<br>only Poster on Mobile:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_disable_on_mobile" >
			</div>

			<div class="mb10">
				<label for="input_use_poster_on_mobile"><?php _e('No Poster on Mobile:', 'thunderslider'); ?></label>
				<input type="checkbox" class="checkbox_video_dialog tp-moderncheckbox" id="input_use_poster_on_mobile" >
				<div style="width:100%;height:10px"></div>
			</div>
			<!--div class="mb10">
				<select id="select_mobile_setting">
					<option value=""><?php _e('Show Video and Show Poster', 'thunderslider'); ?></option>posteronlyonmobile="off" && disablevideoonmobile="off"
					<option value=""><?php _e('Show Video and Hide Poster', 'thunderslider'); ?></option>
					<option value="use_poster_on_mobile"><?php _e('Hide Video and Show Poster', 'thunderslider'); ?></option>
					<option value="disable_on_mobile"><?php _e('Hide Video and Hide Poster', 'thunderslider'); ?></option>
				</select>
			</div-->
			
		</div>

		<div id="rs-video-arguments" style="display:none">
			<div>
				<label><?php _e('Arguments:', 'thunderslider'); ?></label>
				<input type="text" id="input_video_arguments" style="width:350px;" value="" data-youtube="<?php echo ThunderSliderGlobals::DEFAULT_YOUTUBE_ARGUMENTS; ?>" data-vimeo="<?php echo ThunderSliderGlobals::DEFAULT_VIMEO_ARGUMENTS; ?>" >
			</div>
		</div>
		
		<div class="add-button-wrapper" style="margin-left:25px;">
			<a href="javascript:void(0)" class="button-primary revblue" id="button-video-add" data-textadd="<?php _e('Add This Video', 'thunderslider'); ?>" data-textupdate="<?php _e('Update Video', 'thunderslider'); ?>" ><?php _e('Add This Video', 'thunderslider'); ?></a>
			<a href="javascript:void(0)" class="button-primary revblue" style="display: none;" id="button-audio-add" data-textadd="<?php _e('Add This Audio', 'thunderslider'); ?>" data-textupdate="<?php _e('Update Audio', 'thunderslider'); ?>" ><?php _e('Add This Audio', 'thunderslider'); ?></a>
		</div>
	</form>
</div>