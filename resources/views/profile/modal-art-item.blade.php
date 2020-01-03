{{--Modal for new/edit art item--}}
<div class="modal fade" id="modal-art-item" tabindex="-1" role="dialog" aria-hidden="true" v-if="editMode">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal()"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">@{{ modalTitle }}</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="(editItemMode ? updateArtItem() : createArtItem())">

                    {{--Title--}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">Title:</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" v-model="art_item.title" required />
                        </div>
                    </div>

                    {{--Type--}}
                    <div class="form-group row">
                        <label for="tournament_type_id" class="col-sm-3 col-form-label">Type:</label>
                        <div class="col-sm-9">
                            <select name="type-helper" class="form-control" v-model="art_item.typeHelper"
                                    @change="art_item.type = art_item.typeHelper.trim()" required>
                                <option value=" ">--- other ---</option>
                                <option :value="itemType" v-for="itemType in art_types">@{{ itemType }}</option>
                            </select>
                            <input :type="art_item.typeHelper == ' ' ? 'text' : 'hidden'" name="type" class="form-control"
                                   v-model="art_item.type" placeholder="enter type" required/>
                        </div>
                    </div>

                    {{-- Photo --}}
                    <div class="form-group row" v-if="!editItemMode">
                        <label for="photo" class="col-sm-3 col-form-label">Photo:</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo" class="form-control" id="art-to-upload" @change="uploadArtPhoto()"/>
                            <div class="small-text text-xs-center">max 8MB png or jpg file</div>
                            {{-- photo preview --}}
                            <img id="photo-preview" v-if="art_item.photoThumbUrl != null" :src="art_item.photoThumbUrl"
                                class="shrink100x100"/>
                        </div>
                    </div>

                    {{-- Proper --}}
                    <div class="form-group row p-t-1">
                        <div class="form-check form-check-inline text-xs-center" style="width: 100%">
                            <input class="form-check-input" type="checkbox" value="" v-model="art_item.proper"
                                   style="margin-left:0" name="proper-check" false-value="0" true-value="1">
                            <label class="form-check-label" for="proper-check">
                                proper text
                                @include('partials.popover', ['direction' => 'top', 'content' =>
                                    'This card has the official text and is tournament legal.'])
                            </label>
                        </div>
                    </div>

                    {{-- Official --}}
                    <div class="form-group row p-t-1">
                        <div class="form-check form-check-inline text-xs-center" style="width: 100%">
                            <input class="form-check-input" type="checkbox" value="" v-model="art_item.official"
                                   style="margin-left:0" name="official-check" false-value="0" true-value="1">
                            <label class="form-check-label" for="official-check">
                                official FFG/NISEI item
                                @include('partials.popover', ['direction' => 'right', 'content' =>
                                    'This item is part of an official FFG/NISEI card pack or prize kit.'])
                            </label>
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <button type="submit" class="btn btn-success" v-if="!imageUploading">@{{ modalButton }}</button>
                        <em v-if="imageUploading">image uploading...</em>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>