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
                Claims or conflicts can be removed by the tournament creator, admins or claim owners.
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
        {{--Concluded by--}}
        @if ($tournament->concluded_by || $tournament->concluded_at)
            <div id="concluded-by" class="small-text m-t-1" style="line-height: 2">
                <strong>concluded by:</strong>
                @if ($tournament->concluded_by)
                    <a href="/profile/{{ $tournament->concluded_by }}" class="{{ $tournament->concluder->linkClass() }}">{{ $tournament->concluder->displayUsername() }}</a>
                @else
                    <em>NRTM user</em>
                @endif
                @include('partials.popover', ['direction' => 'top', 'content' =>
                        'If the results / player number / top-cut is incorrect, ask the tournament creator or admins to
                         edit it.'])
                {{--revert conclusion button--}}
                @if ($user && ($user->admin || $user->id == $tournament->creator || $user->id == $tournament->concluded_by))
                    {!! Form::open(['method' => 'POST', 'url' => "/tournaments/$tournament->id/conclude/revert", 'style' => 'display: inline']) !!}
                        {!! Form::button('<i class="fa fa-undo" aria-hidden="true"></i> Revert conclusion',
                            array('type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'id' => 'button-revert',
                            'onclick' => "return confirm('Are you sure you want to reset this tournament to an unconcluded state? All claims and imported entries are kept and will be displayed after this tournament is concluded again.')")) !!}
                    {!! Form::close() !!}
                @endif

                {{--relax/strict mode--}}
                <br/>
                <strong>conflicts:</strong>
                @if ($tournament->relax_conflicts)
                    relaxed
                    @include('partials.popover', ['direction' => 'bottom', 'content' =>
                    'conflicts are hidden if there can be more than one entry on a rank'])
                @else
                    strict
                    @include('partials.popover', ['direction' => 'bottom', 'content' =>
                    'conflicts are displayed if there are more than one entry on a rank'])
                @endif
                {{--relax/strict button--}}
                @if ($user && ($user->admin || $user->id == $tournament->creator || $user->id == $tournament->concluded_by))
                    @if ($tournament->relax_conflicts)
                        {!! Form::open(['method' => 'POST', 'url' => "/tournaments/$tournament->id/relax/0", 'style' => 'display: inline']) !!}
                            {!! Form::button('<i class="fa fa-bell" aria-hidden="true"></i> enforce',
                                array('type' => 'submit', 'class' => 'btn btn-warning btn-xs', 'id' => 'button-revert',
                                'onclick' => "return confirm('Do you want to enforce conflicts?')")) !!}
                        {!! Form::close() !!}
                    @else
                        {!! Form::open(['method' => 'POST', 'url' => "/tournaments/$tournament->id/relax/1", 'style' => 'display: inline; margin-top: 0.5em']) !!}
                        {!! Form::button('<i class="fa fa-bell-slash" aria-hidden="true"></i> relax',
                            array('type' => 'submit', 'class' => 'btn btn-info btn-xs', 'id' => 'button-revert',
                            'onclick' => "return confirm('Do you want to relax conflicts?')")) !!}
                        {!! Form::close() !!}
                    @endif
                @endif

                {{--admin info--}}
                @if ($user && ($user->admin))
                    <br/>
                    <strong>admin/creator info:</strong>
                    timestamp: {{ $tournament->concluded_at }}
                    - via
                    @if ($tournament->import == 1)
                        NRTM import
                    @elseif ($tournament->import == 2)
                        CSV import
                    @elseif ($tournament->import == 3)
                        manual import
                    @elseif ($tournament->import == 4)
                        Cobr.ai
                    @elseif ($tournament->import == 5)
                         Aesop's Tables
                    @else
                        manual conclusion
                    @endif
                @endif
            </div>
        @endif
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
                'creator' => $tournament->creator, 'id' => 'entries-top', 'relax' => $tournament->relax_conflicts])
            <hr/>
        @endif
        <h6>
            Swiss rounds
            @if (file_exists('tjsons/'.$tournament->id.'.json') && $tournament->concluded)
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
            'creator' => $tournament->creator, 'id' => 'entries-swiss', 'relax' => $tournament->relax_conflicts])
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
            The organizer or a player should set it to 'concluded', so players can make claims.
        </div>
        {{--Manage and Conclude Buttons--}}
        <table style="margin:  0 auto;">
            <tbody>
                <tr>
                    <td>
                        {{--Manage in NRTM--}}
                        <a class="btn btn-manage" id="button-manage-nrtm" title="Download results from NRTM app"
                           href="https://steffens.org/nrtm/conclude.html?id={{ $tournament->id }}">
                            <i class="fa fa-cloud-download" aria-hidden="true"></i> NRTM results<br/>
                        </a>
                    </td>
                    <td>
                        {{--Conclude modal, button--}}
                        @if ($user)
                            <button class="btn btn-conclude" data-toggle="modal" data-target="#concludeModal"
                                    data-tournament-id="{{$tournament->id}}"
                                    data-subtitle="{{$tournament->title.' - '.$tournament->date}}" id="button-conclude">
                                <i class="fa fa-check" aria-hidden="true"></i> Conclude
                            </button>

                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="small-text text-xs-center">(iOS app, <a href="/faq#import">help</a>)</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <hr/>
    @endif
    {{--Register--}}
    @include('tournaments.viewer.register')
</div>