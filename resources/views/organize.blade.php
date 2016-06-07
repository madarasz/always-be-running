@extends('layout.general')

@section('content')
    <h3 class="page-header">Organize</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <div class="bracket text-center">
                <a href="/tournaments/create" class="btn btn-primary margin-tb">Create Tournament</a>
            </div>
            <div class="bracket">
                <h4>Tournament calendar</h4>
            </div>
        </div>
        <div class="col-md-9 col-xs-12">
            <div class="bracket">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_delete' ],
                'data' => $created, 'title' => 'Tournaments created by me',
                'empty_message' => 'no tournaments created yet', 'id' => 'created'])
            </div>
            <div class="bracket">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'claim', 'conclusion', 'players', 'decks',
                    'action_view'  ],
                'data' => $registered, 'title' => 'Tournaments I registered to',
                'empty_message' => 'no tournaments registered to yet', 'id' => 'registered'])
            </div>
        </div>
    </div>
@stop

