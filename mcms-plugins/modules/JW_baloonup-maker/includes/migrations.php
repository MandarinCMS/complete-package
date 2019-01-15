<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

/**
 * Checks if passive migration for baloonups should be enabled.
 *
 * This determines if the query load may be potentially too high to run passive migrations on live servers.
 *
 * @return bool
 */
function pum_passive_baloonups_enabled() {
	/** @var int $baloonup_count */
	static $baloonup_count;

	if ( defined( 'PUM_DISABLE_PASSIVE_UPGRADES' ) && PUM_DISABLE_PASSIVE_UPGRADES ) {
		return false;
	}

	if ( ! $baloonup_count ) {
		$baloonup_count = get_transient( 'pum_baloonup_count' );

		if ( $baloonup_count === false ) {

			$baloonups = get_posts( array(
				'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
				'post_type'      => 'baloonup',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			) );

			$baloonup_count = ! empty( $baloonups ) ? count( $baloonups ) : 0;

			set_transient( 'pum_baloonup_count', $baloonup_count, HOUR_IN_SECONDS * 24 );

		}
	}

	return $baloonup_count > apply_filters( 'pum_passive_baloonups_enabled_max_count', 5 );
}

/**
 * Upgrade baloonup data to model v3.
 *
 * @since 1.7.0
 *
 * @param $baloonup PUM_Model_BaloonUp
 */
function pum_baloonup_migration_2( &$baloonup ) {

	$changed     = false;
	$delete_meta = array();

	/**
	 * Update pum_sub_form shortcode args
	 */
	if ( has_shortcode( $baloonup->post_content, 'pum_sub_form' ) ) {
		$new_content = preg_replace( '/\[pum_sub_form(.*)provider="none"(.*)\]/', '[pum_sub_form$1 provider=""$2]', $baloonup->post_content );

		if ( $baloonup->post_content != $new_content ) {
			$baloonup->post_content = $new_content;
			$changed             = true;
			$baloonup->save( false );
		}
	}

	/**
	 * Migrate baloonup myskin selection.
	 */
	$myskin = $baloonup->get_meta( 'baloonup_myskin' );
	if ( ! empty( $myskin ) && is_numeric( $myskin ) ) {
		$baloonup->settings['myskin_id'] = absint( $myskin );
		$changed                     = true;
		$delete_meta[]               = 'baloonup_myskin';
	}

	/**
	 * Migrate baloonup_display meta data.
	 */
	$display = $baloonup->get_meta( 'baloonup_display' );
	if ( ! empty( $display ) && is_array( $display ) ) {
		$keys = $baloonup->remapped_meta_settings_keys( 'display' );

		// Foreach old key, save the value under baloonup settings for the new key.
		foreach ( $keys as $old_key => $new_key ) {
			if ( isset( $display[ $old_key ] ) && ! empty( $display[ $old_key ] ) ) {
				$baloonup->settings[ $new_key ] = $display[ $old_key ];
				$changed                     = true;
				unset( $display[ $old_key ] );

				if ( in_array( $old_key, array(
						'responsive_min_width',
						'responsive_max_width',
						'custom_width',
						'custom_height',
					) ) && isset( $display[ $old_key . '_unit' ] ) ) {
					$baloonup->settings[ $new_key ] .= $display[ $old_key . '_unit' ];
					unset( $display[ $old_key . '_unit' ] );
				}
			}
		}

		if ( empty( $display ) ) {
			$delete_meta[] = 'baloonup_display';
		} else {
			// Update the saved baloonup display data with any remaining keys from extensions.
			$baloonup->update_meta( 'baloonup_display', $display );
		}
	}

	/**
	 * Migrate baloonup_close meta data
	 */
	$close = $baloonup->get_meta( 'baloonup_close' );
	if ( ! empty( $close ) && is_array( $close ) ) {
		$keys = $baloonup->remapped_meta_settings_keys( 'close' );

		// Foreach old key, save the value under baloonup settings for the new key.
		foreach ( $keys as $old_key => $new_key ) {
			if ( isset( $close[ $old_key ] ) ) {
				$baloonup->settings[ $new_key ] = $close[ $old_key ];
				$changed                     = true;
				unset( $close[ $old_key ] );
			}
		}

		if ( empty( $close ) ) {
			$delete_meta[] = 'baloonup_close';
		} else {
			// Update the saved baloonup close data with any remaining keys from extensions.
			$baloonup->update_meta( 'baloonup_close', $close );
		}
	}

	/**
	 * Migrate triggers.
	 */
	$triggers = $baloonup->get_meta( 'baloonup_triggers' );
	if ( ! empty( $triggers ) && is_array( $triggers ) ) {
		$triggers = ! empty( $baloonup->settings['triggers'] ) && is_array( $baloonup->settings['triggers'] ) ? array_merge( $baloonup->settings['triggers'], $triggers ) : $triggers;

		foreach ( $triggers as $key => $trigger ) {
			if ( isset( $trigger['settings']['cookie']['name'] ) ) {
				$triggers[ $key ]['settings']['cookie_name'] = $trigger['settings']['cookie']['name'];
				unset( $triggers[ $key ]['settings']['cookie'] );
			}
		}

		$baloonup->settings['triggers'] = $triggers;
		$changed                     = true;

		$delete_meta[] = 'baloonup_triggers';
	}

	/**
	 * Migrate cookies.
	 */
	$cookies = $baloonup->get_meta( 'baloonup_cookies' );
	if ( ! empty( $cookies ) && is_array( $cookies ) ) {
		$cookies                    = ! empty( $baloonup->settings['cookies'] ) && is_array( $baloonup->settings['cookies'] ) ? array_merge( $baloonup->settings['cookies'], $cookies ) : $cookies;
		$baloonup->settings['cookies'] = $cookies;
		$changed                    = true;
		$delete_meta[]              = 'baloonup_cookies';
	}

	/**
	 * Migrate conditions.
	 */
	$conditions = $baloonup->get_meta( 'baloonup_conditions' );
	if ( ! empty( $conditions ) && is_array( $conditions ) ) {
		$conditions = ! empty( $baloonup->settings['conditions'] ) && is_array( $baloonup->settings['conditions'] ) ? array_merge( $baloonup->settings['conditions'], $conditions ) : $conditions;

		foreach ( $conditions as $cg_key => $group ) {
			if ( ! empty( $group ) ) {
				foreach ( $group as $c_key => $condition ) {

					// Clean empty conditions.
					if ( ! empty( $condition['target'] ) ) {
						$fixed_condition = array(
							'target'      => $condition['target'],
							'not_operand' => isset( $condition['not_operand'] ) ? (bool) $condition['not_operand'] : false,
							'settings'    => isset( $condition['settings'] ) ? $condition['settings'] : array(),
						);

						foreach ( $condition as $key => $val ) {
							if ( ! in_array( $key, array( 'target', 'not_operand', 'settings' ) ) ) {
								$fixed_condition['settings'][ $key ] = $val;
							}
						}

						$conditions[ $cg_key ][ $c_key ] = $fixed_condition;
					} else {
						unset( $conditions[ $cg_key ][ $c_key ] );
					}
				}

				// Clean empty groups.
				if ( empty( $conditions[ $cg_key ] ) ) {
					unset( $conditions[ $cg_key ] );
				}
			}
		}

		$baloonup->settings['conditions'] = $conditions;
		$changed                       = true;
		$delete_meta[]                 = 'baloonup_conditions';
	}

	/**
	 * Migrate baloonup_mobile_disabled.
	 */
	$mobile_disabled = $baloonup->get_meta( 'baloonup_mobile_disabled' );
	if ( ! empty( $mobile_disabled ) ) {
		$baloonup->settings['disable_on_mobile'] = (bool) ( $mobile_disabled );
		$changed                              = true;
		$delete_meta[]                        = 'baloonup_mobile_disabled';
	}

	/**
	 * Migrate baloonup_tablet_disabled.
	 */
	$tablet_disabled = $baloonup->get_meta( 'baloonup_tablet_disabled' );
	if ( ! empty( $tablet_disabled ) ) {
		$baloonup->settings['disable_on_tablet'] = (bool) ( $tablet_disabled );
		$changed                              = true;
		$delete_meta[]                        = 'baloonup_tablet_disabled';
	}

	/**
	 * Migrate analytics reset keys.
	 */
	$open_count_reset = $baloonup->get_meta( 'baloonup_open_count_reset', false );
	if ( ! empty( $open_count_reset ) && is_array( $open_count_reset ) ) {
		foreach ( $open_count_reset as $key => $reset ) {
			if ( is_array( $reset ) ) {
				add_post_meta( $baloonup->ID, 'baloonup_count_reset', array(
					'timestamp'   => ! empty( $reset['timestamp'] ) ? $reset['timestamp'] : '',
					'opens'       => ! empty( $reset['count'] ) ? absint( $reset['count'] ) : 0,
					'conversions' => 0,
				) );
			}
		}

		$delete_meta[] = 'baloonup_open_count_reset';
	}

	/**
	 * Save only if something changed.
	 */
	if ( $changed ) {
		$baloonup->update_meta( 'baloonup_settings', $baloonup->settings );
	}

	/**
	 * Clean up automatically.
	 */
	if ( ! empty( $delete_meta ) ) {
		foreach ( $delete_meta as $key ) {
			$baloonup->delete_meta( $key );
		}
	}
}

add_action( 'pum_baloonup_passive_migration_2', 'pum_baloonup_migration_2' );
