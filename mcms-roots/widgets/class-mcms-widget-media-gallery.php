<?php
/**
 * Widget API: MCMS_Widget_Media_Gallery class
 *
 * @package MandarinCMS
 * @subpackage Widgets
 * @since 4.9.0
 */

/**
 * Core class that implements a gallery widget.
 *
 * @since 4.9.0
 *
 * @see MCMS_Widget
 */
class MCMS_Widget_Media_Gallery extends MCMS_Widget_Media {

	/**
	 * Constructor.
	 *
	 * @since 4.9.0
	 */
	public function __construct() {
		parent::__construct( 'media_gallery', __( 'Gallery' ), array(
			'description' => __( 'Displays an image gallery.' ),
			'mime_type'   => 'image',
		) );

		$this->l10n = array_merge( $this->l10n, array(
			'no_media_selected' => __( 'No images selected' ),
			'add_media' => _x( 'Add Images', 'label for button in the gallery widget; should not be longer than ~13 characters long' ),
			'replace_media' => '',
			'edit_media' => _x( 'Edit Gallery', 'label for button in the gallery widget; should not be longer than ~13 characters long' ),
		) );
	}

	/**
	 * Get schema for properties of a widget instance (item).
	 *
	 * @since 4.9.0
	 *
	 * @see MCMS_REST_Controller::get_item_schema()
	 * @see MCMS_REST_Controller::get_additional_fields()
	 * @link https://core.trac.mandarincms.com/ticket/35574
	 * @return array Schema for properties.
	 */
	public function get_instance_schema() {
		$schema = array(
			'title' => array(
				'type' => 'string',
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
				'description' => __( 'Title for the widget' ),
				'should_preview_update' => false,
			),
			'ids' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'integer',
				),
				'default' => array(),
				'sanitize_callback' => 'mcms_parse_id_list',
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 3,
				'minimum' => 1,
				'maximum' => 9,
			),
			'size' => array(
				'type' => 'string',
				'enum' => array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ),
				'default' => 'thumbnail',
			),
			'link_type' => array(
				'type' => 'string',
				'enum' => array( 'post', 'file', 'none' ),
				'default' => 'post',
				'media_prop' => 'link',
				'should_preview_update' => false,
			),
			'orderby_random' => array(
				'type'                  => 'boolean',
				'default'               => false,
				'media_prop'            => '_orderbyRandom',
				'should_preview_update' => false,
			),
		);

		/** This filter is documented in mcms-roots/widgets/class-mcms-widget-media.php */
		$schema = apply_filters( "widget_{$this->id_base}_instance_schema", $schema, $this );

		return $schema;
	}

	/**
	 * Render the media on the frontend.
	 *
	 * @since 4.9.0
	 *
	 * @param array $instance Widget instance props.
	 * @return void
	 */
	public function render_media( $instance ) {
		$instance = array_merge( mcms_list_pluck( $this->get_instance_schema(), 'default' ), $instance );

		$shortcode_atts = array_merge(
			$instance,
			array(
				'link' => $instance['link_type'],
			)
		);

		// @codeCoverageIgnoreStart
		if ( $instance['orderby_random'] ) {
			$shortcode_atts['orderby'] = 'rand';
		}

		// @codeCoverageIgnoreEnd
		echo gallery_shortcode( $shortcode_atts );
	}

	/**
	 * Loads the required media files for the media manager and scripts for media widgets.
	 *
	 * @since 4.9.0
	 */
	public function enqueue_admin_scripts() {
		parent::enqueue_admin_scripts();

		$handle = 'media-gallery-widget';
		mcms_enqueue_script( $handle );

		$exported_schema = array();
		foreach ( $this->get_instance_schema() as $field => $field_schema ) {
			$exported_schema[ $field ] = mcms_array_slice_assoc( $field_schema, array( 'type', 'default', 'enum', 'minimum', 'format', 'media_prop', 'should_preview_update', 'items' ) );
		}
		mcms_add_inline_script(
			$handle,
			sprintf(
				'mcms.mediaWidgets.modelConstructors[ %s ].prototype.schema = %s;',
				mcms_json_encode( $this->id_base ),
				mcms_json_encode( $exported_schema )
			)
		);

		mcms_add_inline_script(
			$handle,
			sprintf(
				'
					mcms.mediaWidgets.controlConstructors[ %1$s ].prototype.mime_type = %2$s;
					_.extend( mcms.mediaWidgets.controlConstructors[ %1$s ].prototype.l10n, %3$s );
				',
				mcms_json_encode( $this->id_base ),
				mcms_json_encode( $this->widget_options['mime_type'] ),
				mcms_json_encode( $this->l10n )
			)
		);
	}

	/**
	 * Render form template scripts.
	 *
	 * @since 4.9.0
	 */
	public function render_control_template_scripts() {
		parent::render_control_template_scripts();
		?>
		<script type="text/html" id="tmpl-mcms-media-widget-gallery-preview">
			<# var describedById = 'describedBy-' + String( Math.random() ); #>
			<# if ( data.ids.length ) { #>
				<div class="gallery media-widget-gallery-preview">
					<# _.each( data.ids, function( id, index ) { #>
						<#
						var attachment = data.attachments[ id ];
						if ( ! attachment ) {
							return;
						}
						#>
						<# if ( index < 6 ) { #>
							<dl class="gallery-item">
								<dt class="gallery-icon">
								<# if ( attachment.sizes.thumbnail ) { #>
									<img src="{{ attachment.sizes.thumbnail.url }}" width="{{ attachment.sizes.thumbnail.width }}" height="{{ attachment.sizes.thumbnail.height }}" alt="" />
								<# } else { #>
									<img src="{{ attachment.url }}" alt="" />
								<# } #>
								<# if ( index === 5 && data.ids.length > 6 ) { #>
									<div class="gallery-icon-placeholder">
										<p class="gallery-icon-placeholder-text">+{{ data.ids.length - 5 }}</p>
									</div>
								<# } #>
								</dt>
							</dl>
						<# } #>
					<# } ); #>
				</div>
			<# } else { #>
				<div class="attachment-media-view">
					<p class="placeholder"><?php echo esc_html( $this->l10n['no_media_selected'] ); ?></p>
				</div>
			<# } #>
		</script>
		<?php
	}

	/**
	 * Whether the widget has content to show.
	 *
	 * @since 4.9.0
	 * @access protected
	 *
	 * @param array $instance Widget instance props.
	 * @return bool Whether widget has content.
	 */
	protected function has_content( $instance ) {
		if ( ! empty( $instance['ids'] ) ) {
			$attachments = mcms_parse_id_list( $instance['ids'] );
			foreach ( $attachments as $attachment ) {
				if ( 'attachment' !== get_post_type( $attachment ) ) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
}
