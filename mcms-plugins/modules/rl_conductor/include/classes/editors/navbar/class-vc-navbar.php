<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Renders navigation bar for Editors.
 */
class Vc_Navbar implements Vc_Render {
	/**
	 * @var array
	 */
	protected $controls = array(
		'add_element',
		'templates',
		'save_backend',
		'preview',
		'frontend',
		'custom_css',
		'fullscreen',
		'windowed',
	);
	/**
	 * @var string
	 */
	protected $brand_url = 'http://vc.jiiworks.net/?utm_campaign=VCmodule&utm_source=vc_user&utm_medium=backend_editor';
	/**
	 * @var string
	 */
	protected $css_class = 'vc_navbar';
	/**
	 * @var string
	 */
	protected $controls_filter_name = 'vc_nav_controls';
	/**
	 * @var bool|MCMS_Post
	 */
	protected $post = false;

	/**
	 * @param MCMS_Post $post
	 */
	public function __construct( MCMS_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Generate array of controls by iterating property $controls list.
	 * vc_filter: vc_nav_controls - hook to override list of controls
	 * @return array - list of arrays witch contains key name and html output for button.
	 */
	public function getControls() {
		$list = array();
		foreach ( $this->controls as $control ) {
			$method = vc_camel_case( 'get_control_' . $control );
			if ( method_exists( $this, $method ) ) {
				$list[] = array( $control, $this->$method() . "\n" );
			}
		}

		return apply_filters( $this->controls_filter_name, $list );
	}

	/**
	 * Get current post.
	 * @return null|MCMS_Post
	 */
	public function post() {
		if ( $this->post ) {
			return $this->post;
		}

		return get_post();
	}

	/**
	 * Render template.
	 */
	public function render() {
		vc_include_template( 'editors/navbar/navbar.tpl.php', array(
			'css_class' => $this->css_class,
			'controls' => $this->getControls(),
			'nav_bar' => $this,
			'post' => $this->post(),
		) );
	}

	/**
	 * vc_filter: vc_nav_front_logo - hook to override visual composer logo
	 * @return mixed|void
	 */
	public function getLogo() {
		$output = '<a id="vc_logo" class="vc_navbar-brand" title="' . __( 'RazorLeaf Conductor', 'rl_conductor' )
		          . '" href="' . esc_attr( $this->brand_url ) . '" target="_blank">'
		          . __( 'RazorLeaf Conductor', 'rl_conductor' ) . '</a>';

		return apply_filters( 'vc_nav_front_logo', $output );
	}

	/**
	 * @return string
	 */
	public function getControlCustomCss() {
		if ( ! vc_user_access()->part( 'post_settings' )->can()->get() ) {
			return '';
		}

		return '<li class="vc_pull-right"><a id="vc_post-settings-button" href="javascript:;" class="vc_icon-btn vc_post-settings" title="'
		       . __( 'Page settings', 'rl_conductor' ) . '">'
		       . '<span id="vc_post-css-badge" class="vc_badge vc_badge-custom-css" style="display: none;">' . __( 'CSS', 'rl_conductor' )
			   . '</span><i class="vc-composer-icon vc-c-icon-cog"></i></a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlFullscreen() {
		return '<li class="vc_show-mobile vc_pull-right">'
		       . '<a id="vc_fullscreen-button" class="vc_icon-btn vc_fullscreen-button" title="'. __( 'Full screen', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-fullscreen"></i></a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlWindowed() {
		return '<li class="vc_show-mobile vc_pull-right">'
		       . '<a id="vc_windowed-button" class="vc_icon-btn vc_windowed-button" title="'. __( 'Exit full screen', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-fullscreen_exit"></i></a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlAddElement() {
		if ( vc_user_access()
			     ->part( 'shortcodes' )
			     ->checkStateAny( true, 'custom', null )
			     ->get() &&
		     vc_user_access_check_shortcode_all( 'vc_row' ) && vc_user_access_check_shortcode_all( 'vc_column' )
		) {
			return '<li class="vc_show-mobile">'
			       . '	<a href="javascript:;" class="vc_icon-btn vc_element-button" data-model-id="vc_element" id="vc_add-new-element" title="'
			       . '' . __( 'Add new element', 'rl_conductor' ) . '">'
				   . '    <i class="vc-composer-icon vc-c-icon-add_element"></i>'
			       . '	</a>'
			       . '</li>';
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getControlTemplates() {
		if ( ! vc_user_access()->part( 'templates' )->can()->get() ) {
			return '';
		}

		return '<li><a href="javascript:;" class="vc_icon-btn vc_templates-button vc_navbar-border-right"  id="vc_templates-editor-button" title="'
		       . __( 'Templates', 'rl_conductor' ) . '"><i class="vc-composer-icon vc-c-icon-add_template"></i></a></li>';
	}

	/**
	 * @return string
	 */
	public function getControlFrontend() {
		if ( ! vc_enabled_frontend() ) {
			return '';
		}

		return '<li class="vc_pull-right">'
		       . '<a href="' . vc_frontend_editor()->getInlineUrl() . '" class="vc_btn vc_btn-primary vc_btn-sm vc_navbar-btn" id="mcmsb-edit-inline">' . __( 'Frontend', 'rl_conductor' ) . '</a>'
		       . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlPreview() {
		return '';
	}

	/**
	 * @return string
	 */
	public function getControlSaveBackend() {
		return '<li class="vc_pull-right vc_save-backend">'
		       . '<a href="javascript:;" class="vc_btn vc_btn-grey vc_btn-sm vc_navbar-btn vc_control-preview">' . __( 'Preview', 'rl_conductor' ) . '</a>'
		       . '<a class="vc_btn vc_btn-sm vc_navbar-btn vc_btn-primary vc_control-save" id="mcmsb-save-post">' . __( 'Update', 'rl_conductor' ) . '</a>'
		       . '</li>';
	}
}
