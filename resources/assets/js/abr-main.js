// enable all popovers
$(function () {
    $('[data-toggle="popover"]').popover();
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
    } else {
        $('#overlay-conclusion').addClass('hidden-xs-up');
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

function updateDiscover(filter, map, geocoder) {
    $('.loader').removeClass('hidden-xs-up');
    $('#discover-table').find('tbody').empty();
    getTournamentData(filter, function(data) {
        $('.loader').addClass('hidden-xs-up');
        updateTournamentTable('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'], 'no tournaments to show', '', data);
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
            $('#filter-spacer').addClass('hidden-xs-up');
            if (state !== '---') {
                filter = filter + '&state=' + state;
            }
        }
    }
    if (country !== 'United States') {
        $('#select_state').addClass('hidden-xs-up');
        $('#filter-spacer').removeClass('hidden-xs-up');
    }

    updateDiscover(filter, map, geocoder, infowindow);
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
