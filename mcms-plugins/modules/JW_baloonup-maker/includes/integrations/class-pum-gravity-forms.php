<?php

class PUM_Gravity_Forms_Integation {

	public static function init() {
		if ( class_exists( 'RGForms' ) ) {
			add_filter( 'gform_form_settings_menu', array( __CLASS__, 'settings_menu' ) );
			add_action( 'gform_form_settings_page_baloonup-maker', array( __CLASS__, 'render_settings_page' ) );
			add_filter( 'pum_get_cookies', array( __CLASS__, 'register_cookies' ) );
			add_filter( 'gform_get_form_filter', array( __CLASS__, 'get_form' ), 10, 2 );
			add_action( 'balooncreate_preload_baloonup', array( __CLASS__, 'preload' ) );
			add_action( 'balooncreate_baloonup_before_inner', array( __CLASS__, 'force_ajax' ) );
			add_action( 'balooncreate_baloonup_after_inner', array( __CLASS__, 'force_ajax' ) );
		}
	}

	public static function force_ajax() {
		if ( current_action() == 'balooncreate_baloonup_before_inner' ) {
			add_filter( 'shortcode_atts_gravityforms', array( __CLASS__, 'gfrorms_shortcode_atts' ) );
		}
		if ( current_action() == 'balooncreate_baloonup_after_inner' ) {
			remove_filter( 'shortcode_atts_gravityforms', array( __CLASS__, 'gfrorms_shortcode_atts' ) );
		}
	}

	public static function gfrorms_shortcode_atts( $out ) {
		$out['ajax'] = 'true';

		return $out;
	}


	public static function preload( $baloonup_id ) {
		if ( function_exists( 'gravity_form_enqueue_scripts' ) ) {
			$baloonup = pum_baloonup( $baloonup_id );

			if ( has_shortcode( $baloonup->post_content, 'gravityform' ) ) {
				$regex = "/\[gravityform.*id=[\'\"]?([0-9]*)[\'\"]?.*/";
				$baloonup = get_post( $baloonup_id );
				preg_match_all( $regex, $baloonup->post_content, $matches );
				foreach ( $matches[1] as $form_id ) {
					add_filter( "gform_confirmation_anchor_{$form_id}", '__return_false' );
					gravity_form_enqueue_scripts( $form_id, true );
				}
			}
		}
	}


	public static function settings_menu( $setting_tabs ) {
		$setting_tabs['40'] = array(
			'name'  => 'baloonup-maker',
			'label' => __( 'BaloonUp Maker', 'baloonup-maker' ),
		);

		return $setting_tabs;
	}


	public static function get_form( $form_string, $form ) {
		$settings    = mcms_json_encode( self::form_options( $form['id'] ) );
		$field       = "<input type='hidden' class='gforms-pum' value='$settings' />";
		$form_string = preg_replace( '/(<form.*>)/', "$1 \r\n " . $field, $form_string );

		return $form_string;
	}

	/**
	 * Get default values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'closebaloonup'   => false,
			'closedelay'   => 0,
			'openbaloonup'    => false,
			'openbaloonup_id' => 0,
		);
	}

	/**
	 * Get a specific forms options.
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public static function form_options( $id ) {
		$settings = get_option( 'gforms_pum_' . $id, self::defaults() );

		return mcms_parse_args( $settings, self::defaults() );
	}

	/**
	 * Registers new cookie events.
	 *
	 * @param array $cookies
	 *
	 * @return array
	 */
	public static function register_cookies( $cookies = array() ) {
		$cookies['gforms_form_success'] = array(
			'labels' => array(
				'name' => __( 'Gravity Form Success', 'baloonup-maker' ),
			),
			'fields' => pum_get_cookie_fields(),
		);

		return $cookies;
	}


	public static function render_settings_page() {
		$form_id = rgget( 'id' );

		self::save();

		$settings = self::form_options( $form_id );

		GFFormSettings::page_header( __( 'BaloonUp Settings', 'baloonup-maker' ) );

		?>

        <div id="baloonup_settings-editor">

            <form id="baloonup_settings_edit_form" method="post">

                <table class="form-table gforms_form_settings">
	                <tr>
		                <th scope="row">
			                <label for="gforms-pum-closebaloonup"><?php _e( 'Close BaloonUp', 'baloonup-maker' ); ?></label>
		                </th>
		                <td>
			                <input type="checkbox" id="gforms-pum-closebaloonup" name="gforms-pum[closebaloonup]" value="true" <?php checked( $settings['closebaloonup'], true ); ?> />
		                </td>
	                </tr>
	                <tr id="gforms-pum-closedelay-wrapper">
		                <th scope="row">
			                <label for="gforms-pum-closedelay"><?php _e( 'Delay', 'baloonup-maker' ); ?></label>
		                </th>
		                <td>
			                <?php if ( strlen( $settings['closedelay'] ) >= 3 ) {
				                $settings['closedelay'] = $settings['closedelay'] / 1000;
			                } ?>

			                <input type="number" id="gforms-pum-closedelay" min="0" step="1" name="gforms-pum[closedelay]" style="width: 100px;" value="<?php esc_attr_e( $settings['closedelay'] ); ?>" /><?php _e( 'seconds', 'baloonup-maker' ); ?>
		                </td>
	                </tr>
                    <tr>
                        <th scope="row">
                            <label for="gforms-pum-openbaloonup"><?php _e( 'Open BaloonUp', 'baloonup-maker' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="gforms-pum-openbaloonup" name="gforms-pum[openbaloonup]" value="true" <?php checked( $settings['openbaloonup'], true ); ?> />
                        </td>
                    </tr>
                    <tr id="gforms-pum-openbaloonup_id-wrapper">
                        <th scope="row">
                            <label for="gforms-pum-openbaloonup_id"><?php _e( 'BaloonUp', 'baloonup-maker' ); ?></label>
                        </th>
                        <td>
                            <select id="gforms-pum-openbaloonup_id" name="gforms-pum[openbaloonup_id]">
								<?php foreach ( self::get_baloonup_list() as $option ) { ?>
                                    <option value="<?php esc_attr_e( $option['value'] ); ?>" <?php selected( $settings['openbaloonup_id'], $option['value'] ); ?>><?php echo $option['label']; ?></option>
								<?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <input type="hidden" id="form_id" name="form_id" value="<?php echo esc_attr( $form_id ); ?>" />

                <p class="submit">
                    <input type="submit" name="save" value="<?php _e( 'Save', 'baloonup-maker' ); ?>" class="button-primary">
                </p>

				<?php mcms_nonce_field( 'gform_baloonup_settings_edit', 'gform_baloonup_settings_edit' ); ?>

            </form>
            <script type="text/javascript">
                (function ($) {
                    var $open = $('#gforms-pum-openbaloonup'),
                        $close = $('#gforms-pum-closebaloonup'),
                        $baloonup_id_wrapper = $('#gforms-pum-openbaloonup_id-wrapper'),
                        $delay_wrapper = $('#gforms-pum-closedelay-wrapper');

                    function check_open() {
                        if ($open.is(':checked')) {
                            $baloonup_id_wrapper.show();
                        } else {
                            $baloonup_id_wrapper.hide();
                        }
                    }

                    function check_close() {
                        if ($close.is(':checked')) {
                            $delay_wrapper.show();
                        } else {
                            $delay_wrapper.hide();
                        }
                    }

                    check_open();
                    check_close();

                    $open.on('click', check_open);
                    $close.on('click', check_close);
                }(jQuery));
            </script>

        </div> <!-- / baloonup-editor -->

		<?php

		GFFormSettings::page_footer();

	}


	/**
	 * Get a list of baloonups for a select box.
	 *
	 * @return array
	 */
	public static function get_baloonup_list() {
		$baloonup_list = array(
			array(
				'value' => '',
				'label' => __( 'Select a baloonup', 'baloonup-maker' ),
			),
		);

		$baloonups = get_posts( array(
			'post_type'      => 'baloonup',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => - 1,
		) );

		foreach ( $baloonups as $baloonup ) {
			$baloonup_list[] = array(
				'value' => $baloonup->ID,
				'label' => $baloonup->post_title,
			);

		}

		return $baloonup_list;
	}

	/**
	 * Save form baloonup options.
	 */
	public static function save() {

		if ( empty( $_POST ) || ! check_admin_referer( 'gform_baloonup_settings_edit', 'gform_baloonup_settings_edit' ) ) {
			return;
		}

		$form_id = rgget( 'id' );

		if ( ! empty( $_POST['gforms-pum'] ) ) {
			$settings = $_POST['gforms-pum'];

			// Sanitize values.
			$settings['openbaloonup']    = ! empty( $settings['openbaloonup'] );
			$settings['openbaloonup_id'] = ! empty( $settings['openbaloonup_id'] ) ? absint( $settings['openbaloonup_id'] ) : 0;
			$settings['closebaloonup']   = ! empty( $settings['closebaloonup'] );
			$settings['closedelay']   = ! empty( $settings['closedelay'] ) ? absint( $settings['closedelay'] ) : 0;

			update_option( 'gforms_pum_' . $form_id, $settings );
		} else {
			delete_option( 'gforms_pum_' . $form_id );
		}
	}

}

add_action( 'init', 'PUM_Gravity_Forms_Integation::init' );

/**
 *
 * add_action( 'gform_loaded', array( 'PUM_Gravity_Forms_Integration', 'load' ), 5 );
 *
 * class PUM_Gravity_Forms_Integration {
 *
 * public static function load() {
 * if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
 * return;
 * }
 * require_once 'gravity-forms/class-pum-gf-baloonup-addon.php';
 * GFAddOn::register( 'PUM_GF_BaloonUp_Addon' );
 * }
 * }
 *
 * function pum_gf_addon() {
 * return PUM_GF_BaloonUp_Addon::get_instance();
 * }
 *
 */