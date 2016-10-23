//contains JS for the Google Maps integration

function codeAddress(data, map, geocoder, infowindow) {
    // delete markers
    if (typeof map.markers != 'undefined') {
        for (var i = 0; i < map.markers.length; i++) {
            map.markers[i].setMap(null);
            google.maps.event.clearListeners(map.markers[i], 'click');
        }
    }
    map.markers = [];
    var bounds = new google.maps.LatLngBounds();
    var countAddress = 0;
    for (i = 0; i < data.length; i++) {
        if (data[i].location !== 'online') {
            countAddress++;
        }
    }
    for (i = 0; i < data.length; i++) {
        if (data[i].location !== 'online') {
            countAddress --;
            var address = data[i].address.length > 0 ? data[i].address : data[i].location;
            geocoder.geocode({'address': address}, ownGeocodeCallback(data[i], countAddress == 0, map, bounds, infowindow));
        }
    }
}

function ownGeocodeCallback(data, isLast, map, bounds, infowindow) {
    var geocodeCallback = function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            // create marker
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
            map.markers.push(marker);
            bounds.extend(marker.getPosition());
            // set listener for infowindow
            marker.addListener('click', function () {
                infowindow.setContent(renderInfoText(data));
                infowindow.open(map, marker);
            });
        } else {
            console.log('Geocode was not successful for the following address: ' + data.address + '/' + data.location);
        }
        if (isLast) {
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
    };
    return geocodeCallback;
}

function renderInfoText(data) {
    var html = '<a href="/tournaments/' + data.id + '"><strong>' + data.title + '</strong></a><br/>' +
        '<em>' + data.type + '</em><br/>' +
        '<strong>city</strong>: ' + data.location + '<br/>';
    if (data.store !== '') {
        html = html + '<strong>store</strong>: ' + data.store + '<br/>';
    }
    html = html + '<strong>date</strong>: ' + data.date + '<br/>' +
        '<strong>cardpool</strong>: ' + data.cardpool;
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
        if (document.getElementById('country').innerHTML !== 'United States') {
            document.getElementById('state').innerHTML = '';
            document.getElementById('location_state').value = '';
        }
    }
}
