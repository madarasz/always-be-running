<div class="tab-pane" id="tab-art" role="tabpanel">
    @include('admin.modals.artist')
    @include('profile.modal-art-item')
    @include('profile.modal-art-upload')
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback" id="-art"></confirm-modal>
    <div class="row">
        {{--Artist list--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-paint-brush" aria-hidden="true"></i>
                    Artists (@{{ artists.length }})
                    <div class="pull-right">
                        {{--create button--}}
                        <a class="btn btn-success white-text" id="button-add-artist"
                           data-toggle="modal" data-target="#modal-artist" @click="modalForAddArtist">
                            Add Artist
                        </a>
                    </div>
                </h5>
                <div class="loader" id="artists-loader">&nbsp;</div>
                <table class="table table-sm table-striped abr-table hover-row">
                    <thead>
                        <th></th>
                        <th>artist name</th>
                        <th class="text-xs-center">#items</th>
                        <th></th>
                    </thead>
                    <tbody>
                        <tr v-if="artists.length == 0">
                            <td colspan="4" class="text-xs-center">
                                <em>you have no artists created yet</em>
                            </td>
                        </tr>
                        <tr v-for="(artist, index) in artists" :class="artist.id == selectedArtist.id ? 'row-selected': ''"
                                @click="selectArtistByIndex(index)">
                            <td></td>
                            <td>
                                <a v-if="artist.user" :href="'/profile/'+artist.user.id+'#tab-my-art'" :class="artist.user.linkClass">
                                    @{{ artist.displayArtistName }}
                                </a>
                                <span v-if="!artist.user">
                                    @{{ artist.displayArtistName }}
                                </span>
                            </td>
                            <td class="text-xs-center"><span v-if="artist.items">@{{ artist.items.length }}</span></td>
                            <td class="text-xs-right">
                                {{--edit button--}}
                                <a class="btn btn-primary btn-xs white-text" @click="modalForEditArtist(artist)" v-if="artist.user == null">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                {{--delete button--}}
                                <form method="post" action="" style="display: inline" v-if="artist.items.length == 0 && artist.user == null">
                                    <input name="_method" type="hidden" value="DELETE"/>
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <input name="delete_id" type="hidden" :value="artist.id">
                                    <confirm-button button-text="delete" button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" id="-art"
                                        @click="confirmCallback = function() { deleteArtist(artist.id) }; confirmText = 'Remove Artist?'" />
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        {{--Artist details--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                    Artist details
                    <div class="pull-right" v-if="selectedArtist.id != 0">
                        {{--edit button--}}
                        <a class="btn btn-primary white-text" @click.stop="modalForEditArtist(selectedArtist)">
                            Edit
                        </a>
                    </div>
                </h5>
                <div class="text-xs-center" v-if="selectedArtist.id == 0">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select an artist to view details
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
                <div v-if="selectedArtist.id != 0">
                    <strong>name:</strong> @{{ selectedArtist.displayArtistName }}<br/>
                    <strong>homepage:</strong> <a :href="selectedArtist.url">@{{ selectedArtist.url }}</a><br/>
                    <div class="markdown-content" v-html="compiledMarkdownArtistDescription"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        {{-- Art list --}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    Art list
                    <div class="pull-right" v-if="selectedArtist.id != 0">
                        {{--create button--}}
                        <a class="btn btn-primary btn-sm white-text" @click.stop="modalForAddItem">
                            Add
                        </a>
                    </div>
                </h5>
                <div class="text-xs-center" v-if="selectedArtist.id == 0">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select an artist to view details
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
                <table class="table table-sm table-striped abr-table" v-if="selectedArtist.id > 0">
                    <thead>
                        <th class="text-xs-center">
                            <i class="fa fa-camera" aria-hidden="true"></i>
                        </th>
                        <th style="width: 99%">item</th>
                        <th></th>
                    </thead>
                    <tbody>
                        {{-- no art item message --}}
                        <tr v-if="selectedArtist.items && selectedArtist.items.length == 0">
                            <td colspan="3" class="text-xs-center">
                                <em>no art items yet</em>
                            </td>
                        </tr>
                        {{-- list of art items --}}
                        <tr v-for="(item, index) in selectedArtist.items">
                            <td nowrap>
                                <div class="flex-row">
                                    <div class="gallery-item" style="margin: 0" v-for="photo in item.photos">
                                        <div style="position: relative;">
                                            {{--image thumpnail--}}
                                            <a :href="photo.url" data-toggle="lightbox" data-gallery="prizekit-gallery"
                                                :data-title="item.title">
                                                <img :src="photo.urlThumb" class="shrink100x100"/>
                                            </a>
                                            {{--delete button--}}
                                            <div class="abs-top-left">
                                                <form method="post" action="" style="display: inline">
                                                    <input name="_method" type="hidden" value="DELETE"/>
                                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                                    <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" id="-art"
                                                        @click="confirmCallback = function() { deleteArtPhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                                </form>
                                            </div>
                                        </div>
                                    </div>     
                                    <div v-if="item.photos.length < maxArtPhotos" class="size100x100 flex-center">
                                        <a class="btn btn-primary btn-xs white-text" @click="modallForAddPhoto(index)">
                                            <i class="fa fa-plus"></i> <i class="fa fa-camera"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            {{-- title and type --}}
                            <td>
                                @{{ item.title }}<br/>
                                <span class="small-text">@{{ item.type}}</span>
                            </td>
                            {{-- edit and delete buttons --}}
                            <td>
                                <a class="btn btn-primary btn-xs white-text" @click="modalForEditItem(index)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" button-text="delete" id="-art"
                                            @click="confirmCallback = function() { deleteArtItem(item.id) }; confirmText = 'Are you sure you want to delete art item?'" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var adminArt= new Vue({
        el: '#tab-art',
        data: {
            artists: [],
            selectedArtist: { id: 0 },
            art_item: { id: 0 },
            modalTitle: '',
            modalButton: '',
            art_types: {!! json_encode($art_types) !!},
            editMode: false,
            editItemMode: false,
            imageUploading: false,
            maxArtPhotos: 2,
            confirmCallback: function () {
            },
            confirmText: ''
        },
        mounted: function () {
            this.loadArtists();
        },
        computed: {
            compiledMarkdownArtistDescription: function () {
                if (this.selectedArtist.id == 0 || this.selectedArtist.description == null) {
                    return '';
                }
                return marked(this.selectedArtist.description, {sanitize: true, gfm: true, breaks: true})
            }
        },
        methods: {
            loadArtists: function(idToSelect = -1) {
                axios.get('/api/artists').then(function (response) {
                    $('#artists-loader').addClass('hidden-xs-up');
                    // add artists who are not unregistered or without user
                    adminArt.artists = response.data.filter((x) => (x.user == null || x.user.artist_id != null));
                    // pre-select an artist
                    if (idToSelect > -1 && idToSelect !== undefined) {
                        adminArt.selectedArtist = response.data.find((x) => x.id == idToSelect);
                        adminArt.selectedArtist.name = adminArt.selectedArtist.displayArtistName;
                    }
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the artists.', '', {timeOut: 2000});
                });
            },
            selectArtistByIndex: function(index) {
                this.selectedArtist = JSON.parse(JSON.stringify(this.artists[index])); // copy object
                this.selectedArtist.name = this.selectedArtist.displayArtistName;
                this.selectedArtist.index = index;
            },
            modalForAddArtist: function() {
                this.selectedArtist = {};
                this.editMode = false;
                this.modalTitle = 'Add Artist';
                this.modalButton = 'Add';
            },
            modalForEditArtist: function(artist) {
                this.editMode = true;
                this.modalTitle = 'Edit Artist';
                this.modalButton = 'Save';
                $("#modal-artist").modal('show');
            },
            cancelModal: function() {
                // restore selectedArtist value
                if (this.selectedArtist && this.selectedArtist.index) {
                    this.selectedArtist = JSON.parse(JSON.stringify(this.artists[this.selectedArtist.index]));
                }
            },
            addArtist: function() {
                axios.post('/api/artists', this.selectedArtist)
                    .then(function(response) {
                        adminArt.loadArtists(response.data.id);
                        $("#modal-artist").modal('hide');
                        toastr.info('Artist successfully.', '', {timeOut: 2000});
                    }, function(response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                    }
                );
            },
            updateArtist: function() {
                axios.put('/api/artists/' + this.selectedArtist.id, this.selectedArtist)
                        .then(function(response) {
                            $("#modal-artist").modal('hide');
                            toastr.info('Artist updated successfully.', '', {timeOut: 2000});
                            adminArt.loadArtists(response.data.id);
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            deleteArtist: function(artistId) {
                axios.delete('/api/artists/' + artistId).then(function (response) {
                    adminArt.loadArtists();
                    adminArt.selectedArtist = { id: 0 };
                    toastr.info('Artist removed.', '', {timeOut: 2000});
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            modalForAddItem: function() {
                this.editMode = true; // for the modal
                this.art_item = { 
                    proper: true, 
                    official: false, 
                    artist_id: this.selectedArtist.id,
                    prize_id: null,
                    quantity: null,
                    photoId: null,
                    photoThumbUrl: null
                };
                this.modalTitle = 'Create Art item';
                this.modalButton = 'Create';
                this.editItemMode = false;
                $("#modal-art-item").modal('show');
                $('[data-toggle="popover"]').popover();
                if (document.getElementById('art-to-upload')) {
                    document.getElementById('art-to-upload').value = "";
                }
            },
            modalForEditItem: function(artIndex) {
                this.editMode = true; // for the modal
                this.art_item = JSON.parse(JSON.stringify(this.selectedArtist.items[artIndex])); // copy
                this.art_item.typeHelper = this.art_item.type;
                this.modalTitle = 'Edit Art item';
                this.modalButton = 'Save';
                this.editItemMode = true;
                $("#modal-art-item").modal('show');
                $('[data-toggle="popover"]').popover();
            },
            modallForAddPhoto: function(itemIndex) {
                this.editMode = true; // for the modal
                this.art_item = this.selectedArtist.items[itemIndex];
                this.art_item.photoId = null;
                this.art_item.photoThumbUrl = null;
                $("#modal-art-upload").modal('show');
                document.getElementById('photo-add-file').value = "";
            },
            closeModal: function() {
                this.hidePopovers();
                if (this.art_item.photoId) {
                    this.deleteArtPhoto(this.art_item.photoId);
                }
            },
            hidePopovers: function() {
                $('.popover').popover('hide');
            },
            createArtItem: function() {
                axios.post('/api/prize-items', this.art_item)
                    .then(function(response) {
                        adminArt.art_item.id = response.data.id;
                        adminArt.loadArtists(adminArt.selectedArtist.id);
                        adminArt.hidePopovers();
                        $("#modal-art-item").modal('hide');
                        toastr.info('Art item is created successfully.', '', {timeOut: 2000});
                        // attach photo
                        if (adminArt.art_item.photoId != null) {
                            adminArt.attachArtPhoto();
                        }
                    }, function(response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            updateArtItem: function() {
                axios.put('/api/prize-items/' + this.art_item.id, this.art_item)
                    .then(function(response) {
                        $("#modal-art-item").modal('hide');
                        adminArt.hidePopovers();
                        toastr.info('Art item is updated successfully.', '', {timeOut: 2000});
                        adminArt.loadArtists(adminArt.selectedArtist.id);
                    }, function(response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            deleteArtItem: function(artId) {
                axios.delete('/api/prize-items/' + artId).then(function (response) {
                    adminArt.loadArtists(adminArt.selectedArtist.id);
                    toastr.info('Art item is deleted.', '', {timeOut: 2000});
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            uploadArtPhoto: function(elementId = 'art-to-upload') {
                if (document.getElementById(elementId).files[0]) {
                    // prepare data
                    this.imageUploading = true;
                    var data = new FormData();
                    data.append('photo', document.getElementById(elementId).files[0]);
                    data.append('prize_id', null);
                    data.append('prize_element_id', null); // set later
                    data.append('user_id', {{ $user->id }});
                    data.append('title', '');

                    // post data
                    axios.post('/api/photos', data)
                        .then(function(response) {
                            adminArt.art_item.photoId = response.data.id;
                            adminArt.art_item.photoThumbUrl = response.data.url;
                            adminArt.imageUploading = false;
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                            adminArt.imageUploading = false;
                    });
                } else {
                    // in case of empty field
                    this.deleteArtPhoto(this.art_item.photoId);
                    this.art_item.photoId = null;
                    this.art_item.photoThumbUrl = null;
                }
            },
            attachArtPhoto: function() {
                axios.put('/api/photos/' + this.art_item.photoId, { "prize_element_id": this.art_item.id })
                    .then(function(response) {
                            // photo attached
                            adminArt.loadArtists(adminArt.selectedArtist.id);
                            $("#modal-art-upload").modal('hide');
                        }, function(response) {
                            // error handling
                            toastr.error('Could not attach photo to art item.', '', {timeOut: 2000});
                        }
                );
            },
            deleteArtPhoto: function(photoId) {
                axios.delete('/api/photos/' + photoId).then(function (response) {
                        adminArt.loadArtists(adminArt.selectedArtist.id);
                        toastr.info('Photo deleted.', '', {timeOut: 2000});
                    }, function(response) {
                        // error handling
                        toastr.error('Photo was not deleted.', '', {timeOut: 2000});
                    });
            }
        }
    })
</script>