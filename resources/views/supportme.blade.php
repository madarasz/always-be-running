@extends('layout.general')

@section('content')
    <h4 class="page-header">Support me</h4>
    <div class="row">
        <div class="col-xs-8">
            <div class="bracket text-justify">
                <img src="/img/portrait.jpg" class="pull-left" style="height: 3.5em; border-radius: 50%; margin: 0.5em 0.5em 0.5em 0;"/>
                Hi,
                I'm <strong>Necro</strong> and I'm the creator of
                <img src="/favicon-96x96.png" class="img-inline"/><a href="https://alwaysberunning.net">AlwaysBeRunning.net</a> and
                <img src="http://www.knowthemeta.com/favicon-96x96.png" class="img-inline"/><a href="http://www.knowthemeta.com"/>KnowTheMeta.com</a>.
                I provide these websites and services <strong>free of charge</strong> for lovers of Netrunner all around the globe.
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
        <div class="col-xs-4">
            <div class="bracket">
                {{--<img src="/img/placeholder-youtube.png" class="img-fluid"/>--}}
                {{--<div class="legal-bullshit text-xs-center">support me video coming soon</div>--}}
                {{--<hr/>--}}
                <div class="text-xs-center">
                    <strong>Thank you to all of my supporters!</strong><br/>
                    @foreach($supporters as $key=>$supporter)
                        <a href="/profile/{{$supporter->id}}" class="{{ $supporter->linkClass() }}">
                            {{$supporter->displayUsername()}}
                        </a>{{$key < $scount -1 ? ', ' : ''}}
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <hr/>
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
                <strong>Unofficial</strong> alternate art is also welcomed. Email me via <strong>alwaysberunning (at) gmail.com</strong>
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
        {{--<div class="card card-darker">--}}
            {{--<div class="card-block">--}}
                {{--<em>2017-08-18</em><br/>--}}
                {{--<p>Dear supporters,</p>--}}
                {{--<p>--}}
                    {{--Currently my job, the summer, renovating my flat and a possible startup venture are draining all--}}
                    {{--my spare time.--}}
                {{--</p>--}}
                {{--<p>--}}
                    {{--Active development of new features is on pause. No need to fear, the sites will remain up and--}}
                    {{--running. I will be still doing bugfixes, you can report problems via GitHub or email.--}}
                    {{--KnowTheMeta.com will receive data updates every 1-2 weeks.--}}
                {{--</p>--}}
                {{--<p>--}}
                    {{--Thank you for all your support so far. I will keep you updated.--}}
                {{--</p>--}}
            {{--</div>--}}
        {{--</div>--}}
    </div>
    <hr/>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <div class="row">
                    <div class="col-xs-12">
                        <a name="why" class="anchor"></a>
                        <h4>
                            Why do I need your support?
                        </h4>
                    </div>
                </div>
                <div class="row p-t-2">
                    <div class="col-xs-12 col-md-8 text-justify">
                        <h5>My git commits</h5>
                        This is me getting busy with <strong>AlwaysBeRunning.net</strong>. I know, "number of commits" is a bit obscure
                        for measuring effort. The main takeaway here is that developing a site takes <strong>time, which is a
                        valuable asset</strong>. I have to pay for hosting, but the main cost is my effort I put into developing
                        newer and newer features for the site. In the end I hope it is useful for you and I can help
                        Netrunner communities grow.
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="bracket-mini m-t-0">
                        <a href="/img/abr-commits.png">
                            <img src="/img/abr-commits.png" class="img-fluid"/>
                        </a>
                        </div>
                    </div>
                </div>
                <div class="row p-t-2">
                    <div class="col-xs-12 col-md-8 text-justify">
                        <h5>AlwaysBeRunning.net features</h5>
                        This is my Trello board for <strong>AlwaysBeRunning.net</strong> development. Usually I
                        announce new features on <a href="https://twitter.com/alwaysberunnin">my Twitter</a>, but there
                        are lot of things going on under a hood. There are also bug-fixes, minor tweeks, usability
                        improvements. Anybody can add feature requests on GitHub (as new
                        "<a href="https://github.com/madarasz/always-be-running/issues">issue</a>") or by dropping me
                        an email. I will add them to this huge list and prioritize according to
                        <strong>user-happiness-impact</strong> and <strong>coding effort</strong>. As you can see there's
                        always another feature to code, which requires <strong>my time spent developing</strong>.
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="bracket-mini m-t-0">
                            <a href="/img/abr-trello.png">
                                <img src="/img/abr-trello.png" class="img-fluid"/>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row p-t-2">
                    <div class="col-xs-12 col-md-8 text-justify">
                        <h5>Developer's reports</h5>
                        I'm posting summaries of newly developed featues to
                        <a href="https://www.patreon.com/alwaysberunning/posts">my Patreon page</a>.
                        <ul>
                            <li><a href="https://www.patreon.com/posts/15194130">Developer's report - October 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/14589035">Developer's report - September 2017</a></li>
                            <li><em>summer vacation</em> - August 2017</li>
                            <li><em>summer vacation</em> - July 2017</li>
                            <li><a href="https://www.patreon.com/posts/12936369">Developer's report - June 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/11514676">Developer's report - May 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/9843435">Developer's report - April 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/8635509">Developer's report - March 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/8116446">Developer's report - mid February 2017</a></li>
                            <li><a href="https://www.patreon.com/posts/8116380">Developer's report - mid January 2017</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-md-4">
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
