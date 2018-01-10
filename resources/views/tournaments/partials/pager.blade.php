{{--pager for tables and lists--}}
<div class="text-xs-center hidden-xs-up small-text" id="{{ $id }}-controls" data-maxrows="{{ @$maxrows }}" data-currentpage="1"
        data-totalrows="0" data-torows="0">
    <a onclick="doTournamentPaging('{{ $id }}', false)" class="fake-link hidden-xs-up" id="{{ $id }}-controls-back">
        <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
    </a>
    showing <span id="{{ $id }}-number-from">1</span>-<span id="{{ $id }}-number-to">{{ @$maxrows }}</span> of <span id="{{ $id }}-number-total"></span>
    <a onclick="doTournamentPaging('{{ $id }}', true)" class="fake-link" id="{{ $id }}-controls-forward">
        <i class="fa fa-chevron-circle-right" aria-hidden="true"></i><br/>
    </a>
</div>