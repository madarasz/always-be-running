@extends('layout.general')

@section('content')
    {{--Header, main buttons--}}
    <h4 class="page-header p-b-1">
        <div class="pull-right">
            <a href="/tournaments/create" class="btn btn-primary">Create Tournament</a>
        </div>
        Organize
    </h4>
    {{--Conclude modal--}}
    @include('tournaments.modals.conclude')

    @include('partials.message')
    @include('errors.list')

    {{--Table for tournaments created by me--}}
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
        // Script to trigger tournament load
        getTournamentData("creator={{ $user }}", function(data) {
            updateTournamentTable('#created', ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                'action_edit', 'action_delete'], 'no tournaments to show', '{{ csrf_token() }}', data);
        });
    </script>
@stop

