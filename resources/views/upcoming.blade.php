@extends('layout.general')

@section('content')
    <h4 class="page-header">Upcoming Netrunner Tournaments</h4>
    {{--Filters--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {!! Form::open(['url' => '/tournaments']) !!}
                <div class="row">
                    <div class="col-xl-3 col-md-4 col-xs-12">
                        <h5 class="h5-filter"><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                    </div>
                    <div class="col-xl-3 hidden-lg-down" id="filter-spacer"></div>
                    <div class="col-xl-3 col-md-4 col-xs-12" id="filter-type">
                        <div class="input-group">
                            {!! Form::label('tournament_type_id', 'Type:') !!}
                            {!! Form::select('tournament_type_id', array_combine($tournament_types, $tournament_types),
                                null, ['class' => 'form-control filter',
                                'onchange' => "filterUpcoming()", 'disabled' => '']) !!}
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-4 col-xs-12" id="filter-country">
                        <div class="input-group">
                            {!! Form::label('location_country', 'Country:') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control filter',
                                'onchange' => "filterUpcoming()", 'disabled' => '']) !!}
                        </div>
                        <div class="legal-bullshit text-xs-center">
                            <span class="hidden-xs-up" id="label-default-country">
                                using user's default filter -
                            </span>
                            <span class="hidden-xs-up" id="filter-online">
                                {!! Form::checkbox('videos', null, true, ['id' => 'include-online',
                                'onchange' => "filterUpcoming()"]) !!}
                                {!! Html::decode(Form::label('include-online', 'include online')) !!}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12 hidden-xs-up" id="filter-state">
                        <div class="input-group">
                            {!! Form::label('location_state', 'US State:') !!}
                            {!! Form::select('location_state', $states,
                                        null, ['class' => 'form-control filter',
                                        'onchange'=>"filterUpcoming()", 'disabled' => '']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    {{--Upcoming table--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'regs', 'cardpool', 'type'],
                'title' => 'Upcoming tournaments', 'id' => 'discover-table', 'icon' => 'fa-list-alt',
                'loader' => true, 'maxrows' => 10, 'pager_options' => [10,25,'all']])
                @include('tournaments.partials.icons')
            </div>
        </div>
    </div>
    {{--Featured--}}
    @if (count($featured))
        @include('tournaments.partials.featured-upcoming')
    @endif
    {{--Calendar and map--}}
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-calendar" aria-hidden="true"></i> Upcoming calendar
                    <div class="pull-right">
                        <button id="button-show-weekly-calendar" class="btn btn-primary btn-xs" disabled="disabled">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                            Show recurring
                        </button>
                        <button id="button-hide-weekly-calendar" class="btn btn-primary btn-xs hidden-xs-up">
                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                            Hide recurring
                        </button>
                    </div>
                    <br/>
                    <small>past events are hidden</small>
                </h5>
                @include('partials.calendar')
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4>
                    <i class="fa fa-globe" aria-hidden="true"></i>
                    Map
                    <div class="small-text loader hidden-xs-up" id="loader-locater">locating</div>
                    <div class="pull-right">
                        <span id="error-location" class="alert alert-danger alert-small hidden-xs-up">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            <span id="text-location-error"></span>
                        </span>
                        <button id="button-show-weekly-map" class="btn btn-primary btn-xs" disabled="disabled">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                            Show recurring
                        </button>
                        <button id="button-hide-weekly-map" class="btn btn-primary btn-xs hidden-xs-up">
                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                            Hide recurring
                        </button>
                        <button id="button-near-me" class="btn btn-primary btn-xs" onclick="getLocation()" disabled>
                            <i class="fa fa-bullseye" aria-hidden="true"></i>
                            Zoom to me
                        </button>
                    </div>
                </h4>
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
            </div>
        </div>
    </div>
    {{--Recurring--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'recurday'], 'title' => 'Recurring events / meetups',
                'id' => 'recur-table', 'icon' => 'fa-repeat', 'loader' => true,
                'maxrows' => 10, 'pager_options' => [10,25,'all']])
            </div>
        </div>
    </div>
    <script type="text/javascript">

        // map legend
        document.getElementById('marker-tournament').setAttribute('src', markerIconUrl('red'));
        document.getElementById('marker-recurring').setAttribute('src', markerIconUrl('blue'));
        document.getElementById('marker-both').setAttribute('src', markerIconUrl('purple'));

        var map, infowindow, bounds, calendardata = {},
            upcomingDataAll = { tournaments: [], recurring_events: []},
            upcomingDataFiltered = { tournaments: [], recurring_events: [] },
            defaultCountry = '',
            userLocation = null,
            showWeeklyOnMap = false, showWeeklyOnCalendar = false,
            shortestDistance = 1000.0; // set possible maximum distance while locating user here

        function initializeMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297}
            });
            infowindow = new google.maps.InfoWindow();
            bounds = new google.maps.LatLngBounds();

            $('.filter').prop("disabled", false);
            clearMapMarkers(map);
            $('#discover-table-loader').removeClass('hidden-xs-up');
            $('#revur-table-loader').removeClass('hidden-xs-up');

            // get tournament + recurring event data and display them
            getTournamentData('/upcoming', function (data) {
                upcomingDataAll = $.extend(true, {}, data);
                upcomingDataFiltered = $.extend(true, {}, data);
                // if user has default country filter
                @if (@$default_country)
                    defaultCountry = '{{ $default_country }}';
                    $('#label-default-country').removeClass('hidden-xs-up');
                    document.getElementById('location_country').value = '{{ $default_country_id }}';
                    $('#filter-country').addClass('active-filter');
                    $('#filter-online').removeClass('hidden-xs-up');
                    filterTournamentData(upcomingDataFiltered.tournaments, 'location_country', defaultCountry, true);
                    filterTournamentData(upcomingDataFiltered.recurring_events, 'location_country', defaultCountry);
                @endif
                displayUpcomingPageTournaments(upcomingDataFiltered);
                $('#button-show-weekly-map').prop("disabled", false);
                $('#button-show-weekly-calendar').prop("disabled", false);
            });
        }

        // show / hide recurring buttons
        $('#button-show-weekly-map').click(function() {
            $('#button-show-weekly-map').addClass('hidden-xs-up');
            $('#button-hide-weekly-map').removeClass('hidden-xs-up');
            showWeeklyOnMap = true;
            displayUpcomingPageTournaments(upcomingDataFiltered);
        });
        $('#button-hide-weekly-map').click(function() {
            $('#button-hide-weekly-map').addClass('hidden-xs-up');
            $('#button-show-weekly-map').removeClass('hidden-xs-up');
            showWeeklyOnMap = false;
            displayUpcomingPageTournaments(upcomingDataFiltered);
        });
        $('#button-show-weekly-calendar').click(function() {
            $('#button-show-weekly-calendar').addClass('hidden-xs-up');
            $('#button-hide-weekly-calendar').removeClass('hidden-xs-up');
            showWeeklyOnCalendar = true;
            displayUpcomingPageTournaments(upcomingDataFiltered);
        });
        $('#button-hide-weekly-calendar').click(function() {
            $('#button-hide-weekly-calendar').addClass('hidden-xs-up');
            $('#button-show-weekly-calendar').removeClass('hidden-xs-up');
            showWeeklyOnCalendar = false;
            displayUpcomingPageTournaments(upcomingDataFiltered);
        });

    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_FRONTEND_API')}}&callback=initializeMap&libraries=geometry">
    </script>
@stop

