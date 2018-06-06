@extends('layout.general')

@section('content')
<div id="prize-browser">
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback"></confirm-modal>
    <h4 class="page-header p-b-1">
        Official prize kits
        {{--Edit/Save/Cancel button--}}
        <div class="pull-right p-b-1" v-if="userId">
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
    </h4>

    {{--Filters--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <div class="row">
                    {{--filter title--}}
                    <div class="col-xs-12 col-lg-2 col-md-4">
                        <h5 class="h5-filter">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                            Filter
                        </h5>
                    </div>
                    {{--select kit--}}
                    <div class="col-xs-12 col-lg-6 col-md-8" style="padding-bottom:0.5em">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-gift" aria-hidden="true"></i></span>
                            <select class="custom-select" style="width: 100%" v-model="selectedPrizeId"
                                    :disabled="searchText.length > 0" @change="updateFilter()">
                                <option value="0">--- all ---</option>
                                <option v-for="prize in prizes" :value="prize.id">@{{ prize.year+' '+prize.title }}</option>
                            </select>
                        </div>
                    </div>
                    {{--search--}}
                    <div class="col-xs-12 col-lg-4 col-md-8 offset-md-4 offset-lg-0">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search" aria-hidden="true"></i></span>
                            <input type="search" name="prize-search" v-model="searchText"
                                   class="form-control" :disabled="selectedPrizeId != 0" @input="updateFilter()"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--loader--}}
        <div class="col-xs-12 p-b-3" v-if="prizes.length == 0">
            <div class="loader" id="prizes-loader">&nbsp;</div>
        </div>
    </div>

    {{--Prize kit brackets--}}
    <div class="row" v-for="(prize, index) in prizes">
        <div :class="prizeVisibility[index] ? 'col-xs-12':'col-xs-12 hidden-xs-up'">
            <div class="bracket">
                {{--Photos--}}
                <h5>
                    <i aria-hidden="true" class="fa fa-gift"></i>
                    {{--@{{ prize.year + ' ' + prize.title | searchHighlight }}--}}
                    <span :inner-html.prop="prize.year + ' ' + prize.title | searchHighlight"></span>
                </h5>
                {{--Photos--}}
                <div class="row">
                    {{--Photos of prize--}}
                    <div class="gallery-item col-xl-2 col-md-3 col-sm-4 col-xs-6" v-for="photo in prize.photos" :key="photo.url">
                        <div style="position: relative;">
                            {{--image thumpnail--}}
                            <a :href="photo.url" data-toggle="lightbox" :data-gallery="'prize-gallery' + prize.id"
                               :data-title="prize.year + ' ' + prize.title">
                                <img :src="photo.urlThumb" style="width: 150px; height: auto"/>
                            </a>
                        </div>
                    </div>
                    {{--Photos of prize elements--}}
                    <template v-for="item in prize.elements">
                        <div class="gallery-item col-xl-2 col-md-3 col-sm-4 col-xs-6" v-for="photo in item.photos" :key="photo.url">
                            <div style="position: relative;">
                                {{--image thumpnail--}}
                                <a :href="photo.url" data-toggle="lightbox" :data-gallery="'prize-gallery' + prize.id"
                                   :data-title="prize.year + ' ' + prize.title"
                                   :data-footer="'<em>'+item.quantityString+':</em> <strong>'+item.title+'</strong> '+item.type">
                                    <img :src="photo.urlThumb" style="width: 150px; height: auto"/>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
                {{--Prize table--}}
                <table class="table table-sm">
                    <thead>
                        <th class="text-xs-right">quantity</th>
                        <th>prize</th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center">
                            <i title="owning" class="fa fa-inbox hidden-xl-up"></i>
                            <span class="hidden-lg-down">owning</span>
                        </th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center">
                            <i title="wanting" class="fa fa-download hidden-xl-up"></i>
                            <span class="hidden-lg-down">wanting</span>
                        </th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center">
                            <i title="trading" class="fa fa-upload hidden-xl-up"></i>
                            <span class="hidden-lg-down">trading</span>
                        </th>
                    </thead>
                    <tbody>
                        <tr v-for="item in prize.elements">
                            <td class="text-xs-right">
                                <em>@{{ item.quantityString }}:</em>
                            </td>
                            <td>
                                {{--doesn't have photo--}}
                                <span v-if="item.photos.length == 0">
                                    <strong :inner-html.prop="item.title | searchHighlight"></strong>
                                    <span :inner-html.prop="item.type | searchHighlight"></span>
                                </span>
                                {{--has photo--}}
                                <a v-if="item.photos.length > 0" :href="item.photos[0].url" data-toggle="lightbox"
                                   :data-gallery="'prize-gallery' + prize.id"
                                   :data-title="prize.year + ' ' + prize.title"
                                   :data-footer="'<em>'+item.quantityString+':</em> <strong>'+item.title+'</strong> '+item.type">
                                    <strong :inner-html.prop="item.title | searchHighlight"></strong>
                                    <span :inner-html.prop="item.type | searchHighlight"></span>
                                </a>
                            </td>
                            {{--owning--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].owning > 0 && !editMode">
                                    @{{ prizeCollection[item.id].owning }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                            @click="modifyCollection(item.id, 'owning', -1)"
                                            :disabled="prizeCollection[item.id].owning == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                           v-model="prizeCollection[item.id].owning" maxlength="2"
                                    /><button class="btn btn-xs btn-primary"
                                            @click="modifyCollection(item.id, 'owning', 1)"
                                            :disabled="prizeCollection[item.id].owning == 99">+</button>
                                </span>
                            </td>
                            {{--wanting--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].wanting > 0 && !editMode">
                                    @{{ prizeCollection[item.id].wanting }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'wanting', -1)"
                                            :disabled="prizeCollection[item.id].wanting == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                            v-model="prizeCollection[item.id].wanting" maxlength="2"
                                            /><button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'wanting', 1)"
                                            :disabled="prizeCollection[item.id].wanting == 99">+</button>
                                </span>
                            </td>
                            {{--trading--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].trading > 0 && !editMode">
                                    @{{ prizeCollection[item.id].trading }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'trading', -1)"
                                            :disabled="prizeCollection[item.id].trading == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                            v-model="prizeCollection[item.id].trading" maxlength="2"
                                            /><button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'trading', 1)"
                                            :disabled="prizeCollection[item.id].trading == 99">+</button>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{--Description--}}
                <div class="markdown-content">
                    {{--{!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize->description)) !!}--}}
                </div>
                <div class="text-xs-right" v-if="prize.ffg_url && prize.ffg_url.length > 0">
                    <a :href="prize.ffg_url">read more</a>
                </div>
            </div>
        </div>
    </div>
    <div class="text-xs-center" v-if="!foundKit">
        <em>no such prize kit found</em>
    </div>
</div>

    <script type="text/javascript">
        var prizeBrowser= new Vue({
            el: '#prize-browser',
            data: {
                prizes: [],
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
                    if (this.prizeCollection[id][field] + increment > -1 &&
                            this.prizeCollection[id][field] + increment < 101) {
                        this.prizeCollection[id][field] += increment;
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

