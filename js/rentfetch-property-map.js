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
                    map: map,
                    title: title,
                    icon: markerImage,
                });
            } else {
                // if there's no custom icon, just use the google default
                marker = new google.maps.Marker({
                    position: theposition,
                    map: map,
                    title: title,
                });
            }

            bounds.extend(theposition);
            map.fitBounds(bounds);

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
            });

            // We want the click event to do most of the same stuff as the hover, so that we can click on a .type-properties
            google.maps.event.addListener(marker, 'mouseover', function () {
                for (let i = 0; i < markers.length; i++) {
                    markers[i]['infowindow'].close(map, this);
                }

                this['infowindow'].open(map, this);

                $('.type-properties').removeClass('active');
                $('.type-properties[data-id=' + i + ']').addClass('active');
            });

            markers.push(marker);
        }
    }
    function resetMap() {
        // Clear markers from the map
        for (let i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }

        // Empty the markers array
        markers = [];

        renderMap();
        getLocations();
        addMarkers();
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

    $(document).ajaxComplete(function () {
        resetMap();
    });
});
