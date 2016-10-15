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
        $('.req-conclusion').removeClass('hidden-xs-up');
        document.getElementById('top_number').removeAttribute('disabled');
    } else {
        document.getElementById('players_number').setAttribute('disabled','');
        $('.req-conclusion').addClass('hidden-xs-up');
        document.getElementById('top_number').setAttribute('disabled','');
    }
}
