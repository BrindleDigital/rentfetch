(function (window, document, $) {
	'use strict';

	function initRentfetchApiResponseEditors(context) {
		if (typeof wp === 'undefined' || !wp.codeEditor) {
			return;
		}

		jQuery(context || document)
			.find('.rentfetch-api-response-json')
			.each(function () {
			var $textarea = jQuery(this);

			if ($textarea.data('rentfetchCodeEditorInitialized')) {
				return;
			}
			$textarea.data('rentfetchCodeEditorInitialized', true);

			var settings = {};

			// Prefer settings provided by WP via localized object; fallback to defaultSettings.
			if (typeof rentfetchCodeEditorSettings !== 'undefined') {
				settings = _.clone(rentfetchCodeEditorSettings);
			} else if (wp.codeEditor && wp.codeEditor.defaultSettings) {
				settings = _.clone(wp.codeEditor.defaultSettings);
			}

			settings.codemirror = settings.codemirror || {};
			settings.codemirror.mode = 'application/json';
			settings.codemirror.lineNumbers = true;
			settings.codemirror.lineWrapping = false; // Prevent line wrapping for long JSON lines
			settings.codemirror.foldGutter = true;
			settings.codemirror.lint = false; // Disable linting to avoid parse error messages
			settings.codemirror.gutters = (
				settings.codemirror.gutters || []
			).concat(['CodeMirror-foldgutter']);

				wp.codeEditor.initialize($textarea[0], settings);
			});
	}

	// Initialize only editors already visible on load, then lazy diagnostics.
	jQuery(document).ready(function () {
		initRentfetchApiResponseEditors(document);
	});
	jQuery(document).on(
		'postbox-added postbox-removed',
		function () {
			initRentfetchApiResponseEditors(document);
		}
	);
	document.addEventListener(
		'rentfetch:property-tab-content-loaded',
		function (event) {
			if (event.detail && event.detail.panel) {
				initRentfetchApiResponseEditors(event.detail.panel);
			}
		}
	);
})(window, document, jQuery);
