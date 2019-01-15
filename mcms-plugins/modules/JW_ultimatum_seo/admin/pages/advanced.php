<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$active_tab = filter_input( INPUT_GET, 'tab' );

$tabs = new MCMSSEO_Option_Tabs( 'advanced', 'breadcrumbs' );
$tabs->add_tab(
	new MCMSSEO_Option_Tab(
		'breadcrumbs',
		__( 'Breadcrumbs', 'mandarincms-seo' ),
		array(
			'video_url' => 'https://jiiworks.net/screencast-breadcrumbs',
			'opt_group' => 'mcmsseo_internallinks',
		)
	)
);
$tabs->add_tab(
	new MCMSSEO_Option_Tab(
		'permalinks',
		__( 'Permalinks', 'mandarincms-seo' ),
		array(
			'video_url' => 'https://jiiworks.net/screencast-permalinks',
			'opt_group' => 'mcmsseo_permalinks',
		)
	)
);
$tabs->add_tab(
	new MCMSSEO_Option_Tab(
		'rss',
		__( 'RSS', 'mandarincms-seo' ),
		array(
			'video_url' => 'https://jiiworks.net/screencast-rss',
			'opt_group' => 'mcmsseo_rss',
		)
	)
);

$active_tab = $tabs->get_active_tab();
Ultimatum_Form::get_instance()->admin_header( true, $active_tab->get_opt_group() );

echo '<h2 class="nav-tab-wrapper">';
foreach ( $tabs->get_tabs() as $tab ) {
	$active = ( $tabs->is_active_tab( $tab ) ) ? ' nav-tab-active' : '';
	echo '<a class="nav-tab' . $active . '" id="' . $tab->get_name() . '-tab" href="' . admin_url( 'admin.php?page=mcmsseo_advanced&tab=' . $tab->get_name() ) . '">' . $tab->get_label() . '</a>';
}
echo '</h2>';

$help_center = new MCMSSEO_Help_Center( 'advanced', $active_tab );
$help_center->output_help_center();

require_once MCMSSEO_PATH . 'admin/views/tabs/advanced/' . $active_tab->get_name() . '.php';

Ultimatum_Form::get_instance()->admin_footer();
