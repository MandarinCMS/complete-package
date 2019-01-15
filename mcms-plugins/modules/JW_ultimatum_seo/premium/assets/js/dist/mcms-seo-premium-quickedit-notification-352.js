(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

/* global ajaxurl */
/* jshint -W097 */

var redirectFunctions = require("./redirects/functions");

/**
 * Use notification counter so we can count how many times the function mcmsseo_show_notification is called.
 *
 * @type {number}
 */
var mcmsseo_notification_counter = 0;

/**
 * Show notification to user when there's a redirect created. When the response is empty, up the notification counter with 1, wait 100 ms and call function again.
 * Stop when the notification counter is bigger than 20.
 *
 * @returns {void}
 */
function mcmsseo_show_notification() {
	jQuery.post(ajaxurl, { action: "ultimatum_get_notifications" }, function (response) {
		if (response !== "") {
			var insertAfterElement = jQuery(".wrap").children().eq(0);
			jQuery(response).insertAfter(insertAfterElement);
			mcmsseo_notification_counter = 0;
		}

		if (mcmsseo_notification_counter < 20 && response === "") {
			mcmsseo_notification_counter++;
			setTimeout(mcmsseo_show_notification, 500);
		}
	});
}

window.mcmsseo_show_notification = mcmsseo_show_notification;

/**
 * Gets the current page based on the current URL.
 *
 * @returns {string} The current page.
 */
function mcmsseo_get_current_page() {
	return jQuery(location).attr("pathname").split("/").pop();
}

window.mcmsseo_get_current_page = mcmsseo_get_current_page;

/**
 * Gets the current slug of a post based on the current page and post or term being edited.
 *
 * @returns {string} The slug of the current post or term.
 */
function mcmsseo_get_current_slug() {
	var currentPost = mcmsseo_get_item_id();
	var currentPage = mcmsseo_get_current_page();

	if (currentPage === "edit.php") {
		return jQuery("#inline_" + currentPost).find(".post_name").html();
	}

	if (currentPage === "edit-tags.php") {
		return jQuery("#inline_" + currentPost).find(".slug").html();
	}

	return "";
}

window.mcmsseo_get_current_slug = mcmsseo_get_current_slug;

/**
 * Checks whether or not the slug has changed.
 *
 * @returns {boolean} Whether or not the slug has changed.
 */
function mcmsseo_slug_changed() {
	var editor = mcmsseo_get_active_editor();
	var currentSlug = mcmsseo_get_current_slug();
	var mcmsseo_new_slug = editor.find("input[name=post_name]").val();

	return currentSlug !== mcmsseo_new_slug;
}

window.mcmsseo_slug_changed = mcmsseo_slug_changed;

/**
 * Gets the currently active editor used in quick edit.
 *
 * @returns {Object} The editor that is currently active.
 */
function mcmsseo_get_active_editor() {
	return jQuery("tr.inline-editor");
}

window.mcmsseo_get_active_editor = mcmsseo_get_active_editor;

/**
 * Gets the current post or term id.
 * Returns an empty string if no editor is currently active.
 *
 * @returns {string} The ID of the current post or term.
 */
function mcmsseo_get_item_id() {
	var editor = mcmsseo_get_active_editor();

	if (editor === "") {
		return "";
	}

	return editor.attr("id").replace("edit-", "");
}

window.mcmsseo_get_item_id = mcmsseo_get_item_id;

/**
 * Handles the key-based events in the quick edit editor.
 *
 * @param {Event} ev The event currently being executed.
 *
 * @returns {void}
 */
function mcmsseo_handle_key_events(ev) {
	// 13 refers to the enter key.
	if (ev.which === 13 && mcmsseo_slug_changed()) {
		mcmsseo_show_notification();
	}
}

window.mcmsseo_handle_key_events = mcmsseo_handle_key_events;

/**
 * Handles the button-based events in the quick edit editor.
 *
 * @param {Event} ev The event currently being executed.
 *
 * @returns {void}
 */
function mcmsseo_handle_button_events(ev) {
	if (jQuery(ev.target).attr("id") !== "save-order" && mcmsseo_slug_changed()) {
		mcmsseo_show_notification();
	}
}

window.mcmsseo_handle_button_events = mcmsseo_handle_button_events;

window.mcmsseo_undo_redirect = redirectFunctions.mcmsseo_undo_redirect;
window.mcmsseo_create_redirect = redirectFunctions.mcmsseo_create_redirect;

jQuery(function () {
	var mcmsseoCurrentPage = mcmsseo_get_current_page();

	// If current page is edit*.php, continue execution.
	if (mcmsseoCurrentPage === "edit.php" || mcmsseoCurrentPage === "edit-tags.php") {
		jQuery("#inline-edit input").on("keydown", function (ev) {
			mcmsseo_handle_key_events(ev);
		});

		jQuery(".button-primary").click(function (ev) {
			mcmsseo_handle_button_events(ev);
		});
	}
});

},{"./redirects/functions":2}],2:[function(require,module,exports){
"use strict";

/* global mcmsseo_premium_strings, ajaxurl */

/**
 * Undoes a redirect.
 *
 * @param {string} origin The redirect's origin.
 * @param {string} target The redirect's target.
 * @param {string} type The type of redirect.
 * @param {string} nonce The nonce being used to validate the current AJAX request.
 * @param {object} source The DOMElement containing the alerts.
 *
 * @returns {void}
 */
function mcmsseo_undo_redirect(origin, target, type, nonce, source) {
	jQuery.post(ajaxurl, {
		action: "mcmsseo_delete_redirect_plain",
		ajax_nonce: nonce,
		redirect: {
			origin: origin,
			target: target,
			type: type
		}
	}, function () {
		jQuery(source).closest(".ultimatum-alert").fadeOut("slow");
	});
}

/**
 * Creates a redirect
 *
 * @param {string} origin The origin.
 * @param {string} type   The redirect type, regex or plain.
 * @param {string} nonce  The nonce.
 * @param {object} source The source of the redirect.
 *
 * @returns {void}
 */
function mcmsseo_create_redirect(origin, type, nonce, source) {
	var target = "";

	if (parseInt(type, 10) !== 410) {
		/* eslint-disable no-alert */
		target = window.prompt(mcmsseo_premium_strings.enter_new_url.replace("%s", origin));
		/* eslint-enable no-alert */

		if (target === '') {
			/* eslint-disable no-alert */
			window.alert(mcmsseo_premium_strings.error_new_url);
			/* eslint-enable no-alert */
			return;
		}
	}

	jQuery.post(ajaxurl, {
		action: "mcmsseo_add_redirect_plain",
		ajax_nonce: nonce,
		redirect: {
			origin: origin,
			target: target,
			type: type
		}
	}, function (response) {
		var notice = jQuery(source).closest(".ultimatum-alert");
		// Remove the classes first.
		jQuery(notice).removeClass("updated").removeClass("error");

		// Remove possibly added redirect errors.
		jQuery(notice).find(".redirect_error").remove();

		if (response.error) {
			// Add paragraph on top of the notice with actions and set class to error.
			jQuery(notice).addClass("error").prepend("<p class=\"redirect_error\">" + response.error.message + "</p>");

			return;
		}

		// Parse the success message.
		var successMessage = "";
		if (parseInt(type, 10) === 410) {
			successMessage = mcmsseo_premium_strings.redirect_saved_no_target;
		} else {
			successMessage = mcmsseo_premium_strings.redirect_saved.replace("%2$s", "<code>" + response.target + "</code>");
		}

		successMessage = successMessage.replace("%1$s", "<code>" + response.origin + "</code>");

		// Set class to updated and replace html with the success message.
		jQuery(notice).addClass("updated").html("<p>" + successMessage + "</p>");
	}, "json");
}

module.exports = {
	mcmsseo_create_redirect: mcmsseo_create_redirect,
	mcmsseo_undo_redirect: mcmsseo_undo_redirect
};

},{}]},{},[1]);
