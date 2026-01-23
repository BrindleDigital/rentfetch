jQuery(document).ready(function ($) {
	var map;
	var locationsArray = [];
	var markers = [];

	//* Vars from localization
	// grab the marker image from localization
	var markerImage = options.marker_url;

	var google_maps_default_longitude = options.google_maps_default_longitude;
	var google_maps_default_latitude = options.google_maps_default_latitude;

	// grab the styles from localization and convert the php array to json
	var mapStyle = options.json_style;
	mapsStyle = JSON.stringify(mapStyle);

	function renderMap() {
		var myLatlng = new google.maps.LatLng(
			google_maps_default_latitude,
			google_maps_default_longitude
		);

		var mapOptions = {
			zoom: 8,
			minZoom: 5,
			maxZoom: 16,
			center: myLatlng,
			styles: mapStyle,
			disableDefaultUI: true, // removes the satellite/map selection (might also remove other stuff)
			// scaleControl: true,
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.RIGHT_TOP,
			},
			fullscreenControl: false,
		};

		map = new google.maps.Map(document.getElementById('map'), mapOptions);
	}

	function getLocations() {
		// reset the array
		locationsArray = [];

		// get the positions
		$('#response .type-properties').each(function () {
			lat = $(this).attr('data-latitude');
			long = $(this).attr('data-longitude');
			title = $(this).find('h3').text();
			content = $(this).find('.property-in-map').html();
			id = $(this).attr('data-id');
			locationsArray.push([lat, long, title, content, id]);
		});
	}

	function addMarkers() {
		var bounds = new google.maps.LatLngBounds();

		for (let i = 0; i < locationsArray.length; i++) {
			var latitude = locationsArray[i][0];
			var longitude = locationsArray[i][1];
			var title = locationsArray[i][2];
			var content = locationsArray[i][3];
			var id = locationsArray[i][4];
			var label = title;
			var theposition = new google.maps.LatLng(latitude, longitude);

			var marker;
			if (typeof markerImage !== 'undefined') {
				// if there's a custom marker set, use that
				marker = new google.maps.Marker({
					position: theposition,
					map: null, // Don't add to map yet
					title: title,
					icon: markerImage,
				});
			} else {
				// if there's no custom icon, just use the google default
				marker = new google.maps.Marker({
					position: theposition,
					map: null, // Don't add to map yet
					title: title,
				});
			}

			bounds.extend(theposition);

			marker['infowindow'] = new google.maps.InfoWindow({
				content:
					'<div class="map-property-popup" id="overlay-' +
					id +
					'">' +
					content +
					'</div>',
			});

			// We want the click event to do most of the same stuff as the hover, so that we can click on a .type-properties
			google.maps.event.addListener(marker, 'click', function () {
				for (let i = 0; i < markers.length; i++) {
					markers[i]['infowindow'].close(map, this);
				}

				this['infowindow'].open(map, this);

				$('.type-properties').removeClass('active');
				$('.type-properties[data-id=' + i + ']').addClass('active');

				scrollToActiveProperty(i);

				setTimeout(function () {
					$('.type-properties').removeClass('active');
				}, 1000);
			});

			// We want the click event to do most of the same stuff as the hover, so that we can click on a .type-properties
			google.maps.event.addListener(marker, 'mouseover', function () {
				for (let i = 0; i < markers.length; i++) {
					markers[i]['infowindow'].close(map, this);
				}

				this['infowindow'].open(map, this);

				$('.type-properties').removeClass('active');
				// $('.type-properties[data-id=' + i + ']').addClass('active');
			});

			markers.push(marker);
		}

		// Return the bounds so resetMap can handle the transition
		return bounds;
	}
	function resetMap() {
		// At the start of resetMap, reset the hooks flag
		window.hooksRan = false;

		// Only render a new map if one doesn't exist
		if (!map) {
			renderMap();
		}

		// Get new locations first
		getLocations();

		// Store reference to old markers
		var oldMarkers = markers.slice(); // Create a copy of the current markers array

		// Reset markers array for new markers
		markers = [];

		// Create new markers and get their bounds
		var newBounds = addMarkers();

		// If we have new locations, animate to new bounds smoothly
		if (locationsArray.length > 0) {
			// Show new markers before zoom animation
			for (let i = 0; i < markers.length; i++) {
				markers[i].setMap(map);
			}

			// Animate to new bounds
			map.fitBounds(newBounds);

			// Wait for animation to complete (fitBounds animation typically takes ~500-1000ms)
			setTimeout(function () {
				// Remove old markers after animation completes
				for (let i = 0; i < oldMarkers.length; i++) {
					oldMarkers[i].setMap(null);
				}

				// Run hooks after transition is complete
				if (typeof window.rentFetchMapHooks === 'undefined') {
					window.rentFetchMapHooks = [];
				}

				if (!window.hooksRan) {
					window.rentFetchMapHooks.forEach((hook) => {
						if (typeof hook === 'function') {
							hook(map, markers);
						}
					});
					window.hooksRan = true;
				}
			}, 600); // Wait 600ms for fitBounds animation to complete
		} else {
			// No locations found, just clear old markers and center on default
			for (let i = 0; i < oldMarkers.length; i++) {
				oldMarkers[i].setMap(null);
			}

			var myLatlng = new google.maps.LatLng(
				google_maps_default_latitude,
				google_maps_default_longitude
			);
			map.setCenter(myLatlng);
			map.setZoom(8);
		}
	}

	function openMarkerOnGridHover() {
		let markerIndex = parseInt($(this).attr('data-id')); // Parse as integer

		if (markerIndex >= 0 && markerIndex < markers.length) {
			google.maps.event.trigger(markers[markerIndex], 'mouseover');
		}
	}

	$(document).on('mouseenter', '.type-properties', openMarkerOnGridHover);

	// function activeOnClick() {
	//     $('.type-properties').removeClass('active');
	//     $(this).addClass('active');
	// }

	// $(document).on('click touchstart', '.type-properties', activeOnClick);

	// Listen for property search completion
	$(document).on('rentfetchPropertySearchComplete', function () {
		resetMap();
	});

	function initMapFlow() {
		if (!$('#map').length) {
			return;
		}

		renderMap();
		getLocations();
		var initialBounds = addMarkers();

		// Show initial markers immediately
		if (locationsArray.length > 0) {
			map.fitBounds(initialBounds);
			for (let i = 0; i < markers.length; i++) {
				markers[i].setMap(map);
			}
		}
	}

	function handleGoogleMapsReady() {
		initMapFlow();
	}

	window.rentfetchGoogleMapsLoaded = window.rentfetchGoogleMapsLoaded || function () {
		$(document).trigger('rentfetchGoogleMapsReady');
	};

	$(document).on('rentfetchGoogleMapsReady', handleGoogleMapsReady);

	// Initialize map on page load if the API is already available
	if (window.google && window.google.maps && typeof window.google.maps.LatLng === 'function') {
		handleGoogleMapsReady();
	}
});
