@extends('layout.general')

@section('content')
    <h4 class="page-header">Support me</h4>
    <div class="row">
        <div class="col-xs-2 text-xs-center" style="margin: auto">
            <img src="/favicon-96x96.png"/>
        </div>
        <div class="col-xs-8">
            <div class="bracket text-justify">
                <img src="/img/portrait.jpg" class="pull-left" style="height: 3.5em; border-radius: 50%; margin: 0.5em 0.5em 0.5em 0;"/>
                Hi,
                I'm <strong>Necro</strong> and I'm the creator of
                <a href="https://alwaysberunning.net">AlwaysBeRunning.net</a> and
                <a href="http://www.knowthemeta.com"/>KnowTheMeta.com</a>. I provide these websites and services
                <strong>free of charge</strong> for lovers of Netrunner all around the globe.
                I'm doing this in my spare time. I would appreciate your support and get a warm, fuzzy feeling that
                my work matters.
                <div class="text-xs-center p-t-1">
                    All of my donators will receive one of these special badges:
                    <div class="p-t-1 p-b-1">
                        @foreach($badges as $badge)
                            <div class="inline-block">
                                <img src="/img/badges/{{ $badge->filename }}" data-html="true"
                                     data-toggle="tooltip" data-placement="top"
                                     title="<strong>{{ $badge->name }}</strong><br/>{{ $badge->description }}"/>
                            </div>
                        @endforeach
                    </div>
                    <div class="legal-bullshit">
                        Please mention your NetrunnerDB username while donating!
                    </div>
                </div>

            </div>
        </div>
        <div class="col-xs-2 text-xs-center" style="margin: auto">
            <img src="http://www.knowthemeta.com/favicon-96x96.png"/>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-id-card" aria-hidden="true"></i>
                    with alt-art cards, goodies
                </h5>
                As any warm-blooded Netrunner player, I love alternate art cards. I'm looking for:
                <ul>
                    @include('partials.wanted-alt')
                </ul>
                <strong>Unofficial</strong> alternate art is also welcomed. Email me via <strong>info (at) alwaysberunning.net</strong>
                for my postal address.
            </div>
        </div>
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-paypal" aria-hidden="true"></i>
                    via PayPal
                </h5>
                If you want to support me with a one-time donation, PayPal is the way to go. Choose any amount you wish.
                {{--PayPal code--}}
                <div class="text-xs-center p-t-3 p-b-1">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="2UU3UHHAB6QG4">
                        <input type="image" src="https://www.paypalobjects.com/en_US/NL/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5>
                    <img class="img-patron"/>
                    via Patreon
                </h5>
                You can support me on Patreon on a monthly basis. Check out the different reward levels here:
                {{--Patreon code--}}
                <div class="text-xs-center p-t-2 p-b-1">
                    <a href="https://www.patreon.com/bePatron?u=5036283" data-patreon-widget-type="become-patron-button">Become a Patron!</a><script async src="https://cdn6.patreon.com/becomePatronButton.bundle.js"></script>
                </div>
            </div>
        </div>
    </div>
@stop
