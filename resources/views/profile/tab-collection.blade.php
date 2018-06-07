<div class="tab-pane" id="tab-collection" role="tabpanel">
    <div class="text-xs-center" v-if="userId == visitorId" v-cloak>
        You can edit your collection of official prizes on the
        <a href="/prizes">Prizes</a> page.
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="bracket">
                <h5 class="p-b-1">
                    <i aria-hidden="true" class="fa fa-inbox"></i>
                    Owning
                    <div class="pull-right small-text" v-cloak>
                        <div v-if="!editMode">
                            <div v-if="user.prize_owning_public">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                                public
                            </div>
                            <div v-if="!user.prize_owning_public">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                                private
                            </div>
                        </div>
                        <div v-if="editMode">
                            <div>
                                <input class="form-check-input" type="radio" id="radio_own_private"
                                       v-model="user.prize_owning_public" v-bind:value="0">
                                <label class="form-check-label" for="radio_own_private">
                                    private
                                </label>
                            </div>
                            <div>
                                <input class="form-check-input" type="radio" id="radio_own_public"
                                       v-model="user.prize_owning_public" v-bind:value="1">
                                <label class="form-check-label" for="radio_own_public">
                                    public
                                </label>
                            </div>
                        </div>
                    </div>
                </h5>
                <div v-if="!collectionLoaded" class="row" style="padding-bottom: 6em;">
                    <div class="loader">&nbsp;</div>
                </div>
                <div style="position: relative" v-if="collectionLoaded" v-cloak>
                    <div id="overlay-owning" class="overlay" style="top: 0; bottom: 0" v-if="editMode" v-cloak>
                        <div class="text-xs-center" style="padding: 1em;">
                            <span>
                                You can edit your collection of official prizes on the <a href="/prizes">Prizes</a> page.
                            </span>
                        </div>
                    </div>
                    <table class="prize-collection table-striped">
                        <tr v-for="item in prizeCollection" v-if="item.owning > 0">
                            <td class="text-xs-right">
                                <strong>@{{ item.owning }}x</strong>
                            </td>
                            <td>
                                <div class="small-text"
                                     v-if="prizeItems[item.prize_element_id].title == '' || prizeItems[item.prize_element_id].title == null">
                                    @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].year }}
                                    @{{ prizeKits[prizeItems[item.prize_element_id].prizeKitId].title }}
                                </div>
                                <strong>@{{ prizeItems[item.prize_element_id].title }}</strong>
                                @{{ prizeItems[item.prize_element_id].type }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="bracket">
                <h5>
                    <i aria-hidden="true" class="fa fa-download"></i>
                    Wanting
                </h5>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="bracket">
                <h5>
                    <i aria-hidden="true" class="fa fa-upload"></i>
                    Trading
                </h5>
            </div>
        </div>
    </div>
</div>