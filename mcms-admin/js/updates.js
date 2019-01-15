/**
 * Functions for ajaxified updates, deletions and installs inside the MandarinCMS admin.
 *
 * @version 4.2.0
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/* global pagenow */

/**
 * @param {jQuery}  $                                   jQuery object.
 * @param {object}  mcms                                  MCMS object.
 * @param {object}  settings                            MCMS Updates settings.
 * @param {string}  settings.ajax_nonce                 AJAX nonce.
 * @param {object}  settings.l10n                       Translation strings.
 * @param {object=} settings.modules                    Base names of modules in their different states.
 * @param {Array}   settings.modules.all                Base names of all modules.
 * @param {Array}   settings.modules.active             Base names of active modules.
 * @param {Array}   settings.modules.inactive           Base names of inactive modules.
 * @param {Array}   settings.modules.upgrade            Base names of modules with updates available.
 * @param {Array}   settings.modules.recently_activated Base names of recently activated modules.
 * @param {object=} settings.myskins                     Module/myskin status information or null.
 * @param {number}  settings.myskins.all                 Amount of all myskins.
 * @param {number}  settings.myskins.upgrade             Amount of myskins with updates available.
 * @param {number}  settings.myskins.disabled            Amount of disabled myskins.
 * @param {object=} settings.totals                     Combined information for available update counts.
 * @param {number}  settings.totals.count               Holds the amount of available updates.
 */
(function( $, mcms, settings ) {
	var $document = $( document );

	mcms = mcms || {};

	/**
	 * The MCMS Updates object.
	 *
	 * @since 4.2.0
	 *
	 * @type {object}
	 */
	mcms.updates = {};

	/**
	 * User nonce for ajax calls.
	 *
	 * @since 4.2.0
	 *
	 * @type {string}
	 */
	mcms.updates.ajaxNonce = settings.ajax_nonce;

	/**
	 * Localized strings.
	 *
	 * @since 4.2.0
	 *
	 * @type {object}
	 */
	mcms.updates.l10n = settings.l10n;

	/**
	 * Current search term.
	 *
	 * @since 4.6.0
	 *
	 * @type {string}
	 */
	mcms.updates.searchTerm = '';

	/**
	 * Whether filesystem credentials need to be requested from the user.
	 *
	 * @since 4.2.0
	 *
	 * @type {bool}
	 */
	mcms.updates.shouldRequestFilesystemCredentials = false;

	/**
	 * Filesystem credentials to be packaged along with the request.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Added `available` property to indicate whether credentials have been provided.
	 *
	 * @type {object} filesystemCredentials                    Holds filesystem credentials.
	 * @type {object} filesystemCredentials.ftp                Holds FTP credentials.
	 * @type {string} filesystemCredentials.ftp.host           FTP host. Default empty string.
	 * @type {string} filesystemCredentials.ftp.username       FTP user name. Default empty string.
	 * @type {string} filesystemCredentials.ftp.password       FTP password. Default empty string.
	 * @type {string} filesystemCredentials.ftp.connectionType Type of FTP connection. 'ssh', 'ftp', or 'ftps'.
	 *                                                         Default empty string.
	 * @type {object} filesystemCredentials.ssh                Holds SSH credentials.
	 * @type {string} filesystemCredentials.ssh.publicKey      The public key. Default empty string.
	 * @type {string} filesystemCredentials.ssh.privateKey     The private key. Default empty string.
	 * @type {string} filesystemCredentials.fsNonce            Filesystem credentials form nonce.
	 * @type {bool}   filesystemCredentials.available          Whether filesystem credentials have been provided.
	 *                                                         Default 'false'.
	 */
	mcms.updates.filesystemCredentials = {
		ftp:       {
			host:           '',
			username:       '',
			password:       '',
			connectionType: ''
		},
		ssh:       {
			publicKey:  '',
			privateKey: ''
		},
		fsNonce: '',
		available: false
	};

	/**
	 * Whether we're waiting for an Ajax request to complete.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `ajaxLocked`.
	 *
	 * @type {bool}
	 */
	mcms.updates.ajaxLocked = false;

	/**
	 * Admin notice template.
	 *
	 * @since 4.6.0
	 *
	 * @type {function} A function that lazily-compiles the template requested.
	 */
	mcms.updates.adminNotice = mcms.template( 'mcms-updates-admin-notice' );

	/**
	 * Update queue.
	 *
	 * If the user tries to update a module while an update is
	 * already happening, it can be placed in this queue to perform later.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `queue`.
	 *
	 * @type {Array.object}
	 */
	mcms.updates.queue = [];

	/**
	 * Holds a jQuery reference to return focus to when exiting the request credentials modal.
	 *
	 * @since 4.2.0
	 *
	 * @type {jQuery}
	 */
	mcms.updates.$elToReturnFocusToFromCredentialsModal = undefined;

	/**
	 * Adds or updates an admin notice.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}  data
	 * @param {*=}      data.selector      Optional. Selector of an element to be replaced with the admin notice.
	 * @param {string=} data.id            Optional. Unique id that will be used as the notice's id attribute.
	 * @param {string=} data.className     Optional. Class names that will be used in the admin notice.
	 * @param {string=} data.message       Optional. The message displayed in the notice.
	 * @param {number=} data.successes     Optional. The amount of successful operations.
	 * @param {number=} data.errors        Optional. The amount of failed operations.
	 * @param {Array=}  data.errorMessages Optional. Error messages of failed operations.
	 *
	 */
	mcms.updates.addAdminNotice = function( data ) {
		var $notice = $( data.selector ), $adminNotice;

		delete data.selector;
		$adminNotice = mcms.updates.adminNotice( data );

		// Check if this admin notice already exists.
		if ( ! $notice.length ) {
			$notice = $( '#' + data.id );
		}

		if ( $notice.length ) {
			$notice.replaceWith( $adminNotice );
		} else {
			if ( 'customize' === pagenow ) {
				$( '.customize-myskins-notifications' ).append( $adminNotice );
			} else {
				$( '.wrap' ).find( '> h1' ).after( $adminNotice );
			}
		}

		$document.trigger( 'mcms-updates-notice-added' );
	};

	/**
	 * Handles Ajax requests to MandarinCMS.
	 *
	 * @since 4.6.0
	 *
	 * @param {string} action The type of Ajax request ('update-module', 'install-myskin', etc).
	 * @param {object} data   Data that needs to be passed to the ajax callback.
	 * @return {$.promise}    A jQuery promise that represents the request,
	 *                        decorated with an abort() method.
	 */
	mcms.updates.ajax = function( action, data ) {
		var options = {};

		if ( mcms.updates.ajaxLocked ) {
			mcms.updates.queue.push( {
				action: action,
				data:   data
			} );

			// Return a Deferred object so callbacks can always be registered.
			return $.Deferred();
		}

		mcms.updates.ajaxLocked = true;

		if ( data.success ) {
			options.success = data.success;
			delete data.success;
		}

		if ( data.error ) {
			options.error = data.error;
			delete data.error;
		}

		options.data = _.extend( data, {
			action:          action,
			_ajax_nonce:     mcms.updates.ajaxNonce,
			_fs_nonce:       mcms.updates.filesystemCredentials.fsNonce,
			username:        mcms.updates.filesystemCredentials.ftp.username,
			password:        mcms.updates.filesystemCredentials.ftp.password,
			hostname:        mcms.updates.filesystemCredentials.ftp.hostname,
			connection_type: mcms.updates.filesystemCredentials.ftp.connectionType,
			public_key:      mcms.updates.filesystemCredentials.ssh.publicKey,
			private_key:     mcms.updates.filesystemCredentials.ssh.privateKey
		} );

		return mcms.ajax.send( options ).always( mcms.updates.ajaxAlways );
	};

	/**
	 * Actions performed after every Ajax request.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}  response
	 * @param {array=}  response.debug     Optional. Debug information.
	 * @param {string=} response.errorCode Optional. Error code for an error that occurred.
	 */
	mcms.updates.ajaxAlways = function( response ) {
		if ( ! response.errorCode || 'unable_to_connect_to_filesystem' !== response.errorCode ) {
			mcms.updates.ajaxLocked = false;
			mcms.updates.queueChecker();
		}

		if ( 'undefined' !== typeof response.debug && window.console && window.console.log ) {
			_.map( response.debug, function( message ) {
				window.console.log( $( '<p />' ).html( message ).text() );
			} );
		}
	};

	/**
	 * Refreshes update counts everywhere on the screen.
	 *
	 * @since 4.7.0
	 */
	mcms.updates.refreshCount = function() {
		var $adminBarUpdates              = $( '#mcms-admin-bar-updates' ),
			$dashboardNavMenuUpdateCount  = $( 'a[href="update-core.php"] .update-modules' ),
			$modulesNavMenuUpdateCount    = $( 'a[href="modules.php"] .update-modules' ),
			$appearanceNavMenuUpdateCount = $( 'a[href="myskins.php"] .update-modules' ),
			itemCount;

		$adminBarUpdates.find( '.ab-item' ).removeAttr( 'title' );
		$adminBarUpdates.find( '.ab-label' ).text( settings.totals.counts.total );

		// Remove the update count from the toolbar if it's zero.
		if ( 0 === settings.totals.counts.total ) {
			$adminBarUpdates.find( '.ab-label' ).parents( 'li' ).remove();
		}

		// Update the "Updates" menu item.
		$dashboardNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.total );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$dashboardNavMenuUpdateCount.find( '.update-count' ).text( settings.totals.counts.total );
		} else {
			$dashboardNavMenuUpdateCount.remove();
		}

		// Update the "Modules" menu item.
		$modulesNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.modules );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$modulesNavMenuUpdateCount.find( '.module-count' ).text( settings.totals.counts.modules );
		} else {
			$modulesNavMenuUpdateCount.remove();
		}

		// Update the "Dexign" menu item.
		$appearanceNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.myskins );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$appearanceNavMenuUpdateCount.find( '.myskin-count' ).text( settings.totals.counts.myskins );
		} else {
			$appearanceNavMenuUpdateCount.remove();
		}

		// Update list table filter navigation.
		if ( 'modules' === pagenow || 'modules-network' === pagenow ) {
			itemCount = settings.totals.counts.modules;
		} else if ( 'myskins' === pagenow || 'myskins-network' === pagenow ) {
			itemCount = settings.totals.counts.myskins;
		}

		if ( itemCount > 0 ) {
			$( '.subsubsub .upgrade .count' ).text( '(' + itemCount + ')' );
		} else {
			$( '.subsubsub .upgrade' ).remove();
			$( '.subsubsub li:last' ).html( function() { return $( this ).children(); } );
		}
	};

	/**
	 * Decrements the update counts throughout the various menus.
	 *
	 * This includes the toolbar, the "Updates" menu item and the menu items
	 * for modules and myskins.
	 *
	 * @since 3.9.0
	 *
	 * @param {string} type The type of item that was updated or deleted.
	 *                      Can be 'module', 'myskin'.
	 */
	mcms.updates.decrementCount = function( type ) {
		settings.totals.counts.total = Math.max( --settings.totals.counts.total, 0 );

		if ( 'module' === type ) {
			settings.totals.counts.modules = Math.max( --settings.totals.counts.modules, 0 );
		} else if ( 'myskin' === type ) {
			settings.totals.counts.myskins = Math.max( --settings.totals.counts.myskins, 0 );
		}

		mcms.updates.refreshCount( type );
	};

	/**
	 * Sends an Ajax request to the server to update a module.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updateModule`.
	 *
	 * @param {object}               args         Arguments.
	 * @param {string}               args.module  Module basename.
	 * @param {string}               args.slug    Module slug.
	 * @param {updateModuleSuccess=} args.success Optional. Success callback. Default: mcms.updates.updateModuleSuccess
	 * @param {updateModuleError=}   args.error   Optional. Error callback. Default: mcms.updates.updateModuleError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.updateModule = function( args ) {
		var $updateRow, $card, $message, message;

		args = _.extend( {
			success: mcms.updates.updateModuleSuccess,
			error: mcms.updates.updateModuleError
		}, args );

		if ( 'modules' === pagenow || 'modules-network' === pagenow ) {
			$updateRow = $( 'tr[data-module="' + args.module + '"]' );
			$message   = $updateRow.find( '.update-message' ).removeClass( 'notice-error' ).addClass( 'updating-message notice-warning' ).find( 'p' );
			message    = mcms.updates.l10n.moduleUpdatingLabel.replace( '%s', $updateRow.find( '.module-title strong' ).text() );
		} else if ( 'module-install' === pagenow || 'module-install-network' === pagenow ) {
			$card    = $( '.module-card-' + args.slug );
			$message = $card.find( '.update-now' ).addClass( 'updating-message' );
			message  = mcms.updates.l10n.moduleUpdatingLabel.replace( '%s', $message.data( 'name' ) );

			// Remove previous error messages, if any.
			$card.removeClass( 'module-card-update-failed' ).find( '.notice.notice-error' ).remove();
		}

		if ( $message.html() !== mcms.updates.l10n.updating ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.attr( 'aria-label', message )
			.text( mcms.updates.l10n.updating );

		$document.trigger( 'mcms-module-updating', args );

		return mcms.updates.ajax( 'update-module', args );
	};

	/**
	 * Updates the UI appropriately after a successful module update.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updateModuleSuccess`.
	 *
	 * @typedef {object} updateModuleSuccess
	 * @param {object} response            Response from the server.
	 * @param {string} response.slug       Slug of the module to be updated.
	 * @param {string} response.module     Basename of the module to be updated.
	 * @param {string} response.moduleName Name of the module to be updated.
	 * @param {string} response.oldVersion Old version of the module.
	 * @param {string} response.newVersion New version of the module.
	 */
	mcms.updates.updateModuleSuccess = function( response ) {
		var $moduleRow, $updateMessage, newText;

		if ( 'modules' === pagenow || 'modules-network' === pagenow ) {
			$moduleRow     = $( 'tr[data-module="' + response.module + '"]' )
				.removeClass( 'update' )
				.addClass( 'updated' );
			$updateMessage = $moduleRow.find( '.update-message' )
				.removeClass( 'updating-message notice-warning' )
				.addClass( 'updated-message notice-success' ).find( 'p' );

			// Update the version number in the row.
			newText = $moduleRow.find( '.module-version-author-uri' ).html().replace( response.oldVersion, response.newVersion );
			$moduleRow.find( '.module-version-author-uri' ).html( newText );
		} else if ( 'module-install' === pagenow || 'module-install-network' === pagenow ) {
			$updateMessage = $( '.module-card-' + response.slug ).find( '.update-now' )
				.removeClass( 'updating-message' )
				.addClass( 'button-disabled updated-message' );
		}

		$updateMessage
			.attr( 'aria-label', mcms.updates.l10n.moduleUpdatedLabel.replace( '%s', response.moduleName ) )
			.text( mcms.updates.l10n.moduleUpdated );

		mcms.a11y.speak( mcms.updates.l10n.updatedMsg, 'polite' );

		mcms.updates.decrementCount( 'module' );

		$document.trigger( 'mcms-module-update-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed module update.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updateModuleError`.
	 *
	 * @typedef {object} updateModuleError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the module to be updated.
	 * @param {string}  response.module       Basename of the module to be updated.
	 * @param {string=} response.moduleName   Optional. Name of the module to be updated.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mcms.updates.updateModuleError = function( response ) {
		var $card, $message, errorMessage;

		if ( ! mcms.updates.isValidResponse( response, 'update' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'update-module' ) ) {
			return;
		}

		errorMessage = mcms.updates.l10n.updateFailed.replace( '%s', response.errorMessage );

		if ( 'modules' === pagenow || 'modules-network' === pagenow ) {
			if ( response.module ) {
				$message = $( 'tr[data-module="' + response.module + '"]' ).find( '.update-message' );
			} else {
				$message = $( 'tr[data-slug="' + response.slug + '"]' ).find( '.update-message' );
			}
			$message.removeClass( 'updating-message notice-warning' ).addClass( 'notice-error' ).find( 'p' ).html( errorMessage );

			if ( response.moduleName ) {
				$message.find( 'p' )
					.attr( 'aria-label', mcms.updates.l10n.moduleUpdateFailedLabel.replace( '%s', response.moduleName ) );
			} else {
				$message.find( 'p' ).removeAttr( 'aria-label' );
			}
		} else if ( 'module-install' === pagenow || 'module-install-network' === pagenow ) {
			$card = $( '.module-card-' + response.slug )
				.addClass( 'module-card-update-failed' )
				.append( mcms.updates.adminNotice( {
					className: 'update-message notice-error notice-alt is-dismissible',
					message:   errorMessage
				} ) );

			$card.find( '.update-now' )
				.text( mcms.updates.l10n.updateFailedShort ).removeClass( 'updating-message' );

			if ( response.moduleName ) {
				$card.find( '.update-now' )
					.attr( 'aria-label', mcms.updates.l10n.moduleUpdateFailedLabel.replace( '%s', response.moduleName ) );
			} else {
				$card.find( '.update-now' ).removeAttr( 'aria-label' );
			}

			$card.on( 'click', '.notice.is-dismissible .notice-dismiss', function() {

				// Use same delay as the total duration of the notice fadeTo + slideUp animation.
				setTimeout( function() {
					$card
						.removeClass( 'module-card-update-failed' )
						.find( '.column-name a' ).focus();

					$card.find( '.update-now' )
						.attr( 'aria-label', false )
						.text( mcms.updates.l10n.updateNow );
				}, 200 );
			} );
		}

		mcms.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mcms-module-update-error', response );
	};

	/**
	 * Sends an Ajax request to the server to install a module.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}                args         Arguments.
	 * @param {string}                args.slug    Module identifier in the MandarinCMS.org Module repository.
	 * @param {installModuleSuccess=} args.success Optional. Success callback. Default: mcms.updates.installModuleSuccess
	 * @param {installModuleError=}   args.error   Optional. Error callback. Default: mcms.updates.installModuleError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.installModule = function( args ) {
		var $card    = $( '.module-card-' + args.slug ),
			$message = $card.find( '.install-now' );

		args = _.extend( {
			success: mcms.updates.installModuleSuccess,
			error: mcms.updates.installModuleError
		}, args );

		if ( 'import' === pagenow ) {
			$message = $( '[data-slug="' + args.slug + '"]' );
		}

		if ( $message.html() !== mcms.updates.l10n.installing ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.addClass( 'updating-message' )
			.attr( 'aria-label', mcms.updates.l10n.moduleInstallingLabel.replace( '%s', $message.data( 'name' ) ) )
			.text( mcms.updates.l10n.installing );

		mcms.a11y.speak( mcms.updates.l10n.installingMsg, 'polite' );

		// Remove previous error messages, if any.
		$card.removeClass( 'module-card-install-failed' ).find( '.notice.notice-error' ).remove();

		$document.trigger( 'mcms-module-installing', args );

		return mcms.updates.ajax( 'install-module', args );
	};

	/**
	 * Updates the UI appropriately after a successful module install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installModuleSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed module.
	 * @param {string} response.moduleName  Name of the installed module.
	 * @param {string} response.activateUrl URL to activate the just installed module.
	 */
	mcms.updates.installModuleSuccess = function( response ) {
		var $message = $( '.module-card-' + response.slug ).find( '.install-now' );

		$message
			.removeClass( 'updating-message' )
			.addClass( 'updated-message installed button-disabled' )
			.attr( 'aria-label', mcms.updates.l10n.moduleInstalledLabel.replace( '%s', response.moduleName ) )
			.text( mcms.updates.l10n.moduleInstalled );

		mcms.a11y.speak( mcms.updates.l10n.installedMsg, 'polite' );

		$document.trigger( 'mcms-module-install-success', response );

		if ( response.activateUrl ) {
			setTimeout( function() {

				// Transform the 'Install' button into an 'Activate' button.
				$message.removeClass( 'install-now installed button-disabled updated-message' ).addClass( 'activate-now button-primary' )
					.attr( 'href', response.activateUrl )
					.attr( 'aria-label', mcms.updates.l10n.activateModuleLabel.replace( '%s', response.moduleName ) )
					.text( mcms.updates.l10n.activateModule );
			}, 1000 );
		}
	};

	/**
	 * Updates the UI appropriately after a failed module install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installModuleError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the module to be installed.
	 * @param {string=} response.moduleName   Optional. Name of the module to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mcms.updates.installModuleError = function( response ) {
		var $card   = $( '.module-card-' + response.slug ),
			$button = $card.find( '.install-now' ),
			errorMessage;

		if ( ! mcms.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'install-module' ) ) {
			return;
		}

		errorMessage = mcms.updates.l10n.installFailed.replace( '%s', response.errorMessage );

		$card
			.addClass( 'module-card-update-failed' )
			.append( '<div class="notice notice-error notice-alt is-dismissible"><p>' + errorMessage + '</p></div>' );

		$card.on( 'click', '.notice.is-dismissible .notice-dismiss', function() {

			// Use same delay as the total duration of the notice fadeTo + slideUp animation.
			setTimeout( function() {
				$card
					.removeClass( 'module-card-update-failed' )
					.find( '.column-name a' ).focus();
			}, 200 );
		} );

		$button
			.removeClass( 'updating-message' ).addClass( 'button-disabled' )
			.attr( 'aria-label', mcms.updates.l10n.moduleInstallFailedLabel.replace( '%s', $button.data( 'name' ) ) )
			.text( mcms.updates.l10n.installFailedShort );

		mcms.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mcms-module-install-error', response );
	};

	/**
	 * Updates the UI appropriately after a successful importer install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installImporterSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed module.
	 * @param {string} response.moduleName  Name of the installed module.
	 * @param {string} response.activateUrl URL to activate the just installed module.
	 */
	mcms.updates.installImporterSuccess = function( response ) {
		mcms.updates.addAdminNotice( {
			id:        'install-success',
			className: 'notice-success is-dismissible',
			message:   mcms.updates.l10n.importerInstalledMsg.replace( '%s', response.activateUrl + '&from=import' )
		} );

		$( '[data-slug="' + response.slug + '"]' )
			.removeClass( 'install-now updating-message' )
			.addClass( 'activate-now' )
			.attr({
				'href': response.activateUrl + '&from=import',
				'aria-label': mcms.updates.l10n.activateImporterLabel.replace( '%s', response.moduleName )
			})
			.text( mcms.updates.l10n.activateImporter );

		mcms.a11y.speak( mcms.updates.l10n.installedMsg, 'polite' );

		$document.trigger( 'mcms-importer-install-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed importer install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installImporterError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the module to be installed.
	 * @param {string=} response.moduleName   Optional. Name of the module to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mcms.updates.installImporterError = function( response ) {
		var errorMessage = mcms.updates.l10n.installFailed.replace( '%s', response.errorMessage ),
			$installLink = $( '[data-slug="' + response.slug + '"]' ),
			moduleName = $installLink.data( 'name' );

		if ( ! mcms.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'install-module' ) ) {
			return;
		}

		mcms.updates.addAdminNotice( {
			id:        response.errorCode,
			className: 'notice-error is-dismissible',
			message:   errorMessage
		} );

		$installLink
			.removeClass( 'updating-message' )
			.text( mcms.updates.l10n.installNow )
			.attr( 'aria-label', mcms.updates.l10n.installNowLabel.replace( '%s', moduleName ) );

		mcms.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mcms-importer-install-error', response );
	};

	/**
	 * Sends an Ajax request to the server to delete a module.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}               args         Arguments.
	 * @param {string}               args.module  Basename of the module to be deleted.
	 * @param {string}               args.slug    Slug of the module to be deleted.
	 * @param {deleteModuleSuccess=} args.success Optional. Success callback. Default: mcms.updates.deleteModuleSuccess
	 * @param {deleteModuleError=}   args.error   Optional. Error callback. Default: mcms.updates.deleteModuleError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.deleteModule = function( args ) {
		var $link = $( '[data-module="' + args.module + '"]' ).find( '.row-actions a.delete' );

		args = _.extend( {
			success: mcms.updates.deleteModuleSuccess,
			error: mcms.updates.deleteModuleError
		}, args );

		if ( $link.html() !== mcms.updates.l10n.deleting ) {
			$link
				.data( 'originaltext', $link.html() )
				.text( mcms.updates.l10n.deleting );
		}

		mcms.a11y.speak( mcms.updates.l10n.deleting, 'polite' );

		$document.trigger( 'mcms-module-deleting', args );

		return mcms.updates.ajax( 'delete-module', args );
	};

	/**
	 * Updates the UI appropriately after a successful module deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteModuleSuccess
	 * @param {object} response            Response from the server.
	 * @param {string} response.slug       Slug of the module that was deleted.
	 * @param {string} response.module     Base name of the module that was deleted.
	 * @param {string} response.moduleName Name of the module that was deleted.
	 */
	mcms.updates.deleteModuleSuccess = function( response ) {

		// Removes the module and updates rows.
		$( '[data-module="' + response.module + '"]' ).css( { backgroundColor: '#faafaa' } ).fadeOut( 350, function() {
			var $form            = $( '#bulk-action-form' ),
				$views           = $( '.subsubsub' ),
				$moduleRow       = $( this ),
				columnCount      = $form.find( 'thead th:not(.hidden), thead td' ).length,
				moduleDeletedRow = mcms.template( 'item-deleted-row' ),
				/** @type {object} modules Base names of modules in their different states. */
				modules          = settings.modules;

			// Add a success message after deleting a module.
			if ( ! $moduleRow.hasClass( 'module-update-tr' ) ) {
				$moduleRow.after(
					moduleDeletedRow( {
						slug:    response.slug,
						module:  response.module,
						colspan: columnCount,
						name:    response.moduleName
					} )
				);
			}

			$moduleRow.remove();

			// Remove module from update count.
			if ( -1 !== _.indexOf( modules.upgrade, response.module ) ) {
				modules.upgrade = _.without( modules.upgrade, response.module );
				mcms.updates.decrementCount( 'module' );
			}

			// Remove from views.
			if ( -1 !== _.indexOf( modules.inactive, response.module ) ) {
				modules.inactive = _.without( modules.inactive, response.module );
				if ( modules.inactive.length ) {
					$views.find( '.inactive .count' ).text( '(' + modules.inactive.length + ')' );
				} else {
					$views.find( '.inactive' ).remove();
				}
			}

			if ( -1 !== _.indexOf( modules.active, response.module ) ) {
				modules.active = _.without( modules.active, response.module );
				if ( modules.active.length ) {
					$views.find( '.active .count' ).text( '(' + modules.active.length + ')' );
				} else {
					$views.find( '.active' ).remove();
				}
			}

			if ( -1 !== _.indexOf( modules.recently_activated, response.module ) ) {
				modules.recently_activated = _.without( modules.recently_activated, response.module );
				if ( modules.recently_activated.length ) {
					$views.find( '.recently_activated .count' ).text( '(' + modules.recently_activated.length + ')' );
				} else {
					$views.find( '.recently_activated' ).remove();
				}
			}

			modules.all = _.without( modules.all, response.module );

			if ( modules.all.length ) {
				$views.find( '.all .count' ).text( '(' + modules.all.length + ')' );
			} else {
				$form.find( '.tablenav' ).css( { visibility: 'hidden' } );
				$views.find( '.all' ).remove();

				if ( ! $form.find( 'tr.no-items' ).length ) {
					$form.find( '#the-list' ).append( '<tr class="no-items"><td class="colspanchange" colspan="' + columnCount + '">' + mcms.updates.l10n.noModules + '</td></tr>' );
				}
			}
		} );

		mcms.a11y.speak( mcms.updates.l10n.moduleDeleted, 'polite' );

		$document.trigger( 'mcms-module-delete-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed module deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteModuleError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the module to be deleted.
	 * @param {string}  response.module       Base name of the module to be deleted
	 * @param {string=} response.moduleName   Optional. Name of the module to be deleted.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mcms.updates.deleteModuleError = function( response ) {
		var $module, $moduleUpdateRow,
			moduleUpdateRow  = mcms.template( 'item-update-row' ),
			noticeContent    = mcms.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   response.errorMessage
			} );

		if ( response.module ) {
			$module          = $( 'tr.inactive[data-module="' + response.module + '"]' );
			$moduleUpdateRow = $module.siblings( '[data-module="' + response.module + '"]' );
		} else {
			$module          = $( 'tr.inactive[data-slug="' + response.slug + '"]' );
			$moduleUpdateRow = $module.siblings( '[data-slug="' + response.slug + '"]' );
		}

		if ( ! mcms.updates.isValidResponse( response, 'delete' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'delete-module' ) ) {
			return;
		}

		// Add a module update row if it doesn't exist yet.
		if ( ! $moduleUpdateRow.length ) {
			$module.addClass( 'update' ).after(
				moduleUpdateRow( {
					slug:    response.slug,
					module:  response.module || response.slug,
					colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
					content: noticeContent
				} )
			);
		} else {

			// Remove previous error messages, if any.
			$moduleUpdateRow.find( '.notice-error' ).remove();

			$moduleUpdateRow.find( '.module-update' ).append( noticeContent );
		}

		$document.trigger( 'mcms-module-delete-error', response );
	};

	/**
	 * Sends an Ajax request to the server to update a myskin.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}              args         Arguments.
	 * @param {string}              args.slug    MySkin stylesheet.
	 * @param {updateMySkinSuccess=} args.success Optional. Success callback. Default: mcms.updates.updateMySkinSuccess
	 * @param {updateMySkinError=}   args.error   Optional. Error callback. Default: mcms.updates.updateMySkinError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.updateMySkin = function( args ) {
		var $notice;

		args = _.extend( {
			success: mcms.updates.updateMySkinSuccess,
			error: mcms.updates.updateMySkinError
		}, args );

		if ( 'myskins-network' === pagenow ) {
			$notice = $( '[data-slug="' + args.slug + '"]' ).find( '.update-message' ).removeClass( 'notice-error' ).addClass( 'updating-message notice-warning' ).find( 'p' );

		} else if ( 'customize' === pagenow ) {

			// Update the myskin details UI.
			$notice = $( '[data-slug="' + args.slug + '"].notice' ).removeClass( 'notice-large' );

			$notice.find( 'h3' ).remove();

			// Add the top-level UI, and update both.
			$notice = $notice.add( $( '#customize-control-installed_myskin_' + args.slug ).find( '.update-message' ) );
			$notice = $notice.addClass( 'updating-message' ).find( 'p' );

		} else {
			$notice = $( '#update-myskin' ).closest( '.notice' ).removeClass( 'notice-large' );

			$notice.find( 'h3' ).remove();

			$notice = $notice.add( $( '[data-slug="' + args.slug + '"]' ).find( '.update-message' ) );
			$notice = $notice.addClass( 'updating-message' ).find( 'p' );
		}

		if ( $notice.html() !== mcms.updates.l10n.updating ) {
			$notice.data( 'originaltext', $notice.html() );
		}

		mcms.a11y.speak( mcms.updates.l10n.updatingMsg, 'polite' );
		$notice.text( mcms.updates.l10n.updating );

		$document.trigger( 'mcms-myskin-updating', args );

		return mcms.updates.ajax( 'update-myskin', args );
	};

	/**
	 * Updates the UI appropriately after a successful myskin update.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} updateMySkinSuccess
	 * @param {object} response
	 * @param {string} response.slug       Slug of the myskin to be updated.
	 * @param {object} response.myskin      Updated myskin.
	 * @param {string} response.oldVersion Old version of the myskin.
	 * @param {string} response.newVersion New version of the myskin.
	 */
	mcms.updates.updateMySkinSuccess = function( response ) {
		var isModalOpen    = $( 'body.modal-open' ).length,
			$myskin         = $( '[data-slug="' + response.slug + '"]' ),
			updatedMessage = {
				className: 'updated-message notice-success notice-alt',
				message:   mcms.updates.l10n.myskinUpdated
			},
			$notice, newText;

		if ( 'customize' === pagenow ) {
			$myskin = $( '.updating-message' ).siblings( '.myskin-name' );

			if ( $myskin.length ) {

				// Update the version number in the row.
				newText = $myskin.html().replace( response.oldVersion, response.newVersion );
				$myskin.html( newText );
			}

			$notice = $( '.myskin-info .notice' ).add( mcms.customize.control( 'installed_myskin_' + response.slug ).container.find( '.myskin' ).find( '.update-message' ) );
		} else if ( 'myskins-network' === pagenow ) {
			$notice = $myskin.find( '.update-message' );

			// Update the version number in the row.
			newText = $myskin.find( '.myskin-version-author-uri' ).html().replace( response.oldVersion, response.newVersion );
			$myskin.find( '.myskin-version-author-uri' ).html( newText );
		} else {
			$notice = $( '.myskin-info .notice' ).add( $myskin.find( '.update-message' ) );

			// Focus on Customize button after updating.
			if ( isModalOpen ) {
				$( '.load-customize:visible' ).focus();
			} else {
				$myskin.find( '.load-customize' ).focus();
			}
		}

		mcms.updates.addAdminNotice( _.extend( { selector: $notice }, updatedMessage ) );
		mcms.a11y.speak( mcms.updates.l10n.updatedMsg, 'polite' );

		mcms.updates.decrementCount( 'myskin' );

		$document.trigger( 'mcms-myskin-update-success', response );

		// Show updated message after modal re-rendered.
		if ( isModalOpen && 'customize' !== pagenow ) {
			$( '.myskin-info .myskin-author' ).after( mcms.updates.adminNotice( updatedMessage ) );
		}
	};

	/**
	 * Updates the UI appropriately after a failed myskin update.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} updateMySkinError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the myskin to be updated.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mcms.updates.updateMySkinError = function( response ) {
		var $myskin       = $( '[data-slug="' + response.slug + '"]' ),
			errorMessage = mcms.updates.l10n.updateFailed.replace( '%s', response.errorMessage ),
			$notice;

		if ( ! mcms.updates.isValidResponse( response, 'update' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'update-myskin' ) ) {
			return;
		}

		if ( 'customize' === pagenow ) {
			$myskin = mcms.customize.control( 'installed_myskin_' + response.slug ).container.find( '.myskin' );
		}

		if ( 'myskins-network' === pagenow ) {
			$notice = $myskin.find( '.update-message ' );
		} else {
			$notice = $( '.myskin-info .notice' ).add( $myskin.find( '.notice' ) );

			$( 'body.modal-open' ).length ? $( '.load-customize:visible' ).focus() : $myskin.find( '.load-customize' ).focus();
		}

		mcms.updates.addAdminNotice( {
			selector:  $notice,
			className: 'update-message notice-error notice-alt is-dismissible',
			message:   errorMessage
		} );

		mcms.a11y.speak( errorMessage, 'polite' );

		$document.trigger( 'mcms-myskin-update-error', response );
	};

	/**
	 * Sends an Ajax request to the server to install a myskin.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}               args
	 * @param {string}               args.slug    MySkin stylesheet.
	 * @param {installMySkinSuccess=} args.success Optional. Success callback. Default: mcms.updates.installMySkinSuccess
	 * @param {installMySkinError=}   args.error   Optional. Error callback. Default: mcms.updates.installMySkinError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.installMySkin = function( args ) {
		var $message = $( '.myskin-install[data-slug="' + args.slug + '"]' );

		args = _.extend( {
			success: mcms.updates.installMySkinSuccess,
			error: mcms.updates.installMySkinError
		}, args );

		$message.addClass( 'updating-message' );
		$message.parents( '.myskin' ).addClass( 'focus' );
		if ( $message.html() !== mcms.updates.l10n.installing ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.text( mcms.updates.l10n.installing )
			.attr( 'aria-label', mcms.updates.l10n.myskinInstallingLabel.replace( '%s', $message.data( 'name' ) ) );
		mcms.a11y.speak( mcms.updates.l10n.installingMsg, 'polite' );

		// Remove previous error messages, if any.
		$( '.install-myskin-info, [data-slug="' + args.slug + '"]' ).removeClass( 'myskin-install-failed' ).find( '.notice.notice-error' ).remove();

		$document.trigger( 'mcms-myskin-installing', args );

		return mcms.updates.ajax( 'install-myskin', args );
	};

	/**
	 * Updates the UI appropriately after a successful myskin install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installMySkinSuccess
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the myskin to be installed.
	 * @param {string} response.customizeUrl URL to the Customizer for the just installed myskin.
	 * @param {string} response.activateUrl  URL to activate the just installed myskin.
	 */
	mcms.updates.installMySkinSuccess = function( response ) {
		var $card = $( '.mcms-full-overlay-header, [data-slug=' + response.slug + ']' ),
			$message;

		$document.trigger( 'mcms-myskin-install-success', response );

		$message = $card.find( '.button-primary' )
			.removeClass( 'updating-message' )
			.addClass( 'updated-message disabled' )
			.attr( 'aria-label', mcms.updates.l10n.myskinInstalledLabel.replace( '%s', response.myskinName ) )
			.text( mcms.updates.l10n.myskinInstalled );

		mcms.a11y.speak( mcms.updates.l10n.installedMsg, 'polite' );

		setTimeout( function() {

			if ( response.activateUrl ) {

				// Transform the 'Install' button into an 'Activate' button.
				$message
					.attr( 'href', response.activateUrl )
					.removeClass( 'myskin-install updated-message disabled' )
					.addClass( 'activate' )
					.attr( 'aria-label', mcms.updates.l10n.activateMySkinLabel.replace( '%s', response.myskinName ) )
					.text( mcms.updates.l10n.activateMySkin );
			}

			if ( response.customizeUrl ) {

				// Transform the 'Preview' button into a 'Live Preview' button.
				$message.siblings( '.preview' ).replaceWith( function () {
					return $( '<a>' )
						.attr( 'href', response.customizeUrl )
						.addClass( 'button load-customize' )
						.text( mcms.updates.l10n.livePreview );
				} );
			}
		}, 1000 );
	};

	/**
	 * Updates the UI appropriately after a failed myskin install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installMySkinError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the myskin to be installed.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mcms.updates.installMySkinError = function( response ) {
		var $card, $button,
			errorMessage = mcms.updates.l10n.installFailed.replace( '%s', response.errorMessage ),
			$message     = mcms.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   errorMessage
			} );

		if ( ! mcms.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mcms.updates.maybeHandleCredentialError( response, 'install-myskin' ) ) {
			return;
		}

		if ( 'customize' === pagenow ) {
			if ( $document.find( 'body' ).hasClass( 'modal-open' ) ) {
				$button = $( '.myskin-install[data-slug="' + response.slug + '"]' );
				$card   = $( '.myskin-overlay .myskin-info' ).prepend( $message );
			} else {
				$button = $( '.myskin-install[data-slug="' + response.slug + '"]' );
				$card   = $button.closest( '.myskin' ).addClass( 'myskin-install-failed' ).append( $message );
			}
			mcms.customize.notifications.remove( 'myskin_installing' );
		} else {
			if ( $document.find( 'body' ).hasClass( 'full-overlay-active' ) ) {
				$button = $( '.myskin-install[data-slug="' + response.slug + '"]' );
				$card   = $( '.install-myskin-info' ).prepend( $message );
			} else {
				$card   = $( '[data-slug="' + response.slug + '"]' ).removeClass( 'focus' ).addClass( 'myskin-install-failed' ).append( $message );
				$button = $card.find( '.myskin-install' );
			}
		}

		$button
			.removeClass( 'updating-message' )
			.attr( 'aria-label', mcms.updates.l10n.myskinInstallFailedLabel.replace( '%s', $button.data( 'name' ) ) )
			.text( mcms.updates.l10n.installFailedShort );

		mcms.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mcms-myskin-install-error', response );
	};

	/**
	 * Sends an Ajax request to the server to delete a myskin.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}              args
	 * @param {string}              args.slug    MySkin stylesheet.
	 * @param {deleteMySkinSuccess=} args.success Optional. Success callback. Default: mcms.updates.deleteMySkinSuccess
	 * @param {deleteMySkinError=}   args.error   Optional. Error callback. Default: mcms.updates.deleteMySkinError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mcms.updates.deleteMySkin = function( args ) {
		var $button;

		if ( 'myskins' === pagenow ) {
			$button = $( '.myskin-actions .delete-myskin' );
		} else if ( 'myskins-network' === pagenow ) {
			$button = $( '[data-slug="' + args.slug + '"]' ).find( '.row-actions a.delete' );
		}

		args = _.extend( {
			success: mcms.updates.deleteMySkinSuccess,
			error: mcms.updates.deleteMySkinError
		}, args );

		if ( $button && $button.html() !== mcms.updates.l10n.deleting ) {
			$button
				.data( 'originaltext', $button.html() )
				.text( mcms.updates.l10n.deleting );
		}

		mcms.a11y.speak( mcms.updates.l10n.deleting, 'polite' );

		// Remove previous error messages, if any.
		$( '.myskin-info .update-message' ).remove();

		$document.trigger( 'mcms-myskin-deleting', args );

		return mcms.updates.ajax( 'delete-myskin', args );
	};

	/**
	 * Updates the UI appropriately after a successful myskin deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteMySkinSuccess
	 * @param {object} response      Response from the server.
	 * @param {string} response.slug Slug of the myskin that was deleted.
	 */
	mcms.updates.deleteMySkinSuccess = function( response ) {
		var $myskinRows = $( '[data-slug="' + response.slug + '"]' );

		if ( 'myskins-network' === pagenow ) {

			// Removes the myskin and updates rows.
			$myskinRows.css( { backgroundColor: '#faafaa' } ).fadeOut( 350, function() {
				var $views     = $( '.subsubsub' ),
					$myskinRow  = $( this ),
					totals     = settings.myskins,
					deletedRow = mcms.template( 'item-deleted-row' );

				if ( ! $myskinRow.hasClass( 'module-update-tr' ) ) {
					$myskinRow.after(
						deletedRow( {
							slug:    response.slug,
							colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
							name:    $myskinRow.find( '.myskin-title strong' ).text()
						} )
					);
				}

				$myskinRow.remove();

				// Remove myskin from update count.
				if ( $myskinRow.hasClass( 'update' ) ) {
					totals.upgrade--;
					mcms.updates.decrementCount( 'myskin' );
				}

				// Remove from views.
				if ( $myskinRow.hasClass( 'inactive' ) ) {
					totals.disabled--;
					if ( totals.disabled ) {
						$views.find( '.disabled .count' ).text( '(' + totals.disabled + ')' );
					} else {
						$views.find( '.disabled' ).remove();
					}
				}

				// There is always at least one myskin available.
				$views.find( '.all .count' ).text( '(' + --totals.all + ')' );
			} );
		}

		mcms.a11y.speak( mcms.updates.l10n.myskinDeleted, 'polite' );

		$document.trigger( 'mcms-myskin-delete-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed myskin deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteMySkinError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the myskin to be deleted.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mcms.updates.deleteMySkinError = function( response ) {
		var $myskinRow    = $( 'tr.inactive[data-slug="' + response.slug + '"]' ),
			$button      = $( '.myskin-actions .delete-myskin' ),
			updateRow    = mcms.template( 'item-update-row' ),
			$updateRow   = $myskinRow.siblings( '#' + response.slug + '-update' ),
			errorMessage = mcms.updates.l10n.deleteFailed.replace( '%s', response.errorMessage ),
			$message     = mcms.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   errorMessage
			} );

		if ( mcms.updates.maybeHandleCredentialError( response, 'delete-myskin' ) ) {
			return;
		}

		if ( 'myskins-network' === pagenow ) {
			if ( ! $updateRow.length ) {
				$myskinRow.addClass( 'update' ).after(
					updateRow( {
						slug: response.slug,
						colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
						content: $message
					} )
				);
			} else {
				// Remove previous error messages, if any.
				$updateRow.find( '.notice-error' ).remove();
				$updateRow.find( '.module-update' ).append( $message );
			}
		} else {
			$( '.myskin-info .myskin-description' ).before( $message );
		}

		$button.html( $button.data( 'originaltext' ) );

		mcms.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mcms-myskin-delete-error', response );
	};

	/**
	 * Adds the appropriate callback based on the type of action and the current page.
	 *
	 * @since 4.6.0
	 * @private
	 *
	 * @param {object} data   AJAX payload.
	 * @param {string} action The type of request to perform.
	 * @return {object} The AJAX payload with the appropriate callbacks.
	 */
	mcms.updates._addCallbacks = function( data, action ) {
		if ( 'import' === pagenow && 'install-module' === action ) {
			data.success = mcms.updates.installImporterSuccess;
			data.error   = mcms.updates.installImporterError;
		}

		return data;
	};

	/**
	 * Pulls available jobs from the queue and runs them.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Can handle multiple job types.
	 */
	mcms.updates.queueChecker = function() {
		var job;

		if ( mcms.updates.ajaxLocked || ! mcms.updates.queue.length ) {
			return;
		}

		job = mcms.updates.queue.shift();

		// Handle a queue job.
		switch ( job.action ) {
			case 'install-module':
				mcms.updates.installModule( job.data );
				break;

			case 'update-module':
				mcms.updates.updateModule( job.data );
				break;

			case 'delete-module':
				mcms.updates.deleteModule( job.data );
				break;

			case 'install-myskin':
				mcms.updates.installMySkin( job.data );
				break;

			case 'update-myskin':
				mcms.updates.updateMySkin( job.data );
				break;

			case 'delete-myskin':
				mcms.updates.deleteMySkin( job.data );
				break;

			default:
				break;
		}
	};

	/**
	 * Requests the users filesystem credentials if they aren't already known.
	 *
	 * @since 4.2.0
	 *
	 * @param {Event=} event Optional. Event interface.
	 */
	mcms.updates.requestFilesystemCredentials = function( event ) {
		if ( false === mcms.updates.filesystemCredentials.available ) {
			/*
			 * After exiting the credentials request modal,
			 * return the focus to the element triggering the request.
			 */
			if ( event && ! mcms.updates.$elToReturnFocusToFromCredentialsModal ) {
				mcms.updates.$elToReturnFocusToFromCredentialsModal = $( event.target );
			}

			mcms.updates.ajaxLocked = true;
			mcms.updates.requestForCredentialsModalOpen();
		}
	};

	/**
	 * Requests the users filesystem credentials if needed and there is no lock.
	 *
	 * @since 4.6.0
	 *
	 * @param {Event=} event Optional. Event interface.
	 */
	mcms.updates.maybeRequestFilesystemCredentials = function( event ) {
		if ( mcms.updates.shouldRequestFilesystemCredentials && ! mcms.updates.ajaxLocked ) {
			mcms.updates.requestFilesystemCredentials( event );
		}
	};

	/**
	 * Keydown handler for the request for credentials modal.
	 *
	 * Closes the modal when the escape key is pressed and
	 * constrains keyboard navigation to inside the modal.
	 *
	 * @since 4.2.0
	 *
	 * @param {Event} event Event interface.
	 */
	mcms.updates.keydown = function( event ) {
		if ( 27 === event.keyCode ) {
			mcms.updates.requestForCredentialsModalCancel();
		} else if ( 9 === event.keyCode ) {

			// #upgrade button must always be the last focus-able element in the dialog.
			if ( 'upgrade' === event.target.id && ! event.shiftKey ) {
				$( '#hostname' ).focus();

				event.preventDefault();
			} else if ( 'hostname' === event.target.id && event.shiftKey ) {
				$( '#upgrade' ).focus();

				event.preventDefault();
			}
		}
	};

	/**
	 * Opens the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	mcms.updates.requestForCredentialsModalOpen = function() {
		var $modal = $( '#request-filesystem-credentials-dialog' );

		$( 'body' ).addClass( 'modal-open' );
		$modal.show();
		$modal.find( 'input:enabled:first' ).focus();
		$modal.on( 'keydown', mcms.updates.keydown );
	};

	/**
	 * Closes the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	mcms.updates.requestForCredentialsModalClose = function() {
		$( '#request-filesystem-credentials-dialog' ).hide();
		$( 'body' ).removeClass( 'modal-open' );

		if ( mcms.updates.$elToReturnFocusToFromCredentialsModal ) {
			mcms.updates.$elToReturnFocusToFromCredentialsModal.focus();
		}
	};

	/**
	 * Takes care of the steps that need to happen when the modal is canceled out.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Triggers an event for callbacks to listen to and add their actions.
	 */
	mcms.updates.requestForCredentialsModalCancel = function() {

		// Not ajaxLocked and no queue means we already have cleared things up.
		if ( ! mcms.updates.ajaxLocked && ! mcms.updates.queue.length ) {
			return;
		}

		_.each( mcms.updates.queue, function( job ) {
			$document.trigger( 'credential-modal-cancel', job );
		} );

		// Remove the lock, and clear the queue.
		mcms.updates.ajaxLocked = false;
		mcms.updates.queue = [];

		mcms.updates.requestForCredentialsModalClose();
	};

	/**
	 * Displays an error message in the request for credentials form.
	 *
	 * @since 4.2.0
	 *
	 * @param {string} message Error message.
	 */
	mcms.updates.showErrorInCredentialsForm = function( message ) {
		var $filesystemForm = $( '#request-filesystem-credentials-form' );

		// Remove any existing error.
		$filesystemForm.find( '.notice' ).remove();
		$filesystemForm.find( '#request-filesystem-credentials-title' ).after( '<div class="notice notice-alt notice-error"><p>' + message + '</p></div>' );
	};

	/**
	 * Handles credential errors and runs events that need to happen in that case.
	 *
	 * @since 4.2.0
	 *
	 * @param {object} response Ajax response.
	 * @param {string} action   The type of request to perform.
	 */
	mcms.updates.credentialError = function( response, action ) {

		// Restore callbacks.
		response = mcms.updates._addCallbacks( response, action );

		mcms.updates.queue.unshift( {
			action: action,

			/*
			 * Not cool that we're depending on response for this data.
			 * This would feel more whole in a view all tied together.
			 */
			data: response
		} );

		mcms.updates.filesystemCredentials.available = false;
		mcms.updates.showErrorInCredentialsForm( response.errorMessage );
		mcms.updates.requestFilesystemCredentials();
	};

	/**
	 * Handles credentials errors if it could not connect to the filesystem.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} maybeHandleCredentialError
	 * @param {object} response              Response from the server.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 * @param {string} action                The type of request to perform.
	 * @returns {boolean} Whether there is an error that needs to be handled or not.
	 */
	mcms.updates.maybeHandleCredentialError = function( response, action ) {
		if ( mcms.updates.shouldRequestFilesystemCredentials && response.errorCode && 'unable_to_connect_to_filesystem' === response.errorCode ) {
			mcms.updates.credentialError( response, action );
			return true;
		}

		return false;
	};

	/**
	 * Validates an AJAX response to ensure it's a proper object.
	 *
	 * If the response deems to be invalid, an admin notice is being displayed.
	 *
	 * @param {(object|string)} response              Response from the server.
	 * @param {function=}       response.always       Optional. Callback for when the Deferred is resolved or rejected.
	 * @param {string=}         response.statusText   Optional. Status message corresponding to the status code.
	 * @param {string=}         response.responseText Optional. Request response as text.
	 * @param {string}          action                Type of action the response is referring to. Can be 'delete',
	 *                                                'update' or 'install'.
	 */
	mcms.updates.isValidResponse = function( response, action ) {
		var error = mcms.updates.l10n.unknownError,
		    errorMessage;

		// Make sure the response is a valid data object and not a Promise object.
		if ( _.isObject( response ) && ! _.isFunction( response.always ) ) {
			return true;
		}

		if ( _.isString( response ) && '-1' === response ) {
			error = mcms.updates.l10n.nonceError;
		} else if ( _.isString( response ) ) {
			error = response;
		} else if ( 'undefined' !== typeof response.readyState && 0 === response.readyState ) {
			error = mcms.updates.l10n.connectionError;
		} else if ( _.isString( response.responseText ) && '' !== response.responseText ) {
			error = response.responseText;
		} else if ( _.isString( response.statusText ) ) {
			error = response.statusText;
		}

		switch ( action ) {
			case 'update':
				errorMessage = mcms.updates.l10n.updateFailed;
				break;

			case 'install':
				errorMessage = mcms.updates.l10n.installFailed;
				break;

			case 'delete':
				errorMessage = mcms.updates.l10n.deleteFailed;
				break;
		}

		// Messages are escaped, remove HTML tags to make them more readable.
		error = error.replace( /<[\/a-z][^<>]*>/gi, '' );
		errorMessage = errorMessage.replace( '%s', error );

		// Add admin notice.
		mcms.updates.addAdminNotice( {
			id:        'unknown_error',
			className: 'notice-error is-dismissible',
			message:   _.escape( errorMessage )
		} );

		// Remove the lock, and clear the queue.
		mcms.updates.ajaxLocked = false;
		mcms.updates.queue      = [];

		// Change buttons of all running updates.
		$( '.button.updating-message' )
			.removeClass( 'updating-message' )
			.removeAttr( 'aria-label' )
			.prop( 'disabled', true )
			.text( mcms.updates.l10n.updateFailedShort );

		$( '.updating-message:not(.button):not(.thickbox)' )
			.removeClass( 'updating-message notice-warning' )
			.addClass( 'notice-error' )
			.find( 'p' )
				.removeAttr( 'aria-label' )
				.text( errorMessage );

		mcms.a11y.speak( errorMessage, 'assertive' );

		return false;
	};

	/**
	 * Potentially adds an AYS to a user attempting to leave the page.
	 *
	 * If an update is on-going and a user attempts to leave the page,
	 * opens an "Are you sure?" alert.
	 *
	 * @since 4.2.0
	 */
	mcms.updates.beforeunload = function() {
		if ( mcms.updates.ajaxLocked ) {
			return mcms.updates.l10n.beforeunload;
		}
	};

	$( function() {
		var $moduleFilter        = $( '#module-filter' ),
			$bulkActionForm      = $( '#bulk-action-form' ),
			$filesystemForm      = $( '#request-filesystem-credentials-form' ),
			$filesystemModal     = $( '#request-filesystem-credentials-dialog' ),
			$moduleSearch        = $( '.modules-php .mcms-filter-search' ),
			$moduleInstallSearch = $( '.module-install-php .mcms-filter-search' );

		settings = _.extend( settings, window._mcmsUpdatesItemCounts || {} );

		if ( settings.totals ) {
			mcms.updates.refreshCount();
		}

		/*
		 * Whether a user needs to submit filesystem credentials.
		 *
		 * This is based on whether the form was output on the page server-side.
		 *
		 * @see {mcms_print_request_filesystem_credentials_modal() in PHP}
		 */
		mcms.updates.shouldRequestFilesystemCredentials = $filesystemModal.length > 0;

		/**
		 * File system credentials form submit noop-er / handler.
		 *
		 * @since 4.2.0
		 */
		$filesystemModal.on( 'submit', 'form', function( event ) {
			event.preventDefault();

			// Persist the credentials input by the user for the duration of the page load.
			mcms.updates.filesystemCredentials.ftp.hostname       = $( '#hostname' ).val();
			mcms.updates.filesystemCredentials.ftp.username       = $( '#username' ).val();
			mcms.updates.filesystemCredentials.ftp.password       = $( '#password' ).val();
			mcms.updates.filesystemCredentials.ftp.connectionType = $( 'input[name="connection_type"]:checked' ).val();
			mcms.updates.filesystemCredentials.ssh.publicKey      = $( '#public_key' ).val();
			mcms.updates.filesystemCredentials.ssh.privateKey     = $( '#private_key' ).val();
			mcms.updates.filesystemCredentials.fsNonce            = $( '#_fs_nonce' ).val();
			mcms.updates.filesystemCredentials.available          = true;

			// Unlock and invoke the queue.
			mcms.updates.ajaxLocked = false;
			mcms.updates.queueChecker();

			mcms.updates.requestForCredentialsModalClose();
		} );

		/**
		 * Closes the request credentials modal when clicking the 'Cancel' button or outside of the modal.
		 *
		 * @since 4.2.0
		 */
		$filesystemModal.on( 'click', '[data-js-action="close"], .notification-dialog-background', mcms.updates.requestForCredentialsModalCancel );

		/**
		 * Hide SSH fields when not selected.
		 *
		 * @since 4.2.0
		 */
		$filesystemForm.on( 'change', 'input[name="connection_type"]', function() {
			$( '#ssh-keys' ).toggleClass( 'hidden', ( 'ssh' !== $( this ).val() ) );
		} ).change();

		/**
		 * Handles events after the credential modal was closed.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event}  event Event interface.
		 * @param {string} job   The install/update.delete request.
		 */
		$document.on( 'credential-modal-cancel', function( event, job ) {
			var $updatingMessage = $( '.updating-message' ),
				$message, originalText;

			if ( 'import' === pagenow ) {
				$updatingMessage.removeClass( 'updating-message' );
			} else if ( 'modules' === pagenow || 'modules-network' === pagenow ) {
				if ( 'update-module' === job.action ) {
					$message = $( 'tr[data-module="' + job.data.module + '"]' ).find( '.update-message' );
				} else if ( 'delete-module' === job.action ) {
					$message = $( '[data-module="' + job.data.module + '"]' ).find( '.row-actions a.delete' );
				}
			} else if ( 'myskins' === pagenow || 'myskins-network' === pagenow ) {
				if ( 'update-myskin' === job.action ) {
					$message = $( '[data-slug="' + job.data.slug + '"]' ).find( '.update-message' );
				} else if ( 'delete-myskin' === job.action && 'myskins-network' === pagenow ) {
					$message = $( '[data-slug="' + job.data.slug + '"]' ).find( '.row-actions a.delete' );
				} else if ( 'delete-myskin' === job.action && 'myskins' === pagenow ) {
					$message = $( '.myskin-actions .delete-myskin' );
				}
			} else {
				$message = $updatingMessage;
			}

			if ( $message && $message.hasClass( 'updating-message' ) ) {
				originalText = $message.data( 'originaltext' );

				if ( 'undefined' === typeof originalText ) {
					originalText = $( '<p>' ).html( $message.find( 'p' ).data( 'originaltext' ) );
				}

				$message
					.removeClass( 'updating-message' )
					.html( originalText );

				if ( 'module-install' === pagenow || 'module-install-network' === pagenow ) {
					if ( 'update-module' === job.action ) {
						$message.attr( 'aria-label', mcms.updates.l10n.moduleUpdateNowLabel.replace( '%s', $message.data( 'name' ) ) );
					} else if ( 'install-module' === job.action ) {
						$message.attr( 'aria-label', mcms.updates.l10n.moduleInstallNowLabel.replace( '%s', $message.data( 'name' ) ) );
					}
				}
			}

			mcms.a11y.speak( mcms.updates.l10n.updateCancel, 'polite' );
		} );

		/**
		 * Click handler for module updates in List Table view.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[data-module] .update-link', function( event ) {
			var $message   = $( event.target ),
				$moduleRow = $message.parents( 'tr' );

			event.preventDefault();

			if ( $message.hasClass( 'updating-message' ) || $message.hasClass( 'button-disabled' ) ) {
				return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			// Return the user to the input box of the module's table row after closing the modal.
			mcms.updates.$elToReturnFocusToFromCredentialsModal = $moduleRow.find( '.check-column input' );
			mcms.updates.updateModule( {
				module: $moduleRow.data( 'module' ),
				slug:   $moduleRow.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for module updates in module install view.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$moduleFilter.on( 'click', '.update-now', function( event ) {
			var $button = $( event.target );
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			mcms.updates.updateModule( {
				module: $button.data( 'module' ),
				slug:   $button.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for module installs in module install view.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$moduleFilter.on( 'click', '.install-now', function( event ) {
			var $button = $( event.target );
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			if ( mcms.updates.shouldRequestFilesystemCredentials && ! mcms.updates.ajaxLocked ) {
				mcms.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function() {
					var $message = $( '.install-now.updating-message' );

					$message
						.removeClass( 'updating-message' )
						.text( mcms.updates.l10n.installNow );

					mcms.a11y.speak( mcms.updates.l10n.updateCancel, 'polite' );
				} );
			}

			mcms.updates.installModule( {
				slug: $button.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for importer modules installs in the Import screen.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.importer-item .install-now', function( event ) {
			var $button = $( event.target ),
				moduleName = $( this ).data( 'name' );

			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) ) {
				return;
			}

			if ( mcms.updates.shouldRequestFilesystemCredentials && ! mcms.updates.ajaxLocked ) {
				mcms.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function() {

					$button
						.removeClass( 'updating-message' )
						.text( mcms.updates.l10n.installNow )
						.attr( 'aria-label', mcms.updates.l10n.installNowLabel.replace( '%s', moduleName ) );

					mcms.a11y.speak( mcms.updates.l10n.updateCancel, 'polite' );
				} );
			}

			mcms.updates.installModule( {
				slug:    $button.data( 'slug' ),
				pagenow: pagenow,
				success: mcms.updates.installImporterSuccess,
				error:   mcms.updates.installImporterError
			} );
		} );

		/**
		 * Click handler for module deletions.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[data-module] a.delete', function( event ) {
			var $moduleRow = $( event.target ).parents( 'tr' );

			event.preventDefault();

			if ( ! window.confirm( mcms.updates.l10n.aysDeleteUninstall.replace( '%s', $moduleRow.find( '.module-title strong' ).text() ) ) ) {
				return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			mcms.updates.deleteModule( {
				module: $moduleRow.data( 'module' ),
				slug:   $moduleRow.data( 'slug' )
			} );

		} );

		/**
		 * Click handler for myskin updates.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.myskins-php.network-admin .update-link', function( event ) {
			var $message  = $( event.target ),
				$myskinRow = $message.parents( 'tr' );

			event.preventDefault();

			if ( $message.hasClass( 'updating-message' ) || $message.hasClass( 'button-disabled' ) ) {
				return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			// Return the user to the input box of the myskin's table row after closing the modal.
			mcms.updates.$elToReturnFocusToFromCredentialsModal = $myskinRow.find( '.check-column input' );
			mcms.updates.updateMySkin( {
				slug: $myskinRow.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for myskin deletions.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.myskins-php.network-admin a.delete', function( event ) {
			var $myskinRow = $( event.target ).parents( 'tr' );

			event.preventDefault();

			if ( ! window.confirm( mcms.updates.l10n.aysDelete.replace( '%s', $myskinRow.find( '.myskin-title strong' ).text() ) ) ) {
				return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			mcms.updates.deleteMySkin( {
				slug: $myskinRow.data( 'slug' )
			} );
		} );

		/**
		 * Bulk action handler for modules and myskins.
		 *
		 * Handles both deletions and updates.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[type="submit"]:not([name="clear-recent-list"])', function( event ) {
			var bulkAction    = $( event.target ).siblings( 'select' ).val(),
				itemsSelected = $bulkActionForm.find( 'input[name="checked[]"]:checked' ),
				success       = 0,
				error         = 0,
				errorMessages = [],
				type, action;

			// Determine which type of item we're dealing with.
			switch ( pagenow ) {
				case 'modules':
				case 'modules-network':
					type = 'module';
					break;

				case 'myskins-network':
					type = 'myskin';
					break;

				default:
					return;
			}

			// Bail if there were no items selected.
			if ( ! itemsSelected.length ) {
				event.preventDefault();
				$( 'html, body' ).animate( { scrollTop: 0 } );

				return mcms.updates.addAdminNotice( {
					id:        'no-items-selected',
					className: 'notice-error is-dismissible',
					message:   mcms.updates.l10n.noItemsSelected
				} );
			}

			// Determine the type of request we're dealing with.
			switch ( bulkAction ) {
				case 'update-selected':
					action = bulkAction.replace( 'selected', type );
					break;

				case 'delete-selected':
					if ( ! window.confirm( 'module' === type ? mcms.updates.l10n.aysBulkDelete : mcms.updates.l10n.aysBulkDeleteMySkins ) ) {
						event.preventDefault();
						return;
					}

					action = bulkAction.replace( 'selected', type );
					break;

				default:
					return;
			}

			mcms.updates.maybeRequestFilesystemCredentials( event );

			event.preventDefault();

			// Un-check the bulk checkboxes.
			$bulkActionForm.find( '.manage-column [type="checkbox"]' ).prop( 'checked', false );

			$document.trigger( 'mcms-' + type + '-bulk-' + bulkAction, itemsSelected );

			// Find all the checkboxes which have been checked.
			itemsSelected.each( function( index, element ) {
				var $checkbox = $( element ),
					$itemRow = $checkbox.parents( 'tr' );

				// Only add update-able items to the update queue.
				if ( 'update-selected' === bulkAction && ( ! $itemRow.hasClass( 'update' ) || $itemRow.find( 'notice-error' ).length ) ) {

					// Un-check the box.
					$checkbox.prop( 'checked', false );
					return;
				}

				// Add it to the queue.
				mcms.updates.queue.push( {
					action: action,
					data:   {
						module: $itemRow.data( 'module' ),
						slug:   $itemRow.data( 'slug' )
					}
				} );
			} );

			// Display bulk notification for updates of any kind.
			$document.on( 'mcms-module-update-success mcms-module-update-error mcms-myskin-update-success mcms-myskin-update-error', function( event, response ) {
				var $itemRow = $( '[data-slug="' + response.slug + '"]' ),
					$bulkActionNotice, itemName;

				if ( 'mcms-' + response.update + '-update-success' === event.type ) {
					success++;
				} else {
					itemName = response.moduleName ? response.moduleName : $itemRow.find( '.column-primary strong' ).text();

					error++;
					errorMessages.push( itemName + ': ' + response.errorMessage );
				}

				$itemRow.find( 'input[name="checked[]"]:checked' ).prop( 'checked', false );

				mcms.updates.adminNotice = mcms.template( 'mcms-bulk-updates-admin-notice' );

				mcms.updates.addAdminNotice( {
					id:            'bulk-action-notice',
					className:     'bulk-action-notice',
					successes:     success,
					errors:        error,
					errorMessages: errorMessages,
					type:          response.update
				} );

				$bulkActionNotice = $( '#bulk-action-notice' ).on( 'click', 'button', function() {
					// $( this ) is the clicked button, no need to get it again.
					$( this )
						.toggleClass( 'bulk-action-errors-collapsed' )
						.attr( 'aria-expanded', ! $( this ).hasClass( 'bulk-action-errors-collapsed' ) );
					// Show the errors list.
					$bulkActionNotice.find( '.bulk-action-errors' ).toggleClass( 'hidden' );
				} );

				if ( error > 0 && ! mcms.updates.queue.length ) {
					$( 'html, body' ).animate( { scrollTop: 0 } );
				}
			} );

			// Reset admin notice template after #bulk-action-notice was added.
			$document.on( 'mcms-updates-notice-added', function() {
				mcms.updates.adminNotice = mcms.template( 'mcms-updates-admin-notice' );
			} );

			// Check the queue, now that the event handlers have been added.
			mcms.updates.queueChecker();
		} );

		if ( $moduleInstallSearch.length ) {
			$moduleInstallSearch.attr( 'aria-describedby', 'live-search-desc' );
		}

		/**
		 * Handles changes to the module search box on the new-module page,
		 * searching the repository dynamically.
		 *
		 * @since 4.6.0
		 */
		$moduleInstallSearch.on( 'keyup input', _.debounce( function( event, eventtype ) {
			var $searchTab = $( '.module-install-search' ), data, searchLocation;

			data = {
				_ajax_nonce: mcms.updates.ajaxNonce,
				s:           event.target.value,
				tab:         'search',
				type:        $( '#typeselector' ).val(),
				pagenow:     pagenow
			};
			searchLocation = location.href.split( '?' )[ 0 ] + '?' + $.param( _.omit( data, [ '_ajax_nonce', 'pagenow' ] ) );

			// Clear on escape.
			if ( 'keyup' === event.type && 27 === event.which ) {
				event.target.value = '';
			}

			if ( mcms.updates.searchTerm === data.s && 'typechange' !== eventtype ) {
				return;
			} else {
				$moduleFilter.empty();
				mcms.updates.searchTerm = data.s;
			}

			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', searchLocation );
			}

			if ( ! $searchTab.length ) {
				$searchTab = $( '<li class="module-install-search" />' )
					.append( $( '<a />', {
						'class': 'current',
						'href': searchLocation,
						'text': mcms.updates.l10n.searchResultsLabel
					} ) );

				$( '.mcms-filter .filter-links .current' )
					.removeClass( 'current' )
					.parents( '.filter-links' )
					.prepend( $searchTab );

				$moduleFilter.prev( 'p' ).remove();
				$( '.modules-popular-tags-wrapper' ).remove();
			}

			if ( 'undefined' !== typeof mcms.updates.searchRequest ) {
				mcms.updates.searchRequest.abort();
			}
			$( 'body' ).addClass( 'loading-content' );

			mcms.updates.searchRequest = mcms.ajax.post( 'search-install-modules', data ).done( function( response ) {
				$( 'body' ).removeClass( 'loading-content' );
				$moduleFilter.append( response.items );
				delete mcms.updates.searchRequest;

				if ( 0 === response.count ) {
					mcms.a11y.speak( mcms.updates.l10n.noModulesFound );
				} else {
					mcms.a11y.speak( mcms.updates.l10n.modulesFound.replace( '%d', response.count ) );
				}
			} );
		}, 500 ) );

		if ( $moduleSearch.length ) {
			$moduleSearch.attr( 'aria-describedby', 'live-search-desc' );
		}

		/**
		 * Handles changes to the module search box on the Installed Modules screen,
		 * searching the module list dynamically.
		 *
		 * @since 4.6.0
		 */
		$moduleSearch.on( 'keyup input', _.debounce( function( event ) {
			var data = {
				_ajax_nonce:   mcms.updates.ajaxNonce,
				s:             event.target.value,
				pagenow:       pagenow,
				module_status: 'all'
			},
			queryArgs;

			// Clear on escape.
			if ( 'keyup' === event.type && 27 === event.which ) {
				event.target.value = '';
			}

			if ( mcms.updates.searchTerm === data.s ) {
				return;
			} else {
				mcms.updates.searchTerm = data.s;
			}

			queryArgs = _.object( _.compact( _.map( location.search.slice( 1 ).split( '&' ), function( item ) {
				if ( item ) return item.split( '=' );
			} ) ) );

			data.module_status = queryArgs.module_status || 'all';

			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', location.href.split( '?' )[ 0 ] + '?s=' + data.s + '&module_status=' + data.module_status );
			}

			if ( 'undefined' !== typeof mcms.updates.searchRequest ) {
				mcms.updates.searchRequest.abort();
			}

			$bulkActionForm.empty();
			$( 'body' ).addClass( 'loading-content' );
			$( '.subsubsub .current' ).removeClass( 'current' );

			mcms.updates.searchRequest = mcms.ajax.post( 'search-modules', data ).done( function( response ) {

				// Can we just ditch this whole subtitle business?
				var $subTitle    = $( '<span />' ).addClass( 'subtitle' ).html( mcms.updates.l10n.searchResults.replace( '%s', _.escape( data.s ) ) ),
					$oldSubTitle = $( '.wrap .subtitle' );

				if ( ! data.s.length ) {
					$oldSubTitle.remove();
					$( '.subsubsub .' + data.module_status + ' a' ).addClass( 'current' );
				} else if ( $oldSubTitle.length ) {
					$oldSubTitle.replaceWith( $subTitle );
				} else {
					$( '.mcms-header-end' ).before( $subTitle );
				}

				$( 'body' ).removeClass( 'loading-content' );
				$bulkActionForm.append( response.items );
				delete mcms.updates.searchRequest;

				if ( 0 === response.count ) {
					mcms.a11y.speak( mcms.updates.l10n.noModulesFound );
				} else {
					mcms.a11y.speak( mcms.updates.l10n.modulesFound.replace( '%d', response.count ) );
				}
			} );
		}, 500 ) );

		/**
		 * Trigger a search event when the search form gets submitted.
		 *
		 * @since 4.6.0
		 */
		$document.on( 'submit', '.search-modules', function( event ) {
			event.preventDefault();

			$( 'input.mcms-filter-search' ).trigger( 'input' );
		} );

		/** 
		 * Trigger a search event when the "Try Again" button is clicked. 
		 * 
		 * @since 4.9.0
		 */ 
		$document.on( 'click', '.try-again', function( event ) { 
			event.preventDefault(); 
			$moduleInstallSearch.trigger( 'input' ); 
		} );

		/**
		 * Trigger a search event when the search type gets changed.
		 *
		 * @since 4.6.0
		 */
		$( '#typeselector' ).on( 'change', function() {
			var $search = $( 'input[name="s"]' );

			if ( $search.val().length ) {
				$search.trigger( 'input', 'typechange' );
			}
		} );

		/**
		 * Click handler for updating a module from the details modal on `module-install.php`.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$( '#module_update_from_iframe' ).on( 'click', function( event ) {
			var target = window.parent === window ? null : window.parent,
				update;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target || -1 !== window.parent.location.pathname.indexOf( 'update-core.php' ) ) {
				return;
			}

			event.preventDefault();

			update = {
				action: 'update-module',
				data:   {
					module: $( this ).data( 'module' ),
					slug:   $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( update ), window.location.origin );
		} );

		/**
		 * Click handler for installing a module from the details modal on `module-install.php`.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$( '#module_install_from_iframe' ).on( 'click', function( event ) {
			var target = window.parent === window ? null : window.parent,
				install;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target || -1 !== window.parent.location.pathname.indexOf( 'index.php' ) ) {
				return;
			}

			event.preventDefault();

			install = {
				action: 'install-module',
				data:   {
					slug: $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( install ), window.location.origin );
		} );

		/**
		 * Handles postMessage events.
		 *
		 * @since 4.2.0
		 * @since 4.6.0 Switched `update-module` action to use the queue.
		 *
		 * @param {Event} event Event interface.
		 */
		$( window ).on( 'message', function( event ) {
			var originalEvent  = event.originalEvent,
				expectedOrigin = document.location.protocol + '//' + document.location.hostname,
				message;

			if ( originalEvent.origin !== expectedOrigin ) {
				return;
			}

			try {
				message = $.parseJSON( originalEvent.data );
			} catch ( e ) {
				return;
			}

			if ( ! message || 'undefined' === typeof message.action ) {
				return;
			}

			switch ( message.action ) {

				// Called from `mcms-admin/includes/class-mcms-upgrader-skins.php`.
				case 'decrementUpdateCount':
					/** @property {string} message.upgradeType */
					mcms.updates.decrementCount( message.upgradeType );
					break;

				case 'install-module':
				case 'update-module':
					/* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
					window.tb_remove();
					/* jscs:enable */

					message.data = mcms.updates._addCallbacks( message.data, message.action );

					mcms.updates.queue.push( message );
					mcms.updates.queueChecker();
					break;
			}
		} );

		/**
		 * Adds a callback to display a warning before leaving the page.
		 *
		 * @since 4.2.0
		 */
		$( window ).on( 'beforeunload', mcms.updates.beforeunload );
	} );
})( jQuery, window.mcms, window._mcmsUpdatesSettings );
