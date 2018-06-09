{{--collection part template--}}
<script type="text/x-template" id="template-collection-part">
    <div>
        {{--Header--}}
        <h5 class="p-b-1">
            <i aria-hidden="true" :class="'fa '+icon"></i>
            @{{ title }}
            <div class="pull-right small-text" v-cloak>
                <div v-if="!editMode">
                     <div v-if="public">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                        public
                    </div>
                    <div v-if="!public">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        private
                    </div>
                </div>
                <div v-if="editMode">
                    <div>
                        <input class="form-check-input" type="radio" :id="'radio_'+title+'_private'"
                               @click="$emit('set-publicity', 0)" v-model="public" v-bind:value="0"/>
                        <label class="m-b-0" :for="'radio_'+title+'_private'">
                            private
                        </label>
                    </div>
                    <div>
                        <input class="form-check-input" type="radio" :id="'radio_'+title+'_public'"
                                @click="$emit('set-publicity', 1)" v-model="public" v-bind:value="1"/>
                        <label class="m-b-0" :for="'radio_'+title+'_public'">
                            public
                        </label>
                    </div>
                </div>
            </div>
        </h5>
        {{--Loader--}}
        <div v-if="!collectionLoaded" class="row" style="padding-bottom: 6em;">
            <div class="loader">&nbsp;</div>
        </div>
        {{--List--}}
        <div style="position: relative; min-height: 4em" v-if="collectionLoaded" v-cloak>
            <div :id="'overlay-' + title" class="overlay" style="top: 0; bottom: 0" v-if="editMode" v-cloak>
                <div class="text-xs-center" style="padding: 1em;">
                    <span>
                        You can edit your collection of official prizes on the <a href="/prizes">Prizes</a> page.
                    </span>
                </div>
            </div>

            <div v-for="(itemType, index) in prizeCollection" class="p-b-1" v-if="hasDataIn(index)">
                <span style="text-decoration: underline">@{{ index }}</span>
                <table class="prize-collection table-striped">
                    <tr v-for="item in itemType" v-if="item[part] > 0">
                        <td class="text-xs-right" style="width: 1%">
                            <strong>@{{ item[part] }}x</strong>
                        </td>
                        <td>
                            {{--has photo--}}
                            <a v-if="prizeItems[item.prize_element_id].photoUrl"
                               :href="prizeItems[item.prize_element_id].photoUrl" data-toggle="lightbox"
                               :data-gallery="'gallery-' + item.prize_element_id"
                               :data-title="prizeKits[prizeItems[item.prize_element_id].prizeKitId].year + ' ' + prizeKits[prizeItems[item.prize_element_id].prizeKitId].title"
                               :data-footer="'<strong>'+prizeKits[prizeItems[item.prize_element_id].prizeKitId].title+'</strong> '+prizeItems[item.prize_element_id].type">
                            <span class="small-text"
                                  v-if="prizeItems[item.prize_element_id].title == '' || prizeItems[item.prize_element_id].title == null">
                                @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].year }}
                                @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].title }}
                            </span>
                                <strong>@{{ prizeItems[item.prize_element_id].title }}</strong>
                            </a>
                            {{--doesn't have photo--}}
                            <span v-if="!prizeItems[item.prize_element_id].photoUrl">
                            <span class="small-text"
                                  v-if="prizeItems[item.prize_element_id].title == '' || prizeItems[item.prize_element_id].title == null">
                                @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].year }}
                                @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].title }}
                            </span>
                            <strong>@{{ prizeItems[item.prize_element_id].title }}</strong>
                        </span>
{{--                            @{{ prizeItems[item.prize_element_id].type }}--}}
                        </td>
                    </tr>
                </table>
            </div>


            {{--No data--}}
            <div class="text-xs-center m-t-1" v-if="!hasData" v-cloak>
                <em v-if="ownData || public">empty</em>
                <em v-if="!ownData && !public">private information</em>
            </div>
        </div>
        {{--Extra text--}}
        <div v-if="(editMode || extraText.length > 0) && (ownData || public)" class="p-t-1" v-cloak>
            <strong>Additional items/info:</strong><br/>
            <div v-if="editMode" class="p-b-2">
                <textarea rows="6" @input="$emit('set-text', $event.target.value)" style="width:100%">@{{ extraText }}</textarea>
                <div class="pull-right">
                    <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a><br/>'])
                </div>
            </div>
            <div class="markdown-content" v-if="!editMode" v-html="markdownText"></div>
        </div>
    </div>
</script>