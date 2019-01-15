<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Pizza Lite
 */
?>
<div id="sidebar">    
    <?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
        <h3 class="widget-title"><?php esc_html_e( 'Category', 'pizza-lite' ); ?></h3>
        <aside id="categories" class="widget">           
            <ul>
                <?php mcms_list_categories('title_li=');  ?>
            </ul>
        </aside>
       <h3 class="widget-title"><?php esc_html_e( 'Archives', 'pizza-lite' ); ?></h3>
        <aside id="archives" class="widget">           
            <ul>
                <?php mcms_get_archives( array( 'type' => 'monthly' ) ); ?>
            </ul>
        </aside>
         <h3 class="widget-title"><?php esc_html_e( 'Meta', 'pizza-lite' ); ?></h3>
         <aside id="meta" class="widget">           
            <ul>
                <?php mcms_register(); ?>
                <li><?php mcms_loginout(); ?></li>
                <?php mcms_meta(); ?>
            </ul>
        </aside>
    <?php endif; // end sidebar widget area ?>	
</div><!-- sidebar -->