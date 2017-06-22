//contains JS for the Google Maps integration

function clearMapMarkers(map) {
    if (typeof map.markers != 'undefined') {
        for (var i = 0; i < map.markers.length; i++) {
            map.markers[i].setMap(null);
            google.maps.event.clearListeners(map.markers[i], 'click');
        }
    }
    map.markers = [];
}

// returns google maps marker image URL with certain color
function markerIconUrl(color) {
    switch (color) {
        case 'red':
            return 'https://mt.googleapis.com/vt/icon/name=icons/spotlight/spotlight-poi.png';
        case 'blue':
            return 'https://mt.google.com/vt/icon?name=icons/spotlight/spotlight-waypoint-blue.png';
        case 'purple':
            return 'https://mt.google.com/vt/icon/name=icons/spotlight/spotlight-ad.png';
        default:
            return 'https://mt.googleapis.com/vt/icon/name=icons/spotlight/spotlight-poi.png';
    }
}

function codeAddress(data, map, bounds, infowindow, callback) {

    // putting down markers
    for (i = 0; i < data.length; i++) {
        if (data[i].location !== 'online' && data[i].location_lat && data[i].location_lng) {
            var latlng = new google.maps.LatLng(data[i].location_lat, data[i].location_lng),
                infotext = renderInfoText(data[i]),
                marker = new google.maps.Marker({
                    map: map,
                    position: latlng,
                    icon: data[i].date ? markerIconUrl('red') : markerIconUrl('blue'),
                    title: infotext
                });
            map.markers.push(marker);
            bounds.extend(marker.getPosition());

            // searching for the same location, merging
            for (var u = 0; u < map.markers.length - 1; u++) {
                if (Math.abs(data[i].location_lng - map.markers[u].getPosition().lng()) < 0.000001 &&
                    Math.abs(data[i].location_lat - map.markers[u].getPosition().lat()) < 0.000001) {
                    infotext =  map.markers[u].getTitle() + '<hr/>' + renderInfoText(data[i]);
                    // in case tournament and weekly
                    if (map.markers[u].getIcon() !== marker.getIcon()) {
                        marker.setIcon(markerIconUrl('purple'));
                    }
                }
            }

            // set listener for infowindow
            marker.addListener('click', (function (infotext, infowindow, map, marker) {
                return function() {
                    infowindow.setContent(infotext);
                    infowindow.open(map, marker);
                }
            })(infotext, infowindow, map, marker));
        }
    }
    setZoom(map, bounds);

    // make callback if exists
    typeof callback === 'function' && callback.apply(this, arguments);
}

// setting optimal map zoom
function setZoom(map, bounds) {
    // avoiding to much zoom
    var zoombounds = 0.002;
    if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
        var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + zoombounds, bounds.getNorthEast().lng() + zoombounds);
        var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - zoombounds, bounds.getNorthEast().lng() - zoombounds);
        bounds.extend(extendPoint1);
        bounds.extend(extendPoint2);
    }
    map.fitBounds(bounds);
}

function renderInfoText(data) {
    var html = '<a href="/tournaments/' + data.id + '"><strong>' + data.title + '</strong></a><br/>';
    if (data.date) {
        html = html + '<em>' + data.type + '</em><br/>';
    } else {
        html = html + '<em>recurring event</em><br/>';
    }
    html = html + '<strong>city</strong>: ' + data.location + '<br/>';
    if (data.store !== '') {
        html = html + '<strong>store</strong>: ' + data.store + '<br/>';
    }
    if (data.date) {
        html = html + '<strong>date</strong>: ' + data.date + '<br/>' +
            '<strong>cardpool</strong>: ' + data.cardpool;
    } else {
        html = html + '<strong>recurring</strong>: ' + data.recurring_day;
    }
    if (data.contact !== '') {
        html = html + '<br/><strong>contact</strong>: ' + data.contact;
    }
    return html;
}

function renderPlace(place, marker, map) {
    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
        var bounds = new google.maps.LatLngBounds();
        bounds.union(place.geometry.viewport);
        var zoombounds = 0.002;
        if (Math.abs(bounds.getNorthEast().lat() - bounds.getSouthWest().lat()) < zoombounds ) {
            var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + zoombounds, bounds.getNorthEast().lng() + zoombounds);
            var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - zoombounds, bounds.getNorthEast().lng() - zoombounds);
            bounds.extend(extendPoint1);
            bounds.extend(extendPoint2);
        }
        map.fitBounds(bounds);
        //map.fitBounds(place.geometry.viewport);
    } else {
        map.setCenter(place.geometry.location);
        map.setZoom(15);
    }
    marker.setPosition(place.geometry.location);
    marker.setVisible(true);

    // if we are on the tournament form, refresh infos
    if (document.getElementById('location_place_id')) {
        refreshAddressInfo(place);
    }
}

function refreshAddressInfo(place) {
    // place id
    if (typeof place.place_id !== 'undefined') {
        document.getElementById('location_place_id').value = place.place_id;
    } else {
        document.getElementById('location_place_id').value = '';
    }
    // place name, address
    if (typeof place.types !== 'undefined') {
        // if the store/place has a name
        if ($.inArray('establishment', place.types) > -1 || ($.inArray('store', place.types) > -1)) {
            document.getElementById('store').innerHTML = place.name;
            document.getElementById('location_store').value = place.name;
        } else {
            document.getElementById('store').innerHTML = '';
            document.getElementById('location_store').value = '';
        }
        // if it has an address
        if ($.inArray('street_address', place.types) > -1 || $.inArray('store', place.types) > -1
            || $.inArray('establishment', place.types) > -1) {
            document.getElementById('address').innerHTML = place.formatted_address;
            document.getElementById('location_address').value = place.formatted_address;
        } else {
            document.getElementById('address').innerHTML = '';
            document.getElementById('location_address').value = '';
        }
    } else {
        document.getElementById('store').innerHTML = '';
        document.getElementById('address').innerHTML = '';
        document.getElementById('location_store').value = '';
        document.getElementById('location_address').value = '';
    }
    // country, city, US state
    if (typeof place.address_components !== 'undefined') {
        document.getElementById('country').innerHTML = '';
        document.getElementById('city').innerHTML = '';
        document.getElementById('location_country').value = '';
        document.getElementById('location_city').value = '';
        place.address_components.forEach(function (comp) {
            if (comp.types[0] === 'country') {
                document.getElementById('country').innerHTML = comp.long_name;
                document.getElementById('location_country').value = comp.long_name;
            }
            if (comp.types[0] === 'locality') {
                document.getElementById('city').innerHTML = comp.long_name;
                document.getElementById('location_city').value = comp.long_name;
            }
            if (comp.types[0] === 'administrative_area_level_1') {
                document.getElementById('state').innerHTML = comp.long_name;
                document.getElementById('location_state').value = comp.short_name;
            }
        });
        // US fix for city
        if (document.getElementById('city').innerHTML == '') {
            place.address_components.forEach(function (comp) {
                if (comp.types[0] === 'sublocality_level_1') {
                    document.getElementById('city').innerHTML = comp.long_name;
                    document.getElementById('location_city').value = comp.long_name;
                }
            });
        }
        // non-US location has no state
        if (document.getElementById('country').innerHTML !== 'United States') {
            document.getElementById('state').innerHTML = '';
            document.getElementById('location_state').value = '';
        }
    }
    //coordinates
    document.getElementById('location_lat').value = place.geometry.location.lat();
    document.getElementById('location_long').value = place.geometry.location.lng();
}

// hides recurring events from map if needed
function hideRecurringMap(map) {
    var bounds = new google.maps.LatLngBounds();
    if (document.getElementById('hide-recurring-map').checked) {
        for (var i = 0; i < map.markers.length; i++) {
            if (map.markers[i].icon === markerIconUrl('blue')) {
                map.markers[i].setVisible(false);
            } else {
                bounds.extend(map.markers[i].getPosition());
            }
        }
    } else {
        for (var i = 0; i < map.markers.length; i++) {
            map.markers[i].setVisible(true);
            bounds.extend(map.markers[i].getPosition());
        }
    }
    setZoom(map, bounds);
}

// user geolocation
function getLocation() {
    if (userLocation == null) {
        // user not yet located
        $('#loader-locater').removeClass('hidden-xs-up');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(getMyLocation, showGeolocationError, { maximumAge: 600000, timeout: 10000 });
            $('#error-location').addClass('hidden-xs-up');
        } else {
            $('#error-location').removeClass('hidden-xs-up');
            $('#text-location-error').text('Geolocation is not supported by this browser.');
        }
    } else {
        // already found
        zoomNearMe();
    }
}

// error handling for geolocation
function showGeolocationError(error) {
    var errorText;
    switch(error.code) {
        case error.PERMISSION_DENIED:
            errorText = "User denied the request for Geolocation.";
            break;
        case error.POSITION_UNAVAILABLE:
            errorText = "Location information is unavailable.";
            break;
        case error.TIMEOUT:
            errorText = "The request to get user location timed out.";
            break;
        case error.UNKNOWN_ERROR:
            errorText = "An unknown error occurred.";
            break;
    }
    $('#text-location-error').text(errorText);
    $('#error-location').removeClass('hidden-xs-up');
    $('#loader-locater').addClass('hidden-xs-up');
}


function getMyLocation(position) {
    // user's location
    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    userLocation = new google.maps.Marker({
        map: map,
        position: latlng,
        icon: {
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            scale: 4
        }
    });

    // find closest location
    var closestMarker,
        dist;
    for (var i = 0; i < map.markers.length; i++) {
        if (map.markers[i].visible) {
            dist = google.maps.geometry.spherical.computeDistanceBetween(userLocation.position, map.markers[i].position) / 1000;
            if (dist < shortestDistance) {
                closestMarker = map.markers[i];
                shortestDistance = dist;
            }
        }
    }
    // console.log(dist, closestMarker);

    // display user's location
    map.markers.push(userLocation);
    $('#loader-locater').addClass('hidden-xs-up');

    // zoom map
    if (shortestDistance < 150) {
        shortestDistance = 150; // minimal distance
    }
    zoomNearMe();
}

// zooms map to user's geolocation
function zoomNearMe() {
    var circle = new google.maps.Circle({radius: shortestDistance * 1000, center: userLocation.position});
    map.fitBounds(circle.getBounds());
}
