@extends('layout.general')

@section('content')
    <h4 class="page-header">Results</h4>
    @include('partials.message')
    <div class="row">
        {{--Results table--}}
        <div class="col-md-9 col-xs-12 push-md-3">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims' ],
                    'title' => 'Tournament results from the past', 'id' => 'results', 'icon' => 'fa-list-alt',
                    'subtitle' => 'only concluded tournaments'])
            </div>
        </div>
        <div class="col-md-3 col-xs-12 pull-md-9">
            {{--Filters--}}
            <div class="bracket">
                <h5><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                {!! Form::open(['url' => '/tournaments']) !!}
                    <div class="form-group" id="filter-cardpool">
                        {!! Form::label('cardpool', 'Cardpool') !!}
                        {!! Form::select('cardpool', $tournament_cardpools,
                            null, ['class' => 'form-control filter', 'onchange' => 'filterResults(defaultFilter, packlist)', 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-type">
                        {!! Form::label('tournament_type_id', 'Type') !!}
                        {!! Form::select('tournament_type_id', $tournament_types,
                            null, ['class' => 'form-control filter', 'onchange' => 'filterResults(defaultFilter, packlist)', 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-country">
                        {!! Form::label('location_country', 'Country') !!}
                        {!! Form::select('location_country', $countries, null,
                            ['class' => 'form-control filter', 'onchange' => 'filterResults(defaultFilter, packlist)', 'disabled' => '']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
            {{--Stats--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    Statistics - <span id="stat-packname" class="small-text"></span><br/>
                    <small>provided by <a href="http://www.knowthemeta.com">KnowTheMeta</a></small>
                </h5>
                <div class="text-xs-center">
                    {{--runner ID chart--}}
                    <div class="loader-chart stat-load">loading</div>
                    <div id="stat-chart-runner" class="stat-chart"></div>
                    <div class="small-text p-b-1 hidden-xs-up stat-error">no stats available</div>
                    <div class="small-text p-b-1">runner IDs</div>
                    {{--corp ID chart--}}
                    <div class="loader-chart stat-load">loading</div>
                    <div id="stat-chart-corp" class="stat-chart"></div>
                    <div class="small-text p-b-1 hidden-xs-up stat-error">no stats available</div>
                    <div class="small-text">corp IDs</div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        var defaultFilter = "approved=1&concluded=1&end={{ $nowdate }}",
                packlist = [],
                currentPack = "",
                runnerIDs = [], corpIDs = [];

        // table entries
        getTournamentData(defaultFilter, function(data) {
            updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', data);
            $('.filter').prop("disabled", false);
        });

        // statistics charts
        google.charts.setOnLoadCallback(initCharts);
        google.charts.load('current', {'packages':['corechart']});
        function initCharts() {
            // KtM get packs
            getKTMDataPacks(function (packs) {
                packlist = packs;
                currentPack = packs[packs.length-1];
                updateIdStats(currentPack);
            });
        }

        // redraw charts on window resize
        $(window).resize(function(){
            drawResultStats('stat-chart-runner', runnerIDs, 0.04);
            drawResultStats('stat-chart-corp', corpIDs, 0.04);
        });
    </script>
@stop

