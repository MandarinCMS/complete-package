<?php

class GardenLogin_Entities {

  /**
  * Variable that Check for GardenLogin Key.
  *
  * @var string
  * @since 1.0.0
  */
  public $gardenlogin_key;

  /**
  * Class constructor
  */
  public function __construct() {

    $this->gardenlogin_key = get_option( 'gardenlogin_customization' );
    $this->_hooks();
  }


  /**
  * Hook into actions and filters
  *
  * @since 1.0.0
  */
  private function _hooks() {

    add_filter( 'login_headerurl',		array( $this, 'login_page_logo_url' ) );
    add_filter( 'login_headertitle',	array( $this, 'login_page_logo_title' ) );
    add_filter( 'login_errors',			 	array( $this, 'login_error_messages' ) );

    add_filter( 'login_message',			array( $this, 'change_welcome_message' ) );
    add_action( 'customize_register', array( $this, 'customize_login_panel' ) );
    add_action( 'login_footer',			 	array( $this, 'login_page_custom_footer' ) );
    add_action( 'login_head',				 	array( $this, 'login_page_custom_head' ) );
    add_action( 'init',							 	array( $this, 'redirect_to_custom_page' ) );
    add_action( 'admin_menu',				 	array( $this, 'menu_url' ), 10 );

    /**
     * This function enqueues scripts and styles in the Customizer.
     */
    add_action( 'customize_controls_enqueue_scripts', 	array( $this,  'gardenlogin_customizer_js' ) );

    /**
     * This function is triggered on the initialization of the Previewer in the Customizer.
     * We add actions that pertain to the Previewer window here.
     * The actions added here are triggered only in the Previewer and not in the Customizer.
     * @since 1.0.23
     */
    add_action( 'customize_preview_init',               array( $this, 'gardenlogin_customizer_previewer_js' ) );
    add_filter( 'woocommerce_process_login_errors',     array( $this, 'gardenlogin_woo_login_errors' ), 10, 3 );

  }


  /**
  * Enqueue jQuery and use mcms_localize_script.
  *
  * @since 1.0.9
  * @version 1.1.3
  */
  function gardenlogin_customizer_js() {
    mcms_enqueue_script('jquery');
    mcms_enqueue_script( 'gardenlogin-customize-control', modules_url(  'js/customize-controls.js' , LOGINPRESS_ROOT_FILE  ), array( 'jquery', 'customize-preview' ), LOGINPRESS_VERSION, true );

    /*
  	 * Our Customizer script
  	 *
  	 * Dependencies: Customizer Controls script (core)
  	 */
  	mcms_enqueue_script( 'gardenlogin-control-script', modules_url(  'js/customizer.js' , LOGINPRESS_ROOT_FILE  ), array( 'customize-controls' ), LOGINPRESS_VERSION, true );


    // Get Background URL for use in Customizer JS.
    $user = mcms_get_current_user();
    $name = empty( $user->user_firstname ) ? ucfirst( $user->display_name ) : ucfirst( $user->user_firstname );
    $gardenlogin_bg = get_option( 'gardenlogin_customization');
    $gardenlogin_bg_url = $gardenlogin_bg['setting_background'] ? $gardenlogin_bg['setting_background'] : false;

    // Array for localize.
    $gardenlogin_localize = array(
      'admin_url'         => admin_url(),
      'module_url'        => modules_url(),
      'login_myskin'       => get_option( 'customize_presets_settings', true ),
      'gardenlogin_bg_url' => $gardenlogin_bg_url,
    );

    mcms_localize_script( 'gardenlogin-customize-control', 'gardenlogin_script', $gardenlogin_localize );

  }

  /**
   * This function is called only on the Previwer and enqueues scripts and styles.
   * Our Customizer script
   *
   * Dependencies: Customizer Preview script (core)
   * @since 1.0.23
   */
  function gardenlogin_customizer_previewer_js() {

    mcms_enqueue_style( 'gardenlogin-customizer-previewer-style', modules_url(  'css/style-previewer.css' , LOGINPRESS_ROOT_FILE  ), array(), LOGINPRESS_VERSION );

  	mcms_enqueue_script( 'gardenlogin-customizer-previewer-script', modules_url(  'js/customizer-previewer.js' , LOGINPRESS_ROOT_FILE  ), array( 'customize-preview' ), LOGINPRESS_VERSION, true );

  }

  /**
   * Create a method of setting and control for GardenLogin_Range_Control.
   * @param  object $mcms_customize
   * @param  string $control
   * @param  string $default
   * @param  string $label
   * @param  array $input_attr
   * @param  array $unit
   * @param  int $index
   * @return object
   * @since  1.1.3
   */
  function gardenlogin_rangle_seting( $mcms_customize, $control, $default, $label, $input_attr, $unit, $section, $index, $priority = '' ){

    $mcms_customize->add_setting( "gardenlogin_customization[{$control[$index]}]", array(
      'default'               => $default[$index],
      'type' 			            => 'option',
      'capability'		        => 'manage_options',
      'transport'             => 'postMessage',
      'sanitize_callback'     => 'absint',
    ) );

    $mcms_customize->add_control( new GardenLogin_Range_Control( $mcms_customize, "gardenlogin_customization[{$control[$index]}]", array(
      'type'           => 'gardenlogin-range',
      'label'          => $label[$index],
      'section'        => $section,
      'priority'		   => $priority,
      'settings'       => "gardenlogin_customization[{$control[$index]}]",
      'default'        => $default[$index],
      'input_attrs'    => $input_attr[$index],
      'unit'           => $unit[$index],
    ) ) );
  }

  /**
   * Create a method of setting and control for GardenLogin_Group_Control.
   * @param  object $mcms_customize
   * @param  string $control
   * @param  string $label
   * @param  string $section
   * @param  string $info_test
   * @param  int $index
   * @return object
   * @since 1.1.3
   */
  function gardenlogin_group_setting( $mcms_customize, $control, $label, $info_test, $section, $index, $priority = '' ){

    $mcms_customize->add_setting( "gardenlogin_customization[{$control[$index]}]", array(
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new GardenLogin_Group_Control( $mcms_customize, "gardenlogin_customization[{$control[$index]}]", array(
      'settings'	  => "gardenlogin_customization[{$control[$index]}]",
      'label'		    => $label[$index],
      'section'	    => $section,
      'type'        => 'group',
      'info_text'   => $info_test[$index],
      'priority'		=> $priority,
    ) ) );
  }

  /**
   * Create a method of setting and control for MCMS_Customize_Color_Control.
   * @param  object $mcms_customize
   * @param  string $control
   * @param  string $label
   * @param  string $section
   * @param  int $index
   * @return object
   * @since 1.1.3
   */
  function gardenlogin_color_setting( $mcms_customize, $control, $label, $section, $index, $priority = '' ){

    $mcms_customize->add_setting( "gardenlogin_customization[{$control[$index]}]", array(
      // 'default'				=> $form_color_default[$form_color],
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, "gardenlogin_customization[{$control[$index]}]", array(
      'label'		    => $label[$index],
      'section'	    => $section,
      'settings'	  => "gardenlogin_customization[{$control[$index]}]",
      'priority'		=> $priority,
    ) ) );
  }

  function gardenlogin_hr_setting( $mcms_customize, $control, $section, $index, $priority = '' ){

    $mcms_customize->add_setting( "gardenlogin_customization[{$control[$index]}]", array(
    	'sanitize_callback' => 'sanitize_text_field',
    ) );

    $mcms_customize->add_control( new GardenLogin_Misc_Control( $mcms_customize, "gardenlogin_customization[{$control[$index]}]", array(
      'section'     => $section,
      'type'        => 'hr',
      'priority'	  => $priority,
    ) ) );
  }

  /**
  * Register module settings Panel in MCMS Customizer
  *
  * @param	$mcms_customize
  * @since	1.0.0
  */
  public function customize_login_panel( $mcms_customize ) {

    include LOGINPRESS_ROOT_PATH .'classes/control-presets.php';

    include LOGINPRESS_ROOT_PATH .'classes/controls/background-gallery.php';

    include LOGINPRESS_ROOT_PATH .'classes/controls/range.php';

    include LOGINPRESS_ROOT_PATH .'classes/controls/group.php';

    include LOGINPRESS_ROOT_PATH .'classes/controls/radio-button.php';

    include LOGINPRESS_ROOT_PATH .'classes/controls/miscellaneous.php';

    include LOGINPRESS_ROOT_PATH .'include/customizer-strings.php';

    if ( ! has_action( 'gardenlogin_pro_add_template' ) ) :
      include LOGINPRESS_ROOT_PATH .'classes/class-gardenlogin-promo.php';
    endif;

    //	=============================
    //	= Panel for the GardenLogin	=
    //	=============================
    $mcms_customize->add_panel( 'gardenlogin_panel', array(
      'title'						=> __( 'GardenLogin', 'gardenlogin' ),
      'description'			=> __( 'Customize Your MandarinCMS Login Page with GardenLogin :)', 'gardenlogin' ),
      'priority'				=> 30,
    ) );

    /**
    * =============================
    *	= Section for Presets		=
    * =============================
    *
    * @since	1.0.9
    * @version 1.0.23
    */
    $mcms_customize->add_section( 'customize_presets', array(
      'title'				   => __( 'MySkins', 'gardenlogin' ),
      'description'	   => __( 'Choose MySkin', 'gardenlogin' ),
      'priority'			 => 1,
      'panel'				   => 'gardenlogin_panel',
      ) );

      $mcms_customize->add_setting( 'customize_presets_settings', array(
        'default'				=> 'default1',
        'type'					=> 'option',
        'capability'		=> 'manage_options',
      ) );

      $gardenlogin_free_templates  = array();
      $gardenlogin_myskin_name = array( "", "",
        __( 'Company',        'gardenlogin' ),
        __( 'Persona',        'gardenlogin' ),
        __( 'Corporate',      'gardenlogin' ),
        __( 'Corporate',      'gardenlogin' ),
        __( 'Startup',        'gardenlogin' ),
        __( 'Wedding',        'gardenlogin' ),
        __( 'Wedding #2',     'gardenlogin' ),
        __( 'Company',        'gardenlogin' ),
        __( 'Bikers',         'gardenlogin' ),
        __( 'Fitness',        'gardenlogin' ),
        __( 'Shopping',       'gardenlogin' ),
        __( 'Writers',        'gardenlogin' ),
        __( 'Persona',        'gardenlogin' ),
        __( 'Geek',           'gardenlogin' ),
        __( 'Innovation',     'gardenlogin' ),
        __( 'Photographers',  'gardenlogin' ) );

        // 1st template that is default
        $gardenlogin_free_templates["default1" ] = array(
          'img'       => modules_url( 'img/bg.jpg', LOGINPRESS_ROOT_FILE ),
          'thumbnail' => modules_url( 'img/thumbnail/default-1.png', LOGINPRESS_ROOT_FILE ),
          'id'        => 'default1',
          'name'      => 'Default'
        ) ;

        // Loof through the templates.
        $_count = 2;
        while ( $_count <= 17 ) :

          $gardenlogin_free_templates["default{$_count}" ] = array(
            // 'img'       => modules_url( 'img/bg.jpg', LOGINPRESS_ROOT_FILE ),
            'thumbnail' => modules_url( "img/thumbnail/default-{$_count}.png", LOGINPRESS_ROOT_FILE ),
            'id'        => "default{$_count}",
            'name'      => $gardenlogin_myskin_name[$_count],
            'pro'       => 'yes'
          );
          $_count++;
        endwhile;

        // 18th template for custom design.
        $gardenlogin_free_templates["default18" ] = array(
            'img'       => modules_url( 'gardenlogin/img/bg17.jpg', LOGINPRESS_ROOT_PATH ),
            'thumbnail' => modules_url( 'gardenlogin/img/thumbnail/custom-design.png', LOGINPRESS_ROOT_PATH ),
            'id'        => 'default18',
            'name'      => __( 'Custom Design', 'gardenlogin' ),
            'link'      => 'yes'
          );
        $gardenlogin_templates = apply_filters( 'gardenlogin_pro_add_template', $gardenlogin_free_templates );

        $mcms_customize->add_control( new GardenLogin_Presets( $mcms_customize, 'customize_presets_settings',
        array(
          'section' => 'customize_presets',
          // 'label'   => __( 'MySkins', 'gardenlogin' ),
          'choices' => $gardenlogin_templates
        ) ) );
    //End of Presets.


    //	=============================
    //	= Section for Login Logo		=
    //	=============================
    $mcms_customize->add_section( 'customize_logo_section', array(
      'title'				 => __( 'Logo', 'gardenlogin' ),
      'description'	 => __( 'Customize Your Logo Section', 'gardenlogin' ),
      'priority'			=> 5,
      'panel'				 => 'gardenlogin_panel',
    ) );

    /**
     * [ Enable / Disabe Logo Image with GardenLogin_Radio_Control ]
     * @since 1.1.3
     */

     $mcms_customize->add_setting( 'gardenlogin_customization[setting_logo_display]', array(
       'default'        => false,
       'type'           => 'option',
       'capability'		  => 'manage_options',
       'transport'      => 'postMessage'
     ) );

     $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[setting_logo_display]', array(
      'settings'    => 'gardenlogin_customization[setting_logo_display]',
   		'label'	      => __( 'Disable Logo:', 'gardenlogin'),
   		'section'     => 'customize_logo_section',
      'priority'	=> 4,
   		'type'        => 'ios',// light, ios, flat
     ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[setting_logo]', array(
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'      => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Image_Control( $mcms_customize, 'gardenlogin_customization[setting_logo]', array(
      'label'		  => __( 'Logo Image:', 'gardenlogin' ),
      'section'	  => 'customize_logo_section',
      'priority'	=> 5,
      'settings'	=> 'gardenlogin_customization[setting_logo]'
    ) ) );

    /**
     * [ Change CSS Properties Input fields with GardenLogin_Range_Control ]
     * @since 1.0.1
     * @version 1.1.3
     */

    $this->gardenlogin_rangle_seting( $mcms_customize, $logo_range_control, $logo_range_default, $logo_range_label, $logo_range_attrs, $logo_range_unit, 'customize_logo_section', 0, 10 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $logo_range_control, $logo_range_default, $logo_range_label, $logo_range_attrs, $logo_range_unit, 'customize_logo_section', 1, 15 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $logo_range_control, $logo_range_default, $logo_range_label, $logo_range_attrs, $logo_range_unit, 'customize_logo_section', 2, 20 );

    $logo_control = array( 'customize_logo_hover', 'customize_logo_hover_title' );
    $logo_default = array( '', '' );
    $logo_label = array( __( 'Logo URL:', 'gardenlogin' ), __( 'Logo Hover Title:', 'gardenlogin' ) );

    $logo = 0;
    while ( $logo < 2 ) :

      $mcms_customize->add_setting( "gardenlogin_customization[{$logo_control[$logo]}]", array(
        'default'					=> $logo_default[$logo],
        'type'						=> 'option',
        'capability'			=> 'manage_options',
        'transport'       => 'postMessage'
      ) );

      $mcms_customize->add_control( "gardenlogin_customization[{$logo_control[$logo]}]", array(
        'label'						 => $logo_label[$logo],
        'section'					 => 'customize_logo_section',
        'priority'					=> 25,
        'settings'					=> "gardenlogin_customization[{$logo_control[$logo]}]"
      ) );

      $logo++;
    endwhile;

    //	=============================
    //	= Section for Background		=
    //	=============================
    $mcms_customize->add_section( 'section_background', array(
      'title'				 => __( 'Background', 'gardenlogin' ),
      'description'	 => '',
      'priority'		 => 10,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[setting_background_color]', array(
      // 'default'				=> '#ddd5c3',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[setting_background_color]', array(
      'label'		 => __( 'Background Color:', 'gardenlogin' ),
      'section'	 => 'section_background',
      'priority'	=> 5,
      'settings'	=> 'gardenlogin_customization[setting_background_color]'
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[gardenlogin_display_bg]', array(
      'default'        => true,
      'type'           => 'option',
      'capability'		 => 'manage_options',
      'transport'      => 'postMessage'
    ) );
    /**
     * [Enable / Disabe Background Image with GardenLogin_Radio_Control]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[gardenlogin_display_bg]', array(
      'settings'    => 'gardenlogin_customization[gardenlogin_display_bg]',
  		'label'	      => __( 'Enable Background Image?', 'gardenlogin'),
  		'section'     => 'section_background',
      'priority'    => 10,
  		'type'        => 'ios',// light, ios, flat
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[setting_background]', array(
      // 'default'       =>  modules_url( 'img/bg.jpg', LOGINPRESS_ROOT_FILE ) ,
      'type'					 => 'option',
      'capability'		 => 'manage_options',
      'transport'      => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Image_Control( $mcms_customize, 'gardenlogin_customization[setting_background]', array(
      'label'		   => __( 'Background Image:', 'gardenlogin' ),
      'section'	   => 'section_background',
      'priority'	 => 15,
      'settings'	 => 'gardenlogin_customization[setting_background]',
    ) ) );

    /**
     * [ Add Background Gallery ]
     * @since 1.1.0
     */
    $mcms_customize->add_setting( 'gardenlogin_customization[gallery_background]', array(
      'default'				=> modules_url( "img/gallery/img-1.jpg", LOGINPRESS_ROOT_FILE ),
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $gardenlogin_free_background  = array();
    $gardenlogin_background_name  = array( "",
      __( 'Company',        'gardenlogin' ),
      __( 'Persona',        'gardenlogin' ),
      __( 'Corporate',      'gardenlogin' ),
      __( 'Corporate',      'gardenlogin' ),
      __( 'Startup',        'gardenlogin' ),
      __( 'Wedding',        'gardenlogin' ),
      __( 'Wedding #2',     'gardenlogin' ),
      __( 'Company',        'gardenlogin' ),
      __( 'Bikers',         'gardenlogin' ) );

    // Loof through the backgrounds.
    $bg_count = 1;
    while ( $bg_count <= 9 ) :

      $thumbname = modules_url( "img/gallery/img-{$bg_count}.jpg", LOGINPRESS_ROOT_FILE );
      $gardenlogin_free_background[$thumbname] = array(
        'thumbnail' => modules_url( "img/thumbnail/gallery-img-{$bg_count}.jpg", LOGINPRESS_ROOT_FILE ),
        'id'        => $thumbname,
        'name'      => $gardenlogin_background_name[$bg_count]
      );
      $bg_count++;
    endwhile;

    $gardenlogin_background = apply_filters( 'gardenlogin_pro_add_background', $gardenlogin_free_background );

    $mcms_customize->add_control( new GardenLogin_Background_Gallery_Control( $mcms_customize, 'gardenlogin_customization[gallery_background]',
    array(
      'section' => 'section_background',
      'label'   => __( 'Background Gallery:', 'gardenlogin' ),
      'choices' => $gardenlogin_background
    ) ) );


    $mcms_customize->add_setting( 'gardenlogin_customization[background_repeat_radio]', array(
      'default'				=> 'no-repeat',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[background_repeat_radio]', array(
      'label'					=> __( 'Background Repeat:', 'gardenlogin' ),
      'section'				=> 'section_background',
      'priority'			=> 20,
      'settings'			=> 'gardenlogin_customization[background_repeat_radio]',
      'type'					=> 'radio',
      'choices'				=> array(
        'repeat'				=> 'repeat',
        'repeat-x'			=> 'repeat-x',
        'repeat-y'			=> 'repeat-y',
        'no-repeat'		  => 'no-repeat',
        'initial'			  => 'initial',
        'inherit'			  => 'inherit',
      ),
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[background_position]', array(
      'default'				=> 'center',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );
    $mcms_customize->add_control( 'gardenlogin_customization[background_position]', array(
      'settings'			=> 'gardenlogin_customization[background_position]',
      'label'					=> __( 'Select Position:', 'gardenlogin' ),
      'section'				=> 'section_background',
      'priority'			=> 25,
      'type'					=> 'select',
      'choices'				=> array(
        'left top'			=> 'left top',
        'left center'	  => 'left center',
        'left bottom'	  => 'left bottom',
        'right top'		  => 'right top',
        'right center'	=> 'right center',
        'right bottom'	=> 'right bottom',
        'center top'		=> 'center top',
        'center'				=> 'center',
        'center bottom' => 'center bottom',
      ),
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[background_image_size]', array(
      'default'				=> 'cover',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ));

    $mcms_customize->add_control( 'gardenlogin_customization[background_image_size]', array(
      'label'					=> __( 'Background Image Size: ', 'gardenlogin' ),
      'section'				=> 'section_background',
      'priority'			=> 30,
      'settings'			=> 'gardenlogin_customization[background_image_size]',
      'type'					=> 'select',
      'choices'					=> array(
        'auto'					=> 'auto',
        'cover'				  => 'cover',
        'contain'			  => 'contain',
        'initial'			  => 'initial',
        'inherit'			  => 'inherit',
      ),
    ) );

    //	=============================
    //	= Section for Form Beauty	 =
    //	=============================
    $mcms_customize->add_section( 'section_form', array(
      'title'				 => __( 'Customize Login Form', 'gardenlogin' ),
      'description'	 => '',
      'priority'			=> 15,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_form', 2, 4 );

    /**
     * [ Enable / Disabe Form Background Image with GardenLogin_Radio_Control ]
     * @since 1.1.3
     */

     $mcms_customize->add_setting( 'gardenlogin_customization[setting_form_display_bg]', array(
       'default'        => false,
       'type'           => 'option',
       'capability'		 => 'manage_options',
       'transport'      => 'postMessage'
     ) );

     $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[setting_form_display_bg]', array(
       'settings'    => 'gardenlogin_customization[setting_form_display_bg]',
   		'label'	      => __( 'Enable Form Transparency:', 'gardenlogin'),
   		'section'     => 'section_form',
      'priority'	 => 5,
   		'type'        => 'ios',
     ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[setting_form_background]', array(
      'type'          => 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Image_Control( $mcms_customize, 'gardenlogin_customization[setting_form_background]', array(
      'label'		 => __( 'Form Background Image:', 'gardenlogin' ),
      'section'	 => 'section_form',
      'priority'	=> 6,
      'settings'	=> 'gardenlogin_customization[setting_form_background]'
    ) ) );

    $this->gardenlogin_color_setting( $mcms_customize, $form_color_control, $form_color_label, 'section_form', 0, 7 );

    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 0, 15 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 1, 20 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 2, 25 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 3, 30 );
    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 4, 35 );

    $form_padding = 0;
    while ( $form_padding < 2 ) :

      $mcms_customize->add_setting( "gardenlogin_customization[{$form_control[$form_padding]}]", array(
        'default'				=> $form_default[$form_padding],
        'type'					=> 'option',
        'capability'		=> 'manage_options',
        'transport'     => 'postMessage'
      ) );

      $mcms_customize->add_control( "gardenlogin_customization[{$form_control[$form_padding]}]", array(
        'label'						 => $form_label[$form_padding],
        'section'					 => 'section_form',
        'priority'					=> 40,
        'settings'				 => "gardenlogin_customization[{$form_control[$form_padding]}]"
      ) );

      $form_padding++;
    endwhile;

    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_form', 3, 41 );

    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_form', 0, 45 );

    $this->gardenlogin_color_setting( $mcms_customize, $form_color_control, $form_color_label, 'section_form', 1, 50 );
    $this->gardenlogin_color_setting( $mcms_customize, $form_color_control, $form_color_label, 'section_form', 2, 55 );

    $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 5, 60 );
    // textfield_radius.
    // $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 6, 65 );
    // textfield_shadow.
    // $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 7, 70 );
    // textfield_shadow_opacity.
    // $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 8, 75 );

    /**
     * [ Enable / Disabe Form Background Image with GardenLogin_Radio_Control ]
     * @since 1.1.3
     */

     // $mcms_customize->add_setting( 'gardenlogin_customization[textfield_inset_shadow]', array(
     //   'default'        => false,
     //   'type'           => 'option',
     //   'capability'    => 'manage_options',
     //   'transport'      => 'postMessage'
     // ) );
     //
     // $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[textfield_inset_shadow]', array(
     //  'settings'    => 'gardenlogin_customization[textfield_inset_shadow]',
     //  'label'       => __( 'Input Text Field Shadow Inset:', 'gardenlogin'),
     //  'section'     => 'section_form',
     //  'priority'		=> 80,
     //  'type'        => 'ios',// light, ios, flat
     // ) ) );

    $input_padding = 2;
    while ( $input_padding < 3 ) :

      $mcms_customize->add_setting( "gardenlogin_customization[{$form_control[$input_padding]}]", array(
        'default'				=> $form_default[$input_padding],
        'type'					=> 'option',
        'capability'		=> 'manage_options',
        'transport'     => 'postMessage'
      ) );

      $mcms_customize->add_control( "gardenlogin_customization[{$form_control[$input_padding]}]", array(
        'label'						 => $form_label[$input_padding],
        'section'					 => 'section_form',
        'priority'					=> 85,
        'settings'				 => "gardenlogin_customization[{$form_control[$input_padding]}]"
      ) );

      $input_padding++;
    endwhile;

    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_form', 4, 86 );
    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_form', 1, 90 );

    // $form_input_label = 3;
    // while ( $form_input_label < 5 ) :
    //
    //   $mcms_customize->add_setting( "gardenlogin_customization[{$form_control[$form_input_label]}]", array(
    //     'default'				=> $form_default[$form_input_label],
    //     'type'					=> 'option',
    //     'capability'		=> 'manage_options',
    //     'transport'     => 'postMessage'
    //   ) );
    //
    //   $mcms_customize->add_control( "gardenlogin_customization[{$form_control[$form_input_label]}]", array(
    //     'label'						 => $form_label[$form_input_label],
    //     'section'					 => 'section_form',
    //     'priority'					=> 91,
    //     'settings'				 => "gardenlogin_customization[{$form_control[$form_input_label]}]"
    //   ) );
    //
    //   $form_input_label++;
    // endwhile;

    $this->gardenlogin_color_setting( $mcms_customize, $form_color_control, $form_color_label, 'section_form', 3, 95 );
    $this->gardenlogin_color_setting( $mcms_customize, $form_color_control, $form_color_label, 'section_form', 4, 100 );

    // customize_form_label.
    // $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 9, 105 );
    // remember_me_font_size.
    // $this->gardenlogin_rangle_seting( $mcms_customize, $form_range_control, $form_range_default, $form_range_label, $form_range_attrs, $form_range_unit, 'section_form', 10, 110 );
    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_form', 5, 111 );

    //	=============================
    //	= Section for Forget Form	 =
    //	=============================
    $mcms_customize->add_section(
    'section_forget_form',
    array(
      'title'				 => __( 'Customize Forget Form', 'gardenlogin' ),
      'description'	 => '',
      'priority'		 => 20,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[forget_form_background]', array(
      'type'          => 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Image_Control( $mcms_customize, 'gardenlogin_customization[forget_form_background]', array(
      'label'		    => __( 'Forget Form Background Image:', 'gardenlogin' ),
      'section'	    => 'section_forget_form',
      'priority'	  => 5,
      'settings'	  => 'gardenlogin_customization[forget_form_background]'
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[forget_form_background_color]', array(
      // 'default'				=> '#FFF',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[forget_form_background_color]', array(
      'label'		 => __( 'Forget Form Background Color:', 'gardenlogin' ),
      'section'	 => 'section_forget_form',
      'priority'	=> 10,
      'settings'	=> 'gardenlogin_customization[forget_form_background_color]'
    ) ) );

    //	=============================
    //	= Section for Button Style	=
    //	=============================
    $mcms_customize->add_section( 'section_button', array(
      'title'				 => __( 'Button Beauty', 'gardenlogin' ),
      'description'	 => '',
      'priority'		 => 25,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 0, 5 );
    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 1, 10 );
    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 2, 15 );
    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 3, 20 );
    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 4, 25 );
    $this->gardenlogin_color_setting( $mcms_customize, $button_control, $button_label, 'section_button', 5, 30 );

    /**
     * [ Change Button CSS Properties with GardenLogin_Range_Control ]
     * @since 1.0.1
     * @version 1.1.3
     */

     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 0, 35 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 1, 40 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 2, 45 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 3, 50 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 4, 55 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 5, 60 );
     $this->gardenlogin_rangle_seting( $mcms_customize, $button_range_control, $button_range_default, $button_range_label, $button_range_attrs, $button_range_unit, 'section_button', 6, 65 );

    //	=============================
    //	= Section for Error message =
    //	=============================
    $mcms_customize->add_section( 'section_error', array(
      'title'				 => __( 'Error Messages', 'gardenlogin' ),
      'description'	 => '',
      'priority'		 => 30,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $error_control = array( 'incorrect_username', 'incorrect_password', 'empty_username', 'empty_password', 'invalid_email', 'empty_email', 'username_exists', 'email_exists', 'invalidcombo_message', 'force_email_login' );
    $error_default = array(
      sprintf( __( '%1$sError:%2$s Invalid Username.', 'gardenlogin' ), '<strong>', '</strong>' ), sprintf( __( '%1$sError:%2$s Invalid Password.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s The username field is empty.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s The password field is empty.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s The email address isn\'t correct..', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s Please type your email address.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s This username is already registered. Please choose another one.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s This email is already registered, please choose another one.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s Invalid username or email.', 'gardenlogin' ), '<strong>', '</strong>' ),
      sprintf( __( '%1$sError:%2$s Invalid Email Address', 'gardenlogin' ), '<strong>', '</strong>' ) );
    $error_label = array(
      __( 'Incorrect Username Message:',  'gardenlogin' ),
      __( 'Incorrect Password Message:',  'gardenlogin' ),
      __( 'Empty Username Message:',      'gardenlogin' ),
      __( 'Empty Password Message:',      'gardenlogin' ),
      __( 'Invalid Email Message:',       'gardenlogin' ),
      __( 'Empty Email Message:',         'gardenlogin' ),
      __( 'Username Already Exist Message:','gardenlogin' ),
      __( 'Email Already Exist Message:', 'gardenlogin' ),
      __( 'Forget Password Message:',     'gardenlogin' ),
      __( 'Login with Email Message:',    'gardenlogin' ),
    );

    $error = 0;
    while ( $error < 10 ) :

      $mcms_customize->add_setting( "gardenlogin_customization[{$error_control[$error]}]", array(
        'default'				=> $error_default[$error],
        'type'					=> 'option',
        'capability'		=> 'manage_options',
        'transport'     => 'postMessage'
      ) );

      $mcms_customize->add_control( "gardenlogin_customization[{$error_control[$error]}]", array(
        'label'						 => $error_label[$error],
        'section'					 => 'section_error',
        'priority'				 => 5,
        'settings'				 => "gardenlogin_customization[{$error_control[$error]}]",
      ) );

      $error++;
    endwhile;

    //	=============================
    //	= Section for Welcome message
    //	=============================
    $mcms_customize->add_section( 'section_welcome', array(
      'title'				 => __( 'Welcome Messages', 'gardenlogin' ),
      'description'	 => '',
      'priority'		 => 35,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $welcome_control = array( 'lostpwd_welcome_message', 'welcome_message', 'register_welcome_message', 'logout_message', 'message_background_border' );
    $welcome_default = array( 'Forgot password?', 'Welcome', 'Register For This Site', 'Logout', '' );
    $welcome_label	 = array(
      __( 'Welcome Message on Lost Password:', 'gardenlogin' ),
      __( 'Welcome Message on Login Page:', 'gardenlogin' ),
      __( 'Welcome Message on Registration:', 'gardenlogin' ),
      __( 'Logout Message:', 'gardenlogin' ),
      __( 'Message Field Border: ( Example: 1px solid #00a0d2; )', 'gardenlogin' ),
    );

    $welcome = 0;
    while ( $welcome < 5 ) :

      $mcms_customize->add_setting( "gardenlogin_customization[{$welcome_control[$welcome]}]", array(
        'type'					=> 'option',
        'capability'		=> 'manage_options',
        'transport'     => 'postMessage'
      ));

      $mcms_customize->add_control( "gardenlogin_customization[{$welcome_control[$welcome]}]", array(
        'label'						 => $welcome_label[ $welcome ],
        'section'					 => 'section_welcome',
        'priority'					=> 5,
        'settings'					=> "gardenlogin_customization[{$welcome_control[$welcome]}]",
        'input_attrs' => array(
            'placeholder' => $welcome_default[ $welcome ],
        )
      ) );

      $welcome++;
    endwhile;

    $mcms_customize->add_setting( 'gardenlogin_customization[message_background_color]', array(
      // 'default'				=> '#fff',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[message_background_color]', array(
      'label'		 => __( 'Message Field Background Color:', 'gardenlogin' ),
      'section'	 => 'section_welcome',
      'priority'	=> 30,
      'settings'	=> 'gardenlogin_customization[message_background_color]'
    ) ) );

    //	=============================
    //	= Section for Header message
    //	=============================
    // $mcms_customize->add_section(
    //		 'section_head',
    //		 array(
    //				 'title'				 => __( 'Header Message', 'gardenlogin' ),
    //				 'description'	 => '',
    //				 'priority'			=> 35,
    //				 'panel'				 => 'gardenlogin_panel',
    // ));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_hearder_message]', array(
    //		 'default'					 => 'Latest NEWS',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control( 'login_hearder_message', array(
    //		 'label'						 => __( 'Header Message:', 'gardenlogin' ),
    //		 'section'					 => 'section_head',
    //		 'priority'					=> 5,
    //		 'settings'					=> 'gardenlogin_customization[login_hearder_message]',
    // ));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_hearder_message_link]', array(
    //		 'default'					 => '#',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control( 'login_hearder_message_link', array(
    //		 'label'						 => __( 'Header Message Link:', 'gardenlogin' ),
    //		 'section'					 => 'section_head',
    //		 'priority'					=> 5,
    //		 'settings'					=> 'gardenlogin_customization[login_hearder_message_link]',
    // ));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_head_color]', array(
    //		 'default'					 => '#17a8e3',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control(
    //		 new MCMS_Customize_Color_Control(
    //				 $mcms_customize,
    //				 'login_head_color',
    //				 array(
    //						 'label'		 => __( 'Header Text Color:', 'gardenlogin' ),
    //						 'section'	 => 'section_head',
    //						 'priority'	=> 10,
    //						 'settings'	=> 'gardenlogin_customization[login_head_color]'
    //		 )));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_head_color_hover]', array(
    //		 // 'default'					 => '#17a8e3',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control(
    //		 new MCMS_Customize_Color_Control(
    //				 $mcms_customize,
    //				 'login_head_color_hover',
    //				 array(
    //						 'label'		 => __( 'Header Text Hover Color:', 'gardenlogin' ),
    //						 'section'	 => 'section_head',
    //						 'priority'	=> 15,
    //						 'settings'	=> 'gardenlogin_customization[login_head_color_hover]'
    //		 )));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_head_font_size]', array(
    //		 'default'					 => '13px;',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control( 'login_head_font_size', array(
    //		 'label'						 => __( 'Text Font Size:', 'gardenlogin' ),
    //		 'section'					 => 'section_head',
    //		 'priority'					=> 20,
    //		 'settings'					=> 'gardenlogin_customization[login_head_font_size]',
    // ));
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[login_head_bg_color]', array(
    //		 // 'default'					 => '#17a8e3',
    //		 'type'							=> 'option',
    //		 'capability'				=> 'edit_myskin_options',
    // ));
    //
    // $mcms_customize->add_control(
    //		 new MCMS_Customize_Color_Control(
    //				 $mcms_customize,
    //				 'login_head_bg_color',
    //				 array(
    //						 'label'		 => __( 'Header Background Color:', 'gardenlogin' ),
    //						 'section'	 => 'section_head',
    //						 'priority'	=> 25,
    //						 'settings'	=> 'gardenlogin_customization[login_head_bg_color]'
    //		 )));

    //	=============================
    //	= Custom Header Login menu	=
    //	=============================
    // $menuVals	 = array();
    // $menus			= get_registered_nav_menus();
    //
    // foreach ( $menus as $location => $name ) {
    //   $menuVals[$location] =	 $name ;
    // }
    // $mcms_customize->add_section(
    // 'customize_menu_section',
    // array(
    //   'title'				 => __( 'Login Page Menus', 'gardenlogin' ),
    //   'description'	 => '',
    //   'priority'			=> 32,
    //   'panel'				 => 'gardenlogin_panel',
    // ));
    //
    // $mcms_customize->add_setting('gardenlogin_customization[header_login_menu]', array(
    //   'capability' => 'edit_myskin_options',
    //   'type'			 => 'option',
    // ));
    //
    // $mcms_customize->add_control('header_login_menu', array(
    //   'settings' => 'gardenlogin_customization[header_login_menu]',
    //   'label'		=> __( 'Display Header Menu?', 'gardenlogin'),
    //   'section'	=> 'customize_menu_section',
    //   'priority' => 5,
    //   'type'		 => 'checkbox',
    // ));
    //
    // $mcms_customize->add_setting('gardenlogin_customization[customize_login_menu]', array(
    //   'capability'		 => 'edit_myskin_options',
    //   'type'					 => 'option',
    //
    // ));
    // $mcms_customize->add_control( 'customize_login_menu', array(
    //   'settings' => 'gardenlogin_customization[customize_login_menu]',
    //   'label'	 => __( 'Select Menu for Header:', 'gardenlogin' ),
    //   'section' => 'customize_menu_section',
    //   'type'		=> 'select',
    //   'priority' => 10,
    //   'choices'		=> $menuVals,
    // ));
    //
    // $mcms_customize->add_setting('gardenlogin_customization[footer_login_menu]', array(
    //   'capability' => 'edit_myskin_options',
    //   'type'			 => 'option',
    // ));
    //
    // $mcms_customize->add_control('footer_login_menu', array(
    //   'settings' => 'gardenlogin_customization[footer_login_menu]',
    //   'label'		=> __( 'Display Footer Menu?', 'gardenlogin' ),
    //   'section'	=> 'customize_menu_section',
    //   'priority' => 15,
    //   'type'		 => 'checkbox',
    // ));
    //
    // $mcms_customize->add_setting('gardenlogin_customization[customize_login_footer_menu]', array(
    //   'capability'		 => 'edit_myskin_options',
    //   'type'					 => 'option',
    //
    // ));
    // $mcms_customize->add_control( 'customize_login_footer_menu', array(
    //   'settings' => 'gardenlogin_customization[customize_login_footer_menu]',
    //   'label'	 => __( 'Select Menu:', 'gardenlogin' ),
    //   'section' => 'customize_menu_section',
    //   'priority' => 20,
    //   'type'		=> 'select',
    //   'choices'		=> $menuVals,
    // ));

    //	=============================
    //	= Section for Form Footer	 =
    //	=============================
    $mcms_customize->add_section( 'section_fotter', array(
      'title'				 => __( 'Form Footer', 'gardenlogin' ),
      'description'	 => '',
      'priority'			=> 40,
      'panel'				 => 'gardenlogin_panel',
    ) );

    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_fotter', 3, 4 );

    $mcms_customize->add_setting( 'gardenlogin_customization[footer_display_text]', array(
      'default'					=> true,
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ));

    /**
     * [Enable / Disabe Footer Text with GardenLogin_Radio_Control]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[footer_display_text]', array(
      'settings'    => 'gardenlogin_customization[footer_display_text]',
  		'label'	      => __( 'Enable Footer Text:', 'gardenlogin' ),
  		'section'     => 'section_fotter',
      'priority'    => 5,
  		'type'        => 'ios',// light, ios, flat
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_text]', array(
      'default'					=> 'Lost your password?',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[login_footer_text]', array(
      'label'						 => __( 'Lost Password Text:', 'gardenlogin' ),
      'section'					 => 'section_fotter',
      'priority'				 => 10,
      'settings'				 => 'gardenlogin_customization[login_footer_text]',
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_text_decoration]', array(
      'default'					=> 'none',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'

    ) );
    $mcms_customize->add_control( 'gardenlogin_customization[login_footer_text_decoration]', array(
      'settings'				=> 'gardenlogin_customization[login_footer_text_decoration]',
      'label'						=> 'Select Text Decoration:',
      'section'					=> 'section_fotter',
      'priority'				=> 15,
      'type'						=> 'select',
      'choices'					=> array(
        'none'					=> 'none',
        'overline'			=> 'overline',
        'line-through'	=> 'line-through',
        'underline'		  => 'underline',
      ),
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_color]', array(
      // 'default'					=> '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_footer_color]', array(
      'label'		    => __( 'Footer Text Color:', 'gardenlogin' ),
      'section'	    => 'section_fotter',
      'priority'	  => 20,
      'settings'	  => 'gardenlogin_customization[login_footer_color]'
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_color_hover]', array(
      // 'default'				  => '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_footer_color_hover]', array(
      'label'		 => __( 'Footer Text Hover Color:', 'gardenlogin' ),
      'section'	 => 'section_fotter',
      'priority'	=> 25,
      'settings'	=> 'gardenlogin_customization[login_footer_color_hover]'
    ) ) );

    $mcms_customize->add_setting( "gardenlogin_customization[login_footer_font_size]", array(
      'default'               => '13',
      'type' 			            => 'option',
      'capability'		        => 'manage_options',
      'transport'             => 'postMessage',
      'sanitize_callback'     => 'absint',
    ) );

    /**
     * [ Change login_footer_font_size Input fields with GardenLogin_Range_Control ]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Range_Control( $mcms_customize, "gardenlogin_customization[login_footer_font_size]", array(
      'type'           => 'gardenlogin-range',
      'label'          => __( 'Text Font Size:', 'gardenlogin' ),
      'section'        => 'section_fotter',
      'settings'       => "gardenlogin_customization[login_footer_font_size]",
      'default'        => '13',
      'priority'			 => 30,
      'input_attrs'    => array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => 'px' )
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_bg_color]', array(
      // 'default'					 => '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_footer_bg_color]', array(
      'label'		 => __( 'Footer Background Color:', 'gardenlogin' ),
      'section'	 => 'section_fotter',
      'priority'	=> 35,
      'settings'	=> 'gardenlogin_customization[login_footer_bg_color]'
    ) ) );

    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_fotter', 0, 36 );

    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_fotter', 4, 40 );

    $mcms_customize->add_setting( 'gardenlogin_customization[back_display_text]', array(
      'default'					=> true,
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    /**
     * [Enable / Disabe Footer Text with GardenLogin_Radio_Control]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[back_display_text]', array(
      'settings'    => 'gardenlogin_customization[back_display_text]',
  		'label'	      => __( 'Enable "Back to" Text:', 'gardenlogin' ),
  		'section'     => 'section_fotter',
      'priority'    => 45,
  		'type'        => 'ios',// light, ios, flat
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_back_text_decoration]', array(
      'default'					=> 'none',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'

    ) );
    $mcms_customize->add_control( 'gardenlogin_customization[login_back_text_decoration]', array(
      'settings'				 => 'gardenlogin_customization[login_back_text_decoration]',
      'label'						 => __( '"Back to" Text Decoration:', 'gardenlogin' ),
      'section'					 => 'section_fotter',
      'priority'				 => 50,
      'type'						 => 'select',
      'choices'					 => array(
        'none'					=> 'none',
        'overline'			=> 'overline',
        'line-through'	=> 'line-through',
        'underline'		  => 'underline',
      ),
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_back_color]', array(
      // 'default'					=> '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_back_color]', array(
      'label'		 => __( '"Back to" Text Color:', 'gardenlogin' ),
      'section'	 => 'section_fotter',
      'priority'	=> 55,
      'settings'	=> 'gardenlogin_customization[login_back_color]'
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_back_color_hover]', array(
      // 'default'					 => '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_back_color_hover]', array(
      'label'		  => __( '"Back to" Text Hover Color:', 'gardenlogin' ),
      'section'	  => 'section_fotter',
      'priority'	=> 60,
      'settings'	=> 'gardenlogin_customization[login_back_color_hover]'
    ) ) );

    $mcms_customize->add_setting( "gardenlogin_customization[login_back_font_size]", array(
      'default'               => '13',
      'type' 			            => 'option',
      'capability'		        => 'manage_options',
      'transport'             => 'postMessage',
      'sanitize_callback'     => 'absint',
    ) );

    /**
     * [ Change login_back_font_size Input fields with GardenLogin_Range_Control ]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Range_Control( $mcms_customize, "gardenlogin_customization[login_back_font_size]", array(
      'type'           => 'gardenlogin-range',
      'label'          => __( '"Back to" Text Font Size:', 'gardenlogin' ),
      'section'        => 'section_fotter',
      'settings'       => "gardenlogin_customization[login_back_font_size]",
      'default'        => '13',
      'priority'			 => 65,
      'input_attrs'    => array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => 'px' )
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_back_bg_color]', array(
      // 'default'					 => '#17a8e3',
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( new MCMS_Customize_Color_Control( $mcms_customize, 'gardenlogin_customization[login_back_bg_color]', array(
      'label'		        => __( '"Back to" Background Color:', 'gardenlogin' ),
      'section'	        => 'section_fotter',
      'priority'	      => 70,
      'settings'	      => 'gardenlogin_customization[login_back_bg_color]'
    ) ) );

    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_fotter', 1, 71 );

    $this->gardenlogin_group_setting( $mcms_customize, $group_control, $group_label, $group_info, 'section_fotter', 5, 72 );

    /**
     * [Enable / Disabe Footer Text with GardenLogin_Radio_Control]
     * @since 1.1.3
     */
    $mcms_customize->add_setting( 'gardenlogin_customization[login_copy_right_display]', array(
      'default'          => false,
      'type'             => 'option',
      'capability'		   => 'manage_options',
      'transport'        => 'postMessage'
    ) );
    $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[login_copy_right_display]', array(
      'settings'    => 'gardenlogin_customization[login_copy_right_display]',
  		'section'     => 'section_fotter',
      'priority'    => 73,
  		'type'        => 'ios',// light, ios, flat
  		'label'	      => __( 'Enable Copyright Note:', 'gardenlogin' ),
    ) ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[login_footer_copy_right]', array(
      'default'					=> sprintf( __('© %1$s %2$s, All Rights Reserved.', 'gardenlogin'), date("Y"), get_bloginfo('name') ),
      'type'						=> 'option',
      'capability'			=> 'manage_options',
      'transport'       => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[login_footer_copy_right]', array(
      'label'						 => __( 'Copyright Note:', 'gardenlogin' ),
      'type'						 => 'textarea',
      'section'					 => 'section_fotter',
      'priority'				 => 75,
      'settings'				 => 'gardenlogin_customization[login_footer_copy_right]'
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[gardenlogin_show_love]', array(
      'default'          => true,
      'type'             => 'option',
      'capability'		   => 'manage_options',
      'transport'        => 'postMessage'
    ) );

    /**
     * [Enable / Disabe Footer Text with GardenLogin_Radio_Control]
     * @since 1.0.1
     * @version 1.0.23
     */
    $mcms_customize->add_control( new GardenLogin_Radio_Control( $mcms_customize, 'gardenlogin_customization[gardenlogin_show_love]', array(
      'settings'    => 'gardenlogin_customization[gardenlogin_show_love]',
  		'section'     => 'section_fotter',
      'priority'    => 80,
  		'type'        => 'ios',// light, ios, flat
  		'label'	      => __( 'Show some Love. Please help others learn about this free module by placing small link in footer. Thank you very much!', 'gardenlogin' ),
    ) ) );

    /**
     * [Love position on footer.]
     * @since 1.1.3
     */
    $mcms_customize->add_setting( 'gardenlogin_customization[show_love_position]', array(
      'default'				=> 'right',
      'type'					=> 'option',
      'capability'		=> 'manage_options',
      'transport'     => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[show_love_position]', array(
      'label'					=> __( 'Love Position:', 'gardenlogin' ),
      'section'				=> 'section_fotter',
      'priority'			=> 85,
      'settings'			=> 'gardenlogin_customization[show_love_position]',
      'type'					=> 'radio',
      'choices'				=> array(
        'left'			=> 'Left',
        'right'			=> 'Right',
      ),
    ) );
    $this->gardenlogin_hr_setting( $mcms_customize, $close_control, 'section_fotter', 2, 90 );

    //	=============================
    //	= Section for Custom CSS/JS	=
    //	=============================
    $mcms_customize->add_section(
    'gardenlogin_custom_css_js',
    array(
      'title'				      => __( 'Custom CSS/JS', 'gardenlogin' ),
      'description'	      => '',
      'priority'		      => 50,
      'panel'				      => 'gardenlogin_panel',
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[gardenlogin_custom_css]', array(
      'type'						  => 'option',
      'capability'			  => 'manage_options',
      'transport'         => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[gardenlogin_custom_css]', array(
      'label'						  => __( 'Customize CSS:', 'gardenlogin' ),
      'type'						  => 'textarea',
      'section'					  => 'gardenlogin_custom_css_js',
      'priority'				  => 5,
      'settings'				  => 'gardenlogin_customization[gardenlogin_custom_css]',
      'description'       => sprintf( __( 'Custom CSS doen\'t make effect live. For preview please save the setting and visit %1$s login%2$s page or after save refresh the customizer.', "gardenlogin" ), '<a href="' . mcms_login_url() .'"title="Login" target="_blank">', '</a>')
    ) );

    $mcms_customize->add_setting( 'gardenlogin_customization[gardenlogin_custom_js]', array(
      'type'						   => 'option',
      'capability'			   => 'manage_options',
      'transport'          => 'postMessage'
    ) );

    $mcms_customize->add_control( 'gardenlogin_customization[gardenlogin_custom_js]', array(
      'label'						   => __( 'Customize JS:', 'gardenlogin' ),
      'type'						   => 'textarea',
      'section'					   => 'gardenlogin_custom_css_js',
      'priority'				   => 10,
      'settings'				   => 'gardenlogin_customization[gardenlogin_custom_js]',
      'description'        => sprintf( __( 'Custom JS doen\'t make effect live. For preview please save the setting and visit %1$s login%2$s page or after save refresh the customizer.', "gardenlogin" ), '<a href="' . mcms_login_url() .'"title="Login" target="_blank">', '</a>')
    ) );
    //	=============================
    //	= Section for Custom JS		 =
    //	=============================
    // $mcms_customize->add_section(
    // 'section_js',
    // array(
    //   'title'				       => __( 'Custom JS', 'gardenlogin' ),
    //   'description'	       => '',
    //   'priority'		       => 55,
    //   'panel'				       => 'gardenlogin_panel',
    // ) );
    //
    // $mcms_customize->add_setting( 'gardenlogin_customization[gardenlogin_custom_js]', array(
    //   'type'						   => 'option',
    //   'capability'			   => 'manage_options',
    //   'transport'          => 'postMessage'
    // ) );
    //
    // $mcms_customize->add_control( 'gardenlogin_customization[gardenlogin_custom_js]', array(
    //   'label'						   => __( 'Customize JS', 'gardenlogin' ),
    //   'type'						   => 'textarea',
    //   'section'					   => 'section_js',
    //   'priority'				   => 5,
    //   'settings'				   => 'gardenlogin_customization[gardenlogin_custom_js]',
    //   'description'        => sprintf( __( 'Custom JS doen\'t make effect live. For preview please save the setting and visit %1$s login%2$s page or after save refresh the customizer.', "gardenlogin" ), '<a href="' . mcms_login_url() .'"title="Login" target="_blank">', '</a>')
    // ) );
  }

  /**
  * Manage the Login Footer Links
  *
  * @since	1.0.0
  * @version 1.1.3
  * * * * * * * * * * * * * * * */
  public function login_page_custom_footer() {

    /**
     * Add brand postion class.
     * @since 1.1.3
     */
    $position = ''; // Empty variable for storing position class.
    if ( $this->gardenlogin_key ) {
      if ( isset( $this->gardenlogin_key['show_love_position'] ) && $this->gardenlogin_key['show_love_position'] == 'left' ) {
        $position = ' love-postion';
      }
    }

    if ( empty( $this->gardenlogin_key ) || ( isset( $this->gardenlogin_key['gardenlogin_show_love'] ) &&  $this->gardenlogin_key['gardenlogin_show_love'] != '' ) ) {
      echo '<div class="gardenlogin-show-love' . $position . '">' . __( 'Powered by:', 'gardenlogin' ) . ' <a href="https://mandarincms.com" target="_blank">Mandarin CMS</a></div>';
    } elseif ( empty( $this->gardenlogin_key ) || ( ! isset( $this->gardenlogin_key['gardenlogin_show_love'] ) ) ) {
      echo '<div class="gardenlogin-show-love' . $position . '">' . __( 'Powered by:', 'gardenlogin' ) . ' <a href="https://mandarincms.com" target="_blank">Mandarin CMS</a></div>';
    }

    echo '<div class="footer-wrapper">';
    echo '<div class="footer-cont">';

    if ( $this->gardenlogin_key ) {

      // echo '</div></div>';


      //   if ( array_key_exists( 'footer_login_menu', $this->gardenlogin_key ) && checked( $this->gardenlogin_key['footer_login_menu'], true, false ) ) {
      //
      //     mcms_nav_menu( array(
      //       'myskin_location' => $this->gardenlogin_key['customize_login_footer_menu'],
      //       'container' => false,
      //       'menu_class' => 'loginFooterMenu',
      //       'echo' => true,
      //     )
      //   );
      //
      // }

      if ( array_key_exists( 'login_copy_right_display', $this->gardenlogin_key ) && true == $this->gardenlogin_key['login_copy_right_display'] ) {
        if ( array_key_exists( 'login_footer_copy_right', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['login_footer_copy_right'] ) ) {
          echo '<div class="copyRight">'.$this->gardenlogin_key['login_footer_copy_right'].'</div>';
        } else {
          echo '<div class="copyRight">'. sprintf( __('© %1$s %2$s, All Rights Reserved.', 'gardenlogin'), date("Y"), get_bloginfo('name') ) .'</div>';
        }
      }

    }
    echo '</div></div>';
  }

  /**
  * Manage the Login Head
  *
  * @since	1.0.0
  * * * * * * * * * * * */
  public function login_page_custom_head() {

		add_filter( 'gettext', array( $this, 'change_lostpassword_message' ), 20, 3 );
    add_filter( 'gettext', array( $this, 'change_username_label' ), 20, 3 );
    add_filter( 'gettext', array( $this, 'change_password_label' ), 20, 3 );
    // Include CSS File in heared.
    mcms_enqueue_script( 'jquery' );
    include( LOGINPRESS_DIR_PATH . 'css/style-presets.php' );
    include( LOGINPRESS_DIR_PATH . 'css/style-login.php' );

    if ( $this->gardenlogin_key && array_key_exists( 'header_login_menu', $this->gardenlogin_key ) ) {

      // echo '<div class="header-wrapper">';
      // echo '<div class="header-cell">';
      //   if ( array_key_exists( 'header_login_menu', $this->gardenlogin_key ) && checked( $this->gardenlogin_key['header_login_menu'], true, false ) ) {
      //
      //     mcms_nav_menu( array(
      //       'myskin_location' => $this->gardenlogin_key['customize_login_menu'],
      //       'container' => false,
      //       'menu_class' => 'loginHeaderMenu',
      //       'echo' => true,
      //     )
      //   );
      // }
      // echo '</div></div><div class="login-wrapper"><div class="login-cell">';
    }
  }
  /**
  * Set Redirect Path of Logo
  *
  * @since	1.0.0
  * @return mixed
  * * * * * * * * * * * * * */
  public function login_page_logo_url() {

    if ( $this->gardenlogin_key && array_key_exists( 'customize_logo_hover', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['customize_logo_hover'] ) ) {
      return $this->gardenlogin_key["customize_logo_hover"];
    } else {
      return	home_url();
    }
  }

  /**
  * Remove the filter login_errors from woocommerce login form.
  *
  * @since	1.0.16
  * @return errors
  * * * * * * * * * * * * */
  function gardenlogin_woo_login_errors( $validation_error, $arg1, $arg2 ) {

      remove_filter( 'login_errors', array( $this, 'login_error_messages' ) );
      return $validation_error;
  }


  /**
  * Set hover Title of Logo
  *
  * @since	1.0.0
  * @return mixed
  * * * * * * * * * * * * */
  public function login_page_logo_title() {

    if ( $this->gardenlogin_key && array_key_exists( 'customize_logo_hover_title', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['customize_logo_hover_title'] ) ) {
      return $this->gardenlogin_key["customize_logo_hover_title"];
    } else {
      return	home_url();
    }
  }

  /**
  * Set Errors Messages to Show off
  *
  * @param	$error
  * @since	1.0.0
  * @return string
  * * * * * * * * * * * * * * * * */
  public function login_error_messages($error) {

    global $errors;

    if ( isset( $errors ) ){

        $error_codes = $errors->get_error_codes();

        if ( $this->gardenlogin_key ) {

            $invalid_usrname = array_key_exists( 'incorrect_username', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['incorrect_username'] ) ? $this->gardenlogin_key['incorrect_username'] : sprintf( __( '%1$sError:%2$s Invalid Username.', 'gardenlogin' ), '<strong>', '</strong>' );

            $invalid_pasword = array_key_exists( 'incorrect_password', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['incorrect_password'] ) ? $this->gardenlogin_key['incorrect_password'] : sprintf( __( '%1$sError:%2$s Invalid Password.', 'gardenlogin' ), '<strong>', '</strong>' );

            $empty_username = array_key_exists( 'empty_username', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['empty_username'] ) ? $this->gardenlogin_key['empty_username'] : sprintf( __( '%1$sError:%2$s The username field is empty.', 'gardenlogin' ), '<strong>', '</strong>' );

            $empty_password = array_key_exists( 'empty_password', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['empty_password'] ) ? $this->gardenlogin_key['empty_password'] : sprintf( __( '%1$sError:%2$s The password field is empty.', 'gardenlogin' ), '<strong>', '</strong>' );

            $invalid_email   = array_key_exists( 'invalid_email', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['invalid_email'] ) ? $this->gardenlogin_key['invalid_email'] : sprintf( __( '%1$sError:%2$s The email address isn\'t correct..', 'gardenlogin' ), '<strong>', '</strong>' );

            $empty_email     = array_key_exists( 'empty_email', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['empty_email'] ) ? $this->gardenlogin_key['empty_email'] : sprintf( __( '%1$sError:%2$s Please type your email address.', 'gardenlogin' ), '<strong>', '</strong>' );

            $username_exists  = array_key_exists( 'username_exists', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['username_exists'] ) ? $this->gardenlogin_key['username_exists'] : sprintf( __( '%1$sError:%2$s This username is already registered. Please choose another one.', 'gardenlogin' ), '<strong>', '</strong>' );

            $email_exists  = array_key_exists( 'email_exists', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['email_exists'] ) ? $this->gardenlogin_key['email_exists'] : sprintf( __( '%1$sError:%2$s This email is already registered, please choose another one.', 'gardenlogin' ), '<strong>', '</strong>' );

            $invalidcombo   = array_key_exists( 'invalidcombo_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['invalidcombo_message'] ) ? $this->gardenlogin_key['invalidcombo_message'] : sprintf( __( '%1$sError:%2$s Invalid username or email.', 'gardenlogin' ), '<strong>', '</strong>' );

            if ( in_array( 'invalid_username',    $error_codes ) ) return $invalid_usrname;

            if ( in_array( 'incorrect_password',  $error_codes ) ) return $invalid_pasword;

            if ( in_array( 'empty_username',      $error_codes ) ) return $empty_username;

            if ( in_array( 'empty_password',      $error_codes ) ) return $empty_password;

            // registeration Form enteries.
            if ( in_array( 'invalid_email',       $error_codes ) ) return $invalid_email;

            if ( in_array( 'empty_email',         $error_codes ) ) return "</br>" . $empty_email;

            if ( in_array( 'username_exists',     $error_codes ) ) return $username_exists;

            if ( in_array( 'email_exists',        $error_codes ) ) return $email_exists;

            // forget password entery.
            if ( in_array( 'invalidcombo',        $error_codes ) ) return $invalidcombo;
        }
    }

    return $error;
  }

  /**
  * Change Lost Password Text from Form
  *
  * @param	$text
  * @since	1.0.0
  * @version 1.0.21
  * @return mixed
  * * * * * * * * * * * * * * * * * * */
  public function change_lostpassword_message ( $translated_text, $text, $domain ) {

		if ( is_array( $this->gardenlogin_key ) && array_key_exists( 'login_footer_text', $this->gardenlogin_key ) && $text == 'Lost your password?'  && 'default' == $domain && trim( $this->gardenlogin_key['login_footer_text'] ) ) {

			return trim( $this->gardenlogin_key['login_footer_text'] );
		}

    return $translated_text;
  }
  /**
   * Change Username Label from Form.
   * @param  [type] $translated_text [description]
   * @param  [type] $text            [description]
   * @param  [type] $domain          [description]
   * @return string
   * @since 1.1.3
   */
  public function change_username_label( $translated_text, $text, $domain ){

    if ( $this->gardenlogin_key ) {
      $default = 'Username or Email Address';
  		$options = $this->gardenlogin_key;
      $gardenlogin_setting = get_option( 'gardenlogin_setting' );

  		// $label   = array_key_exists( 'form_username_label', $options ) ? $options['form_username_label'] : '';
      $login_order 	= isset( $gardenlogin_setting['login_order'] ) ? $gardenlogin_setting['login_order'] : '';

  		// If the option does not exist, return the text unchanged.
  		if ( ! $gardenlogin_setting && $default === $text ) {
  			return $translated_text;
  		}

  		// If options exsit, then translate away.
  		if ( $gardenlogin_setting && $default === $text ) {

  			// Check if the option exists.
  			if ( '' != $login_order ) {
          if ( 'username' == $login_order ) {
            $label = __( 'Username', 'gardenlogin' );
          } elseif ( 'email' == $login_order ) {
            $label = __( 'Email Address', 'gardenlogin' );
          } else {
            $label = 'Username or Email Address';
          }
  				$translated_text = esc_html( $label );
  			} else {
  				return $translated_text;
  			}
  		}
    }
    return $translated_text;
  }
  /**
   * Change Password Label from Form.
   * @param  [type] $translated_text [description]
   * @param  [type] $text            [description]
   * @param  [type] $domain          [description]
   * @return string
   * @since 1.1.3
   */
  public function change_password_label( $translated_text, $text, $domain ) {

			if ( $this->gardenlogin_key ) {
        $default = 'Password';
        $options = $this->gardenlogin_key;
        $label   = array_key_exists( 'form_password_label', $options ) ? $options['form_password_label'] : '';

  			// If the option does not exist, return the text unchanged.
  			if ( ! $options && $default === $text ) {
  				return $translated_text;
  			}

  			// If options exsit, then translate away.
  			if ( $options && $default === $text ) {

  				// Check if the option exists.
  				if ( array_key_exists( 'form_password_label', $options ) ) {
  					$translated_text = esc_html( $label );
  				} else {
  					return $translated_text;
  				}
  			}
      }
      return $translated_text;
		}

  /**
  * Manage Welcome Messages
  *
  * @param	$message
  * @since	1.0.0
  * @return string
  * * * * * * * * * * * * */
  public function change_welcome_message ($message) {

    if ( $this->gardenlogin_key ) {

      //Check, User Logedout.
      if ( isset( $_GET['loggedout'] ) && TRUE == $_GET['loggedout'] ) {

        if ( array_key_exists( 'logout_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['logout_message'] ) ) {

          $gardenlogin_message = $this->gardenlogin_key['logout_message'];
        }
      }

      //Logged In messages.
      else if ( strpos( $message, __( "Please enter your username or email address. You will receive a link to create a new password via email." ) ) == true ) {

        if ( array_key_exists( 'lostpwd_welcome_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['lostpwd_welcome_message'] ) ) {

          $gardenlogin_message = $this->gardenlogin_key['lostpwd_welcome_message'];
        }
      }

      else if( strpos( $message, __( "Register For This Site" ) ) == true ) {

        if ( array_key_exists( 'register_welcome_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['register_welcome_message'] ) ) {

          $gardenlogin_message = $this->gardenlogin_key['register_welcome_message'];
        }
      }

      // @since 1.0.18
      // else if ( strpos( $message, __( "Enter your new password below." ) ) == true ) {
      //
      //   if ( array_key_exists( 'reset_hint_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['reset_hint_message'] ) ) {
      //
      //     $gardenlogin_message = $this->gardenlogin_key['reset_hint_message'];
      //   }
      // }

      // @since 1.0.18
      else if ( strpos( $message, __( "Your password has been reset." ) ) == true ) {

        // if ( array_key_exists( 'after_reset_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['after_reset_message'] ) ) {

          $gardenlogin_message = __( 'Your password has been reset.' ) . ' <a href="' . esc_url( mcms_login_url() ) . '">' . __( 'Log in' ) . '</a></p>';
        // }
      }

      else {
        if ( array_key_exists( 'welcome_message', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['welcome_message'] ) ) {

          $gardenlogin_message = $this->gardenlogin_key['welcome_message'];
        }
      }


      return ! empty( $gardenlogin_message ) ? "<p class='custom-message'>" . $gardenlogin_message. "</p>" : $message;
    }
  }

  /**
  * Hook to Redirect Page for Customize
  *
  * @since	1.1.3
  * * * * * * * * * * * * * * * * * * */
  public function redirect_to_custom_page() {
    if ( ! empty($_GET['page'] ) ) {

      if( ( $_GET['page'] == "abw" ) || ( $_GET['page'] == "gardenlogin" ) ) {

        if ( is_multisite() ) { // if subdirectories are used in multisite.

          $gardenlogin_obj 	= new GardenLogin();
      		$gardenlogin_page = $gardenlogin_obj->get_gardenlogin_page();

					$page = get_permalink( $gardenlogin_page );

					// Generate the redirect url.
					$url = add_query_arg(
						array(
							'autofocus[panel]' => 'gardenlogin_panel',
							'url'              => rawurlencode( $page ),
						),
						admin_url( 'customize.php' )
					);

					mcms_safe_redirect( $url );

        } else {

          mcms_redirect( get_admin_url() . "customize.php?url=" . mcms_login_url() );
        }
      }
    }
  }

  /**
  * Redirect to the Admin Panel After Closing GardenLogin Customizer
  *
  * @since	1.0.0
  * @return null
  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
  public function menu_url() {

    global $submenu;

    $parent = 'index.php';
    $page	 = 'abw';

    // Create specific url for login view
    $login_url = mcms_login_url();
    $url			 = add_query_arg(
    array(
      'url'		=> urlencode( $login_url ),
      'return' => admin_url( 'myskins.php' ),
    ),
    admin_url( 'customize.php' )
    );

    // If is Not Design Menu, return
    if ( ! isset( $submenu[ $parent ] ) ) {
      return NULL;
    }

    foreach ( $submenu[ $parent ] as $key => $value ) {
      // Set new URL for menu item
      if ( $page === $value[ 2 ] ) {
        $submenu[ $parent ][ $key ][ 2 ] = $url;
        break;
      }
    }
  }
}

?>
