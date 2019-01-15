<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Class to change or add MandarinCMS dashboard widgets
 */
class Ultimatum_Dashboard_Widget {

	const CACHE_TRANSIENT_KEY = 'mcmsseo-dashboard-totals';

	/**
	 * @var MCMSSEO_Statistics
	 */
	protected $statistics;

	/**
	 * @param MCMSSEO_Statistics $statistics The statistics class to retrieve statistics from.
	 */
	public function __construct( MCMSSEO_Statistics $statistics = null ) {
		if ( null === $statistics ) {
			$statistics = new MCMSSEO_Statistics();
		}

		$this->statistics = $statistics;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_stylesheet' ) );
		add_action( 'mcms_insert_post', array( $this, 'clear_cache' ) );
		add_action( 'delete_post', array( $this, 'clear_cache' ) );

		if ( $this->show_widget() ) {
			add_action( 'mcms_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		}
	}

	/**
	 * Adds dashboard widget to MandarinCMS
	 */
	public function add_dashboard_widget() {
		mcms_add_dashboard_widget(
			'mcmsseo-dashboard-overview',
			/* translators: %s is the module name */
			sprintf( __( '%s Posts Overview', 'mandarincms-seo' ), 'Ultimatum SEO' ),
			array( $this, 'display_dashboard_widget' )
		);
	}

	/**
	 * Display the dashboard widget
	 */
	public function display_dashboard_widget() {
		$statistics = $this->statistic_items();

		$onpage_option = new MCMSSEO_OnPage_Option();
		$onpage        = false;
		if ( $onpage_option->is_enabled() ) {
			$onpage = array(
					'indexable' => $onpage_option->get_status(),
					'can_fetch' => $onpage_option->should_be_fetched(),
			);
		}

		include MCMSSEO_PATH . '/admin/views/dashboard-widget.php';
	}

	/**
	 * Enqueue's stylesheet for the dashboard if the current page is the dashboard
	 */
	public function enqueue_dashboard_stylesheet() {
		$current_screen = get_current_screen();

		if ( $current_screen instanceof MCMS_Screen && 'dashboard' === $current_screen->id ) {
			$asset_manager = new MCMSSEO_Admin_Asset_Manager();
			$asset_manager->enqueue_style( 'mcms-dashboard' );
		}
	}

	/**
	 * Clears the dashboard widget items cache
	 */
	public function clear_cache() {
		delete_transient( self::CACHE_TRANSIENT_KEY );
	}

	/**
	 * An array representing items to be added to the At a Glance dashboard widget
	 *
	 * @return array
	 */
	private function statistic_items() {
		$transient = get_transient( self::CACHE_TRANSIENT_KEY );
		$user_id   = get_current_user_id();

		if ( isset( $transient[ $user_id ] ) ) {
			return $transient[ $user_id ];
		}

		return $this->set_statistic_items_for_this_user( $transient );
	}

	/**
	 * Set the cache for a specific user
	 *
	 * @param array|boolean $transient The current stored transient with the cached data.
	 *
	 * @return mixed
	 */
	private function set_statistic_items_for_this_user( $transient ) {
		if ( $transient === false ) {
			$transient = array();
		}

		$user_id               = get_current_user_id();
		$transient[ $user_id ] = array_filter( $this->get_seo_scores_with_post_count(), array( $this, 'filter_items' ) );

		set_transient( self::CACHE_TRANSIENT_KEY, $transient, DAY_IN_SECONDS );

		return $transient[ $user_id ];
	}

	/**
	 * Set the SEO scores belonging to their SEO score result
	 *
	 * @return array
	 */
	private function get_seo_scores_with_post_count() {
		$ranks = MCMSSEO_Rank::get_all_ranks();

		return array_map( array( $this, 'map_rank_to_widget' ), $ranks );
	}

	/**
	 * Converts a rank to data usable in the dashboard widget
	 *
	 * @param MCMSSEO_Rank $rank The rank to map.
	 *
	 * @return array
	 */
	private function map_rank_to_widget( MCMSSEO_Rank $rank ) {
		return array(
			'seo_rank'   => $rank->get_rank(),
			'title'      => $this->get_title_for_rank( $rank ),
			'class'      => 'mcmsseo-glance-' . $rank->get_css_class(),
			'icon_class' => $rank->get_css_class(),
			'count'      => $this->statistics->get_post_count( $rank ),
		);
	}

	/**
	 * Returns a dashboard widget label to use for a certain rank
	 *
	 * @param MCMSSEO_Rank $rank The rank to return a label for.
	 *
	 * @return string
	 */
	private function get_title_for_rank( MCMSSEO_Rank $rank ) {
		$labels = array(
			MCMSSEO_Rank::NO_FOCUS => __( 'Posts without focus keyword', 'mandarincms-seo' ),
			MCMSSEO_Rank::BAD      => __( 'Posts with bad SEO score', 'mandarincms-seo' ),
			MCMSSEO_Rank::OK       => __( 'Posts with OK SEO score', 'mandarincms-seo' ),
			MCMSSEO_Rank::GOOD     => __( 'Posts with good SEO score', 'mandarincms-seo' ),
			/* translators: %s expands to <span lang="en">noindex</span> */
			MCMSSEO_Rank::NO_INDEX => sprintf( __( 'Posts that are set to &#8220;%s&#8221;', 'mandarincms-seo' ), '<span lang="en">noindex</span>' ),
		);

		return $labels[ $rank->get_rank() ];
	}

	/**
	 * Filter items if they have a count of zero
	 *
	 * @param array $item Data array.
	 *
	 * @return bool
	 */
	private function filter_items( $item ) {
		return 0 !== $item['count'];
	}

	/**
	 * Returns true when the dashboard widget should be shown.
	 *
	 * @return bool
	 */
	private function show_widget() {
		$analysis_seo = new MCMSSEO_Metabox_Analysis_SEO();

		return $analysis_seo->is_enabled() && current_user_can( 'edit_posts' );
	}
}
