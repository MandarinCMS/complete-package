<?php
/**
 * MandarinCMS Administration Update API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Selects the first update version from the update_core option.
 *
 * @return object|array|false The response from the API on success, false on failure.
 */
function get_preferred_from_update_core() {
	$updates = get_core_updates();
	if ( ! is_array( $updates ) )
		return false;
	if ( empty( $updates ) )
		return (object) array( 'response' => 'latest' );
	return $updates[0];
}

/**
 * Get available core updates.
 *
 * @param array $options Set $options['dismissed'] to true to show dismissed upgrades too,
 * 	                     set $options['available'] to false to skip not-dismissed updates.
 * @return array|false Array of the update objects on success, false on failure.
 */
function get_core_updates( $options = array() ) {
	$options = array_merge( array( 'available' => true, 'dismissed' => false ), $options );
	$dismissed = get_site_option( 'dismissed_update_core' );

	if ( ! is_array( $dismissed ) )
		$dismissed = array();

	$from_api = get_site_transient( 'update_core' );

	if ( ! isset( $from_api->updates ) || ! is_array( $from_api->updates ) )
		return false;

	$updates = $from_api->updates;
	$result = array();
	foreach ( $updates as $update ) {
		if ( $update->response == 'autoupdate' )
			continue;

		if ( array_key_exists( $update->current . '|' . $update->locale, $dismissed ) ) {
			if ( $options['dismissed'] ) {
				$update->dismissed = true;
				$result[] = $update;
			}
		} else {
			if ( $options['available'] ) {
				$update->dismissed = false;
				$result[] = $update;
			}
		}
	}
	return $result;
}

/**
 * Gets the best available (and enabled) Auto-Update for MandarinCMS Core.
 *
 * If there's 1.2.3 and 1.3 on offer, it'll choose 1.3 if the installation allows it, else, 1.2.3
 *
 * @since 3.7.0
 *
 * @return array|false False on failure, otherwise the core update offering.
 */
function find_core_auto_update() {
	$updates = get_site_transient( 'update_core' );
	if ( ! $updates || empty( $updates->updates ) )
		return false;

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

	$auto_update = false;
	$upgrader = new MCMS_Automatic_Updater;
	foreach ( $updates->updates as $update ) {
		if ( 'autoupdate' != $update->response )
			continue;

		if ( ! $upgrader->should_update( 'core', $update, BASED_TREE_URI ) )
			continue;

		if ( ! $auto_update || version_compare( $update->current, $auto_update->current, '>' ) )
			$auto_update = $update;
	}
	return $auto_update;
}

/**
 * Gets and caches the checksums for the given version of MandarinCMS.
 *
 * @since 3.7.0
 *
 * @param string $version Version string to query.
 * @param string $locale  Locale to query.
 * @return bool|array False on failure. An array of checksums on success.
 */
function get_core_checksums( $version, $locale ) {
	$url = $http_url = 'http://api.mandarincms.com/core/checksums/1.0/?' . http_build_query( compact( 'version', 'locale' ), null, '&' );

	if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$options = array(
		'timeout' => mcms_doing_cron() ? 30 : 3,
	);

	$response = mcms_remote_get( $url, $options );
	if ( $ssl && is_mcms_error( $response ) ) {
		trigger_error(
			sprintf(
				/* translators: %s: support forums URL */
				__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
				__( 'https://mandarincms.com/support/' )
			) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
			headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
		);
		$response = mcms_remote_get( $http_url, $options );
	}

	if ( is_mcms_error( $response ) || 200 != mcms_remote_retrieve_response_code( $response ) )
		return false;

	$body = trim( mcms_remote_retrieve_body( $response ) );
	$body = json_decode( $body, true );

	if ( ! is_array( $body ) || ! isset( $body['checksums'] ) || ! is_array( $body['checksums'] ) )
		return false;

	return $body['checksums'];
}

/**
 *
 * @param object $update
 * @return bool
 */
function dismiss_core_update( $update ) {
	$dismissed = get_site_option( 'dismissed_update_core' );
	$dismissed[ $update->current . '|' . $update->locale ] = true;
	return update_site_option( 'dismissed_update_core', $dismissed );
}

/**
 *
 * @param string $version
 * @param string $locale
 * @return bool
 */
function undismiss_core_update( $version, $locale ) {
	$dismissed = get_site_option( 'dismissed_update_core' );
	$key = $version . '|' . $locale;

	if ( ! isset( $dismissed[$key] ) )
		return false;

	unset( $dismissed[$key] );
	return update_site_option( 'dismissed_update_core', $dismissed );
}

/**
 *
 * @param string $version
 * @param string $locale
 * @return object|false
 */
function find_core_update( $version, $locale ) {
	$from_api = get_site_transient( 'update_core' );

	if ( ! isset( $from_api->updates ) || ! is_array( $from_api->updates ) )
		return false;

	$updates = $from_api->updates;
	foreach ( $updates as $update ) {
		if ( $update->current == $version && $update->locale == $locale )
			return $update;
	}
	return false;
}

/**
 *
 * @param string $msg
 * @return string
 */
function core_update_footer( $msg = '' ) {
	if ( !current_user_can('update_core') )
		return sprintf( __( 'Version %s' ), get_bloginfo( 'version', 'display' ) );

	$cur = get_preferred_from_update_core();
	if ( ! is_object( $cur ) )
		$cur = new stdClass;

	if ( ! isset( $cur->current ) )
		$cur->current = '';

	if ( ! isset( $cur->url ) )
		$cur->url = '';

	if ( ! isset( $cur->response ) )
		$cur->response = '';

	switch ( $cur->response ) {
	case 'development' :
		/* translators: 1: MandarinCMS version number, 2: MandarinCMS updates admin screen URL */
		return sprintf( __( 'You are using a development version (%1$s). Cool! Please <a href="%2$s">stay updated</a>.' ), get_bloginfo( 'version', 'display' ), network_admin_url( 'update-core.php' ) );

	case 'upgrade' :
		return '<strong><a href="' . network_admin_url( 'update-core.php' ) . '">' . sprintf( __( 'Get Version %s' ), $cur->current ) . '</a></strong>';

	case 'latest' :
	default :
		return sprintf( __( 'Version %s' ), get_bloginfo( 'version', 'display' ) );
	}
}

/**
 *
 * @global string $pagenow
 * @return false|void
 */
function update_nag() {
	if ( is_multisite() && !current_user_can('update_core') )
		return false;

	global $pagenow;

	if ( 'update-core.php' == $pagenow )
		return;

	$cur = get_preferred_from_update_core();

	if ( ! isset( $cur->response ) || $cur->response != 'upgrade' )
		return false;

	if ( current_user_can( 'update_core' ) ) {
		$msg = sprintf(
			/* translators: 1: Codex URL to release notes, 2: new MandarinCMS version, 3: URL to network admin, 4: accessibility text */
			__( '<a href="%1$s">MandarinCMS %2$s</a> is available! <a href="%3$s" aria-label="%4$s">Please update now</a>.' ),
			sprintf(
				/* translators: %s: MandarinCMS version */
				esc_url( __( 'https://dev.mandarincms.com/Version_%s' ) ),
				$cur->current
			),
			$cur->current,
			network_admin_url( 'update-core.php' ),
			esc_attr__( 'Please update MandarinCMS now' )
		);
	} else {
		$msg = sprintf(
			/* translators: 1: Codex URL to release notes, 2: new MandarinCMS version */
			__( '<a href="%1$s">MandarinCMS %2$s</a> is available! Please notify the site administrator.' ),
			sprintf(
				/* translators: %s: MandarinCMS version */
				esc_url( __( 'https://dev.mandarincms.com/Version_%s' ) ),
				$cur->current
			),
			$cur->current
		);
	}
	echo "<div class='update-nag'>$msg</div>";
}

// Called directly from dashboard
function update_right_now_message() {
	$myskin_name = mcms_get_myskin();
	if ( current_user_can( 'switch_myskins' ) ) {
		$myskin_name = sprintf( '<a href="myskins.php">%1$s</a>', $myskin_name );
	}

	$msg = '';

	if ( current_user_can('update_core') ) {
		$cur = get_preferred_from_update_core();

		if ( isset( $cur->response ) && $cur->response == 'upgrade' )
			$msg .= '<a href="' . network_admin_url( 'update-core.php' ) . '" class="button" aria-describedby="mcms-version">' . sprintf( __( 'Update to %s' ), $cur->current ? $cur->current : __( 'Latest' ) ) . '</a> ';
	}

	/* translators: 1: version number, 2: myskin name */
	#$content = __( 'MandarinCMS %1$s running %2$s myskin.' );

	/**
	 * Filters the text displayed in the 'At a Glance' dashboard widget.
	 *
	 * Prior to 3.8.0, the widget was named 'Right Now'.
	 *
	 * @since 4.4.0
	 *
	 * @param string $content Default text.
	 */
	$content = apply_filters( 'update_right_now_text', $content );

	$msg .= sprintf( '<span id="mcms-version">' . $content . '</span>', get_bloginfo( 'version', 'display' ), $myskin_name );

	echo "<p id='mcms-version-message'>$msg</p>";
}

/**
 * @since 2.9.0
 *
 * @return array
 */
function get_module_updates() {
	$all_modules = get_modules();
	$upgrade_modules = array();
	$current = get_site_transient( 'update_modules' );
	foreach ( (array)$all_modules as $module_file => $module_data) {
		if ( isset( $current->response[ $module_file ] ) ) {
			$upgrade_modules[ $module_file ] = (object) $module_data;
			$upgrade_modules[ $module_file ]->update = $current->response[ $module_file ];
		}
	}

	return $upgrade_modules;
}

/**
 * @since 2.9.0
 */
function mcms_module_update_rows() {
	if ( !current_user_can('update_modules' ) )
		return;

	$modules = get_site_transient( 'update_modules' );
	if ( isset($modules->response) && is_array($modules->response) ) {
		$modules = array_keys( $modules->response );
		foreach ( $modules as $module_file ) {
			add_action( "after_module_row_$module_file", 'mcms_module_update_row', 10, 2 );
		}
	}
}

/**
 * Displays update information for a module.
 *
 * @param string $file        Module basename.
 * @param array  $module_data Module information.
 * @return false|void
 */
function mcms_module_update_row( $file, $module_data ) {
	$current = get_site_transient( 'update_modules' );
	if ( ! isset( $current->response[ $file ] ) ) {
		return false;
	}

	$response = $current->response[ $file ];

	$modules_allowedtags = array(
		'a'       => array( 'href' => array(), 'title' => array() ),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'em'      => array(),
		'strong'  => array(),
	);

	$module_name   = mcms_kses( $module_data['Name'], $modules_allowedtags );
	$details_url   = self_admin_url( 'module-install.php?tab=module-information&module=' . $response->slug . '&section=changelog&TB_iframe=true&width=600&height=800' );

	/** @var MCMS_Modules_List_Table $mcms_list_table */
	$mcms_list_table = _get_list_table( 'MCMS_Modules_List_Table' );

	if ( is_network_admin() || ! is_multisite() ) {
		if ( is_network_admin() ) {
			$active_class = is_module_active_for_network( $file ) ? ' active' : '';
		} else {
			$active_class = is_module_active( $file ) ? ' active' : '';
		}

		echo '<tr class="module-update-tr' . $active_class . '" id="' . esc_attr( $response->slug . '-update' ) . '" data-slug="' . esc_attr( $response->slug ) . '" data-module="' . esc_attr( $file ) . '"><td colspan="' . esc_attr( $mcms_list_table->get_column_count() ) . '" class="module-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';

		if ( ! current_user_can( 'update_modules' ) ) {
			/* translators: 1: module name, 2: details URL, 3: additional link attributes, 4: version number */
			printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>.' ),
				$module_name,
				esc_url( $details_url ),
				sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
					/* translators: 1: module name, 2: version number */
					esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $module_name, $response->new_version ) )
				),
				$response->new_version
			);
		} elseif ( empty( $response->package ) ) {
			/* translators: 1: module name, 2: details URL, 3: additional link attributes, 4: version number */
			printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this module.</em>' ),
				$module_name,
				esc_url( $details_url ),
				sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
					/* translators: 1: module name, 2: version number */
					esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $module_name, $response->new_version ) )
				),
				$response->new_version
			);
		} else {
			/* translators: 1: module name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
			printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
				$module_name,
				esc_url( $details_url ),
				sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
					/* translators: 1: module name, 2: version number */
					esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $module_name, $response->new_version ) )
				),
				$response->new_version,
				mcms_nonce_url( self_admin_url( 'update.php?action=upgrade-module&module=' ) . $file, 'upgrade-module_' . $file ),
				sprintf( 'class="update-link" aria-label="%s"',
					/* translators: %s: module name */
					esc_attr( sprintf( __( 'Update %s now' ), $module_name ) )
				)
			);
		}

		/**
		 * Fires at the end of the update message container in each
		 * row of the modules list table.
		 *
		 * The dynamic portion of the hook name, `$file`, refers to the path
		 * of the module's primary file relative to the modules directory.
		 *
		 * @since 2.8.0
		 *
		 * @param array $module_data {
		 *     An array of module metadata.
		 *
		 *     @type string $name        The human-readable name of the module.
		 *     @type string $module_uri  Module URI.
		 *     @type string $version     Module version.
		 *     @type string $description Module description.
		 *     @type string $author      Module author.
		 *     @type string $author_uri  Module author URI.
		 *     @type string $text_domain Module text domain.
		 *     @type string $domain_path Relative path to the module's .mo file(s).
		 *     @type bool   $network     Whether the module can only be activated network wide.
		 *     @type string $title       The human-readable title of the module.
		 *     @type string $author_name Module author's name.
		 *     @type bool   $update      Whether there's an available update. Default null.
		 * }
		 * @param array $response {
		 *     An array of metadata about the available module update.
		 *
		 *     @type int    $id          Module ID.
		 *     @type string $slug        Module slug.
		 *     @type string $new_version New module version.
		 *     @type string $url         Module URL.
		 *     @type string $package     Module update package URL.
		 * }
		 */
		do_action( "in_module_update_message-{$file}", $module_data, $response );

		echo '</p></div></td></tr>';
	}
}

/**
 *
 * @return array
 */
function get_myskin_updates() {
	$current = get_site_transient('update_myskins');

	if ( ! isset( $current->response ) )
		return array();

	$update_myskins = array();
	foreach ( $current->response as $stylesheet => $data ) {
		$update_myskins[ $stylesheet ] = mcms_get_myskin( $stylesheet );
		$update_myskins[ $stylesheet ]->update = $data;
	}

	return $update_myskins;
}

/**
 * @since 3.1.0
 */
function mcms_myskin_update_rows() {
	if ( !current_user_can('update_myskins' ) )
		return;

	$myskins = get_site_transient( 'update_myskins' );
	if ( isset($myskins->response) && is_array($myskins->response) ) {
		$myskins = array_keys( $myskins->response );

		foreach ( $myskins as $myskin ) {
			add_action( "after_myskin_row_$myskin", 'mcms_myskin_update_row', 10, 2 );
		}
	}
}

/**
 * Displays update information for a myskin.
 *
 * @param string   $myskin_key MySkin stylesheet.
 * @param MCMS_MySkin $myskin     MySkin object.
 * @return false|void
 */
function mcms_myskin_update_row( $myskin_key, $myskin ) {
	$current = get_site_transient( 'update_myskins' );

	if ( ! isset( $current->response[ $myskin_key ] ) ) {
		return false;
	}

	$response = $current->response[ $myskin_key ];

	$details_url = add_query_arg( array(
		'TB_iframe' => 'true',
		'width'     => 1024,
		'height'    => 800,
	), $current->response[ $myskin_key ]['url'] );

	/** @var MCMS_MS_MySkins_List_Table $mcms_list_table */
	$mcms_list_table = _get_list_table( 'MCMS_MS_MySkins_List_Table' );

	$active = $myskin->is_allowed( 'network' ) ? ' active' : '';

	echo '<tr class="module-update-tr' . $active . '" id="' . esc_attr( $myskin->get_stylesheet() . '-update' ) . '" data-slug="' . esc_attr( $myskin->get_stylesheet() ) . '"><td colspan="' . $mcms_list_table->get_column_count() . '" class="module-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';
	if ( ! current_user_can( 'update_myskins' ) ) {
		/* translators: 1: myskin name, 2: details URL, 3: additional link attributes, 4: version number */
		printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>.'),
			$myskin['Name'],
			esc_url( $details_url ),
			sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
				/* translators: 1: myskin name, 2: version number */
				esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin['Name'], $response['new_version'] ) )
			),
			$response['new_version']
		);
	} elseif ( empty( $response['package'] ) ) {
		/* translators: 1: myskin name, 2: details URL, 3: additional link attributes, 4: version number */
		printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this myskin.</em>' ),
			$myskin['Name'],
			esc_url( $details_url ),
			sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
				/* translators: 1: myskin name, 2: version number */
				esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin['Name'], $response['new_version'] ) )
			),
			$response['new_version']
		);
	} else {
		/* translators: 1: myskin name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
		printf( __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
			$myskin['Name'],
			esc_url( $details_url ),
			sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
				/* translators: 1: myskin name, 2: version number */
				esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin['Name'], $response['new_version'] ) )
			),
			$response['new_version'],
			mcms_nonce_url( self_admin_url( 'update.php?action=upgrade-myskin&myskin=' ) . $myskin_key, 'upgrade-myskin_' . $myskin_key ),
			sprintf( 'class="update-link" aria-label="%s"',
				/* translators: %s: myskin name */
				esc_attr( sprintf( __( 'Update %s now' ), $myskin['Name'] ) )
			)
		);
	}

	/**
	 * Fires at the end of the update message container in each
	 * row of the myskins list table.
	 *
	 * The dynamic portion of the hook name, `$myskin_key`, refers to
	 * the myskin slug as found in the MandarinCMS.org myskins repository.
	 *
	 * @since 3.1.0
	 *
	 * @param MCMS_MySkin $myskin    The MCMS_MySkin object.
	 * @param array    $response {
	 *     An array of metadata about the available myskin update.
	 *
	 *     @type string $new_version New myskin version.
	 *     @type string $url         MySkin URL.
	 *     @type string $package     MySkin update package URL.
	 * }
	 */
	do_action( "in_myskin_update_message-{$myskin_key}", $myskin, $response );

	echo '</p></div></td></tr>';
}

/**
 *
 * @global int $upgrading
 * @return false|void
 */
function maintenance_nag() {
	include( BASED_TREE_URI . MCMSINC . '/version.php' ); // include an unmodified $mcms_version
	global $upgrading;
	$nag = isset( $upgrading );
	if ( ! $nag ) {
		$failed = get_site_option( 'auto_core_update_failed' );
		/*
		 * If an update failed critically, we may have copied over version.php but not other files.
		 * In that case, if the installation claims we're running the version we attempted, nag.
		 * This is serious enough to err on the side of nagging.
		 *
		 * If we simply failed to update before we tried to copy any files, then assume things are
		 * OK if they are now running the latest.
		 *
		 * This flag is cleared whenever a successful update occurs using Core_Upgrader.
		 */
		$comparison = ! empty( $failed['critical'] ) ? '>=' : '>';
		if ( version_compare( $failed['attempted'], $mcms_version, $comparison ) )
			$nag = true;
	}

	if ( ! $nag )
		return false;

	if ( current_user_can('update_core') )
		$msg = sprintf( __('An automated MandarinCMS update has failed to complete - <a href="%s">please attempt the update again now</a>.'), 'update-core.php' );
	else
		$msg = __('An automated MandarinCMS update has failed to complete! Please notify the site administrator.');

	echo "<div class='update-nag'>$msg</div>";
}

/**
 * Prints the JavaScript templates for update admin notices.
 *
 * Template takes one argument with four values:
 *
 *     param {object} data {
 *         Arguments for admin notice.
 *
 *         @type string id        ID of the notice.
 *         @type string className Class names for the notice.
 *         @type string message   The notice's message.
 *         @type string type      The type of update the notice is for. Either 'module' or 'myskin'.
 *     }
 *
 * @since 4.6.0
 */
function mcms_print_admin_notice_templates() {
	?>
	<script id="tmpl-mcms-updates-admin-notice" type="text/html">
		<div <# if ( data.id ) { #>id="{{ data.id }}"<# } #> class="notice {{ data.className }}"><p>{{{ data.message }}}</p></div>
	</script>
	<script id="tmpl-mcms-bulk-updates-admin-notice" type="text/html">
		<div id="{{ data.id }}" class="{{ data.className }} notice <# if ( data.errors ) { #>notice-error<# } else { #>notice-success<# } #>">
			<p>
				<# if ( data.successes ) { #>
					<# if ( 1 === data.successes ) { #>
						<# if ( 'module' === data.type ) { #>
							<?php
							/* translators: %s: Number of modules */
							printf( __( '%s module successfully updated.' ), '{{ data.successes }}' );
							?>
						<# } else { #>
							<?php
							/* translators: %s: Number of myskins */
							printf( __( '%s myskin successfully updated.' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } else { #>
						<# if ( 'module' === data.type ) { #>
							<?php
							/* translators: %s: Number of modules */
							printf( __( '%s modules successfully updated.' ), '{{ data.successes }}' );
							?>
						<# } else { #>
							<?php
							/* translators: %s: Number of myskins */
							printf( __( '%s myskins successfully updated.' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } #>
				<# } #>
				<# if ( data.errors ) { #>
					<button class="button-link bulk-action-errors-collapsed" aria-expanded="false">
						<# if ( 1 === data.errors ) { #>
							<?php
							/* translators: %s: Number of failed updates */
							printf( __( '%s update failed.' ), '{{ data.errors }}' );
							?>
						<# } else { #>
							<?php
							/* translators: %s: Number of failed updates */
							printf( __( '%s updates failed.' ), '{{ data.errors }}' );
							?>
						<# } #>
						<span class="screen-reader-text"><?php _e( 'Show more details' ); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
				<# } #>
			</p>
			<# if ( data.errors ) { #>
				<ul class="bulk-action-errors hidden">
					<# _.each( data.errorMessages, function( errorMessage ) { #>
						<li>{{ errorMessage }}</li>
					<# } ); #>
				</ul>
			<# } #>
		</div>
	</script>
	<?php
}

/**
 * Prints the JavaScript templates for update and deletion rows in list tables.
 *
 * The update template takes one argument with four values:
 *
 *     param {object} data {
 *         Arguments for the update row
 *
 *         @type string slug    Module slug.
 *         @type string module  Module base name.
 *         @type string colspan The number of table columns this row spans.
 *         @type string content The row content.
 *     }
 *
 * The delete template takes one argument with four values:
 *
 *     param {object} data {
 *         Arguments for the update row
 *
 *         @type string slug    Module slug.
 *         @type string module  Module base name.
 *         @type string name    Module name.
 *         @type string colspan The number of table columns this row spans.
 *     }
 *
 * @since 4.6.0
 */
function mcms_print_update_row_templates() {
	?>
	<script id="tmpl-item-update-row" type="text/template">
		<tr class="module-update-tr update" id="{{ data.slug }}-update" data-slug="{{ data.slug }}" <# if ( data.module ) { #>data-module="{{ data.module }}"<# } #>>
			<td colspan="{{ data.colspan }}" class="module-update colspanchange">
				{{{ data.content }}}
			</td>
		</tr>
	</script>
	<script id="tmpl-item-deleted-row" type="text/template">
		<tr class="module-deleted-tr inactive deleted" id="{{ data.slug }}-deleted" data-slug="{{ data.slug }}" <# if ( data.module ) { #>data-module="{{ data.module }}"<# } #>>
			<td colspan="{{ data.colspan }}" class="module-update colspanchange">
				<# if ( data.module ) { #>
					<?php
					printf(
						/* translators: %s: Module name */
						_x( '%s was successfully deleted.', 'module' ),
						'<strong>{{{ data.name }}}</strong>'
					);
					?>
				<# } else { #>
					<?php
					printf(
						/* translators: %s: MySkin name */
						_x( '%s was successfully deleted.', 'myskin' ),
						'<strong>{{{ data.name }}}</strong>'
					);
					?>
				<# } #>
			</td>
		</tr>
	</script>
	<?php
}
