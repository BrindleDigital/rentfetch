jQuery(function ($) {
	var $searchRoot = $('[data-property-search-shortcode-attributes]').first();
	var rentfetchConfig = window.rentfetchPropertySearchConfig || {};
	var restUrl =
		$searchRoot.attr('data-property-search-rest-url') ||
		rentfetchConfig.restUrl ||
		null;
	var shortcodeAttributes = {};
	var shortcodeAttributesJson = $searchRoot.attr(
		'data-property-search-shortcode-attributes'
	);
	var $filter = $('#filter');
	var $reset = $('#reset');
	var $response = $('#response');
	var $filterToggles = $('#filter-toggles');
	var $propertiesFound = $('#properties-found');
	var activeMapBounds = null;
	var isMapBoundsFilterActive = false;
	var latestMapPoints = [];
	var activeRequest = null;
	var latestRequestId = 0;

	if (shortcodeAttributesJson) {
		try {
			shortcodeAttributes = JSON.parse(shortcodeAttributesJson);
		} catch (error) {
			console.error(
				'Property search shortcode attributes could not be parsed',
				error
			);
		}
	}

	if (!restUrl) {
		console.error('REST API URL not available from server');
	}

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
			var startThirty = new Date();
			startThirty.setDate(startThirty.getDate() + 30);
			var endSixty = new Date();
			endSixty.setDate(endSixty.getDate() + 60);
			label =
				'30-60 days (' +
				(startThirty.getMonth() + 1) +
				'/' +
				startThirty.getDate() +
				'-' +
				(endSixty.getMonth() + 1) +
				'/' +
				endSixty.getDate() +
				')';
		} else if (val === '60-90') {
			var startSixty = new Date();
			startSixty.setDate(startSixty.getDate() + 60);
			var endNinety = new Date();
			endNinety.setDate(endNinety.getDate() + 90);
			label =
				'60-90 days (' +
				(startSixty.getMonth() + 1) +
				'/' +
				startSixty.getDate() +
				'-' +
				(endNinety.getMonth() + 1) +
				'/' +
				endNinety.getDate() +
				')';
		} else if (val.startsWith('fall-')) {
			label = 'Fall ' + val.split('-')[1] + ' (6/30-10/1)';
		} else if (val.startsWith('spring-')) {
			label = 'Spring ' + val.split('-')[1] + ' (3/1-5/31)';
		}

		dateRangeCache[val] = label;
		return label;
	}

	function updateURLWithQueryParameters(params) {
		var url = new URL(window.location.href);
		var existingParams = {};
		url.searchParams.forEach(function (value, key) {
			existingParams[key] = value;
		});
		var mergedParams = $.extend({}, existingParams, params);
		delete mergedParams.map_north;
		delete mergedParams.map_south;
		delete mergedParams.map_east;
		delete mergedParams.map_west;
		var baseUrl = url.origin + url.pathname;
		var queryString = $.param(mergedParams);
		var newUrl = baseUrl + (queryString ? '?' + queryString : '');
		history.pushState(null, '', newUrl);
	}

	function getQueryParametersFromForm() {
		var queryParams = {};

		$filter.find('input, select').each(function () {
			var $input = $(this);
			var inputName = $input.attr('name');

			if (!inputName) {
				return;
			}

			var inputValue = $input.val();
			if ($input.is(':checkbox')) {
				if (!queryParams[inputName]) {
					queryParams[inputName] = [];
				}
				if ($input.is(':checked')) {
					queryParams[inputName].push(inputValue);
				}
			} else if ($input.is(':radio')) {
				if ($input.is(':checked')) {
					queryParams[inputName] = inputValue;
				}
			} else {
				queryParams[inputName] = inputValue;
			}
		});

		$.each(queryParams, function (key, value) {
			if (
				value === '' ||
				key === 'action' ||
				key === 'availability' ||
				key === 'rentfetch_frontend_nonce_field'
			) {
				delete queryParams[key];
			}
		});

		return queryParams;
	}

	function outputToggles(toggleData) {
		var toggleMarkup = '';
		var fieldsets = toggleData.find('fieldset');

		fieldsets.each(function () {
			var fieldset = $(this);
			var legend = fieldset.find('legend').text();
			var activeFields = fieldset
				.find(
					'input:checked, input[type="number"], input[type="text"], input[type="date"]'
				)
				.filter(function () {
					return $(this).val().trim() !== '';
				})
				.not('[name="action"]');

			if (activeFields.length > 0) {
				var dataId = activeFields.first().attr('name');
				var dataType = activeFields.first().attr('data-type');
				var dataValues = activeFields
					.map(function () {
						return $(this).val();
					})
					.get()
					.join(',');
				var buttonContent = legend + ': ';

				switch (true) {
					case activeFields.length === 2 &&
						!activeFields.is(':checkbox'):
						buttonContent +=
							dataId === 'pricesmall' || dataId === 'pricebig'
								? '$' + dataValues.replace(',', '-')
								: dataValues.replace(/,/g, '-');
						break;
					case dataType === 'taxonomy':
						buttonContent =
							legend + ' (' + activeFields.length + ' selected)';
						break;
					case dataId === 'search-dates[]':
						var labels = [];
						dataValues.split(',').forEach(function (val) {
							var label = getDateRangeLabel(val);
							if (label) {
								labels.push(label);
							}
						});
						buttonContent = legend + ': ' + labels.join(', ');
						break;
					default:
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

		if (isMapBoundsFilterActive && activeMapBounds) {
			toggleMarkup +=
				'<button data-id="map_area" data-values="Map area">Map area</button>';
		}

		return toggleMarkup;
	}

	function updateToggleMarkup() {
		$filterToggles.html(outputToggles($filter));
	}

	function clearMapBoundsFilter() {
		activeMapBounds = null;
		isMapBoundsFilterActive = false;
	}

	function isPointWithinBounds(latitude, longitude, bounds) {
		var lat = parseFloat(latitude);
		var lng = parseFloat(longitude);

		if (
			!bounds ||
			Number.isNaN(lat) ||
			Number.isNaN(lng)
		) {
			return false;
		}

		return (
			lat <= bounds.north &&
			lat >= bounds.south &&
			lng <= bounds.east &&
			lng >= bounds.west
		);
	}

	function updateVisiblePropertyCount() {
		var visibleCount = $response.find('.properties-loop > :visible').length;
		var $resultsCount = $response.find('#properties-results-count-number');

		if ($resultsCount.length) {
			$resultsCount.text(visibleCount);
		}

		$propertiesFound.text(visibleCount);
	}

	function getFilteredMapPoints() {
		if (!isMapBoundsFilterActive || !activeMapBounds) {
			return latestMapPoints.slice();
		}

		return latestMapPoints.filter(function (point) {
			return isPointWithinBounds(
				point.latitude,
				point.longitude,
				activeMapBounds
			);
		});
	}

	function applyClientSideMapAreaFilter(options) {
		var filterOptions = $.extend(
			{
				preserveCurrentMapBounds: isMapBoundsFilterActive,
			},
			options
		);

		$response.find('.properties-loop > *').each(function () {
			var $property = $(this);
			var isVisible =
				!isMapBoundsFilterActive ||
				!activeMapBounds ||
				isPointWithinBounds(
					$property.attr('data-latitude'),
					$property.attr('data-longitude'),
					activeMapBounds
				);

			$property.toggle(isVisible);
		});

		updateVisiblePropertyCount();

		$(document).trigger('rentfetchPropertySearchComplete', [
			{
				mapPoints: getFilteredMapPoints(),
				preserveCurrentMapBounds: !!filterOptions.preserveCurrentMapBounds,
			},
		]);
	}

	function performAJAXSearch(options) {
		var requestOptions = $.extend(
			{
				preserveCurrentMapBounds: false,
			},
			options
		);

		if (!restUrl) {
			console.error('REST API URL not available');
			$reset.text('Clear All');
			$response.html(
				'<p>Search service unavailable. Please refresh the page and try again.</p>'
			);
			return;
		}

		var queryData = $.extend({}, shortcodeAttributes, getQueryParametersFromForm());
		var requestId = ++latestRequestId;

		if (activeRequest && activeRequest.readyState !== 4) {
			activeRequest.abort();
		}

		activeRequest = $.ajax({
			url: restUrl,
			data: queryData,
			type: 'GET',
			dataType: 'json',
			beforeSend: function () {
				$reset.text('Searching...');
				$response.html('');
			},
			success: function (response) {
				if (requestId !== latestRequestId) {
					return;
				}

				$reset.text('Clear All');
				$response.html(response.html);
				latestMapPoints = Array.isArray(response.map_points)
					? response.map_points
					: [];
				updateToggleMarkup();

				if ($('#map').length) {
					var mapOffset = $('#map').offset().top;
					var viewportTop = $(window).scrollTop();
					if (mapOffset - viewportTop > 200) {
						$('html, body').animate({}, 1000);
					}
				}

				applyClientSideMapAreaFilter({
					preserveCurrentMapBounds:
						!!requestOptions.preserveCurrentMapBounds ||
						isMapBoundsFilterActive,
				});
			},
			error: function (jqXHR, textStatus) {
				if (textStatus === 'abort' || requestId !== latestRequestId) {
					return;
				}

				$reset.text('Clear All');
				$response.html('<p>Search failed. Please try again.</p>');
			},
		});
	}

	function submitForm(options) {
		updateURLWithQueryParameters(getQueryParametersFromForm());
		performAJAXSearch(options);
		return false;
	}

	var submitTimer;
	function submitFormAfterInactivity() {
		clearTimeout(submitTimer);
		submitTimer = setTimeout(function () {
			submitForm();
		}, 500);
	}

	function clearFilterValues() {
		$('#filter, #featured-filters')
			.find('input:not([type="hidden"],[type="checkbox"],[type="radio"])')
			.val('');
		$('#filter, #featured-filters')
			.find('[type="checkbox"]:checked')
			.prop('checked', false);
		$('#filter, #featured-filters')
			.find('[type="radio"]:checked')
			.prop('checked', false);

		var defaultValSmall = $('#pricesmall').data('default-value');
		var defaultValBig = $('#pricebig').data('default-value');

		$('#pricesmall, #featured-pricesmall').val(defaultValSmall);
		$('#pricebig, #featured-pricebig').val(defaultValBig);
	}

	$(document).on('click', '#filter-toggles button', function () {
		var dataId = $(this).data('id');

		if (dataId === 'map_area') {
			clearMapBoundsFilter();
			updateToggleMarkup();
			applyClientSideMapAreaFilter({ preserveCurrentMapBounds: false });
			return;
		}

		var correspondingFields = $('[name="' + dataId + '"]');
		var fieldset = correspondingFields.closest('fieldset');

		fieldset.find(':checkbox').prop('checked', false);
		fieldset.find(':input:not(:checkbox)').val('').trigger('change');
		submitForm();
	});

	$('#reset, #featured-reset').click(function () {
		clearMapBoundsFilter();
		clearFilterValues();
		submitForm({ preserveCurrentMapBounds: false });
	});

	var $inputs = $('input, select, textarea');
	$inputs.on('change input', function () {
		var $source = $(this);
		var elementName = $source.attr('name');
		var newValue = $source.val();
		var isChecked = $source.is(':checked');
		var sourceId = $source.attr('id');

		$inputs
			.filter('[name="' + elementName + '"]')
			.not($source)
			.each(function () {
				var elementType = $(this).prop('tagName').toLowerCase();

				if (
					elementType === 'input' &&
					$(this).attr('type') === 'checkbox'
				) {
					if ($(this).val() === newValue) {
						$(this).prop('checked', isChecked);
					}
				} else if ($(this).is(':radio')) {
					$(this).prop(
						'checked',
						isChecked && $(this).attr('id') === sourceId
					);
				} else if ($(this).val() !== newValue) {
					$(this).val(newValue);
				}
			});

		submitFormAfterInactivity();
	});

	$(document).on('rentfetchPropertyMapBoundsChanged', function (event, payload) {
		if (
			!payload ||
			!payload.bounds ||
			(typeof payload.userInitiated !== 'undefined' &&
				!payload.userInitiated)
		) {
			return;
		}

		activeMapBounds = payload.bounds;
		isMapBoundsFilterActive = true;
		updateToggleMarkup();
		applyClientSideMapAreaFilter({ preserveCurrentMapBounds: true });
	});

	updateToggleMarkup();
	submitForm({ preserveCurrentMapBounds: false });
});
