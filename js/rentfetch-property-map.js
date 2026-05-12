jQuery(function ($) {
	var map;
	var markers = [];
	var markersById = {};
	var locationsArray = [];
	var markerImage = options.marker_url;
	var googleMapsDefaultLongitude = options.google_maps_default_longitude;
	var googleMapsDefaultLatitude = options.google_maps_default_latitude;
	var mapStyle = options.json_style;
	var suppressBoundsFilter = false;
	var suppressBoundsFilterTimeout = null;
	var userInteractionDetected = false;
	var latestSearchPayload = null;
	var activeInfoWindow = null;
	var lastAppliedBoundsSignature = null;
	var initialMapRevealComplete = false;
	var mapElement = null;

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

	function setMapOpacity(opacity) {
		if (!mapElement) {
			return;
		}

		mapElement.style.transition = 'opacity 180ms ease';
		mapElement.style.opacity = opacity;
	}

	function revealMapAfterIdle() {
		if (!map) {
			setMapOpacity('1');
			initialMapRevealComplete = true;
			return;
		}

		google.maps.event.addListenerOnce(map, 'idle', function () {
			window.requestAnimationFrame(function () {
				setMapOpacity('1');
				initialMapRevealComplete = true;
			});
		});
	}

	function renderMap() {
		mapElement = document.getElementById('map');
		if (!mapElement) {
			return false;
		}

		if (!initialMapRevealComplete) {
			mapElement.style.opacity = '0';
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

		activeInfoWindow = new google.maps.InfoWindow();

		return true;
	}

	function setLocations(points) {
		locationsArray = Array.isArray(points) ? points : [];
	}

	function clearMarkers() {
		if (activeInfoWindow) {
			activeInfoWindow.close();
		}
		markers.forEach(function (marker) {
			marker.setMap(null);
		});
		markers = [];
		markersById = {};
	}

	function openPropertyMarker(marker, location) {
		if (!activeInfoWindow) {
			activeInfoWindow = new google.maps.InfoWindow();
		}

		activeInfoWindow.setContent(
			'<div class="map-property-popup" id="overlay-' +
				location.marker_id +
				'">' +
				location.popup_html +
				'</div>'
		);
		activeInfoWindow.open(map, marker);
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

			google.maps.event.addListener(marker, 'click', function () {
				openPropertyMarker(marker, location);
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
				openPropertyMarker(marker, location);
				$('.type-properties').removeClass('active');
			});

			markers.push(marker);
			markersById[String(location.marker_id)] = marker;
		});

		return bounds;
	}

	function getBoundsSignature(bounds) {
		if (!bounds) {
			return '';
		}

		var northEast = bounds.getNorthEast();
		var southWest = bounds.getSouthWest();

		return [
			northEast.lat().toFixed(4),
			northEast.lng().toFixed(4),
			southWest.lat().toFixed(4),
			southWest.lng().toFixed(4),
		].join('|');
	}

	function shouldFitBounds(bounds) {
		var signature = getBoundsSignature(bounds);

		if (!signature || signature === lastAppliedBoundsSignature) {
			return false;
		}

		lastAppliedBoundsSignature = signature;
		return true;
	}

	function getBoundsSpan(bounds) {
		if (!bounds) {
			return null;
		}

		var northEast = bounds.getNorthEast();
		var southWest = bounds.getSouthWest();

		return {
			lat: Math.abs(northEast.lat() - southWest.lat()),
			lng: Math.abs(northEast.lng() - southWest.lng()),
		};
	}

	function isDramaticBoundsChange(nextBounds) {
		var currentBounds = map ? map.getBounds() : null;
		var currentSpan = getBoundsSpan(currentBounds);
		var nextSpan = getBoundsSpan(nextBounds);

		if (!currentSpan || !nextSpan || !currentSpan.lat || !currentSpan.lng || !nextSpan.lat || !nextSpan.lng) {
			return true;
		}

		var latRatio = nextSpan.lat / currentSpan.lat;
		var lngRatio = nextSpan.lng / currentSpan.lng;

		return latRatio > 3 || latRatio < 0.33 || lngRatio > 3 || lngRatio < 0.33;
	}

	function latRad(lat) {
		var sin = Math.sin((lat * Math.PI) / 180);
		var radX2 = Math.log((1 + sin) / (1 - sin)) / 2;

		return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
	}

	function getBoundsZoom(bounds) {
		if (!mapElement || !bounds) {
			return 8;
		}

		var worldDimension = { height: 256, width: 256 };
		var padding = 80;
		var zoomMax = 17;
		var northEast = bounds.getNorthEast();
		var southWest = bounds.getSouthWest();
		var latFraction =
			(latRad(northEast.lat()) - latRad(southWest.lat())) / Math.PI;
		var lngDiff = northEast.lng() - southWest.lng();
		var lngFraction = (lngDiff < 0 ? lngDiff + 360 : lngDiff) / 360;
		var mapWidth = Math.max(1, mapElement.offsetWidth - padding);
		var mapHeight = Math.max(1, mapElement.offsetHeight - padding);
		var latZoom =
			latFraction > 0
				? Math.floor(Math.log(mapHeight / worldDimension.height / latFraction) / Math.LN2)
				: zoomMax;
		var lngZoom =
			lngFraction > 0
				? Math.floor(Math.log(mapWidth / worldDimension.width / lngFraction) / Math.LN2)
				: zoomMax;

		return Math.min(latZoom, lngZoom, zoomMax);
	}

	function moveCameraToBounds(bounds) {
		var camera = {
			center: bounds.getCenter(),
			zoom: getBoundsZoom(bounds),
		};

		if (typeof map.moveCamera === 'function') {
			map.moveCamera(camera);
			return;
		}

		map.setCenter(camera.center);
		map.setZoom(camera.zoom);
	}

	function fitBoundsCleanly(bounds) {
		var dramaticChange = !initialMapRevealComplete || isDramaticBoundsChange(bounds);

		withSuppressedBoundsFilter(function () {
			if (dramaticChange && initialMapRevealComplete) {
				moveCameraToBounds(bounds);
			} else {
				map.fitBounds(bounds);
			}
		});

		if (!initialMapRevealComplete) {
			revealMapAfterIdle();
		}
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
				if (!initialMapRevealComplete) {
					setMapOpacity('0');
				}
				withSuppressedBoundsFilter(function () {
					map.setCenter(
						new google.maps.LatLng(
							googleMapsDefaultLatitude,
							googleMapsDefaultLongitude
						)
					);
					map.setZoom(8);
				});
				if (!initialMapRevealComplete) {
					revealMapAfterIdle();
				}
			}
			return;
		}

		var bounds = addMarkers();

		markers.forEach(function (marker) {
			marker.setMap(map);
		});

		if (!preserveCurrentBounds && shouldFitBounds(bounds)) {
			window.requestAnimationFrame(function () {
				fitBoundsCleanly(bounds);
			});
		} else if (!initialMapRevealComplete) {
			revealMapAfterIdle();
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
		var markerId = String($(this).attr('data-id'));
		var marker = markersById[markerId];

		if (marker) {
			google.maps.event.trigger(marker, 'mouseover');
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
