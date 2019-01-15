<?php
/**
 * Customize API: MCMS_Customize_Upload_Control class
 *
 * @package MandarinCMS
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Upload Control Class.
 *
 * @since 3.4.0
 *
 * @see MCMS_Customize_Media_Control
 */
class MCMS_Customize_Upload_Control extends MCMS_Customize_Media_Control {
	public $type          = 'upload';
	public $mime_type     = '';
	public $button_labels = array();
	public $removed = ''; // unused
	public $context; // unused
	public $extensions = array(); // unused

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 *
	 * @uses MCMS_Customize_Media_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$value = $this->value();
		if ( $value ) {
			// Get the attachment model for the existing file.
			$attachment_id = attachment_url_to_postid( $value );
			if ( $attachment_id ) {
				$this->json['attachment'] = mcms_prepare_attachment_for_js( $attachment_id );
			}
		}
	}
}
