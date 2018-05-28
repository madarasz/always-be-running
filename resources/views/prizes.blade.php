@extends('layout.general')

@section('content')
    <h4 class="page-header">
        Official prize kits
    </h4>
    <div id="prize-browser">
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
        </div>
        <div class="loader" id="prizes-loader" v-if="prizes.length == 0">&nbsp;</div>

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
    </div>

    <script type="text/javascript">
        var prizeBrowser= new Vue({
            el: '#prize-browser',
            data: {
                prizes: [],
                prizeVisibility: [],
                selectedPrizeId: 0,
                searchText: ''
            },
            components: {},
            computed: {},
            mounted: function () {
                this.loadPrizes();
            },
            filters: {
              searchHighlight: function(value) {
                  if (prizeBrowser.searchText.length < 3) {
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
                    }, function (response) {
                        // error handling
                        toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                    });
                },
                updateFilter: function() {
                    var defaultValue = this.selectedPrizeId == 0 && this.searchText.length <= 2;
                    this.prizeVisibility = [];
                    for (var i = 0; i < this.prizes.length; i++) {
                        this.prizeVisibility.push(defaultValue);
                    }
                    // filter for one prize kit
                    if (this.selectedPrizeId > 0) {
                        for (var i = 0; i < this.prizes.length; i++) {
                            if (this.prizes[i].id == this.selectedPrizeId) {
                                this.prizeVisibility[i] = true;
                                return true;
                            }
                        }
                    } else if (this.searchText.length > 2) {
                        for (var i = 0; i < this.prizes.length; i++) {
                            // prize kit year / title matches
                            if ((this.prizes[i].year+this.prizes[i].title.toUpperCase()).indexOf(this.searchText.toUpperCase()) > -1) {
                                this.prizeVisibility[i] = true;
                            } else {
                                // prize item title / type matches
                                for (var u = 0; u < this.prizes[i].elements.length; u++) {
                                    var prizeItem = this.prizes[i].elements[u];
                                    if (prizeItem.title.toUpperCase().indexOf(this.searchText.toUpperCase()) > -1 ||
                                            prizeItem.type.toUpperCase().indexOf(this.searchText.toUpperCase()) > -1) {
                                        this.prizeVisibility[i] = true;
                                    }
                                }
                            }
                        }
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

