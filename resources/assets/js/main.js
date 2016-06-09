function showDiv(target, sourcebox) {
    if (document.getElementById(sourcebox).checked) {
        $(target).removeClass('hidden');
    } else {
        $(target).addClass('hidden');
    }
}

function showUsState() {
    if ($("#location_country option:selected").html() === 'United States') {
        $('#select_state').removeClass('hidden');
    } else {
        $('#select_state').addClass('hidden');
    }
}

function showLocation() {
    if ($("#tournament_type_id option:selected").html() === 'online event') {
        $('#select_location').addClass('hidden');
    } else {
        $('#select_location').removeClass('hidden');
    }

}

function calculateAddress(country, state, city, store, address) {
    var q = country;
    if (state !== '') {
        q = q + ', ' + state;
    }
    if (city !== '') {
        q = q + ', ' + city;
    }
    if (address !== '') {
        q = q + ', ' + address;
    } else {
        if (store !== '') {
            q = q + ' ' + store;
        }
    }
    return q;
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
    $(elementID).find('tbody').empty();
    $.each(data, function (index, element) {
        newrow = $('<tr>').appendTo(elementID + ' > tbody');

        // if zero rows
        if (data.length == 0) {
            newrow.append($('<td>', {
                text: emptyMessage,
                colspan: columns.length,
                'class': 'text-center'
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
        // approved
        if ($.inArray('approval', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-center'
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
                'class': 'text-center'
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
                'class': 'text-center'
            }).appendTo(newrow);

            if (element.concluded) {
                cell.append($('<span>', {
                    text: 'concluded',
                    'class': 'label label-success'
                }));
            } else if (element.date <= '{{ $nowdate }}') {
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
                'class': 'text-center'
            }));
        }
        // claims
        if ($.inArray('claims', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-center'
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
            }), ' edit')));
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

function codeAddress(data, map, geocoder) {
    var bounds = new google.maps.LatLngBounds();
    var u = 0;
    for (var i = 0; i < data.length; i++) {
        geocoder.geocode({'address': data[i].location_full}, function (results, status) {

            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location,
                    title: data[u].title
                });
                bounds.extend(marker.getPosition());

            } else {
                console.log('Geocode was not successful for the following address: ' + data[u].location_full);
            }
            u++;
            if (u == data.length) {
                map.fitBounds(bounds);
            }
        });
    }
}

