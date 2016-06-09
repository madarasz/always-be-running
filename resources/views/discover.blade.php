@extends('layout.general')

@section('content')
    <h3 class="page-header">Discover upcoming tournaments</h3>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-filter" aria-hidden="true"></i> Filter</h4>
                {!! Form::open(['url' => '/tournaments']) !!}
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                null, ['class' => 'form-control', 'onchange' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('location_country', 'Country') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control', 'onchange' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('location_us_state', 'State') !!}
                            {!! Form::select('location_us_state', $us_states,
                                        null, ['class' => 'form-control', 'onchange'=>'']) !!}
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
                ['columns' => ['title', 'location', 'date', 'players', 'cardpool'],
                'data' => $tournaments, 'title' => 'Upcoming tournaments',
                 'id' => 'discover-table', 'icon' => 'fa-list-alt'])
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4>
                    <i class="fa fa-calendar" aria-hidden="true"></i> Upcoming calendar<br/>
                    <small>past events are hidden</small>
                </h4>
                <div class="custom-calendar-wrap">
                    <div id="custom-inner" class="custom-inner">
                        <div class="custom-header clearfix">
                            <nav>
                                <span id="custom-prev" class="custom-prev"></span>
                                <span id="custom-next" class="custom-next"></span>
                            </nav>
                            <h2 id="custom-month" class="custom-month"></h2>
                            <h3 id="custom-year" class="custom-year"></h3>
                        </div>
                        <div id="calendar" class="fc-calendar-container"></div>
                    </div>
                </div>
                <script type="application/javascript">



                </script>
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

        getTournamentData('', function(data) {
            updateTournamentTable('#discover-table', ['title', 'date', 'location', 'cardpool', 'players'], 'no tournaments to show', data);
            updateTournamentCalendar(data);
            codeAddress(data, map, geocoder);
        });

        var geocoder;
        var map;
        function initializeMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297}
            });
            geocoder = new google.maps.Geocoder();
        }

    </script>
@stop

