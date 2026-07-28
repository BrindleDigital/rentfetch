(function (window, document, $) {
	'use strict';

	function initFees(panel) {
		var $panel = $(panel);
		var $refreshButton = $panel.find('#refresh-monthly-required-fees-now');
		var $refreshStatus = $panel.find('#monthly-required-fees-refresh-status');
		var $totalField = $panel.find('#property_monthly_required_total_fees');
		var $csvUrlField = $panel.find('#property_fees_csv_url');
		var $uploadButton = $panel.find('#property_fees_csv_upload_btn');

		if ($panel.data('rfFeesInitialized')) {
			return;
		}

		$panel.data('rfFeesInitialized', true);

		if ($uploadButton.length && window.wp && wp.media) {
			var csvMediaFrame;

			$uploadButton.on('click', function (event) {
				event.preventDefault();

				if (csvMediaFrame) {
					csvMediaFrame.open();
					return;
				}

				csvMediaFrame = wp.media({
					title: 'Select or Upload CSV File',
					button: { text: 'Use this CSV' },
					multiple: false,
					library: {
						type: [
							'text/csv',
							'application/vnd.ms-excel',
							'text/plain',
						],
					},
				});

				csvMediaFrame.on('select', function () {
					var attachment = csvMediaFrame
						.state()
						.get('selection')
						.first()
						.toJSON();

					$csvUrlField.val(attachment.url).trigger('change');
					$(document).trigger('rentfetch_csv_uploaded');
				});

				csvMediaFrame.open();
			});
		}

		if (!$refreshButton.length) {
			return;
		}

		var lastParsedCsvUrl = ($csvUrlField.val() || '').trim();
		var autoParseTimeout = null;
		var pendingValidationParseUrl = '';
		var pendingValidationSourceUrl = '';
		var autoParseInFlight = false;
		var autoParseInFlightUrl = '';

		function setStatus(message, isError) {
			$refreshStatus
				.text(message)
				.css('color', isError ? '#b32d2e' : '#1d7f2f');
		}

		function refreshFromCsv(reloadAfter, statusMessage, sourceCsvUrl) {
			var csvUrl = ($csvUrlField.val() || '').trim();

			sourceCsvUrl =
				'string' === typeof sourceCsvUrl
					? sourceCsvUrl.trim()
					: csvUrl;

			if (
				!reloadAfter &&
				autoParseInFlight &&
				autoParseInFlightUrl === csvUrl
			) {
				return;
			}

			if (!reloadAfter) {
				autoParseInFlight = true;
				autoParseInFlightUrl = csvUrl;
			}

			$refreshButton.prop('disabled', true);
			setStatus(statusMessage || 'Refreshing from CSV…', false);

			$.ajax({
				url: window.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'rentfetch_refresh_monthly_required_fees_now',
					post_id: $refreshButton.data('post-id'),
					nonce: $refreshButton.data('nonce'),
					csv_url: csvUrl,
					source_csv_url: sourceCsvUrl,
				},
			})
				.done(function (response) {
					if (!response || !response.success) {
						setStatus(
							response &&
								response.data &&
								response.data.message
								? response.data.message
								: 'Refresh failed.',
							true
						);
						return;
					}

					if (response.data && response.data.stale) {
						return;
					}

					if (
						response.data &&
						'undefined' !== typeof response.data.total
					) {
						$totalField.val(response.data.total || '');
					}

					var successMessage =
						response.data && response.data.message
							? response.data.message
							: 'Refresh complete.';

					if (reloadAfter) {
						setStatus(successMessage + ' Reloading…', false);
						window.setTimeout(function () {
							window.location.reload();
						}, 500);
					} else {
						setStatus(successMessage, false);
					}
				})
				.fail(function () {
					setStatus('Request failed while refreshing fees.', true);
				})
				.always(function () {
					$refreshButton.prop('disabled', false);
					if (!reloadAfter) {
						autoParseInFlight = false;
						autoParseInFlightUrl = '';
					}
				});
		}

		$refreshButton.on('click', function (event) {
			event.preventDefault();
			pendingValidationParseUrl = '';
			pendingValidationSourceUrl = '';
			refreshFromCsv(
				true,
				'Refreshing from CSV…',
				($csvUrlField.val() || '').trim()
			);
		});

		$csvUrlField.on('input change paste', function () {
			var csvUrl = ($csvUrlField.val() || '').trim();
			var previousCsvUrl = lastParsedCsvUrl;

			if (csvUrl === lastParsedCsvUrl) {
				return;
			}

			lastParsedCsvUrl = csvUrl;
			if (autoParseTimeout) {
				window.clearTimeout(autoParseTimeout);
			}

			if (!csvUrl) {
				pendingValidationParseUrl = '';
				pendingValidationSourceUrl = '';
				setStatus(
					'CSV removed. Clearing stored monthly required fees…',
					false
				);
				autoParseTimeout = window.setTimeout(function () {
					refreshFromCsv(
						false,
						'Clearing stored monthly required fees…',
						previousCsvUrl
					);
				}, 250);
				return;
			}

			pendingValidationParseUrl = csvUrl;
			pendingValidationSourceUrl = previousCsvUrl;
			setStatus(
				'CSV changed. Validating before calculating monthly required fees…',
				false
			);
		});

		$(document).on(
			'rentfetch_property_fees_csv_validation_complete.rfMonthlyFeesAuto',
			function (_event, payload) {
				if (!payload) {
					return;
				}

				var payloadUrl = (payload.url || '').trim();
				var currentUrl = ($csvUrlField.val() || '').trim();

				if (
					!pendingValidationParseUrl ||
					currentUrl !== pendingValidationParseUrl ||
					payloadUrl !== pendingValidationParseUrl
				) {
					return;
				}

				if (!payload.valid) {
					pendingValidationParseUrl = '';
					pendingValidationSourceUrl = '';
					setStatus(
						'CSV validation failed. Monthly required fees were not recalculated.',
						true
					);
					return;
				}

				var sourceCsvUrl = pendingValidationSourceUrl;
				pendingValidationParseUrl = '';
				pendingValidationSourceUrl = '';
				refreshFromCsv(
					false,
					'CSV validated. Calculating monthly required fees…',
					sourceCsvUrl
				);
			}
		);
	}

	document.addEventListener(
		'rentfetch:property-tab-activated',
		function (event) {
			if (event.detail && 'fees' === event.detail.tabId) {
				initFees(event.detail.panel);
			}
		}
	);

	$(function () {
		var activeFeesPanel = document.querySelector(
			'[data-rf-property-panel="fees"].is-active'
		);
		if (activeFeesPanel) {
			initFees(activeFeesPanel);
		}
	});
})(window, document, jQuery);
