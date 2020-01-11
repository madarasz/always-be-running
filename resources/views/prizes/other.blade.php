<div class="tab-pane" id="tab-other" role="tabpanel">
    <div class="row" v-for="artist in artists">
        <div class="col-xs-12">
            <div class="bracket">
                <h5 style="overflow: hidden">
                    <i aria-hidden="true" class="fa fa-paint-brush"></i>
                    <strong>
                        <a v-if="artist.user" :href="'/profile/'+artist.user.id">
                            @{{ artist.displayArtistName }}
                        </a>
                        <span v-if="!artist.user">
                            @{{ artist.displayArtistName }}
                        </span>
                    </strong>
                    {{-- artist webpage --}}
                    <a class="small-text pull-right hidden-sm-down" v-if="artist.url" :href="artist.url">
                        @{{ artist.url}}
                    </a>
                    <a class="small-text ellipis-overflow hidden-md-up" v-if="artist.url" :href="artist.url" style="">
                        @{{ artist.url}}
                    </a>
                </h5>
                <div class="markdown-content" v-html="markdownDescription( artist.description)">
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-6 p-t-1 p-b-1 checkered-md" v-for="(item, index) in artist.items">
                        <div class="flex-row">
                            <div class="gallery-item" style="margin: 0" v-for="photo in item.photos">
                                <div style="position: relative;">
                                    {{--image thumpnail--}}
                                    <a :href="photo.url" data-toggle="lightbox" data-gallery="prizekit-gallery"
                                        :data-title="item.title">
                                        <img :src="photo.urlThumb" class="shrink100x100"/>
                                    </a>
                                </div>
                            </div>     
                            <div class="m-l-1">
                                @{{ item.title }} <em v-if="item.info && item.info.length > 0">(@{{ item.info }})</em><br/>
                                <span class="small-text">@{{ item.type}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>