<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * RazorLeaf Conductor basic class.
 * @since 4.2
 */
class Vc_Base {
	/**
	 * Shortcode's edit form.
	 *
	 * @since  4.2
	 * @access protected
	 * @var bool|Vc_Shortcode_Edit_Form
	 */
	protected $shortcode_edit_form = false;

	/**
	 * Templates management panel.
	 * @deprecated 4.4 updated to $templates_panel_editor, use Vc_Base::setTemplatesPanelEditor
	 * @since  4.2
	 * @access protected
	 * @var bool|Vc_Templates_Editor
	 */
	protected $templates_editor = false;
	/**
	 * Templates management panel editor.
	 * @since  4.4
	 * @access protected
	 * @var bool|Vc_Templates_Panel_Editor
	 */
	protected $templates_panel_editor = false;
	/**
	 * Post object for VC in Admin.
	 *
	 * @since  4.4
	 * @access protected
	 * @var bool|Vc_Post_Admin
	 */
	protected $post_admin = false;
	/**
	 * Post object for VC.
	 *
	 * @since  4.4.3
	 * @access protected
	 * @var bool|Vc_Post_Admin
	 */
	protected $post = false;
	/**
	 * List of shortcodes map to VC.
	 *
	 * @since  4.2
	 * @access public
	 * @var array MCMSBakeryShortCodeFishBones
	 */
	protected $shortcodes = array();

	/**
	 * @deprecated 4.4 due to autoload logic
	 * @var Vc_Vendors_Manager $vendor_manager
	 */
	protected $vendor_manager;

	/** @var  Vc_Shared_Templates */
	public $shared_templates;

	/**
	 * Load default object like shortcode parsing.
	 *
	 * @since  4.2
	 * @access public
	 */
	public function init() {
		do_action( 'vc_before_init_base' );
		if ( is_admin() ) {
			$this->postAdmin()->init();
		}
		add_filter( 'body_class', array(
			$this,
			'bodyClass',
		) );
		add_filter( 'the_excerpt', array(
			$this,
			'excerptFilter',
		) );
		add_action( 'mcms_head', array(
			$this,
			'addMetaData',
		) );
		add_action( 'mcms_head', array(
			$this,
			'addIEMinimalSupport',
		) );
		if ( is_admin() ) {
			$this->initAdmin();
		} else {
			$this->initPage();
		}
		do_action( 'vc_after_init_base' );
	}

	/**
	 * Post object for interacting with Current post data.
	 * @since 4.4
	 * @return Vc_Post_Admin
	 */
	public function postAdmin() {
		if ( false === $this->post_admin ) {
			require_once vc_path_dir( 'CORE_DIR', 'class-vc-post-admin.php' );
			$this->post_admin = new Vc_Post_Admin();
		}

		return $this->post_admin;
	}

	/**
	 * Build VC for frontend pages.
	 *
	 * @since  4.2
	 * @access public
	 */
	public function initPage() {
		do_action( 'vc_build_page' );
		add_action( 'template_redirect', array(
			$this,
			'frontCss',
		) );
		add_action( 'template_redirect', array(
			'MCMSBMap',
			'addAllMappedShortcodes',
		) );
		add_action( 'mcms_head', array(
			$this,
			'addFrontCss',
		), 1000 );
		add_action( 'mcms_head', array(
			$this,
			'addNoScript',
		), 1000 );
		add_action( 'template_redirect', array(
			$this,
			'frontJsRegister',
		) );
		add_filter( 'the_content', array(
			$this,
			'fixPContent',
		), 11 );
	}

	/**
	 * Load admin required modules and elements
	 *
	 * @since  4.2
	 * @access public
	 */
	public function initAdmin() {
		do_action( 'vc_build_admin_page' );
		// Build settings for admin page;
		//$this->registerAdminJavascript();
		//$this->registerAdminCss();

		// editors actions:
		$this->editForm()->init();
		$this->templatesPanelEditor()->init();
		$this->shared_templates->init();
		// ajax params/shortcode action
		add_action( 'mcms_ajax_mcmsb_single_image_src', array(
			$this,
			'singleImageSrc',
		) ); // @todo move it
		add_action( 'mcms_ajax_mcmsb_gallery_html', array(
			$this,
			'galleryHTML',
		) ); // @todo move it

		// modules list page actions links
		add_filter( 'module_action_links', array(
			$this,
			'moduleActionLinks',
		), 10, 2 );
	}

	/**
	 * Setter for edit form.
	 * @since 4.2
	 *
	 * @param Vc_Shortcode_Edit_Form $form
	 */
	public function setEditForm( Vc_Shortcode_Edit_Form $form ) {
		$this->shortcode_edit_form = $form;
	}

	/**
	 * Get Shortcodes Edit form object.
	 *
	 * @see    Vc_Shortcode_Edit_Form::__construct
	 * @since  4.2
	 * @access public
	 * @return Vc_Shortcode_Edit_Form
	 */
	public function editForm() {
		return $this->shortcode_edit_form;
	}

	/**
	 * Setter for Templates editor.
	 * @deprecated 4.4 updated to panel editor see Vc_Templates_Panel_Editor::__construct
	 * @use setTemplatesPanelEditor
	 * @since 4.2
	 *
	 * @param Vc_Templates_Editor $editor
	 */
	public function setTemplatesEditor( Vc_Templates_Editor $editor ) {
		_deprecated_function( 'Vc_Base::setTemplatesEditor', '4.4 (will be removed in 5.1)', 'Vc_Base::setTemplatesPanelEditor' );
		$this->templates_editor = $editor;
	}

	/**
	 * Setter for Templates editor.
	 * @since 4.4
	 *
	 * @param Vc_Templates_Panel_Editor $editor
	 */
	public function setTemplatesPanelEditor( Vc_Templates_Panel_Editor $editor ) {
		$this->templates_panel_editor = $editor;
	}

	/**
	 * Get templates manager.
	 * @deprecated updated to panel editor see Vc_Templates_Panel_Editor::__construct
	 * @see    Vc_Templates_Editor::__construct
	 * @since  4.2
	 * @access public
	 * @return bool|Vc_Templates_Editor
	 */
	public function templatesEditor() {
		_deprecated_function( 'Vc_Base::templatesEditor', '4.4 (will be removed in 5.1)', 'Vc_Base::templatesPanelEditor' );

		return $this->templates_editor;
	}

	/**
	 * Get templates manager.
	 * @see    Vc_Templates_Panel_Editor::__construct
	 * @since  4.4
	 * @access public
	 * @return bool|Vc_Templates_Panel_Editor
	 */
	public function templatesPanelEditor() {
		return $this->templates_panel_editor;
	}

	/**
	 * Save method for edit_post action.
	 * @deprecated 4.9
	 * @since  4.2
	 * @access public
	 *
	 * @param null $post_id
	 */
	public function save( $post_id = null ) {
		_deprecated_function( '\Vc_Base::save', '4.9 (will be removed in 5.1)', '\Vc_Post_Admin::save' );
	}

	/**
	 * Add new shortcode to Visual composer.
	 *
	 * @see    MCMSBMap::map
	 * @since  4.2
	 * @access public
	 * @deprecated 4.9
	 *
	 * @param array $shortcode - array of options.
	 */
	public function addShortCode( array $shortcode ) {
		_deprecated_function( '\Vc_Base::addShortcode', '4.9 (will be removed in 5.1)', 'vc_map' );
		if ( ! isset( $this->shortcodes[ $shortcode['base'] ] ) ) {
			require_once vc_path_dir( 'SHORTCODES_DIR', 'shortcodes.php' );
			$this->shortcodes[ $shortcode['base'] ] = new MCMSBakeryShortCodeFishBones( $shortcode );
		}

	}

	/**
	 * Get shortcode class instance.
	 *
	 * @see    MCMSBakeryShortCodeFishBones
	 * @since  4.2
	 * @access public
	 *
	 * @param string $tag
	 *
	 * @return Vc_Shortcodes_Manager|null
	 */
	public function getShortCode( $tag ) {
		return Vc_Shortcodes_Manager::getInstance()->setTag( $tag );
	}

	/**
	 * Remove shortcode from shortcodes list of VC.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param $tag - shortcode tag
	 */
	public function removeShortCode( $tag ) {
		remove_shortcode( $tag );
	}

	/**
	 * @todo move it
	 * @since 4.2
	 */
	public function singleImageSrc() {
		// @todo again, this method should be moved (comment added on 4.8)
		vc_user_access()->checkAdminNonce()->validateDie()->mcmsAny( 'edit_posts', 'edit_pages' )->validateDie();

		$image_id = (int) vc_post_param( 'content' );
		$params = vc_post_param( 'params' );
		$post_id = vc_post_param( 'post_id' );
		$img_size = vc_post_param( 'size' );
		$img = '';

		if ( ! empty( $params['source'] ) ) {
			$source = $params['source'];
		} else {
			$source = 'media_library';
		}

		switch ( $source ) {
			case 'media_library':
			case 'featured_image':

				if ( 'featured_image' === $source ) {
					if ( $post_id && has_post_thumbnail( $post_id ) ) {
						$img_id = get_post_thumbnail_id( $post_id );
					} else {
						$img_id = 0;
					}
				} else {
					$img_id = preg_replace( '/[^\d]/', '', $image_id );
				}

				if ( ! $img_size ) {
					$img_size = 'thumbnail';
				}

				if ( $img_id ) {
					$img = mcms_get_attachment_image_src( $img_id, $img_size );
					if ( $img ) {
						$img = $img[0];
					}
				}

				break;

			case 'external_link':
				if ( ! empty( $params['custom_src'] ) ) {
					$img = $params['custom_src'];
				}
				break;
		}

		die( $img );
	}

	/**
	 * @todo move it
	 * @since 4.2
	 */
	public function galleryHTML() {
		// @todo again, this method should be moved (comment added on 4.8)
		vc_user_access()->checkAdminNonce()->validateDie()->mcmsAny( 'edit_posts', 'edit_pages' )->validateDie();

		$images = vc_post_param( 'content' );
		if ( ! empty( $images ) ) {
			echo fieldAttachedImages( explode( ',', $images ) );
		}
		die();
	}

	/**
	 * Set or modify new settings for shortcode.
	 *
	 * This function widely used by MCMSBMap class methods to modify shortcodes mapping
	 *
	 * @since 4.3
	 *
	 * @param $tag
	 * @param $name
	 * @param $value
	 */
	public function updateShortcodeSetting( $tag, $name, $value ) {
		Vc_Shortcodes_Manager::getInstance()->getElementClass( $tag )->setSettings( $name, $value );
	}

	/**
	 * Build custom css styles for page from shortcodes attributes created by VC editors.
	 *
	 * Called by save method, which is hooked by edit_post action.
	 * Function creates meta data for post with the key '_mcmsb_shortcodes_custom_css'
	 * and value as css string, which will be added to the footer of the page.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param $post_id
	 */
	public function buildShortcodesCustomCss( $post_id ) {
		$post = get_post( $post_id );
		/**
		 * vc_filter: vc_base_build_shortcodes_custom_css
		 * @since 4.4
		 */
		$css = apply_filters( 'vc_base_build_shortcodes_custom_css', $this->parseShortcodesCustomCss( $post->post_content ) );
		if ( empty( $css ) ) {
			delete_post_meta( $post_id, '_mcmsb_shortcodes_custom_css' );
		} else {
			update_post_meta( $post_id, '_mcmsb_shortcodes_custom_css', $css );
		}
	}

	/**
	 * Parse shortcodes custom css string.
	 *
	 * This function is used by self::buildShortcodesCustomCss and creates css string from shortcodes attributes
	 * like 'css_editor'.
	 *
	 * @see    MCMSBakeryVisualComposerCssEditor
	 * @since  4.2
	 * @access public
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function parseShortcodesCustomCss( $content ) {
		$css = '';
		if ( ! preg_match( '/\s*(\.[^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $content ) ) {
			return $css;
		}
		MCMSBMap::addAllMappedShortcodes();
		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes );
		foreach ( $shortcodes[2] as $index => $tag ) {
			$shortcode = MCMSBMap::getShortCode( $tag );
			$attr_array = shortcode_parse_atts( trim( $shortcodes[3][ $index ] ) );
			if ( isset( $shortcode['params'] ) && ! empty( $shortcode['params'] ) ) {
				foreach ( $shortcode['params'] as $param ) {
					if ( isset( $param['type'] ) && 'css_editor' === $param['type'] && isset( $attr_array[ $param['param_name'] ] ) ) {
						$css .= $attr_array[ $param['param_name'] ];
					}
				}
			}
		}
		foreach ( $shortcodes[5] as $shortcode_content ) {
			$css .= $this->parseShortcodesCustomCss( $shortcode_content );
		}

		return $css;
	}

	/**
	 * Hooked class method by mcms_head MCMS action to output post custom css.
	 *
	 * Method gets post meta value for page by key '_mcmsb_post_custom_css' and if it is not empty
	 * outputs css string wrapped into style tag.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param int $id
	 */
	public function addPageCustomCss( $id = null ) {
		if ( ! is_singular() ) {
			return;
		}
		if ( ! $id ) {
			$id = get_the_ID();
		}
		if ( $id ) {
			$post_custom_css = get_post_meta( $id, '_mcmsb_post_custom_css', true );
			if ( ! empty( $post_custom_css ) ) {
				$post_custom_css = strip_tags( $post_custom_css );
				echo '<style type="text/css" data-type="vc_custom-css">';
				echo $post_custom_css;
				echo '</style>';
			}
		}
	}

	/**
	 * Hooked class method by mcms_footer MCMS action to output shortcodes css editor settings from page meta data.
	 *
	 * Method gets post meta value for page by key '_mcmsb_shortcodes_custom_css' and if it is not empty
	 * outputs css string wrapped into style tag.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param int $id
	 *
	 */
	public function addShortcodesCustomCss( $id = null ) {
		if ( ! is_singular() ) {
			return;
		}
		if ( ! $id ) {
			$id = get_the_ID();
		}

		if ( $id ) {
			$shortcodes_custom_css = get_post_meta( $id, '_mcmsb_shortcodes_custom_css', true );
			if ( ! empty( $shortcodes_custom_css ) ) {
				$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
				echo '<style type="text/css" data-type="vc_shortcodes-custom-css">';
				echo $shortcodes_custom_css;
				echo '</style>';
			}
		}
	}

	/**
	 * Add css styles for current page and elements design options added w\ editor.
	 */
	public function addFrontCss() {
		$this->addPageCustomCss();
		$this->addShortcodesCustomCss();
	}

	public function addNoScript() {
		echo '<noscript>';
		echo '<style type="text/css">';
		echo ' .mcmsb_animate_when_almost_visible { opacity: 1; }';
		echo '</style>';
		echo '</noscript>';
	}

	/**
	 * Register front css styles.
	 *
	 * Calls mcms_register_style for required css libraries files.
	 *
	 * @since  3.1
	 * @access public
	 */
	public function frontCss() {
		mcms_register_style( 'flexslider', vc_asset_url( 'lib/bower/flexslider/flexslider.min.css' ), array(), MCMSB_VC_VERSION );
		mcms_register_style( 'nivo-slider-css', vc_asset_url( 'lib/bower/nivoslider/nivo-slider.min.css' ), array(), MCMSB_VC_VERSION );
		mcms_register_style( 'nivo-slider-myskin', vc_asset_url( 'lib/bower/nivoslider/myskins/default/default.min.css' ), array( 'nivo-slider-css' ), MCMSB_VC_VERSION );
		mcms_register_style( 'prettyphoto', vc_asset_url( 'lib/prettyphoto/css/prettyPhoto.min.css' ), array(), MCMSB_VC_VERSION );
		mcms_register_style( 'isotope-css', vc_asset_url( 'css/lib/isotope.min.css' ), array(), MCMSB_VC_VERSION );
		mcms_register_style( 'font-awesome', vc_asset_url( 'lib/bower/font-awesome/css/font-awesome.min.css' ), array(), MCMSB_VC_VERSION );
		mcms_register_style( 'animate-css', vc_asset_url( 'lib/bower/animate-css/animate.min.css' ), array(), MCMSB_VC_VERSION );

		$front_css_file = vc_asset_url( 'css/rl_conductor.min.css' );
		$upload_dir = mcms_upload_dir();
		$vc_upload_dir = vc_upload_dir();
		if ( '1' === vc_settings()->get( 'use_custom' ) && is_file( $upload_dir['basedir'] . '/' . $vc_upload_dir . '/rl_conductor_front_custom.css' ) ) {
			$front_css_file = $upload_dir['baseurl'] . '/' . $vc_upload_dir . '/rl_conductor_front_custom.css';
			$front_css_file = vc_str_remove_protocol( $front_css_file );
		}
		mcms_register_style( 'rl_conductor_front', $front_css_file, array(), MCMSB_VC_VERSION );

		$custom_css_path = $upload_dir['basedir'] . '/' . $vc_upload_dir . '/custom.css';
		if ( is_file( $upload_dir['basedir'] . '/' . $vc_upload_dir . '/custom.css' ) && filesize( $custom_css_path ) > 0 ) {
			$custom_css_url = $upload_dir['baseurl'] . '/' . $vc_upload_dir . '/custom.css';
			$custom_css_url = vc_str_remove_protocol( $custom_css_url );
			mcms_register_style( 'rl_conductor_custom_css', $custom_css_url, array(), MCMSB_VC_VERSION );
		}
		add_action( 'mcms_enqueue_scripts', array(
			$this,
			'enqueueStyle',
		) );

		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_front_css' );
	}

	/**
	 * Enqueue base css class for VC elements and enqueue custom css if exists.
	 */
	public function enqueueStyle() {
		$post = get_post();
		if ( $post && preg_match( '/vc_row/', $post->post_content ) ) {
			mcms_enqueue_style( 'rl_conductor_front' );
		}
		mcms_enqueue_style( 'rl_conductor_custom_css' );
	}

	/**
	 * Register front javascript libs.
	 *
	 * Calls mcms_register_script for required css libraries files.
	 *
	 * @since  3.1
	 * @access public
	 */
	public function frontJsRegister() {
		mcms_register_script( 'prettyphoto', vc_asset_url( 'lib/prettyphoto/js/jquery.prettyPhoto.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'waypoints', vc_asset_url( 'lib/waypoints/waypoints.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );

		// @deprecated used in old tabs
		mcms_register_script( 'jquery_ui_tabs_rotate', vc_asset_url( 'lib/bower/jquery-ui-tabs-rotate/jquery-ui-tabs-rotate.min.js' ), array(
			'jquery',
			'jquery-ui-tabs',
		), MCMSB_VC_VERSION, true );

		// used in vc_gallery, old grid
		mcms_register_script( 'isotope', vc_asset_url( 'lib/bower/isotope/dist/isotope.pkgd.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );

		mcms_register_script( 'twbs-pagination', vc_asset_url( 'lib/bower/twbs-pagination/jquery.twbsPagination.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'nivo-slider', vc_asset_url( 'lib/bower/nivoslider/jquery.nivo.slider.pack.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'flexslider', vc_asset_url( 'lib/bower/flexslider/jquery.flexslider-min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'mcmsb_composer_front_js', vc_asset_url( 'js/dist/rl_conductor_front.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );

		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_front_js' );
	}

	/**
	 * Register admin javascript libs.
	 *
	 * Calls mcms_register_script for required css libraries files for Admin dashboard.
	 *
	 * @since  3.1
	 * vc_filter: vc_i18n_locale_composer_js_view, since 4.4 - override localization for js
	 * @access public
	 */
	public function registerAdminJavascript() {
		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_admin_js' );

	}

	/**
	 * Register admin css styles.
	 *
	 * Calls mcms_register_style for required css libraries files for admin dashboard.
	 *
	 * @since  3.1
	 * @access public
	 */
	public function registerAdminCss() {
		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_admin_css' );
	}

	/**
	 * Add Settings link in module's page
	 * @since 4.2
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 */
	public function moduleActionLinks( $links, $file ) {
		if ( module_basename( vc_path_dir( 'APP_DIR', '/rl_conductor.php' ) ) == $file ) {
			$title = __( 'RazorLeaf Conductor Settings', 'rl_conductor' );
			$html = esc_html__( 'Settings', 'rl_conductor' );
			if ( ! vc_user_access()->part( 'settings' )->can( 'vc-general-tab' )->get() ) {
				$title = __( 'About RazorLeaf Conductor', 'rl_conductor' );
				$html = esc_html__( 'About', 'rl_conductor' );
			}
			$link = '<a title="' . esc_attr( $title ) . '" href="' . esc_url( $this->getSettingsPageLink() ) . '">' . $html . '</a>';
			array_unshift( $links, $link ); // Add to top
		}

		return $links;
	}

	/**
	 * Get settings page link
	 * @since 4.2
	 * @return string url to settings page
	 */
	public function getSettingsPageLink() {
		$page = 'vc-general';
		if ( ! vc_user_access()->part( 'settings' )->can( 'vc-general-tab' )->get() ) {
			$page = 'vc-welcome';
		}

		return add_query_arg( array( 'page' => $page ), admin_url( 'admin.php' ) );
	}

	/**
	 * Hooked class method by mcms_head MCMS action.
	 * @since  4.2
	 * @access public
	 */
	public function addMetaData() {
		echo '<meta name="generator" content="Powered by RazorLeaf Conductor - drag and drop page builder for MandarinCMS."/>' . "\n";
	}

	/**
	 * Also add fix for IE8 bootstrap styles from MCMSExplorer
	 * @since  4.9
	 * @access public
	 */
	public function addIEMinimalSupport() {
		echo '<!--[if lte IE 9]><link rel="stylesheet" type="text/css" href="' . vc_asset_url( 'css/vc_lte_ie9.min.css' ) . '" media="screen"><![endif]-->';
	}

	/**
	 * Method adds css class to body tag.
	 *
	 * Hooked class method by body_class MCMS filter. Method adds custom css class to body tag of the page to help
	 * identify and build design specially for VC shortcodes.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function bodyClass( $classes ) {
		return rl_conductor_body_class( $classes );
	}

	/**
	 * Builds excerpt for post from content.
	 *
	 * Hooked class method by the_excerpt MCMS filter. When user creates content with VC all content is always wrapped by
	 * shortcodes. This methods calls do_shortcode for post's content and then creates a new excerpt.
	 *
	 * @since  4.2
	 * @access public
	 *
	 * @param $output
	 *
	 * @return string
	 */
	public function excerptFilter( $output ) {
		global $post;
		if ( empty( $output ) && ! empty( $post->post_content ) ) {
			$text = strip_tags( do_shortcode( $post->post_content ) );
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[...]' );
			$text = mcms_trim_words( $text, $excerpt_length, $excerpt_more );

			return $text;
		}

		return $output;
	}

	/**
	 * Remove unwanted wraping with p for content.
	 *
	 * Hooked by 'the_content' filter.
	 * @since 4.2
	 *
	 * @param null $content
	 *
	 * @return string|null
	 */
	public function fixPContent( $content = null ) {
		if ( $content ) {
			$s = array(
				'/' . preg_quote( '</div>', '/' ) . '[\s\n\f]*' . preg_quote( '</p>', '/' ) . '/i',
				'/' . preg_quote( '<p>', '/' ) . '[\s\n\f]*' . preg_quote( '<div ', '/' ) . '/i',
				'/' . preg_quote( '<p>', '/' ) . '[\s\n\f]*' . preg_quote( '<section ', '/' ) . '/i',
				'/' . preg_quote( '</section>', '/' ) . '[\s\n\f]*' . preg_quote( '</p>', '/' ) . '/i',
			);
			$r = array(
				'</div>',
				'<div ',
				'<section ',
				'</section>',
			);
			$content = preg_replace( $s, $r, $content );

			return $content;
		}

		return null;
	}

	/**
	 * @todo remove this (comment added on 4.8) also remove helpers
	 * Set manger for custom third-party modules.
	 * @deprecated due to autoload logic 4.4
	 * @since 4.3
	 *
	 * @param Vc_Vendors_Manager $vendor_manager
	 */
	public function setVendorsManager( Vc_Vendors_Manager $vendor_manager ) {
		_deprecated_function( 'Vc_Base::setVendorsManager', '4.4 (will be removed in 5.1)', 'autoload logic' );

		$this->vendor_manager = $vendor_manager;
	}

	/**
	 * @todo remove this (comment added on 4.8) also remove helpers
	 * Get vendors manager.
	 * @deprecated due to autoload logic from 4.4
	 * @since 4.3
	 * @return bool|Vc_Vendors_Manager
	 */
	public function vendorsManager() {
		_deprecated_function( 'Vc_Base::vendorsManager', '4.4 (will be removed in 5.1)', 'autoload logic' );

		return $this->vendor_manager;
	}

	/**
	 * Get array of string for locale.
	 *
	 * @since 4.7
	 *
	 * @return array
	 */
	public function getEditorsLocale() {
		return array(
			'add_remove_picture' => __( 'Add/remove picture', 'rl_conductor' ),
			'finish_adding_text' => __( 'Finish Adding Images', 'rl_conductor' ),
			'add_image' => __( 'Add Image', 'rl_conductor' ),
			'add_images' => __( 'Add Images', 'rl_conductor' ),
			'settings' => __( 'Settings', 'rl_conductor' ),
			'main_button_title' => __( 'RazorLeaf Conductor', 'rl_conductor' ),
			'main_button_title_backend_editor' => __( 'BACKEND EDITOR', 'rl_conductor' ),
			'main_button_title_frontend_editor' => __( 'FRONTEND EDITOR', 'rl_conductor' ),
			'main_button_title_revert' => __( 'CLASSIC MODE', 'rl_conductor' ),
			'please_enter_templates_name' => __( 'Enter template name you want to save.', 'rl_conductor' ),
			'confirm_deleting_template' => __( 'Confirm deleting "{template_name}" template, press Cancel to leave. This action cannot be undone.', 'rl_conductor' ),
			'press_ok_to_delete_section' => __( 'Press OK to delete section, Cancel to leave', 'rl_conductor' ),
			'drag_drop_me_in_column' => __( 'Drag and drop me in the column', 'rl_conductor' ),
			'press_ok_to_delete_tab' => __( 'Press OK to delete "{tab_name}" tab, Cancel to leave', 'rl_conductor' ),
			'slide' => __( 'Slide', 'rl_conductor' ),
			'tab' => __( 'Tab', 'rl_conductor' ),
			'section' => __( 'Section', 'rl_conductor' ),
			'please_enter_new_tab_title' => __( 'Please enter new tab title', 'rl_conductor' ),
			'press_ok_delete_section' => __( 'Press OK to delete "{tab_name}" section, Cancel to leave', 'rl_conductor' ),
			'section_default_title' => __( 'Section', 'rl_conductor' ),
			'please_enter_section_title' => __( 'Please enter new section title', 'rl_conductor' ),
			'error_please_try_again' => __( 'Error. Please try again.', 'rl_conductor' ),
			'if_close_data_lost' => __( 'If you close this window all shortcode settings will be lost. Close this window?', 'rl_conductor' ),
			'header_select_element_type' => __( 'Select element type', 'rl_conductor' ),
			'header_media_gallery' => __( 'Media gallery', 'rl_conductor' ),
			'header_element_settings' => __( 'Element settings', 'rl_conductor' ),
			'add_tab' => __( 'Add tab', 'rl_conductor' ),
			'are_you_sure_convert_to_new_version' => __( 'Are you sure you want to convert to new version?', 'rl_conductor' ),
			'loading' => __( 'Loading...', 'rl_conductor' ),
			// Media editor
			'set_image' => __( 'Set Image', 'rl_conductor' ),
			'are_you_sure_reset_css_classes' => __( 'Are you sure that you want to remove all your data?', 'rl_conductor' ),
			'loop_frame_title' => __( 'Loop settings', 'rl_conductor' ),
			'enter_custom_layout' => __( 'Custom row layout', 'rl_conductor' ),
			'wrong_cells_layout' => __( 'Wrong row layout format! Example: 1/2 + 1/2 or span6 + span6.', 'rl_conductor' ),
			'row_background_color' => __( 'Row background color', 'rl_conductor' ),
			'row_background_image' => __( 'Row background image', 'rl_conductor' ),
			'column_background_color' => __( 'Column background color', 'rl_conductor' ),
			'column_background_image' => __( 'Column background image', 'rl_conductor' ),
			'guides_on' => __( 'Guides ON', 'rl_conductor' ),
			'guides_off' => __( 'Guides OFF', 'rl_conductor' ),
			'template_save' => __( 'New template successfully saved.', 'rl_conductor' ),
			'template_added' => __( 'Template added to the page.', 'rl_conductor' ),
			'template_added_with_id' => __( 'Template added to the page. Template has ID attributes, make sure that they are not used more than once on the same page.', 'rl_conductor' ),
			'template_removed' => __( 'Template successfully removed.', 'rl_conductor' ),
			'template_is_empty' => __( 'Template is empty: There is no content to be saved as a template.', 'rl_conductor' ),
			'template_save_error' => __( 'Error while saving template.', 'rl_conductor' ),
			'css_updated' => __( 'Page settings updated!', 'rl_conductor' ),
			'update_all' => __( 'Update all', 'rl_conductor' ),
			'confirm_to_leave' => __( 'The changes you made will be lost if you navigate away from this page.', 'rl_conductor' ),
			'inline_element_saved' => __( '%s saved!', 'rl_conductor' ),
			'inline_element_deleted' => __( '%s deleted!', 'rl_conductor' ),
			'inline_element_cloned' => __( '%s cloned. <a href="#" class="vc_edit-cloned" data-model-id="%s">Edit now?</a>', 'rl_conductor' ),
			'gfonts_loading_google_font_failed' => __( 'Loading Google Font failed', 'rl_conductor' ),
			'gfonts_loading_google_font' => __( 'Loading Font...', 'rl_conductor' ),
			'gfonts_unable_to_load_google_fonts' => __( 'Unable to load Google Fonts', 'rl_conductor' ),
			'no_title_parenthesis' => sprintf( '(%s)', __( 'no title', 'rl_conductor' ) ),
			'error_while_saving_image_filtered' => __( 'Error while applying filter to the image. Check your server and memory settings.', 'rl_conductor' ),
			'ui_saved' => sprintf( '<i class="vc-composer-icon vc-c-icon-check"></i> %s', __( 'Saved!', 'rl_conductor' ) ),
			'ui_danger' => sprintf( '<i class="vc-composer-icon vc-c-icon-close"></i> %s', __( 'Failed to Save!', 'rl_conductor' ) ),
			'delete_preset_confirmation' => __( 'You are about to delete this preset. This action can not be undone.', 'rl_conductor' ),
			'ui_template_downloaded' => __( 'Downloaded', 'rl_conductor' ),
			'ui_template_update' => __( 'Update', 'rl_conductor' ),
			'ui_templates_failed_to_download' => __( 'Failed to download template', 'rl_conductor' ),
		);
	}

}

/**
 * @todo remove this (comment added on 4.8) also remove helpers
 * VC backward capability.
 * @deprecated @since 4.3
 */
class MCMSBakeryVisualComposer extends Vc_Base {

	/**
	 * @deprecated since 4.3
	 */
	function __construct() {
		_deprecated_function( 'MCMSBakeryVisualComposer class', '4.3 (will be removed in 5.1)', 'Vc_Base class' );
	}

	/**
	 * @param $template
	 *
	 * @deprecated 4.3
	 * @return string
	 */
	public static function getUserTemplate( $template ) {
		_deprecated_function( 'MCMSBakeryVisualComposer getUserTemplate', '4.3 (will be removed in 5.1)', 'Vc_Base getShortcodesTemplateDir' );

		return vc_manager()->getShortcodesTemplateDir( $template );
	}
}
