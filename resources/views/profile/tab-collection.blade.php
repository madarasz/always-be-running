<div class="tab-pane" id="tab-collection" role="tabpanel">
    <div class="text-xs-center" v-if="userId == visitorId" v-cloak>
        You can edit your collection of official prizes on the
        <a href="/prizes">Prizes</a> page.
    </div>
    <div class="row">
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">
                <collection-part title="Keeping" :edit-mode="editMode" :public="user.prize_owning_public"
                        :extra-text="user.prize_owning_text" v-on:set-text="user.prize_owning_text = $event"
                        :collection-loaded="collectionLoaded" :prize-collection="prizeCollectionByType" part="owning"
                        :prize-items="prizeItems" :prize-kits="prizeKits" icon="fa-inbox"
                        :own-data="userId == visitorId" v-on:set-publicity="user.prize_owning_public = $event">
                </collection-part>
            </div>
        </div>
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">
                <collection-part title="Wanted" :edit-mode="editMode" :public="user.prize_wanting_public"
                         :extra-text="user.prize_wanting_text" v-on:set-text="user.prize_wanting_text = $event"
                         :collection-loaded="collectionLoaded" :prize-collection="prizeCollectionByType" part="wanting"
                         :prize-items="prizeItems" :prize-kits="prizeKits" icon="fa-download"
                         :own-data="userId == visitorId" v-on:set-publicity="user.prize_wanting_public = $event">
                </collection-part>
            </div>
        </div>
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">
                <collection-part title="For trade" :edit-mode="editMode" :public="user.prize_trading_public"
                         :extra-text="user.prize_trading_text" v-on:set-text="user.prize_trading_text = $event"
                         :collection-loaded="collectionLoaded" :prize-collection="prizeCollectionByType" part="trading"
                         :prize-items="prizeItems" :prize-kits="prizeKits" icon="fa-upload"
                         :own-data="userId == visitorId" v-on:set-publicity="user.prize_trading_public = $event">
                </collection-part>
            </div>
        </div>
    </div>
</div>