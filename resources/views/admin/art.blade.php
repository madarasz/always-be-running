<div class="tab-pane" id="tab-art" role="tabpanel">
    @include('admin.modals.artist')
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
                        {{-- <a class="btn btn-success white-text" id="button-add-artist"
                           data-toggle="modal" data-target="#modal-artist" @click="modalForAddArtist">
                            Add Artist
                        </a> --}}
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
                                {{-- <a class="btn btn-primary btn-xs white-text" @click.stop="modalForEditArtist(artist)">
                                    <i class="fa fa-pencil"></i> edit
                                </a> --}}
                                {{--delete button--}}
                                {{-- <form method="post" action="" style="display: inline" v-if="artist.tournamentCount != 0">
                                    <input name="_method" type="hidden" value="DELETE"/>
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <input name="delete_id" type="hidden" :value="artist.id">
                                    <confirm-button button-text="delete" button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" id="-art"
                                        @click="confirmCallback = function() { deleteArtist(artist.id) }; confirmText = 'Remove Artist?'" />
                                </form> --}}
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
                        {{-- <a class="btn btn-primary white-text" @click.stop="modalForEditArtist(selectedArtist)">
                            Edit
                        </a> --}}
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
                    <strong>name:</strong> @{{ selectedArtist.name }}<br/>
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
                    {{-- <div class="pull-right" v-if="selectedArtist.id != 0"> --}}
                        {{--create button--}}
                        {{-- <a class="btn btn-primary btn-sm white-text" @click.stop="modalForAddItem">
                            Add
                        </a> --}}
                    {{-- </div> --}}
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
                                            {{-- <div class="abs-top-left" v-if="editMode">
                                                <form method="post" action="" style="display: inline">
                                                    <input name="_method" type="hidden" value="DELETE"/>
                                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                                    <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                                        @click="confirmCallback = function() { deleteArtPhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                                </form>
                                            </div> --}}
                                        </div>
                                    </div>     
                                    {{-- <div v-if="editMode && item.photos.length < maxArtPhotos" class="size100x100 flex-center">
                                        <a class="btn btn-primary btn-xs white-text" v-if="editMode" @click="modallForAddPhoto(index)">
                                            <i class="fa fa-plus"></i> <i class="fa fa-camera"></i>
                                        </a>
                                    </div> --}}
                                </div>
                            </td>
                            {{-- title and type --}}
                            <td>
                                @{{ item.title }}<br/>
                                <span class="small-text">@{{ item.type}}</span>
                            </td>
                            {{-- edit and delete buttons --}}
                            <td>
                                {{-- <a class="btn btn-primary btn-xs white-text" v-if="editMode" @click="modalForEditArtItem(index)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" button-text="delete" v-if="editMode"
                                            @click="confirmCallback = function() { deleteArtItem(item.id) }; confirmText = 'Are you sure you want to delete art item?'" /> --}}
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
            selectedArt: { id: 0},
            modalTitle: '',
            modalButton: '',
            editMode: false,
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
            loadArtists: function() {
                axios.get('/api/artists').then(function (response) {
                    $('#artists-loader').addClass('hidden-xs-up');
                    // add artists who are not unregistered
                    adminArt.artist = [];
                    for (var i = 0; i < response.data.length; i++) {
                        if (response.data[i].user.artist_id != null) {
                            adminArt.artists.push(response.data[i]);
                        }
                    }
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the artists.', '', {timeOut: 2000});
                });
            },
            selectArtistByIndex: function(index) {
                this.selectedArtist = this.artists[index];
            },
            modalForAddArtist: function() {
                this.editMode = false;
                this.modalTitle = 'Add Artist';
                this.modalButton = 'Add';
            },
            modalForEditArtist: function(artist) {
                this.selectedArtist = artist;
                this.editMode = true;
                this.modalTitle = 'Edit Artist';
                this.modalButton = 'Save';
                $("#modal-artist").modal('show');
            },
            modalForAddItem: function() {

            },
            modalForEditItem: function() {

            },
            addArtist: function() {
                axios.post('/api/artists', this.selectedArtist)
                    .then(function(response) {
                        adminArt.loadArtists();
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
                            adminArt.loadArtists();
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
            }
        }
    })
</script>