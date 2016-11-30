@extends('layout.general')

@section('content')
    {{--Header, main buttons--}}
    <h4 class="page-header p-b-1">
        <div class="pull-right">
            <a href="/tournaments/create" class="btn btn-primary">Create Tournament</a>
            <button class="btn btn-conclude" data-toggle="modal" data-target="#concludeModal" data-hide-manual="true"
                    data-tournament-id="-1" data-subtitle="use this for new concluded tournaments">Create via Import</button>
        </div>
        Organize
    </h4>
    {{--Conclude modal--}}
    @include('tournaments.modals.conclude')

    @include('partials.message')
    @include('errors.list')

    {{--Notifications for conclude, unknown cardpool incomplete--}}
    <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-conclude" data-badge="">
        <i class="fa fa-clock-o" aria-hidden="true"></i>
        You have tournaments waiting for conclusion.
    </div>
    <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-cardpool" data-badge="">
        <i class="fa fa-clock-o" aria-hidden="true"></i>
        It's time to set the cardpool for some of your tournaments.
    </div>
    <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-incomplete" data-badge="">
        <i class="fa fa-clock-o" aria-hidden="true"></i>
        You have incomplete imports. Please update or delete.
    </div>

    {{--Table for tournaments created by me--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
            @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                    'action_edit', 'action_delete' ], 'doublerow' => true,
                'title' => 'Tournaments created by me', 'id' => 'created', 'icon' => 'fa-list-alt', 'loader' => true])
            </div>
            <div class="bracket hidden-xs-up" id="bracket-incomplete">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'players',
                        'created_at', 'action_edit', 'action_purge' ], 'doublerow' => true,
                    'title' => 'Incomplete imports', 'subtitle' => 'please update or delete',
                    'id' => 'incomplete', 'icon' => 'fa-exclamation-triangle'])
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Script to trigger tournament load
        getTournamentData("creator={{ $user }}", function(data) {
            updateTournamentTable('#created', ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                'action_edit', 'action_delete'], 'no tournaments to show', '{{ csrf_token() }}', data);
            getTournamentData("creator={{ $user }}&incomplete=1", function(data) {
                updateTournamentTable('#incomplete', ['title', 'date', 'location', 'cardpool', 'players',
                    'created_at', 'action_edit', 'action_purge'], 'no incomplete items', '{{ csrf_token() }}', data);
                if (data.length) {
                    $('#bracket-incomplete').removeClass('hidden-xs-up');
                }
            });
        });
    </script>
@stop

