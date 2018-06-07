@extends('layout.general')

@section('content')
    <div id="page-profile">
        <h4 class="page-header p-b-1 m-b-0">
            {{--Edit button--}}
            @if (Auth::check() && Auth::user()->id == $user->id)
                <div class="pull-right">
                    <a class="btn btn-primary" href="#" @click="editMode=true" id="button-edit" v-if="!editMode" v-cloak>
                        <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                    </a>
                    <a class="btn btn-secondary" href="#" @click="cancelEdits()" id="button-cancel" v-if="editMode" v-cloak>
                        <i class="fa fa-times" aria-hidden="true"></i> Cancel
                    </a>
                    <a class="btn btn-info" href="#" id="button-save" @click="saveProfile()" v-if="editMode" v-cloak>
                        <i class="fa fa-pencil" aria-hidden="true"></i> Save
                    </a>
                </div>
            @endif
            Profile - <span class="{{ $user->linkClass() }}" v-cloak>@{{ displayUserName }}</span>
        </h4>

        {{--Tabs--}}
        <div class="modal-tabs p-b-1">
            <ul id="profile-tabs" class="nav nav-tabs" role="tablist">
                <li class="nav-item" id="tabf-info">
                    <a class="nav-link active" data-toggle="tab" href="#tab-info" role="tab">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        Info
                    </a>
                </li>
                <li class="nav-item" id="tabf-collection">
                    <a class="nav-link" data-toggle="tab" href="#tab-collection" role="tab">
                        <i class="fa fa-gift" aria-hidden="true"></i>
                        Prize collection
                    </a>
                </li>
            </ul>
        </div>

        {{--Tab panes--}}
        <div class="tab-content">
            @include('profile.tab-info')
            @include('profile.tab-collection')
        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">

        var chart, chartOptions, chartDataTable;

        var pageProfile= new Vue({
            el: '#page-profile',
            data: {
                prizes: [],
                userId: {{ $user->id }},
                prizeCollection: {},
                editMode: false,
                collectionLoaded: false,
                user: {
                    username_preferred: '{{ $user->username_preferred }}',
                    username_real: '{{ $user->username_real }}',
                    username_jinteki: '{{ $user->username_jinteki }}',
                    username_slack: '{{ $user->username_slack }}',
                    username_stimhack: '{{ $user->username_stimhack }}',
                    username_twitter: '{{ $user->username_twitter }}',
                    favorite_faction: '{{ $user->favorite_faction }}',
                    show_chart: '{{ $user->show_chart }}',
                    about: `{{ $user->about }}`,
                    website: '{{ $user->website }}',
                    autofilter_upcoming: '{{ $user->autofilter_upcoming }}' == 1,
                    autofilter_results: '{{ $user->autofilter_results }}' == 1,
                    show_chart: '{{ $user->show_chart }}' == 1,
                    country_id: '{{ $user->country_id }}',
                    country: '{{ $user->country_id }}' == 0 ? {} : {
                        flag: '{{ @$user->country->flag }}',
                        name: '{{ @$user->country->name }}',
                    }
                },
                userOriginal: {},
                countryMapping: {},
                claimCount: '{{ $claim_count }}'
            },
            components: {},
            computed: {
                displayUserName: function() {
                    if (this.user.username_preferred.length > 0) {
                        return this.user.username_preferred;
                    }
                    return '{{ $user->name }}';
                },
                markdownAbout: function () {
                    if (this.user.about == '' || this.user.about == null) {
                        return '';
                    }
                    return marked(this.user.about, { sanitize: true })
                }
            },
            mounted: function () {
                this.userOriginal = JSON.parse(JSON.stringify(this.user)); // copy object
                this.initFactions();
                this.loadCountries();
                this.loadPrizes();
                // tournament claims chart
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(this.drawClaimChart);
            },
            methods: {
                loadPrizes: function() {

                },
                loadCountries: function() {
                    axios.get('/api/country-mapping').then(function (response) {
                                pageProfile.countryMapping = response.data;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the countries.', '', {timeOut: 2000});
                    });
                },
                cancelEdits: function() {
                    this.user = JSON.parse(JSON.stringify(this.userOriginal)); // copy object
                    this.editMode = false;
                },
                saveProfile: function() {
                    axios.post('/profile/' + this.userId, this.user)
                            .then(function(response) {
                                toastr.info('Profile updated successfully.', '', {timeOut: 2000});
                                pageProfile.editMode = false;
                                // draw chart
                                if (!pageProfile.userOriginal.show_chart && pageProfile.user.show_chart) {
                                    pageProfile.drawClaimChart();
                                }
                                this.userOriginal = JSON.parse(JSON.stringify(this.user)); // copy object
                                // update country
                                var elt = document.getElementById('country_id');
                                pageProfile.user.country.name = elt.options[elt.selectedIndex].text;
                                pageProfile.user.country.flag =
                                        pageProfile.countryMapping[pageProfile.user.country.name];

                            }, function(response) {
                                // error handling
                                toastr.error('Something went wrong.', '', {timeOut: 2000});
                            }
                    );
                },
                factionCodeToFactionTitle: function(code) {
                    switch (code) {
                        case '' : return '--- not set ---';
                        case 'weyland-cons': return 'Weyland Consortium';
                        case 'haas-bioroid': return 'Haas-Bioroid';
                        case 'sunny-lebeau': return 'Sunny Lebeau';
                    }
                    return code.charAt(0).toUpperCase() + code.substr(1);
                },
                initFactions: function() {
                    $('#favorite_faction option').each(function(i, obj) {
                        if (i > 0) {
                            obj.text = factionCodeToFactionTitle(obj.value);
                        }
                    });
                },
                drawClaimChart: function() {

                    if (this.user.show_chart && this.claimCount > 2) {

                        chartDataTable = new google.visualization.DataTable();

                        chartDataTable.addColumn('date', 'date');
                        chartDataTable.addColumn('number', 'rank');
                        chartDataTable.addColumn({type: 'string', role: 'style'});
                        chartDataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});

                        // polinomial constants for size calculation
                        var poly1 = -0.00084, poly2 = 0.2789, poly3 = 2.8224;

                        @foreach($claims->reverse() as $claim)

                            <?php
                                $tooltip = '<div style="padding: 0.5em"><strong>'.addslashes($claim->tournament->title).
                                    '</strong><br/>claim: #'.$claim->rank().'/'.$claim->tournament->players_number.
                                    '&nbsp;<img src="/img/ids/'.$claim->runner_deck_identity.'.png" class="id-medium">'.
                                    '&nbsp;<img src="/img/ids/'.$claim->corp_deck_identity.'.png" class="id-medium"></div>';
                            ?>

                            chartDataTable.addRow([
                                    new Date({{ substr($claim->tournament->date, 0, 4) }},
                                            {{ intval(substr($claim->tournament->date, 5, 2))-1 }},
                                            {{ substr($claim->tournament->date, 8, 2) }}),
                                    {{ ($claim->rank() - $claim->tournament->players_number) / (-$claim->tournament->players_number+1)}},
                                    'point { fill-color: ' + tournamentTypeToColor({{$claim->tournament->tournament_type_id}}) +
                                    '; size: ' + Math.round(poly1 * {{ $claim->tournament->players_number }}  * {{ $claim->tournament->players_number }} +poly2 * {{ $claim->tournament->players_number }} +poly3) +
                                    '; stroke-color: #fff }',
                                    '{!! $tooltip !!}'
                                ]);

                        @endforeach

                        chartOptions = {
                            legend: 'none',
                            tooltip: {isHtml: true},
                            series: {0: {lineWidth: 0, pointSize: 5}},
                            vAxis: {viewWindow: {min: -0.2, max: 1.2}, ticks: [{v: 1, f: 'first'}, {v: 0, f: 'last'}]}
                        };


                        chart = new google.visualization.LineChart(document.getElementById('chart-claim'));
                        chart.draw(chartDataTable, chartOptions);
                    }
                }
            }
        });

        // initializer pagers for claim and created list
        @if ($claim_count > 0)
            updatePaging('list-claims');
        @endif
        @if ($created_count > 0)
            updatePaging('list-created');
        @endif

        //create trigger to resizeEnd event
        $(window).resize(function() {
            if(this.resizeTO) clearTimeout(this.resizeTO);
            this.resizeTO = setTimeout(function() {
                $(this).trigger('resizeEnd');
            }, 500);
        });

        //redraw graph when window resize is completed
        $(window).on('resizeEnd', function() {
            chart.draw(chartDataTable, chartOptions);
        });

    </script>
@stop

