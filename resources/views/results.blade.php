@extends('layout.general')

@section('content')
    <h3 class="page-header">Results</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
        </div>
        <div class="col-md-8 col-xs-12">
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

