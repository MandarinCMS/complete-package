<?php
/**
* Class for Radio Button Control.
*
* @since  1.0.23
* @access public
*/
class GardenLogin_Misc_Control extends MCMS_Customize_Control {

	/**
	* The type of customize control being rendered.
	*
	* @since  1.0.23
	* @access public
	* @var    string
	*/
	public $type = '';

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since 1.0.23
   * @access public
   * @return void
	 */
	public function enqueue() {

		// mcms_enqueue_script( 'gardenlogin-miscellaneous-control-js', LOGINPRESS_DIR_URL . 'js/controls/gardenlogin-miscellaneous-control.js', array( 'jquery' ), LOGINPRESS_VERSION, true );
		// mcms_enqueue_style( 'gardenlogin-miscellaneous-control-css', LOGINPRESS_DIR_URL . 'css/controls/gardenlogin-miscellaneous-control.css', array(), LOGINPRESS_VERSION );

	}

	/**
  * Displays the control content.
  *
  * @since  1.0.23
  * @access public
  * @return void
  */
	public function render_content() {

		switch ( $this->type ) {
            default:

            case 'hr' :
                echo '<hr />';
                break;
        }
	}
}
