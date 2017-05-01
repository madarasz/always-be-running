@if ($user->show_chart)
    <div class="bracket">
        <h5>
            <i class="fa fa-line-chart" aria-hidden="true"></i>
            Claims chart
            @include('partials.popover', ['direction' => 'top', 'content' =>
                    'This chart shows your tournament rankings over time.
                    The higher the bubble is the closer you were to the first place.
                    Bubble size reflects number of players on the tournament.
                    This chart can be hidden using your profile settings.'])
        </h5>
        @if ($claim_count > 2)
            <div id="chart-claim"></div>
        @else
            <div class="text-xs-center small-text m-t-1 m-b-1">
                Claim in at least 3 tournaments to make the chart appear.
            </div>
        @endif
    </div>
@endif