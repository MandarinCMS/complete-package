<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * MCMSBakery RazorLeaf Conductor admin editor
 *
 * @package MCMSBakeryVisualComposer
 *
 */

/**
 * VC backend editor.
 *
 * This editor is available on default Wp post/page admin edit page. ON admin_init callback adds meta box to
 * edit page.
 *
 * @since 4.2
 */
class Vc_Backend_Editor implements Vc_Editor_Interface {

	/**
	 * @var
	 */
	protected $layout;
	/**
	 * @var
	 */
	public $post_custom_css;
	/**
	 * @var bool|string $post - stores data about post.
	 */
	public $post = false;

	/**
	 * This method is called by Vc_Manager to register required action hooks for VC backend editor.
	 *
	 * @since  4.2
	 * @access public
	 */
	public function addHooksSettings() {
		// @todo - fix_roles do this only if be editor is enabled.
		// load backend editor
		if ( function_exists( 'add_myskin_support' ) ) {
			add_myskin_support( 'post-thumbnails' ); // @todo check is it needed?
		}
		add_action( 'add_meta_boxes', array(
			$this,
			'render',
		), 5 );
		add_action( 'admin_print_scripts-post.php', array(
			$this,
			'printScriptsMessages',
		) );
		add_action( 'admin_print_scripts-post-new.php', array(
			$this,
			'printScriptsMessages',
		) );

	}

	/**
	 *    Calls add_meta_box to create Editor block. Block is rendered by MCMSBakeryVisualComposerLayout.
	 *
	 * @see MCMSBakeryVisualComposerLayout
	 * @since  4.2
	 * @access public
	 *
	 * @param $post_type
	 */
	public function render( $post_type ) {
		if ( $this->isValidPostType( $post_type ) ) {
			$this->registerBackendJavascript();
			$this->registerBackendCss();
			// B.C:
			visual_composer()->registerAdminCss();
			visual_composer()->registerAdminJavascript();

			// meta box to render
			add_meta_box( 'mcmsb_visual_composer', __( 'RazorLeaf Conductor', 'rl_conductor' ), array(
				$this,
				'renderEditor',
			), $post_type, 'normal', 'high' );
		}
	}

	/**
	 * Output html for backend editor meta box.
	 *
	 * @param null|Wp_Post $post
	 *
	 * @return bool
	 */
	public function renderEditor( $post = null ) {
		/**
		 * TODO: setter/getter for $post
		 */
		if ( ! is_object( $post ) || 'MCMS_Post' !== get_class( $post ) || ! isset( $post->ID ) ) {
			return false;
		}
		$this->post = $post;
		$post_custom_css = strip_tags( get_post_meta( $post->ID, '_mcmsb_post_custom_css', true ) );
		$this->post_custom_css = $post_custom_css;
		vc_include_template( 'editors/backend_editor.tpl.php', array(
			'editor' => $this,
			'post' => $this->post,
		) );
		add_action( 'admin_footer', array(
			$this,
			'renderEditorFooter',
		) );
		do_action( 'vc_backend_editor_render' );

		return true;
	}

	/**
	 * Output required html and js content for VC editor.
	 *
	 * Here comes panels, modals and js objects with data for mapped shortcodes.
	 */
	public function renderEditorFooter() {
		vc_include_template( 'editors/partials/backend_editor_footer.tpl.php', array(
			'editor' => $this,
			'post' => $this->post,
		) );
		do_action( 'vc_backend_editor_footer_render' );
	}

	/**
	 * Check is post type is valid for rendering VC backend editor.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function isValidPostType( $type = '' ) {
		if ( 'vc_grid_item' === $type ) {
			return false;
		}

		return vc_check_post_type( ! empty( $type ) ? $type : get_post_type() );
	}

	/**
	 * Enqueue required javascript libraries and css files.
	 *
	 * This method also setups reminder about license activation.
	 *
	 * @since  4.2
	 * @access public
	 */
	public function printScriptsMessages() {
		if ( ! vc_is_frontend_editor() && $this->isValidPostType( get_post_type() ) ) {
			$this->enqueueEditorScripts();
		}
	}

	/**
	 * Enqueue required javascript libraries and css files.
	 *
	 * @since  4.8
	 * @access public
	 */
	public function enqueueEditorScripts() {
		if ( $this->editorEnabled() ) {
			$this->enqueueJs();
			$this->enqueueCss();
			MCMSBakeryShortCodeFishBones::enqueueCss();
			MCMSBakeryShortCodeFishBones::enqueueJs();
		} else {
			mcms_enqueue_script( 'vc-backend-actions-js' );
			$this->enqueueCss(); //needed for navbar @todo split
		}
		do_action( 'vc_backend_editor_enqueue_js_css' );
	}

	/**
	 * @deprecated 4.8
	 * @return string
	 */
	public function showRulesValue() {
		_deprecated_function( '\Vc_Backend_Editor::showRulesValue', '4.8 (will be removed in next release)' );
		global $current_user;
		mcms_get_current_user();
		/** @var $settings - get use group access rules */
		$settings = vc_settings()->get( 'groups_access_rules' );
		$role = is_object( $current_user ) && isset( $current_user->roles[0] ) ? $current_user->roles[0] : '';

		return isset( $settings[ $role ]['show'] ) ? $settings[ $role ]['show'] : '';
	}

	public function registerBackendJavascript() {
		// editor can be disabled but fe can be enabled. so we currently need this file. @todo maybe make backend-disabled.min.js
		mcms_register_script( 'vc-backend-actions-js', vc_asset_url( 'js/dist/backend-actions.min.js' ), array(
			'jquery',
			'backbone',
			'underscore',
		), MCMSB_VC_VERSION, true );
		mcms_register_script( 'vc-backend-min-js', vc_asset_url( 'js/dist/backend.min.js' ), array( 'vc-backend-actions-js' ), MCMSB_VC_VERSION, true );
		// used in tta shortcodes, and panels.
		mcms_register_script( 'vc_accordion_script', vc_asset_url( 'lib/vc_accordion/vc-accordion.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'mcmsb_php_js', vc_asset_url( 'lib/php.default/php.default.min.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		// used as polyfill for JSON.stringify and etc
		mcms_register_script( 'mcmsb_json-js', vc_asset_url( 'lib/bower/json-js/json2.min.js' ), array(), MCMSB_VC_VERSION, true );
		// used in post settings editor
		mcms_register_script( 'ace-editor', vc_asset_url( 'lib/bower/ace-builds/src-min-noconflict/ace.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
		mcms_register_script( 'webfont', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js' ); // Google Web Font CDN

		mcms_localize_script( 'vc-backend-actions-js', 'i18nLocale', visual_composer()->getEditorsLocale() );
	}

	public function registerBackendCss() {
		mcms_register_style( 'rl_conductor', vc_asset_url( 'css/rl_conductor_backend_editor.min.css' ), array(), MCMSB_VC_VERSION, false );

		if ( $this->editorEnabled() ) {
			/**
			 * @deprecated, used for accordions/tabs/tours
			 */
			mcms_register_style( 'ui-custom-myskin', vc_asset_url( 'css/ui-custom-myskin/jquery-ui-less.custom.min.css' ), array(), MCMSB_VC_VERSION );

			/**
			 * @todo check vc_add-element-deprecated-warning for fa icon usage ( set to our font )
			 * also used in vc_icon shortcode
			 */
			mcms_register_style( 'font-awesome', vc_asset_url( 'lib/bower/font-awesome/css/font-awesome.min.css' ), array(), MCMSB_VC_VERSION );

			/**
			 * @todo check for usages
			 * definetelly used in edit form param: css_animation, but curreny vc_add_shortcode_param doesn't accept css [ @todo refactor that ]
			 */
			mcms_register_style( 'animate-css', vc_asset_url( 'lib/bower/animate-css/animate.min.css' ), array(), MCMSB_VC_VERSION );
		}
	}

	public function enqueueJs() {
		$mcms_dependencies = array(
			'jquery',
			'underscore',
			'backbone',
			'media-views',
			'media-editor',
			'mcms-pointer',
			'mce-view',
			'mcms-color-picker',
			'jquery-ui-sortable',
			'jquery-ui-droppable',
			'jquery-ui-draggable',
			'jquery-ui-autocomplete',
			'jquery-ui-resizable',
			// used in @deprecated tabs
			'jquery-ui-tabs',
			'jquery-ui-accordion',
		);
		$dependencies = array(
			'vc_accordion_script',
			'mcmsb_php_js',
			// used in our files [e.g. edit form saving sprintf]
			'mcmsb_json-js',
			'ace-editor',
			'webfont',
			'vc-backend-min-js',
		);

		// This workaround will allow to disable any of dependency on-the-fly
		foreach ( $mcms_dependencies as $dependency ) {
			mcms_enqueue_script( $dependency );
		}
		foreach ( $dependencies as $dependency ) {
			mcms_enqueue_script( $dependency );
		}
	}

	public function enqueueCss() {
		$mcms_dependencies = array(
			'mcms-color-picker',
			'farbtastic',
			// deprecated for tabs/accordion
			'ui-custom-myskin',
			// used in deprecated message and also in vc-icon shortcode
			'font-awesome',
			// used in css_animation edit form param
			'animate-css',
		);
		$dependencies = array(
			'rl_conductor',
		);

		// This workaround will allow to disable any of dependency on-the-fly
		foreach ( $mcms_dependencies as $dependency ) {
			mcms_enqueue_style( $dependency );
		}
		foreach ( $dependencies as $dependency ) {
			mcms_enqueue_style( $dependency );
		}
	}

	/**
	 * @return bool
	 */
	public function editorEnabled() {
		return vc_user_access()->part( 'backend_editor' )->can()->get();
	}
}
