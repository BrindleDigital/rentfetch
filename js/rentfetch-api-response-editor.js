(function (window, document, $) {
	'use strict';

	function initRentfetchApiResponseEditors() {
		if (typeof wp === 'undefined' || !wp.codeEditor) {
			return;
		}

		jQuery('.rentfetch-api-response-json').each(function () {
			var $textarea = jQuery(this);

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

	// Initialize on document ready and when new metaboxes are added (AJAX)
	jQuery(document).ready(initRentfetchApiResponseEditors);
	jQuery(document).on(
		'postbox-added postbox-removed',
		initRentfetchApiResponseEditors
	);
})(window, document, jQuery);
