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

function getTournamentData(postfix, callback) {
    $.ajax({
        url: '/api/tournaments' + postfix,
        dataType: "json",
        async: true,
        success: function (data) {
            callback(data);
        }
    });
}

// filter tournament array
function filterTournamentData(data, field, filterValue, includeOnline) {
    for (var i = 0; i < data.length; i++) {
        if (data[i][field] != filterValue &&
            (!includeOnline || data[i]['location'] != 'online') && // include online location
            (field != 'videos' || data[i]['videos'] == 0)) {  // filter for videos
                data.splice(i, 1);
                i--;
        }
    }
}

// filter events on Upcoming page
function filterUpcoming() {
    upcomingDataFiltered = $.extend(true, {}, upcomingDataAll);

    var type = document.getElementById('tournament_type_id').value,
        countrySelector = document.getElementById('location_country'),
        stateSelector = document.getElementById('location_state'),
        country = countrySelector.options[parseInt(countrySelector.value)+1].innerHTML,
        state = stateSelector.options[parseInt(stateSelector.value)+1].innerHTML,
        includeOnline = document.getElementById('include-online').checked;

    // type filtering
    if (type != '---') {
        filterTournamentData(upcomingDataFiltered.tournaments, 'type', type, '');
        $('#filter-type').addClass('active-filter');
    } else {
        $('#filter-type').removeClass('active-filter');
    }
    // country filtering
    if (country !== '---') {
        filterTournamentData(upcomingDataFiltered.tournaments, 'location_country', country, includeOnline);
        filterTournamentData(upcomingDataFiltered.recurring_events, 'location_country', country);
        $('#filter-country').addClass('active-filter');
        $('#filter-online').removeClass('hidden-xs-up');
        if (country === 'United States') {
            $('#filter-state').removeClass('hidden-xs-up');
            $('#filter-spacer').addClass('hidden-xs-up');
            // state filtering
            if (state !== '---') {
                filterTournamentData(upcomingDataFiltered.tournaments, 'location_state', state, includeOnline ? 'online' : '');
                filterTournamentData(upcomingDataFiltered.recurring_events, 'location_state', state, '');
                $('#filter-state').addClass('active-filter');
            } else {
                $('#filter-state').removeClass('active-filter');
            }
        }
    } else {
        $('#filter-country').removeClass('active-filter');
        $('#filter-online').addClass('hidden-xs-up');
    }
    // state filter only visible for US
    if (country !== 'United States') {
        $('#filter-state').addClass('hidden-xs-up');
        $('#filter-spacer').removeClass('hidden-xs-up');
    }
    // user's default country
    if (country == defaultCountry) {
        $('#label-default-country').removeClass('hidden-xs-up');
    } else {
        $('#label-default-country').addClass('hidden-xs-up');
    }

    // refresh tournaments
    displayUpcomingPageTournaments(upcomingDataFiltered);
}

// display upcoming tournaments, recurring events, calendar and map on Upcoming page
function displayUpcomingPageTournaments(data) {
    // empty tables
    $('#discover-table').find('tbody').empty();
    $('#recur-table').find('tbody').empty();
    // empty map
    clearMapMarkers(map);
    var bounds = new google.maps.LatLngBounds();
    // empty calendar
    calendardata = {};

    // upcoming tournaments
    updateTournamentTable('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'],
        'no tournaments to show', '', data.tournaments);
    updateTournamentCalendar(data.tournaments);
    codeAddress(data.tournaments, map, bounds, infowindow);
    // recurring events
    updateTournamentTable('#recur-table', ['title', 'location', 'recurday'],
        'no tournaments to show', '', data.recurring_events);

    // hide or display recurring events
    if (showWeeklyOnCalendar) {
        updateTournamentCalendar(data.recurring_events);
    }
    if (showWeeklyOnMap) {
        codeAddress(data.recurring_events, map, bounds, infowindow);
    }
    hideRecurring();
    hideRecurringMap(map);

    // draw calendar
    drawCalendar(calendardata);
    // enable filters
    $('#button-near-me').prop("disabled", false);
    // update paging
    updatePaging('discover-table');
    updatePaging('recur-table');
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
    $('.popover').popover('hide');
}

// toggle manually adding entries on tournament view page
function toggleEntriesEdit(value) {
    if (value) {
        $('#section-edit-entries').removeClass('hidden-xs-up');
        //$('.delete-anonym').removeClass('hidden-xs-up');
        $('#button-done-entries').removeClass('hidden-xs-up');
        $('#button-edit-entries').addClass('hidden-xs-up');
        recalculateDeckNames('_manual');
    } else {
        $('#section-edit-entries').addClass('hidden-xs-up');
        //$('.delete-anonym').addClass('hidden-xs-up');
        $('#button-done-entries').addClass('hidden-xs-up');
        $('#button-edit-entries').removeClass('hidden-xs-up');
    }
}

// toggle adding photos/videos on tournament view page
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
// toggle adding photos on tournament view page
function togglePhotoAdd(value) {
    if (value) {
        $('#section-add-photos').removeClass('hidden-xs-up');
        $('#button-done-photos').removeClass('hidden-xs-up');
        $('#button-add-photos').addClass('hidden-xs-up');
    } else {
        $('#section-add-photos').addClass('hidden-xs-up');
        $('#button-done-photos').addClass('hidden-xs-up');
        $('#button-add-photos').removeClass('hidden-xs-up');
    }
}

// toggle video player
function watchVideo(videoId, type) {
    $('#table-videos > tbody > tr').removeClass('row-selected');    // clear selected video
    $('#tagged-users').empty(); // clear tagged user information

    if(videoId) {
        $('#section-watch-video').removeClass('hidden-xs-up');  // show video
        $('#helper-select').addClass('hidden-xs-up');   // hide helper text
        // add video player iframe
        switch (parseInt(type)) {
            case 2:
                $('#section-video-player').html('<iframe id="iframe-video" src="//player.twitch.tv/?video=v' + videoId + '&autoplay=false" frameborder="0" allowfullscreen="true" scrolling="no"></iframe>');
                break;
            default:
                $('#section-video-player').html('<iframe id="iframe-video" src="//www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>');
                break;
        }
        resizeVideo();
        $('#video-'+videoId).addClass('row-selected');  // mark video in list
        // add tagged user information
        if ($('#tags-'+videoId).length) {
            $('#tagged-users').text('tagged users: ');
            $('#tags-'+videoId).clone().appendTo('#tagged-users');
        }
        // remember selected video
        setCookie('selected-video', videoId, 14);
        setCookie('selected-video-type', type, 14);
        // scroll
        $('html, body').animate({
            scrollTop: $("#section-watch-video").offset().top - 60
        }, 500);
    } else {    // close button
        $('#section-watch-video').addClass('hidden-xs-up');
        $('#helper-select').removeClass('hidden-xs-up');
        $('#section-video-player').empty();
        setCookie('selected-video', '', 14);
    }
}

function resizeVideo() {
    if ($('#iframe-video').length) {
        var videoWidth = $('#section-video-player').width();
        $('#iframe-video').width(videoWidth).height(videoWidth * 0.5625);
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
function recalculateDeckNames(postfix) {
    var corp = document.getElementById('corp_deck_identity' + postfix),
        runner = document.getElementById('runner_deck_identity' + postfix);
    document.getElementById('corp_deck_title' + postfix).value = shortenID(corp.options[corp.selectedIndex].text);
    document.getElementById('runner_deck_title' + postfix).value = shortenID(runner.options[runner.selectedIndex].text);
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
    if (!showWeeklyOnCalendar) {
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
                    // tournament alerts
                    if (data.adminAlerts.pendingTournament + data.adminAlerts.conflictTournament > 0) {
                        $('#notif-tournament').removeClass('hidden-xs-up');
                        if (data.adminAlerts.pendingTournament) {
                            $('#pending-title').addClass('notif-red notif-badge-page').attr('data-badge', data.adminAlerts.pendingTournament);
                        }
                        if (data.adminAlerts.conflictTournament) {
                            $('#conflict-title').addClass('notif-red notif-badge-page').attr('data-badge', data.adminAlerts.conflictTournament);
                        }
                        document.getElementById('tabf-tournament').setAttribute("data-badge", data.adminAlerts.pendingTournament + data.adminAlerts.conflictTournament);
                    }
                    // photos alerts
                    if (data.adminAlerts.pendingPhoto > 0) {
                        $('#notif-photo').removeClass('hidden-xs-up');
                        $('#pending-photo-title').addClass('notif-red notif-badge-page').attr('data-badge', data.adminAlerts.pendingPhoto);
                        document.getElementById('tabf-photo').setAttribute("data-badge", data.adminAlerts.pendingPhoto);
                        $('#no-approve-photo').addClass('hidden-xs-up');
                    }
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

// shows collapsed users on badge page
function showBadgeUsers(id) {
    $('#button-show-' + id).addClass('hidden-xs-up');
    $('#users-badge-' + id).removeClass('hidden-xs-up');
}

function tournamentTypeToColor(type) {
    switch (type) {
        case 2: return '#3a8922'; // store
        case 3: return '#722086'; // regional
        case 4: return '#24598a'; // national
        case 5: return '#892222'; // worlds
        case 9: return '#8a5e25'; // continental
        default: return 'grey';
    }
}

function tournamentEmblem(target, type, format) {
    // tournament types
    switch (type) {
        case 'store championship':
            target.append('<span class="tournament-type type-store" title="store championship">S</span> ');
            break;
        case 'regional championship':
            target.append('<span class="tournament-type type-regional" title="regional championship">R</span> ');
            break;
        case 'national championship':
            target.append('<span class="tournament-type type-national" title="national championship">N</span> ');
            break;
        case 'continental championship':
            target.append('<span class="tournament-type type-continental" title="continental championship">C</span> ');
            break;
        case 'worlds championship':
            target.append('<span class="tournament-type type-world" title="worlds championship">W</span> ');
            break;
    }

    // tournament formats
    switch (format) {
        case 'cache refresh':
            target.append('<span class="tournament-format type-cache" title="cache refresh">CR</span> ');
            break;
        case '1.1.1.1':
            target.append('<span class="tournament-format type-onesies" title="1.1.1.1">1</span> ');
            break;
        case 'draft':
            target.append('<span class="tournament-format type-draft" title="draft">D</span> ');
            break;
        case 'cube draft':
            target.append('<span class="tournament-format type-cube-draft" title="cube draft">CD</span> ');
            break;
    }
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}