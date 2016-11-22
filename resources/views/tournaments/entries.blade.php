@extends('layout.general')

@section('content')
    {{--Header--}}
    <h4 class="page-header">
        <span id="tournament-title">{{ $tournament->title }}</span><br/>
        <small>
            <span id="tournament-type">{{ $tournament->tournament_type->type_name }}</span> -
            <em>
                created by
                <span id="tournament-creator">
                    <a href="/profile/{{ $tournament->user->id }}">{{ $tournament->user->displayUsername() }}</a>
                </span>
            </em>
            {{--Charity--}}
            @if ($tournament->charity)
                -
                <i title="charity" class="fa fa-heart text-danger"></i>
                charity event
            @endif
        </small>
    </h4>
    @include('partials.message')
    <div class="row">
        <div class="col-xs-12">
            {{--Tournament info--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                    Mass edit tournament entries
                </h5>
                <table class="table table-sm table-striped abr-table">
                    <thead>
                        <tr class="text-xs-center">
                            @if ($tournament->top_number)
                                <td>top-cut</td>
                            @endif
                            <td>swiss</td>
                            <td>user</td>
                            <td>imported name</td>
                            <td>runner ID</td>
                            <td>corp ID</td>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($tournament->entries()->orderBy(DB::raw('-rank_top'), 'asc')->orderBy('rank')->get() as $entry)
                        <tr>
                            @if ($tournament->top_number)
                                <td class="text-xs-center">
                                    #{{ $entry->rank_top ? $entry->rank_top : '-' }}
                                </td>
                            @endif
                            <td class="text-xs-center">#{{ $entry->rank }}</td>
                            <td>
                                @if ($entry->user)
                                    <a href="/profile/{{ $entry->user }}">{{ $entry->player->name }}</a>
                                @endif
                            </td>
                            <td>{{ $entry->user ? '' : $entry->import_username }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

