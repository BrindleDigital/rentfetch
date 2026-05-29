jQuery(function ($) {
	var $searchRoot = $('[data-floorplan-search-shortcode-attributes]').first();
	var rentfetchConfig = window.rentfetchFloorplanSearchConfig || {};
	var restUrl =
		$searchRoot.attr('data-floorplan-search-rest-url') ||
		rentfetchConfig.restUrl ||
		null;
	var shortcodeAttributes = {};
	var shortcodeAttributesJson = $searchRoot.attr(
		'data-floorplan-search-shortcode-attributes'
	);
	var $filter = $('#filter');
	var $featuredFilters = $('#featured-filters');
	var $filterToggles = $('#filter-toggles');
	var $response = $('#response');
	var $reset = $('#reset');

	if (shortcodeAttributesJson) {
		try {
			shortcodeAttributes = JSON.parse(shortcodeAttributesJson);
		} catch (error) {
			console.error(
				'Floorplan search shortcode attributes could not be parsed',
				error
			);
		}
	}

	if (!restUrl) {
		console.error('REST API URL not available from server');
	}

	function getSearchErrorMessage(jqXHR, textStatus, errorThrown) {
		var fallbackMessage =
			'Search failed. Please refresh the page and try again.';

		if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
			return jqXHR.responseJSON.message;
		}

		if (jqXHR && jqXHR.status) {
			return (
				'Search failed with server status ' +
				jqXHR.status +
				'. Please refresh the page and try again.'
			);
		}

		if (textStatus === 'parsererror') {
			return 'Search failed because the server returned an invalid response.';
		}

		if (textStatus === 'timeout') {
			return 'Search timed out. Please try again.';
		}

		if (errorThrown) {
			return fallbackMessage + ' (' + errorThrown + ')';
		}

		return fallbackMessage;
	}

	function renderSearchError($target, message) {
		$target.html('<p>' + $('<div>').text(message).html() + '</p>');
	}

	function logTransientCacheDebug(debug) {
		if (
			!rentfetchConfig.enableCacheConsoleLogging ||
			!debug ||
			!window.console ||
			!console.info
		) {
			return;
		}

		var label =
			debug.status === 'hit'
				? 'Rent Fetch transient HIT'
				: 'Rent Fetch transient MISS';

		console.info(label + ': ' + debug.description, {
			key: debug.key,
			family: debug.family,
			stale: !!debug.stale,
			stats: debug.stats || null,
			cache: {
				lookupAttempted: !!debug.lookup_attempted,
				readEnabled: !!debug.read_enabled,
				writeEnabled: !!debug.write_enabled,
				writeAttempted: !!debug.write_attempted,
				writeStored: debug.write_stored,
			},
		});

		if (debug.background_refresh_scheduled) {
			console.info(
				'Rent Fetch background transient refresh scheduled: ' +
					debug.description,
				{
					key: debug.key,
					family: debug.family,
				}
			);
		}

		if (Array.isArray(debug.events)) {
			debug.events.forEach(function (event) {
				if (event.key === debug.key) {
					return;
				}

				console.info(
					'Rent Fetch query transient ' +
						event.status.toUpperCase() +
						': ' +
						event.description,
					{
						key: event.key,
						family: event.family,
					}
				);
			});
		}

		if (Array.isArray(debug.writes)) {
			debug.writes.forEach(function (event) {
				console.info(
					'Rent Fetch transient WRITE ' +
						(event.stored ? 'STORED' : 'FAILED') +
						': ' +
						event.description,
					{
						key: event.key,
						family: event.family,
						valueType: event.value_type,
						valueSize: event.value_size,
					}
				);
			});
		}

		if (Array.isArray(debug.prunes)) {
			debug.prunes.forEach(function (event) {
				console.info(
					'Rent Fetch transient PRUNED: ' + event.description,
					{
						key: event.key,
						family: event.family,
					}
				);
			});
		}
	}

	function logSearchRequest(queryData) {
		if (
			!rentfetchConfig.enableCacheConsoleLogging ||
			!window.console ||
			!console.info
		) {
			return;
		}

		console.info('Rent Fetch floorplan search', {
			query: $.param(queryData),
			params: queryData,
		});
	}

	function normalizeQueryParameterName(name) {
		return String(name || '').replace(/\[[^\]]*\]$/, '');
	}

	function getManagedQueryParameterNames() {
		var managedParams = {};

		$filter.add($featuredFilters)
			.find('input, select, textarea')
			.each(function () {
				var name = $(this).attr('name');

				if (name) {
					managedParams[normalizeQueryParameterName(name)] = true;
				}
			});

		return managedParams;
	}

	// Function to update URL with query parameters
	function updateURLWithQueryParameters(params) {
		var url = new URL(window.location.href);
		var managedParams = getManagedQueryParameterNames();
		var mergedParams = {};

		url.searchParams.forEach(function (value, key) {
			if (managedParams[normalizeQueryParameterName(key)]) {
				return;
			}

			mergedParams[key] = value;
		});

		$.extend(mergedParams, params);

		var baseUrl = url.origin + url.pathname;
		var queryString = $.param(mergedParams);
		var newUrl = baseUrl + (queryString ? '?' + queryString : '');
		history.pushState(null, '', newUrl);
	}

	// Function to get query parameters from form for URL updates
	function getQueryParametersFromForm() {
		var queryParams = {};
		$filter.find('input, select').each(function () {
			var inputName = $(this).attr('name');
			var inputId = $(this).attr('id');

			if (inputName) {
				var inputValue = $(this).val();

				if ($(this).is(':checkbox')) {
					if (!queryParams[inputName]) {
						queryParams[inputName] = [];
					}
					if ($(this).is(':checked')) {
						queryParams[inputName].push(inputValue);
					}
				} else if ($(this).is(':radio')) {
					// Handle radio buttons separately
					if ($(this).is(':checked') && inputId) {
						queryParams[inputName] = inputValue;
					}
				} else {
					queryParams[inputName] = inputValue;
				}
			}
		});

		// Remove empty and unwanted parameters
		$.each(queryParams, function (key, value) {
			if (
				value === '' || // Exclude empty values
				(Array.isArray(value) && !value.length) ||
				key === 'action' || // Exclude specific parameters
				key === 'rentfetch_frontend_nonce_field'
			) {
				delete queryParams[key];
			}
		});

		return queryParams;
	}

	// Function to clear values from corresponding fields and trigger change event
	$filterToggles.on('click', 'button', function () {
		var dataId = $(this).data('id');
		var correspondingFields = $filter
			.add($featuredFilters)
			.find('[name="' + dataId + '"]');
		var fieldset = correspondingFields.closest('fieldset'); // Find the fieldset containing the correspondingFields

		fieldset.find(':checkbox').prop('checked', false); // Clear checkbox inputs within the fieldset

		// Clear non-checkbox inputs within the fieldset and trigger change event
		fieldset.find(':input:not(:checkbox)').each(function () {
			var elementType = $(this).prop('type');

			if (elementType === 'radio') {
				// For radio buttons, clear the checked status
				$(this).prop('checked', false);
			} else {
				// For other inputs, clear the value and trigger change event
				$(this).val('').trigger('change');
			}
		});

		submitForm();
	});

	function outputToggles(toggleData) {
		var toggleMarkup = '';

		// Get all the fieldsets within the toggleData
		var fieldsets = toggleData.find('fieldset');

		// Iterate through each fieldset
		fieldsets.each(function () {
			var fieldset = $(this);
			var legend = fieldset.find('legend').text();
			var activeFields = fieldset
				.find(
					'input:checked, input[type="number"], input[type="text"], input[type="date"]'
				)
				.filter(function () {
					return $(this).val().trim() !== ''; // Check if input is not empty
				})
				.not('[name="action"]');

			// Check if there are any active fields
			if (activeFields.length > 0) {
				var dataId = activeFields.first().attr('name');
				var dataValues = activeFields
					.map(function () {
						return $(this).val();
					})
					.get()
					.join(',');

				// Create the button element
				var buttonContent = legend + ': ';

				switch (true) {
					case activeFields.length === 2 &&
						!activeFields.is(':checkbox'):
						// if it's a range, replace the comma with a dash
						if (dataId === 'pricesmall' || dataId === 'pricebig') {
							buttonContent += '$' + dataValues.replace(',', '-');
						} else {
							// for example, if it's a square footage range
							buttonContent += dataValues.replace(/,/g, '-');
						}
						break;
					case dataId === 'search-amenities[]':
						// if it's the amenities, change the content
						buttonContent =
							legend + ' (' + activeFields.length + ' selected)';
						break;
					case dataId === 'search-floorplancategory[]':
						// if it's the property types, change the content
						buttonContent =
							legend + ' (' + activeFields.length + ' selected)';
						break;
					case dataId === 'search-floorplantype[]':
						// if it's the property types, change the content
						buttonContent =
							legend + ' (' + activeFields.length + ' selected)';
						break;
					case dataId === 'search-dates[]':
						// Map selected values to display labels with date ranges
						var selectedValues = dataValues.split(',');
						var labels = [];
						selectedValues.forEach(function (val) {
							var label = '';
							if (val === 'now-30') {
								var start = new Date();
								start.setDate(start.getDate() - 365);
								var end = new Date();
								end.setDate(end.getDate() + 30);
								label =
									'Next 30 days (' +
									(start.getMonth() + 1) +
									'/' +
									start.getDate() +
									'-' +
									(end.getMonth() + 1) +
									'/' +
									end.getDate() +
									')';
							} else if (val === '30-60') {
								var start = new Date();
								start.setDate(start.getDate() + 30);
								var end = new Date();
								end.setDate(end.getDate() + 60);
								label =
									'30-60 days (' +
									(start.getMonth() + 1) +
									'/' +
									start.getDate() +
									'-' +
									(end.getMonth() + 1) +
									'/' +
									end.getDate() +
									')';
							} else if (val === '60-90') {
								var start = new Date();
								start.setDate(start.getDate() + 60);
								var end = new Date();
								end.setDate(end.getDate() + 90);
								label =
									'60-90 days (' +
									(start.getMonth() + 1) +
									'/' +
									start.getDate() +
									'-' +
									(end.getMonth() + 1) +
									'/' +
									end.getDate() +
									')';
							} else if (val.startsWith('fall-')) {
								var year = val.split('-')[1];
								label = 'Fall ' + year + ' (6/30-10/1)';
							} else if (val.startsWith('spring-')) {
								var year = val.split('-')[1];
								label = 'Spring ' + year + ' (3/1-5/31)';
							}
							if (label) labels.push(label);
						});
						buttonContent = legend + ': ' + labels.join(', ');
						break;
					case dataId === 'sort':
						// if it's the property types, change the content
						if (dataValues === 'availability') {
							buttonContent += 'Available units';
						} else if (dataValues === 'beds') {
							buttonContent += 'Beds';
						} else if (dataValues === 'baths') {
							buttonContent += 'Baths';
						} else if (dataValues === 'pricelow') {
							buttonContent += 'Price (low to high)';
						} else if (dataValues === 'pricehigh') {
							buttonContent += 'Price (high to low)';
						} else if (dataValues === 'alphabetical') {
							buttonContent += 'Alphabetical';
						} else {
							buttonContent += dataValues.replace(/,/g, ', ');
						}
						break;
					default:
						// otherwise, just add the values (this handles checkboxes)
						buttonContent += dataValues.replace(/,/g, ', ');
				}

				toggleMarkup +=
					'<button data-id="' +
					dataId +
					'" data-values="' +
					dataValues.replace(/,/g, ', ') +
					'">' +
					buttonContent +
					'</button>';
			}
		});

		return toggleMarkup;
	}

	// Function to perform REST API search
	function performAJAXSearch(queryParams) {
		if (!restUrl) {
			console.error('REST API URL not available');
			$reset.text('Clear All');
			$response.html(
				'<p>Search service unavailable. Please refresh the page and try again.</p>'
			);
			return;
		}
		performActualSearch(queryParams);
	}

	// Function to perform the actual REST API search
	function performActualSearch(queryParams) {
		var toggleData = $filter;

		// Build query parameters from form
		var formData = {};
		$filter.find('input, select').each(function () {
			var name = $(this).attr('name');
			var value = $(this).val();

			// Skip hidden fields like action and nonce
			if (
				name === 'action' ||
				name === 'rentfetch_frontend_nonce_field'
			) {
				return;
			}

			if ($(this).is(':checkbox')) {
				if ($(this).is(':checked')) {
					if (!formData[name]) {
						formData[name] = [];
					}
					formData[name].push(value);
				}
			} else if ($(this).is(':radio')) {
				if ($(this).is(':checked')) {
					formData[name] = value;
				}
			} else if (value !== '') {
				formData[name] = value;
			}
		});

		// Merge with shortcode attributes
		var queryData = $.extend({}, shortcodeAttributes, formData);
		logSearchRequest(queryData);

		$.ajax({
			url: restUrl,
			data: queryData,
			type: 'GET',
			dataType: 'json',
			cache: !rentfetchConfig.enableCacheConsoleLogging,
			beforeSend: function (xhr) {
				if (rentfetchConfig.nonce) {
					xhr.setRequestHeader('X-WP-Nonce', rentfetchConfig.nonce);
				}
				$reset.text('Searching...'); // changing the button label
				$response.html(''); // clear response div
			},
			success: function (response) {
				logTransientCacheDebug(response.cache_debug);

				$reset.text('Clear All'); // changing the button label
				$response.html(response.html); // insert HTML from REST response

				var toggles = outputToggles(toggleData);
				$filterToggles.html(toggles);

				// look in data for .properties-loop, and count the number of children
				var count = $('.floorplans-loop').children().length;
				// update #properties-found with the count
				$('#floorplans-found').text(count);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error('Rent Fetch floorplan search failed', {
					status: jqXHR.status,
					statusText: jqXHR.statusText,
					response: jqXHR.responseJSON || jqXHR.responseText,
					textStatus: textStatus,
					errorThrown: errorThrown,
				});

				$reset.text('Clear All');
				renderSearchError(
					$response,
					getSearchErrorMessage(jqXHR, textStatus, errorThrown)
				);
			},
		});
	}

	// Our ajax query to get stuff and put it into the response div
	function submitForm() {
		var queryParams = getQueryParametersFromForm(); // Get parameters from form

		updateURLWithQueryParameters(queryParams);
		performAJAXSearch(); // Perform REST API search

		return false;
	}

	// submit on page load
	submitForm();

	//! WHEN CHANGES ARE MADE, SUBMIT THE FORM

	var submitTimer; // Timer identifier

	// Function to submit the form after half a second of inactivity
	function submitFormAfterInactivity() {
		var self = this; // Capture the current context
		clearTimeout(submitTimer); // Clear any previous timer
		submitTimer = setTimeout(function () {
			submitForm(); // Submit the form after half a second of inactivity
		}, 500);
	}

	// Call the function on input
	// $('#filter').on('change', submitFormAfterInactivity);

	//! RESET THE FORMS

	// Function to clear all values from fields in #filter when #reset is clicked
	function clearFilterValues() {
		// Reset all non-hidden inputs to null value
		$filter.add($featuredFilters)
			.find('input:not([type="hidden"],[type="checkbox"],[type="radio"])')
			.val('');
		// .trigger('change'); // Trigger the change event

		// Reset checkboxes to unchecked
		$filter.add($featuredFilters)
			.find('[type="checkbox"]:checked') // Select only checked checkboxes
			.prop('checked', false);
		// .trigger('change'); // Trigger the change event

		// Get default values for input#pricesmall and input#pricebig
		var defaultValSmall = $('#pricesmall').data('default-value');
		var defaultValBig = $('#pricebig').data('default-value');

		// Set default values for input#pricesmall and input#pricebig
		$('#pricesmall, #featured-pricesmall').val(defaultValSmall);
		// .trigger('change'); // Trigger the change event
		$('#pricebig, #featured-pricebig').val(defaultValBig);
		// .trigger('change'); // Trigger the change event
	}

	// Call the function when #reset is clicked
	$filter.add($featuredFilters).on('click', '#reset, #featured-reset', function () {
		clearFilterValues();
		submitForm();
	});

	//! SYNC THE FORMS

	// Select all input, select, and textarea elements
	var $inputs = $filter.add($featuredFilters).find('input, select, textarea');

	var programmaticChange = false; // Flag to check if the change was programmatic

	$inputs.on('change input', function () {
		if (programmaticChange) {
			// If the change was programmatic, return early
			return;
		}

		var elementName = $(this).attr('name');
		var elementId = $(this).attr('id');
		var newValue = $(this).val();
		var isChecked = $(this).is(':checked');

		if ($(this).is(':radio')) {
			// If it's a radio input, only update the checked status for radios with the same name and id
			$inputs
				.filter('[name="' + elementName + '"]')
				.not(this)
				.each(function () {
					if ($(this).attr('id') === elementId) {
						$(this).prop('checked', isChecked);
					}
				});
		} else {
			// For other input types (e.g., text, checkbox), update their values
			$inputs
				.filter('[name="' + elementName + '"]')
				.not(this)
				.not(':radio') // Exclude radios
				.each(function () {
					var elementType = $(this).prop('tagName').toLowerCase();

					if (
						elementType === 'input' &&
						$(this).attr('type') === 'checkbox'
					) {
						// For checkboxes, update the checked status
						var otherValue = $(this).val();
						if (otherValue === newValue) {
							$(this).prop('checked', isChecked);
						}
					} else {
						// For other elements, update the value
						if ($(this).val() !== newValue) {
							$(this).off('change input'); // Temporarily remove the event handler
							$(this).val(newValue);
							$(this).trigger('change');
							// $(this).on('change input', changeInputHandler); // Reattach the event handler
						}
					}
				});
		}

		submitFormAfterInactivity();
	});
});
