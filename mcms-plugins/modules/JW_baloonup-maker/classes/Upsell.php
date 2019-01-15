<?php

class PUM_Upsell {


	public static function init() {
		add_filter( 'pum_baloonup_close_settings_fields', array( __CLASS__, 'fi_promotion' ) );
		add_filter( 'pum_baloonup_targeting_settings_fields', array( __CLASS__, 'atc_promotion' ) );
	}

	public static function fi_promotion( $fields ) {
		if ( pum_extension_enabled( 'forced-interaction' ) ) {
			return $fields;
		}

		ob_start();

		?>

		<div class="pum-upgrade-tip">
			<img src="<?php echo BaloonUp_Maker::$URL; ?>/assets/images/upsell-icon-forced-interaction.png" />
			<?php printf( _x( 'Want to disable the close button? Check out %sForced Interaction%s!', '%s represent the opening & closing link html', 'baloonup-maker' ), '<a href="https://mcmsbaloonupmaker.com/extensions/forced-interaction/?utm_source=module-myskin-editor&utm_medium=text-link&utm_campaign=Upsell&utm_content=close-button-settings" target="_blank">', '</a>' ); ?>
		</div>

		<?php

		$html = ob_get_clean();

		$key = key( $fields );

		$fields[ $key ]['fi_promotion'] = array(
			'type'     => 'html',
			'content'  => $html,
			'priority' => 30,
		);

		return $fields;
	}

	public static function atc_promotion( $fields ) {
		if ( pum_extension_enabled( 'advanced-targetng-conditions' ) ) {
			return $fields;
		}

		ob_start();

		?>

		<div class="pum-upgrade-tip">
			<img src="<?php echo BaloonUp_Maker::$URL; ?>/assets/images/logo.png" height="28" />
			<?php printf( __( 'Need more %sadvanced targeting%s options?', 'baloonup-maker' ), '<a href="https://mcmsbaloonupmaker.com/extensions/advanced-targeting-conditions/?utm_campaign=Upsell&utm_source=module-baloonup-editor&utm_medium=text-link&utm_content=conditions-editor" target="_blank">', '</a>' ); ?>
		</div>

		<?php

		$html = ob_get_clean();
		$key  = key( $fields );


		$fields[ $key ]['atc_promotion'] = array(
			'type'     => 'html',
			'content'  => $html,
			'priority' => 30,
		);

		return $fields;
	}

}