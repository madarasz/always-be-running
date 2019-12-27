{{--Modal for new/edit prize kit--}}
<div class="modal fade" id="modal-artist" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">@{{ modalTitle }}</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="(editMode ? updateArtist() : addArtist())">

                    {{--Name--}}
                    <div class="form-group row">
                        <label for="name" class="col-sm-3 col-form-label">Name:</label>
                        <div class="col-sm-9">
                            <input type="text" name="name" class="form-control" v-model="selectedArtist.name" required />
                        </div>
                    </div>

                    {{--User ID--}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-9 col-form-label">NetrunnerDB UserID:</label>
                        <div class="col-sm-3">
                            <input type="number" name="title" class="form-control" v-model="selectedArtist.user_id"/>
                        </div>
                    </div>

                    {{--Description--}}
                    <div class="form-group m-b-3">
                        <label for="description">Description:</label>
                        <textarea name="description" class="form-control" v-model="selectedArtist.description" rows="6"></textarea>
                        <div class="pull-right">
                            <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a>'])
                        </div>
                    </div>

                    {{--URL--}}
                    <div class="form-group row">
                        <label for="url" class="col-sm-3 col-form-label">Artist homepage:</label>
                        <div class="col-sm-9">
                            <input type="text" name="ffg_url" class="form-control" v-model="selectedArtist.url"
                                    placeholder="https://"/>
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <button type="submit" class="btn btn-success">@{{ modalButton }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>