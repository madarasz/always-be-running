@extends('layout.general')

@section('content')
    <h3 class="page-header">My Tournaments</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-xs-12">
            <h4>Tournaments created by me</h4>
            <table class="table table-condensed table-striped">
                <thead>
                    <th>title</th>
                    <th>date</th>
                    <th>approval</th>
                    <th>conclusion</th>
                    <th class="text-center">players</th>
                    <th class="text-center">decks</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody>
                    @if (count($created) == 0)
                        <tr><td colspan="9" class="text-center"><em>no tournaments created yet</em></td></tr>
                    @endif
                    @foreach ($created as $tournament)
                        <tr>
                            <td>{{ $tournament->title }}</td>
                            <td>{{ $tournament->date }}</td>
                            <td>
                                @if ($tournament->approved == '')
                                    <span class="label label-warning">pending</span>
                                @elseif ($tournament->approved == 1)
                                    <span class="label label-success">approved</span>
                                @else
                                    <span class="label label-danger">rejected</span>
                                @endif
                            </td>
                            <td>
                                @if ($tournament->concluded == 1)
                                    <span class="label label-success">concluded</span>
                                @elseif ($tournament->date <= $nowdate)
                                    <span class="label label-danger">due, pls update</span>
                                @else
                                    <span class="label label-info">not yet</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($tournament->concluded == 1)
                                   {{ $tournament->players_number }}
                                @endif
                            </td>
                            <td></td>
                            <td><a href="/tournaments/{{ $tournament->id }}">view</a></td>
                            <td><a href="/tournaments/{{ $tournament->id }}/edit">edit</a></td>
                            <td>
                                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id"]) !!}
                                    {!! Form::submit('delete', ['class' => 'btn btn-danger btn-xs']) !!}
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <h4>Tournaments I registered to</h4>
            <table class="table table-condensed table-striped">
                <thead>
                <th>title</th>
                <th>date</th>
                <th>conclusion</th>
                <th>my status</th>
                <th></th>
                </thead>
                <tbody>
                    @if (count($registered) == 0)
                        <tr><td colspan="5" class="text-center"><em>no tournaments registered to yet</em></td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

