<div class="tab-pane active" id="tab-official" role="tabpanel">
    {{--Filters--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <div class="row">
                    {{--filter title--}}
                    <div class="col-xs-12 col-lg-2 col-md-4">
                        <h5 class="h5-filter">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                            Filter
                        </h5>
                    </div>
                    {{--select kit--}}
                    <div class="col-xs-12 col-lg-6 col-md-8" style="padding-bottom:0.5em">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-gift" aria-hidden="true"></i></span>
                            <select class="custom-select" style="width: 100%" v-model="selectedPrizeId"
                                    :disabled="searchText.length > 0" @change="updateFilter()">
                                <option value="0">--- all ---</option>
                                <option v-for="prize in prizes" :value="prize.id">@{{ prize.year+' '+prize.title }}</option>
                            </select>
                        </div>
                    </div>
                    {{--search--}}
                    <div class="col-xs-12 col-lg-4 col-md-8 offset-md-4 offset-lg-0">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search" aria-hidden="true"></i></span>
                            <input type="search" name="prize-search" v-model="searchText"
                                class="form-control" :disabled="selectedPrizeId != 0" @input="updateFilter()"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--loader--}}
        <div class="col-xs-12 p-b-3" v-if="prizes.length == 0">
            <div class="loader" id="prizes-loader">&nbsp;</div>
        </div>
    </div>

    {{--Prize kit brackets--}}
    <div class="row" v-for="(prize, index) in prizes">
        <div :class="prizeVisibility[index] ? 'col-xs-12':'col-xs-12 hidden-xs-up'">
            <div class="bracket">
                {{--Photos--}}
                <h5>
                    <i aria-hidden="true" class="fa fa-gift"></i>
                    {{--@{{ prize.year + ' ' + prize.title | searchHighlight }}--}}
                    <span :inner-html.prop="prize.year + ' ' + prize.title | searchHighlight"></span>
                </h5>
                {{--Photos--}}
                <div class="row">
                    {{--Photos of prize--}}
                    <div class="gallery-item col-xl-2 col-md-3 col-sm-4 col-xs-6" v-for="photo in prize.photos" :key="photo.url">
                        <div style="position: relative;">
                            {{--image thumpnail--}}
                            <a :href="photo.url" data-toggle="lightbox" :data-gallery="'prize-gallery' + prize.id"
                            :data-title="prize.year + ' ' + prize.title">
                                <img :src="photo.urlThumb" style="width: 150px; height: auto"/>
                            </a>
                        </div>
                    </div>
                    {{--Photos of prize elements--}}
                    <template v-for="item in prize.elements">
                        <div class="gallery-item col-xl-2 col-md-3 col-sm-4 col-xs-6" v-for="photo in item.photos" :key="photo.url">
                            <div style="position: relative;">
                                {{--image thumpnail--}}
                                <a :href="photo.url" data-toggle="lightbox" :data-gallery="'prize-gallery' + prize.id"
                                :data-title="prize.year + ' ' + prize.title"
                                :data-footer="'<em>'+item.quantityString+':</em> <strong>'+item.title+'</strong> '+item.type">
                                    <img :src="photo.urlThumb" style="width: 150px; height: auto"/>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
                {{--Prize table--}}
                <table class="table table-sm">
                    <thead>
                        <th class="text-xs-right">quantity</th>
                        <th>prize</th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center text-nowrap">
                            <i title="collection" class="fa fa-inbox hidden-xl-up"></i>
                            <span class="hidden-lg-down">keeping</span>
                        </th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center text-nowrap">
                            <i title="wanting" class="fa fa-download hidden-xl-up"></i>
                            <span class="hidden-lg-down">wanted</span>
                        </th>
                        <th v-if="userId" style="width: 1%" class="text-xs-center text-nowrap">
                            <i title="for trade" class="fa fa-upload hidden-xl-up"></i>
                            <span class="hidden-lg-down">for trade</span>
                        </th>
                    </thead>
                    <tbody>
                        <tr v-for="item in prize.elements">
                            <td class="text-xs-right">
                                <em>@{{ item.quantityString }}:</em>
                            </td>
                            <td>
                                {{--doesn't have photo--}}
                                <span v-if="item.photos.length == 0">
                                    <strong :inner-html.prop="item.title | searchHighlight"></strong>
                                    <span :inner-html.prop="item.type | searchHighlight"></span>
                                </span>
                                {{--has photo--}}
                                <a v-if="item.photos.length > 0" :href="item.photos[0].url" data-toggle="lightbox"
                                :data-gallery="'prize-gallery' + prize.id"
                                :data-title="prize.year + ' ' + prize.title"
                                :data-footer="'<em>'+item.quantityString+':</em> <strong>'+item.title+'</strong> '+item.type">
                                    <strong :inner-html.prop="item.title | searchHighlight"></strong>
                                    <span :inner-html.prop="item.type | searchHighlight"></span>
                                </a>
                            </td>
                            {{--owning--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].owning > 0 && !editMode">
                                    @{{ prizeCollection[item.id].owning }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                            @click="modifyCollection(item.id, 'owning', -1)"
                                            :disabled="prizeCollection[item.id].owning == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                        v-model="prizeCollection[item.id].owning" maxlength="2"
                                    /><button class="btn btn-xs btn-primary"
                                            @click="modifyCollection(item.id, 'owning', 1)"
                                            :disabled="prizeCollection[item.id].owning == 99">+</button>
                                </span>
                            </td>
                            {{--wanting--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].wanting > 0 && !editMode">
                                    @{{ prizeCollection[item.id].wanting }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'wanting', -1)"
                                            :disabled="prizeCollection[item.id].wanting == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                            v-model="prizeCollection[item.id].wanting" maxlength="2"
                                            /><button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'wanting', 1)"
                                            :disabled="prizeCollection[item.id].wanting == 99">+</button>
                                </span>
                            </td>
                            {{--trading--}}
                            <td class="text-xs-center text-nowrap">
                                <span v-if="collectionLoaded && prizeCollection[item.id].trading > 0 && !editMode">
                                    @{{ prizeCollection[item.id].trading }}
                                </span>
                                <span v-if="collectionLoaded && editMode">
                                    <button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'trading', -1)"
                                            :disabled="prizeCollection[item.id].trading == 0">-</button
                                    ><input type="tel"  class="input-collection"
                                            v-model="prizeCollection[item.id].trading" maxlength="2"
                                            /><button class="btn btn-xs btn-primary"
                                    @click="modifyCollection(item.id, 'trading', 1)"
                                            :disabled="prizeCollection[item.id].trading == 99">+</button>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{--Description--}}
                <div class="markdown-content">
                    {{--{!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize->description)) !!}--}}
                </div>
                <div class="text-xs-right" v-if="prize.ffg_url && prize.ffg_url.length > 0">
                    <a :href="prize.ffg_url">read more</a>
                </div>
            </div>
        </div>
    </div>
    <div class="text-xs-center" v-if="!foundKit">
        <em>no such prize kit found</em>
    </div>
</div>