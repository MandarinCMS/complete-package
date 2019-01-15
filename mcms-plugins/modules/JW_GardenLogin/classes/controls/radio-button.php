<?php
/**
* Class for Radio Button Control.
*
* @since  1.0.23
* @access public
*/
class GardenLogin_Radio_Control extends MCMS_Customize_Control {

	/**
	* The type of customize control being rendered.
	*
	* @since  1.0.23
	* @access public
	* @var    string
	*/
	public $type = 'ios';

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since 1.0.23
   * @access public
   * @return void
	 */
	public function enqueue() {

		mcms_enqueue_script( 'gardenlogin-radio-control-js', LOGINPRESS_DIR_URL . 'js/controls/gardenlogin-radio-button-control.js', array( 'jquery' ), LOGINPRESS_VERSION, true );
		mcms_enqueue_style( 'gardenlogin-radio-control-css', LOGINPRESS_DIR_URL . 'css/controls/gardenlogin-radio-button-control.css', array(), LOGINPRESS_VERSION );

		$css = '
			.disabled-control-title {
				color: #a0a5aa;
			}
			input[type=checkbox].gardenlogin-radio-light:checked + .gardenlogin-radio-btn {
				background: #0085ba;
			}
			input[type=checkbox].gardenlogin-radio-light + .gardenlogin-radio-btn {
			  background: #a0a5aa;
			}
			input[type=checkbox].gardenlogin-radio-light + .gardenlogin-radio-btn:after {
			  background: #f7f7f7;
			}

			input[type=checkbox].gardenlogin-radio-ios:checked + .gardenlogin-radio-btn {
			  background: #0085ba;
			}

			input[type=checkbox].gardenlogin-radio-flat:checked + .gardenlogin-radio-btn {
			  border: 4px solid #0085ba;
			}
			input[type=checkbox].gardenlogin-radio-flat:checked + .gardenlogin-radio-btn:after {
			  background: #0085ba;
			}
			';
		mcms_add_inline_style( 'gardenlogin-radio-control-css' , $css );
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
			<div style="display:flex;flex-direction: row;justify-content: flex-start;">
				<span class="customize-control-title" style="flex: 2 0 0; vertical-align: middle;"><?php echo esc_html( $this->label ); ?></span>
				<input id="cb<?php echo $this->instance_number ?>" type="checkbox" class="gardenlogin-radio gardenlogin-radio-<?php echo $this->type?>" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?> />
				<label for="cb<?php echo $this->instance_number ?>" class="gardenlogin-radio-btn"></label>
			</div>
			<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php
	}
}
