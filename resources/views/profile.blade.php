@extends('layout.general')

@section('content')
    <div id="page-profile">
        <h4 class="page-header p-b-1 m-b-0">
            {{--Edit button--}}
            <div class="pull-right" v-if="userId == visitorId" v-cloak>
                <button class="btn btn-primary" href="#" @click="editMode=true; confirmNavigatingAway(true);"
                        id="button-edit" v-if="!editMode" v-cloak>
                    <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                </button>
                <button class="btn btn-secondary" href="#" @click="cancelEdits()" id="button-cancel" v-if="editMode" v-cloak>
                    <i class="fa fa-times" aria-hidden="true"></i> Cancel
                </button>
                <button class="btn btn-info" href="#" id="button-save" @click="saveProfile()" v-if="editMode" v-cloak>
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </button>
            </div>
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
                <li class="nav-item" id="tabf-my-art" v-if="user.artist">
                    <a class="nav-link" data-toggle="tab" href="#tab-my-art" role="tab">
                        <i class="fa fa-paint-brush" aria-hidden="true"></i>
                        My art
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
            @include('profile.tab-my-art')
            @include('profile.tab-collection')
        </div>
    </div>

    @include('profile.component-collection')

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">

        var chart, chartOptions, chartDataTable;

        var collectionPart = {
            template: '#template-collection-part',
            props: ['title', 'edit-mode', 'public', 'collection-loaded', 'prize-collection', 'part', 'prize-items',
                'prize-kits', 'icon', 'own-data', 'extra-text'],
            name: 'collection-part',
            computed: {
                hasData: function() {
                    for (var k in this.prizeCollection) {
                        if (this.prizeCollection.hasOwnProperty(k)) {
                            for (var j in k) {
                                if (this.prizeCollection[k].hasOwnProperty(j) && this.prizeCollection[k][j][this.part] > 0) {
                                    return true;
                                }
                            }
                        }
                    }
                    return false;
                },
                markdownText: function () {
                    if (this.extraText == '' || this.extraText == null) {
                        return '';
                    }
                    return marked(this.extraText, { sanitize: true, gfm: true, breaks: true })
                }
            },
            methods: {
                hasDataIn: function(key) {
                    for (var k in this.prizeCollection[key]) {
                        if (this.prizeCollection[key].hasOwnProperty(k) && this.prizeCollection[key][k][this.part] > 0) {
                            return true;
                        }
                    }
                    return false;
                }
            }
        };

        var pageProfile= new Vue({
            el: '#page-profile',
            data: {
                prizeKits: {},
                prizeItems: {},
                userId: {{ $user->id }},
                visitorId: {{ Auth::check() ? Auth::user()->id : 0 }},
                prizeCollection: {},
                prizeCollectionByType: {},
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
                    },
                    prize_owning_public: {{ $user->prize_owning_public }},
                    prize_trading_public: {{ $user->prize_trading_public }},
                    prize_wanting_public: {{ $user->prize_wanting_public }},
                    prize_owning_text: `{{ $user->prize_owning_text }}`,
                    prize_trading_text: `{{ $user->prize_trading_text }}`,
                    prize_wanting_text: `{{ $user->prize_wanting_text }}`,
                    artist: '{{ $user->artist_id !== NULL }}' == 1
                },
                artist: {},
                art_item: {},
                art_types: {!! json_encode($art_types) !!},
                maxArtPhotos: 3,
                userOriginal: {},
                countryMapping: {},
                claimCount: '{{ $claim_count }}',
                confirmCallback: function () {},
                confirmText: '',
                modalTitle: '',
                modalButton: '',
                editItemMode: false
            },
            components: {
                collectionPart : collectionPart
            },
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
                    return marked(this.user.about, { sanitize: true, gfm: true, breaks: true })
                },
                markdownArtistDescription: function () {
                    if (this.artist.description == '' || this.artist.description == null) {
                        return '';
                    }
                    return marked(this.artist.description, { sanitize: true, gfm: true, breaks: true })
                }
            },
            mounted: function () {
                this.userOriginal = JSON.parse(JSON.stringify(this.user)); // copy object
                this.initFactions();
                this.loadCountries();
                this.loadPrizes();
                if (this.user.artist) {
                    this.loadArtist();
                }
                // Enable gallery
                $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                    event.preventDefault();
                    $(this).ekkoLightbox({alwaysShowClose: true});
                });
                // tournament claims chart
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(this.drawClaimChart);
            },
            methods: {
                // load prize DB
                loadPrizes: function() {
                    axios.get('/api/prizes').then(function (response) {
                        for (var i = 0; i < response.data.length; i++) {
                            // prize kits
                            pageProfile.$set(
                                    pageProfile.prizeKits,
                                    response.data[i].id,
                                    {
                                        year: response.data[i].year,
                                        title: response.data[i].title,
                                        photoUrl: response.data[i].photos.length > 0
                                                ? response.data[i].photos[0].url : null,
                                        photoUrlThumb: response.data[i].photos.length > 0
                                                ? response.data[i].photos[0].urlThum : null
                                    }
                            );
                            // prize items
                            for (var u = 0; u < response.data[i].elements.length; u++) {
                                pageProfile.$set(
                                        pageProfile.prizeItems,
                                        response.data[i].elements[u].id,
                                        {
                                            prizeKitId: response.data[i].id,
                                            title: response.data[i].elements[u].title,
                                            type: response.data[i].elements[u].type,
                                            photoUrl: response.data[i].elements[u].photos.length > 0
                                                ? response.data[i].elements[u].photos[0].url : null,
                                            photoUrlThumb: response.data[i].elements[u].photos.length > 0
                                                    ? response.data[i].elements[u].photos[0].urlThumb : null,
                                        }
                                );
                            }
                        }
                        // load prize collection
                        pageProfile.loadCollection();
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                    });
                },
                // load user's collection
                loadCollection: function() {
                    axios.get('/api/prize-collections/'+this.userId).then(function (response) {
                        for (var i = 0; i < response.data.length; i ++) {
                            // unordered collection
                            pageProfile.$set(
                                    pageProfile.prizeCollection,
                                    response.data[i].prize_element_id,
                                    response.data[i]
                            );

                            // collection grouped by type
                            if (!(pageProfile.prizeItems[response.data[i].prize_element_id].type
                                    in pageProfile.prizeCollectionByType)) {
                                pageProfile.$set(
                                        pageProfile.prizeCollectionByType,
                                        pageProfile.prizeItems[response.data[i].prize_element_id].type,
                                        []
                                )
                            }
                            pageProfile.prizeCollectionByType[pageProfile.prizeItems[response.data[i].prize_element_id].type].push(response.data[i]);

                        }
                        pageProfile.collectionLoaded = true;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the user\'s collection.', '', {timeOut: 2000});
                    });
                },
                loadCountries: function() {
                    axios.get('/api/country-mapping').then(function (response) {
                                pageProfile.countryMapping = response.data;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the countries.', '', {timeOut: 2000});
                    });
                },
                loadArtist: function() {
                    axios.get('/api/artists/' + '{{ $user->artist_id }}').then(function (response) {
                                pageProfile.artist = response.data;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading artist details.', '', {timeOut: 2000});
                    });
                },
                cancelEdits: function() {
                    this.user = JSON.parse(JSON.stringify(this.userOriginal)); // copy object
                    this.editMode = false;
                    this.confirmNavigatingAway(false);
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
                                pageProfile.userOriginal = JSON.parse(JSON.stringify(pageProfile.user)); // copy object
                                pageProfile.confirmNavigatingAway(false);
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
                    // save artist details too
                    if (this.user.artist) {
                        this.saveArtistDetails();
                    }
                },
                saveArtistDetails: function() {
                    axios.put('/api/artists/' + '{{ $user->artist_id }}', this.artist)
                        .then(function(response) {
                                toastr.info('Artist details updated successfully.', '', {timeOut: 2000});
                            }, function(response) {
                                // error handling
                                toastr.error('Something went wrong.', '', {timeOut: 2000});
                            }
                        );
                },
                hidePopovers: function() {
                    $('.popover').popover('hide');
                },
                modalForAddArtItem: function() {
                    this.art_item = { proper: true, official: false, artist_id: this.artist.id };
                    this.modalTitle = 'Create Art item';
                    this.modalButton = 'Create';
                    this.editItemMode = false;
                    $("#modal-art-item").modal('show');
                    $('[data-toggle="popover"]').popover();
                },
                modalForEditArtItem: function(artIndex) {
                    this.art_item = this.artist.items[artIndex];
                    this.art_item.typeHelper = this.art_item.type;
                    this.modalTitle = 'Edit Art item';
                    this.modalButton = 'Save';
                    this.editItemMode = true;
                    $("#modal-art-item").modal('show');
                    $('[data-toggle="popover"]').popover();
                },
                createArtItem: function() {
                    axios.post('/api/prize-items', this.art_item)
                        .then(function(response) {
                            pageProfile.loadArtist();
                            $("#modal-art-item").modal('hide');
                            toastr.info('Art item is created successfully.', '', {timeOut: 2000});
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
                },
                updateArtItem: function() {

                },
                deleteArtItem: function(artId) {
                    axios.delete('/api/prize-items/' + artId).then(function (response) {
                        pageProfile.loadArtist();
                        toastr.info('Art item is deleted.', '', {timeOut: 2000});
                    }, function(response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                    });
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
                confirmNavigatingAway: function(confirmNeeded) {
                    if (confirmNeeded) {
                        window.onbeforeunload = function() {
                            return true;
                        };
                    } else {
                        window.onbeforeunload = null
                    }
                },
                registerArtist: function() {
                    axios.post('/api/artists/register').then(function(response) {
                        toastr.info('Registered as artist successfully.', '', {timeOut: 2000});
                        pageProfile.user.artist = true;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                    });
                },
                unregisterArtist: function() {
                    axios.post('/api/artists/unregister').then(function(response) {
                        toastr.info('Unregistered as artist successfully.', '', {timeOut: 2000});
                        pageProfile.user.artist = false;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                    });
                },
                drawClaimChart: function() {

                    if (this.user.show_chart && this.claimCount > 2) {

                        chartDataTable = new google.visualization.DataTable();

                        chartDataTable.addColumn('date', 'date');
                        chartDataTable.addColumn('number', 'rank');
                        chartDataTable.addColumn({type: 'string', role: 'style'});
                        chartDataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});

                        // constants for logarithmic size calculation: const1 * log(const2 * X + const3)
                        var const1 = 15, const2 = 0.2, const3 = 1;

                        @foreach($claims_by_size as $claim)

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
                                    '; size: ' + Math.round(const1 * Math.log10(const2 * {{ $claim->tournament->players_number }} + const3)) +
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
            if (pageProfile.userOriginal.show_chart && pageProfile.claimCount > 2) {
                chart.draw(chartDataTable, chartOptions);
            }
        });

    </script>
@stop

