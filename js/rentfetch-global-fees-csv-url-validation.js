/**
 * CSV URL Validation for Global Property Fees
 * Validates the CSV URL on page load and when the URL changes
 */
jQuery(document).ready(function ($) {
	var $urlInput = $('#rentfetch_options_global_property_fees_csv_url');
	var $statusDiv = $('#global-csv-url-validation-status');
	var validationTimeout = null;

	// Don't run if elements don't exist
	if (!$urlInput.length || !$statusDiv.length) {
		return;
	}

	/**
	 * Show validation status with appropriate styling
	 */
	function showStatus(type, message, details) {
		var icon, bgColor, borderColor, textColor;

		switch (type) {
			case 'success':
				icon = '✓';
				bgColor = '#d4edda';
				borderColor = '#c3e6cb';
				textColor = '#28a745';
				break;
			case 'warning':
				icon = '⚠';
				bgColor = 'rgba(255, 193, 7, 0.2)';
				borderColor = 'rgba(255, 193, 7, 0.3)';
				textColor = '#856404';
				break;
			case 'error':
				icon = '✕';
				bgColor = '#f8d7da';
				borderColor = '#f5c6cb';
				textColor = '#dc3545';
				break;
			case 'loading':
				icon = '⟳';
				bgColor = '#e2e3e5';
				borderColor = '#d6d8db';
				textColor = '#383d41';
				break;
			default:
				icon = 'ℹ';
				bgColor = '#d1ecf1';
				borderColor = '#bee5eb';
				textColor = '#0c5460';
		}

		var html =
			'<div style="padding: 8px 12px; background: ' +
			bgColor +
			'; border: 1px solid ' +
			borderColor +
			'; border-radius: 4px; color: ' +
			textColor +
			'; font-size: 13px;">';
		html += '<strong>' + icon + ' ' + message + '</strong>';

		if (details && details.length > 0) {
			html += '<ul style="margin: 8px 0 0 20px; padding: 0;">';
			for (var i = 0; i < details.length; i++) {
				html += '<li>' + details[i] + '</li>';
			}
			html += '</ul>';
		}

		html += '</div>';

		$statusDiv.html(html);
	}

	/**
	 * Clear validation status
	 */
	function clearStatus() {
		$statusDiv.empty();
	}

	/**
	 * Validate the CSV URL
	 */
	function validateCsvUrl() {
		var url = $urlInput.val().trim();

		// Clear status if URL is empty
		if (!url) {
			clearStatus();
			return;
		}

		// Basic URL validation
		if (!url.match(/^https?:\/\//i)) {
			showStatus('error', 'Invalid URL format', [
				'URL must start with http:// or https://',
			]);
			return;
		}

		// Show loading state
		showStatus('loading', 'Validating CSV file...');

		// Make AJAX request to validate
		$.ajax({
			url: rentfetchGlobalCsvValidation.ajaxurl,
			type: 'POST',
			data: {
				action: 'rentfetch_validate_fees_csv_url',
				nonce: rentfetchGlobalCsvValidation.nonce,
				url: url,
			},
			success: function (response) {
				if (response.success) {
					var data = response.data;

					if (data.valid) {
						// Valid CSV
						var allDetails = [];

						// Add success info - format found columns with empty column note
						var columnsDisplay = data.found_columns.join(', ');
						if (
							data.empty_column_count &&
							data.empty_column_count > 0
						) {
							columnsDisplay +=
								' (and ' +
								data.empty_column_count +
								' empty column' +
								(data.empty_column_count > 1 ? 's' : '') +
								')';
						}
						allDetails.push('Found columns: ' + columnsDisplay);
						allDetails.push('Data rows: ' + data.row_count);

						// Add any warnings
						if (data.warnings && data.warnings.length > 0) {
							for (var i = 0; i < data.warnings.length; i++) {
								allDetails.push('⚠ ' + data.warnings[i]);
							}
						}

						if (data.warnings && data.warnings.length > 0) {
							showStatus(
								'warning',
								data.success_message,
								allDetails
							);
						} else {
							showStatus(
								'success',
								data.success_message,
								allDetails
							);
						}
					} else {
						// Invalid CSV
						var errorDetails = [];

						// Add error messages
						if (data.messages && data.messages.length > 0) {
							for (var i = 0; i < data.messages.length; i++) {
								errorDetails.push(data.messages[i]);
							}
						}

						// Show what was found - format with empty column note
						if (
							data.found_columns &&
							data.found_columns.length > 0
						) {
							var columnsDisplay = data.found_columns.join(', ');
							if (
								data.empty_column_count &&
								data.empty_column_count > 0
							) {
								columnsDisplay +=
									' (and ' +
									data.empty_column_count +
									' empty column' +
									(data.empty_column_count > 1 ? 's' : '') +
									')';
							}
							errorDetails.push(
								'Found columns: ' + columnsDisplay
							);
						}

						// Show expected format
						errorDetails.push(
							'Expected columns: ' +
								data.expected_columns.join(', ')
						);

						showStatus(
							'error',
							'CSV validation failed',
							errorDetails
						);
					}
				} else {
					// Error response
					showStatus('error', 'Validation error', [
						response.data.message || 'Could not validate CSV file',
					]);
				}
			},
			error: function (xhr, status, error) {
				showStatus('error', 'Request failed', [
					'Could not connect to server to validate CSV',
					error,
				]);
			},
		});
	}

	// Validate on page load if URL exists
	if ($urlInput.val().trim()) {
		validateCsvUrl();
	}

	// Validate on URL change with debounce
	$urlInput.on('input change paste', function () {
		// Clear any pending validation
		if (validationTimeout) {
			clearTimeout(validationTimeout);
		}

		var url = $urlInput.val().trim();

		if (!url) {
			clearStatus();
			return;
		}

		// Show loading immediately for paste events
		showStatus('loading', 'Validating CSV file...');

		// Debounce the actual validation
		validationTimeout = setTimeout(function () {
			validateCsvUrl();
		}, 500);
	});

	// Also trigger validation after file upload completes
	$(document).on('rentfetch_global_csv_uploaded', function () {
		validateCsvUrl();
	});
});
