<?php

/**
* Class for Group.
*
* @since  1.1.3
* @access public
*/
class GardenLogin_Group_Control extends MCMS_Customize_Control {

  /**
  * The type of customize control being rendered.
  *
  * @since  1.1.3
  * @access public
  * @var    string
  */
  public $type = 'group';

  /**
  * Information text for the Group.
  *
  * @since  1.1.3
  * @access public
  * @var    string
  */
  public $info_text;

  /**
  * Enqueue scripts/styles.
  *
  * @since  1.0.17
  * @access public
  * @return void
  */
  public function enqueue() {
		mcms_enqueue_style( 'gardenlogin-group-control-css', LOGINPRESS_DIR_URL . 'css/controls/gardenlogin-group-control.css', array(), LOGINPRESS_VERSION );
  }

  /**
  * Displays the control content.
  *
  * @since  1.0.17
  * @access public
  * @return void
  */
  public function render_content() {
    ?>

    <div id="input_<?php echo $this->id; ?>" class="gardenlogin-group-wrapper">
      <h3 class="gardenlogin-group-heading"><?php echo esc_attr( $this->label ); ?></h3>
      <div class="gardenlogin-group-info">
        <p><span class="gardenlogin-group-badge badges"><?php esc_html_e( 'Info:', 'gardenlogin' ) ?></span><?php echo esc_html( $this->info_text ); ?></p>
      </div>
    </div>


  <?php }


}
?>
