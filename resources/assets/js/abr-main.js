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
            $('#overlay-cardpool').removeClass('hidden-xs-up');
    } else {
        document.getElementById('date').removeAttribute('disabled');
        $('#req-date').removeClass('hidden-xs-up');
        document.getElementById('date').setAttribute('required','');
        $('#overlay-cardpool').addClass('hidden-xs-up');
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
    $(table + '-loader').removeClass('hidden-xs-up');
    $(table).find('tbody').empty();

    getTournamentData(filter, function(data) {
        updateTournamentTable(table, columns, 'no tournaments to show', '', data);
        updateTournamentCalendar(data);
        codeAddress(data, map, bounds, infowindow, callback);
    });
}

// update filter settings for the Upcoming page
function filterDiscover(default_filter, default_country, map, infowindow) {
    var filter = default_filter,
        recur_filter = 'approved=1&recur=1',
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
        recur_filter = recur_filter + '&country=' + country;
        $('#filter-country').addClass('active-filter');
        if (country === 'United States') {
            $('#filter-state').removeClass('hidden-xs-up');
            $('#filter-spacer').addClass('hidden-xs-up');
            // state filtering
            if (state !== '---') {
                filter = filter + '&state=' + state;
                recur_filter = recur_filter + '&state=' + state;
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
    // user's default country
    if (countrySelector.value == default_country) {
        $('#label-default-country').removeClass('hidden-xs-up');
    } else {
        $('#label-default-country').addClass('hidden-xs-up');
    }

    clearMapMarkers(map);
    var bounds = new google.maps.LatLngBounds();
    calendardata = {};
    // get tournaments
    updateDiscover('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'], filter, map, bounds,
        infowindow, function() {
            // get weekly events
            updateDiscover('#recur-table', ['title', 'location', 'recurday'], recur_filter, map, bounds, infowindow, function() {
                drawCalendar(calendardata);
                hideRecurring();
                updatePaging('discover-table');
                updatePaging('recur-table');
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

function hideNonRequired() {
    if (document.getElementById('hide-non').checked) {
        $('.hide-nonrequired').addClass('hidden-xs-up');
    } else {
        $('.hide-nonrequired').removeClass('hidden-xs-up');
    }
}

// toggle manually adding entries on tournament view page
function toggleEntriesEdit(value) {
    if (value) {
        $('#section-edit-entries').removeClass('hidden-xs-up');
        $('.delete-anonym').removeClass('hidden-xs-up');
        $('#button-done-entries').removeClass('hidden-xs-up');
        $('#button-edit-entries').addClass('hidden-xs-up');
        recalculateDeckNames();
    } else {
        $('#section-edit-entries').addClass('hidden-xs-up');
        $('.delete-anonym').addClass('hidden-xs-up');
        $('#button-done-entries').addClass('hidden-xs-up');
        $('#button-edit-entries').removeClass('hidden-xs-up');
    }
}

// toggle adding videos on tournament view page
function toggleVideoAdd(value) {
    if (value) {
        $('#section-add-videos').removeClass('hidden-xs-up');
        $('#button-done-videos').removeClass('hidden-xs-up');
        $('#button-add-videos').addClass('hidden-xs-up');
    } else {
        $('#section-add-videos').addClass('hidden-xs-up');
        $('#button-done-videos').addClass('hidden-xs-up');
        $('#button-add-videos').removeClass('hidden-xs-up');
    }
}

// toggle video player
function watchVideo(videoId) {
    if(videoId) {
        $('#section-watch-video').removeClass('hidden-xs-up')
        $('#section-video-player').html('<iframe width="480" height="270" src="//www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>')
        $('html, body').animate({
            scrollTop: $("#section-watch-video").offset().top - 60
        }, 500);
    } else {
        $('#section-watch-video').addClass('hidden-xs-up')
        $('#section-video-player').empty()
    }
}

function showVideoList(show) {
    if(show) {
        $('#showVideoList').addClass('hidden-xs-up');
        $('#hideVideoList').removeClass('hidden-xs-up');
        $('.hide-video').removeClass('hidden-xs-up');
    } else {
        $('#showVideoList').removeClass('hidden-xs-up');
        $('#hideVideoList').addClass('hidden-xs-up');
        $('.hide-video').addClass('hidden-xs-up');
    }
}

// recalculating deck names from IDs while adding manually entries
function recalculateDeckNames() {
    var corp = document.getElementById('corp_deck_identity'),
        runner = document.getElementById('runner_deck_identity');
    document.getElementById('corp_deck_title').value = shortenID(corp.options[corp.selectedIndex].text);
    document.getElementById('runner_deck_title').value = shortenID(runner.options[runner.selectedIndex].text);
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
            if (data.organizeAlert.total) {
                document.getElementById('nav-organize').setAttribute("data-badge", data.organizeAlert.total);
                if ($('#notif-conclude').length) {
                    if (data.organizeAlert.concludeAlert) {
                        $('#notif-conclude').removeClass('hidden-xs-up');
                        document.getElementById('notif-conclude').setAttribute("data-badge", data.organizeAlert.concludeAlert);
                    }
                    if (data.organizeAlert.incompleteAlert) {
                        $('#notif-incomplete').removeClass('hidden-xs-up');
                        document.getElementById('notif-incomplete').setAttribute("data-badge", data.organizeAlert.incompleteAlert);
                    }
                    if (data.organizeAlert.unknownCardpoolAlert) {
                        $('#notif-cardpool').removeClass('hidden-xs-up');
                        document.getElementById('notif-cardpool').setAttribute("data-badge", data.organizeAlert.unknownCardpoolAlert);
                    }
                }
            }
            // admin page alerts
            if (data.adminAlerts && data.adminAlerts.total) {
                document.getElementById('nav-admin').setAttribute("data-badge", data.adminAlerts.total);
                if ($('#notif-tournament').length) {
                    $('#notif-tournament').removeClass('hidden-xs-up');
                    if (data.adminAlerts.pendingAlerts) {
                        $('#pending-title').addClass('notif-red notif-badge-page').attr('data-badge', data.adminAlerts.pendingAlerts);
                    }
                    if (data.adminAlerts.conflictAlerts) {
                        $('#conflict-title').addClass('notif-red notif-badge-page').attr('data-badge', data.adminAlerts.conflictAlerts);
                    }
                    document.getElementById('tabf-tournament').setAttribute("data-badge", data.adminAlerts.total);
                }
            }
            // personal page alerts
            if (data.personalAlerts.total) {
                document.getElementById('nav-personal').setAttribute("data-badge", data.personalAlerts.total);
                if ($('#notif-toclaim').length) {
                    if (data.personalAlerts.toClaimAlert) {
                        $('#notif-toclaim').removeClass('hidden-xs-up');
                        document.getElementById('notif-toclaim').setAttribute("data-badge", data.personalAlerts.toClaimAlert);
                    }
                    if (data.personalAlerts.brokenClaimAlert) {
                        $('#notif-brokenclaim').removeClass('hidden-xs-up');
                        document.getElementById('notif-brokenclaim').setAttribute("data-badge", data.personalAlerts.brokenClaimAlert);
                    }
                }
            }
            // profile page alerts
            if (data.profileAlerts) {
                document.getElementById('nav-profile').setAttribute("data-badge", data.profileAlerts);
                if ($('#notif-profile').length) {
                    $('#notif-profile').removeClass('hidden-xs-up');
                    document.getElementById('notif-profile').setAttribute("data-badge", data.profileAlerts);
                    // set badges to 'seen'
                    if (document.getElementById('nav-profile').classList.contains('active')) {
                        $.post('/api/badgesseen/' + window.location.href.split("/").pop(),
                            { '_token': document.getElementsByName('_token')[0].value});
                    }
                }
            }
        }
    });
}

function factionCodeToFactionTitle(code) {
    switch (code) {
        case '' : return '--- not set ---'
        case 'weyland-cons': return 'Weyland Consortium';
        case 'haas-bioroid': return 'Haas-Bioroid';
        case 'sunny-lebeau': return 'Sunny Lebeau';
    }
    return code.charAt(0).toUpperCase() + code.substr(1);

}

// converts string to URL friendly string
function convertToURLString (input) {
    var output = input.toLowerCase();
    output = output.replace(/[^a-z0-9_\s-]/g, "");
    output = output.replace(/[\s-]+/g, " ");
    output = output.replace(/[\s_]/g, "-");
    return output;
}

// sometimes you need to go back, use with caution
function convertFromURLString (input) {
    return toTitleCase(input.replace(/-/g, " "));
}

// capitalize each first letter of each word
function toTitleCase(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

// collects options from html form select element
function collectOptions(id) {
    var resultOptions = {};
    var options = document.getElementById(id).options;
    for (var i = 0, len = options.length; i < len; i++) {
        resultOptions[convertToURLString(options[i].text)] = options[i].value;
    }
    return resultOptions;
}
