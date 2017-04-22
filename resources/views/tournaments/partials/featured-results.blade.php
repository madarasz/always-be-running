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
    @foreach($featured as $ft)
        <div class="featured-box">
            {{--header--}}
            <div class="featured-header">
                <div class="featured-bg" style="background-image: url({{ $ft->coverImage() }});"></div>
                <div class="stuff">
                    <div class="featured-title">
                        @include('tournaments.partials.list-type', ['tournament' => $ft])
                        <a href="{{ $ft->seoUrl() }}">
                            <strong>{{ $ft->title }}</strong>
                        </a>
                    </div>
                    <span class="small-text">({{ $ft->date }}) - {{ $ft->cardpool->name }}</span>
                </div>
            </div>
            {{--winner--}}
            <table class="table table-striped small-text" style="margin-bottom: 0.5em">
                <tr>
                    <td><i class="fa fa-trophy" aria-hidden="true"></i></td>
                    <td>
                        @if ($ft->winner()->player)
                            <a href="/profile/{{ $ft->winner()->player->id }}">{{ $ft->winner()->player->name }}</a>
                        @else
                            {{  $ft->winner()->import_username }}
                        @endif
                    </td>
                    <td class="text-xs-right" nowrap="">
                        @if ($ft->winner()->type == 3)
                            <a href="{{ $ft->winner()->runner_deck_url() }}" title="{{ $ft->winner()->runner_deck_title }}"><img src="/img/ids/{{ $ft->winner()->runner_deck_identity }}.png"></a>&nbsp;<a href="{{ $ft->winner()->corp_deck_url() }}"><img src="/img/ids/{{ $ft->winner()->corp_deck_identity }}.png"></a>
                        @else
                            <img src="/img/ids/{{ $ft->winner()->runner_deck_identity }}.png">&nbsp;<img src="/img/ids/{{ $ft->winner()->corp_deck_identity }}.png">
                        @endif
                    </td>
                </tr>
            </table>
            {{--photos--}}
            @if (count($ft->photos))
                <div class="featured-images">
                    @foreach($ft->photos as $index => $photo)
                        <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="gallery" data-title="{{ $photo->title }}"
                           data-footer="{{ 'uploaded by <a href="/profile/'.$photo->user->id.'">'.$photo->user->name.'</a>' }}">
                            <img src="{{ $photo->urlThumb() }}"/>
                        </a>
                    @endforeach
                </div>
            @endif
            {{--videos--}}
            @if (count($ft->videos))
                <div class="featured-images">
                    @foreach($ft->videos as $index => $video)
                        <a href="https://www.youtube.com/watch?v={{ $video->video_id }}">
                            <img src="{{ $video->thumbnail_url }}"/>
                        </a>
                    @endforeach
                </div>
            @endif
            {{--numbers--}}
            <div class="small-text text-xs-center featured-footer">
                {{ $ft->players_number }} <i class="fa fa-user" title="players"></i>
                {{ $ft->claim_number() }} <i class="fa fa-address-card" title="claims"></i>
                @if (count($ft->photos))
                    {{ count($ft->photos) }} <i title="photo" class="fa fa-camera"></i>
                @endif
                @if (count($ft->videos))
                    {{ count($ft->videos) }} <i title="video" class="fa fa-video-camera"></i>
                @endif
                {{ $ft->location_country }}
            </div>
        </div>
    @endforeach
</div>
{{--Enable gallery--}}
<script type="application/javascript">
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({alwaysShowClose: true});
    });
</script>