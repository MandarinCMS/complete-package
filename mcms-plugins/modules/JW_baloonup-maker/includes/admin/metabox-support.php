<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * BaloonUp Maker Support Metabox
 *
 * Extensions (as well as the core module) can add items to the baloonup support
 * metabox via the `balooncreate_support_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_support_meta_box() { ?>
	<div id="balooncreate_support_fields" class="balooncreate_meta_table_wrap">
	<?php do_action( 'balooncreate_support_meta_box_fields' ); ?>
	</div><?php
}


add_action( 'balooncreate_support_meta_box_fields', 'balooncreate_support_meta_box_links', 10 );
function balooncreate_support_meta_box_links() {
	global $pagenow;

	$source = $pagenow;

	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], balooncreate_get_settings_tabs() ) ? $_GET['tab'] : null;

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'pum-settings' ) {
		$source = 'module-settings-page' . ( ! empty( $active_tab ) ? '-' . $active_tab . '-tab' : '' );
	} elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'pum-tools' ) {
		$source = 'module-tools-page' . ( ! empty( $active_tab ) ? '-' . $active_tab . '-tab' : '' );
	}
	?>
	<ul class="balooncreate-support-links">
		<li>
			<a href="https://docs.mcmsbaloonupmaker.com/?utm_medium=support-sidebar&utm_campaign=ContextualHelp&utm_source=<?php echo $source; ?>&utm_content=documentation">
				<img src="<?php echo POPMAKE_URL; ?>/assets/images/support-pane-docs-icon.png" />
				<span><?php _e( 'Documentation', 'baloonup-maker' ); ?></span>
			</a>
		</li>
		<li>
			<a href="https://mandarincms.org/support/module/baloonup-maker">
				<img src="<?php echo POPMAKE_URL; ?>/assets/images/support-pane-mcmsforums-icon.png" />
				<span><?php _e( 'Free Support Forums', 'baloonup-maker' ); ?></span>
			</a>
		</li>
		<li>
			<a href="https://mcmsbaloonupmaker.com/support/?utm_medium=support-sidebar&utm_campaign=ContextualHelp&utm_source=<?php echo $source; ?>&utm_content=extension-support">
				<img src="<?php echo POPMAKE_URL; ?>/assets/images/support-pane-extensions-icon.png" />
				<span><?php _e( 'Extension Support', 'baloonup-maker' ); ?></span>
			</a>
		</li>
	</ul>
	<?php
}
