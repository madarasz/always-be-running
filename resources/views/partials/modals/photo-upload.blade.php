{{--Modal for photo upload--}}
<div class="modal fade" id="modal-photo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Upload photo</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="uploadPhoto">

                    {{--Hidden IDs--}}
                    <input type="hidden" name="tournament_id" v-model="photo.tournament_id" />
                    <input type="hidden" name="prize_id" v-model="photo.prize_id" />
                    <input type="hidden" name="prize_element_id" v-model="photo.prize_element_id" />

                    {{--File--}}
                    <div class="form-group row">
                        <label for="photo" class="col-sm-3 col-form-label">File:</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo" class="form-control" required id="photo-to-upload"/>
                        </div>
                    </div>

                    {{--Title--}}
                    <div class="form-group row" v-if="photo.showTitleField">
                        <label for="title" class="col-sm-3 col-form-label">Title:</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" v-model="photo.title" />
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <button type="submit" class="btn btn-success">Upload</button>
                        <div class="small-text">max 8MB png or jpg file</div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>