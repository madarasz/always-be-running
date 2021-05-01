@extends('layout.general')

@section('content')
    <h4 class="page-header">Netrunner Tournament Results</h4>
    @include('partials.message')
    <div class="row" id="results-page">
        {{--Results table--}}
        <div class="col-lg-9 push-lg-3 col-12" id="col-results">
            <div class="bracket">
                {{--Result / to be concluded tabs--}}
                <div class="modal-tabs">
                    <ul id="result-tabs" class="nav nav-tabs" role="tablist">
                        <li class="nav-item" id="t-results">
                            <a class="nav-link active" data-toggle="tab" href="#tab-results" role="tab">
                                <h5>
                                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                                    Tournament results
                                    <br/>
                                    <small>concluded tournaments from the past</small>
                                </h5>
                            </a>
                        </li>
                        <li class="nav-item" id="t-to-be-concluded">
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
                        <div class="loader" id="results-loader" v-if="!resultsLoaded">&nbsp;</div>
                        <tournament-table :tournaments="resultsData" table-id="results" :is-loaded="resultsLoaded" :headers="resultHeaders" 
                                empty-message="no tournaments to show" :show-flags="showFlag"/>
                    </div>
                    {{--Conclude modal--}}
                    <!-- TODO @include('tournaments.modals.conclude') -->
                    {{--To be concluded table--}}
                    <div class="tab-pane" id="tab-to-be-concluded" role="tabpanel">
                        {{--Warning for not logged in users--}}
                        @if (!@Auth::user())
                            <div class="alert alert-warning text-xs-center" id="warning-conclude">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                Please <a href="/oauth2/redirect">login via NetrunnerDB</a> to conclude tournaments.
                            </div>
                        @endif
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'cardpool', 'conclusion', 'regs'],
                            'skip_header' => true, 'id' => 'to-be-concluded', 'doublerow' => true, 'loader' => true,
                            'maxrows' => 50, 'pager_options' => [50,100,'all']])
                    </div>
                </div>
                @include('tournaments.partials.icons')
            </div>
        </div>
        <div class="col-lg-3 pull-lg-9 col-12" id="col-other">
            {{--Filters--}}
            <div class="bracket" id="bracket-filters">
                <div class="loader" id="filter-loader" style="margin-top: 0" v-if="!resultsLoaded">loading</div>
                <h5><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                {!! Form::open(['url' => '/tournaments']) !!}
                    <div class="row no-gutters">
                        <div class="form-group col-xs-6 col-lg-12" id="filter-cardpool">
                            {!! Form::label('cardpool', 'Cardpool') !!}
                            {!! Form::select('cardpool', array_combine($tournament_cardpools, $tournament_cardpools),
                                null, ['class' => 'form-control filter',
                                'onchange' => "filterResults()", 'disabled' => '']) !!}
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-type">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', array_combine($tournament_types,$tournament_types),
                                null, ['class' => 'form-control filter',
                                'onchange' => "filterResults()", 'disabled' => '']) !!}
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-country">
                            {!! Form::label('location_country', 'Country') !!}
                            {!! Form::select('location_country', array_combine($countries, $countries), null,
                                ['class' => 'form-control filter',
                                'onchange' => "filterResults()", 'disabled' => '']) !!}
                            <div class="legal-bullshit text-xs-center hidden-xs-up" id="label-default-country">
                                using user's default filter
                            </div>
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-format">
                            {!! Form::label('format', 'Format') !!}
                            {!! Form::select('format', array_combine($tournament_formats, $tournament_formats), null,
                                ['class' => 'form-control filter',
                                'onchange' => "filterResults()", 'disabled' => '']) !!}
                        </div>
                        <div class="form-group col-xs-6 col-lg-12 m-b-0" id="filter-video">
                            {!! Form::checkbox('videos', null, null, ['id' => 'videos', 'class' => 'filter', 'disabled' => '',
                                'onchange' => "filterResults()"]) !!}
                            {!! Html::decode(Form::label('videos', 'has video <i class="fa fa-video-camera" aria-hidden="true"></i>')) !!}
                        </div>
                        <div class="form-group col-xs-6 col-lg-12 m-b-0" id="filter-matchdata">
                            {!! Form::checkbox('matchdata', null, null, ['id' => 'matchdata', 'class' => 'filter', 'disabled' => '',
                                'onchange' => "filterResults()"]) !!}
                            {!! Html::decode(Form::label('matchdata', 'has match data <i class="fa fa-handshake-o" aria-hidden="true"></i>')) !!}
                        </div>
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
            {{--Featured--}}
            @if (count($featured))
                @include('tournaments.partials.featured-results')
            @endif
        </div>
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        var resultsDataAll = [], resultsDataFiltered = [], toBeConcludedAll = [], toBeConcludedFiltered = [],
                userAuthenticated = @if (@Auth::user()) true @else false @endif,
                packlist = [],
                defaultCountry = "",
                currentPack = "",
                runnerIDs = [], corpIDs = [], offset = 50, offsetIterator = 1000;

        var resultsPage = new Vue({
            el: '#results-page',
            data: {
                resultsData: [],
                toConcludeData: [],
                resultsLoaded: false,
                resultHeaders: ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'],
                showFlag: true
            },
            computed: {},
            mounted: function () {
                this.getResultsData()
            },
            methods: {
                getResultsData: function() {
                    $.ajax({
                        url: '/api/tournaments/results',
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            resultsPage.resultsData = data
                            resultsPage.resultsLoaded = true
                            $('.filter').prop("disabled", false)
                        }
                    });
                }
            }
        });

        /*positionFilters();

        // load tournaments, first 50 for quick display
        getTournamentData('/results?limit='+offset, function(data) {
            // duplicate arrays
            resultsDataAll = data.slice();
            resultsDataFiltered = data.slice();

            // apply filters in URL
            applyInitialResultsFilters();

            // display
            updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', resultsDataFiltered);
            $('#results-more-loader').removeClass('hidden-xs-up');
            $('#results-controls').addClass('hidden-xs-up');

            // load tournaments to be concluded
            getTournamentData("?concluded=0&recur=0&hide-non=1&desc=1&end={{ $nowdate }}", function(data) {
                toBeConcludedAll = data;
                toBeConcludedFiltered = toBeConcludedAll.slice();

                applyInitialResultsFilters();

                updateTournamentTable('#to-be-concluded', ['title', 'date', 'location', 'cardpool', 'conclusion', 'players'],
                        'no tournaments waiting for conclusion', '', toBeConcludedFiltered);
                updatePaging('to-be-concluded');
            });

            var resultDataUpdater = function(data) {
                resultsDataAll = resultsDataAll.concat(data);
                resultsDataFiltered = resultsDataAll.slice();

                if (data.length == offsetIterator) {
                    // we have more results to add, load next chunk
                    offset += offsetIterator;
                    getTournamentData('/results?limit='+offsetIterator+'&offset='+offset, resultDataUpdater);
                } else {
                    // all is loaded, display all
                    // apply filters in URL
                    applyInitialResultsFilters();
                    $('#results').find('tbody').empty();
                    updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', resultsDataFiltered);
                    updatePaging('results');

                    $('#results-more-loader').addClass('hidden-xs-up');
                    $('#filter-loader').addClass('hidden-xs-up');
                    $('#results-controls').removeClass('hidden-xs-up');
                    $('.filter').prop("disabled", false);
                }                
            };

            // load the rest
            getTournamentData('/results?limit='+offsetIterator+'&offset='+offset, resultDataUpdater);
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
            positionFilters();
        });

        // position filter bracket based on screen width
        function positionFilters() {
            if (window.matchMedia( "(min-width: 992px)").matches) {
                // lg-size
                $('#col-other').prepend($('#bracket-filters'));
            } else {
                // below lg-size
                $('#col-results').prepend($('#bracket-filters'));
            }
        }

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

            // just tournaments with matchdata
            @if ($matchdata !== null)
                filterTournamentData(resultsDataFiltered, 'matchdata', true);
                document.getElementById('matchdata').checked = true;
                $('#filter-matchdata').addClass('active-filter');
            @endif
        }*/
    </script>
@stop

