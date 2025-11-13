jQuery(function ($) {
	// Get REST URL and shortcode attributes from localized data (generated in PHP via wp_add_inline_script)
	var rentfetchData = window.rentfetchPropertySearch || {};
	var restUrl = rentfetchData.restUrl || null;
	var shortcodeAttributes = rentfetchData.shortcodeAttributes || {};

	if (!restUrl) {
		console.error('REST API URL not available from server');
	}

	// Cache frequently used DOM elements
	var $filter = $('#filter');
	var $reset = $('#reset');
	var $response = $('#response');
	var $filterToggles = $('#filter-toggles');
	var $propertiesFound = $('#properties-found');

	// Pre-compute date ranges for performance
	var dateRangeCache = {};
	function getDateRangeLabel(val) {
		if (dateRangeCache[val]) {
			return dateRangeCache[val];
		}

		var label = '';
		if (val === 'now-30') {
			var start = new Date();
			start.setFullYear(start.getFullYear() - 1);
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

		dateRangeCache[val] = label;
		return label;
	}

	// Function to update URL with query parameters
	function updateURLWithQueryParameters(params) {
		var baseUrl = window.location.href.split('?')[0];
		var queryString = $.param(params); // Serialize the parameters
		var newUrl = baseUrl + (queryString ? '?' + queryString : '');
		history.pushState(null, '', newUrl);
	}

	// Function to get query parameters from form for URL updates
	function getQueryParametersFromForm() {
		var queryParams = {};

		// Use cached selector and more efficient iteration
		$filter.find('input, select').each(function () {
			var $this = $(this);
			var inputName = $this.attr('name');
			if (inputName) {
				var inputValue = $this.val();

				// Handle checkboxes and multiple values
				if ($this.is(':checkbox')) {
					if (!queryParams[inputName]) {
						queryParams[inputName] = [];
					}
					if ($this.is(':checked')) {
						queryParams[inputName].push(inputValue);
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
				key === 'action' || // Exclude specific parameters
				key === 'availability' ||
				key === 'rentfetch_frontend_nonce_field'
			) {
				delete queryParams[key];
			}
		});

		return queryParams;
	}

	// Function to clear values from corresponding fields and trigger change event
	$(document).on('click', '#filter-toggles button', function () {
		var dataId = $(this).data('id');
		var correspondingFields = $('[name="' + dataId + '"]');
		var fieldset = correspondingFields.closest('fieldset'); // Find the fieldset containing the correspondingFields

		fieldset.find(':checkbox').prop('checked', false); // Clear checkbox inputs within the fieldset
		fieldset.find(':input:not(:checkbox)').val('').trigger('change'); // Clear non-checkbox inputs within the fieldset and trigger change event
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
				var dataType = activeFields.first().attr('data-type');
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
							buttonContent += dataValues.replace(/,/g, '-');
						}
						break;
					case dataType === 'taxonomy':
						// if it's a taxonomy, change the content
						buttonContent =
							legend + ' (' + activeFields.length + ' selected)';
						break;
					case dataId === 'search-dates[]':
						// Map selected values to display labels with date ranges (using cache)
						var selectedValues = dataValues.split(',');
						var labels = [];
						selectedValues.forEach(function (val) {
							var label = getDateRangeLabel(val);
							if (label) labels.push(label);
						});
						buttonContent = legend + ': ' + labels.join(', ');
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
	function performAJAXSearch() {
		if (!restUrl) {
			console.error('REST API URL not available');
			$reset.text('Clear All');
			$response.html(
				'<p>Search service unavailable. Please refresh the page and try again.</p>'
			);
			return;
		}
		performActualPropertySearch();
	}

	// Function to perform the actual REST API search
	function performActualPropertySearch() {
		var filter = $('#filter');
		var toggleData = filter;

		// Build query parameters from form and shortcode attributes
		var formData = {};
		filter.find('input, select').each(function () {
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

		$.ajax({
			url: restUrl,
			data: queryData,
			type: 'GET',
			dataType: 'json',
			beforeSend: function (xhr) {
				$reset.text('Searching...'); // changing the button label
				$response.html(''); // clear #response div
			},
			success: function (response) {
				$reset.text('Clear All'); // changing the button label
				$response.html(response.html); // insert HTML from REST response

				var toggles = outputToggles(toggleData);
				$filterToggles.html(toggles);

				if ($('#map').length) {
					var mapOffset = $('#map').offset().top;
					var viewportTop = $(window).scrollTop();
					if (mapOffset - viewportTop > 200) {
						$('html, body').animate(
							{
								// scrollTop: mapOffset,
								// TODO make the scrolldown optional
							},
							1000
						);
					}
				}

				// look in data for .properties-loop, and count the number of children
				var count = $('.properties-loop').children().length;
				// update #properties-found with the count
				$propertiesFound.text(count);

				// Trigger custom event for map update
				$(document).trigger('rentfetchPropertySearchComplete');
			},
			error: function (jqXHR) {
				$reset.text('Clear All');
				$response.html('<p>Search failed. Please try again.</p>');
			},
		});
	}

	// Our ajax query to get stuff and put it into the response div
	function submitForm() {
		var queryParams = getQueryParametersFromForm();
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
		$('#filter, #featured-filters')
			.find('input:not([type="hidden"],[type="checkbox"],[type="radio"])')
			.val('');
		// .trigger('change'); // Trigger the change event

		// Reset checkboxes to unchecked
		$('#filter, #featured-filters')
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
	$('#reset, #featured-reset').click(function () {
		clearFilterValues();
		submitForm();
	});

	//! SYNC THE FORMS

	// Select all input, select, and textarea elements
	var $inputs = $('input, select, textarea');

	var programmaticChange = false; // Flag to check if the change was programmatic

	$inputs.on('change input', function () {
		if (programmaticChange) {
			// If the change was programmatic, return early
			return;
		}

		var elementName = $(this).attr('name');
		var newValue = $(this).val();
		var isChecked = $(this).is(':checked');

		// Update identically named elements with the new value and checked status
		$inputs
			.filter('[name="' + elementName + '"]')
			.not(this)
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
			}); // end .each()

		submitFormAfterInactivity();
	}); // end $inputs.on()
}); // end jQuery
