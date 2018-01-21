<div class="bracket">
    <h5>
        <i class="fa fa-star" aria-hidden="true"></i>
        Featured
        @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Handpicked, criteria being considered:
                            <ul>
                                <li>has photos / videos</li>
                                <li>many players, claims</li>
                                <li>recent</li>
                                <li>unique event</li>
                                <li>TO is a <a href="/support-me">supporter</a></li>
                            </ul>'])
    </h5>
    <div class="row" style="padding: .5em 1em 1em;">
        {{--support me box--}}
        <div class="col-xs-12 featured-col">
            <div class="featured-box">
                <div class="featured-header">
                    <div class="featured-bg support-bg"></div>
                    <div class="stuff">
                        <div class="featured-title">
                            <i title="support me" class="fa fa-gift"></i>
                            <a href="/support-me" class="supporter">
                                <strong>Support me</strong>
                            </a>
                        </div>
                        <span class="small-text">thank you for using this site</span>
                    </div>
                </div>
            </div>
        </div>
        {{--featured tournaments--}}
        @foreach($featured as $ft)
            <div class="col-xs-6 col-lg-12 featured-col">
            <div class="featured-box">
                {{--header--}}
                <div class="featured-header">
                    <div class="featured-bg" style="background-image: url({{ $ft->coverImage() }});"></div>
                    <div class="stuff">
                        <div class="featured-title">
                            @if ($ft->charity)
                                <i title="charity" class="fa fa-heart text-danger"></i>
                            @endif
                            @include('tournaments.partials.list-type', ['tournament' => $ft])
                            @include('tournaments.partials.list-format', ['tournament' => $ft])
                            <a href="{{ $ft->seoUrl() }}">
                                <strong>{{ $ft->title }}</strong>
                            </a>
                        </div>
                        <span class="small-text">({{ $ft->date }}) - {{ $ft->cardpool->name }}</span>
                    </div>
                </div>
                {{--winner--}}
                @if ($ft->winner)
                <table class="table table-striped small-text" style="margin-bottom: 0.5em">
                    <tr>
                        <td><i class="fa fa-trophy" aria-hidden="true"></i></td>
                        <td>
                            @if ($ft->winner->player)
                                <a href="/profile/{{ $ft->winner->player->id }}">{{ $ft->winner->player->displayUsername() }}</a>
                            @else
                                {{  $ft->winner->import_username }}
                            @endif
                        </td>
                        <td class="text-xs-right cell-winner" nowrap="">
                            @if ($ft->winner->type == 3)
                                <a href="{{ $ft->winner->runner_deck_url() }}" title="{{ $ft->winner->runner_deck_title }}"><img src="/img/ids/{{ $ft->winner->runner_deck_identity }}.png"></a>&nbsp;<a href="{{ $ft->winner->corp_deck_url() }}"><img src="/img/ids/{{ $ft->winner->corp_deck_identity }}.png"></a>
                            @else
                                <img src="/img/ids/{{ $ft->winner->runner_deck_identity }}.png">&nbsp;<img src="/img/ids/{{ $ft->winner->corp_deck_identity }}.png">
                            @endif
                        </td>
                    </tr>
                </table>
                @endif
                {{--photos--}}
                @if (count($ft->photos))
                    <div class="featured-images">
                        @foreach($ft->photos as $index => $photo)
                            <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="gallery" data-title="{{ $photo->title }}"
                               data-footer="{{ 'uploaded by <a href="/profile/'.$photo->user->id.'">'.$photo->user->displayUsername().'</a>' }}">
                                <img src="{{ $photo->urlThumb() }}"/>
                            </a>
                        @endforeach
                    </div>
                @endif
                {{--videos--}}
                @if (count($ft->videos))
                    <div class="featured-images">
                        @foreach($ft->videos as $index => $video)
                            @if (intval($video->type) == 1)
                                <a href="https://www.youtube.com/watch?v={{ $video->video_id }}">
                                    <img src="{{ $video->thumbnail_url }}"/>
                                </a>
                            @else
                                <a href="https://www.twitch.tv/videos/{{ $video->video_id }}">
                                    <img src="{{ $video->thumbnail_url }}"/>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
                {{--numbers--}}
                <div class="small-text text-xs-center featured-footer">
                    {{ $ft->players_number }} <i class="fa fa-user" title="players"></i>
                    {{ $ft->claimCount }} <i class="fa fa-address-card" title="claims"></i>
                    @if (count($ft->photos))
                        {{ count($ft->photos) }} <i title="photo" class="fa fa-camera"></i>
                    @endif
                    @if (count($ft->videos))
                        {{ count($ft->videos) }} <i title="video" class="fa fa-video-camera"></i>
                    @endif
                    {{ $ft->tournament_type_id == 7 ? 'online' : $ft->location_country }}
                </div>
            </div>
            </div>
        @endforeach
    </div>
</div>
{{--Enable gallery--}}
<script type="application/javascript">
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({alwaysShowClose: true});
    });
</script>