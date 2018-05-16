{{--Prizes admin tab--}}
<div class="tab-pane" id="tab-prizes" role="tabpanel">
    @include('admin.modals.prize')
    @include('admin.modals.prize-item')
    @include('partials.modals.photo-upload')
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
                        Create Prize kit
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
                                @click="selectPrizeByIndex(index)">
                            <td>@{{ prize.year }}</td>
                            <td>@{{ prize.title }}</td>
                            <td class="text-xs-center">@{{ prize.elements.length }}</td>
                            <td class="text-xs-center">@{{ prize.pictureCount }}</td>
                            <td class="text-xs-center">@{{ prize.tournamentCount }}</td>
                            <td class="text-xs-left">
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
                    {{--Add photo--}}
                    <div class="pull-right" v-if="selectedPrize != ''">
                        <a class="btn btn-primary white-text" id="button-create-group"
                           data-toggle="modal" data-target="#modal-photo" @click="modalForPrizePhoto">
                        Upload photo
                        </a>
                        <div class="small-text text-xs-center">for prize kit</div>
                    </div>

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
                    {{--Details--}}
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
                                {{--delete button--}}
                                <div class="abs-top-left">
                                    <form method="post" action="" style="display: inline">
                                        <input name="_method" type="hidden" value="DELETE"/>
                                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                        <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                            @click="confirmCallback = function() { deletePhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                    </form>
                                </div>
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
                                {{--delete button--}}
                                <div class="abs-top-left">
                                    <form method="post" action="" style="display: inline">
                                        <input name="_method" type="hidden" value="DELETE"/>
                                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                        <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                        @click="confirmCallback = function() { deletePhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                    </form>
                                </div>
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
                    <div class="pull-right">
                        <a class="btn btn-success white-text" id="button-create-group" v-if="selectedPrize != ''"
                           data-toggle="modal" data-target="#modal-prize-item" @click="modalForCreateItem">
                        Create Prize item
                        </a>
                    </div>
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
                            <td>
                                <i class="fa fa-camera" title="picture available" v-if="element.photos.length > 0"></i>
                            </td>
                            <td class="text-xs-right">
                                {{--edit button--}}
                                <a class="btn btn-primary btn-xs white-text" @click.stop="modalForEditItem(element)">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                {{--delete button--}}
                                <form method="post" action="" style="display: inline">
                                    <input name="_method" type="hidden" value="DELETE"/>
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <input name="delete_id" type="hidden" :value="element.id">
                                    <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                    @click="confirmCallback = function() { deleteItem(element.id) }; confirmText = 'Delete prize item?'" />
                                </form>
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
                                {{--delete button--}}
                                <div class="abs-top-left">
                                    <form method="post" action="" style="display: inline">
                                        <input name="_method" type="hidden" value="DELETE"/>
                                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                        <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                        @click="confirmCallback = function() { deletePhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Add photo--}}
                    <div class="text-xs-center p-t-1">
                        <a class="btn btn-primary white-text" id="button-create-group"
                           data-toggle="modal" data-target="#modal-photo" @click="modalForItemPhoto">
                            Upload photo
                        </a>
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
            itemTypes: [],
            prize: {},
            item: {},
            photo: {
                showTitleField: false,
                tournament_id: null
            },
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
            loadPrizes: function (selectPrizeId = 0, selectItemId = 0) {
                axios.get('/api/prizes').then(function (response) {
                    adminPrizes.selectedPrize = '';
                    adminPrizes.prizes = response.data;
                    $('#prizes-loader').addClass('hidden-xs-up');
                    adminPrizes.gatherItemTypes();

                    // select newly created prize kit, prize item, if any
                    if (selectPrizeId > 0) {
                        adminPrizes.selectPrizeById(selectPrizeId);
                    }
                    if (selectItemId > 0) {
                        adminPrizes.selectItemById(selectItemId);
                    }
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                });
            },
            selectPrizeByIndex: function(index) {
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
                this.modalTitle = 'Create Prize kit';
                this.modalButton = 'Create';
                this.editMode = false;
            },
            modalForEditPrize: function(prize) {
                this.prize = prize;
                this.modalTitle = 'Edit Prize kit';
                this.modalButton = 'Update';
                this.editMode = true;
                $("#modal-prize").modal('show');
            },
            modalForCreateItem: function() {
                this.item = { prize_id: this.selectedPrize.id };
                this.modalTitle = 'Create Prize kit';
                this.modalButton = 'Create';
                this.editMode = false;
            },
            modalForEditItem: function(item) {
                this.item = item;
                this.item.typeHelper = this.item.type;
                this.modalTitle = 'Edit Prize item';
                this.modalButton = 'Update';
                this.editMode = true;
                $("#modal-prize-item").modal('show');
            },
            modalForPrizePhoto: function() {
                this.photo.prize_id = this.selectedPrize.id;
                this.photo.prize_element_id = null;
            },
            modalForItemPhoto: function() {
                this.photo.prize_id = null;
                this.photo.prize_element_id = this.selectedItem.id;
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
            },
            createItem: function() {
                axios.post('/api/prize-items', this.item)
                        .then(function(response) {
                            adminPrizes.loadPrizes(response.data.prize_id, response.data.id);
                            $("#modal-prize-item").modal('hide');
                            toastr.info('Prize item created successfully.', '', {timeOut: 2000});
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            updateItem: function() {
                axios.put('/api/prize-items/' + this.item.id, this.item)
                        .then(function(response) {
                            $("#modal-prize-item").modal('hide');
                            toastr.info('Prize item updated successfully.', '', {timeOut: 2000});
                            adminPrizes.loadPrizes(response.data.prize_id, response.data.id);
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            deleteItem: function(itemId) {
                axios.delete('/api/prize-items/' + itemId).then(function (response) {
                    adminPrizes.loadPrizes(adminPrizes.selectedPrize.id);
                    adminPrizes.selectedItem = '';
                    toastr.info('Prize item deleted.', '', {timeOut: 2000});
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            // gathers all previously existing prize item types
            gatherItemTypes: function() {
                this.itemTypes = [];
                for (var i = 0; i < this.prizes.length; i++) {
                    for (var u = 0; u < this.prizes[i].elements.length; u++) {
                        if (this.itemTypes.indexOf(this.prizes[i].elements[u].type) == -1) {
                            this.itemTypes.push(this.prizes[i].elements[u].type);
                        }
                    }
                }
            },
            selectPrizeById: function(prizeId) {
                for (var i = 0; i < this.prizes.length; i++) {
                    if (this.prizes[i].id == prizeId) {
                        this.selectPrizeByIndex(i);
                        break;
                    }
                }
            },
            // selects item by id
            selectItemById: function(itemId) {
                for (var i = 0; i < this.selectedPrize.elements.length; i++) {
                    if (this.selectedPrize.elements[i].id == itemId) {
                        this.selectedItem = this.selectedPrize.elements[i];
                        break;
                    }
                }
            },
            uploadPhoto: function() {
                // prepare data
                var data = new FormData();
                data.append('photo', document.getElementById('photo-to-upload').files[0]);
                data.append('prize_id', this.photo.prize_id);
                data.append('prize_element_id', this.photo.prize_element_id);

                // post data
                axios.post('/api/photos', data)
                        .then(function(response) {
                            var prizeItemId = response.data.prize_element_id == 'null' ? 0 : response.data.prize_element_id;
                            adminPrizes.loadPrizes(adminPrizes.selectedPrize.id, prizeItemId);
                            $("#modal-photo").modal('hide');
                            toastr.info('Photo uploaded successfully.', '', {timeOut: 2000});
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            deletePhoto: function(photoId) {
                axios.delete('/api/photos/' + photoId).then(function (response) {
                    var itemId = adminPrizes.selectedItem == '' ? 0 : adminPrizes.selectedItem.id;
                    adminPrizes.loadPrizes(adminPrizes.selectedPrize.id, itemId);
                    toastr.info('Photo deleted.', '', {timeOut: 2000});
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            }
        }
    });
</script>