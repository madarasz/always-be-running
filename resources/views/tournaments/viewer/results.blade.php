{{--Tournament results--}}
<div class="bracket">
    @if ($tournament->concluded)
        <h5>
            <i class="fa fa-list-ol" aria-hidden="true"></i>
            Results
        </h5>
        {{--Conflict--}}
        @if ($tournament->conflict)
            <div class="alert alert-danger" id="conflict-warning">
                <i class="fa fa-exclamation-triangle text-danger" title="conflict"></i>
                This tournament has conflicting claims.<br/>
                Claims can be removed by the tournament creator, admins or claim owners.
            </div>
        @endif
        {{--Player numbers--}}
        <div id="player-numbers">
            <strong>Number of players</strong>: {{ $tournament->players_number }}<br/>
            @if ($tournament->top_number)
                <span id="top-player-numbers">
                                <strong>Top cut players</strong>: {{ $tournament->top_number }}
                            </span><br/>
            @else
                <em>only swiss rounds, no top cut</em><br/>
            @endif
        </div>
        {{--User claim--}}
        @if ($user)
            <hr/>
            <h6>Your claim</h6>
            {{--Existing claim--}}
            @if ($user_entry && $user_entry->runner_deck_id)
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
                        Corporation deck:
                        <img src="/img/ids/{{ $user_entry->corp_deck_identity }}.png">&nbsp;
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
                            data error
                        @endif
                    </li>
                    <li>
                        Runner deck:
                        <img src="/img/ids/{{ $user_entry->runner_deck_identity }}.png">&nbsp;
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
                            data error
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
        <hr/>
        {{--Import NRTM, Clear anonym claims--}}
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <a name="importing"/>
            <div class="text-xs-center">
                @if ($tournament->import)
                    {{--Clear import--}}
                    {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id/clearanonym", 'class' => 'inline-block']) !!}
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove all imported claims',
                        array('type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'id' => 'button-clear-nrtm')) !!}
                    {!! Form::close() !!}
                @else
                    {{--Import--}}
                    <button class="btn btn-conclude btn-xs" data-toggle="modal" data-hide-manual="true"
                            data-target="#concludeModal" data-tournament-id="{{$tournament->id}}"
                            data-subtitle="{{$tournament->title.' - '.$tournament->date}}" id="button-import-nrtm">
                        <i class="fa fa-check" aria-hidden="true"></i> Import results
                    </button>
                @endif
                {{--Edit entries button--}}
                <button class="btn btn-primary btn-xs" id="button-edit-entries"
                        onclick="toggleEntriesEdit(true)">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Import manually
                </button>
                <button class="btn btn-primary btn-xs hidden-xs-up" id="button-done-entries"
                        onclick="toggleEntriesEdit(false)">
                    <i class="fa fa-check" aria-hidden="true"></i> Done
                </button>
                {{--Edit entries form--}}
                <div id="section-edit-entries" class="hidden-xs-up small-text">
                    <hr/>
                    <div class="p-b-1">
                        <i class="fa fa-user-circle" aria-hidden="true"></i>
                        You can import IDs. Only players can link their decklists.
                    </div>
                    {!! Form::open(['method' => 'POST', 'url' => "/entries/anonym",
                        'class' => 'form-inline']) !!}
                    {!! Form::hidden('tournament_id', $tournament->id) !!}
                    {!! Form::hidden('corp_deck_title', '', ['id' => 'corp_deck_title']) !!}
                    {!! Form::hidden('runner_deck_title', '', ['id' => 'runner_deck_title']) !!}
                    @if ($tournament->top_number)
                        <div class="form-group">
                            {!! Form::label('rank_top', 'top-cut') !!}
                            {!! Form::select('rank_top',
                                array_combine(range(0, $tournament->top_number), array_merge(['n/a'], range(1, $tournament->top_number))),
                                null, ['class' => 'form-control']) !!}
                        </div>
                    @else
                        {!! Form::hidden('rank_top', 0) !!}
                    @endif
                    <div class="form-group">
                        {!! Form::label('rank', 'swiss') !!}
                        {!! Form::select('rank',
                            array_combine(range(1, $tournament->players_number), range(1, $tournament->players_number))
                            , null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('import_username', 'name') !!}
                        {!! Form::text('import_username', '', ['class' => 'form-control']) !!}
                    </div><br/>
                    <div class="form-group">
                        {!! Form::label('corp_deck_identity', 'corp ID') !!}
                        <select name="corp_deck_identity" class="form-control" id="corp_deck_identity" onchange="recalculateDeckNames()">
                            @foreach($corpIDs as $key => $faction)
                                <optgroup label="{{ $key }}">
                                    @foreach($faction as $code => $id)
                                        <option value="{{ $code }}">{{ $id }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group p-b-1">
                        {!! Form::label('runner_deck_identity', 'runner ID') !!}
                        <select name="runner_deck_identity" class="form-control" id="runner_deck_identity" onchange="recalculateDeckNames()">
                            @foreach($runnerIDs as $key => $faction)
                                <optgroup label="{{ $key }}">
                                    @foreach($faction as $code => $id)
                                        <option value="{{ $code }}">{{ $id }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div><br/>
                    {!! Form::button('Add result', array('type' => 'submit',
                        'class' => 'btn btn-success btn-xs', 'id' => 'button-add-claim')) !!}
                    {!! Form::close() !!}
                </div>
                <hr/>
            </div>
        @endif
        {{--Tables of tournament standings --}}
        @if ($tournament->top_number)
            <h6>Top cut</h6>
            @include('tournaments.partials.entries',
                ['entries' => $entries_top, 'user_entry' => $user_entry, 'rank' => 'rank_top',
                'creator' => $tournament->creator, 'id' => 'entries-top'])
            <hr/>
        @endif
        <h6>Swiss rounds</h6>
        @include('tournaments.partials.entries',
            ['entries' => $entries_swiss, 'user_entry' => $user_entry, 'rank' => 'rank',
            'creator' => $tournament->creator, 'id' => 'entries-swiss'])
        <hr/>
        {{--Tournament is due and not non-tournament without results--}}
    @elseif($tournament->date <= $nowdate && $tournament->tournament_type_id != 8)
        <h5>
            <i class="fa fa-list-ol" aria-hidden="true"></i>
            Results
        </h5>
        <div class="alert alert-warning" id="due-warning">
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            This tournament is due for completion.<br/>
            The tournament creator should set it to 'concluded', so players can make claims.
        </div>
        {{--Conclude modal, button--}}
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="text-xs-center">
                <button class="btn btn-conclude" data-toggle="modal" data-target="#concludeModal"
                        data-tournament-id="{{$tournament->id}}"
                        data-subtitle="{{$tournament->title.' - '.$tournament->date}}" id="button-conclude">
                    <i class="fa fa-check" aria-hidden="true"></i> Conclude
                </button>
            </div>
        @endif
        <hr/>
    @endif
    {{--List of registered players--}}
    <h6>Registered players {{ $regcount > 0 ? '('.$regcount.')' : '' }}</h6>
    @if (count($entries) > 0)
        <ul id="registered-players">
            @foreach ($entries as $entry)
                @if ($entry->player)
                    <li><a href="/profile/{{ $entry->player->id }}">{{ $entry->player->displayUsername() }}</a></li>
                @endif
            @endforeach
        </ul>
    @else
        <p><em id="no-registered-players">no players yet</em></p>
    @endif
    @if (!$tournament->concluded)
        <div class="text-xs-center">
            @if ($user)
                @if ($user_entry)
                    @if ($user_entry->rank)
                        <span class="btn btn-danger disabled" id="unregister-disabled"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</span><br/>
                        <small><em>remove your claim first</em></small>
                    @else
                        <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
                    @endif
                @else
                    <a href="{{"/tournaments/$tournament->id/register"}}" class="btn btn-primary" id="register"><i class="fa fa-plus-circle" aria-hidden="true"></i> Register</a>
                @endif
            @else
                <div class="text-xs-center p-b-1" id="suggest-login2">
                    <a href="/oauth2/redirect">Login via NetrunnerDB</a> to register for this tournament.
                </div>
            @endif
        </div>
    @elseif ($user_entry && !$user_entry->rank)
        <div class="text-xs-center">
            <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
        </div>
    @endif
</div>