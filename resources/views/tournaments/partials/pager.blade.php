{{--pager for tables and lists--}}
<div class="text-xs-center hidden-xs-up small-text" id="{{ $id }}-controls" data-maxrows="{{ @$maxrows }}"
     data-maxrowsoriginal="{{ @$maxrows }}" data-currentpage="1" data-totalrows="0" data-torows="0">
    <a onclick="doTournamentPaging('{{ $id }}', false)" class="fake-link hidden-xs-up" id="{{ $id }}-controls-back">
        <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
    </a>
    showing <span id="{{ $id }}-number-from">1</span>-<span id="{{ $id }}-number-to">{{ @$maxrows }}</span> of <span id="{{ $id }}-number-total"></span>
    <a onclick="doTournamentPaging('{{ $id }}', true)" class="fake-link" id="{{ $id }}-controls-forward">
        <i class="fa fa-chevron-circle-right" aria-hidden="true"></i><br/>
    </a>
</div>
<div class="text-xs-center hidden-xs-up" id="{{ $id }}-options" style="font-size: 80%; font-style: normal"
        data-selected="{{ @$pager_options[0] }}">
    @if (count(@$pager_options)>0)
        @foreach($pager_options as $key=>$option)
            <span class="label control-paging {{ $key==0 ? 'label-active':'label-inactive' }}"
                  onclick="changePageOptions('{{$id}}', '{{ $option }}')">
                {{ $option }}
            </span>
        @endforeach
    @endif
    {{--country flag / text switcher--}}
    @if (@$flag_switcher)
        <span> - </span>
        <span class="label control-flag label-active" onclick="changeFlagOption(true)">flag</span>
        <span class="label control-text label-inactive" onclick="changeFlagOption(false)">text</span>
    @endif
</div>
