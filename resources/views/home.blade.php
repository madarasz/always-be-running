@extends('layout.general')

@section('content')
    <h4 class="page-header">Welcome</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-9 col-xs-12">
            {{--Upcoming--}}
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'cardpool'],
                'title' => 'Upcoming tournaments', 'id' => 'discover-table', 'icon' => 'fa-list-alt', 'loader' => true])
                {{--<div class="p-t-1">--}}
                    {{--<a href="/upcoming">More upcoming >>></a>--}}
                {{--</div>--}}
            </div>
            {{--Results--}}
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'players', 'claims' ],
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
                    Hot IDs<br/>
                    <small>by <a href="http://www.knowthemeta.com/">Know the Meta</a></small>
                </h5>
                <div class="hot-id">
                    <img src="http://www.knowthemeta.com/static/img/cards/netrunner-whizzard-master-gamer.png"/><br/>
                    <small>Whizzard: Master Gamer</small>
                </div>
                <div class="hot-id">
                    <img src="http://www.knowthemeta.com/static/img/cards/netrunner-nbn-controlling-the-message.png"/><br/>
                    <small>NBN: Controlling the Message</small>
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
                updateTournamentTable('#results', ['title', 'date', 'location', 'cardpool', 'players', 'claims'], 'no tournaments to show', '', data);
                $('.filter').prop("disabled", false);
            });
        });
    </script>
@stop

