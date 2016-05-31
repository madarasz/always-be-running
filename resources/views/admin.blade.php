@extends('layout.general')

@section('content')
    <h3 class="page-header">Administration</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-xs-12">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_approve', 'action_reject', 'action_delete' ],
                'data' => $to_approve, 'title' => 'Pending/Rejected tournaments',
                'empty_message' => 'no pending tournaments', 'id' => 'pending'])
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_view', 'action_edit', 'action_restore'],
                'data' => $deleted, 'title' => 'Deleted tournaments',
                'empty_message' => 'no deleted tournaments', 'id' => 'deleted'])
            <hr/>
            <h4>Card data</h4>
            <a href="/admin/identities/update" class="btn-primary btn">Update Identities</a> Identity count: {{ $count_ids }} (last: <em>{{ $last_id }}</em>)<br/>
            <a href="/admin/packs/update" class="btn-primary btn">Update Card packs</a> Card pack count: {{ $count_packs }} (last: <em>{{ $last_pack }}</em>)<br/>
            <a href="/admin/cycles/update" class="btn-primary btn">Update Card cycles</a> Card cycle count: {{ $count_cycles }} (last: <em>{{ $last_cycle }}</em>)
            <hr/>
            <h4>Cardpool usage</h4>
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
@stop

