<?php
/**
 * Customize API: MCMS_Customize_MySkin_Control class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize MySkin Control class.
 *
 * @since 4.2.0
 *
 * @see MCMS_Customize_Control
 */
class MCMS_Customize_MySkin_Control extends MCMS_Customize_Control {

	/**
	 * Customize control type.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public $type = 'myskin';

	/**
	 * MySkin object.
	 *
	 * @since 4.2.0
	 * @var MCMS_MySkin
	 */
	public $myskin;

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 4.2.0
	 *
	 * @see MCMS_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();
		$this->json['myskin'] = $this->myskin;
	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 *
	 * @since 4.2.0
	 */
	public function render_content() {}

	/**
	 * Render a JS template for myskin display.
	 *
	 * @since 4.2.0
	 */
	public function content_template() {
		/* translators: %s: myskin name */
		$details_label = sprintf( __( 'Details for myskin: %s' ), '{{ data.myskin.name }}' );
		/* translators: %s: myskin name */
		$customize_label = sprintf( __( 'Customize myskin: %s' ), '{{ data.myskin.name }}' );
		/* translators: %s: myskin name */
		$preview_label = sprintf( __( 'Live preview myskin: %s' ), '{{ data.myskin.name }}' );
		/* translators: %s: myskin name */
		$install_label = sprintf( __( 'Install and preview myskin: %s' ), '{{ data.myskin.name }}' );
		?>
		<# if ( data.myskin.active ) { #>
			<div class="myskin active" tabindex="0" aria-describedby="{{ data.section }}-{{ data.myskin.id }}-action">
		<# } else { #>
			<div class="myskin" tabindex="0" aria-describedby="{{ data.section }}-{{ data.myskin.id }}-action">
		<# } #>

			<# if ( data.myskin.screenshot && data.myskin.screenshot[0] ) { #>
				<div class="myskin-screenshot">
					<img data-src="{{ data.myskin.screenshot[0] }}" alt="" />
				</div>
			<# } else { #>
				<div class="myskin-screenshot blank"></div>
			<# } #>

			<span class="more-details myskin-details" id="{{ data.section }}-{{ data.myskin.id }}-action" aria-label="<?php echo esc_attr( $details_label ); ?>"><?php _e( 'MySkin Details' ); ?></span>

			<div class="myskin-author"><?php
				/* translators: MySkin author name */
				printf( _x( 'By %s', 'myskin author' ), '{{ data.myskin.author }}' );
			?></div>

			<# if ( 'installed' === data.myskin.type && data.myskin.hasUpdate ) { #>
				<div class="update-message notice inline notice-warning notice-alt" data-slug="{{ data.myskin.id }}">
					<p>
						<?php
						/* translators: %s: "Update now" button */
						printf( __( 'New version available. %s' ), '<button class="button-link update-myskin" type="button">' . __( 'Update now' ) . '</button>' );
						?>
					</p>
				</div>
			<# } #>

			<# if ( data.myskin.active ) { #>
				<div class="myskin-id-container">
					<h3 class="myskin-name" id="{{ data.section }}-{{ data.myskin.id }}-name">
						<?php
						/* translators: %s: myskin name */
						printf( __( '<span>Previewing:</span> %s' ), '{{ data.myskin.name }}' );
						?>
					</h3>
					<div class="myskin-actions">
						<button type="button" class="button button-primary customize-myskin" aria-label="<?php echo esc_attr( $customize_label ); ?>"><?php _e( 'Customize' ); ?></button>
					</div>
				</div>
				<div class="notice notice-success notice-alt"><p><?php _ex( 'Installed', 'myskin' ); ?></p></div>
			<# } else if ( 'installed' === data.myskin.type ) { #>
				<div class="myskin-id-container">
					<h3 class="myskin-name" id="{{ data.section }}-{{ data.myskin.id }}-name">{{ data.myskin.name }}</h3>
					<div class="myskin-actions">
						<button type="button" class="button button-primary preview-myskin" aria-label="<?php echo esc_attr( $preview_label ); ?>" data-slug="{{ data.myskin.id }}"><?php _e( 'Live Preview' ); ?></button>
					</div>
				</div>
				<div class="notice notice-success notice-alt"><p><?php _ex( 'Installed', 'myskin' ); ?></p></div>
			<# } else { #>
				<div class="myskin-id-container">
					<h3 class="myskin-name" id="{{ data.section }}-{{ data.myskin.id }}-name">{{ data.myskin.name }}</h3>
					<div class="myskin-actions">
						<button type="button" class="button button-primary myskin-install preview" aria-label="<?php echo esc_attr( $install_label ); ?>" data-slug="{{ data.myskin.id }}" data-name="{{ data.myskin.name }}"><?php _e( 'Install &amp; Preview' ); ?></button>
					</div>
				</div>
			<# } #>
		</div>
	<?php
	}
}
