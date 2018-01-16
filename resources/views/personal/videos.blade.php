{{--Personal page-Videos tab content--}}
<div class="row">
    <div class="col-xs-12">
        {{--Notification for unavailable videos--}}
        <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-unvideo" data-badge="">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            You have videos flagged as unavailable.
        </div>
        <div class="bracket">
            <h5 class="p-b-2">
                <i class="fa fa-video-camera" aria-hidden="true"></i>
                My videos ({{ $video_count }})<br/>
                <small>videos I have added</small>
            </h5>
            @foreach($video_tournaments as $tournament)
                <h6>
                    <a href="{{ $tournament->seoURL() }}">{{ $tournament->title }}</a>
                    <small>
                        - {{ $tournament->location() }} - {{ $tournament->date }}
                    </small>
                </h6>
                <hr/>
                <table class="table table-sm table-striped abr-table" id="table-my-videos">
                    <tbody>
                        @foreach($tournament->videos_all as $video)
                            <tr class="{{ $video->flag_removed ? 'row-danger':'' }}">
                                {{--thumbnail--}}
                                <td>
                                    <img src="{{ $video->thumbnail_url }}" class="video-thumbnail"/>
                                </td>
                                {{--video info--}}
                                <td>
                                    {{--error for deleted flag--}}
                                    @if ($video->flag_removed)
                                        {{--Notification for claim--}}
                                        <div class="alert alert-warning view-indicator">
                                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                            This video is flagged as unavailable.
                                        </div>
                                    @endif
                                    <b>{{ $video->video_title }}
                                        @if ($video->length)
                                            <span class="small-text font-weight-normal">({{ $video->length }})</span>
                                        @endif
                                    </b>
                                    <br/>
                                    {{ $video->channel_name }}
                                    {{--tagged users--}}
                                    @if (count($video->videoTags))
                                        <br/>
                                        <span id="tags-{{ $video->video_id }}">
                                            @foreach($video->videoTags as $key => $tag)
                                                {{--display IDs--}}
                                                @if ($video->tournament->registration_number() && $tag->user)
                                                    <?php if (is_null(@$entries)) $entries = $video->tournament->entries; ?>
                                                    @for ($u = 0; $u < count($entries); $u++)
                                                        @if($entries[$u]->user == $tag->user_id)
                                                            @if ($entries[$u]->type == 3)
                                                                @if ($tag->is_runner == true || is_null($tag->is_runner))
                                                                    <a href="{{ $entries[$u]->runner_deck_url() }}" title="{{ $entries[$u]->runner_deck_title }}"><img src="/img/ids/{{ $entries[$u]->runner_deck_identity }}.png" class="id-small"></a>
                                                                @endif
                                                                @if ($tag->is_runner == false || is_null($tag->is_runner))
                                                                    <a href="{{ $entries[$u]->corp_deck_url() }}" title="{{ $entries[$u]->corp_deck_title }}"><img src="/img/ids/{{ $entries[$u]->corp_deck_identity }}.png" class="id-small"></a>
                                                                @endif
                                                            @elseif ($entries[$u]->type != 0)
                                                                @if ($tag->is_runner == true || is_null($tag->is_runner))
                                                                    <img src="/img/ids/{{ $entries[$u]->runner_deck_identity }}.png" class="id-small">
                                                                @endif
                                                                @if ($tag->is_runner == false || is_null($tag->is_runner))
                                                                    <img src="/img/ids/{{ $entries[$u]->corp_deck_identity }}.png" class="id-small">
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
                                                {{ $key != count($video->videoTags)-1 ? '- ' : '' }}
                                            @endforeach
                                        </span>
                                    @endif
                                </td>
                                {{--buttons--}}
                                <td>
                                    {!! Form::open(['method' => 'DELETE', 'url' => "/videos/{$video->id}"]) !!}
                                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                                        array('type' => 'submit', 'class' => 'btn btn-danger btn-xs delete-video',
                                        'onclick' => "return confirm('Are you sure you want to delete video?')")) !!}
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
</div>