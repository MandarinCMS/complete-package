<?php
/**
 * @package MCMSSEO\Admin\Notifications
 */

/**
 * Class Ultimatum_Alerts
 */
class Ultimatum_Alerts {

	const ADMIN_PAGE = 'mcmsseo_dashboard';

	/** @var int Total notifications count */
	private static $notification_count = 0;

	/** @var array All error notifications */
	private static $errors = array();
	/** @var array Active errors */
	private static $active_errors = array();
	/** @var array Dismissed errors */
	private static $dismissed_errors = array();

	/** @var array All warning notifications */
	private static $warnings = array();
	/** @var array Active warnings */
	private static $active_warnings = array();
	/** @var array Dismissed warnings */
	private static $dismissed_warnings = array();

	/**
	 * Ultimatum_Alerts constructor.
	 */
	public function __construct() {

		$this->add_hooks();
	}

	/**
	 * Add hooks
	 */
	private function add_hooks() {

		$page = filter_input( INPUT_GET, 'page' );
		if ( self::ADMIN_PAGE === $page ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		// Needed for adminbar and Alerts page.
		add_action( 'admin_init', array( __CLASS__, 'collect_alerts' ), 99 );

		// Add AJAX hooks.
		add_action( 'mcms_ajax_ultimatum_dismiss_alert', array( $this, 'ajax_dismiss_alert' ) );
		add_action( 'mcms_ajax_ultimatum_restore_alert', array( $this, 'ajax_restore_alert' ) );
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_assets() {

		$asset_manager = new MCMSSEO_Admin_Asset_Manager();
		$asset_manager->enqueue_style( 'alerts' );
	}

	/**
	 * Handle ajax request to dismiss an alert
	 */
	public function ajax_dismiss_alert() {

		$notification = $this->get_notification_from_ajax_request();
		if ( $notification ) {
			$notification_center = Ultimatum_Notification_Center::get();
			$notification_center->maybe_dismiss_notification( $notification );

			$this->output_ajax_response( $notification->get_type() );
		}

		mcms_die();
	}

	/**
	 * Handle ajax request to restore an alert
	 */
	public function ajax_restore_alert() {

		$notification = $this->get_notification_from_ajax_request();
		if ( $notification ) {
			delete_user_meta( get_current_user_id(), $notification->get_dismissal_key() );

			$this->output_ajax_response( $notification->get_type() );
		}

		mcms_die();
	}

	/**
	 * Create AJAX response data
	 *
	 * @param string $type Alert type.
	 */
	private function output_ajax_response( $type ) {

		$html = $this->get_view_html( $type );
		echo mcms_json_encode(
			array(
				'html'  => $html,
				'total' => self::get_active_alert_count(),
			)
		);
	}

	/**
	 * Get the HTML to return in the AJAX request
	 *
	 * @param string $type Alert type.
	 *
	 * @return bool|string
	 */
	private function get_view_html( $type ) {

		switch ( $type ) {
			case 'error':
				$view = 'errors';
				break;

			case 'warning':
			default:
				$view = 'warnings';
				break;
		}

		// Re-collect alerts.
		self::collect_alerts();

		/** @noinspection PhpUnusedLocalVariableInspection */
		$alerts_data = self::get_template_variables();

		ob_start();
		include MCMSSEO_PATH . 'admin/views/partial-alerts-' . $view . '.php';
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Extract the Ultimatum Notification from the AJAX request
	 *
	 * @return null|Ultimatum_Notification
	 */
	private function get_notification_from_ajax_request() {

		$notification_center = Ultimatum_Notification_Center::get();
		$notification_id     = filter_input( INPUT_POST, 'notification' );

		return $notification_center->get_notification_by_id( $notification_id );
	}

	/**
	 * Show the alerts overview page
	 */
	public static function show_overview_page() {

		/** @noinspection PhpUnusedLocalVariableInspection */
		$alerts_data = self::get_template_variables();

		include MCMSSEO_PATH . 'admin/views/alerts-dashboard.php';
	}

	/**
	 * Collect the alerts and group them together
	 */
	public static function collect_alerts() {

		$notification_center = Ultimatum_Notification_Center::get();

		$notifications            = $notification_center->get_sorted_notifications();
		self::$notification_count = count( $notifications );

		self::$errors           = array_filter( $notifications, array( __CLASS__, 'filter_error_alerts' ) );
		self::$dismissed_errors = array_filter( self::$errors, array( __CLASS__, 'filter_dismissed_alerts' ) );
		self::$active_errors    = array_diff( self::$errors, self::$dismissed_errors );

		self::$warnings           = array_filter( $notifications, array( __CLASS__, 'filter_warning_alerts' ) );
		self::$dismissed_warnings = array_filter( self::$warnings, array( __CLASS__, 'filter_dismissed_alerts' ) );
		self::$active_warnings    = array_diff( self::$warnings, self::$dismissed_warnings );
	}

	/**
	 * Get the variables needed in the views
	 *
	 * @return array
	 */
	public static function get_template_variables() {

		return array(
			'metrics'  => array(
				'total'    => self::$notification_count,
				'active'   => self::get_active_alert_count(),
				'errors'   => count( self::$errors ),
				'warnings' => count( self::$warnings ),
			),
			'errors'   => array(
				'dismissed' => self::$dismissed_errors,
				'active'    => self::$active_errors,
			),
			'warnings' => array(
				'dismissed' => self::$dismissed_warnings,
				'active'    => self::$active_warnings,
			),
		);
	}

	/**
	 * Get the number of active alerts
	 *
	 * @return int
	 */
	public static function get_active_alert_count() {

		return ( count( self::$active_errors ) + count( self::$active_warnings ) );
	}

	/**
	 * Filter out any non-errors
	 *
	 * @param Ultimatum_Notification $notification Notification to test.
	 *
	 * @return bool
	 */
	private static function filter_error_alerts( Ultimatum_Notification $notification ) {

		return $notification->get_type() === 'error';
	}

	/**
	 * Filter out any non-warnings
	 *
	 * @param Ultimatum_Notification $notification Notification to test.
	 *
	 * @return bool
	 */
	private static function filter_warning_alerts( Ultimatum_Notification $notification ) {

		return $notification->get_type() !== 'error';
	}

	/**
	 * Filter out any dismissed notifications
	 *
	 * @param Ultimatum_Notification $notification Notification to test.
	 *
	 * @return bool
	 */
	private static function filter_dismissed_alerts( Ultimatum_Notification $notification ) {

		return Ultimatum_Notification_Center::is_notification_dismissed( $notification );
	}
}
