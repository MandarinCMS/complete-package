/**
 * Distraction-Free Writing (mcms-fullscreen) backward compatibility stub.
 * Todo: remove at the end of 2016.
 *
 * Original was deprecated in 4.1, removed in 4.3.
 */
( function() {
	var noop = function(){};

	window.mcms = window.mcms || {};
	window.mcms.editor = window.mcms.editor || {};
	window.mcms.editor.fullscreen = {
		bind_resize: noop,
		dfwWidth: noop,
		off: noop,
		on: noop,
		refreshButtons: noop,
		resizeTextarea: noop,
		save: noop,
		switchmode: noop,
		toggleUI: noop,

		settings: {},
		pubsub: {
			publish: noop,
			subscribe: noop,
			unsubscribe: noop,
			topics: {}
		},
		fade: {
			In: noop,
			Out: noop
		},
		ui: {
			fade: noop,
			init: noop
		}
	};
}());
