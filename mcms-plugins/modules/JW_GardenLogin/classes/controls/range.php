<?php
/**
* Class for Range Control.
*
* @since  1.0.23
* @access public
*/
class GardenLogin_Range_Control extends MCMS_Customize_Control {

	/**
  * The type of customize control being rendered.
  *
  * @since  1.0.23
  * @access public
  * @var    string
  */
	public $type = 'gardenlogin-range';

	/**
  * Default for the Controler
  *
  * @since  1.0.23
  * @access public
  * @var    string
  */
  public $default;

	/**
  * Unit for the Controler
  *
  * @since  1.0.23
  * @access public
  * @var    string
  */
  public $unit = 'px';

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since 1.0.23
   * @access public
   * @return void
	 */
	public function enqueue() {

		mcms_enqueue_script( 'gardenlogin-range-control-js', LOGINPRESS_DIR_URL . 'js/controls/gardenlogin-range-control.js', array( 'jquery' ), LOGINPRESS_VERSION, true );

		mcms_enqueue_style( 'gardenlogin-range-control-css', LOGINPRESS_DIR_URL . 'css/controls/gardenlogin-range-control.css', array(), LOGINPRESS_VERSION );
	}

	/**
  * Displays the control content.
  *
  * @since  1.0.23
  * @access public
  * @return void
  */
	public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<div class="gardenlogin-range-slider"  style="width:100%; display:flex;flex-direction: row;justify-content: flex-start;">
				<span  style="width:100%; flex: 1 0 0; vertical-align: middle;">
					<span class="gardenlogin-range-slider_reset"><a type="button" value="reset" class="gardenlogin-range-reset"></a></span>
					<input class="gardenlogin-range-slider_range" data-default-value="<?php echo esc_html( $this->default ); ?>" type="range" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->input_attrs(); $this->link(); ?>>
					<input type="text" class="gardenlogin-range-slider_val" value="<?php echo esc_attr( $this->value() ); ?>" />
					<span><?php echo $this->unit; ?></span>
				</span>
			</div>
			<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php
	}
}
