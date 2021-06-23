@extends('layout.general')

@section('content')
    <h4 class="page-header">Netrunner Tournament Results</h4>
    @include('partials.message')
    @include('tournaments.modals.conclude')
    <div class="row" id="results-page">
        {{--Results table--}}
        <div class="col-lg-9 push-lg-3 col-12" id="col-results">
            <div class="bracket">
                {{--Result / to be concluded tabs--}}
                <div class="modal-tabs">
                    <ul id="result-tabs" class="nav nav-tabs" role="tablist">
                        <li class="nav-item" id="t-results" role="tab">
                            <a class="nav-link active" data-toggle="tab" href="#tab-results">
                                <h5>
                                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                                    Tournament results
                                    <br/>
                                    <small>concluded tournaments from the past</small>
                                </h5>
                            </a>
                        </li>
                        <li class="nav-item" id="t-to-be-concluded" role="tab">
                            <a class="nav-link" data-toggle="tab" href="#tab-to-be-concluded" @click="getToConcludeData()">
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
                        <div class="loader" id="results-loader" v-if="resultsData.length == 0">&nbsp;</div>
                        <tournament-table :tournaments="resultsFiltered" table-id="results" :is-loaded="resultsLoaded" :headers="resultHeaders" 
                                empty-message="no tournaments to show" v-on:pageforward="resultPageForward" :tournament-count="resultsFilteredCount"/>
                    </div>
                    {{--To be concluded table--}}
                    <div class="tab-pane" id="tab-to-be-concluded" role="tabpanel">
                        {{--Warning for not logged in users--}}
                        @if (!@Auth::user())
                            <div class="alert alert-warning text-xs-center" id="warning-conclude">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                Please <a href="/oauth2/redirect">login via NetrunnerDB</a> to conclude tournaments.
                            </div>
                        @endif
                        <div class="loader" id="to-be-concluded-loader" v-if="toConcludeData.length == 0">&nbsp;</div>
                        <tournament-table :tournaments="toConcludeFiltered" table-id="to-be-concluded" :is-loaded="toConcludeLoaded" :headers="toConcludeHeaders" 
                                empty-message="no tournaments waiting for conclusion" :user-auth="userAuthenticated" :tournament-count="toConcludeFiltered.length"/>
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
                <form>
                    <div class="row no-gutters">
                        <div class="form-group col-xs-6 col-lg-12" id="filter-cardpool">
                            <label for="cardpool">Cardpool</label>
                            <select v-model="filterCardpool" @change="applyFilters" id="cardpool" name="cardpool" class="form-control filter" 
                                    :class="filterCardpool !== '---' ? 'active-filter' : ''" :disabled="!resultsLoaded">
                                <option v-for="cardpool in tournamentCardpools" :key="cardpool" :value="cardpool">@{{ cardpool }}</option>
                            </select> 
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-mwl">
                            <label for="mwl">Ban list</label>
                            <select v-model="filterMwl" @change="applyFilters" id="mwl" name="mwl" class="form-control filter" 
                                    :class="filterMwl !== '---' ? 'active-filter' : ''" :disabled="!resultsLoaded">
                                <option v-for="mwl in tournamentMwls" :key="mwl" :value="mwl">@{{ mwl }}</option>
                            </select> 
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-type">
                            <label for="tournament_type_id">Type</label>
                            <select v-model="filterType" @change="applyFilters" id="tournament_type_id" name="tournament_type_id" class="form-control filter" 
                                    :class="filterType !== '---' ? 'active-filter' : ''" :disabled="!resultsLoaded">
                                <option v-for="ttype in tournamentTypes" :key="ttype" :value="ttype">@{{ ttype }}</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-country">
                            <label for="location_country">Country</label>
                            <select v-model="filterCountry" @change="applyFilters" id="location_country" name="location_country" class="form-control filter" 
                                    :class="filterCountry !== '---' ? 'active-filter' : ''" :disabled="!resultsLoaded">
                                <option v-for="country in tournamentCountries" :key="country" :value="country">@{{ country }}</option>
                            </select>
                            @if (@$default_country !== null)
                            <div class="legal-bullshit text-xs-center" v-if="filterCountry === '{{ @$default_country }}'" id="label-default-country">
                                using user's default filter
                            </div>
                            @endif
                        </div>
                        <div class="form-group col-xs-6 col-lg-12" id="filter-format">
                            <label for="format">Format</label>
                            <select v-model="filterFormat" @change="applyFilters" id="format" name="format" class="form-control filter" 
                                    :class="filterFormat !== '---' ? 'active-filter' : ''" :disabled="!resultsLoaded">
                                <option v-for="format in tournamentFormats" :key="format" :value="format">@{{ format }}</option>
                            </select>
                        </div>
                        <div id="filter-video" class="form-group col-xs-6 col-lg-12 m-b-0" :class="filterVideo ? 'active-filter' : ''">
                            <input v-model="filterVideo" id="videos" @change="applyFilters" name="videos" type="checkbox" class="filter" :disabled="!resultsLoaded">
                            <label for="videos">has video <i aria-hidden="true" class="fa fa-video-camera"></i></label>
                        </div>
                        <div id="filter-matchdata" class="form-group col-xs-6 col-lg-12 m-b-0" :class="filterMatchdata ? 'active-filter' : ''">
                            <input v-model="filterMatchdata" id="matchdata" @change="applyFilters" name="matchdata" type="checkbox" class="filter" :disabled="!resultsLoaded">
                            <label for="matchdata">has match data <i aria-hidden="true" class="fa fa-handshake-o"></i></label>
                        </div>
                    </div>
                </form>
            </div>
            {{--Stats--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    Statistics<br/>
                    <div id="stat-packname" class="small-text text-xs-center p-b-1 p-t-1" v-if="statsLoaded">
                        <span v-if="!statError">@{{ currentPack.title }}<br/>@{{ currentPack.mwl }}</span>
                        <span v-else>@{{ filterCardpool }}</span>
                    </div>
                </h5>
                <div class="text-xs-center">
                    {{--runner ID chart--}}
                    <div class="loader-chart stat-load" v-if="!statsLoaded">loading</div>
                    <div id="stat-chart-runner" :class="statError ? 'hidden-xs-up' : ''"></div>
                    <div class="small-text p-b-1" v-if="statError">no stats available</div>
                    <div class="small-text p-b-1">runner IDs</div>
                    {{--corp ID chart--}}
                    <div class="loader-chart stat-load" v-if="!statsLoaded">loading</div>
                    <div id="stat-chart-corp" :class="statError ? 'hidden-xs-up' : ''"></div>
                    <div class="small-text p-b-1" v-if="statError">no stats available</div>
                    <div class="small-text">corp IDs</div>
                </div>
                <h5 class="text-xs-right p-t-1"><small>provided by <a href="http://www.knowthemeta.com">KnowTheMeta</a></small></h5>
            </div>
            {{--Featured--}}
            @if (count($featured))
                @include('tournaments.partials.featured-results')
            @endif
        </div>
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

        Vue.use(VueLazyload, {
            preLoad: 1.3,
            error: 'img/fail.png',
            loading: 'img/loading-spinner.gif',
            attempt: 1
        })

        var resultsPage = new Vue({
            el: '#results-page',
            data: {
                resultsData: [],
                toConcludeData: [],
                resultsFiltered: [],
                toConcludeFiltered: [],
                resultsLoaded: false,
                resultsLoadedFully: false,
                toConcludeLoaded: false,
                resultHeaders: ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'],
                toConcludeHeaders: ['title', 'date', 'location', 'cardpool', 'conclusion', 'regs'],
                showFlag: true,
                userAuthenticated: @if (@Auth::user()) true @else false @endif,
                tournamentCardpools: [ @foreach ($tournament_cardpools as $cardpoola) '{{ $cardpoola }}', @endforeach ],
                tournamentTypes: [ @foreach ($tournament_types as $ttype) '{{ $ttype }}', @endforeach ],
                tournamentCountries: [ @foreach ($countries as $countrya) '{{ $countrya }}', @endforeach ],
                tournamentFormats: [ @foreach ($tournament_formats as $formata) '{{ $formata }}', @endforeach ],
                tournamentMwls: [ @foreach ($tournament_mwls as $mwla) '{{ $mwla }}', @endforeach ],
                filterCardpool: @if ($cardpool !== null) convertFromURLString('{{ $cardpool }}') @else '---' @endif,
                filterMwl:  @if ($mwl !== null) convertFromURLString('{{ $mwl }}') @else '---' @endif,
                filterType: @if ($type !== null) '{{ $type }}' @else '---' @endif,
                filterCountry: @if ($country !== null) '{{ $country }}' @else @if (@$default_country !== null) '{{ $default_country }}' @else '---' @endif @endif,
                filterFormat: @if ($format !== null) '{{ $format }}' @else '---' @endif,
                filterVideo: @if ($videos !== null) true @else false @endif,
                filterMatchdata: @if ($matchdata !== null) true @else false @endif,
                packs: [],
                currentPack: {},
                metaStatsRunner: [],
                metaStatsCorp: [],
                statsLoaded: false,
                statError: false,
                offset: 100,
                limit: getCookie('pager-results-option') ? getCookie('pager-results-option') * 2 : 100,
                offsetIterator: 1000,
                resultsCount: {{ $results_count }}
            },
            mounted: function () {
                this.getResultsData(this.limit, 0)
                this.positionFilters()
                this.updateUrlWithFilters()
                google.charts.load('current', {'packages':['corechart']})
                google.charts.setOnLoadCallback(this.initCharts)
            },
            computed: {
                resultsFilteredCount: function() {
                    if (this.isFilterActive) return this.resultsFiltered.length
                    return this.resultsCount
                },
                isFilterActive: function() {
                    return this.filterCardpool !== '---' || this.filterType !== '---' || this.filterMwl !== '---' || this.filterCountry !== '---' ||
                                this.filterFormat !== '---' || this.filterVideo || this.filterMatchdata
                }
            },
            methods: {
                getResultsData: function(rlimit, roffset) {
                    this.resultsLoaded = false
                    $.ajax({
                        url: `/api/tournaments/results?limit=${rlimit}&offset=${roffset}`,
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            resultsPage.resultsData = resultsPage.resultsData.concat(data)
                            resultsPage.resultsFiltered = resultsPage.filterDataSet(resultsPage.resultsData)
                            resultsPage.resultsLoaded = true
                            if (resultsPage.resultsData.length >= resultsPage.resultsCount) {
                                resultsPage.resultsLoadedFully = true
                            } else if (resultsPage.isFilterActive) resultsPage.getMoreResultsData() // load all results if a filter is selected
                        }
                    })
                },
                getMoreResultsData: function() {
                    this.limit = this.offsetIterator
                    this.getResultsData(this.limit, this.offset)
                    this.offset += this.offsetIterator
                },
                resultPageForward: function(toIndex) {
                    if (this.resultsFiltered.length <= toIndex) this.getMoreResultsData()
                },
                getToConcludeData: function() {
                    $.ajax({
                        url: '/api/tournaments?concluded=0&recur=0&hide-non=1&desc=1&end={{ $nowdate }}',
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            resultsPage.toConcludeData = data
                            resultsPage.toConcludeFiltered = resultsPage.filterDataSet(data)
                            resultsPage.toConcludeLoaded = true
                        }
                    })
                },
                applyFilters: function() {
                    this.resultsFiltered = this.filterDataSet(this.resultsData)
                    if (this.resultsData.length < this.resultsCount) this.getMoreResultsData()
                    this.toConcludeFiltered = this.filterDataSet(this.toConcludeData)
                    this.updateUrlWithFilters()
                    this.switchIdStats()
                },
                filterDataSet: function(data) {
                    if (this.filterCardpool !== '---') data = data.filter(x => x.cardpool === this.filterCardpool)
                    if (this.filterType !== '---') data = data.filter(x => x.type === this.filterType)
                    if (this.filterMwl !== '---') data = data.filter(x => x.mwl === this.filterMwl)
                    if (this.filterCountry !== '---') data = data.filter(x => x.location_country === this.filterCountry)
                    if (this.filterFormat !== '---') data = data.filter(x => x.format === this.filterFormat)
                    if (this.filterVideo) data = data.filter(x => x.videos > 0)
                    if (this.filterMatchdata) data = data.filter(x => x.matchdata)
                    return data
                },
                updateUrlWithFilters: function() {
                    var newUrl = '/results?'
                    if (this.filterCardpool !== '---' )  newUrl += 'cardpool=' + convertToURLString(this.filterCardpool) + '&'
                    if (this.filterType !== '---') newUrl += 'type=' + this.filterType + '&'
                    if (this.filterMwl !== '---') newUrl += 'mwl=' + convertToURLString(this.filterMwl) + '&'
                    if (this.filterCountry !== '---') newUrl += 'country=' + this.filterCountry + '&'
                    if (this.filterFormat !== '---') newUrl += 'format=' + this.filterFormat + '&'
                    if (this.filterVideo) newUrl += 'videos=true&'
                    if (this.filterMatchdata) newUrl += 'matchdata=true&'
                    window.history.pushState("Results", "Results - " + this.filterCardpool + " - " + this.filterType + " - " + this.filterCountry, newUrl.slice(0, -1))
                },
                switchIdStats: function() {
                    if (this.filterCardpool == '---') {
                        this.currentPack = this.packs[0] // get latest stat
                        this.statError = false
                        this.getMetaStats(this.currentPack.file)
                    } else {
                        this.currentPack = this.packs.find(x => x.cardpool == this.filterCardpool)
                        if (this.currentPack) {
                            this.statError = false
                            this.getMetaStats(this.currentPack.file)
                        }
                        else {
                            this.statError = true
                            this.statsLoaded = true    
                            $("div[dir='ltr']").hide() // hide any leftover charts, don't know why 
                        }
                    }
                },
                initCharts: function() {
                    $.ajax({
                        url: 'https://alwaysberunning.net/ktm/metas.json',
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            resultsPage.packs = data
                            resultsPage.currentPack = data[0]
                            resultsPage.switchIdStats()
                        }
                    })
                },
                getMetaStats: function(metafile) {
                    resultsPage.statsLoaded = false
                    $.ajax({
                        url: 'https://alwaysberunning.net/ktm/' + metafile,
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            resultsPage.metaStatsRunner = data.identities.runner.map(x => { return { title: x.title, allStandingCount: x.used, faction: x.faction }})
                            resultsPage.metaStatsCorp = data.identities.corp.map(x => { return { title: x.title, allStandingCount: x.used, faction: x.faction }})     
                            resultsPage.statsLoaded = true
                            Vue.nextTick(function() {
                                drawResultStats('stat-chart-runner', resultsPage.metaStatsRunner, 0.04)
                                drawResultStats('stat-chart-corp', resultsPage.metaStatsCorp, 0.04) 
                            })
                        }
                    })
                },
                positionFilters: function () {
                    if (window.matchMedia( "(min-width: 992px)").matches) {
                        // lg-size
                        $('#col-other').prepend($('#bracket-filters'))
                    } else {
                        // below lg-size
                        $('#col-results').prepend($('#bracket-filters'))
                    }
                }
            }
        });

        // redraw charts on window resize
        $(window).resize(function(){
            if (resultsPage.statsLoaded) {
                drawResultStats('stat-chart-runner', resultsPage.metaStatsRunner, 0.04)
                drawResultStats('stat-chart-corp', resultsPage.metaStatsCorp, 0.04)
            }
            resultsPage.positionFilters()
        })
    </script>
@stop

