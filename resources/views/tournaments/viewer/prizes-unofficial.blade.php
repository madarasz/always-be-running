@if (!is_null($tournament->prize_id))
<hr/>
@endif
<h5>
    <i class="fa fa-paint-brush" aria-hidden="true"></i>
    Unofficial prizes
</h5>
<div id="unofficial-prizes">
    <div class="loader-chart" v-if="addedUnofficialPrizes.length == 0">&nbsp;</div>
    {{-- photos --}}
    <div class="row">
        <template v-for="prize in addedUnofficialPrizes">
            <div class="gallery-item col-xs-3" v-if="prize.url.length">
                <div style="position: relative;">
                    {{--image thumpnail--}}
                    <a :href="prize.url" data-toggle="lightbox" data-gallery="prize-gallery"
                    :data-title="prize.title + ' by ' + prize.artist" class="markdown-content">
                        <img :src="prize.urlThumb"/>
                    </a>
                </div>
            </div>
        </template>
    </div>
    {{-- list --}}
    <table class="table table-sm table-prizes" v-if="addedUnofficialPrizes.length">
        <tbody>
            <tr v-for="prize in addedUnofficialPrizes">
                <td class="text-xs-right">
                    <em v-if="prize.quantity.length">@{{ prize.quantity }}@{{ isNaN(prize.quantity) ? ':' : 'x'}}</em>
                </td>
                <td>
                    <a :href="prize.url" v-if="prize.url.length" data-toggle="lightbox" data-gallery="prize-gallery"
                                :data-title="prize.title + ' by ' + prize.artist">
                        <strong>@{{ prize.title }}</strong>
                        @{{ prize.type}}
                    </a>
                    <span v-else>
                        <strong>@{{ prize.title }}</strong>
                        @{{ prize.type}}
                    </span>
                    by <em>@{{ prize.artist }}</em>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
var unofficialPrizes= new Vue({
    el: '#unofficial-prizes',
    data: {
        unofficialPrizes: [],
        addedUnofficialPrizes: []
    },
    mounted: function() {
        this.loadUnofficialPrizes();
    }, 
    methods: {
        loadUnofficialPrizes: function() {
            axios.get('/api/artists').then(function (response) {
                unofficialPrizes.unofficialPrizes = response.data.map(
                    x => { return x.items.map(
                        y => { return {
                            id: y.id, 
                            title: y.title, 
                            artist: x.displayArtistName,
                            urlThumb: y.photos.length > 0 ? y.photos[0].urlThumb : "",
                            url: y.photos.length > 0 ? y.photos[0].url : "",
                            type: y.type
                        }}
                    )}
                ).flat();
                unofficialPrizes.loadAddedUnofficialPrizes();
            }, function (response) {
                // error handling
                toastr.error('Something went wrong while loading the unofficial prizes.', {timeOut: 2000});
            });
        },
        loadAddedUnofficialPrizes: function() {
            axios.get('/api/tournaments/'+{{ $tournament->id }}+'/unofficial-prizes').then(function (response) {
                unofficialPrizes.addedUnofficialPrizes = response.data.map(
                    x => {
                        prize =  unofficialPrizes.unofficialPrizes.find(y => { return y.id == x.prize_element_id; });
                        return {
                            artist: prize.artist,
                            quantity: x.quantity,
                            title: prize.title,
                            urlThumb: prize.urlThumb,
                            url: prize.url
                        };
                    }
                );
            }, function (response) {
                // error handling
                toastr.error('Something went wrong while loading the unofficial prizes for the tournament', '', {timeOut: 2000});
            });
        }
    }
});
</script>