@extends('layout.general')

@section('content')
    @if ($user && ($user->admin || $user->id == $tournament->creator))
        @include('tournaments.modals.conclude')
        @include('tournaments.modals.transfer')
    @endif
    {{--Header--}}
    <h4 class="page-header">
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="pull-right" id="control-buttons">
                    {{--Edit--}}
                    <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary" id="edit-button"><i class="fa fa-pencil" aria-hidden="true"></i> Update</a>
                    {{--Transfer--}}
                    <button class="btn btn-primary" data-toggle="modal" data-hide-manual="true"
                            data-target="#transferModal" id="button-transfer">
                        <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Transfer
                    </button>
                    {{--Approval --}}
                    @if ($user && $user->admin)
                        @if ($tournament->approved !== "1")
                            <a href="/tournaments/{{ $tournament->id }}/approve" class="btn btn-success" id="approve-button"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</a>
                        @endif
                        @if ($tournament->approved !== "0")
                            <a href="/tournaments/{{ $tournament->id }}/reject" class="btn btn-danger" id="reject-button"><i class="fa fa-thumbs-down" aria-hidden="true"></i> Reject</a>
                        @endif
                    @endif
                    {{--Delete--}}
                    @if (is_null($tournament->deleted_at))
                        {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id", 'class' => 'inline-block']) !!}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger', 'id' => 'delete-button')) !!}
                        {!! Form::close() !!}
                    {{--Restore--}}
                    @elseif ($user->admin)
                        <a href="/tournaments/{{ $tournament->id }}/restore" class="btn btn-primary" id="restore-button"><i class="fa fa-recycle" aria-hidden="true"></i> Restore</a>
                    @endif
            </div>
        @endif
        <span id="tournament-title">{{ $tournament->title }}</span><br/>
        <small>
            <span id="tournament-type">{{ $type }}</span> -
            <em>
                created by
                <span id="tournament-creator">
                    <a href="/profile/{{ $tournament->user->id }}">{{ $tournament->user->displayUsername() }}</a>
                </span>
            </em>
            {{--Charity--}}
            @if ($tournament->charity)
                -
                <i title="charity" class="fa fa-heart text-danger"></i>
                charity event
            @endif
            @if ($tournament->incomplete)
                <div class="alert alert-danger view-indicator" id="viewing-as-admin">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    This is tournament is incomplete. Please UPDATE and fill out missing fields.
                </div>
            @endif
            @if ($tournament->deleted_at)
                <div class="alert alert-danger view-indicator" id="viewing-as-admin">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    This is a deleted tournament. Admins can restore it.
                </div>
            @endif
            @if ($user && $user->admin)
                <div class="alert alert-success view-indicator" id="viewing-as-admin">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    viewing as admin
                </div>
            @elseif ($user && $user->id == $tournament->creator)
                <div class="alert alert-success view-indicator" id="viewing-as-creator">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    viewing as creator
                </div>
            @endif
        </small>
    </h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--Tournament info--}}
            <div class="bracket">
                {{--Approval--}}
                @if ($tournament->approved === null)
                    <div class="alert alert-warning" id="approval-needed">
                        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                        This tournament hasn't been approved by the admins yet.
                        You can already share it, though it's not appearing in any tournament lists.
                    </div>
                @elseif ($tournament->approved == 0)
                    <div class="alert alert-danger" id="approval-rejected">
                        <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                        This tournament has been rejected by an admin.
                        Only the tournament creator and the admins can see this tournament.
                        Please try to fix the issue.
                    </div>
                @endif
                {{--Location, date--}}
                <h5>
                    @unless($tournament->tournament_type_id == 7)
                        <span id="tournament-location">
                            {{ $tournament->location_country }}, {{$tournament->location_country === 'United States' ? $tournament->location_state.', ' : ''}}{{ $tournament->location_city }}
                        </span>
                        <br/>
                    @endunless
                    <span id="tournament-date">
                        @if ($tournament->date)
                            {{ $tournament->date }}
                        @else
                            <br/>
                            <em>recurring: {{ $tournament->recurDay() }}</em>
                        @endif
                    </span>
                </h5>
                {{--Details--}}
                @if ($tournament->link_facebook)
                    <p><strong><a href="{{ $tournament->link_facebook }}" rel="nofollow">
                                Facebook {{ strpos($tournament->link_facebook, 'event') ? 'event' : 'group' }}
                    </a></strong></p>
                @endif
                @if ($tournament->date)
                    <p><strong>Legal cardpool up to:</strong> <span id="cardpool"><em>{{ $tournament->cardpool->name }}</em></span></p>
                @endif
                @if($tournament->decklist == 1)
                    <p><strong><u><span id="decklist-mandatory">decklist is mandatory!</span></u></strong></p>
                @endif
                <p>
                    @unless($tournament->start_time === '')
                        <strong>Starting time</strong>: <span id="start-time">{{ $tournament->start_time }}</span> (local time)<br/>
                    @endunless
                    @unless($tournament->location_store === '')
                        <strong>Store/venue</strong>: <span id="store">{{ $tournament->location_store }}</span><br/>
                    @endunless
                    @unless($tournament->location_address === '')
                        <strong>Address</strong>: <span id="address">{{ $tournament->location_address }}</span><br/>
                    @endunless
                    @unless($tournament->contact === '')
                        <strong>Contact</strong>: <span id="contact">{{ $tournament->contact }}</span><br/>
                    @endunless
                </p>
                {{--Google map--}}
                @if($tournament->tournament_type_id != 7)
                    <div class="map-wrapper-small">
                        <div id="map"></div>
                    </div>
                @endif
            </div>
            {{--Statistics--}}
            @if ($tournament->concluded)
            <div class="bracket">
                <h5>
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    Statistics
                </h5>
                <div class="loader-chart stat-load">loading</div>
                <div id="stat-chart-runner"></div>
                <div class="text-xs-center small-text p-b-1">runner IDs</div>
                <div class="loader-chart stat-load">loading</div>
                <div id="stat-chart-corp"></div>
                <div class="text-xs-center small-text">corp IDs</div>
            </div>
            @endif
        </div>
        {{--Standings and claims--}}
        <div class="col-md-8 col-xs-12">
            {{--Tournament description--}}
            @include('errors.list')
            @unless($tournament->description === '')
                <div class="bracket">
                    {{--<h5>Description with markdown</h5>--}}
                    @if (strlen($tournament->description) > 1000)
                        <div class="more-container collapse" id="more-collapse">
                            <div class="more-overlay" id="more-overlay"></div>
                            <a name="more-top"/>
                            <div id="tournament-description" class="markdown-content">
                                {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->description)) !!}
                            </div>
                        </div>
                        <div id="more-more">
                            <i class="fa fa-caret-right" aria-hidden="true"></i>
                            <a data-toggle="collapse" href="#more-collapse" aria-expanded="false" aria-controls="more-collapse">
                                more...
                            </a>
                        </div>
                        <div id="more-less" class="hidden-xs-up">
                            <i class="fa fa-caret-up" aria-hidden="true"></i>
                            <a data-toggle="collapse" href="#more-collapse" aria-expanded="false" aria-controls="more-collapse">
                                less...
                            </a>
                        </div>
                        <script type="text/javascript">
                            $('#more-collapse').on('shown.bs.collapse', function () {
                                $('#more-collapse').css({
                                    'max-height': 'none'
                                });
                                $('#more-overlay').addClass('hidden-xs-up');
                                $('#more-more').addClass('hidden-xs-up');
                                $('#more-less').removeClass('hidden-xs-up');
                            });
                            $('#more-collapse').on('hidden.bs.collapse', function () {
                                $('#more-collapse').css({
                                    'max-height': '400px'
                                });
                                $('#more-overlay').removeClass('hidden-xs-up');
                                $('#more-more').removeClass('hidden-xs-up');
                                $('#more-less').addClass('hidden-xs-up');
                                location.hash = "#more-top"; location.hash = "#";
                            })
                        </script>
                    @else
                        <div id="tournament-description" class="markdown-content">
                            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->description)) !!}
                        </div>
                    @endif
                </div>
            @endunless
            {{--Matches--}}
            @if (file_exists('tjsons/'.$tournament->id.'.json'))
                <div class="bracket">
                    <h5>
                        <i class="fa fa-handshake-o" aria-hidden="true"></i>
                        Matches
                        <div class="pull-right">
                            <button class="btn btn-primary btn-xs disabled" id="button-showmatches" disabled onclick="displayMatches({{ $tournament->id }})">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                                show
                            </button>
                        </div>
                    </h5>
                    <div id="content-matches" class="hidden-xs-up">
                        <div id="loader-content" class="hidden-xs-up loader">loading</div>
                        {{--Top cut--}}
                        <h6 class="hidden-xs-up" id="header-top">
                            Top-cut
                        </h6>
                        {{--Missing top--}}
                        <div class="alert alert-warning view-indicator hidden-xs-up" id="warning-matches-top">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            Elimination match data is missing.
                        </div>
                        <div id="tree-top"></div>
                        {{--double elimination tree iframe, to avoid nasty CSS clashes--}}
                        <iframe src="/elimination" id="iframe-tree"></iframe>
                        <table id="table-matches-top" class="table-match hidden-xs-up m-b-2">
                        </table>
                        {{--Swiss rounds--}}
                        <h6>Swiss rounds</h6>
                        {{--Missing swiss--}}
                        <div class="alert alert-warning view-indicator hidden-xs-up" id="warning-matches-swiss">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            Swiss match data is missing.
                        </div>
                        <table id="table-matches-swiss" class="table-match">
                        </table>
                    </div>
                </div>
            @endif
            {{--Videos--}}
            @if ($tournament->concluded)
            <div class="bracket">
                <h5>
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos
                </h5>
                {{--Add video--}}
                <button class="btn btn-primary btn-xs" id="button-add-videos"
                        onclick="toggleVideoAdd(true)">
                    <i class="fa fa-video-camera" aria-hidden="true"></i> Add videos
                </button>
                <button class="btn btn-primary btn-xs hidden-xs-up" id="button-done-videos"
                        onclick="toggleVideoAdd(false)">
                    <i class="fa fa-check" aria-hidden="true"></i> Done
                </button>
                <div id="section-add-videos" class="hidden-xs-up">
                    <hr/>
                    <div class="p-b-1">
                        Add a Youtube video
                    </div>
                    {!! Form::open(['method' => 'POST', 'url' => "/videos",
                        'class' => 'form-inline']) !!}
                        {!! Form::hidden('tournament_id', $tournament->id) !!}
                        <div class="form-group">
                            {!! Form::label('video_id', 'Youtube Video ID or URL', ['class' => 'small-text']) !!}
                            {!! Form::text('video_id', '', ['class' => 'form-control']) !!}
                        </div><br/>
                        {!! Form::button('Add video', array('type' => 'submit',
                            'class' => 'btn btn-success btn-xs', 'id' => 'button-add-video')) !!}
                    {!! Form::close() !!}
                </div>
                <hr/>
                {{--List of videos--}}
                @if (count($tournament->videos) > 0)
                  @include('tournaments.partials.videos',
                      ['videos' => $tournament->videos, 'creator' => $tournament->creator, 'id' => 'videos'])
                @else
                    <p><em id="no-videos">no videos yet</em></p>
                @endif
                <div id="section-watch-video" class="hidden-xs-up">
                    <hr/>
                    <p>
                        <button class="btn btn-danger btn-xs" onclick="watchVideo(false)">
                            <i class="fa fa-window-close" aria-hidden="true"></i> Close
                        </button>
                    </p>
                    <div id="section-video-player"></div>
                </div>
            </div>
            @endif
            {{--Results--}}
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
        </div>
    </div>
    {{--Statistics chart--}}
    @if ($tournament->concluded)
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            var chartData, playernum = parseInt('{{ $tournament->players_number }}');
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                $.ajax({
                    url: "/api/entries?id={{ $tournament->id }}",
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        $('.stat-load').addClass('hidden-xs-up');
                        drawEntryStats(data, 'runner', 'stat-chart-runner', playernum);
                        drawEntryStats(data, 'corp', 'stat-chart-corp', playernum);
                        chartData = data;
                        $('#button-showmatches').removeClass('disabled').prop("disabled", false);
                    }
                });
            }

            // redraw charts on window resize
            $(window).resize(function(){
                drawEntryStats(chartData, 'runner', 'stat-chart-runner', playernum);
                drawEntryStats(chartData, 'corp', 'stat-chart-corp', playernum);
            });

            @if (session('editmode'))
                // manual importing
                toggleEntriesEdit(true);
                window.location.hash = '#importing';
            @endif

        </script>
    @endif
    {{--Google maps library--}}
    @if($tournament->tournament_type_id != 7)
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&libraries=places&callback=initializeMap">
        </script>
        {{--Scripts for google maps--}}
        <script type="text/javascript">
            var map, marker;

            function initializeMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 1,
                    center: {lat: 40.157053, lng: 19.329297},
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    streetViewControl: false,
                    mapTypeControl: false
                });

                marker = new google.maps.Marker({
                    map: map,
                    anchorPoint: new google.maps.Point(0, -29)
                });

                var service = new google.maps.places.PlacesService(map);
                service.getDetails({placeId: '{{ $tournament->location_place_id }}'}, function(place, status){
                    if (status === google.maps.places.PlacesServiceStatus.OK) {
                        renderPlace(place, marker, map)
                    }
                });
            }
        </script>
    @endif
@stop
