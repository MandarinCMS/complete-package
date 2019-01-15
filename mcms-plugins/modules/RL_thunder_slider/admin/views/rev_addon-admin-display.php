<?php

/**
 * Provide a admin area view for the module
 *
 * This file is used to markup the admin-facing aspects of the module.
 *
 * @link       http://www.jiiworks.net
 * @since      1.0.0
 *
 * @package    Rev_addon_gal
 * @subpackage Rev_addon_gal/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="viewWrapper" class="view_wrapper">
	<div class='wrap'>
		<div class="clear_both"></div>
		<div class="title_line" style="margin-bottom:10px">
			<?php 
				$icon_general = '<div class="icon32" id="icon-options-general"></div>';
				echo apply_filters( 'rev_icon_general_filter', $icon_general ); 
			?>
		</div>
		
		<div class="title_line sub_title">
			<div id="icon-options-configure" class="icon32"></div> 
			<span><?php _e("Install &amp; Configure Add-ons", 'thunderslider'); ?><a href="?page=rev_addon&amp;checkforupdates=true" class="rs-reload-shop"><i class="eg-icon-arrows-ccw"></i><?php _e("Check for new Add-ons", 'thunderslider'); ?></a></span>
		</div>


		<div class="clear_both"></div>

	<div style="width:100%;height:40px"></div>
		<span id="ajax_thunder_slider_addon_nonce" class="hidden"><?php echo mcms_create_nonce( 'ajax_thunder_slider_addon_nonce' ) ?></span>
		<div class="rs-dashboard rs-dash-addons">
		<?php 
			//load $addons from repository
			$addons = get_option('thunderslider-addons');

			$addons = (array)$addons;
			$addons = apply_filters( 'rev_addons_filter', $addons );

			$modules = get_modules();

			foreach($addons as $addon){
				if(version_compare(ThunderSliderGlobals::SLIDER_REVISION, $addon->version_from, '<') || version_compare(ThunderSliderGlobals::SLIDER_REVISION, $addon->version_to, '>')){
					continue;
				}
				if( empty($addon->title) ) continue;
				
				$rs_dash_background_style = !empty($addon->background) ? 'style="background-image: url('.$addon->background.');"' : "";
				?>
				<!-- <?php echo $addon->slug; ?> WIDGET -->
					<div class="rs-dash-widget <?php echo $addon->slug; ?>" <?php echo $rs_dash_background_style; ?>>
						<div class="rs-dash-title-wrap">
							<div class="rs-dash-title"><?php echo $addon->title; ?></div>
							<?php 
								//Module Status
								$rs_addon_not_activated = $rs_addon_activated = $rs_addon_not_installed = 'style="display:none"';
								$rev_addon_version = "";
								if (array_key_exists($addon->slug.'/'.$addon->slug.'.php', $modules)) {
									if (is_module_inactive($addon->slug.'/'.$addon->slug.'.php')) {
										$rs_addon_not_activated = 'style="display:block"'; 									
									} else {
										$rs_addon_activated = 'style="display:block"';
									}
									$rev_addon_version = $modules[$addon->slug.'/'.$addon->slug.'.php']['Version'];
								} else { 
									$rs_addon_not_installed = 'style="display:block"';
								}
							
								//Check for registered slider
								$rev_addon_validated = get_option('thunderslider-valid', 'false');
								$rev_addon_validated = $rev_addon_validated=='true' ? true : false;

								if($rev_addon_validated){
							?>
									<div class="rs-dash-title-button rs-status-orange" <?php echo $rs_addon_not_activated; ?> data-module="<?php echo $addon->slug.'/'.$addon->slug.'.php';?>" data-alternative="<i class='icon-no-problem-found'></i>Activate"><i class="icon-update-refresh"></i><?php _e("Not Active", 'thunderslider'); ?></div>
									<div class="rs-dash-button-gray rs-dash-deactivate-addon rs-dash-title-button" <?php echo $rs_addon_activated; ?> data-module="<?php echo $addon->slug.'/'.$addon->slug.'.php';?>" data-alternative="<i class='icon-update-refresh'></i>Deactivate"><i class="icon-update-refresh"></i><?php _e("Deactivate", 'thunderslider'); ?></div>
									<div class=" rs-dash-title-button rs-status-green" <?php echo $rs_addon_activated; ?> data-module="<?php echo $addon->slug.'/'.$addon->slug.'.php';?>" data-alternative="<i class='icon-update-refresh'></i>Deactivate"><i class="icon-no-problem-found"></i><?php _e("Active", 'thunderslider'); ?></div>
									<div class=" rs-dash-title-button rs-status-red" <?php echo $rs_addon_not_installed; ?> data-alternative="<i class='icon-update-refresh'></i>Install" data-module="<?php echo $addon->slug;?>"><i class="icon-not-registered"></i><?php _e("Not Installed", 'thunderslider'); ?></div>
							<?php } else { 
									$rev_addon_version="";
									$result = deactivate_modules( $addon->slug.'/'.$addon->slug.'.php' );
							?>
									<div class="rs-dash-title-button rs-status-red" style="display:block"><i class="icon-not-registered"></i><?php _e("Add-on locked", 'thunderslider'); ?></div>
							<?php }
							?>
						</div>
						<div class="rs-dash-widget-inner rs-dash-widget-registered">
							
							<div class="rs-dash-content">
								<div class="rs-dash-strong-content"><?php echo $addon->line_1; ?></div>
								<div><?php echo $addon->line_2; ?></div>				
							</div>
							<div class="rs-dash-content-space"></div>
							<?php if(!empty($rev_addon_version)){ ?>
								<div class="rs-dash-version-info">
									<div class="rs-dash-strong-content ">
										<?php _e('Installed Version','thunderslider'); ?>
									</div>
									<?php 
										//$rev_addon_version = strtoupper($addon->slug."_VERSION"); 
										echo $rev_addon_version;
										$rev_addon = "";
									?>
								</div>
							<?php } ?>
							<div class="rs-dash-version-info">
								<div class="rs-dash-strong-content rs-dash-version-info">
									<?php _e('Available Version','thunderslider'); ?>
								</div>
								<?php echo $addon->available; ?>
							</div>
							<?php if(!empty($rev_addon_version)){ ?>
							<div class="rs-dash-content-space"></div>	
							<a class="rs-dash-invers-button" href="?page=rev_addon&amp;checkforupdates=true" id="rev_check_version"><?php _e('Check for Update','thunderslider'); ?></a>
							<div class="rs-dash-content-space"></div>
							<?php } ?>
							<div class="rs-dash-bottom-wrapper">
								<?php if(!empty($rev_addon_version)){ ?>
									<?php 
										if( version_compare($rev_addon_version, $addon->available) >= 0 ){ ?>
											<span class="rs-dash-button-gray"><?php _e('Up to date','thunderslider'); ?></span>							
									<?php
										} else { ?>
										    <a href="update-core.php?checkforupdates=true" class="rs-dash-button"><?php _e('Update Now', 'thunderslider'); ?></a>							
									<?php	
										}
									?>
								<?php } else { 
										if($rev_addon_validated){?>
										<span data-module="<?php echo $addon->slug;?>" class="rs-addon-not-installed rs-dash-button"><?php _e('Install this Add-on', 'thunderslider'); ?></span>
								<?php 
										} else { ?>
											<a href="<?php echo admin_url( 'admin.php?page=thunderslider');?>" class="rs-dash-button"><?php _e('Register RazorLeaf ThunderSlider', 'thunderslider'); ?></a>
									<?php 
										}
									} ?>
									
								<?php if(!empty($addon->button) && $rev_addon_validated && !empty($rev_addon_version) ){  // && !empty($rev_addon_code)
										if($rs_addon_activated=='style="display:block"'){
								?>		
											<span <?php echo $rs_addon_activated=='style="display:none"' ? $rs_addon_activated : ''; ?> href="javascript:void(0)" class="rs-dash-button rs-dash-action-button rs-dash-margin-left-10" id="rs-dash-addons-slide-out-trigger_<?php echo $addon->slug; ?>"><?php echo $addon->button; ?></span>				
								<?php 	} else {?>
											<span data-module="<?php echo $addon->slug.'/'.$addon->slug.'.php';?>" class="rs-addon-not-activated rs-dash-button rs-dash-action-button rs-dash-margin-left-10" id="rs-dash-addons-slide-out-trigger_<?php echo $addon->slug; ?>"><?php _e('Activate Module','thunderslider'); ?></span>
								<?php 	}  
									} ?>
							</div>
						</div>		
						
					</div>
				<!-- END OF <?php echo $addon->slug; ?> WIDGET -->
				<?php
			} // end foreach
		?>

			<div class="tp-clearfix"></div>
		<!--/div>
	</div>
</div-->		
<!-- SOURCE SLIDE OUT SETTINGS -->
<?php apply_filters( 'rev_addon_dash_slideouts',''); ?>
<!--End Add-On Area-->
</div> </div>
<div id="waitaminute">
	<div class="waitaminute-message"><i class="eg-icon-emo-coffee"></i><br><?php _e("Please Wait...", 'thunderslider'); ?></div>
</div>