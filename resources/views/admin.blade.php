@extends('layout.general')

@section('content')
    <h4 class="page-header m-b-0">Administration</h4>
    @include('partials.message')
    @include('errors.list')

    {{--Conclude modal--}}
    @include('tournaments.modals.conclude')

    {{--Tabs--}}
    <div class="modal-tabs">
        <ul id="admin-tabs" class="nav nav-tabs" role="tablist">
            <li class="nav-item notif-red notif-badge" id="tabf-tournament">
                <a class="nav-link active" data-toggle="tab" href="#tab-tournaments" role="tab">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-entries" role="tab">
                    <i class="fa fa-list-ol" aria-hidden="true"></i>
                    Entries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-packs" role="tab">
                    <i class="fa fa-cubes" aria-hidden="true"></i>
                    Packs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-badges" role="tab">
                    <i class="fa fa-child" aria-hidden="true"></i>
                    Badges
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-photos" role="tab">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    Photos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-videos" role="tab">
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-stats" role="tab">
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    Stats
                </a>
            </li>
        </ul>
    </div>

    {{--Tab panes--}}
    <div class="tab-content">
        {{--Tournaments--}}
        <div class="tab-pane active" id="tab-tournaments" role="tabpanel">
            {{--Notification for approve--}}
            <div class="alert alert-warning view-indicator hidden-xs-up" id="notif-tournament">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                You have tournaments waiting for approval or having conflicts.
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        {{--Pending--}}
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                                'action_edit', 'action_approve', 'action_reject', 'action_delete'],
                            'title' => 'Pending tournaments', 'id' => 'pending', 'icon' => 'fa-question-circle-o', 'loader' => true])
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                                'action_edit', 'action_approve', 'action_delete'],
                            'title' => 'Rejected tournaments', 'id' => 'rejected', 'icon' => 'fa-thumbs-down', 'loader' => true])
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        {{--Conflict--}}
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'type', 'creator', 'approval', 'players', 'claims', 'action_delete'],
                            'title' => 'Conflicts',
                            'id' => 'conflict', 'icon' => 'fa-exclamation-triangle', 'loader' => true])
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        {{--Late conclusion--}}
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'creator', 'conclusion', 'regs', 'action_delete'],
                            'title' => 'Tournaments to be concluded', 'subtitle' => 'creators should conclude these',
                            'id' => 'late', 'icon' => 'fa-clock-o', 'loader' => true])
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        {{--Deleted--}}
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'creator', 'approval', 'conclusion', 'players', 'decks',
                                'action_edit', 'action_restore', 'action_purge'],
                            'title' => 'Deleted tournaments', 'subtitle' => 'only creator and Necro can hard delete',
                            'id' => 'deleted', 'icon' => 'fa-times-circle-o', 'loader' => true])
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        @include('tournaments.partials.tabledin',
                            ['columns' => ['title', 'date', 'location', 'cardpool', 'creator', 'players',
                                'created_at', 'action_edit', 'action_purge' ],
                            'title' => 'Incomplete imports',
                            'id' => 'incomplete', 'icon' => 'fa-exclamation-triangle', 'loader' => true])
                    </div>
                </div>
            </div>
        </div>
        {{--Entries--}}
        <div class="tab-pane" id="tab-entries" role="tabpanel">
            <div class="row">
                <div class="col-xs-12">
                    {{--Entry types--}}
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-list-ol" aria-hidden="true"></i>
                            Entry types
                        </h5>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <table class="table table-sm table-striped abr-table">
                            <thead>
                                <tr>
                                    <th>type</th>
                                    <th>number of entries</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entry_types as $type => $count)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                            </div>
                            <div class="col-xs-12 col-md-6" id="chart-entry-types">
                            </div>
                        </div>
                    </div>
                    {{--Decks--}}
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-id-card-o" aria-hidden="true"></i>
                            Decks
                        </h5>
                        <p>
                            Total number of decks: {{ $published_count + $private_count }}
                        </p>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                Published decks: {{ $published_count }}<br/>
                                Private decks: {{ $private_count }}<br/>
                                Broken deck links: {{ $broken_count }} - users:
                                <?php $bcount = count($broken_users) ?>
                                @foreach($broken_users as $key=>$buser)
                                    <a href="/profile/{{ $buser->id }}">{{ $buser->displayUsername() }}</a>{{ $key != $bcount-1 ? ',' : ''}}
                                @endforeach
                                <br/>
                                @if (Auth::user() && Auth::user()->id == 1276)
                                    <a href="/admin/decks/broken" class="btn btn-primary">Detect broken</a>
                                @endif
                            </div>
                            <div class="col-md-6 col-xs-12">
                                With backlink to NetrunnerDB: {{ $backlink_count }}<br/>
                                Without backlink to NetrunnerDB: {{ $no_backlink_count }}<br/>
                                Unexported: {{ $unexported_count }}
                            </div>
                        </div>
                    </div>
                    {{--KTM update--}}
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                            Know the Meta update
                        </h5>
                        <p>
                            Last update: {{ $ktm_update }}<br/>
                            New entries since:
                            <ul>
                                @foreach($ktm_packs as $key=>$pack)
                                    <li>{{$key}}: {{$pack[0]}} entries ({{$pack[1]}} claims)</li>
                                @endforeach
                            </ul>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        {{--Packs--}}
        <div class="tab-pane" id="tab-packs" role="tabpanel">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="bracket">
                        <h5>Cardpool usage</h5>
                        <ul>
                            @for($i = 0; $i < count ($cycles); $i++)
                                <li>{{ $cycles[$i]->name }}</li>
                                <ul>
                                    @foreach ($packs[$i] as $pack)
                                        <li>
                                            {{ $pack->name }}
                                            @if ($pack->usable)
                                                <a href="{{ "/packs/$pack->id/disable" }}" class="btn-danger btn btn-xs">disable</a>
                                            @else
                                                <a href="{{ "/packs/$pack->id/enable" }}" class="btn-success btn btn-xs">enable</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endfor
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="bracket">
                        <h5>Card data</h5>
                        <a href="/admin/cycles/update" class="btn-primary btn btn-sm">Update Card cycles</a> Card cycle count: {{ $count_cycles }} (last: <em>{{ $last_cycle }}</em>)<br/>
                        <a href="/admin/packs/update" class="btn-primary btn btn-sm">Update Card packs</a> Card pack count: {{ $count_packs }} (last: <em>{{ $last_pack }}</em>)<br/>
                        <a href="/admin/identities/update" class="btn-primary btn btn-sm">Update Identities</a> Identity count: {{ $count_ids }} (last: <em>{{ $last_id }}</em>)
                    </div>
                </div>
            </div>
        </div>
        {{--Badges--}}
        <div class="tab-pane" id="tab-badges" role="tabpanel">
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        Badge types: {{ $badge_type_count }}<br/>
                        Badges: {{ $badge_count }}<br/>
                        Unseen badges: {{ $unseen_badge_count }}<br/>
                        <a href="/admin/badges/refresh" class="btn btn-primary">Refresh badges</a>
                    </div>
                </div>
            </div>
        </div>
        {{--Photos--}}
        <div class="tab-pane" id="tab-photos" role="tabpanel">
            <div class="row">
                {{--Pending photos--}}
                <div class="col-xs-12">
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                            Pending photos
                        </h5>
                        @include('admin.gallery', ['photos' => $photos, 'approval' => null])
{{--                        @if(count($photos->whereNull('approved')))--}}
                        {{--@elseif--}}
                            {{--<span class="user-counts">no photos to approve</span>--}}
                        {{--@endif--}}
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="bracket">
                        <div class="row">
                            {{--photos by channel--}}
                            <div class="col-xs-12 col-md-6">
                                <table class="table table-sm table-striped abr-table" id="videos">
                                    <thead>
                                    <tr>
                                        <th>tournament name</th>
                                        <th>number of photos</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($photo_tournaments as $tournament_id => $count)
                                        <?php $vtournament = App\Tournament::findOrFail($tournament_id); ?>
                                        <tr>
                                            <td>
                                                <a href="{{ $vtournament->seoUrl() }}">
                                                    {{ $vtournament->title }}
                                                </a>
                                            </td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{--photos by user--}}
                            <div class="col-xs-12 col-md-6">
                                <table class="table table-sm table-striped abr-table" id="videos">
                                    <thead>
                                    <tr>
                                        <th>user</th>
                                        <th>number of photos</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($photo_users as $userid => $count)
                                        <?php $vuser = App\User::findOrFail($userid); ?>
                                        <tr>
                                            <td>
                                                <a href="/profile/{{ $vuser->id }}" {{ $vuser->supporter ? 'class=supporter' : '' }}>
                                                    {{ $vuser->name }}
                                                </a>
                                            </td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{--Approved photos--}}
                <div class="col-xs-12">
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                            Approved photos
                        </h5>
                        @include('admin.gallery', ['photos' => $photos, 'approval' => true])
                    </div>
                </div>
            </div>
        </div>
        {{--Videos--}}
        <div class="tab-pane" id="tab-videos" role="tabpanel">
            <div class="row">
                <div class="col-xs-12">
                    <div class="bracket">
                        <div class="row">
                            {{--videos by channel--}}
                            <div class="col-xs-12 col-md-6">
                                <table class="table table-sm table-striped abr-table" id="videos">
                                    <thead>
                                        <tr>
                                            <th>channel name</th>
                                            <th>number of videos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($video_channels as $name => $count)
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{--videos by user--}}
                            <div class="col-xs-12 col-md-6">
                                <table class="table table-sm table-striped abr-table" id="videos">
                                    <thead>
                                    <tr>
                                        <th>user</th>
                                        <th>number of videos</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($video_users as $userid => $count)
                                        <?php $vuser = App\User::findOrFail($userid); ?>
                                        <tr>
                                            <td>
                                                <a href="/profile/{{ $vuser->id }}" {{ $vuser->supporter ? 'class=supporter' : '' }}>
                                                    {{ $vuser->name }}
                                                </a>
                                            </td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--Stats--}}
        <div class="tab-pane" id="tab-stats" role="tabpanel">
            <div id="chart1"></div>
            <div class="legal-bullshit">Reminder: the last week in graph is not a whole week.</div>
            <div class="">
                <strong>Total number of users:</strong> <span id="stat-total-users"></span><br/>
                <strong>Total number of approved tournaments:</strong> <span id="stat-total-tournaments"></span><br/>
                <strong>Total number of claims:</strong> <span id="stat-total-entries"></span>
            </div>
            <div id="chart2" class="p-t-2"></div>
        </div>
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        // activate tabs
        $('#admin-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // charts
        var entryTypes = [['type', 'count'],
            @foreach($entry_types as $type => $count)
                ['{{$type}}', {{$count}}],
            @endforeach
        ];
        google.charts.load('current', {
            'packages':['corechart', 'geochart'],
            'mapsApiKey': '{{ env('GOOGLE_MAPS_API') }}'
        });

        // get tournament data
        getTournamentData("approved=null", function(data) {
            updateTournamentTable('#pending', ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                'action_edit', 'action_approve', 'action_reject', 'action_delete'], 'no pending tournaments', '{{ csrf_token() }}', data);
            getTournamentData("approved=0", function(data) {
                updateTournamentTable('#rejected', ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_edit', 'action_approve', 'action_delete'], 'no rejected tournaments', '{{ csrf_token() }}', data);
                getTournamentData("conflict=1", function(data) {
                    updateTournamentTable('#conflict', ['title', 'date', 'type', 'creator', 'approval', 'players', 'claims', 'action_delete'],
                            'no tournaments with conflicts', '{{ csrf_token() }}', data);
                    getTournamentData("approved=1&concluded=0&recur=0&end={{ $nowdate }}", function(data) {
                        updateTournamentTable('#late', ['title', 'date', 'location', 'creator', 'conclusion', 'players', 'action_delete'],
                                'no late tournaments', '{{ csrf_token() }}', data);
                        getTournamentData("deleted=1", function(data) {
                            updateTournamentTable('#deleted', ['title', 'date', 'creator', 'approval', 'conclusion', 'players', 'decks',
                                'action_edit', 'action_restore', 'action_purge'], 'no deleted tournaments', '{{ csrf_token() }}', data);
                            getTournamentData("incomplete=1", function(data) {
                                updateTournamentTable('#incomplete', ['title', 'date', 'location', 'cardpool', 'creator', 'players',
                                    'created_at', 'action_edit', 'action_purge'], 'no incomplete items', '{{ csrf_token() }}', data);
                                drawAdminChart(entryTypes);
                            });
                        });
                    });
                });
            });
        });

        // enable gallery
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({alwaysShowClose: true});
        });
    </script>
@stop

