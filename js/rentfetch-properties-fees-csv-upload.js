// Override addEventListener to make scroll-blocking events passive by default
(function () {
	var originalAddEventListener = EventTarget.prototype.addEventListener;
	EventTarget.prototype.addEventListener = function (
		type,
		listener,
		options
	) {
		// Events that can block scrolling and should be passive
		var passiveEvents = [
			'touchstart',
			'touchmove',
			'touchend',
			'touchcancel',
			'wheel',
			'mousewheel',
			'scroll',
		];

		// If this is a scroll-blocking event and no options are specified, make it passive
		if (
			passiveEvents.indexOf(type) !== -1 &&
			(options === undefined || options === null)
		) {
			options = { passive: true };
		}
		// If options is an object and passive is not specified, set it for scroll-blocking events
		else if (
			passiveEvents.indexOf(type) !== -1 &&
			typeof options === 'object' &&
			options.passive === undefined
		) {
			options.passive = true;
		}
		// If options is just a boolean (capture), convert to object and add passive
		else if (
			passiveEvents.indexOf(type) !== -1 &&
			typeof options === 'boolean'
		) {
			options = { capture: options, passive: true };
		}

		return originalAddEventListener.call(this, type, listener, options);
	};
})();

jQuery(document).ready(function ($) {
	var fileInput = document.getElementById('property_fees_csv');
	if (!fileInput) {
		return;
	}

	// Handle CSV file upload
	fileInput.addEventListener(
		'change',
		function (e) {
			var file = e.target.files[0];
			if (!file) {
				return;
			}

			// Check if it's a CSV file
			if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
				alert('Please select a valid CSV file.');
				return;
			}

			var postId = $('#property_fees_csv').data('post-id');
			var formData = new FormData();
			formData.append('action', 'rentfetch_upload_fees_csv');
			formData.append(
				'nonce',
				$('#rentfetch_properties_metabox_nonce').val()
			);
			formData.append('post_id', postId);
			formData.append('csv_file', file);

			// Show loading state
			$(this).prop('disabled', true);
			$(this).after(
				'<span class="csv-upload-loading"> Processing CSV...</span>'
			);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.success) {
						// Update the JSON textarea
						$('.rentfetch-fees-json').val(response.data.json);

						// Re-initialize CodeMirror if it exists
						if (wp.codeEditor) {
							var editor = $('.rentfetch-fees-json').next(
								'.CodeMirror'
							);
							if (editor.length) {
								editor[0].CodeMirror.setValue(
									response.data.json
								);
							}
						}
					} else {
						alert('Error processing CSV: ' + response.data.message);
					}
				},
				error: function (xhr, status, error) {
					alert('Error uploading CSV file.');
				},
				complete: function () {
					// Remove loading state
					$('#property_fees_csv').prop('disabled', false);
					$('.csv-upload-loading').remove();
					// Clear the file input
					$('#property_fees_csv').val('');
				},
			});
		},
		{ passive: true }
	);
});
