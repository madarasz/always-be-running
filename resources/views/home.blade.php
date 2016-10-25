@extends('layout.general')

@section('content')
    <h4 class="page-header">Welcome</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-9 col-xs-12">
            {{--<div class="row">--}}
                {{--<div class="row-height">--}}
                    {{--<div class="col-md-6 col-xs-12 col-sm-height">--}}
                        {{--<div class="bracket inside-full-height">--}}
                            {{--<h5>--}}
                                {{--<i class="fa fa-star" aria-hidden="true"></i>--}}
                                {{--Featured tournament--}}
                            {{--</h5>--}}
                            {{--<div class="featured-tournament">--}}
                                {{--<a href="/tournaments/1">--}}
                                    {{--<img src="http://i.imgur.com/GTcljf0m.jpg"/>--}}
                                    {{--<div class="small-bold">--}}
                                        {{--2016 Hungarian nationals</div>--}}
                                {{--</a>--}}
                                {{--<div class="small-text">--}}
                                    {{--20 players<br/>--}}
                                    {{--winner: Korrigan <img src="/img/ids/01033.png">&nbsp;<img src="/img/ids/06005.png">--}}
                                {{--</div>--}}
                            {{--</div>--}}

                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<div class="col-md-6 col-xs-12 col-sm-height">--}}
                        {{--<div class="bracket inside-full-height">--}}
                            {{--<h5>--}}
                                {{--<i class="fa fa-info" aria-hidden="true"></i>--}}
                                {{--News--}}
                            {{--</h5>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
            {{--Upcoming--}}
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'cardpool'],
                'title' => 'Upcoming tournaments', 'id' => 'discover-table', 'icon' => 'fa-list-alt', 'loader' => true])
            </div>
            {{--Results--}}
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims' ],
                    'title' => 'Tournament results', 'id' => 'results', 'icon' => 'fa-list-alt'])
            </div>
        </div>
        <div class="col-md-3 col-xs-12">
            {{--User info--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    User
                </h5>
                <div class="text-xs-center">
                    @if ($user)
                        <h6>{{ $user->name }}</h6>
                        <div class="user-counts">
                            {{ $created_count }} tournament{{ $created_count > 1 ? 's' : '' }} organized<br/>
                            {{ $claim_count }} tournament claim{{ $claim_count > 1 ? 's' : '' }}<br/>
                            {{ $user->published_decks }} published deck{{ $user->published_decks > 1 ? 's' : '' }}
                            @if ($user->private_decks)
                                <br/>
                                {{ $user->private_decks }} private deck{{ $user->private_decks > 1 ? 's' : '' }}
                            @endif
                        </div>
                    @else
                        <div class="m-b-1 m-t-1">
                            <a href="/oauth2/redirect">Login via NetrunnerDB</a>
                        </div>
                    @endif
                </div>
            </div>
            {{--Integration--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-link" aria-hidden="true"></i>
                    Integration
                </h5>
                <div class="integration">
                    <a href="https://netrunnerdb.com/">
                        <img src="/img/logo_netrunnerdb.png"/>
                    </a>
                    <div class="hint">link decks to tournament claims</div>
                </div>
                <div class="integration">
                    <a href="https://itunes.apple.com/us/app/nrtm/id695468874?mt=8">
                        <img src="/img/logo_nrtm.png"/>
                    </a>
                    <div class="hint">upload tournament results</div>
                </div>
            </div>
            {{--Hot IDs--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-fire" aria-hidden="true"></i>
                    Popular IDs - <span id="hot-packname" class="small-text"></span><br/>
                    <small>by <a href="http://www.knowthemeta.com/">Know the Meta</a></small>
                </h5>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 col-md-12">
                        <div class="hot-id" id="hot-id-runner"></div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-12">
                        <div class="hot-id" id="hot-id-corp"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        // upcoming table
        getTournamentData('start={{ $yesterday }}&approved=1', function(data) {
            $('.loader').addClass('hidden-xs-up');
            updateTournamentTable('#discover-table', ['title', 'date', 'location', 'cardpool'], 'no tournaments to show', '', data);
            // results table
            getTournamentData("approved=1&concluded=1&end={{ $tomorrow }}", function(data) {
                updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'winner', 'players', 'claims'], 'no tournaments to show', '', data);
                $('.filter').prop("disabled", false);
                // KtM get
                getKTMDataPacks(function (packs) {
                    updatePopularIds(packs[packs.length-1]);
                })
            });
        });
    </script>
@stop

