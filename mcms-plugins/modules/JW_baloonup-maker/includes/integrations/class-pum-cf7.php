<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_CF7_Integration
 */
class PUM_CF7_Integration {

	/**
	 * Initialize if CF7 is active.
	 */
	public static function init() {
		if ( class_exists( 'MCMSCF7' ) || ( defined( 'MCMSCF7_VERSION' ) && MCMSCF7_VERSION ) ) {
			add_filter( 'pum_get_cookies', array( __CLASS__, 'register_cookies' ) );
			add_filter( 'mcmscf7_editor_panels', array( __CLASS__, 'editor_panels' ) );
			add_action( 'mcmscf7_after_save', array( __CLASS__, 'save' ) );
			add_filter( 'mcmscf7_form_elements', array( __CLASS__, 'form_elements' ) );
			add_action( 'balooncreate_preload_baloonup', array( __CLASS__, 'preload' ) );
		}
	}

	/**
	 * Check if the baloonups use CF7 Forms and force enqueue their assets.
	 *
	 * @param $baloonup_id
	 */
	public static function preload( $baloonup_id ) {
		$baloonup = pum_baloonup( $baloonup_id );

		if ( has_shortcode( $baloonup->post_content, 'contact-form-7' ) ) {
			if ( function_exists( 'mcmscf7_enqueue_scripts' ) ) {
				mcmscf7_enqueue_scripts();
			}

			if ( function_exists( 'mcmscf7_enqueue_styles' ) ) {
				mcmscf7_enqueue_styles();
			}
		}
	}

	/**
	 * Append a hidden meta html element with the forms baloonup settings.
	 *
	 * @param $elements
	 *
	 * @return string
	 */
	public static function form_elements( $elements ) {
		$form = mcmscf7_get_current_contact_form();

		$settings = mcms_json_encode( self::form_options( $form->id() ) );

		return $elements . "<input type='hidden' class='mcmscf7-pum' value='$settings' />";
	}

	/**
	 * Get a specific forms options.
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public static function form_options( $id ) {
		$settings = get_option( 'cf7_pum_' . $id, self::defaults() );

		return mcms_parse_args( $settings, self::defaults() );
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
	 * Registers new cookie events.
	 *
	 * @param array $cookies
	 *
	 * @return array
	 */
	public static function register_cookies( $cookies = array() ) {
		$cookies['cf7_form_success'] = array(
			'labels' => array(
				'name' => __( 'Contact Form 7 Success', 'baloonup-maker' ),
			),
			'fields' => pum_get_cookie_fields(),
		);

		return $cookies;
	}

	/**
	 * Register new CF7 form editor tab.
	 *
	 * @param array $panels
	 *
	 * @return array
	 */
	public static function editor_panels( $panels = array() ) {
		return array_merge( $panels, array(
			'baloonups' => array(
				'title'    => __( 'BaloonUp Settings', 'baloonup-maker' ),
				'callback' => array( __CLASS__, 'editor_panel' ),
			),
		) );
	}

	/**
	 * Render the baloonup tab.
	 *
	 * @param object $args
	 */
	public static function editor_panel( $args ) {

		$settings = self::form_options( $args->id() ); ?>
        <h2><?php _e( 'BaloonUp Settings', 'baloonup-maker' ); ?></h2>
        <p class="description"><?php _e( 'These settings control baloonups after successful form submissions.', 'baloonup-maker' ); ?></p>
        <table class="form-table">
            <tbody>
            <tr>
	            <th scope="row">
		            <label for="mcmscf7-pum-closebaloonup"><?php _e( 'Close BaloonUp', 'baloonup-maker' ); ?></label>
	            </th>
	            <td>
		            <input type="checkbox" id="mcmscf7-pum-closebaloonup" name="mcmscf7-pum[closebaloonup]" value="true" <?php checked( $settings['closebaloonup'], true ); ?> />
	            </td>
            </tr>
            <tr id="mcmscf7-pum-closedelay-wrapper">
	            <th scope="row">
		            <label for="mcmscf7-pum-closedelay"><?php _e( 'Delay', 'baloonup-maker' ); ?></label>
	            </th>
	            <td>
		            <?php if ( strlen( $settings['closedelay'] ) >= 3 ) {
			            $settings['closedelay'] = $settings['closedelay'] / 1000;
		            } ?>

		            <input type="number" id="mcmscf7-pum-closedelay" min="0" step="1" name="mcmscf7-pum[closedelay]" style="width: 100px;" value="<?php esc_attr_e( $settings['closedelay'] ); ?>" /><?php _e( 'seconds', 'baloonup-maker' ); ?>
	            </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mcmscf7-pum-openbaloonup"><?php _e( 'Open BaloonUp', 'baloonup-maker' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="mcmscf7-pum-openbaloonup" name="mcmscf7-pum[openbaloonup]" value="true" <?php checked( $settings['openbaloonup'], true ); ?> />
                </td>
            </tr>
            <tr id="mcmscf7-pum-openbaloonup_id-wrapper">
                <th scope="row">
                    <label for="mcmscf7-pum-openbaloonup_id"><?php _e( 'BaloonUp', 'baloonup-maker' ); ?></label>
                </th>
                <td>
                    <select id="mcmscf7-pum-openbaloonup_id" name="mcmscf7-pum[openbaloonup_id]">
						<?php foreach ( self::get_baloonup_list() as $option ) { ?>
                            <option value="<?php esc_attr_e( $option['value'] ); ?>" <?php selected( $settings['openbaloonup_id'], $option['value'] ); ?>><?php echo $option['label']; ?></option>
						<?php } ?>
                    </select>
                </td>
            </tr>

            </tbody>
        </table>
        <script>
            (function ($) {
                var $open = $('#mcmscf7-pum-openbaloonup'),
                    $close = $('#mcmscf7-pum-closebaloonup'),
                    $baloonup_id_wrapper = $('#mcmscf7-pum-openbaloonup_id-wrapper'),
                    $delay_wrapper = $('#mcmscf7-pum-closedelay-wrapper');

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
		<?php
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
	 *
	 * @param $args
	 */
	public static function save( $args ) {
		if ( ! empty( $_POST['mcmscf7-pum'] ) ) {
			$settings = $_POST['mcmscf7-pum'];

			// Sanitize values.
			$settings['openbaloonup']    = ! empty( $settings['openbaloonup'] );
			$settings['openbaloonup_id'] = ! empty( $settings['openbaloonup_id'] ) ? absint( $settings['openbaloonup_id'] ) : 0;
			$settings['closebaloonup']   = ! empty( $settings['closebaloonup'] );
			$settings['closedelay']   = ! empty( $settings['closedelay'] ) ? absint( $settings['closedelay'] ) : 0;

			update_option( 'cf7_pum_' . $args->id(), $settings );
		} else {
			delete_option( 'cf7_pum_' . $args->id() );
		}
	}
}

add_action( 'init', 'PUM_CF7_Integration::init' );
