{{--QR code--}}
<div class="bracket">
    <h5>
        <i class="fa fa-qrcode" aria-hidden="true"></i>
        QR code
        @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Ideal for printing. It links to this tournament page. Click QR code for bigger resolution.'])
        <div class="text-xs-center p-t-1 markdown-content">
            <a href="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}&size=500x500">
                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}" />
            </a>
            <div class="legal-bullshit">
                provided by <a href="http://goqr.me/" rel="nofollow">goQR.me</a>
            </div>
        </div>
    </h5>
</div>