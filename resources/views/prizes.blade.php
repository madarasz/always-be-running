@extends('layout.general')

@section('content')
<div id="prize-browser">
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback"></confirm-modal>
    <h4 class="page-header" :style="userId ? 'padding-bottom: 2.5em;' : ''" style="margin-bottom: 0">
        Prizes
        {{--Edit/Save/Cancel button--}}
        <div class="pull-right p-b-1" v-if="userId">
            <div class="text-xs-center">
                {{--Edit button--}}
                <button class="btn btn-primary" v-if="!editMode" @click="editMode = true" :disabled="!collectionLoaded">
                <i aria-hidden="true" class="fa fa-pencil"></i>
                Edit collection
                </button>
                {{--Save button--}}
                <button class="btn btn-info" v-if="editMode" @click="saveCollection()">
                <i aria-hidden="true" class="fa fa-check"></i>
                Save collection
                </button>
                {{--Cancel button--}}
                <confirm-button button-class="btn btn-secondary" button-icon="fa fa-times" button-text="Cancel" v-if="editMode"
                @click="confirmCallback = function() { loadCollection() }; confirmText = 'Cancel collection edits?'" />
            </div>
            <div class="text-xs-center" style="font-size: 65%; padding-top: 0.5em">
                <em>
                    your collection may be <br/>
                    listed on your <strong><a :href="'/profile/'+userId+'#tab-collection'">Profile</a></strong>
                </em>
            </div>
        </div>
    </h4>

    <div class="modal-tabs">
        <ul id="prizes-tabs" class="nav nav-tabs" role="tablist">
            <li class="nav-item notif-red notif-badge" id="tabf-official">
                <a class="nav-link active" data-toggle="tab" href="#tab-official" role="tab">
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    Official kits
                </a>
            </li>
            <li class="nav-item notif-red notif-badge" id="tabf-other">
                <a class="nav-link" data-toggle="tab" href="#tab-other" role="tab">
                    <i class="fa fa-paint-brush" aria-hidden="true"></i>
                    Other art
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        @include('prizes.official')
        @include('prizes.other')
    </div>
</div>

    <script type="text/javascript">
        var prizeBrowser= new Vue({
            el: '#prize-browser',
            data: {
                prizes: [],
                artists: [],
                prizeVisibility: [],
                selectedPrizeId: 0,
                searchText: '',
                foundKit: true,
                userId: {{ $user_id }},
                prizeCollection: {},
                editMode: false,
                collectionLoaded: false,
                collectionChange: false,
                confirmText: '',
                confirmCallback: function () {}
            },
            components: {
            },
            computed: {},
            mounted: function () {
                this.loadPrizes();
                this.loadArtists();
            },
            filters: {
              searchHighlight: function(value) {
                  if (prizeBrowser.searchText.length < 3 || !value) {
                      return value;
                  } else {
                      var iQuery = new RegExp(prizeBrowser.searchText, "ig");
                      return value.toString().replace(iQuery, function(matchedTxt,a,b) {
                          return ('<span class=\'highlight\'>' + matchedTxt + '</span>');
                      });
                  }
              }
            },
            methods: {
                // load all my groups
                loadPrizes: function () {
                    axios.get('/api/prizes').then(function (response) {
                        prizeBrowser.prizes = response.data;
                        prizeBrowser.updateFilter();
                        // filling up collection with blanks
                        for (var i = 0; i < prizeBrowser.prizes.length; i++) {
                            for (var u = 0; u < prizeBrowser.prizes[i].elements.length; u++) {
                                // adding as new reactive element
                                prizeBrowser.$set(prizeBrowser.prizeCollection, prizeBrowser.prizes[i].elements[u].id, {
                                    owning: 0,
                                    trading: 0,
                                    wanting: 0
                                });
                            }
                        }
                        // load prize collection
                        if (prizeBrowser.userId > 0) {
                            prizeBrowser.loadCollection();
                        }
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                    });
                },
                loadArtists: function() {
                    axios.get('/api/artists').then(function (response) {
                        // just artists with art items
                        prizeBrowser.artists = response.data.filter((x) => x.items.length > 0);
                    },  function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the artists.', '', {timeOut: 2000});
                    });
                },
                // update search results
                updateFilter: function() {
                    var defaultValue = this.selectedPrizeId == 0 && this.searchText.length <= 2;
                    this.foundKit = defaultValue;
                    this.prizeVisibility = [];
                    for (var i = 0; i < this.prizes.length; i++) {
                        this.prizeVisibility.push(defaultValue);
                    }
                    // filter for one prize kit
                    if (this.selectedPrizeId > 0) {
                        for (var i = 0; i < this.prizes.length; i++) {
                            if (this.prizes[i].id == this.selectedPrizeId) {
                                this.prizeVisibility[i] = true;
                                this.foundKit = true;
                                return true;
                            }
                        }
                    } else if (this.searchText.length > 2) {
                        for (var i = 0; i < this.prizes.length; i++) {
                            // prize kit year / title matches
                            if ((this.prizes[i].year+this.prizes[i].title.toUpperCase()).indexOf(this.searchText.toUpperCase()) > -1) {
                                this.prizeVisibility[i] = true;
                                this.foundKit = true;
                            } else {
                                // prize item title / type matches
                                for (var u = 0; u < this.prizes[i].elements.length; u++) {
                                    var prizeItem = this.prizes[i].elements[u];
                                    if ((prizeItem.title && prizeItem.title.toUpperCase().indexOf(this.searchText.toUpperCase()) > -1) ||
                                            prizeItem.type.toUpperCase().indexOf(this.searchText.toUpperCase()) > -1) {
                                        this.prizeVisibility[i] = true;
                                        this.foundKit = true;
                                    }
                                }
                            }
                        }
                    }
                },
                // load all my groups
                loadCollection: function () {
                    axios.get('/api/prize-collections/'+this.userId).then(function (response) {
                        for (var i = 0; i < response.data.length; i ++) {
                            prizeBrowser.$set(
                                    prizeBrowser.prizeCollection,
                                    response.data[i].prize_element_id,
                                    response.data[i]
                            );
                        }
                        prizeBrowser.collectionLoaded = true;
                        prizeBrowser.editMode = false;
                        window.onbeforeunload = null;
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading your collection.', '', {timeOut: 2000});
                    });
                },
                modifyCollection: function(id, field, increment) {
                    if (parseInt(this.prizeCollection[id][field]) + increment > -1 &&
                            parseInt(this.prizeCollection[id][field]) + increment < 101) {
                        this.prizeCollection[id][field] = parseInt(this.prizeCollection[id][field])+ increment;
                        this.collectionChange = true;
                        // sure you want to leave when navigating away
                        window.onbeforeunload = function() {
                            return true;
                        };
                    }
                },
                saveCollection: function() {
                    if (this.collectionChange) {
                        axios.put('/api/prize-collections/'+this.userId, this.prizeCollection)
                                .then(function(response) {
                                    toastr.info('Prize collection updated successfully.', '', {timeOut: 2000});
                                    prizeBrowser.editMode = false;
                                    prizeBrowser.collectionChange = false;
                                    window.onbeforeunload = null;
                                }, function(response) {
                                    // error handling
                                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                                }
                        );
                    } else {
                        toastr.info('There was no change made.', '', {timeOut: 2000});
                        prizeBrowser.editMode = false;
                    }

                },
                markdownDescription: function (description) {
                    if (description == null || description.length == 0) {
                        return '';
                    }
                    return marked(description, {sanitize: true, gfm: true, breaks: true})
                }
            }
        });

        {{--Enable gallery--}}
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({alwaysShowClose: true});
        });
    </script>
@stop

