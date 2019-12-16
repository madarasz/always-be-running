<div class="tab-pane" id="tab-art" role="tabpanel">
    @include('admin.modals.artist')
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback" id="-art"></confirm-modal>
    <div class="row">
        {{--Prize kit list--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-paint-brush" aria-hidden="true"></i>
                    Artists of unofficial art (@{{ artists.length }})
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
                        <th>#items</th>
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
                            <td>@{{ artist.name }}</td>
                            <td></td>
                            <td class="text-xs-right">
                                {{--edit button--}}
                                <a class="btn btn-primary btn-xs white-text" @click.stop="modalForEditArtist(artist)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                {{--delete button--}}
                                <form method="post" action="" style="display: inline" v-if="artist.tournamentCount != 0">
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
</div>
<script type="text/javascript">
    var adminArt= new Vue({
        el: '#tab-art',
        data: {
            artists: [],
            selectedArtist: { id: 0 },
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
        methods: {
            loadArtists: function() {
                axios.get('/api/artists').then(function (response) {
                    $('#artists-loader').addClass('hidden-xs-up');
                    adminArt.artists = response.data;
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the artists.', '', {timeOut: 2000});
                });
            },
            selectArtistByIndex: function(index) {
                this.selectedArtist = this.artist[index];
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