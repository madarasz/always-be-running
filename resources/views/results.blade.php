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
            </div>
            <div class="text-xs-center small-text">
                <i title="charity" class="fa fa-heart text-danger"></i>
                charity |
                <img class="img-patron-o">
                patreon T.O. |
                <i title="match data" class="fa fa-handshake-o"></i>
                match data, points available |
                <i title="video" class="fa fa-video-camera"></i>
                has video<br/>
                <span class="tournament-type type-store" title="store championship">S</span> store championship |
                <span class="tournament-type type-regional" title="regional championship">R</span> regional championship |
                <span class="tournament-type type-national" title="national championship">N</span> national championship |
                <span class="tournament-type type-world" title="worlds championship">W</span> worlds championship
            </div>
        </div>
        <div class="col-md-4 pull-md-8 col-lg-3 pull-lg-9 col-col-sm-12">
            {{--Filters--}}
            <div class="bracket">
                <h5><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>
                {!! Form::open(['url' => '/tournaments']) !!}
                    <div class="form-group" id="filter-cardpool">
                        {!! Form::label('cardpool', 'Cardpool') !!}
                        {!! Form::select('cardpool', $tournament_cardpools,
                            null, ['class' => 'form-control filter',
                            'onchange' => "filterResults(defaultFilter, packlist, '".@$default_country_id."')", 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-type">
                        {!! Form::label('tournament_type_id', 'Type') !!}
                        {!! Form::select('tournament_type_id', $tournament_types,
                            null, ['class' => 'form-control filter',
                            'onchange' => "filterResults(defaultFilter, packlist, '".@$default_country_id."')", 'disabled' => '']) !!}
                    </div>
                    <div class="form-group" id="filter-country">
                        {!! Form::label('location_country', 'Country') !!}
                        {!! Form::select('location_country', $countries, null,
                            ['class' => 'form-control filter',
                            'onchange' => "filterResults(defaultFilter, packlist, '".@$default_country_id."')", 'disabled' => '']) !!}
                        <div class="legal-bullshit text-xs-center hidden-xs-up" id="label-default-country">
                            using user's default filter
                        </div>
                    </div>
                    <div class="form-group" id="filter-video">
                        {!! Form::checkbox('videos', null, null, ['id' => 'videos',
                            'onchange' => "filterResults(defaultFilter, packlist, '".@$default_country_id."')"]) !!}
                        {!! Html::decode(Form::label('videos', 'has video <i class="fa fa-video-camera" aria-hidden="true"></i>')) !!}
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
        var defaultFilter = "approved=1&concluded=1&recur=0&end={{ $nowdate }}",
                newFilter = defaultFilter,  // changed with user's default filter
                packlist = [],
                currentPack = "",
                runnerIDs = [], corpIDs = [];

        @if ($cardpool !== '' && $cardpool !== '-')
            // cardpool from URL
            var availableCardpools = collectOptions('cardpool'),
                    requestedCardpool = '{{ $cardpool }}';
            if (requestedCardpool in availableCardpools) {
                document.getElementById('cardpool').value = availableCardpools[requestedCardpool];
                newFilter = newFilter + '&cardpool=' + availableCardpools[requestedCardpool];
                $('#filter-cardpool').addClass('active-filter');
            }
        @endif

        @if ($type !== '' && $type !== '-')
            // type from URL
            var availableTypes = collectOptions('tournament_type_id'),
                    requestedType = '{{ $type }}';
            if (requestedType in availableTypes) {
                document.getElementById('tournament_type_id').value = availableTypes[requestedType];
                newFilter = newFilter + '&type=' + availableTypes[requestedType];
                $('#filter-type').addClass('active-filter');
            }
        @endif

        @if ($videos !== '' && $videos !== '-')
            // just tournaments with videos
            newFilter = newFilter + '&videos=1';
            document.getElementById('videos').checked = true;
            $('#filter-video').addClass('active-filter');
        @endif

        @if ($country !== '' && $country !== '-')
            // country from URL
            var availableCountries = collectOptions('location_country'),
                    requestedCountry = '{{ $country }}';
            if (requestedCountry in availableCountries) {
                document.getElementById('location_country').value = availableCountries[requestedCountry];
                newFilter = newFilter + '&country=' + convertFromURLString(requestedCountry);
                $('#filter-country').addClass('active-filter');
            }
        @elseif (@$default_country)
            // user's default country
            newFilter = defaultFilter + '&country=' + '{{ $default_country }}';
            $('#label-default-country').removeClass('hidden-xs-up');
            document.getElementById('location_country').value = '{{ $default_country_id }}';
            $('#filter-country').addClass('active-filter');
        @endif

        // table entries
        getTournamentData(newFilter, function(data) {
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
                updateIdStats(packs[0]);
            });
        }

        // redraw charts on window resize
        $(window).resize(function(){
            drawResultStats('stat-chart-runner', runnerIDs, 0.04);
            drawResultStats('stat-chart-corp', corpIDs, 0.04);
        });
    </script>
@stop

