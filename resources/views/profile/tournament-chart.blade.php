<div class="bracket" :class="user.show_chart ? '' : 'hidden-xs-up'" v-cloak>
    <h5>
        <i class="fa fa-line-chart" aria-hidden="true"></i>
        Claims chart
        @include('partials.popover', ['direction' => 'top', 'content' =>
                'This chart shows your tournament rankings over time.
                The higher the bubble is the closer you were to the first place.
                Bubble size reflects number of players on the tournament.
                This chart can be hidden using your profile settings.'])
    </h5>
    <div id="chart-claim" :class="claimCount > 2 ? '' : 'hidden-xs-up'"></div>
    <div class="text-xs-center small-text m-t-1 m-b-1" v-if="claimCount < 3">
        Claim in at least 3 tournaments to make the chart appear.
    </div>
</div>