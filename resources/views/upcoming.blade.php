@extends('layout.general')

@section('content')
    <h4 class="page-header">Upcoming Netrunner Tournaments</h4>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {!! Form::open(['url' => '/tournaments']) !!}
                <div class="row">
                    <div class="col-md-3 col-xs-12">
                        <h5 class="h5-filter"><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                    </div>
                    <div class="col-md-3" id="filter-spacer"></div>
                    <div class="col-md-3 col-xs-12" id="filter-type">
                        <div class="input-group">
                            {!! Form::label('tournament_type_id', 'Type:') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                null, ['class' => 'form-control filter',
                                'onchange' => "filterUpcomingPage(allUpcomingTournaments, allRecurringTournaments, '".@$default_country_id."')", 'disabled' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12" id="filter-country">
                        <div class="input-group">
                            {!! Form::label('location_country', 'Country:') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control filter',
                                'onchange' => "filterDiscover(default_filter, '".@$default_country_id."', map, infowindow)", 'disabled' => '']) !!}
                        </div>
                        <div class="legal-bullshit text-xs-center">
                            <span class="hidden-xs-up" id="label-default-country">
                                using user's default filter -
                            </span>
                            <span class="hidden-xs-up" id="filter-online">
                                {!! Form::checkbox('videos', null, true, ['id' => 'include-online',
                                'onchange' => "filterDiscover(default_filter, '".@$default_country_id."', map, infowindow)"]) !!}
                                {!! Html::decode(Form::label('include-online', 'include online')) !!}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12 hidden-xs-up" id="filter-state">
                        <div class="input-group">
                            {!! Form::label('location_state', 'US State:') !!}
                            {!! Form::select('location_state', $states,
                                        null, ['class' => 'form-control filter',
                                        'onchange'=>"filterDiscover(default_filter, '".@$default_country_id."', map, infowindow)", 'disabled' => '']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'regs', 'cardpool', 'type'],
                'title' => 'Upcoming tournaments', 'id' => 'discover-table', 'icon' => 'fa-list-alt',
                'loader' => true, 'maxrows' => 10])
                <div class="text-xs-center small-text m-t-1">
                    <i title="charity" class="fa fa-heart text-danger"></i>
                    charity |
                    <img class="img-patron-o">
                    patreon T.O. |
                    <span class="tournament-type type-store" title="store championship">S</span>&nbsp;store championship |
                    <span class="tournament-type type-regional" title="regional championship">R</span>&nbsp;regional championship |
                    <span class="tournament-type type-national" title="national championship">N</span>&nbsp;national championship |
                    <span class="tournament-type type-continental" title="continental championship">C</span>&nbsp;continental championship |
                    <span class="tournament-type type-world" title="worlds championship">W</span>&nbsp;worlds championship
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-calendar" aria-hidden="true"></i> Upcoming calendar<br/>
                    <small>past events are hidden</small>
                </h5>
                <style type="text/css">
                    .fc-past {
                        cursor: default !important; background: transparent !important;
                    }
                </style>
                @include('partials.calendar')
                <div class="text-xs-center">
                    <input type="checkbox" id="hide-recurring" checked onchange="hideRecurring()"/>
                    <label for="hide-recurring">hide weekly events</label>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-globe" aria-hidden="true"></i> Map</h4>
                <div class="map-wrapper">
                    <div id="map"></div>
                </div>
                <div class="text-xs-center">
                    <em>
                        <img src="" id="marker-tournament" class="map-legend-icon"/> tournament -
                        <img src="" id="marker-recurring" class="map-legend-icon"/> weekly event -
                        <img src="" id="marker-both" class="map-legend-icon"/> both
                    </em>
                </div>
                <div class="text-xs-center">
                    <input type="checkbox" id="hide-recurring-map" checked onchange="hideRecurringMap(map)"/>
                    <label for="hide-recurring-map">hide weekly events</label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'recurday'], 'title' => 'Weekly events',
                'id' => 'recur-table', 'icon' => 'fa-repeat', 'loader' => true, 'maxrows' => 10])
            </div>
        </div>
    </div>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&callback=initializeMap">
    </script>
    <script type="text/javascript">

        // map legend
        document.getElementById('marker-tournament').setAttribute('src', markerIconUrl('red'));
        document.getElementById('marker-recurring').setAttribute('src', markerIconUrl('blue'));
        document.getElementById('marker-both').setAttribute('src', markerIconUrl('purple'));

        var map, infowindow, bounds, calendardata = {},
            default_filter = 'start={{ $nowdate }}&recur=0&concluded=0&approved=1',
            recur_filter = 'approved=1&recur=1',
            new_filter = default_filter,    // changed with user's default filter
            new_recur_filter = recur_filter;
        var allUpcomingTournaments, allRecurringTournaments, upcomingFilter, recurringFilter;

        @if (@$default_country)
            // user's default country
            new_filter = default_filter + '&country=' + '{{ $default_country }}' + '&include_online=1';
            new_recur_filter = recur_filter + '&country=' + '{{ $default_country }}';
            $('#label-default-country').removeClass('hidden-xs-up');
            document.getElementById('location_country').value = '{{ $default_country_id }}';
            $('#filter-country').addClass('active-filter');
            $('#filter-online').removeClass('hidden-xs-up');
        @endif

        function initializeMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297}
            });
            infowindow = new google.maps.InfoWindow();
            bounds = new google.maps.LatLngBounds();
            $('.filter').prop("disabled", false);
            clearMapMarkers(map);
            // get tournaments
//            updateDiscover('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'],
//                    new_filter, map, bounds, infowindow, function() {
//                        // get weekly events
//                        updateDiscover('#recur-table', ['title', 'location', 'recurday'], new_recur_filter, map, bounds, infowindow, function() {
//                            drawCalendar(calendardata);
//                            hideRecurring();
//                            hideRecurringMap(map);
//                        });
//                    });

            getTournamentData(default_filter, function(data) {
                allUpcomingTournaments = data;
                updateUpcomingTournaments(allUpcomingTournaments);
            });
            getTournamentData(recur_filter, function(data) {
                allRecurringTournaments = data;
                updateRecurringTournaments(allRecurringTournaments);
            })
        }

        function filterTournaments(filters, data) {
            var result = data.slice();
            for (var filter in filters) {
                if (filters.hasOwnProperty(filter)) {
                    var subresult = [];
                    for (var i = 0; i < result.length; i++) {
                        // country filtering
                        if (filter == 'country') {
                            if (result[i].location_country == filters[filter]) {
                                subresult.push(result[i]);
                            } else if (filters.hasOwnProperty('online') && filters.online && result[i].location_country == 'online') {
                                subresult.push(result[i]); // inluce online
                            }
                        // type filtering
                        } else if (filter == 'type') {
                            if (result[i].type == filters[filter]) {
                                subresult.push(result[i]);
                            }
                        // state filtering
                        } else if (filter == 'state') {
                            if (result[i].location_state == filters[filter]) {
                                subresult.push(result[i]);
                            }
                        }
                    }
                    result = subresult;
                }
            }
            return result;
        }

        function filterUpcomingPage(allUpcomingTournaments, allRecurringTournaments, default_country) {
            var typeSelector = document.getElementById('tournament_type_id'),
                countrySelector = document.getElementById('location_country'),
                stateSelector = document.getElementById('location_state'),
                type = typeSelector.options[parseInt(typeSelector.value)+1].innerHtml,
                country = countrySelector.options[parseInt(countrySelector.value)+1].innerHTML,
                state = stateSelector.options[parseInt(stateSelector.value)+1].innerHTML,
                includeOnline = document.getElementById('include-online').checked,
                    upcomingFilter={}, recurringFilter={};

            // type filtering
            if (typeSelector.value > 0) {
                upcomingFilter.type = type;
                $('#filter-type').addClass('active-filter');
            } else {
                $('#filter-type').removeClass('active-filter');
            }
            // country filtering
            if (country !== '---') {
                upcomingFilter.country = country;
                recurringFilter.country = country;
                $('#filter-country').addClass('active-filter');
                $('#filter-online').removeClass('hidden-xs-up');
                if (country === 'United States') {
                    $('#filter-state').removeClass('hidden-xs-up');
                    $('#filter-spacer').addClass('hidden-xs-up');
                    // state filtering
                    if (state !== '---') {
                        upcomingFilter.state = state;
                        recurringFilter.state = state;
                        $('#filter-state').addClass('active-filter');
                    } else {
                        $('#filter-state').removeClass('active-filter');
                    }
                }
                if (includeOnline) {
                    upcomingFilter.online = true;
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
            if (countrySelector.value == default_country) {
                $('#label-default-country').removeClass('hidden-xs-up');
            } else {
                $('#label-default-country').addClass('hidden-xs-up');
            }

            clearMapMarkers(map);
            bounds = new google.maps.LatLngBounds();
            calendardata = {};
            updateUpcomingTournaments(filterTournaments(upcomingFilter, allUpcomingTournaments));
            updateRecurringTournaments(filterTournaments(recurringFilter, allRecurringTournaments));
        }

        function updateUpcomingTournaments(data) {
            $('#discover-table').removeClass('hidden-xs-up').find('tbody').empty();
            updateTournamentTable('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'], 'no tournaments to show', '', data);
            updateTournamentCalendar(data);
            codeAddress(data, map, bounds, infowindow);
        }
        function updateRecurringTournaments(data) {
            $('#recur-table').removeClass('hidden-xs-up').find('tbody').empty();
            updateTournamentTable('#recur-table', ['title', 'location', 'recurday'], 'no tournaments to show', '', data);
            updateTournamentCalendar(data);
            codeAddress(data, map, bounds, infowindow, function() {
                drawCalendar(calendardata);
                hideRecurring();
                hideRecurringMap(map);
                updatePaging('discover-table');
                updatePaging('recur-table');
            });
        }

    </script>
@stop

