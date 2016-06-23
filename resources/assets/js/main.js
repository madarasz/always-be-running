function showLocation() {
    if ($("#tournament_type_id option:selected").html() === 'online event') {
        $('#select_location').addClass('hidden-xs-up');
    } else {
        $('#select_location').removeClass('hidden-xs-up');
    }

}

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

function getTournamentData(filters, callback) {
    $.ajax({
        url: '/api/tournaments?' + filters,
        dataType: "json",
        async: true,
        success: function (data) {
            callback(data);
        }
    });
}

function updateTournamentTable(elementID, columns, emptyMessage, data) {
    var nowdate = nowDate();
    $.each(data, function (index, element) {
        newrow = $('<tr>').appendTo(elementID + ' > tbody');

        // if zero rows
        if (data.length == 0) {
            newrow.append($('<td>', {
                text: emptyMessage,
                colspan: columns.length,
                'class': 'text-xs-center'
            }));
            return 0;
        }

        // title
        if ($.inArray('title', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                text: element.title,
                href: '/tournaments/' + element.id
            })));
        }
        // date
        if ($.inArray('date', columns) > -1) {
            newrow.append($('<td>', {
                text: element.date
            }));
        }
        // location
        if ($.inArray('location', columns) > -1) {
            newrow.append($('<td>', {
                text: element.location
            }));
        }
        // cardpool
        if ($.inArray('cardpool', columns) > -1) {
            newrow.append($('<td>', {
                text: element.cardpool
            }));
        }
        // type
        if ($.inArray('type', columns) > -1) {
            newrow.append($('<td>').append($('<em>', {
                text: element.type
            })));
        }
        // approved
        if ($.inArray('approval', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.approved === null) {
                cell.append($('<span>', {
                    text: 'pending',
                    'class': 'label label-warning'
                }));
            } else if (element.approved) {
                cell.append($('<span>', {
                    text: 'approved',
                    'class': 'label label-success'
                }));
            } else {
                cell.append($('<i>', {
                    'aria-hidden': true,
                    'class': 'fa fa-thumbs-down text-danger'
                }), ' ', $('<span>', {
                    text: 'rejected',
                    'class': 'label label-danger'
                }));
            }
        }
        // claim
        if ($.inArray('user_claim', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.user_claim) {
                cell.append($('<span>', {
                    text: 'claimed',
                    'class': 'label label-success'
                }));
            } else if (element.concluded) {
                cell.append($('<i>', {
                    'aria-hidden': true,
                    'class': 'fa fa-clock-o text-danger'
                }), ' ', $('<span>', {
                    text: 'please claim',
                    'class': 'label label-danger'
                }));
            } else {
                cell.append($('<span>', {
                    text: 'registered',
                    'class': 'label label-info'
                }));
            }
        }
        // conclusion
        if ($.inArray('conclusion', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.concluded) {
                cell.append($('<span>', {
                    text: 'concluded',
                    'class': 'label label-success'
                }));
            } else if (element.date <= nowdate) {
                cell.append($('<i>', {
                    'aria-hidden': true,
                    'class': 'fa fa-clock-o text-danger'
                }), ' ', $('<span>', {
                    text: 'due, pls update',
                    'class': 'label label-danger'
                }));
            } else {
                cell.append($('<span>', {
                    text: 'not yet',
                    'class': 'label label-info'
                }));
            }
        }
        // players
        if ($.inArray('players', columns) > -1) {
            newrow.append($('<td>', {
                text: element.concluded ? element.players_count : element.registration_count,
                'class': 'text-xs-center'
            }));
        }
        // claims
        if ($.inArray('claims', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.claim_conflict) {
                cell.append($('<i>', {
                    'title': 'conflict',
                    'class': 'fa fa-exclamation-triangle text-danger'
                }), ' ');
            }

            cell.append(element.claim_count);

        }
        // action_edit
        if ($.inArray('action_edit', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                'class': 'btn btn-primary btn-xs',
                href: '/tournaments/' + element.id + '/edit'
            }).append($('<i>', {
                'class': 'fa fa-pencil',
                'aria-hidden': true
            }), ' update')));
        }
        // action_approve
        if ($.inArray('action_approve', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                'class': 'btn btn-success btn-xs',
                href: '/tournaments/' + element.id + '/approve'
            }).append($('<i>', {
                'class': 'fa fa-thumbs-up',
                'aria-hidden': true
            }), ' approve')));
        }
        // action_reject
        if ($.inArray('action_reject', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                'class': 'btn btn-danger btn-xs',
                href: '/tournaments/' + element.id + '/reject'
            }).append($('<i>', {
                'class': 'fa fa-thumbs-down',
                'aria-hidden': true
            }), ' reject')));
        }
        // action_restore
        if ($.inArray('action_restore', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                'class': 'btn btn-primary btn-xs',
                href: '/tournaments/' + element.id + '/restore'
            }).append($('<i>', {
                'class': 'fa fa-recycle',
                'aria-hidden': true
            }), ' restore')));
        }
        // action_delete
        if ($.inArray('action_delete', columns) > -1) {
            newrow.append($('<td>').append($('<form>', {
                method: 'POST',
                action: '/tournaments/' + element.id
            }).append($('<input>', {
                name: '_method',
                type: 'hidden',
                value: 'DELETE'
            }), $('<input>', {
                name: '_token',
                type: 'hidden',
                value: '{{ csrf_token() }}'
            }), $('<button>', {
                type: 'submit',
                'class': 'btn btn-danger btn-xs'
            }).append($('<i>', {
                'class': 'fa fa-trash',
                'aria-hidden': true
            }), ' delete'))));
        }

    }, columns, emptyMessage);
}

function convertDateForCalendar(dataString) {
    return dataString.substr(5,2) + '-' + dataString.substr(8,2) + '-' + dataString.substr(0,4);
}

function nowDate() {
    var date = new Date(),
        day = date.getDate(),
        monthIndex = date.getMonth() + 1,
        result = date.getFullYear() + '.';
    if (monthIndex < 10) {
        result = result + '0';
    }
    result = result + monthIndex + '.';
    if (day < 10) {
        result = result + '0';
    }
    result = result + day + '.';
    return result;
}

function leadingZero(number) {
    if (number < 10) {
        return '0' + number;
    } else {
        return number;
    }
}

function updateTournamentCalendar(data) {
    var calendardata = {};
    $.each(data, function (index, element) {
        var entry = '<a href="/tournaments/' + element.id + '">' + element.title + '</a><small>' + element.location + '</small>';
        if (typeof calendardata[convertDateForCalendar(element.date)] == "undefined") {
            calendardata[convertDateForCalendar(element.date)] = [entry];
        } else {
            calendardata[convertDateForCalendar(element.date)].push(entry);
        }
    });

    $(function() {

        var $wrapper = $( '#custom-inner' ),
            $calendar = $( '#calendar' ),
            cal = $calendar.calendario( {
                onDayClick : function( $el, $contentEl, dateProperties ) {

                    if( $contentEl.content.length > 0 ) {
                        showEvents( $contentEl.content, dateProperties );
                    }

                },
                caldata : calendardata,
                displayWeekAbbr : true
            } ),
            $month = $( '#custom-month' ).html( cal.getMonthName() ),
            $year = $( '#custom-year' ).html( cal.getYear() );

        $( '#custom-next' ).on( 'click', function() {
            cal.gotoNextMonth( updateMonthYear );
        } );
        $( '#custom-prev' ).on( 'click', function() {
            cal.gotoPreviousMonth( updateMonthYear );
        } );

        function updateMonthYear() {
            $month.html( cal.getMonthName() );
            $year.html( cal.getYear() );
        }

        function showEvents( $contentEl, dateProperties ) {

            hideEvents();

            var $events = $( '<div id="custom-content-reveal" class="custom-content-reveal"><h4>Tournaments for ' + dateProperties.year + '.' + leadingZero(dateProperties.month) + '.' + leadingZero(dateProperties.day) + '.</h4></div>' ),
                $close = $( '<span class="custom-content-close"></span>' ).on( 'click', hideEvents );

            $events.append( $contentEl , $close ).insertAfter( $wrapper );

            setTimeout( function() {
                $events.css( 'top', '0%' );
            }, 10 );

        }
        function hideEvents() {

            var $events = $( '#custom-content-reveal' );
            if( $events.length > 0 ) {

                $events.css( 'top', '100%' );
                $events.remove();

            }

        }

    });
}

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

function updateDiscover(filter, map, geocoder) {
    $('.loader').removeClass('hidden-xs-up');
    $('#discover-table').find('tbody').empty();
    getTournamentData(filter, function(data) {
        $('.loader').addClass('hidden-xs-up');
        updateTournamentTable('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'], 'no tournaments to show', data);
        updateTournamentCalendar(data);
        codeAddress(data, map, geocoder, infowindow);
    });
}

function filterDiscover(default_filter, map, geocoder, infowindow) {
    var filter = default_filter,
        type = document.getElementById('tournament_type_id').value,
        countrySelector = document.getElementById('location_country'),
        stateSelector = document.getElementById('location_state'),
        country = countrySelector.options[parseInt(countrySelector.value)+1].innerHTML,
        state = stateSelector.options[parseInt(stateSelector.value)+1].innerHTML;
    if (type > 0) {
        filter = filter + '&type=' + type;
    }
    if (country !== '---') {
        filter = filter + '&country=' + country;
        if (country === 'United States') {
            $('#select_state').removeClass('hidden-xs-up');
            if (state !== '---') {
                filter = filter + '&state=' + state;
            }
        }
    }
    if (country !== 'United States') {
        $('#select_state').addClass('hidden-xs-up');
    }

    updateDiscover(filter, map, geocoder, infowindow);
}

function conclusionCheck() {
    if (document.getElementById('concluded').checked) {
        document.getElementById('players_number').removeAttribute('disabled');
        $('#pn-req').removeClass('hidden-xs-up');
        document.getElementById('top_number').removeAttribute('disabled');
    } else {
        document.getElementById('players_number').setAttribute('disabled','');
        $('#pn-req').addClass('hidden-xs-up');
        document.getElementById('top_number').setAttribute('disabled','');
    }
}

function renderPlace(place, marker, map) {
    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
        map.fitBounds(place.geometry.viewport);
    } else {
        map.setCenter(place.geometry.location);
        map.setZoom(15);
    }
    marker.setPosition(place.geometry.location);
    marker.setVisible(true);

    refreshAddressInfo(place);
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
        if ($.inArray('establishment', place.types) > -1 || ($.inArray('store', place.types) > -1)) {
            document.getElementById('store').innerHTML = place.name;
            document.getElementById('location_store').value = place.name;
        }
        if ($.inArray('street_address', place.types) > -1 || $.inArray('store', place.types) > -1
            || $.inArray('establishment', place.types) > -1) {
            document.getElementById('address').innerHTML = place.formatted_address;
            document.getElementById('location_address').value = place.formatted_address;
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
