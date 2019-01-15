<?php

if (!defined('BASED_TREE_URI')) {
	exit;
}

/***** Welcome Notice in MandarinCMS Dashboard *****/

if (!function_exists('jmd_magandanow_new_admin_notice')) {
	function jmd_magandanow_new_admin_notice() {
		global $pagenow, $jmd_magandanow_new_version;
		if (current_user_can('edit_myskin_options') && 'index.php' === $pagenow && !get_option('jmd_magandanow_new_notice_welcome') || current_user_can('edit_myskin_options') && 'myskins.php' === $pagenow && isset($_GET['activated']) && !get_option('jmd_magandanow_new_notice_welcome')) {
			mcms_enqueue_style('jmd-magandanow-new-admin-notice', get_template_directory_uri() . '/admin/admin-notice.css', array(), $jmd_magandanow_new_version);
			jmd_magandanow_new_welcome_notice();
		}
	}
}

/***** Hide Welcome Notice in MandarinCMS Dashboard *****/

if (!function_exists('jmd_magandanow_new_hide_notice')) {
	function jmd_magandanow_new_hide_notice() {
		if (isset($_GET['jmd-magandanow-new-hide-notice']) && isset($_GET['_jmd_magandanow_new_notice_nonce'])) {
			if (!mcms_verify_nonce($_GET['_jmd_magandanow_new_notice_nonce'], 'jmd_magandanow_new_hide_notices_nonce')) {
				mcms_die(esc_html__('Action failed. Please refresh the page and retry.', 'jmd-magandanow-new'));
			}
			if (!current_user_can('edit_myskin_options')) {
				mcms_die(esc_html__('You do not have the necessary permission to perform this action.', 'jmd-magandanow-new'));
			}
			$hide_notice = sanitize_text_field($_GET['jmd-magandanow-new-hide-notice']);
			update_option('jmd_magandanow_new_notice_' . $hide_notice, 1);
		}
	}
}

/***** Content of Welcome Notice in MandarinCMS Dashboard *****/

if (!function_exists('jmd_magandanow_new_welcome_notice')) {
	function jmd_magandanow_new_welcome_notice() {
		global $jmd_magandanow_new_data; ?>
		<div class="notice notice-success mh-welcome-notice">
			<a class="notice-dismiss mh-welcome-notice-hide" href="<?php echo esc_url(mcms_nonce_url(remove_query_arg(array('activated'), add_query_arg('jmd-magandanow-new-hide-notice', 'welcome')), 'jmd_magandanow_new_hide_notices_nonce', '_jmd_magandanow_new_notice_nonce')); ?>">
				<span class="screen-reader-text">
					<?php echo esc_html__('Dismiss this notice.', 'jmd-magandanow-new'); ?>
				</span>
			</a>
			<p><?php printf(esc_html__('Thanks for using %1$s! To get started please make sure you visit our %2$swelcome page%3$s.', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name'], '<a href="' . esc_url(admin_url('myskins.php?page=magandanews')) . '">', '</a>'); ?></p>
			<p class="mh-welcome-notice-button">
				<a class="button-secondary" href="<?php echo esc_url(admin_url('myskins.php?page=magandanews')); ?>">
					<?php printf(esc_html__('Get Started with %s', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name']); ?>
				</a>
				<a class="button-primary" href="<?php echo esc_url('https://www.mhmyskins.com/myskins/mh/magandanews/'); ?>" target="_blank">
					<?php esc_html_e('Upgrade to JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
				</a>
			</p>
		</div><?php
	}
}

/***** MySkin Info Page *****/

if (!function_exists('jmd_magandanow_new_myskin_info_page')) {
	function jmd_magandanow_new_myskin_info_page() {
		add_myskin_page(esc_html__('Welcome to JMD MagandaNow lite', 'jmd-magandanow-new'), esc_html__('MySkin Info', 'jmd-magandanow-new'), 'edit_myskin_options', 'magandanews', 'jmd_magandanow_new_display_myskin_page');
	}
}

if (!function_exists('jmd_magandanow_new_display_myskin_page')) {
	function jmd_magandanow_new_display_myskin_page() {
		global $jmd_magandanow_new_data; ?>
		<div class="myskin-info-wrap">
			<h1>
				<?php printf(esc_html__('Welcome to %1$1s %2$2s', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name'], $jmd_magandanow_new_data['Version']); ?>
			</h1>
			<div class="mh-row myskin-intro mh-clearfix">
				<div class="mh-col-1-4">
					<img class="myskin-screenshot" src="<?php echo get_template_directory_uri(); ?>/screenshot.png" alt="<?php esc_html_e('MySkin Screenshot', 'jmd-magandanow-new'); ?>" />
				</div>
				<div class="mh-col-3-4 myskin-description">
					<?php echo esc_html($jmd_magandanow_new_data['Description']); ?>
				</div>
			</div>
			<hr>
			<div class="myskin-links mh-clearfix">
				<p>
					<strong><?php esc_html_e('Important Links:', 'jmd-magandanow-new'); ?></strong>
					<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/mh/magandanews-lite/'); ?>" target="_blank">
						<?php esc_html_e('MySkin Info Page', 'jmd-magandanow-new'); ?>
					</a>
					<a href="<?php echo esc_url('https://www.mhmyskins.com/support/'); ?>" target="_blank">
						<?php esc_html_e('Support Center', 'jmd-magandanow-new'); ?>
					</a>
					<a href="<?php echo esc_url('https://mandarincms.org/support/myskin/jmd-magandanow-new'); ?>" target="_blank">
						<?php esc_html_e('Support Forum', 'jmd-magandanow-new'); ?>
					</a>
					<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/showcase/'); ?>" target="_blank">
						<?php esc_html_e('MH MySkins Showcase', 'jmd-magandanow-new'); ?>
					</a>
				</p>
			</div>
			<hr>
			<div id="getting-started">
				<h3>
					<?php printf(esc_html__('Get Started with %s', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name']); ?>
				</h3>
				<div class="mh-row mh-clearfix">
					<div class="mh-col-1-2">
						<div class="section">
							<h4>
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<?php esc_html_e('MySkin Documentation', 'jmd-magandanow-new'); ?>
							</h4>
							<p class="about">
								<?php printf(esc_html__('Need any help with configuring %s? The documentation for this myskin includes all myskin related information that is needed to get your site up and running in no time. In case you have any additional questions, feel free to reach out in the myskin support forums on MandarinCMS.org.', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name']); ?>
							</p>
							<p>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/support/documentation-mh-magandanews/'); ?>" target="_blank" class="button button-secondary">
									<?php esc_html_e('MySkin Documentation', 'jmd-magandanow-new'); ?>
								</a>
								<a href="<?php echo esc_url('https://mandarincms.org/support/myskin/jmd-magandanow-new'); ?>" target="_blank" class="button button-secondary">
									<?php esc_html_e('Support Forum', 'jmd-magandanow-new'); ?>
								</a>
							</p>
						</div>
						<div class="section">
							<h4>
								<span class="dashicons dashicons-admin-appearance"></span>
								<?php esc_html_e('MySkin Options', 'jmd-magandanow-new'); ?>
							</h4>
							<p class="about">
								<?php printf(esc_html__('%s supports the MySkin Customizer for all myskin settings. Click "Customize MySkin" to open the Customizer now.',  'jmd-magandanow-new'), $jmd_magandanow_new_data['Name']); ?>
							</p>
							<p>
								<a href="<?php echo admin_url('customize.php'); ?>" class="button button-secondary">
									<?php esc_html_e('Customize MySkin', 'jmd-magandanow-new'); ?>
								</a>
							</p>
						</div>
					</div>
					<div class="mh-col-1-2">
						<div class="section">
							<h4>
								<span class="dashicons dashicons-cart"></span>
								<?php esc_html_e('JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
							</h4>
							<p class="about">
								<?php esc_html_e('If you like the free version of this myskin, you will LOVE the full version of JMD MagandaNow which includes unique custom widgets, additional features and more useful options to customize your website.', 'jmd-magandanow-new'); ?>
							</p>
							<p>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/mh/magandanews/'); ?>" target="_blank" class="button button-primary">
									<?php esc_html_e('Upgrade to JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
								</a>
							</p>
						</div>
						<div class="section">
							<h4>
								<span class="dashicons dashicons-images-alt"></span>
								<?php esc_html_e('JMD MagandaNow MySkin Demos', 'jmd-magandanow-new'); ?>
							</h4>
							<p class="about">
								<?php esc_html_e('The premium version of JMD MagandaNow includes lots of additional features and options to customize your website. We have created several myskin demos as examples in order to show what is possible with this flexible magandanews myskin.', 'jmd-magandanow-new'); ?>
							</p>
							<p>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/mh/magandanews/#demos'); ?>" target="_blank" class="button button-secondary">
									<?php esc_html_e('MySkin Demos', 'jmd-magandanow-new'); ?>
								</a>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/showcase/'); ?>" target="_blank" class="button button-secondary">
									<?php esc_html_e('MH MySkins Showcase', 'jmd-magandanow-new'); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
			<hr>
			<div class="myskin-comparison">
				<h3 class="myskin-comparison-intro">
					<?php esc_html_e('Upgrade to JMD MagandaNow for more awesome features:', 'jmd-magandanow-new'); ?>
				</h3>
				<table>
					<thead class="myskin-comparison-header">
						<tr>
							<th class="table-feature-title"><h3><?php esc_html_e('Features', 'jmd-magandanow-new'); ?></h3></th>
							<th><h3><?php esc_html_e('JMD MagandaNow lite', 'jmd-magandanow-new'); ?></h3></th>
							<th><h3><?php esc_html_e('JMD MagandaNow', 'jmd-magandanow-new'); ?></h3></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><h3><?php esc_html_e('MySkin Price', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('Free', 'jmd-magandanow-new'); ?></td>
							<td>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/pricing/#join'); ?>" target="_blank">
									<?php esc_html_e('View Pricing', 'jmd-magandanow-new'); ?>
								</a>
							</td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Site Width', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('max. 1080px', 'jmd-magandanow-new'); ?></td>
							<td><?php esc_html_e('max. 1080px / 1431px', 'jmd-magandanow-new'); ?></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Responsive Layout', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-yes"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Extended Layout Options', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Second Sidebar', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Homepage Template', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-yes"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Total Widget Areas', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('12', 'jmd-magandanow-new'); ?></td>
							<td><?php esc_html_e('26', 'jmd-magandanow-new'); ?></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Custom Widgets', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('6 (Basic Features)', 'jmd-magandanow-new'); ?></td>
							<td><?php esc_html_e('23 (Full Features)', 'jmd-magandanow-new'); ?></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Custom Menus', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('1', 'jmd-magandanow-new'); ?></td>
							<td><?php esc_html_e('5', 'jmd-magandanow-new'); ?></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Transparent Header', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('jQuery News Ticker', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('FlexSlider 2 with Touch Support', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-yes"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Built-in Breadcrumb Navigation', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Built-in Social Buttons', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Related Posts Feature', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Advertising Options', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('MySkin Options', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('Basic Options', 'jmd-magandanow-new'); ?></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Color Options', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Google Webfonts Collection', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Typography Options', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Extended Features', 'jmd-magandanow-new'); ?></h3></td>
							<td><span class="dashicons dashicons-no"></span></td>
							<td><span class="dashicons dashicons-yes"></span></td>
						</tr>
						<tr>
							<td><h3><?php esc_html_e('Support', 'jmd-magandanow-new'); ?></h3></td>
							<td><?php esc_html_e('Support Forum', 'jmd-magandanow-new'); ?></td>
							<td><?php esc_html_e('Personal E-Mail Support', 'jmd-magandanow-new'); ?></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td>
								<a href="<?php echo esc_url('https://www.mhmyskins.com/myskins/mh/magandanews/'); ?>" target="_blank" class="upgrade-button">
									<?php esc_html_e('Upgrade to JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<hr>
			<div id="myskin-author">
				<p>
					<?php printf(esc_html__('%1$1s is proudly brought to you by %2$2s. If you like %3$3s: %4$4s.', 'jmd-magandanow-new'), $jmd_magandanow_new_data['Name'], '<a target="_blank" href="https://www.mhmyskins.com/" title="MH MySkins">MH MySkins</a>', $jmd_magandanow_new_data['Name'], '<a target="_blank" href="https://mandarincms.org/support/view/myskin-reviews/jmd-magandanow-new?filter=5" title="JMD MagandaNow lite Review">' . esc_html__('Rate this myskin', 'jmd-magandanow-new') . '</a>'); ?>
				</p>
			</div>
		</div><?php
	}
}

/***** Add Welcome Notice and Admin Menu for Parent MySkin and Official Child MySkins *****/

if (jmd_magandanow_new_official_myskin()) {
	add_action('admin_notices', 'jmd_magandanow_new_admin_notice');
	add_action('mcms_loaded', 'jmd_magandanow_new_hide_notice');
	add_action('admin_menu', 'jmd_magandanow_new_myskin_info_page');
}

?>