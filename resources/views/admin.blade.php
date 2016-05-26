@extends('layout.general')

@section('content')
    <h3 class="page-header">Tournament administration</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-xs-12">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_approve', 'action_reject', 'action_delete' ],
                'data' => $to_approve, 'title' => 'Pending tournaments',
                'empty_message' => 'no pending tournaments', 'id' => 'pending'])
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_restore'],
                'data' => $deleted, 'title' => 'Deleted tournaments',
                'empty_message' => 'no deleted tournaments', 'id' => 'deleted'])
            <hr/>
            Identity count: {{ $count_ids }} (last: <em>{{ $last_id }}</em>) <a href="/admin/identities/update" class="btn-primary btn">Update Identities</a>
        </div>
    </div>
@stop

