{{--References--}}
<div class="bracket">
    <h5>
        <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
        References
        {{--QR code--}}
        <div class="text-xs-center p-t-1 p-b-1 markdown-content">
            <a href="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}&size=500x500">
                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}" />
            </a>
            <div class="legal-bullshit">
                provided by <a href="http://goqr.me/" rel="nofollow">goQR.me</a>
                @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Ideal for printing. It links to this tournament page. Click QR code for bigger resolution.'])
            </div>
        </div>
        {{--Calendar--}}
        <div class="text-xs-center">
            <a href="/calendar/event/{{ $tournament->id }}" class="btn btn-secondary">
                <i class="fa fa-calendar-o"></i>
                Download to Calendar
            </a>
        </div>
    </h5>
</div>