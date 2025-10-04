jQuery(document).ready(function ($) {
	// Initialize CodeMirror for JSON editing
	if (
		typeof wp !== 'undefined' &&
		wp.codeEditor &&
		rentfetchCodeEditorSettings
	) {
		var textarea = document.querySelector('.rentfetch-fees-json');
		if (textarea) {
			wp.codeEditor.initialize(textarea, rentfetchCodeEditorSettings);
		}
	}

	// Handle copy to clipboard button
	document.getElementById('copy-fees-json').addEventListener(
		'click',
		function (e) {
			e.preventDefault();

			var button = this;
			var originalText = button.textContent;
			var textarea = document.querySelector('.rentfetch-fees-json');
			if (!textarea) {
				showTemporaryButtonText(button, 'Error: JSON not found', originalText);
				return;
			}

			var textToCopy = textarea.value;

			// Try modern clipboard API first
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(textToCopy).then(
					function () {
						showTemporaryButtonText(button, 'Fees copied', originalText);
					},
					function (err) {
						console.error('Failed to copy: ', err);
						fallbackCopyTextToClipboard(textToCopy, button, originalText);
					}
				);
			} else {
				// Fallback for older browsers or non-HTTPS
				fallbackCopyTextToClipboard(textToCopy, button, originalText);
			}
		},
		{ passive: true }
	);

	// Helper function to temporarily change button text
	function showTemporaryButtonText(button, newText, originalText) {
		button.textContent = newText;
		setTimeout(function () {
			button.textContent = originalText;
		}, 2000);
	}

	// Fallback function for older browsers
	function fallbackCopyTextToClipboard(text, button, originalText) {
		var textArea = document.createElement('textarea');
		textArea.value = text;

		// Avoid scrolling to bottom
		textArea.style.top = '0';
		textArea.style.left = '0';
		textArea.style.position = 'fixed';
		textArea.style.opacity = '0';

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			if (successful) {
				showTemporaryButtonText(button, 'Fees copied', originalText);
			} else {
				showTemporaryButtonText(button, 'Copy failed', originalText);
			}
		} catch (err) {
			console.error('Fallback copy failed: ', err);
			showTemporaryButtonText(button, 'Copy failed', originalText);
		}

		document.body.removeChild(textArea);
	}
});
