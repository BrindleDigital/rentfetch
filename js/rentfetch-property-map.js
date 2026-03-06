jQuery(function ($) {
	var map;
	var markers = [];
	var locationsArray = [];
	var markerImage = options.marker_url;
	var googleMapsDefaultLongitude = options.google_maps_default_longitude;
	var googleMapsDefaultLatitude = options.google_maps_default_latitude;
	var mapStyle = options.json_style;
	var suppressBoundsFilter = false;
	var suppressBoundsFilterTimeout = null;
	var userInteractionDetected = false;
	var latestSearchPayload = null;

	function withSuppressedBoundsFilter(callback) {
		suppressBoundsFilter = true;
		clearTimeout(suppressBoundsFilterTimeout);
		callback();
		suppressBoundsFilterTimeout = setTimeout(function () {
			suppressBoundsFilter = false;
		}, 250);
	}

	function isGoogleMapsReady() {
		return (
			window.google &&
			window.google.maps &&
			typeof window.google.maps.LatLng === 'function'
		);
	}

	function renderMap() {
		var mapElement = document.getElementById('map');
		if (!mapElement) {
			return false;
		}

		var myLatlng = new google.maps.LatLng(
			googleMapsDefaultLatitude,
			googleMapsDefaultLongitude
		);

		map = new google.maps.Map(mapElement, {
			zoom: 8,
			minZoom: 5,
			maxZoom: 17,
			center: myLatlng,
			styles: mapStyle,
			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.RIGHT_TOP,
			},
			fullscreenControl: false,
		});

		map.addListener('dragstart', function () {
			userInteractionDetected = true;
		});

		map.addListener('zoom_changed', function () {
			if (!suppressBoundsFilter) {
				userInteractionDetected = true;
			}
		});

		map.addListener('idle', function () {
			if (!userInteractionDetected || suppressBoundsFilter) {
				userInteractionDetected = false;
				return;
			}

			var bounds = map.getBounds();
			if (!bounds) {
				userInteractionDetected = false;
				return;
			}

			var northEast = bounds.getNorthEast();
			var southWest = bounds.getSouthWest();

			$(document).trigger('rentfetchPropertyMapBoundsChanged', [
				{
					userInitiated: true,
					bounds: {
						north: northEast.lat(),
						east: northEast.lng(),
						south: southWest.lat(),
						west: southWest.lng(),
					},
				},
			]);

			userInteractionDetected = false;
		});

		return true;
	}

	function setLocations(points) {
		locationsArray = Array.isArray(points) ? points : [];
	}

	function clearMarkers() {
		markers.forEach(function (marker) {
			marker.setMap(null);
		});
		markers = [];
	}

	function addMarkers() {
		var bounds = new google.maps.LatLngBounds();

		locationsArray.forEach(function (location) {
			var position = new google.maps.LatLng(
				location.latitude,
				location.longitude
			);
			var marker = new google.maps.Marker({
				position: position,
				map: null,
				title: location.title,
				icon: typeof markerImage !== 'undefined' ? markerImage : undefined,
			});

			bounds.extend(position);

			marker.infowindow = new google.maps.InfoWindow({
				content:
					'<div class="map-property-popup" id="overlay-' +
					location.marker_id +
					'">' +
					location.popup_html +
					'</div>',
			});

			google.maps.event.addListener(marker, 'click', function () {
				markers.forEach(function (existingMarker) {
					existingMarker.infowindow.close(map, marker);
				});

				marker.infowindow.open(map, marker);
				$('.type-properties').removeClass('active');
				$('.type-properties[data-id=' + location.marker_id + ']').addClass(
					'active'
				);

				if (typeof scrollToActiveProperty === 'function') {
					scrollToActiveProperty(location.marker_id);
				}

				setTimeout(function () {
					$('.type-properties').removeClass('active');
				}, 1000);
			});

			google.maps.event.addListener(marker, 'mouseover', function () {
				markers.forEach(function (existingMarker) {
					existingMarker.infowindow.close(map, marker);
				});

				marker.infowindow.open(map, marker);
				$('.type-properties').removeClass('active');
			});

			markers.push(marker);
		});

		return bounds;
	}

	function applyMapPoints(points, preserveCurrentBounds) {
		if (!isGoogleMapsReady()) {
			return;
		}

		if (!map) {
			if (!renderMap()) {
				return;
			}
		}

		setLocations(points);
		clearMarkers();

		if (!locationsArray.length) {
			if (!preserveCurrentBounds) {
				withSuppressedBoundsFilter(function () {
					map.setCenter(
						new google.maps.LatLng(
							googleMapsDefaultLatitude,
							googleMapsDefaultLongitude
						)
					);
					map.setZoom(8);
				});
			}
			return;
		}

		var bounds = addMarkers();
		markers.forEach(function (marker) {
			marker.setMap(map);
		});

		if (!preserveCurrentBounds) {
			withSuppressedBoundsFilter(function () {
				map.fitBounds(bounds);
			});
		}

		if (typeof window.rentFetchMapHooks === 'undefined') {
			window.rentFetchMapHooks = [];
		}

		window.rentFetchMapHooks.forEach(function (hook) {
			if (typeof hook === 'function') {
				hook(map, markers);
			}
		});
	}

	function openMarkerOnGridHover() {
		var markerIndex = parseInt($(this).attr('data-id'), 10);

		if (markerIndex >= 0 && markerIndex < markers.length) {
			google.maps.event.trigger(markers[markerIndex], 'mouseover');
		}
	}

	$(document).on('mouseenter', '.type-properties', openMarkerOnGridHover);

	$(document).on('rentfetchPropertySearchComplete', function (event, payload) {
		if (!payload || !Array.isArray(payload.mapPoints)) {
			return;
		}

		latestSearchPayload = payload;

		if (!isGoogleMapsReady()) {
			return;
		}

		applyMapPoints(payload.mapPoints, !!payload.preserveCurrentMapBounds);
	});

	function handleGoogleMapsReady() {
		if (!map) {
			renderMap();
		}

		if (latestSearchPayload && Array.isArray(latestSearchPayload.mapPoints)) {
			applyMapPoints(
				latestSearchPayload.mapPoints,
				!!latestSearchPayload.preserveCurrentMapBounds
			);
		}

		if (!window.rentfetchAdvancedMarkerNoticeShown) {
			window.rentfetchAdvancedMarkerNoticeShown = true;
			console.warn(
				'[rentfetch] google.maps.Marker is deprecated. Google Maps Advanced Markers require a Map ID. Switching would be a breaking change for existing sites because each site would need to update its API setup and provide a map_id, which is not currently feasible. Google\'s warning below in the console should therefore be ignored until we\'re willing to make such a breaking change.'
			);
		}
	}

	window.rentfetchGoogleMapsLoaded =
		window.rentfetchGoogleMapsLoaded ||
		function () {
			window.rentfetchGoogleMapsReadyFired = true;
			$(document).trigger('rentfetchGoogleMapsReady');
		};

	$(document).on('rentfetchGoogleMapsReady', handleGoogleMapsReady);
	if (window.rentfetchGoogleMapsReadyFired) {
		$(document).trigger('rentfetchGoogleMapsReady');
	}

	if (isGoogleMapsReady()) {
		handleGoogleMapsReady();
	}
});
