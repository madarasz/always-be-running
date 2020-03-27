{{--References--}}
<div class="bracket">
    <h5>
        <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
        References
        {{--Calendar--}}
        <div class="text-xs-center p-t-1">
            <a href="/calendar/event/{{ $tournament->id }}" class="btn btn-info">
                <i class="fa fa-calendar-o"></i>
                Download to Calendar
            </a>
        </div>
        {{--QR code--}}
        <div class="text-xs-center p-t-2">
            <a href="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}&size=500x500">
                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}" class="qr-code"/>
            </a>
            <div>event QR code</div>
            <div class="legal-bullshit">
                provided by <a href="http://goqr.me/" rel="nofollow">goQR.me</a>
                @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Ideal for printing. It links to this tournament page. Click QR code for bigger resolution.'])
            </div>
        </div>
        
    </h5>
</div>