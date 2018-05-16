{{--Prizes admin tab--}}
<div class="tab-pane" id="tab-prizes" role="tabpanel">
    @include('admin.modals.prize')
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback"></confirm-modal>
    <div class="row">
        {{--Prize kit list--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    Prize kits
                    <div class="pull-right">
                        <a class="btn btn-success white-text" id="button-create-group"
                           data-toggle="modal" data-target="#modal-prize" @click="modalForCreatePrize">
                        Create Prize Kit
                        </a>
                    </div>
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
                            <td class="text-xs-right">
                                {{--edit button--}}
                                <a class="btn btn-primary btn-xs white-text" @click.stop="modalForEditPrize(prize)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                {{--delete button--}}
                                <form method="post" action="" style="display: inline" v-if="prize.tournamentCount == 0">
                                    <input name="_method" type="hidden" value="DELETE"/>
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <input name="delete_id" type="hidden" :value="prize.id">
                                    <confirm-button button-text="delete" button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                        @click="confirmCallback = function() { deletePrize(prize.id) }; confirmText = 'Delete prize kit?'" />
                                </form>
                            </td>
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
                    <strong>type:</strong> @{{ selectedPrize.tournament_type.type_name }}<br/>
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
            prize: {},
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
            loadPrizes: function (selectId = 0) {
                axios.get('/api/prizes').then(function (response) {
                    adminPrizes.selectedPrize = '';
                    adminPrizes.prizes = response.data;
                    $('#prizes-loader').addClass('hidden-xs-up');
                    // select newly created ID, if any
                    if (selectId > 0) {
                        for (var i = 0; i < adminPrizes.prizes.length; i++) {
                            if (adminPrizes.prizes[i].id == selectId) {
                                adminPrizes.selectPrize(i);
                                break;
                            }
                        }
                    }
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                });
            },
            selectPrize: function(index) {
                this.selectedPrize = this.prizes[index];
                this.selectedItem = '';
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
            },
            modalForCreatePrize: function() {
                this.prize = {};
                this.modalTitle = 'Create Prize Kit';
                this.modalButton = 'Create';
                this.editMode = false;
            },
            modalForEditPrize: function(prize) {
                adminPrizes.prize = prize;
                this.modalTitle = 'Edit Prize Kit';
                this.modalButton = 'Save';
                this.editMode = true;
                $("#modal-prize").modal('show');
            },
            createPrize: function() {
                axios.post('/api/prizes', this.prize)
                        .then(function(response) {
                            adminPrizes.selectedItem = '';
                            adminPrizes.loadPrizes(response.data.id);
                            $("#modal-prize").modal('hide');
                            toastr.info('Prize kit created successfully.', '', {timeOut: 2000});
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            deletePrize: function(prizeId) {
                axios.delete('/api/prizes/' + prizeId).then(function (response) {
                    adminPrizes.loadPrizes();
                    toastr.info('Prize kit deleted.', '', {timeOut: 2000});
                    adminPrizes.selectedPrize = '';
                    adminPrizes.selectedItem = '';
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            updatePrize: function() {
                axios.put('/api/prizes/' + this.prize.id, this.prize)
                        .then(function(response) {
                            $("#modal-prize").modal('hide');
                            toastr.info('Prize kit updated successfully.', '', {timeOut: 2000});
                            adminPrizes.loadPrizes(response.data.id);
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            }
        }
    });
</script>