@extends('layout.general')

@section('content')
    <h4 class="page-header">Netrunner Tournament Results</h4>
    @include('partials.message')
    <div class="row">
        {{--Results table--}}
        <div class="col-lg-9 push-lg-3 col-12">
            <div class="bracket">
                {{--Result / to be concluded tabs for logged in users--}}
                @if (@Auth::user())
                <div class="modal-tabs">
                    <ul id="result-tabs" class="nav nav-tabs" role="tablist">
                        <li class="nav-item" id="tab-results">
                            <a class="nav-link active" data-toggle="tab" href="#tab-results" role="tab">
                                <h5>
                                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                                    Tournament results
                                    <br/>
                                    <small>concluded tournaments from the past</small>
                                </h5>
                            </a>
                        </li>
                        <li class="nav-item" id="tab-to-be-concluded">
                            <a class="nav-link" data-toggle="tab" href="#tab-to-be-concluded" role="tab">
                                <h5>
                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                    Waiting for conclusion
                                    <br/>
                                    <small>add player number / results</small>
                                </h5>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">
                    {{--Results table--}}
                    <div class="tab-pane active" id="tab-results" role="tabpanel">
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims' ],
                            'skip_header' => true, 'id' => 'results', 'doublerow' => true, 'loader' => true, 'maxrows' => 50])
                        <div class="loader hidden-xs-up" id="results-more-loader">loading more</div>
                    </div>
                    {{--Conclude modal--}}
                    @include('tournaments.modals.conclude')
                    {{--To be concluded table--}}
                    <div class="tab-pane" id="tab-to-be-concluded" role="tabpanel">
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'cardpool', 'conclusion', 'regs'],
                            'skip_header' => true, 'id' => 'to-be-concluded', 'doublerow' => true, 'loader' => true, 'maxrows' => 50])
                    </div>
                </div>
                @else
                    <h5>
                        <i class="fa fa-list-alt" aria-hidden="true"></i>
                        Tournament results
                        <br/>
                        <small>concluded tournaments from the past</small>
                    </h5>
                    @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims' ],
                            'skip_header' => true, 'id' => 'results', 'doublerow' => true, 'loader' => true, 'maxrows' => 50])
                    <div class="loader hidden-xs-up" id="results-more-loader">loading more</div>
                @endif

                @include('tournaments.partials.icons')
            </div>
        </div>
        <div class="col-lg-3 pull-lg-9 col-12">
            {{--Filters--}}
            <div class="bracket">
                <div class="loader" id="filter-loader" style="margin-top: 0">loading</div>
                <h5><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                {!! Form::open(['url' => '/tournaments']) !!}
                    <div class="form-group" id="filter-cardpool">
                        {!! Form::label('cardpool', 'Cardpool') !!}
                        {!! Form::select('cardpool', array_combine($tournament_cardpools, $tournament_cardpools),
                            null, ['class' => 'form-control filter',
                            'onchange' => "filterResults()", 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-type">
                        {!! Form::label('tournament_type_id', 'Type') !!}
                        {!! Form::select('tournament_type_id', array_combine($tournament_types,$tournament_types),
                            null, ['class' => 'form-control filter',
                            'onchange' => "filterResults()", 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-country">
                        {!! Form::label('location_country', 'Country') !!}
                        {!! Form::select('location_country', array_combine($countries, $countries), null,
                            ['class' => 'form-control filter',
                            'onchange' => "filterResults()", 'disabled' => '']) !!}
                        <div class="legal-bullshit text-xs-center hidden-xs-up" id="label-default-country">
                            using user's default filter
                        </div>
                    </div>
                    <div class="form-group" id="filter-format">
                        {!! Form::label('format', 'Format') !!}
                        {!! Form::select('format', array_combine($tournament_formats, $tournament_formats), null,
                            ['class' => 'form-control filter',
                            'onchange' => "filterResults()", 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-video">
                        {!! Form::checkbox('videos', null, null, ['id' => 'videos', 'class' => 'filter', 'disabled' => '',
                            'onchange' => "filterResults()"]) !!}
                        {!! Html::decode(Form::label('videos', 'has video <i class="fa fa-video-camera" aria-hidden="true"></i>')) !!}
                    </div>
                {!! Form::close() !!}
            </div>
            {{--Featured--}}
            @if (count($featured))
                @include('tournaments.partials.featured-results')
            @endif
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
        var resultsDataAll = [], resultsDataFiltered = [], toBeConcludedAll = [], toBeConcludedFiltered = [],
                packlist = [],
                defaultCountry = "",
                currentPack = "",
                runnerIDs = [], corpIDs = [];

        // load tournaments, first 50 for quick display
        getTournamentData('/results?limit=50', function(data) {
            // duplicate arrays
            resultsDataAll = data.slice();
            resultsDataFiltered = data.slice();

            // apply filters in URL
            applyInitialResultsFilters();

            // display
            updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', resultsDataFiltered);
            $('#results-more-loader').removeClass('hidden-xs-up');
            $('#results-controls').addClass('hidden-xs-up');

            @if(@Auth::user())
            // load tournaments to be concluded
            getTournamentData("?approved=1&concluded=0&recur=0&hide-non=1&desc=1&end={{ $nowdate }}", function(data) {
                toBeConcludedAll = data;
                toBeConcludedFiltered = toBeConcludedAll.slice();

                applyInitialResultsFilters();

                updateTournamentTable('#to-be-concluded', ['title', 'date', 'location', 'cardpool', 'conclusion', 'players'],
                        'no tournaments waiting for conclusion', '', toBeConcludedFiltered);
                updatePaging('to-be-concluded');

            });
            @endif

            // load the rest
            getTournamentData('/results?offset=50', function(data) {
                resultsDataAll = resultsDataAll.concat(data);
                resultsDataFiltered = resultsDataAll.slice();

                // apply filters in URL
                applyInitialResultsFilters();

                // display all tournaments
                $('#results').find('tbody').empty();
                updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', resultsDataFiltered);

                $('#results-more-loader').addClass('hidden-xs-up');
                $('#filter-loader').addClass('hidden-xs-up');
                $('#results-controls').removeClass('hidden-xs-up');
                $('.filter').prop("disabled", false);
                updatePaging('results');
            });
        });

        // statistics charts
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(initCharts);
        function initCharts() {
            // KtM get packs
            getKTMDataPacks(function (packs) {
                packlist = packs;
                updateIdStats(packs[0]);
            });
        }

        // redraw charts on window resize
        $(window).resize(function(){
            drawResultStats('stat-chart-runner', runnerIDs, 0.04);
            drawResultStats('stat-chart-corp', corpIDs, 0.04);
        });

        // apply Results page filters from URL and user settings
        function applyInitialResultsFilters() {
            var requestedCardpool = '-',
                    requestedType = '-',
                    requestedCountry = '-',
                    requestedFormat = '-';

            // cardpool from URL
                    @if ($cardpool !== null)
                        var availableCardpools = collectOptions('cardpool');
            requestedCardpool = '{{ $cardpool }}';

            if (requestedCardpool in availableCardpools) {
                filterTournamentData(resultsDataFiltered, 'cardpool', availableCardpools[requestedCardpool]);
                filterTournamentData(toBeConcludedFiltered, 'cardpool', availableCardpools[requestedCardpool]);
                document.getElementById('cardpool').value = availableCardpools[requestedCardpool];
                $('#filter-cardpool').addClass('active-filter');
            }
            @endif

            // type from URL
                    @if ($type !== null)
                        var availableTypes = collectOptions('tournament_type_id');
            requestedType = '{{ $type }}';

            if (requestedType in availableTypes) {
                filterTournamentData(resultsDataFiltered, 'type', availableTypes[requestedType]);
                filterTournamentData(toBeConcludedFiltered, 'type', availableTypes[requestedType]);
                document.getElementById('tournament_type_id').value = availableTypes[requestedType];
                $('#filter-type').addClass('active-filter');
            }
            @endif

            // format from URL
                    @if ($format !== null)
                        var availableFormats = collectOptions('format');
            requestedFormat = '{{ $format }}';

            if (requestedFormat in availableFormats) {
                filterTournamentData(resultsDataFiltered, 'format', availableFormats[requestedFormat]);
                filterTournamentData(toBeConcludedFiltered, 'format', availableFormats[requestedFormat]);
                document.getElementById('format').value = availableFormats[requestedFormat];
                $('#filter-format').addClass('active-filter');
            }
            @endif

            // country from URL
            @if ($country !== null)
                var availableCountries = collectOptions('location_country');
                requestedCountry = '{{ $country }}';

                if (requestedCountry in availableCountries) {
                    filterTournamentData(resultsDataFiltered, 'location_country', convertFromURLString(requestedCountry));
                    filterTournamentData(toBeConcludedFiltered, 'location_country', convertFromURLString(requestedCountry));
                    document.getElementById('location_country').value = availableCountries[requestedCountry];
                    $('#filter-country').addClass('active-filter');
                }
            @elseif (@$default_country && $country == null && $videos == null && $format == null && $type == null && $cardpool == null)
                // user's default country
                defaultCountry = '{{ $default_country }}';
                filterTournamentData(resultsDataFiltered, 'location_country', defaultCountry);
                filterTournamentData(toBeConcludedFiltered, 'location_country', defaultCountry);
                $('#label-default-country').removeClass('hidden-xs-up');
                document.getElementById('location_country').value = defaultCountry;
                $('#filter-country').addClass('active-filter');
                updateResultsURL(requestedCardpool, requestedType, defaultCountry, requestedFormat,
                        document.getElementById('videos').checked);
            @endif

            // just tournaments with videos
            @if ($videos !== null)
                filterTournamentData(resultsDataFiltered, 'videos', true);
                document.getElementById('videos').checked = true;
                $('#filter-video').addClass('active-filter');
            @endif

        }
    </script>
@stop

