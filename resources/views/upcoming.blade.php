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
                                'onchange' => "filterDiscover(default_filter, '".@$default_country_id."', map, infowindow)", 'disabled' => '']) !!}
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
            updateDiscover('#discover-table', ['title', 'date', 'type', 'location', 'cardpool', 'players'],
                    new_filter, map, bounds, infowindow, function() {
                        // get weekly events
                        updateDiscover('#recur-table', ['title', 'location', 'recurday'], new_recur_filter, map, bounds, infowindow, function() {
                            drawCalendar(calendardata);
                            hideRecurring();
                        });
                    });
        }

    </script>
@stop

