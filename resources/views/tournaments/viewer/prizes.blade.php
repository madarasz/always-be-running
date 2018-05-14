{{--Tournament prize list--}}
<div class="bracket">
    <a name="more-prize-top" class="anchor"></a>
    <h5>
        <i class="fa fa-gift" aria-hidden="true"></i>
        Prizes
        <div class="small-text">
            {{ $tournament->prize->year }}
            {{ $tournament->prize->title }}
        </div>
    </h5>

    {{--Photos of prizes--}}

        {{--image collapser--}}
        @if ($tournament->prize->photos->count() + $tournament->prize->elements->count() > 3)
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
    @if ($tournament->prize->photos->count() + $tournament->prize->elements->count() > 3)
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

    {{--Description--}}
    <div id="prizes-description" class="markdown-content p-b-1">
        {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize->description)) !!}
        @if ($tournament->prize->ffg_url !== '')
            <div class="text-xs-right">
                <a href="{{ $tournament->prize->ffg_url }}">read more</a>
            </div>
        @endif
    </div>
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
    {{--Additonal prizes--}}
    @if ($tournament->prize_additional !== '')
        <div id="markdown-additional" class="markdown-content">
            @if ($tournament->prizes)
                <strong>Additional prizes:</strong><br/>
            @endif
            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize_additional)) !!}
        </div>
    @endif
</div>