<?php

function jmd_magandanow_new_customize_register($mcms_customize) {

	/***** Register Custom Controls *****/

	class MH_Magazine_Lite_Upgrade extends MCMS_Customize_Control {
        public function render_content() {  ?>
        	<p class="mh-upgrade-thumb">
        		<img src="<?php echo get_template_directory_uri(); ?>/images/jmd_magandanews.png" />
        	</p>
        	<p class="customize-control-title mh-upgrade-title">
        		<?php esc_html_e('JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
        	</p>
        	<p class="textfield mh-upgrade-text">
        		<?php esc_html_e('If you like the free version of this myskin, you will LOVE the full version of JMD MagandaNow which includes unique custom widgets, additional features and more useful options to customize your website.', 'jmd-magandanow-new'); ?>
			</p>
			<p class="customize-control-title mh-upgrade-title">
        		<?php esc_html_e('Additional Features:', 'jmd-magandanow-new'); ?>
        	</p>
        	<ul class="mh-upgrade-features">
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Options to modify color scheme', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Typography options', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Several additional widget areas', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Additional custom widgets', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Extended layout options', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Additional custom menu slots', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Advanced advertising options', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Social buttons, related articles, ...', 'jmd-magandanow-new'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('News ticker and many more...', 'jmd-magandanow-new'); ?>
	        	</li>
        	</ul>
			<p class="mh-button mh-upgrade-button">
				<a href="https://www.mhmyskins.com/myskins/mh/magandanews/" target="_blank" class="button button-secondary">
					<?php esc_html_e('Upgrade to JMD MagandaNow Pro', 'jmd-magandanow-new'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://www.mhmyskins.com/myskins/mh/magandanews/#demos" target="_blank" class="button button-secondary">
					<?php esc_html_e('MySkin Demos', 'jmd-magandanow-new'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://www.mhmyskins.com/myskins/showcase/" target="_blank" class="button button-secondary">
					<?php esc_html_e('MH MySkins Showcase', 'jmd-magandanow-new'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://www.mhmyskins.com/support/documentation-mh-magandanews/" target="_blank" class="button button-secondary">
					<?php esc_html_e('MySkin Documentation', 'jmd-magandanow-new'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://mandarincms.org/support/myskin/jmd-magandanow-new" target="_blank" class="button button-secondary">
					<?php esc_html_e('Support Forum', 'jmd-magandanow-new'); ?>
				</a>
			</p><?php
        }
    }

    /***** Add Panels *****/

	$mcms_customize->add_panel('jmd_myskin_options', array('title' => esc_html__('MySkin Options', 'jmd-magandanow-new'), 'description' => '', 'capability' => 'edit_myskin_options', 'myskin_supports' => '', 'priority' => 1));

	/***** Add Sections *****/

	$mcms_customize->add_section('jmd_magandanow_new_general', array('title' => esc_html__('General', 'jmd-magandanow-new'), 'priority' => 1, 'panel' => 'jmd_myskin_options'));
	$mcms_customize->add_section('jmd_magandanow_new_layout', array('title' => esc_html__('Layout', 'jmd-magandanow-new'), 'priority' => 2, 'panel' => 'jmd_myskin_options'));
	if (jmd_magandanow_new_official_myskin()) {
		$mcms_customize->add_section('jmd_magandanow_new_upgrade', array('title' => esc_html__('More Features &amp; Options', 'jmd-magandanow-new'), 'priority' => 3, 'panel' => 'jmd_myskin_options'));
	}

    /***** Add Settings *****/

    $mcms_customize->add_setting('jmd_magandanow_new_options[excerpt_length]', array('default' => 25, 'type' => 'option', 'sanitize_callback' => 'jmd_sanitize_integer'));
    $mcms_customize->add_setting('jmd_magandanow_new_options[excerpt_more]', array('default' => '[...]', 'type' => 'option', 'sanitize_callback' => 'jmd_sanitize_text'));
    $mcms_customize->add_setting('jmd_magandanow_new_options[sb_position]', array('default' => 'right', 'type' => 'option', 'sanitize_callback' => 'jmd_sanitize_select'));
    $mcms_customize->add_setting('jmd_magandanow_new_options[author_box]', array('default' => 'enable', 'type' => 'option', 'sanitize_callback' => 'jmd_sanitize_select'));
    $mcms_customize->add_setting('jmd_magandanow_new_options[post_nav]', array('default' => 'enable', 'type' => 'option', 'sanitize_callback' => 'jmd_sanitize_select'));
	$mcms_customize->add_setting('jmd_magandanow_new_options[premium_version_upgrade]', array('default' => '', 'type' => 'option', 'sanitize_callback' => 'esc_attr'));

    /***** Add Controls *****/

    $mcms_customize->add_control('excerpt_length', array('label' => esc_html__('Custom excerpt length in words', 'jmd-magandanow-new'), 'section' => 'jmd_magandanow_new_general', 'settings' => 'jmd_magandanow_new_options[excerpt_length]', 'priority' => 1, 'type' => 'text'));
    $mcms_customize->add_control('excerpt_more', array('label' => esc_html__('Custom excerpt more text', 'jmd-magandanow-new'), 'section' => 'jmd_magandanow_new_general', 'settings' => 'jmd_magandanow_new_options[excerpt_more]', 'priority' => 2, 'type' => 'text'));
    $mcms_customize->add_control('sb_position', array('label' => esc_html__('Position of default sidebar', 'jmd-magandanow-new'), 'section' => 'jmd_magandanow_new_layout', 'settings' => 'jmd_magandanow_new_options[sb_position]', 'priority' => 1, 'type' => 'select', 'choices' => array('left' => esc_html__('Left', 'jmd-magandanow-new'), 'right' => esc_html__('Right', 'jmd-magandanow-new'))));
    $mcms_customize->add_control('author_box', array('label' => esc_html__('Author Box', 'jmd-magandanow-new'), 'section' => 'jmd_magandanow_new_layout', 'settings' => 'jmd_magandanow_new_options[author_box]', 'priority' => 2, 'type' => 'select', 'choices' => array('enable' => esc_html__('Enable', 'jmd-magandanow-new'), 'disable' => esc_html__('Disable', 'jmd-magandanow-new'))));
    $mcms_customize->add_control('post_nav', array('label' => esc_html__('Post/Attachment Navigation', 'jmd-magandanow-new'), 'section' => 'jmd_magandanow_new_layout', 'settings' => 'jmd_magandanow_new_options[post_nav]', 'priority' => 4, 'type' => 'select', 'choices' => array('enable' => esc_html__('Enable', 'jmd-magandanow-new'), 'disable' => esc_html__('Disable', 'jmd-magandanow-new'))));
	$mcms_customize->add_control(new MH_Magazine_Lite_Upgrade($mcms_customize, 'premium_version_upgrade', array('section' => 'jmd_magandanow_new_upgrade', 'settings' => 'jmd_magandanow_new_options[premium_version_upgrade]', 'priority' => 1)));
}
add_action('customize_register', 'jmd_magandanow_new_customize_register');

/***** Data Sanitization *****/

function jmd_sanitize_text($input) {
    return mcms_kses_post(force_balance_tags($input));
}
function jmd_sanitize_integer($input) {
    return strip_tags($input);
}
function jmd_sanitize_checkbox($input) {
    if ($input == 1) {
        return 1;
    } else {
        return '';
    }
}
function jmd_sanitize_select($input) {
    $valid = array(
        'left' => esc_html__('Left', 'jmd-magandanow-new'),
        'right' => esc_html__('Right', 'jmd-magandanow-new'),
        'enable' => esc_html__('Enable', 'jmd-magandanow-new'),
        'disable' => esc_html__('Disable', 'jmd-magandanow-new'),
    );
    if (array_key_exists($input, $valid)) {
        return $input;
    } else {
        return '';
    }
}

/***** Return MySkin Options / Set Default Options *****/

if (!function_exists('jmd_magandanow_new_myskin_options')) {
	function jmd_magandanow_new_myskin_options() {
		$myskin_options = mcms_parse_args(
			get_option('jmd_magandanow_new_options', array()),
			jmd_magandanow_new_default_options()
		);
		return $myskin_options;
	}
}

if (!function_exists('jmd_magandanow_new_default_options')) {
	function jmd_magandanow_new_default_options() {
		$default_options = array(
			'excerpt_length' => 25,
			'excerpt_more' => '[...]',
			'sb_position' => 'right',
			'author_box' => 'enable',
			'post_nav' => 'enable',
			'premium_version_label' => '',
			'premium_version_text' => '',
			'premium_version_button' => ''
		);
		return $default_options;
	}
}

/***** Enqueue Customizer CSS *****/

function jmd_magandanow_new_customizer_css() {
	mcms_enqueue_style('mh-customizer', get_template_directory_uri() . '/admin/customizer.css', array());
}
add_action('customize_controls_print_styles', 'jmd_magandanow_new_customizer_css');

?>