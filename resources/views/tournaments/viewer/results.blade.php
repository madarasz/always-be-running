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
        @include('tournaments.viewer.claim')
        <hr/>
        {{--Import NRTM, Clear anonym claims--}}
        @include('tournaments.viewer.manual')
        {{--Tables of tournament standings --}}
        @if ($tournament->top_number)
            <h6>Top cut</h6>
            @include('tournaments.partials.entries',
                ['entries' => $entries_top, 'user_entry' => $user_entry, 'rank' => 'rank_top',
                'creator' => $tournament->creator, 'id' => 'entries-top'])
            <hr/>
        @endif
        <h6>
            Swiss rounds
            @if (file_exists('tjsons/'.$tournament->id.'.json'))
            <div class="pull-right">
                <button class="btn btn-primary btn-xs" id="button-showpoints" onclick="displayScores({{ $tournament->id }})">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                    points
                </button>
            </div>
            @endif
        </h6>
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
    {{--Register--}}
    @include('tournaments.viewer.register')
</div>