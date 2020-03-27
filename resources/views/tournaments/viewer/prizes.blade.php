{{--Tournament prize list--}}
<div class="bracket">
    @unless(is_null($tournament->prize_id))
        <a name="more-prize-top" class="anchor"></a>
        <h5>
            <i class="fa fa-gift" aria-hidden="true"></i>
            Prizes
            <div class="small-text">
                {{ $tournament->prize->year }}
                {{ $tournament->prize->title }}
                @if ($tournament->prize->ffg_url !== '')
                    <div class="text-xs-right pull-right">
                        <a href="{{ $tournament->prize->ffg_url }}">read more</a>
                    </div>
                @endif
            </div>
        </h5>

        {{--Photos of prizes--}}
        <div class="row">
            {{--Photos of prize--}}
            @foreach($tournament->prize->photos as $photo)
                <div class="gallery-item col-xs-3">
                    <div style="position: relative;">
                        {{--image thumpnail--}}
                        <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="prize-gallery"
                           data-title="{{ $tournament->prize->year.' '.$tournament->prize->title }}" class="markdown-content">
                            <img src="{{ $photo->urlThumb() }}"/>
                        </a>
                    </div>
                </div>
            @endforeach
            {{--Photos of prize elements--}}
            @foreach($tournament->prize->elements()->get() as $element)
                @foreach($element->photos as $photo)
                    <div class="gallery-item col-xs-3">
                        <div style="position: relative;">
                            {{--image thumpnail--}}
                            <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="prize-gallery"
                               data-title="{{ $tournament->prize->year.' '.$tournament->prize->title }}"
                               data-footer="{{ '<em>'.$element->quantityToString().':</em> <strong>'.$element->title.'</strong> '.$element->type }}" class="markdown-content">
                                <img src="{{ $photo->urlThumb() }}"/>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        {{--Prize list--}}
        <table class="table table-sm table-prizes">
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
        </div>
    @endunless

    {{-- Unofficial Prizes --}}
    @if ($tournament->unofficial_prizes->count() > 0)
        @include('tournaments.viewer.prizes-unofficial')
    @endif

    {{--Additonal prizes--}}
    @if ($tournament->prize_additional !== '')
        <div id="markdown-additional" class="markdown-content">
            @unless (is_null($tournament->prize_id) && $tournament->unofficial_prizes->count() == 0)
                <hr/>
            @endunless
            <h5>
                <i class="fa fa-plus" aria-hidden="true"></i>
                Additional prizes and information
            </h5>
            
            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $tournament->prize_additional)) !!}
        </div>
    @endif
</div>