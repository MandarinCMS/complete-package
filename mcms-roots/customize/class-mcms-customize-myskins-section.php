<?php
/**
 * Customize API: MCMS_Customize_MySkins_Section class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize MySkins Section class.
 *
 * A UI container for myskin controls, which are displayed within sections.
 *
 * @since 4.2.0
 *
 * @see MCMS_Customize_Section
 */
class MCMS_Customize_MySkins_Section extends MCMS_Customize_Section {

	/**
	 * Section type.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public $type = 'myskins';

	/**
	 * MySkin section action.
	 *
	 * Defines the type of myskins to load (installed, mcmsorg, etc.).
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $action = '';

	/**
	 * MySkin section filter type.
	 *
	 * Determines whether filters are applied to loaded (local) myskins or by initiating a new remote query (remote).
	 * When filtering is local, the initial myskins query is not paginated by default.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $filter_type = 'local';

	/**
	 * Get section parameters for JS.
	 *
	 * @since 4.9.0
	 * @return array Exported parameters.
	 */
	public function json() {
		$exported = parent::json();
		$exported['action'] = $this->action;
		$exported['filter_type'] = $this->filter_type;

		return $exported;
	}

	/**
	 * Render a myskins section as a JS template.
	 *
	 * The template is only rendered by PHP once, so all actions are prepared at once on the server side.
	 *
	 * @since 4.9.0
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="myskin-section">
			<button type="button" class="customize-myskins-section-title myskins-section-{{ data.id }}">{{ data.title }}</button>
			<?php if ( current_user_can( 'install_myskins' ) || is_multisite() ) : // @todo: upload support ?>
			<?php endif; ?>
			<div class="customize-myskins-section myskins-section-{{ data.id }} control-section-content myskins-php">
				<div class="myskin-overlay" tabindex="0" role="dialog" aria-label="<?php esc_attr_e( 'MySkin Details' ); ?>"></div>
				<div class="myskin-browser rendered">
					<div class="customize-preview-header myskins-filter-bar">
						<?php $this->filter_bar_content_template(); ?>
					</div>
					<?php $this->filter_drawer_content_template(); ?>
					<div class="error unexpected-error" style="display: none; "><p><?php _e( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://mandarincms.com/support/">support forums</a>.' ); ?></p></div>
					<ul class="myskins">
					</ul>
					<p class="no-myskins"><?php _e( 'No myskins found. Try a different search.' ); ?></p>
					<p class="no-myskins-local">
						<?php
						/* translators: %s: "Search MandarinCMS.org myskins" button */
						printf( __( 'No myskins found. Try a different search, or %s.' ),
							sprintf( '<button type="button" class="button-link search-dotorg-myskins">%s</button>', __( 'Search MandarinCMS.org myskins' ) )
						);
						?>
					</p>
					<p class="spinner"></p>
				</div>
			</div>
		</li>
		<?php
	}

	/**
	 * Render the filter bar portion of a myskins section as a JS template.
	 *
	 * The template is only rendered by PHP once, so all actions are prepared at once on the server side.
	 * The filter bar container is rendered by @see `render_template()`.
	 *
	 * @since 4.9.0
	 */
	protected function filter_bar_content_template() {
		?>
		<button type="button" class="button button-primary customize-section-back customize-myskins-mobile-back"><?php _e( 'Back to myskin sources' ); ?></button>
		<# if ( 'mcmsorg' === data.action ) { #>
			<div class="search-form">
				<label for="mcms-filter-search-input-{{ data.id }}" class="screen-reader-text"><?php _e( 'Search myskins&hellip;' ); ?></label>
				<input type="search" id="mcms-filter-search-input-{{ data.id }}" placeholder="<?php esc_attr_e( 'Search myskins&hellip;' ); ?>" aria-describedby="{{ data.id }}-live-search-desc" class="mcms-filter-search">
				<div class="search-icon" aria-hidden="true"></div>
				<span id="{{ data.id }}-live-search-desc" class="screen-reader-text"><?php _e( 'The search results will be updated as you type.' ); ?></span>
			</div>
			<button type="button" class="button feature-filter-toggle">
				<span class="filter-count-0"><?php _e( 'Filter myskins' ); ?></span><span class="filter-count-filters">
				<?php
				/* translators: %s: number of filters selected. */
				printf( __( 'Filter myskins (%s)' ), '<span class="myskin-filter-count">0</span>' );
				?>
				</span>
			</button>
		<# } else { #>
			<div class="myskins-filter-container">
				<label for="{{ data.id }}-myskins-filter" class="screen-reader-text"><?php _e( 'Search myskins&hellip;' ); ?></label>
				<input type="search" id="{{ data.id }}-myskins-filter" placeholder="<?php esc_attr_e( 'Search myskins&hellip;' ); ?>" aria-describedby="{{ data.id }}-live-search-desc" class="mcms-filter-search mcms-filter-search-myskins" />
				<div class="search-icon" aria-hidden="true"></div>
				<span id="{{ data.id }}-live-search-desc" class="screen-reader-text"><?php _e( 'The search results will be updated as you type.' ); ?></span>
			</div>
		<# } #>
		<div class="filter-myskins-count">
			<span class="myskins-displayed">
				<?php
				/* translators: %s: number of myskins displayed. */
				echo sprintf( __( '%s myskins' ), '<span class="myskin-count">0</span>' );
				?>
			</span>
		</div>
		<?php
	}

	/**
	 * Render the filter drawer portion of a myskins section as a JS template.
	 *
	 * The filter bar container is rendered by @see `render_template()`.
	 *
	 * @since 4.9.0
	 */
	protected function filter_drawer_content_template() {
		$feature_list = get_myskin_feature_list( false ); // @todo: Use the .org API instead of the local core feature list. The .org API is currently outdated and will be reconciled when the .org myskins directory is next redesigned.
		?>
		<# if ( 'mcmsorg' === data.action ) { #>
			<div class="filter-drawer filter-details">
				<?php foreach ( $feature_list as $feature_name => $features ) : ?>
					<fieldset class="filter-group">
						<legend><?php echo esc_html( $feature_name ); ?></legend>
						<div class="filter-group-feature">
							<?php foreach ( $features as $feature => $feature_name ) : ?>
								<input type="checkbox" id="filter-id-<?php echo esc_attr( $feature ); ?>" value="<?php echo esc_attr( $feature ); ?>" />
								<label for="filter-id-<?php echo esc_attr( $feature ); ?>"><?php echo esc_html( $feature_name ); ?></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endforeach; ?>
			</div>
		<# } #>
		<?php
	}
}
