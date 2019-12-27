{{--Modal for new/edit art item--}}
<div class="modal fade" id="modal-art-upload" tabindex="-1" role="dialog" aria-hidden="true" v-if="editMode">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal()"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Add photo</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="attachArtPhoto()">

                    {{-- Photo --}}
                    <div class="form-group row">
                        <label for="photo" class="col-sm-3 col-form-label">Photo:</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo" class="form-control" id="photo-add-file" @change="uploadArtPhoto('photo-add-file')"/>
                            {{-- photo preview --}}
                            <img id="photo-add-preview" v-if="art_item.photoThumbUrl != null" :src="art_item.photoThumbUrl"
                                class="shrink100x100"/>
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <button type="submit" class="btn btn-success" v-if="!imageUploading">Add photo</button>
                        <em v-if="imageUploading">image uploading...</em>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>