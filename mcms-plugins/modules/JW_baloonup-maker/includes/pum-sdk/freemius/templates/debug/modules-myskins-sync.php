<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.1.7.3
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	$fs_options  = FS_Options::instance( MCMS_FS__ACCOUNTS_OPTION_NAME, true );
	$all_modules = $fs_options->get_option( 'all_modules' );
	$all_myskins  = $fs_options->get_option( 'all_myskins' );

    /* translators: %s: time period (e.g. In "2 hours") */
	$in_x_text = fs_text_inline( 'In %s', 'in-x' );
    /* translators: %s: time period (e.g. "2 hours" ago) */
	$x_ago_text = fs_text_inline( '%s ago', 'x-ago' );
	$sec_text   = fs_text_x_inline( 'sec', 'seconds' );
?>
<h1><?php fs_esc_html_echo_inline( 'Modules & mySkins Sync', 'modules-myskins-sync' ) ?></h1>
<table class="widefat">
	<thead>
	<tr>
		<th></th>
		<th><?php fs_esc_html_echo_inline( 'Total', 'total' ) ?></th>
		<th><?php fs_esc_html_echo_inline( 'Last', 'last' ) ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if ( is_object( $all_modules ) ) : ?>
		<tr>
			<td><?php fs_esc_html_echo_inline( 'Modules', 'modules' ) ?></td>
			<td><?php echo count( $all_modules->modules ) ?></td>
			<td><?php
					if ( isset( $all_modules->timestamp ) && is_numeric( $all_modules->timestamp ) ) {
						$diff       = abs( MCMS_FS__SCRIPT_START_TIME - $all_modules->timestamp );
						$human_diff = ( $diff < MINUTE_IN_SECONDS ) ?
							$diff . ' ' . $sec_text :
							human_time_diff( MCMS_FS__SCRIPT_START_TIME, $all_modules->timestamp );

                        echo esc_html( sprintf(
                            ( ( MCMS_FS__SCRIPT_START_TIME < $all_modules->timestamp ) ?
                                $in_x_text :
                                $x_ago_text ),
                            $human_diff
                        ) );
					}
				?></td>
		</tr>
	<?php endif ?>
	<?php if ( is_object( $all_myskins ) ) : ?>
		<tr>
			<td><?php fs_esc_html_echo_inline( 'mySkins', 'myskins' ) ?></td>
			<td><?php echo count( $all_myskins->myskins ) ?></td>
			<td><?php
					if ( isset( $all_myskins->timestamp ) && is_numeric( $all_myskins->timestamp ) ) {
						$diff       = abs( MCMS_FS__SCRIPT_START_TIME - $all_myskins->timestamp );
						$human_diff = ( $diff < MINUTE_IN_SECONDS ) ?
							$diff . ' ' . $sec_text :
							human_time_diff( MCMS_FS__SCRIPT_START_TIME, $all_myskins->timestamp );

                        echo esc_html( sprintf(
                            ( ( MCMS_FS__SCRIPT_START_TIME < $all_myskins->timestamp ) ?
                                $in_x_text :
                                $x_ago_text ),
                            $human_diff
                        ) );
					}
				?></td>
		</tr>
	<?php endif ?>
	</tbody>
</table>
