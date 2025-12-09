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
	// Handle CSV file upload
	document
		.getElementById('rentfetch_options_global_property_fees_csv')
		.addEventListener(
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

				var formData = new FormData();
				formData.append('action', 'upload_global_property_fees_csv');
				formData.append(
					'_wpnonce',
					$('#rentfetch_main_options_nonce_field').val()
				);
				formData.append('file', file);

				// Show loading state
				$(this).prop('disabled', true);
				$(this).after(
					'<span class="csv-upload-loading"> Uploading CSV...</span>'
				);

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (response) {
						if (response.success) {
							// Set the URL in the input field
							$(
								'#rentfetch_options_global_property_fees_csv_url'
							).val(response.data.url);
						} else {
							alert(
								'Error uploading CSV: ' + response.data.message
							);
						}
					},
					error: function (xhr, status, error) {
						alert('Error uploading CSV file.');
					},
					complete: function () {
						// Remove loading state
						$('#rentfetch_options_global_property_fees_csv').prop(
							'disabled',
							false
						);
						$('.csv-upload-loading').remove();
						// Clear the file input
						$('#rentfetch_options_global_property_fees_csv').val(
							''
						);
					},
				});
			},
			{ passive: true }
		);
});
