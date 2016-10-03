@extends('layout.general')

@section('content')
    <h4 class="page-header p-b-1">
        <div class="pull-right">
            <a href="/tournaments/create" class="btn btn-primary">Create Tournament</a>
        </div>
        Organize
    </h4>
    @include('partials.message')
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
            @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                    'action_edit', 'action_delete' ],
                'title' => 'Tournaments created by me', 'id' => 'created', 'icon' => 'fa-list-alt'])
            </div>
        </div>
    </div>
    <script type="text/javascript">
        getTournamentData("creator={{ $user }}", function(data) {
            updateTournamentTable('#created', ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                'action_edit', 'action_delete'], 'no tournaments to show', '{{ csrf_token() }}', data);
        });
    </script>
@stop

