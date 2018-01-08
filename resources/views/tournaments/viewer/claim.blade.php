{{--User claim--}}
@if ($user)
    <hr/>
    <h6>Your claim</h6>
    {{--Existing claim--}}
    @if ($user_entry && $user_entry->type >= 3)
        <ul id="player-claim">
            @if ($tournament->top_number)
                <li>Top cut rank:
                    @if ($user_entry->rank_top)
                        <strong>#{{ $user_entry->rank_top}}</strong>
                    @else
                        <em>none</em>
                    @endif
                </li>
            @endif
            <li>Swiss rounds rank: <strong>#{{ $user_entry->rank }}</strong></li>
            <li>
                Corporation {{ $user_entry->type == 3 ? 'deck' : 'ID'}}:
                <img src="/img/ids/{{ $user_entry->corp_deck_identity }}.png" class="id-small">&nbsp;
                {{--public deck--}}
                @if ($user_entry->corp_deck_type == 1)
                    <a href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->corp_deck_id }}">
                        {{ $user_entry->corp_deck_title }}
                    </a>
                    {{--private deck--}}
                @elseif ($user_entry->corp_deck_type == 2)
                    <a href="{{ "https://netrunnerdb.com/en/deck/view/".$user_entry->corp_deck_id }}">
                        {{ $user_entry->corp_deck_title }}
                    </a>
                @else
                    {{ $user_entry->corp_deck_title }}
                @endif
            </li>
            <li>
                Runner {{ $user_entry->type == 3 ? 'deck' : 'ID'}}:
                <img src="/img/ids/{{ $user_entry->runner_deck_identity }}.png" class="id-small">&nbsp;
                {{--public deck--}}
                @if ($user_entry->runner_deck_type == 1)
                    <a href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->runner_deck_id }}">
                        {{ $user_entry->runner_deck_title }}
                    </a>
                    {{--private deck--}}
                @elseif ($user_entry->runner_deck_type == 2)
                    <a href="{{ "https://netrunnerdb.com/en/deck/view/".$user_entry->runner_deck_id }}">
                        {{ $user_entry->runner_deck_title }}
                    </a>
                @else
                    {{ $user_entry->runner_deck_title }}
                @endif
            </li>
        </ul>
        <div class="text-xs-center">
            {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$user_entry->id"]) !!}
            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove my claim',
            array('type' => 'submit', 'class' => 'btn btn-danger', 'id' => 'remove-claim')) !!}
            {!! Form::close() !!}
        </div>
        {{--Creating new claim--}}
    @else
        @include('tournaments.modals.claim')
        <div class="text-xs-center">
            <button class="btn btn-claim" data-toggle="modal"
                    data-players-number="{{$tournament->players_number}}"
                    data-top-number="{{$tournament->top_number}}"
                    data-target="#claimModal" data-tournament-id="{{$tournament->id}}"
                    data-subtitle="{{$tournament->title.' - '.$tournament->date}}" id="button-claim">
                <i class="fa fa-list-ol" aria-hidden="true"></i> Claim your spot
            </button>
        </div>
    @endif
@else
    <hr/>
    <div class="text-xs-center" id="suggest-login">
        <a href="/oauth2/redirect">Login via NetrunnerDB</a> to claim spot.
    </div>
@endif