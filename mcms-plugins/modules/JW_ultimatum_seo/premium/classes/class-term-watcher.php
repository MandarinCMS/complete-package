<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Term_Watcher
 */
class MCMSSEO_Term_Watcher extends MCMSSEO_Watcher {

	/**
	 * Type of watcher.
	 *
	 * This will be used for the filters.
	 *
	 * @var string
	 */
	protected $watch_type = 'term';

	/**
	 * Used when the slug is changed using quick edit
	 *
	 * @var string
	 */
	protected $old_url = '';

	/**
	 * Constructing the object
	 */
	public function __construct() {
		$this->set_hooks();
	}

	/**
	 * Load needed js file
	 *
	 * @param string $current_page The page that is opened at the moment.
	 */
	public function page_scripts( $current_page ) {
		if ( $current_page === 'edit-tags.php' ) {
			mcms_enqueue_script( 'mcms-seo-premium-quickedit-notification', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcms-seo-premium-quickedit-notification-352' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery' ), MCMSSEO_VERSION );
			mcms_localize_script( 'mcms-seo-premium-quickedit-notification', 'mcmsseo_premium_strings', MCMSSEO_Premium_Javascript_Strings::strings() );
		}

		if ( $current_page === 'term.php' ) {
			mcms_enqueue_script( 'mcms-seo-premium-redirect-notifications', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcms-seo-premium-redirect-notifications-352' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery' ), MCMSSEO_VERSION );
			mcms_localize_script( 'mcms-seo-premium-redirect-notifications', 'mcmsseo_premium_strings', MCMSSEO_Premium_Javascript_Strings::strings() );
		}
	}

	/**
	 * Add an extra field to term edit screen
	 *
	 * @param string $tag      The current tag name.
	 * @param string $taxonomy The name of the current taxonomy.
	 */
	public function old_url_field( $tag, $taxonomy ) {
		$url = $this->get_target_url( $tag, $taxonomy );

		echo $this->parse_url_field( $url, 'term' );
	}

	/**
	 * Set old URL when the quick edit is used for taxonomies
	 */
	public function set_old_url_quick_edit() {
		$permalink = $this->get_taxonomy_permalink();

		if ( ! is_mcms_error( $permalink ) ) {
			$this->old_url = str_replace( home_url(), '', $permalink );
		}
	}

	/**
	 * Detect if the slug changed, hooked into 'post_updated'
	 *
	 * @param integer  $term_id  The term id.
	 * @param integer  $tt_id    The term taxonomy id.
	 * @param stdClass $taxonomy Object with the values of the taxonomy.
	 *
	 * @return bool
	 */
	public function detect_slug_change( $term_id, $tt_id, $taxonomy ) {
		/**
		 * Filter: 'mcmsseo_premium_term_redirect_slug_change' - Check if a redirect should be created on term slug change
		 *
		 * @api bool unsigned
		 */
		if ( apply_filters( 'mcmsseo_premium_term_redirect_slug_change', false ) === true ) {
			return true;
		}

		$old_url = $this->get_old_url();

		if ( ! $old_url ) {
			return false;
		}

		// Get the new URL.
		$new_url = $this->get_target_url( $term_id, $taxonomy );

		// Maybe we can undo the created redirect.
		$this->notify_undo_slug_redirect( $old_url, $new_url );
	}

	/**
	 * Offer to create a redirect from the term that is about to get deleted
	 *
	 * @param integer $term_id The term id that will be deleted.
	 */
	public function detect_term_delete( $term_id ) {
		// When term is a menu don't show the redirect creation notice.
		if ( is_nav_menu( $term_id ) ) {
			return;
		}

		global $mcmsdb;

		// Get the term and taxonomy from the term_taxonomy table.
		$term_row = $mcmsdb->get_row( $mcmsdb->prepare( 'SELECT `term_id`, `taxonomy` FROM `' . $mcmsdb->term_taxonomy . '` WHERE `term_taxonomy_id` = %d ', $term_id ) );

		// Check result.
		if ( null !== $term_row ) {

			// Get the URL.
			$url = $this->get_target_url( get_term( $term_row->term_id, $term_row->taxonomy ), $term_row->taxonomy );

			$this->set_delete_notification( $url );
		}
	}

	/**
	 * Get the URL to the term and returns it's path
	 *
	 * @param string $tag      The current tag name.
	 * @param string $taxonomy The name of the current taxonomy.
	 *
	 * @return string
	 */
	protected function get_target_url( $tag, $taxonomy ) {
		// Use the correct URL path.
		$url = parse_url( get_term_link( $tag, $taxonomy ) );
		$url = $url['path'];

		return $url;
	}

	/**
	 * Get permalink for taxonomy
	 *
	 * @return string|MCMS_Error
	 */
	protected function get_taxonomy_permalink() {
		return get_term_link( get_term( filter_input( INPUT_POST, 'tax_ID' ), filter_input( INPUT_POST, 'taxonomy' ) ), filter_input( INPUT_POST, 'taxonomy' ) );
	}

	/**
	 * Get the old URL
	 *
	 * @return bool|string
	 */
	protected function get_old_url() {
		$mcmsseo_old_term_url = filter_input( INPUT_POST, 'mcmsseo_old_term_url' );

		if ( empty( $mcmsseo_old_term_url ) ) {
			if ( ! empty( $this->old_url ) ) {
				return $this->old_url;
			}

			return false;
		}
		return $mcmsseo_old_term_url;
	}

	/**
	 * Setting the hooks for the term watcher
	 */
	protected function set_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'page_scripts' ) );

		// Get all taxonomies.
		$taxonomies = get_taxonomies();

		// Loop through all taxonomies.
		if ( count( $taxonomies ) > 0 ) {
			foreach ( $taxonomies as $taxonomy ) {
				// Add old URL field to term edit screen.
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'old_url_field' ), 10, 2 );
			}
		}

		add_action( 'mcms_ajax_inline-save-tax', array( $this, 'set_old_url_quick_edit' ), 1 );

		// Detect the term slug change.
		add_action( 'edited_term', array( $this, 'detect_slug_change' ), 10, 3 );

		// Detect a term delete.
		add_action( 'delete_term_taxonomy', array( $this, 'detect_term_delete' ) );
	}

	/**
	 * Returns the undo message for the term.
	 *
	 * @return string
	 */
	protected function get_undo_slug_notification() {
		/* translators: %1$s: Ultimatum SEO, %2$s and %3$s expand to a link to the admin page, %4$s: Old slug of the term, %5$s: New slug of the term, the text surrounded by %6$s and %7$s is placed in a button that can undo the created redirect */
		return __(
			'%1$s created a %2$sredirect%3$s from the old term URL to the new term URL. %6$sClick here to undo this%7$s  <br> Old URL: %4$s <br> New URL: %5$s',
			'mandarincms-seo-premium'
		);
	}

	/**
	 * Returns the delete message for the term.
	 *
	 * @return string
	 */
	protected function get_delete_notification() {
		/* translators: %1$s: Ultimatum SEO, %2$s: List with actions, %3$s: <a href='{post_with_explaination.}'>, %4$s: </a> */
		return __(
			'%1$s detected that you deleted a term. You can either: %2$s Don\'t know what to do? %3$sRead this post %4$s.',
			'mandarincms-seo-premium'
		);
	}
}
