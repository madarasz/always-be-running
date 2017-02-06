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