<?php
/**
 * JMD Worldcasts Theme Customizer
 *
 * @package JMD_MandarinCMS
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param MCMS_Customize_Manager $mcms_customize Theme Customizer object.
 */
function jmd_worldcasts_customize_register( $mcms_customize ) {
	$mcms_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$mcms_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$mcms_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $mcms_customize->selective_refresh ) ) {
		$mcms_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => 'jmd_worldcasts_customize_partial_blogname',
		) );
		$mcms_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => 'jmd_worldcasts_customize_partial_blogdescription',
		) );
	}
}
add_action( 'customize_register', 'jmd_worldcasts_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function jmd_worldcasts_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function jmd_worldcasts_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function jmd_worldcasts_customize_preview_js() {
	mcms_enqueue_script( 'jmd-worldcast-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'jmd_worldcasts_customize_preview_js' );

function jmd_worldcasts_options_customize_register( $mcms_customize ) {

	$mcms_customize->add_section( 'colors_section', array(
		'title'          => esc_html__( 'Elements Colors', 'jmd-worldcast' ),
		'priority'       => 134,
	));

		$mcms_customize->add_setting( 'label_background', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_hex_color',
		) );

		$mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'label_background', array(
			'label'   => esc_html__( 'Label Background', 'jmd-worldcast' ),
			'section' => 'colors_section',
			'settings'   => 'label_background',
		)));

		$mcms_customize->add_setting( 'slider_background', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_hex_color',
		) );

		$mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'slider_background', array(
			'label'   => esc_html__( 'Slider Background', 'jmd-worldcast' ),
			'section' => 'colors_section',
			'settings'   => 'slider_background',
		)));

	$mcms_customize->add_section( 'footer_section' , array(
		'title'      => 'Footer',
		'priority'   => 135,
	));

		$mcms_customize->add_setting( 'footer_copyright', array(
			'default'        => '',
			'sanitize_callback'	=> 'mcms_kses_post',
		));

		$mcms_customize->add_control( 'footer_copyright', array(
			'label'   => esc_html__( 'Footer Copyright', 'jmd-worldcast' ),
			'section' => 'footer_section',
			'settings'   => 'footer_copyright',
			'type'    => 'text',
			'priority' => 3
		));

		
	$mcms_customize->add_section( 'social_section' , array(
		'title'      => esc_html__( 'Social', 'jmd-worldcast' ),
		'priority'   => 135,
	));

		$mcms_customize->add_setting( 'social_twitter', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_twitter', array(
			'label'   => esc_html__( 'Twitter', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_twitter',
			'type'    => 'text',
			'priority' => 3
		));

		$mcms_customize->add_setting( 'social_facebook', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_facebook', array(
			'label'   => esc_html__( 'Facebook', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_facebook',
			'type'    => 'text',
			'priority' => 3
		));

		$mcms_customize->add_setting( 'social_google', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_google', array(
			'label'   => esc_html__( 'Google +', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_google',
			'type'    => 'text',
			'priority' => 3
		));

		$mcms_customize->add_setting( 'social_instagram', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_instagram', array(
			'label'   => esc_html__( 'Instagram', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_instagram',
			'type'    => 'text',
			'priority' => 3
		));

		$mcms_customize->add_setting( 'social_pinterest', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_pinterest', array(
			'label'   => esc_html__( 'Pinterest', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_pinterest',
			'type'    => 'text',
			'priority' => 3
		));

		$mcms_customize->add_setting( 'social_vimeo', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_vimeo', array(
			'label'   => esc_html__( 'Vimeo', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_vimeo',
			'type'    => 'text',
			'priority' => 3
		));	

		$mcms_customize->add_setting( 'social_youtube', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_youtube', array(
			'label'   => esc_html__( 'Youtube', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_youtube',
			'type'    => 'text',
			'priority' => 3
		));	

		$mcms_customize->add_setting( 'social_linkedin', array(
			'default'        => '',
			'sanitize_callback'	=> 'sanitize_text_field',
		));

		$mcms_customize->add_control( 'social_linkedin', array(
			'label'   => esc_html__( 'LinkedIn', 'jmd-worldcast' ),
			'section' => 'social_section',
			'settings'   => 'social_linkedin',
			'type'    => 'text',
			'priority' => 3
		));	

}

add_action( 'customize_register', 'jmd_worldcasts_options_customize_register' );



function jmd_worldcasts_elements_style() { ?>
<style>
<?php $label_background = get_myskin_mod('label_background');
if (!empty($label_background)): ?>
.categories-wrap a,header .site-navigation .current-menu-item > a,header .site-navigation a:hover{background: <?php echo esc_attr($label_background); ?>;}
.video-label{color: <?php echo esc_attr($label_background); ?>;}
<?php endif; ?>
<?php $slider_background = get_myskin_mod('slider_background');
if (!empty($slider_background)): ?>
.main-slider-wrap{background: <?php echo esc_attr($slider_background); ?>;}
<?php endif; ?>
</style>

<?php }

add_action( 'mcms_head', 'jmd_worldcasts_elements_style' );