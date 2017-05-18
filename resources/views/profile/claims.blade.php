{{--Claims--}}
<div class="bracket">
    <h5>
        <i class="fa fa-list-ol" aria-hidden="true"></i>
        Claims ({{$claim_count}})
    </h5>
    <ul>
        @foreach($claims as $claim)
            @if ($claim->tournament->tournament_type_id > 1 && $claim->tournament->tournament_type_id < 6)
                <li style="list-style: none">
            @else
                <li>
            @endif
                    @include('tournaments.partials.list-type', ['tournament' => $claim->tournament, 'class' => 'no-li'])
                    @include('tournaments.partials.list-format', ['tournament' => $claim->tournament, 'class' => 'no-li'])
                    <strong>#{{ $claim->rank() }} / {{ $claim->tournament->players_number }}</strong>
                    @if ($claim->type == 3)
                        <a href="{{ $claim->runner_deck_url() }}" title="{{ $claim->runner_deck_title }}"><img src="/img/ids/{{ $claim->runner_deck_identity }}.png"></a>&nbsp;<a href="{{ $claim->corp_deck_url() }}" title="{{ $claim->corp_deck_title }}"><img src="/img/ids/{{ $claim->corp_deck_identity }}.png"></a>
                    @else
                        <img src="/img/ids/{{ $claim->runner_deck_identity }}.png">&nbsp;<img src="/img/ids/{{ $claim->corp_deck_identity }}.png">
                    @endif
                    <a href="{{ $claim->tournament->seoUrl() }}">
                        {{ $claim->tournament->title }}
                    </a>
                    <span class="legal-bullshit">({{ $claim->tournament->date }})</span>
                </li>
        @endforeach
    </ul>
</div>