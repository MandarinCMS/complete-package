<?php
//about myskin info
add_action( 'admin_menu', 'pizza_lite_aboutmyskin' );
function pizza_lite_aboutmyskin() {    	
	add_myskin_page( esc_html__('About MySkin', 'pizza-lite'), esc_html__('About MySkin', 'pizza-lite'), 'edit_myskin_options', 'pizza_lite_guide', 'pizza_lite_mostrar_guide');   
} 
//guidline for about myskin
function pizza_lite_mostrar_guide() { 
	//custom function about myskin customizer
	$return = add_query_arg( array()) ;
?>
<div class="wrapper-info">
	<div class="col-left">
   		   <div class="col-left-area">
			  <?php esc_attr_e('MySkin Information', 'pizza-lite'); ?>
		   </div>
          <p><?php esc_attr_e('Pizza Lite is suitable for pizza home delivery, online ordering, eCommerce, eatery, fast food, restaurant, bistro, cafe, coffee shop, bar, pub, diner, joint, outlet, dining room, cafeteria, food, recipe, chef, grill, hideaway, eating place, caterers, hotdog, takeaway, night club and other types of websites which are for local and small business and need responsive site which is multilingual module compatible like MCMSML, qTranslate X and Polylang and also is page builder friendly with elementor, divi, visual composer, beaver builder, live composer and others. WooCommerce compatible and can be used to book a table and book food online as well.','pizza-lite'); ?></p>
		  <a href="<?php echo esc_url(PIZZA_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/free-vs-pro.png" alt="" /></a>
	</div><!-- .col-left -->
	<div class="col-right">			
			<div class="centerbold">
				<hr />
				<a href="<?php echo esc_url(PIZZA_LITE_SKTTHEMES_LIVE_DEMO); ?>" target="_blank"><?php esc_attr_e('Live Demo', 'pizza-lite'); ?></a> | 
				<a href="<?php echo esc_url(PIZZA_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><?php esc_attr_e('Buy Pro', 'pizza-lite'); ?></a> | 
				<a href="<?php echo esc_url(PIZZA_LITE_SKTTHEMES_THEME_DOC); ?>" target="_blank"><?php esc_attr_e('Documentation', 'pizza-lite'); ?></a>
                <div class="space5"></div>
				<hr />                
                <a href="<?php echo esc_url(PIZZA_LITE_SKTTHEMES_THEMES); ?>" target="_blank"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/sktskill.jpg" alt="" /></a>
			</div>		
	</div><!-- .col-right -->
</div><!-- .wrapper-info -->
<?php } ?>