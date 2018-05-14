{{--Tournament prize list--}}
<div class="bracket">
    <a name="more-prize-top" class="anchor"></a>
    <h5>
        <i class="fa fa-gift" aria-hidden="true"></i>
        Prizes
        @unless(is_null($tournament->prize_id))
            <div class="small-text">
                {{ $tournament->prize->year }}
                {{ $tournament->prize->title }}
            </div>
        @endunless
    </h5>

    @unless(is_null($tournament->prize_id))
        {{--Photos of prizes--}}

        {{--image collapser--}}
        <?php $countPhotos = $tournament->prize->countPhotos(); ?>
        @if ($countPhotos > 3)
            <div class="more-image-container collapse" id="more-prize-collapse" aria-expanded="false" style="max-height: 400px; height: 0px;">
                <div class="more-image-overlay" id="more-prize-overlay"></div>
        @endif

        <div class="row">
            {{--Photos of prize--}}
            @foreach($tournament->prize->photos as $photo)
                <div class="gallery-item col-xs-4">
                    <div style="position: relative;">
                        {{--image thumpnail--}}
                        <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="prize-gallery"
                           data-title="{{ $tournament->prize->year.' '.$tournament->prize->title }}">
                            <img src="{{ $photo->urlThumb() }}"/>
                        </a>
                    </div>
                </div>
            @endforeach
            {{--Photos of prize elements--}}
            @foreach($tournament->prize->elements()->get() as $element)
                @foreach($element->photos as $photo)
                    <div class="gallery-item col-xs-4">
                        <div style="position: relative;">
                            {{--image thumpnail--}}
                            <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="prize-gallery"
                               data-title="{{ $tournament->prize->year.' '.$tournament->prize->title }}"
                               data-footer="{{ '<em>'.$element->quantityToString().':</em> <strong>'.$element->title.'</strong> '.$element->type }}">
                                <img src="{{ $photo->urlThumb() }}"/>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        {{--end of image collapser, show more button--}}
        @if ($countPhotos > 3)
            </div>
            <div class="text-xs-center p-b-1">
                <a class="btn btn-xs btn-primary white-text" data-toggle="collapse" id="button-more-prize"
                   href="#more-prize-collapse" aria-expanded="false" aria-controls="more-collapse">show more</a>
            </div>
            <script type="text/javascript">
                $('#more-prize-collapse').on('shown.bs.collapse', function () {
                    $('#more-prize-collapse').css({
                        'max-height': 'none'
                    });
                    $('#more-prize-overlay').addClass('hidden-xs-up');
                    $('#button-more-prize').text('show less');
                }).on('hidden.bs.collapse', function () {
                    $('#more-prize-collapse').css({
                        'max-height': '300px'
                    });
                    $('#more-prize-overlay').removeClass('hidden-xs-up');
                    $('#button-more-prize').text('show more');
                    location.hash = "#more-prize-top";
                })
            </script>
        @endif

        {{--Prize list--}}
        <table class="table table-sm">
            <tbody>
            @foreach($tournament->prize->elements as $index=>$element)
                @if ($index > 0 && $tournament->prize->elements[$index-1]->quantity == $tournament->prize->elements[$index]->quantity)
                    +
                @else
                    <tr>
                        <td class="text-xs-right">
                            <em>{{ $element->quantityToString() }}:</em>
                        </td>
                        <td>
                @endif

                @if (is_null($element->photos()->first()))
                    {{--doesn't have photo--}}
                    <strong>{{ $element->title }}</strong>
                    {{ $element->type }}
                @else
                    {{--has photo--}}
                    <a href="{{ $element->photos()->first()->url() }}" data-toggle="lightbox" data-gallery="prize-link-gallery"
                       data-title="{{ $tournament->prize->year.' '.$tournament->prize->title }}"
                       data-footer="{{ '<em>'.$element->quantityToString().':</em> <strong>'.$element->title.'</strong> '.$element->type }}">
                        <strong>{{ $element->title }}</strong>
                        {{ $element->type }}
                    </a>
                @endif

                @unless ($index+1 < count($tournament->prize->elements) && $tournament->prize->elements[$index+1]->quantity == $tournament->prize->elements[$index]->quantity)
                        </td>
                    </tr>
                @endunless
            @endforeach
            </tbody>
        </table>

        {{--Description--}}
        <div id="prizes-description" class="markdown-content">
            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize->description)) !!}
            @if ($tournament->prize->ffg_url !== '')
                <div class="text-xs-right">
                    <a href="{{ $tournament->prize->ffg_url }}">read more</a>
                </div>
            @endif
        </div>
    @endunless

    {{--Additonal prizes--}}
    @if ($tournament->prize_additional !== '')
        <div id="markdown-additional" class="markdown-content">
            @unless (is_null($tournament->prize_id))
                <hr/>
                <strong>Additional prizes for this tournament:</strong><br/>
            @endunless
            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize_additional)) !!}
        </div>
    @endif
</div>