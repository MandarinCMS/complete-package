<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Addons Page
 *
 * Renders the extensions page contents.
 *
 * @access      private
 * @since        1.0
 * @return      void
 */
function balooncreate_extensions_page() { ?>
	<div class="wrap"><h1><?php _e( 'Extend BaloonUp Maker', 'baloonup-maker' ) ?></h1>

	<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div id="post-body-content"><?php
			$extensions = balooncreate_available_extensions(); ?>
			<hr class="clear" />
			<h2 class="section-heading">
				<?php _e( 'Extensions', 'baloonup-maker' ) ?>
				&nbsp;&nbsp;<a href="https://mcmsbaloonupmaker.com/extensions/?utm_source=module-extension-page&utm_medium=text-link&utm_campaign=Upsell&utm_content=browse-all" class="button-primary" title="<?php _e( 'Browse All Extensions', 'baloonup-maker' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'baloonup-maker' ); ?></a>
			</h2>
			<p><?php _e( 'These extensions <strong>add extra functionality</strong> to your baloonups.', 'baloonup-maker' ); ?></p>
			<ul class="extensions-available">
				<?php
				$modules           = get_modules();
				$installed_modules = array();
				foreach ( $modules as $key => $module ) {
					$is_active                          = is_module_active( $key );
					$installed_module                   = array(
						'is_active' => $is_active,
					);
					$installerUrl                       = add_query_arg( array(
						'action' => 'activate',
						'module' => $key,
						'em'     => 1,
					), network_admin_url( 'modules.php' ) //admin_url('update.php')
					);
					$installed_module["activation_url"] = $is_active ? "" : mcms_nonce_url( $installerUrl, 'activate-module_' . $key );


					$installerUrl                         = add_query_arg( array(
						'action' => 'deactivate',
						'module' => $key,
						'em'     => 1,
					), network_admin_url( 'modules.php' ) //admin_url('update.php')
					);
					$installed_module["deactivation_url"] = ! $is_active ? "" : mcms_nonce_url( $installerUrl, 'deactivate-module_' . $key );
					$installed_modules[ $key ]            = $installed_module;
				}
				$existing_extension_images = apply_filters( 'balooncreate_existing_extension_images', array() );
				if ( ! empty( $extensions ) ) {

					shuffle( $extensions );

					foreach ( $extensions as $key => $ext ) {
						unset( $extensions[ $key ] );
						$extensions[ $ext['slug'] ] = $ext;
					}

					$extensions = array_merge( array( 'core-extensions-bundle' => $extensions['core-extensions-bundle'] ), $extensions );

					$i = 0;

					foreach ( $extensions as $extension ) : ?>
						<li class="available-extension-inner <?php esc_attr_e( $extension['slug'] ); ?>">
							<h3>
								<a target="_blank" href="<?php echo esc_url( $extension['homepage'] ); ?>?utm_source=module-extension-page&utm_medium=extension-title-<?php echo $i; ?>&utm_campaign=Upsell&utm_content=<?php esc_attr_e( urlencode( str_replace( ' ', '+', $extension['name'] ) ) ); ?>">
									<?php esc_html_e( $extension['name'] ) ?>
								</a>
							</h3>
							<?php $image = in_array( $extension['slug'], $existing_extension_images ) ? POPMAKE_URL . '/assets/images/extensions/' . $extension['slug'] . '.png' : $extension['image']; ?>
							<img class="extension-thumbnail" src="<?php esc_attr_e( $image ) ?>">

							<p><?php esc_html_e( $extension['excerpt'] ); ?></p>
							<?php
							/*
							if(!empty($extension->download_link) && !isset($installed_modules[$extension->slug.'/'.$extension->slug.'.php']))
							{
								$installerUrl = add_query_arg(
									array(
										'action' => 'install-module',
										'module' => $extension->slug,
										'edd_sample_module' => 1
									),
									network_admin_url('update.php')
									//admin_url('update.php')
								);
								$installerUrl = mcms_nonce_url($installerUrl, 'install-module_' . $extension->slug)?>
								<span class="action-links"><?php
								printf(
									'<a class="button install" href="%s">%s</a>',
									esc_attr($installerUrl),
									__('Install')
								);?>
								</span><?php
							}
							elseif(isset($installed_modules[$extension->slug.'/'.$extension->slug.'.php']['is_active']))
							{?>
								<span class="action-links"><?php
									if(!$installed_modules[$extension->slug.'/'.$extension->slug.'.php']['is_active'])
									{
										printf(
											'<a class="button install" href="%s">%s</a>',
											esc_attr($installed_modules[$extension->slug.'/'.$extension->slug.'.php']["activation_url"]),
											__('Activate')
										);

									}
									else
									{
										printf(
											'<a class="button install" href="%s">%s</a>',
											esc_attr($installed_modules[$extension->slug.'/'.$extension->slug.'.php']["deactivation_url"]),
											__('Deactivate')
										);
									}?>
								</span><?php
							}
							else
							{
								?><span class="action-links"><a class="button" target="_blank" href="<?php esc_attr_e($extension->homepage);?>"><?php _e('Get It Now');?></a></span><?php
							}
							*/
							?>

							<span class="action-links">
			                    <a class="button" target="_blank" href="<?php echo esc_url( $extension['homepage'] ); ?>?utm_source=module-extension-page&utm_medium=extension-button-<?php echo $i; ?>&utm_campaign=Upsell&utm_content=<?php esc_attr_e( urlencode( str_replace( ' ', '+', $extension['name'] ) ) ); ?>"><?php _e( 'Get this Extension', 'baloonup-maker' ); ?></a>
			                </span>
						</li>
						<?php
						$i ++;
					endforeach;
				} ?>
			</ul>

			<br class="clear" />

			<a href="https://mcmsbaloonupmaker.com/extensions/?utm_source=module-extension-page&utm_medium=text-link&utm_campaign=Upsell&utm_content=browse-all-bottom" class="button-primary" title="<?php _e( 'Browse All Extensions', 'baloonup-maker' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'baloonup-maker' ); ?></a>

			<br class="clear" />
			<br class="clear" />
			<br class="clear" />
			<hr class="clear" />
			<br class="clear" />

			<h2 class="section-heading">
				<?php _e( 'Other Compatible Modules', 'baloonup-maker' ); ?>
			</h2>
			<p><?php _e( 'These modules should work in baloonups with no extra setup.', 'baloonup-maker' ); ?></p>
			<ul class="extensions-available">
				<?php
				$compatible_modules = array(
					array(
						'slug' => 'gravity-forms',
						'name' => __( 'Gravity Forms', 'baloonup-maker' ),
						'url'  => 'https://mcmsbaloonupmaker.com/grab/gravity-forms',
						'desc' => __( 'Gravity Forms is one of the most popular form building modules.', 'baloonup-maker' ),
					),
					array(
						'slug' => 'contact-form-7',
						'name' => __( 'Contact Form 7', 'baloonup-maker' ),
						'url'  => 'https://mcmsbaloonupmaker.com/grab/contact-form-7',
						'desc' => __( 'CF7 is one of the most downloaded modules on the MandarinCMS repo. Make simple forms with ease and plenty of free addons available.', 'baloonup-maker' ),
					),
					array(
						'slug' => 'quiz-survey-master',
						'name' => __( 'Quiz & Survey Master', 'baloonup-maker' ),
						'url'  => 'https://mcmsbaloonupmaker.com/grab/quiz-survey-master',
						'desc' => __( 'If you need more from your forms data look no further, QSM is all about the statistics & collective data, something other form modules neglect.', 'baloonup-maker' ),
					),
				);

				shuffle( $compatible_modules );

				array_unshift( $compatible_modules, array(
					'slug' => 'ninja-forms',
					'name' => __( 'Ninja Forms', 'baloonup-maker' ),
					'url'  => 'https://mcmsbaloonupmaker.com/grab/ninja-forms',
					'desc' => __( 'Ninja Forms has fast become the most extensible form module available. Build super custom forms and integrate with your favorite services.', 'baloonup-maker' ),
				) );

				$i = 1;

				foreach ( $compatible_modules as $module ) : ?>
				<li class="available-extension-inner <?php esc_attr_e( $module['slug'] ); ?>">
					<h3>
						<a target="_blank" href="<?php esc_attr_e( $module['url'] ); ?>?utm_campaign=FormModules&utm_source=module-extend-page&utm_medium=form-banner&utm_content=<?php echo $module['slug']; ?>">
							<?php esc_html_e( $module['name'] ) ?>
						</a>
					</h3>
					<img class="extension-thumbnail" src="<?php esc_attr_e( POPMAKE_URL . '/assets/images/modules/' . $module['slug'] . '.png' ) ?>">

					<p><?php esc_html_e( $module['desc'] ); ?></p>
					<span class="action-links">
                                <a class="button" target="_blank" href="<?php echo esc_url( $module['url'] ); ?>"><?php _e( 'Check it out', 'baloonup-maker' ); ?></a>
                            </span>
					</li><?php
					$i ++;
				endforeach; ?>
			</ul>

		</div>

	</div>
	</div><?php
}
