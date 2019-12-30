<div class="tab-pane" id="tab-my-art" role="tabpanel">
    @include('profile.modal-art-item')
    @include('profile.modal-art-upload')
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback"></confirm-modal>
    <div class="row">
        {{-- Artist details --}}
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">             
                <h5>
                    <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                    Artist details
                </h5> 
                {{-- website --}}
                <div class="form-group row m-a-1">
                    <h6 style="overflow: hidden">
                        <strong>homepage:</strong>
                        <div class="col-form-label" v-if="!editMode">
                            <a :href="artist.url" v-cloak class="ellipis-overflow">@{{ artist.url }}</a>
                        </div>
                    </h6>
                    <input class="form-control" type="text" id="artist-website" v-if="editMode" v-cloak
                               name="artist-website" v-model="artist.url" placeholder="https://..." @change="artistDetailsChanged = true">
                </div>
                {{-- description --}}
                <div class="row m-t-1 m-l-1 m-r-1">
                    <h6>
                        <strong>additional information:</strong>
                    </h6>
                </div>
                <div class="form-group row m-b-1 m-l-1 m-r-1">
                    <div class="markdown-content" v-html="markdownArtistDescription" v-if="!editMode"></div>
                    <div v-if="editMode" style="width: 100%">
                        <textarea rows="6" cols="" name="artist-description" class="form-control" v-model="artist.description" 
                            @change="artistDetailsChanged = true" v-cloak>
                        </textarea>
                        <div class="pull-right">
                            <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a><br/>
                                    <br/>
                                    How to make your tournament look cool?<br/>
                                    <a href="/markdown" target="_blank">example formatted description</a>'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Gallery --}}
        <div class="col-xs-12 col-lg 8">
            <div class="bracket">
                <h5>
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    Gallery
                    {{--create button--}}
                    <div class="pull-right" v-if="editMode">
                        <a class="btn btn-primary btn-sm white-text" @click.stop="modalForAddArtItem">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Add
                        </a>
                    </div>
                </h5>
                <table class="table table-sm table-striped abr-table">
                    <thead>
                        <th class="text-xs-center">
                            <i class="fa fa-camera" aria-hidden="true"></i>
                        </th>
                        <th style="width: 99%">item</th>
                        <th></th>
                    </thead>
                    <tbody>
                        {{-- no art item message --}}
                        <tr v-if="artist.items && artist.items.length == 0">
                            <td colspan="3" class="text-xs-center">
                                <em>no art items yet</em>
                            </td>
                        </tr>
                        {{-- list of art items --}}
                        <tr v-for="(item, index) in artist.items">
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
                                            <div class="abs-top-left" v-if="editMode">
                                                <form method="post" action="" style="display: inline">
                                                    <input name="_method" type="hidden" value="DELETE"/>
                                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                                    <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                                        @click="confirmCallback = function() { deleteArtPhoto(photo.id) }; confirmText = 'Delete photo?'" />
                                                </form>
                                            </div>
                                        </div>
                                    </div>     
                                    <div v-if="editMode && item.photos.length < maxArtPhotos" class="size100x100 flex-center">
                                        <a class="btn btn-primary btn-xs white-text" v-if="editMode" @click="modallForAddPhoto(index)">
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
                                <a class="btn btn-primary btn-xs white-text" v-if="editMode" @click="modalForEditArtItem(index)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                <confirm-button button-class="btn btn-danger btn-xs" button-icon="fa fa-trash" button-text="delete" v-if="editMode"
                                            @click="confirmCallback = function() { deleteArtItem(item.id) }; confirmText = 'Are you sure you want to delete art item?'" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>