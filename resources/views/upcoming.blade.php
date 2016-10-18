@extends('layout.general')

@section('content')
    <h4 class="page-header">Upcoming tournaments</h4>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {!! Form::open(['url' => '/tournaments']) !!}
                <div class="row">
                    <div class="col-md-3 col-xs-12">
                        <h5 class="h5-filter"><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                    </div>
                    <div class="col-md-3" id="filter-spacer"></div>
                    <div class="col-md-3 col-xs-12">
                        <div class="input-group">
                            {!! Form::label('tournament_type_id', 'Type:') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                null, ['class' => 'form-control filter', 'onchange' => 'filterDiscover(default_filter, map, geocoder, infowindow)', 'disabled' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <div class="input-group">
                            {!! Form::label('location_country', 'Country:') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control filter', 'onchange' => 'filterDiscover(default_filter, map, geocoder, infowindow)', 'disabled' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12 hidden-xs-up" id="select_state">
                        <div class="input-group">
                            {!! Form::label('location_state', 'US State:') !!}
                            {!! Form::select('location_state', $states,
                                        null, ['class' => 'form-control filter', 'onchange'=>'filterDiscover(default_filter, map, geocoder, infowindow)', 'disabled' => '']) !!}
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
                ['columns' => ['title', 'location', 'date', 'players', 'cardpool', 'type'],
                'title' => 'Upcoming tournaments', 'id' => 'discover-table', 'icon' => 'fa-list-alt', 'loader' => true])
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
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-globe" aria-hidden="true"></i> Map</h4>
                <div class="map-wrapper">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&callback=initializeMap">
    </script>
    <script type="text/javascript">

        var geocoder, map, infowindow,
            default_filter = 'start={{ $nowdate }}&approved=1';

        function initializeMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297}
            });
            geocoder = new google.maps.Geocoder();
            infowindow = new google.maps.InfoWindow();
            $('.filter').prop("disabled", false);
            updateDiscover(default_filter, map, geocoder, infowindow);
        }

    </script>
@stop

