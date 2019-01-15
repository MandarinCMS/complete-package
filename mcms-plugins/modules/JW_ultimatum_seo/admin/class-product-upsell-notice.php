<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Represents the upsell notice.
 */
class MCMSSEO_Product_Upsell_Notice {

	const USER_META_DISMISSED = 'mcmsseo-remove-upsell-notice';

	const OPTION_NAME = 'mcmsseo';

	/** @var array */
	protected $options;

	/**
	 * Sets the options, because they always have to be there on instance.
	 */
	public function __construct() {
		$this->options = $this->get_options();
	}

	/**
	 * Checks if the notice should be added or removed.
	 */
	public function initialize() {
		if ( $this->is_notice_dismissed() ) {
			$this->remove_notification();

			return;
		}

		if ( $this->should_add_notification() ) {
			$this->add_notification();
		}
	}

	/**
	 * Sets the upgrade notice.
	 */
	public function set_upgrade_notice() {

		if ( $this->has_first_activated_on() ) {
			return;
		}

		$this->set_first_activated_on();
		$this->add_notification();
	}

	/**
	 * Listener for the upsell notice.
	 */
	public function dismiss_notice_listener() {
		if ( filter_input( INPUT_GET, 'ultimatum_dismiss' ) !== 'upsell' ) {
			return;
		}

		$this->dismiss_notice();

		mcms_redirect( admin_url( 'admin.php?page=mcmsseo_dashboard' ) );
		exit;
	}

	/**
	 * When the notice should be shown.
	 *
	 * @return bool
	 */
	protected function should_add_notification() {
		return ( $this->options['first_activated_on'] < strtotime( '-2weeks' ) );
	}

	/**
	 * Checks if the options has a first activated on date value.
	 */
	protected function has_first_activated_on() {
		return $this->options['first_activated_on'] !== false;
	}

	/**
	 * Sets the first activated on.
	 */
	protected function set_first_activated_on() {
		$this->options['first_activated_on'] = strtotime( '-2weeks' );

		$this->save_options();
	}

	/**
	 * Adds a notification to the notification center.
	 */
	protected function add_notification() {
		$notification_center = Ultimatum_Notification_Center::get();
		$notification_center->add_notification( $this->get_notification() );
	}

	/**
	 * Adds a notification to the notification center.
	 */
	protected function remove_notification() {
		$notification_center = Ultimatum_Notification_Center::get();
		$notification_center->remove_notification( $this->get_notification() );
	}

	/**
	 * Returns a premium upsell section if using the free module.
	 *
	 * @return string
	 */
	protected function get_premium_upsell_section() {
		$features = new MCMSSEO_Features();
		if ( $features->is_free() ) {
			/* translators: %1$s expands anchor to premium module page, %2$s expands to </a> */
			return sprintf(
				__( 'By the way, did you know we also have a %1$sPremium module%2$s? It offers advanced features, like a redirect manager and support for multiple keywords. It also comes with 24/7 personal support.' , 'mandarincms-seo' ),
				"<a href='https://jiiworks.net/premium-notification'>",
				'</a>'
			);
		}

		return '';
	}

	/**
	 * Gets the notification value.
	 *
	 * @return Ultimatum_Notification
	 */
	protected function get_notification() {
		/* translators: %1$s expands anchor to module page on MandarinCMS.org, %2$s expands anchor to the bugreport guidelines on the knowledge base, %3$s expands to a section about Premium, %4$a expands to the notice dismissal anchor, %5$s expands to </a> */
		$message = sprintf(
			__( "We've noticed you've been using Ultimatum SEO for some time now; we hope you love it!
			
			We'd be thrilled if you could %1\$sgive us a 5* rating on MandarinCMS.org%5\$s! If you are experiencing issues, %2\$splease file a bug report%5\$s and we'll do our best to help you out.
			
			%3\$s

			%4\$sPlease don't show me this notification anymore%5\$s", 'mandarincms-seo' ),
			"<a href='https://jiiworks.net/rate-ultimatum-seo'>",
			"<a href='https://jiiworks.net/bugreport'>",
			$this->get_premium_upsell_section(),
			"<a class='button' href=' " . admin_url( '?page=' .  MCMSSEO_Admin::PAGE_IDENTIFIER . '&ultimatum_dismiss=upsell' ) . " '>",
			'</a>'
		);

		$notification = new Ultimatum_Notification(
			$message,
			array(
				'type'         => Ultimatum_Notification::WARNING,
				'id'           => 'mcmsseo-upsell-notice',
				'capabilities' => 'manage_options',
				'priority'     => 0.8,
			)
		);

		return $notification;
	}

	/**
	 * Dismisses the notice.
	 *
	 * @return string
	 */
	protected function is_notice_dismissed() {
		return get_user_meta( get_current_user_id(), self::USER_META_DISMISSED, true ) === '1';
	}

	/**
	 * Dismisses the notice.
	 */
	protected function dismiss_notice() {
		update_user_meta( get_current_user_id(), self::USER_META_DISMISSED, true );
	}

	/**
	 * Returns the set options
	 *
	 * @return mixed|void
	 */
	protected function get_options() {
		return get_option( self::OPTION_NAME );
	}

	/**
	 * Saves the options to the database.
	 */
	protected function save_options() {
		update_option( self::OPTION_NAME, $this->options );
	}
}
