@extends('layout.general')

@section('content')
    <h3 class="page-header">My Tournaments</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-xs-12">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_delete' ],
                'data' => $created, 'title' => 'Tournaments created by me',
                'empty_message' => 'no tournaments created yet', 'id' => 'created'])
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'conclusion', 'players', 'decks',
                    'action_view'  ],
                'data' => $registered, 'title' => 'Tournaments I registered to',
                'empty_message' => 'no tournaments registered to yet', 'id' => 'registered'])
        </div>
    </div>
@stop

