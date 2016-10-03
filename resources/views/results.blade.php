@extends('layout.general')

@section('content')
    <h4 class="page-header">Results</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                {!! Form::open(['url' => '/tournaments']) !!}
                {{--<div class="row">--}}
                    {{--<div class="col-md-4 col-xs-12">--}}
                        <div class="form-group">
                            {!! Form::label('cardpool', 'Cardpool') !!}
                            {!! Form::select('cardpool', $tournament_cardpools,
                                null, ['class' => 'form-control filter', 'onchange' => 'filterDiscover(default_filter, map, geocoder)', 'disabled' => '']) !!}
                        </div>
                    {{--</div>--}}
                    {{--<div class="col-md-4 col-xs-12">--}}
                        <div class="form-group">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                null, ['class' => 'form-control filter', 'onchange' => 'filterDiscover(default_filter, map, geocoder)', 'disabled' => '']) !!}
                        </div>
                    {{--</div>--}}
                    {{--<div class="col-md-4 col-xs-12">--}}
                        <div class="form-group">
                            {!! Form::label('location_country', 'Country') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control filter', 'onchange' => 'filterDiscover(default_filter, map, geocoder)', 'disabled' => '']) !!}
                        </div>
                    {{--</div>--}}
                {{--</div>--}}
                {!! Form::close() !!}
            </div>
            <div class="bracket">
                <h5>
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    Statistics<br/>
                    <small>provided by <a href="http://www.knowthemeta.com">KnowTheMeta</a></small>
                </h5>
                @include('partials.tobedeveloped')
            </div>
        </div>
    {{--</div>--}}
    {{--<div class="row">--}}
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'players', 'claims' ],
                    'title' => 'Tournament results from the past', 'id' => 'results', 'icon' => 'fa-list-alt',
                    'subtitle' => 'only concluded tournaments'])
            </div>
        </div>
    </div>
    <script type="text/javascript">
        getTournamentData("approved=1&conluded=1&end={{ $nowdate }}", function(data) {
            updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'players', 'claims'], 'no tournaments to show', '', data);
            $('.filter').prop("disabled", false);
        });
    </script>
@stop

