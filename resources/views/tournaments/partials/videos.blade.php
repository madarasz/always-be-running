{{--table of entries for tournament details page--}}
<table class="table table-sm table-striped abr-table" id="{{ $id }}">
    <tbody>
    @for ($i = 0; $i < count($videos); $i++)
        <tr>
            <td>
                <a href="#" onClick="watchVideo('{{ $videos[$i]->video_id }}')">
                    <img src="{{ $videos[$i]->thumbnail_url }}"/>
                </a>
            </td>
            <td>
                <b>
                    <a href="#" onClick="watchVideo('{{ $videos[$i]->video_id }}')">
                        {{ $videos[$i]->video_title }}
                    </a>
                </b>
                <br/>{{ $videos[$i]->channel_name }}
            </td>

            @if ($user && ($user->admin || $user->id == $videos[$i]->user_id || $user->id == $videos[$i]->tournament->creator))
                <td>
                    {!! Form::open(['method' => 'DELETE', 'url' => "/videos/{$videos[$i]->id}"]) !!}
                        {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs delete-video')) !!}
                    {!! Form::close() !!}
                </td>
            @else
                <td></td>
            @endif
        </tr>
    @endfor
    </tbody>
</table>
