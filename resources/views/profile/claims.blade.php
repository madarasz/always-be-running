{{--Claims--}}
<div class="bracket">
    <h5>
        <i class="fa fa-list-ol" aria-hidden="true"></i>
        Claims ({{$claim_count}})
    </h5>
    <ul id="list-claims">
        @foreach($claims as $key=>$claim)
            @if ($claim->tournament->tournament_type_id > 1 && $claim->tournament->tournament_type_id < 6)
                <li style="list-style: none" id="list-claims-row-{{ $key+1 }}" class="{{ $key>=$maxrows ? 'hidden-xs-up':'' }}">
            @else
                <li id="list-claims-row-{{ $key+1 }}" class="{{ $key>=$maxrows ? 'hidden-xs-up':'' }}">
            @endif
                    @include('tournaments.partials.list-type', ['tournament' => $claim->tournament, 'class' => 'no-li'])
                    @include('tournaments.partials.list-format', ['tournament' => $claim->tournament, 'class' => 'no-li'])
                    <strong>#{{ $claim->rank() }} / {{ $claim->tournament->players_number }}</strong>
                    @if ($claim->type == 3)
                        <a href="{{ $claim->runner_deck_url() }}" title="{{ $claim->runner_deck_title }}"><img src="/img/ids/{{ $claim->runner_deck_identity }}.png" class="id-small"></a>
                        @if ($claim->broken_runner)<i class="fa fa-chain-broken text-danger broken-deck-profile" title="broken link"></i>@endif
                        <a href="{{ $claim->corp_deck_url() }}" title="{{ $claim->corp_deck_title }}"><img src="/img/ids/{{ $claim->corp_deck_identity }}.png" class="id-small"></a>
                        @if ($claim->broken_corp)<i class="fa fa-chain-broken text-danger broken-deck-profile" title="broken link"></i>@endif
                    @else
                        <img src="/img/ids/{{ $claim->runner_deck_identity }}.png" class="id-small">&nbsp;<img src="/img/ids/{{ $claim->corp_deck_identity }}.png" class="id-small">
                    @endif
                    <a href="{{ $claim->tournament->seoUrl() }}">
                        {{ $claim->tournament->title }}
                    </a>
                    <span class="legal-bullshit">({{ $claim->tournament->date }})</span>
                </li>
        @endforeach
    </ul>
    @include('tournaments.partials.pager', ['id' => 'list-claims', 'maxrows' => $maxrows])
</div>
