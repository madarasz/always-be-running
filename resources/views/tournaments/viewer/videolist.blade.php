{{--table of videos for tournament details page--}}
@if(@$all_users)
    @include('tournaments.modals.video-tagging')
@endif
<table class="table table-sm table-striped abr-table" id="{{ $id }}">
    <tbody>
    @for ($i = 0; $i < count($videos); $i++)
        <tr class="{{ $i >= 4 ? 'hide-video hidden-xs-up' : ''}}" id="video-{{ $videos[$i]->video_id }}">
            {{--thumbnail--}}
            <td>
                <a href="#" onClick="watchVideo('{{ $videos[$i]->video_id }}', {{ $videos[$i]->type }}); setCookie('selected-tournament', '{{ @$tournament->id }}' ,14);">
                    <img src="{{ $videos[$i]->thumbnail_url }}" class="video-thumbnail"/>
                </a>
            </td>
            {{--video info--}}
            <td>
                <b>
                    <a href="#" onClick="watchVideo('{{ $videos[$i]->video_id }}', {{ $videos[$i]->type }}); setCookie('selected-tournament', '{{ @$tournament->id }}' ,14);">
                        {{ $videos[$i]->video_title }}
                    </a>
                    @if ($videos[$i]->length)
                        <span class="small-text font-weight-normal">({{ $videos[$i]->length }})</span>
                    @endif
                </b>
                <br/>
                {{ $videos[$i]->channel_name }}
                @if (!@$tournament)
                    <br/>
                    <span class="small-text">
                        <a href="{{ $videos[$i]->tournament->seoUrl() }}">{{ $videos[$i]->tournament->title }}</a>
                        ({{ $videos[$i]->tournament->date }})
                    </span>
                @endif
                {{--tagged users--}}
                @if (count($videos[$i]->videoTags))
                    <br/>
                    <span id="tags-{{ $videos[$i]->video_id }}">
                    @foreach($videos[$i]->videoTags as $key => $tag)
                        {{--delete tag button--}}
                        @if($user && ($user->admin || $user->id == $tag->tagged_by_user_id ||
                            $user->id == $tag->video->user_id || $user->id == $tag->video->tournament->creator ||
                            $user->id == $tag->user_id))
                            <a href="/videotags/delete/{{ $tag->id }}" class="text-danger"
                               onclick="return confirm('Are you sure you want to untag user?')"><i class="fa fa-trash" title="untag"></i></a>
                        @endif
                        {{--display IDs--}}
                        @if ($videos[$i]->tournament->registration_number() && $tag->user)
                            <?php if (is_null(@$entries)) $entries = $videos[$i]->tournament->entries; ?>
                            @for ($u = 0; $u < count($entries); $u++)
                                @if($entries[$u]->user == $tag->user_id)
                                    @if ($entries[$u]->type == 3)
                                        @if ($tag->is_runner == true || is_null($tag->is_runner))
                                            <a href="{{ $entries[$u]->runner_deck_url() }}" title="{{ $entries[$u]->runner_deck_title }}"><img src="/img/ids/{{ $entries[$u]->runner_deck_identity }}.png"></a>
                                        @endif
                                        @if ($tag->is_runner == false || is_null($tag->is_runner))
                                            <a href="{{ $entries[$u]->corp_deck_url() }}" title="{{ $entries[$u]->corp_deck_title }}"><img src="/img/ids/{{ $entries[$u]->corp_deck_identity }}.png"></a>
                                        @endif
                                    @elseif ($entries[$u]->type != 0)
                                        @if ($tag->is_runner == true || is_null($tag->is_runner))
                                            <img src="/img/ids/{{ $entries[$u]->runner_deck_identity }}.png">
                                        @endif
                                        @if ($tag->is_runner == false || is_null($tag->is_runner))
                                            <img src="/img/ids/{{ $entries[$u]->corp_deck_identity }}.png">
                                        @endif
                                    @endif
                                @endif
                            @endfor
                        @endif
                        {{--user name--}}
                        @if ($tag->user)
                            <a href="/profile/{{ $tag->user->id }}">{{ $tag->user->displayUsername() }}</a>
                        @endif
                        {{--player name--}}
                        @if (strlen($tag->import_player_name))
                            <em>{{ $tag->import_player_name }}</em>
                        @endif
                        {{--side--}}
                        @if (!is_null($tag->is_runner))
                            <span class="small-text">({{ $tag->is_runner ? 'runner' : 'corporation' }})</span>
                        @endif
                        {{--separator--}}
                        {{ $key != count($videos[$i]->videoTags)-1 ? '- ' : '' }}
                    @endforeach
                    </span>
                @endif
            </td>
            {{--buttons--}}
            <td>
                @if ($user && @$all_users)
                <button href="" class="btn btn-xs btn-info" data-toggle="modal" data-target="#videoTaggingModal"
                        data-video-id="{{ $videos[$i]->id }}" data-video-title="{{ $videos[$i]->video_title }}">
                    <i class="fa fa-user" title="tag user"></i>
                </button>
                @endif
                @if ($user && ($user->admin || $user->id == $videos[$i]->user_id || $user->id == $videos[$i]->tournament->creator))
                    {!! Form::open(['method' => 'DELETE', 'url' => "/videos/{$videos[$i]->id}"]) !!}
                        {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                            array('type' => 'submit', 'class' => 'btn btn-danger btn-xs delete-video',
                            'onclick' => "return confirm('Are you sure you want to delete video?')")) !!}
                    {!! Form::close() !!}
                @endif
            </td>
        </tr>
    @endfor
    </tbody>
</table>
@if (count($videos) > 4)
    <div class="text-xs-center">
        <button class="btn btn-primary btn-xs" onclick="showVideoList(true)" id="showVideoList" type="button">
            <i class="fa fa-eye" aria-hidden="true"></i> Show All Videos
        </button>
        <button class="btn btn-primary btn-xs hidden-xs-up" onclick="showVideoList(false)" id="hideVideoList" type="button">
            <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide Videos
        </button>
    </div>
@endif
