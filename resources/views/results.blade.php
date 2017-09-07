@extends('layout.general')

@section('content')
    <h4 class="page-header">Netrunner Tournament Results</h4>
    @include('partials.message')
    <div class="row">
        {{--Results table--}}
        <div class="col-md-8 push-md-4 col-lg-9 push-lg-3 col-sm-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims' ],
                    'title' => 'Tournament results from the past', 'id' => 'results', 'icon' => 'fa-list-alt',
                    'subtitle' => 'only concluded tournaments', 'doublerow' => true, 'loader' => true, 'maxrows' => 50])
                @include('tournaments.partials.icons')
            </div>
        </div>
        <div class="col-md-4 pull-md-8 col-lg-3 pull-lg-9 col-col-sm-12">
            {{--Filters--}}
            <div class="bracket">
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
        var resultsDataAll = [], resultsDataFiltered = [],
                packlist = [],
                defaultCountry = "",
                currentPack = "",
                runnerIDs = [], corpIDs = [];

        // load tournaments
        getTournamentData('/results', function(data) {
            // duplicate arrays
            resultsDataAll = data.slice();
            resultsDataFiltered = data.slice();

            // apply filters in URL
            applyInitialResultsFilters();

            // display
            updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', resultsDataFiltered);
            $('.filter').prop("disabled", false);
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
            @if ($cardpool !== '' && $cardpool !== '-')
                var availableCardpools = collectOptions('cardpool');
                requestedCardpool = '{{ $cardpool }}';

                if (requestedCardpool in availableCardpools) {
                    filterTournamentData(resultsDataFiltered, 'cardpool', availableCardpools[requestedCardpool]);
                    document.getElementById('cardpool').value = availableCardpools[requestedCardpool];
                    $('#filter-cardpool').addClass('active-filter');
                }
            @endif

            // type from URL
            @if ($type !== '' && $type !== '-')
                var availableTypes = collectOptions('tournament_type_id');
                requestedType = '{{ $type }}';

                if (requestedType in availableTypes) {
                    filterTournamentData(resultsDataFiltered, 'type', availableTypes[requestedType]);
                    document.getElementById('tournament_type_id').value = availableTypes[requestedType];
                    $('#filter-type').addClass('active-filter');
                }
            @endif

            // format from URL
            @if ($format !== '' && $format !== '-')
                var availableFormats = collectOptions('format');
                requestedFormat = '{{ $format }}';

                if (requestedFormat in availableFormats) {
                    filterTournamentData(resultsDataFiltered, 'format', availableFormats[requestedFormat]);
                    document.getElementById('format').value = availableFormats[requestedFormat];
                    $('#filter-format').addClass('active-filter');
                }
            @endif

            // country from URL
            @if ($country !== '' && $country !== '-')
                var availableCountries = collectOptions('location_country');
                requestedCountry = '{{ $country }}';

                if (requestedCountry in availableCountries) {
                    filterTournamentData(resultsDataFiltered, 'location_country', convertFromURLString(requestedCountry));
                    document.getElementById('location_country').value = availableCountries[requestedCountry];
                    $('#filter-country').addClass('active-filter');
                }
            @elseif (@$default_country && $country !== '-')
                // user's default country
                defaultCountry = '{{ $default_country }}';
                filterTournamentData(resultsDataFiltered, 'location_country', defaultCountry);
                $('#label-default-country').removeClass('hidden-xs-up');
                document.getElementById('location_country').value = defaultCountry;
                $('#filter-country').addClass('active-filter');
                updateResultsURL(requestedCardpool, requestedType, defaultCountry, requestedFormat,
                        document.getElementById('videos').checked);
            @endif

            // just tournaments with videos
            @if ($videos !== '' && $videos !== '-')
                filterTournamentData(resultsDataFiltered, 'videos', true);
                document.getElementById('videos').checked = true;
                $('#filter-video').addClass('active-filter');
            @endif

        }
    </script>
@stop

