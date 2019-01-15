<?php
/**
 * Administration API: MCMS_Internal_Pointers class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * Core class used to implement an internal admin pointers API.
 *
 * @since 3.3.0
 */
final class MCMS_Internal_Pointers {
	/**
	 * Initializes the new feature pointers.
	 *
	 * @since 3.3.0
	 *
	 * All pointers can be disabled using the following:
	 *     remove_action( 'admin_enqueue_scripts', array( 'MCMS_Internal_Pointers', 'enqueue_scripts' ) );
	 *
	 * Individual pointers (e.g. mcms390_widgets) can be disabled using the following:
	 *     remove_action( 'admin_print_footer_scripts', array( 'MCMS_Internal_Pointers', 'pointer_mcms390_widgets' ) );
	 *
	 * @static
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_scripts( $hook_suffix ) {
		/*
		 * Register feature pointers
		 *
		 * Format:
		 *     array(
		 *         hook_suffix => pointer callback
		 *     )
		 *
		 * Example:
		 *     array(
		 *         'myskins.php' => 'mcms390_widgets'
		 *     )
		 */
		$registered_pointers = array(
			'index.php' => 'mcms496_privacy',
		);

		// Check if screen related pointer is registered
		if ( empty( $registered_pointers[ $hook_suffix ] ) )
			return;

		$pointers = (array) $registered_pointers[ $hook_suffix ];

		/*
		 * Specify required capabilities for feature pointers
		 *
		 * Format:
		 *     array(
		 *         pointer callback => Array of required capabilities
		 *     )
		 *
		 * Example:
		 *     array(
		 *         'mcms390_widgets' => array( 'edit_myskin_options' )
		 *     )
		 */
		$caps_required = array(
			'mcms496_privacy' => array(
				'manage_privacy_options',
				'export_others_personal_data',
				'erase_others_personal_data',
			),
		);

		// Get dismissed pointers
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true ) );

		$got_pointers = false;
		foreach ( array_diff( $pointers, $dismissed ) as $pointer ) {
			if ( isset( $caps_required[ $pointer ] ) ) {
				foreach ( $caps_required[ $pointer ] as $cap ) {
					if ( ! current_user_can( $cap ) )
						continue 2;
				}
			}

			// Bind pointer print function
			add_action( 'admin_print_footer_scripts', array( 'MCMS_Internal_Pointers', 'pointer_' . $pointer ) );
			$got_pointers = true;
		}

		if ( ! $got_pointers )
			return;

		// Add pointers script and style to queue
		mcms_enqueue_style( 'mcms-pointer' );
		mcms_enqueue_script( 'mcms-pointer' );
	}

	/**
	 * Print the pointer JavaScript data.
	 *
	 * @since 3.3.0
	 *
	 * @static
	 *
	 * @param string $pointer_id The pointer ID.
	 * @param string $selector The HTML elements, on which the pointer should be attached.
	 * @param array  $args Arguments to be passed to the pointer JS (see mcms-pointer.js).
	 */
	private static function print_js( $pointer_id, $selector, $args ) {
		if ( empty( $pointer_id ) || empty( $selector ) || empty( $args ) || empty( $args['content'] ) )
			return;

		?>
		<script type="text/javascript">
		(function($){
			var options = <?php echo mcms_json_encode( $args ); ?>, setup;

			if ( ! options )
				return;

			options = $.extend( options, {
				close: function() {
					$.post( ajaxurl, {
						pointer: '<?php echo $pointer_id; ?>',
						action: 'dismiss-mcms-pointer'
					});
				}
			});

			setup = function() {
				$('<?php echo $selector; ?>').first().pointer( options ).pointer('open');
			};

			if ( options.position && options.position.defer_loading )
				$(window).bind( 'load.mcms-pointers', setup );
			else
				$(document).ready( setup );

		})( jQuery );
		</script>
		<?php
	}

	public static function pointer_mcms330_toolbar() {}
	public static function pointer_mcms330_media_uploader() {}
	public static function pointer_mcms330_saving_widgets() {}
	public static function pointer_mcms340_customize_current_myskin_link() {}
	public static function pointer_mcms340_choose_image_from_library() {}
	public static function pointer_mcms350_media() {}
	public static function pointer_mcms360_revisions() {}
	public static function pointer_mcms360_locks() {}
	public static function pointer_mcms390_widgets() {}
	public static function pointer_mcms410_dfw() {}

	/**
	 * Display a pointer for the new privacy tools.
	 *
	 * @since 4.9.6
	 */
	public static function pointer_mcms496_privacy() {
		$content  = '<h3>' . __( 'Personal Data and Privacy' ) . '</h3>';
		$content .= '<h4>' . __( 'Personal Data Export and Erasure' ) . '</h4>';
		$content .= '<p>' . __( 'New <strong>Tools</strong> have been added to help you with personal data export and erasure requests.' ) . '</p>';
		$content .= '<h4>' . __( 'Privacy Policy' ) . '</h4>';
		$content .= '<p>' . __( 'Create or select your site&#8217;s privacy policy page under <strong>Settings &gt; Privacy</strong> to keep your users informed and aware.' ) . '</p>';

		if ( is_rtl() ) {
			$position = array(
				'edge'  => 'right',
				'align' => 'bottom',
			);
		} else {
			$position = array(
				'edge'  => 'left',
				'align' => 'bottom',
			);
		}

		$js_args = array(
			'content'  => $content,
			'position' => $position,
			'pointerClass' => 'mcms-pointer arrow-bottom',
			'pointerWidth' => 420,
		);
		self::print_js( 'mcms496_privacy', '#menu-tools', $js_args );
	}

	/**
	 * Prevents new users from seeing existing 'new feature' pointers.
	 *
	 * @since 3.3.0
	 *
	 * @static
	 *
	 * @param int $user_id User ID.
	 */
	public static function dismiss_pointers_for_new_users( $user_id ) {
		add_user_meta( $user_id, 'dismissed_mcms_pointers', 'mcms496_privacy' );
	}
}
