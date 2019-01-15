<?php
/**
 * Customize API: MCMS_Customize_MySkins_Panel class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.9.0
 */

/**
 * Customize MySkins Panel Class
 *
 * @since 4.9.0
 *
 * @see MCMS_Customize_Panel
 */
class MCMS_Customize_MySkins_Panel extends MCMS_Customize_Panel {

	/**
	 * Panel type.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $type = 'myskins';

	/**
	 * An Underscore (JS) template for rendering this panel's container.
	 *
	 * The myskins panel renders a custom panel heading with the current myskin and a switch myskins button.
	 *
	 * @see MCMS_Customize_Panel::print_template()
	 *
	 * @since 4.9.0
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-panel-myskins">
			<h3 class="accordion-section-title">
				<?php
				if ( $this->manager->is_myskin_active() ) {
					echo '<span class="customize-action">' . __( 'Active myskin' ) . '</span> {{ data.title }}';
				} else {
					echo '<span class="customize-action">' . __( 'Previewing myskin' ) . '</span> {{ data.title }}';
				}
				?>

				<?php if ( current_user_can( 'switch_myskins' ) ) : ?>
					<button type="button" class="button change-myskin" aria-label="<?php esc_attr_e( 'Change myskin' ); ?>"><?php _ex( 'Change', 'myskin' ); ?></button>
				<?php endif; ?>
			</h3>
			<ul class="accordion-sub-container control-panel-content"></ul>
		</li>
		<?php
	}

	/**
	 * An Underscore (JS) template for this panel's content (but not its container).
	 *
	 * Class variables for this panel class are available in the `data` JS object;
	 * export custom variables by overriding MCMS_Customize_Panel::json().
	 *
	 * @since 4.9.0
	 *
	 * @see MCMS_Customize_Panel::print_template()
	 */
	protected function content_template() {
		?>
		<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #> cannot-expand<# } #>">
			<button class="customize-panel-back" tabindex="-1" type="button"><span class="screen-reader-text"><?php _e( 'Back' ); ?></span></button>
			<div class="accordion-section-title">
				<span class="preview-notice">
					<?php
					/* translators: %s: myskins panel title in the Customizer */
					echo sprintf( __( 'You are browsing %s' ), '<strong class="panel-title">' . __( 'MySkins' ) . '</strong>' ); // Separate strings for consistency with other panels.
					?>
				</span>
				<?php if ( current_user_can( 'install_myskins' ) && ! is_multisite() ) : ?>
					<# if ( data.description ) { #>
						<button class="customize-help-toggle dashicons dashicons-editor-help" type="button" aria-expanded="false"><span class="screen-reader-text"><?php _e( 'Help' ); ?></span></button>
					<# } #>
				<?php endif; ?>
			</div>
			<?php if ( current_user_can( 'install_myskins' ) && ! is_multisite() ) : ?>
				<# if ( data.description ) { #>
					<div class="description customize-panel-description">
						{{{ data.description }}}
					</div>
				<# } #>
			<?php endif; ?>

			<div class="customize-control-notifications-container"></div>
		</li>
		<li class="customize-myskins-full-container-container">
			<div class="customize-myskins-full-container">
				<div class="customize-myskins-notifications"></div>
			</div>
		</li>
		<?php
	}
}
