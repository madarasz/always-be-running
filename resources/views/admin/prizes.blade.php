{{--Prizes admin tab--}}
<div class="tab-pane" id="tab-prizes" role="tabpanel">
    <div class="row">
        {{--Prize kit list--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    Prize kits
                </h5>
                <div class="loader" id="prizes-loader">&nbsp;</div>
                <table class="table table-sm table-striped abr-table table-doublerow hover-row">
                    <thead>
                        <th>year</th>
                        <th>title</th>
                        <th class="text-xs-center">
                            <span class="hidden-sm-down">#items</span>
                            <span class="hidden-md-up">
                                <i class="fa fa-list-alt" title="tournaments"></i>
                            </span>
                        </th>
                        <th class="text-xs-center">
                            <span class="hidden-sm-down">#pictures</span>
                            <span class="hidden-md-up">
                                <i class="fa fa-camera" title="pictures"></i>
                            </span>
                        </th>
                        <th class="text-xs-center">
                            <span class="hidden-sm-down">#tournaments</span>
                            <span class="hidden-md-up">
                                <i class="fa fa-file" title="items"></i>
                            </span>
                        </th>
                        <th></th>
                    </thead>
                    <tbody>
                        <tr v-if="prizes.length == 0">
                            <td colspan="6" class="text-xs-center">
                                <em>you have no prizes created yet</em>
                            </td>
                        </tr>
                        <tr v-for="(prize, index) in prizes" :class="prize.id == selectedPrize.id ? 'row-selected': ''"
                                @click="selectPrize(index)">
                            <td>@{{ prize.year }}</td>
                            <td>@{{ prize.title }}</td>
                            <td class="text-xs-center">@{{ prize.elements.length }}</td>
                            <td class="text-xs-center">@{{ prize.pictureCount }}</td>
                            <td class="text-xs-center">@{{ prize.tournamentCount }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        {{--Prize kit details--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    Prize kit details<span v-if="selectedPrize != ''">: @{{ selectedPrize.year + ' ' + selectedPrize.title }}</span>
                </h5>
                <div class="text-xs-center" v-if="selectedPrize == ''">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select a prize kit to view its details
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
                <div v-if="selectedPrize != ''">
                    <em>
                        <strong>created by:</strong> @{{ selectedPrize.user.displayUsername }} - @{{ selectedPrize.created_at }}<br/>
                        <strong>last update:</strong> @{{ selectedPrize.updated_at }}<br/>
                    </em>
                    <strong>ordering number:</strong> @{{ selectedPrize.order }}<br/>
                    <strong>FFG article URL:</strong>
                    <a v-if="selectedPrize.ffg_url !=''" :href="selectedPrize.ffg_url" target="_blank">
                        @{{ selectedPrize.ffg_url }}
                    </a><br/>
                    <strong>description:</strong><br/>
                    <div v-html="compiledMarkdownPrizeDescription"></div>
                    {{--Prize kit images--}}
                    <strong>pictures of prize kit (@{{ selectedPrize.photos.length }})</strong>
                    <div class="row">
                        <div class="gallery-item col-xs-3" v-for="photo in selectedPrize.photos">
                            <div style="position: relative;">
                                {{--image thumpnail--}}
                                <a :href="photo.url" data-toggle="lightbox" data-gallery="prizekit-gallery"
                                   :data-title="selectedPrize.year + ' ' + selectedPrize.title">
                                    <img :src="photo.urlThumb"/>
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    {{-- Prize kit item images --}}
                    <strong>pictures of prize kit items (@{{ selectedPrize.pictureCount - selectedPrize.photos.length }})</strong>
                    <div class="row">
                        <div class="gallery-item col-xs-3" v-for="photo in kitPhotoList">
                            <div style="position: relative;">
                                {{--image thumpnail--}}
                                <a :href="photo.url" data-toggle="lightbox" data-gallery="prize-gallery"
                                   :data-title="selectedPrize.year + ' ' + selectedPrize.title"
                                   :data-footer="'<em>'+selectedItem.quantity +':</em> <strong>'+selectedItem.title+'</strong> '+selectedItem.type">
                                    <img :src="photo.urlThumb"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--Item list--}}
        <div class="col-xs-12 col-lg-8">
            <div class="bracket">
                <h5>
                    <i class="fa fa-file" aria-hidden="true"></i>
                    Items
                </h5>
                <table class="table table-sm table-striped abr-table table-doublerow hover-row">
                    <thead>
                        <th class="text-xs-right">quantity</th>
                        <th>title</th>
                        <th>type</th>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <tr v-for="(element, index) in selectedPrize.elements" :class="element.id == selectedItem.id ? 'row-selected': ''"
                                @click="selectItem(index)">
                            <td class="text-xs-right">
                                @{{ element.quantity }}
                                <em v-if="element.quantity == null">participation</em>
                            </td>
                            <td>@{{ element.title }}</td>
                            <td>@{{ element.type }}</td>
                            <td></td>
                            <td>
                                <i class="fa fa-camera" title="picture available" v-if="element.photos.length > 0"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-xs-center" v-if="selectedPrize == ''">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select a prize kit to view its items
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
            </div>
        </div>
        {{--Item details--}}
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">
                <h5>
                    <i class="fa fa-file" aria-hidden="true"></i>
                    Item details
                </h5>
                <div class="text-xs-center" v-if="selectedItem == ''">
                    <em>
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                        select an item to view its details
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </em>
                </div>
                <div v-if="selectedItem != ''">
                    <em>
                        <strong>created by:</strong> @{{ selectedItem.user.displayUsername }} - @{{ selectedItem.created_at }}<br/>
                        <strong>last update:</strong> @{{ selectedItem.updated_at }}<br/>
                    </em>
                    <strong>quantity:</strong> @{{ selectedItem.quantity }}
                    <em v-if="selectedItem.quantity == null">participation</em><br/>
                    <strong>title:</strong> @{{ selectedItem.title }}<br/>
                    <strong>type:</strong> @{{ selectedItem.type }}<br/>
                    <strong>pictures of item (@{{ selectedItem.photos.length }})</strong>
                    <div class="row">
                        <div class="gallery-item col-xs-12" v-for="photo in selectedItem.photos">
                            <div style="position: relative;">
                                {{--image thumpnail--}}
                                <a :href="photo.url" data-toggle="lightbox" data-gallery="prize-gallery"
                                    :data-title="selectedPrize.year + ' ' + selectedPrize.title"
                                    :data-footer="'<em>'+selectedItem.quantity +':</em> <strong>'+selectedItem.title+'</strong> '+selectedItem.type">
                                <img :src="photo.urlThumb"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    var adminPrizes= new Vue({
        el: '#tab-prizes',
        data: {
            prizes: [],
            kitPhotoList: [],
            modalTitle: '',
            editMode: false,
            selectedPrize: '',
            selectedItem: '',
            modalButton: '',
            confirmCallback: function () {
            },
            confirmText: ''
        },
        components: {},
        computed: {
            compiledMarkdownPrizeDescription: function () {
                if (this.selectedPrize == '' || this.selectedPrize.description == null) {
                    return '';
                }
                return marked(this.selectedPrize.description, {sanitize: true})
            }
        },
        mounted: function () {
            this.loadPrizes();
        },
        methods: {
            // load all my groups
            loadPrizes: function () {
                axios.get('/api/prizes').then(function (response) {
                    adminPrizes.prizes = response.data;
                    $('#prizes-loader').addClass('hidden-xs-up');
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                });
            },
            selectPrize: function(index) {
                this.selectedPrize = this.prizes[index];
                // gather photos of items
                this.kitPhotoList = [];
                for (var i = 0; i < this.selectedPrize.elements.length; i++) {
                    for (var u = 0; u < this.selectedPrize.elements[i].photos.length; u++) {
                        this.kitPhotoList.push(this.selectedPrize.elements[i].photos[u]);
                    }
                }
            },
            selectItem: function(index) {
                this.selectedItem = this.selectedPrize.elements[index];
            }
        }
    });
</script>