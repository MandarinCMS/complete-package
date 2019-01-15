<?php
/**
 * @package MCMSSEO\Admin\Customizer
 */

/**
 * Class with functionality to support MCMS SEO settings in MandarinCMS Customizer.
 */
class MCMSSEO_Customizer {

	/**
	 * @var MCMS_Customize_Manager
	 */
	protected $mcms_customize;

	/**
	 * Construct Method.
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'mcmsseo_customize_register' ) );
	}

	/**
	 * Function to support MandarinCMS Customizer
	 *
	 * @param MCMS_Customize_Manager $mcms_customize Manager class instance.
	 */
	public function mcmsseo_customize_register( $mcms_customize ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->mcms_customize = $mcms_customize;

		$this->breadcrumbs_section();
		$this->breadcrumbs_blog_remove_setting();
		$this->breadcrumbs_separator_setting();
		$this->breadcrumbs_home_setting();
		$this->breadcrumbs_prefix_setting();
		$this->breadcrumbs_archiveprefix_setting();
		$this->breadcrumbs_searchprefix_setting();
		$this->breadcrumbs_404_setting();
	}

	/**
	 * Add the breadcrumbs section to the customizer
	 */
	private function breadcrumbs_section() {
		$this->mcms_customize->add_section(
			'mcmsseo_breadcrumbs_customizer_section', array(
				/* translators: %s is the name of the module */
				'title'           => sprintf( __( '%s Breadcrumbs', 'mandarincms-seo' ), 'Ultimatum SEO' ),
				'priority'        => 999,
				'active_callback' => array( $this, 'breadcrumbs_active_callback' ),
			)
		);

	}

	/**
	 * Returns whether or not the breadcrumbs are active
	 *
	 * @return bool
	 */
	public function breadcrumbs_active_callback() {
		$options = MCMSSEO_Options::get_option( 'mcmsseo_internallinks' );

		return true === ( current_myskin_supports( 'ultimatum-seo-breadcrumbs' ) || $options['breadcrumbs-enable'] );
	}

	/**
	 * Adds the breadcrumbs remove blog checkbox
	 */
	private function breadcrumbs_blog_remove_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-blog-remove]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-blog-remove', array(
					'label'           => __( 'Remove blog page from breadcrumbs', 'mandarincms-seo' ),
					'type'            => 'checkbox',
					'section'         => 'mcmsseo_breadcrumbs_customizer_section',
					'settings'        => 'mcmsseo_internallinks[breadcrumbs-blog-remove]',
					'context'         => '',
					'active_callback' => array( $this, 'breadcrumbs_blog_remove_active_cb' ),
				)
			)
		);
	}

	/**
	 * Returns whether or not to show the breadcrumbs blog remove option
	 *
	 * @return bool
	 */
	public function breadcrumbs_blog_remove_active_cb() {
		return 'page' === get_option( 'show_on_front' );
	}

	/**
	 * Adds the breadcrumbs separator text field
	 */
	private function breadcrumbs_separator_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-sep]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-separator', array(
					'label'    => __( 'Breadcrumbs separator:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-sep]',
					'context'  => '',
				)
			)
		);
	}

	/**
	 * Adds the breadcrumbs home anchor text field
	 */
	private function breadcrumbs_home_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-home]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-home', array(
					'label'    => __( 'Anchor text for the homepage:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-home]',
					'context'  => '',
				)
			)
		);
	}

	/**
	 * Adds the breadcrumbs prefix text field
	 */
	private function breadcrumbs_prefix_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-prefix]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-prefix', array(
					'label'    => __( 'Prefix for breadcrumbs:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-prefix]',
					'context'  => '',
				)
			)
		);
	}

	/**
	 * Adds the breadcrumbs archive prefix text field
	 */
	private function breadcrumbs_archiveprefix_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-archiveprefix]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-archiveprefix', array(
					'label'    => __( 'Prefix for archive pages:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-archiveprefix]',
					'context'  => '',
				)
			)
		);
	}

	/**
	 * Adds the breadcrumbs search prefix text field
	 */
	private function breadcrumbs_searchprefix_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-searchprefix]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-searchprefix', array(
					'label'    => __( 'Prefix for search result pages:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-searchprefix]',
					'context'  => '',
				)
			)
		);
	}

	/**
	 * Adds the breadcrumb 404 prefix text field
	 */
	private function breadcrumbs_404_setting() {
		$this->mcms_customize->add_setting(
			'mcmsseo_internallinks[breadcrumbs-404crumb]', array(
				'default'   => '',
				'type'      => 'option',
				'transport' => 'refresh',
			)
		);

		$this->mcms_customize->add_control(
			new MCMS_Customize_Control(
				$this->mcms_customize, 'mcmsseo-breadcrumbs-404crumb', array(
					'label'    => __( 'Breadcrumb for 404 pages:', 'mandarincms-seo' ),
					'type'     => 'text',
					'section'  => 'mcmsseo_breadcrumbs_customizer_section',
					'settings' => 'mcmsseo_internallinks[breadcrumbs-404crumb]',
					'context'  => '',
				)
			)
		);
	}
}
