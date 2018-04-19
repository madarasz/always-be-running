@extends('layout.general')

@section('content')
    {{--Header, main buttons--}}
    <h4 class="page-header p-b-1 m-b-0">
        <div class="pull-right">
            <a href="/tournaments/create" class="btn btn-primary">Create Tournament</a>
            <button class="btn btn-info" data-toggle="modal" data-target="#fbImportModal">
                Create from <img src="https://en.facebookbrand.com/wp-content/uploads/2016/05/FB-fLogo-Blue-broadcast-2.png" style="height: 1em;"/> event
            </button>
            <button class="btn btn-conclude" data-toggle="modal" data-target="#concludeModal" data-hide-manual="true"
                    data-tournament-id="-1" data-subtitle="use this for new concluded tournaments">Create from Results</button>
        </div>
        Organize
    </h4>
    {{--Conclude modal--}}
    @include('tournaments.modals.conclude')
    {{--FB import modal--}}
    @include('tournaments.modals.fb-import')

    @include('partials.message')
    @include('errors.list')

    {{--Tabs--}}
    <div class="modal-tabs">
        <ul id="admin-tabs" class="nav nav-tabs" role="tablist">
            <li class="nav-item notif-red notif-badge" id="tabf-tournament">
                <a class="nav-link active" data-toggle="tab" href="#tab-tournaments" role="tab">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments
                </a>
            </li>
            <li class="nav-item notif-red notif-badge" id="tabf-group">
                <a class="nav-link" data-toggle="tab" href="#tab-groups" role="tab">
                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                    Tournament Groups
                </a>
            </li>
        </ul>
    </div>

    {{--Tab pages--}}
    <div class="tab-content">
        <div class="tab-pane active" id="tab-tournaments" role="tabpanel">
            @include('organize.tournaments')
        </div>
        <div class="tab-pane" id="tab-groups" role="tabpanel">
            @include('organize.groups')
        </div>
    </div>


    <script type="text/javascript">
        // Script to trigger tournament load
        getTournamentData("?creator={{ $user }}&desc=1", function(data) {
            updateTournamentTable('#created', ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                'action_edit', 'action_delete'], 'no tournaments to show', '{{ csrf_token() }}', data);
            getTournamentData("?creator={{ $user }}&incomplete=1&desc=1", function(data) {
                updateTournamentTable('#incomplete', ['title', 'date', 'location', 'cardpool', 'players',
                    'created_at', 'action_edit', 'action_purge'], 'no incomplete items', '{{ csrf_token() }}', data);
                if (data.length) {
                    $('#bracket-incomplete').removeClass('hidden-xs-up');
                }
            });
        });
    </script>
@stop

