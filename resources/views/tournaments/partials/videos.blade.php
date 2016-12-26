{{--table of videos for tournament details page--}}
<p><b>{{ count($videos) }}</b> video{{ count($videos) != 1 ? 's' : '' }} for this tournament.</p>
<table class="table table-sm table-striped abr-table" id="{{ $id }}">
    <tbody>
    @for ($i = 0; $i < count($videos); $i++)
        <tr class="{{ $i >= 4 ? 'hide-video hidden-xs-up' : ''}}">
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
@if (count($videos) > 4)
    <div>
        <button class="btn btn-primary btn-xs" onClick="showVideoList(true)" id="showVideoList">
            <i class="fa fa-eye" aria-hidden="true"></i> Show All Videos
        </button>
        <button class="btn btn-primary btn-xs hidden-xs-up" onClick="showVideoList(false)" id="hideVideoList">
            <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide Videos
        </button>
    </div>
@endif
