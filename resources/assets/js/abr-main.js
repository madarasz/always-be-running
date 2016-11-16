// enable all popovers
$(function () {
    $('[data-toggle="popover"]').popover();
});
// enable all tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});


//overlay changes based on tournament type on tournament form
function changeTournamentType() {
    //online event, disable location
    if ($("#tournament_type_id option:selected").html() === 'online event') {
        $('#overlay-location').removeClass('hidden-xs-up');
    } else {
        $('#overlay-location').addClass('hidden-xs-up');
    }

    // non-tournament event, disable conclusion
    if ($("#tournament_type_id option:selected").html() === 'non-tournament event') {
        $('#overlay-conclusion').removeClass('hidden-xs-up');
        $('#overlay-weekly').addClass('hidden-xs-up');
    } else {
        $('#overlay-conclusion').addClass('hidden-xs-up');
        $('#overlay-weekly').removeClass('hidden-xs-up');
    }
    recurCheck();
}

function recurCheck() {
    if ($("#tournament_type_id option:selected").html() === 'non-tournament event' &&
        $("#recur_weekly option:selected").html() !== '- no recurrence -') {
            document.getElementById('date').setAttribute('disabled','');
            $('#req-date').addClass('hidden-xs-up');
            document.getElementById('date').removeAttribute('required');

    } else {
        document.getElementById('date').removeAttribute('disabled');
        $('#req-date').removeClass('hidden-xs-up');
        document.getElementById('date').setAttribute('required','');
    }
}

//var delay = (function(){
//    var timer = 0;
//    return function(callback, ms){
//        clearTimeout (timer);
//        timer = setTimeout(callback, ms);
//    };
//})();

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

function updateDiscover(table, columns, filter, map, bounds, infowindow, callback) {
    $('.loader').removeClass('hidden-xs-up');
    $(table).find('tbody').empty();

    getTournamentData(filter, function(data) {
        $('.loader').addClass('hidden-xs-up');
        updateTournamentTable(table, columns, 'no tournaments to show', '', data);
        updateTournamentCalendar(data);
        codeAddress(data, map, bounds, infowindow, callback);
    });
}

// update filter settings for the Upcoming page
function filterDiscover(default_filter, map, infowindow) {
    var filter = default_filter,
        type = document.getElementById('tournament_type_id').value,
        countrySelector = document.getElementById('location_country'),
        stateSelector = document.getElementById('location_state'),
        country = countrySelector.options[parseInt(countrySelector.value)+1].innerHTML,
        state = stateSelector.options[parseInt(stateSelector.value)+1].innerHTML;
    // type filtering
    if (type > 0) {
        filter = filter + '&type=' + type;
        $('#filter-type').addClass('active-filter');
    } else {
        $('#filter-type').removeClass('active-filter');
    }
    // country filtering
    if (country !== '---') {
        filter = filter + '&country=' + country;
        $('#filter-country').addClass('active-filter');
        if (country === 'United States') {
            $('#filter-state').removeClass('hidden-xs-up');
            $('#filter-spacer').addClass('hidden-xs-up');
            // state filtering
            if (state !== '---') {
                filter = filter + '&state=' + state;
                $('#filter-state').addClass('active-filter');
            } else {
                $('#filter-state').removeClass('active-filter');
            }
        }
    } else {
        $('#filter-country').removeClass('active-filter');
    }
    // state filter only visible for US
    if (country !== 'United States') {
        $('#filter-state').addClass('hidden-xs-up');
        $('#filter-spacer').removeClass('hidden-xs-up');
    }

    clearMapMarkers(map);
    var bounds = new google.maps.LatLngBounds();
    calendardata = {};
    // get tournaments
    updateDiscover('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'], filter+'&recur=0', map, bounds,
        infowindow, function() {
            // get weekly events
            updateDiscover('#recur-table', ['title', 'location', 'recurday'], filter.substr(filter.indexOf('&'))+'&recur=1', map, bounds, infowindow, function() {
                drawCalendar(calendardata);
                hideRecurring();
            });
        });
}

function conclusionCheck() {
    if (document.getElementById('concluded').checked) {
        document.getElementById('players_number').removeAttribute('disabled');
        $('.req-conclusion').removeClass('hidden-xs-up');
        document.getElementById('top_number').removeAttribute('disabled');
    } else {
        document.getElementById('players_number').setAttribute('disabled','');
        $('.req-conclusion').addClass('hidden-xs-up');
        document.getElementById('top_number').setAttribute('disabled','');
    }
}

function percentageToString(fraction) {
    return Math.round(fraction * 1000) / 10 + '%';
}

// convert faction_code to color code
function factionCodeToColor(faction) {
    switch (faction) {
        case "shaper":
            return "#7EAC39";
            break;
        case "anarch":
            return "#AC5439";
            break;
        case "criminal":
            return "#3962AC";
            break;
        case "haas-bioroid":
            return "#702871";
            break;
        case "weyland-cons":
            return "#1B654F";
            break;
        case "weyland-consortium":
            return "#1B654F";
            break;
        case "jinteki":
            return "#8f1E0A";
            break;
        case "nbn":
            return "#CB953A";
            break;
        case "apex":
            return "darkred";
            break;
        case "sunny-lebeau":
            return "black";
            break;
        case "adam":
            return "darkgoldenrod";
            break;
        default:
            return "grey";
    }
}

// short version of ID titles
function shortenID(fulltitle) {
    if (!fulltitle || fulltitle.length == 0) {
        return "unknown";
    }

    // when you need the part after ":"
    var special_cases = ['Haas-Bioroid', 'Jinteki:', 'NBN', 'Weyland Consortium'],
        found = false;

    for (var i = 0, len = special_cases.length; i < len; i++) {
        if (fulltitle.indexOf(special_cases[i]) !== -1) {
            found = true;
            break;
        }
    }

    if (found) {
        return fulltitle.split(':')[1]; // after :
    } else {
        return fulltitle.split(':')[0]; // before :
    }
}

// checkbox for hiding recurring events on Upcoming page calendar
function hideRecurring() {
    if (document.getElementById('hide-recurring').checked) {
        $('.fc-content').each(function() {
            var recurringCount = $(this).find('.recurring').length;
            if (recurringCount && recurringCount == $(this).find('.fc-calendar-event').length) {
                $(this).addClass('fc-content-hidden');
            }
            $(this).find('.recurring-block').addClass('hidden-xs-up');
        });

    } else {
        $('.fc-content-hidden').removeClass('fc-content-hidden');
        $('.recurring-block').removeClass('hidden-xs-up');
    }
}

// update navbar notification badges
function updateNavBadges() {
    // navbar notifications
    $.ajax({
        url: '/api/useralert',
        dataType: "json",
        async: true,
        success: function (data) {
            // organize page alerts
            if (data.organizeAlert) {
                document.getElementById('nav-organize').setAttribute("data-badge", data.organizeAlert);
                if ($('#notif-organize').length) {
                    $('#notif-organize').removeClass('hidden-xs-up');
                    document.getElementById('notif-organize').setAttribute("data-badge", data.organizeAlert);
                }
            }
            if (data.adminAlerts) {
                document.getElementById('nav-admin').setAttribute("data-badge", data.adminAlerts);
                if ($('#notif-approve').length) {
                    $('#notif-approve').removeClass('hidden-xs-up');
                    document.getElementById('notif-approve').setAttribute("data-badge", data.adminAlerts);
                }
            }
            if (data.personalAlerts) {
                document.getElementById('nav-personal').setAttribute("data-badge", data.personalAlerts);
                if ($('#notif-personal').length) {
                    $('#notif-personal').removeClass('hidden-xs-up');
                    document.getElementById('notif-personal').setAttribute("data-badge", data.personalAlerts);
                }
            }
        }
    });
}
