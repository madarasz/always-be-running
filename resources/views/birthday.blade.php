@extends('layout.general')

@section('content')

    <h4 class="page-header m-t-1">
        <img src="/img/badges/abr-birthday-1.png" class="badge" style="vertical-align: text-bottom">
        First birthday of <em>AlwaysBeRunning.net</em>
    </h4>
    <div class="row m-b-2">
        <div class="row-height">
            <div class="col-md-6 col-xs-12 col-sm-height">
                <div class="bracket inside-full-height">
                    <h5 class="m-b-2">
                        <i class="fa fa-child" aria-hidden="true"></i>
                        Celebrate
                    </h5>
                    <p>
                        <strong>AlwaysBeRunning.net</strong> went online on <em>2016 October 1st</em>.
                        Since then we have been advancing our agendas and breaking various ICE.
                        We are supporting the competitive community for the card game
                        <strong>Android: Netrunner</strong>.
                    </p>
                    <p>
                        This is what you can do here:
                    </p>
                    <ul>
                        <li>promote your tournaments and events</li>
                        <li>find upcoming tournaments and weekly get-togethers</li>
                        <li>claim spots on tournament rankings and track your history</li>
                        <li>make your decks famous</li>
                        <li>check out tournament results and decks</li>
                        <li>watch tournament videos</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-xs-12 col-sm-height">
                <div class="bracket inside-full-height">
                    <h5 class="m-b-2">
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                        The numbers
                    </h5>
                    <p>
                        We are proud being a growing entity in the endless vastness of cyberspace.
                        Our remotes stand strong.
                    </p>
                    <ul>
                        <li>
                            {{ $tournaments }} <a href="/results">concluded tournaments</a>
                        </li>
                        <li>
                            {{ $weekly }} weekly events
                        </li>
                        <li>
                            in {{ $countries }} countries all over the world
                        </li>
                        <li>
                            {{ $users }} users
                        </li>
                        <li>
                            {{ $claims }} tournament positions claimed
                        </li>
                        <li>
                            {{ $decks }} decks linked
                        </li>
                        <li>
                            {{ $videos }} <a href="/videos">tournament videos</a>
                        </li>
                        <li>
                            {{ $photos }} photos
                        </li>
                        <li>
                            {{ $badges }} collectible <a href="/badges">badges</a> for users
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-b-2">
        <div class="row-height">
            <div class="col-md-6 col-xs-12 col-sm-height">
                <div class="bracket inside-full-height">
                    <h5 class="m-b-2">
                        <i class="fa fa-exchange" aria-hidden="true"></i>
                        Integration
                    </h5>
                    <p>
                        We believe that information should flow freely. That's why we teamed up
                        with these excellent sites/services:
                    </p>
                    <ul>
                        <li>
                            <a href="https://netrunnerdb.com"><strong>NetrunnerDB</strong></a>: link decklists, OAuth login
                        </li>
                        <li>
                            <a href="https://itunes.apple.com/us/app/nrtm/id695468874"><strong>NRTM</strong></a>: upload tournament results
                        </li>
                        <li>
                            <strong>Google Maps</strong>: maps for locations
                        </li>
                        <li>
                            <strong>Youtube</strong>, <strong>Twitch.tv</strong>: add and watch videos
                        </li>
                        <li>
                            <strong>Facebook</strong>: import events
                        </li>
                        <li>
                            <a href="http://www.knowthemeta.com"><strong>KnowTheMeta</strong></a>: collecting tournament statistics
                        </li>
                        <li>
                            <a href="/apidoc"><strong>our own API</strong></a>: you can integrate with us
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-xs-12 col-sm-height">
                <div class="bracket inside-full-height">
                    <h5 class="m-b-2">
                        <i class="fa fa-hand-peace-o" aria-hidden="true"></i>
                        Supporters
                    </h5>
                    <p>
                        Thank you for using <strong>AlwaysBeRunning.net</strong>.
                    </p>
                    <p class="">
                        Big shout-out to the supporters:
                    </p>
                    <div class="text-xs-center m-l-3 m-r-3">
                        @foreach($supporters as $key=>$supporter)
                            <a href="/profile/{{$supporter->id}}" class="{{ $supporter->linkClass() }}">
                                {{$supporter->displayUsername()}}
                            </a>{{$key < count($supporters) -1 ? ', ' : ''}}
                        @endforeach
                    </div>
                    <p class="p-t-2">
                        <a href="/support-me">You can be a supporter as well.</a>
                    </p>

                </div>
            </div>
        </div>

    </div>
    <div class="small-text text-xs-center">
            cake icon made by
            <a href="https://www.flaticon.com/authors/freepik">freepik</a>
            from <a href="www.flaticon.com" rel="nofollow">www.flaticon.com</a>
    </div>
@stop

