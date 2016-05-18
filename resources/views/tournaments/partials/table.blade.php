<h4>{{ $title }}</h4>
<table class="table table-condensed table-striped" id="{{ $id }}">
    <thead>
        @if( in_array('title', $columns) )
            <th>title</th>
        @endif
        @if( in_array('date', $columns) )
            <th>date</th>
        @endif
        @if( in_array('cardpool', $columns) )
            <th>cardpool</th>
        @endif
        @if( in_array('approval', $columns) )
            <th>approval</th>
        @endif
        @if( in_array('conclusion', $columns) )
            <th>conclusion</th>
        @endif
        @if( in_array('players', $columns) )
            <th class="text-center">players</th>
        @endif
        @if( in_array('decks', $columns) )
            <th class="text-center">decks</th>
        @endif
        @if( in_array('action_view', $columns) )
            <th></th>
        @endif
        @if( in_array('action_edit', $columns) )
            <th></th>
        @endif
        @if( in_array('action_approve', $columns) )
            <th></th>
        @endif
        @if( in_array('action_reject', $columns) )
            <th></th>
        @endif
        @if( in_array('action_delete', $columns) )
            <th></th>
        @endif
        @if( in_array('action_restore', $columns) )
            <th></th>
        @endif
    </thead>
    <tbody>
        @if (count($data) == 0)
            <tr><td colspan="{{ count($columns) }}" class="text-center"><em>{{ $empty_message }}</em></td></tr>
        @endif
        @foreach ($data as $row)
            <tr>
                @if( in_array('title', $columns) )
                    <td>{{ $row->title }}</td>
                @endif
                @if( in_array('date', $columns) )
                    <td>{{ $row->date }}</td>
                @endif
                @if( in_array('cardpool', $columns) )  
                    <td></td>
                @endif
                @if( in_array('approval', $columns) )
                    <td>
                        @if ($row->approved === null)
                            <span class="label label-warning">pending</span>
                        @elseif ($row->approved == 1)
                            <span class="label label-success">approved</span>
                        @else
                            <span class="label label-danger">rejected</span>
                        @endif
                    </td>
                @endif
                @if( in_array('conclusion', $columns) )
                    <td>
                        @if ($row->concluded == 1)
                            <span class="label label-success">concluded</span>
                        @elseif ($row->date <= $nowdate)
                            <span class="label label-danger">due, pls update</span>
                        @else
                            <span class="label label-info">not yet</span>
                        @endif
                    </td>
                @endif
                @if( in_array('players', $columns) )
                    <td class="text-center">
                        @if ($row->concluded == 1)
                            {{ $row->players_number }}
                        @endif
                    </td>
                @endif
                @if( in_array('decks', $columns) )
                    <td></td>
                @endif
                @if( in_array('action_view', $columns) )
                    <td><a href="/tournaments/{{ $row->id }}" class="btn btn-primary btn-xs"><i class="fa fa-eye" aria-hidden="true"></i> view</a></td>
                @endif
                @if( in_array('action_edit', $columns) )
                    <td><a href="/tournaments/{{ $row->id }}/edit" class="btn btn-primary btn-xs"><i class="fa fa-pencil" aria-hidden="true"></i> edit</a></td>
                @endif
                @if( in_array('action_approve', $columns) )
                    <td><a href="/tournaments/{{ $row->id }}/approve" class="btn btn-success btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i> approve</a></td>
                @endif
                @if( in_array('action_reject', $columns) )
                    <td><a href="/tournaments/{{ $row->id }}/reject" class="btn btn-danger btn-xs"><i class="fa fa-thumbs-down" aria-hidden="true"></i> reject</a></td>
                @endif
                @if( in_array('action_delete', $columns) )
                    <td>
                        {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$row->id"]) !!}
                            {{--{!! Form::submit('delete', ['class' => 'btn btn-danger btn-xs']) !!}--}}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> delete', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                        {!! Form::close() !!}
                    </td>
                @endif
                @if( in_array('action_restore', $columns) )
                    <td><a href="/tournaments/{{ $row->id }}/restore" class="btn btn-primary btn-xs">restore</a></td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>