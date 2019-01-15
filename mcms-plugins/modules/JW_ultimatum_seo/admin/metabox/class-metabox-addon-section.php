<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Generates and displays a section containing metabox tabs that have been added by other modules through the
 * `mcmsseo_tab_header` and `mcmsseo_tab_content` actions.
 */
class MCMSSEO_Metabox_Addon_Tab_Section extends MCMSSEO_Metabox_Tab_Section {

	/**
	 * Applies the actions for adding a tab to the metabox.
	 */
	public function display_content() {
		?>
		<div id="mcmsseo-meta-section-addons" class="mcmsseo-meta-section">
			<div class="mcmsseo-metabox-tabs-div">
				<ul class="mcmsseo-metabox-tabs">
					<?php do_action( 'mcmsseo_tab_header' ); ?>
				</ul>
			</div>
			<?php do_action( 'mcmsseo_tab_content' ); ?>
		</div>
	<?php
	}

	/**
	 * `MCMSSEO_Metabox_Addon_Section` always has "tabs", represented by registered actions. If this is not the case,
	 * it should not be instantiated.
	 *
	 * @return bool
	 */
	protected function has_tabs() {
		return true;
	}
}
