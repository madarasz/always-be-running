{{--table of entries for tournament details page--}}
<table class="table table-sm table-striped abr-table" id="{{ $id }}">
    <thead>
        <th class="text-right">rank</th>
        <th>player</th>
        <th colspan="2">corp</th>
        <th colspan="2">runner</th>
        <th></th>
    </thead>
    <tbody>
    @for ($i = 0; $i < count($entries); $i++)
        @forelse ($entries[$i] as $entry)
            @if (count($entries[$i])>1 && !$relax)
                <tr class="danger{{ $user_entry && count($entry) && $entry[$rank] == $user_entry[$rank] ? ' own-claim' : '' }}">
                    <td class="text-right"><i class="fa fa-exclamation-triangle text-danger" title="conflict"></i> #{{ $i+1 }}</td>
            @elseif ($user_entry && count($entry) && $entry[$rank] == $user_entry[$rank])
                <tr class="info own-claim">
                    <td class="text-right">#{{ $i+1 }}</td>
            @else
                <tr>
                    <td class="text-right">#{{ $i+1 }}</td>
            @endif

            @if ($entry->player)
                <td><a href="/profile/{{ $entry->player->id }}" class="{{ $entry->player->linkClass() }}">{{ $entry->player->displayUsername() }}</a></td>
            @elseif ($entry->import_username)
                <td class="import-user">{{ $entry->import_username }}</td>
            @else
                <td></td>
            @endif

            {{--corp deck--}}
            <td>
                @if ($entry->corp_deck_identity)
                    <img src="/img/ids/{{ $entry->corp_deck_identity }}.png" class="id-medium">
                @endif
            </td>
            <td>
                @if ($entry->type == 3)
                    @if ($entry->broken_corp)
                        <i class="fa fa-chain-broken text-danger" title="broken link"></i>
                    @endif
                    {{--public deck--}}
                    @if ($entry->corp_deck_type == 1)
                        <a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->corp_deck_id }}">
                            {{ $entry->corp_deck_title }}
                        </a>
                    {{--private deck--}}
                    @elseif ($entry->corp_deck_type == 2)
                        <a href="{{ "https://netrunnerdb.com/en/deck/view/".$entry->corp_deck_id }}">
                            {{ $entry->corp_deck_title }}
                        </a>
                    @else
                        {{ $entry->corp_deck_title }}
                    @endif
                @else
                    {{ $entry->corp_deck_title }}
                @endif
            </td>
            {{--runner deck--}}
            <td>
                @if ($entry->runner_deck_identity)
                    <img src="/img/ids/{{ $entry->runner_deck_identity }}.png" class="id-medium">
                @endif
            </td>
            <td>
                @if ($entry->type == 3)
                        @if ($entry->broken_runner)
                            <i class="fa fa-chain-broken text-danger" title="broken link"></i>
                        @endif
                    {{--public deck--}}
                    @if ($entry->runner_deck_type == 1)
                        <a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->runner_deck_id }}">
                            {{ $entry->runner_deck_title }}
                        </a>
                    {{--private deck--}}
                    @elseif ($entry->runner_deck_type == 2)
                        <a href="{{ "https://netrunnerdb.com/en/deck/view/".$entry->runner_deck_id }}">
                            {{ $entry->runner_deck_title }}
                        </a>
                    @else
                        {{ $entry->runner_deck_title }}
                    @endif
                @else
                    {{ $entry->runner_deck_title }}
                @endif
            </td>
            {{--Claim button--}}
            <td class="text-right" style="white-space: nowrap;">
            @if ((!$user_entry || $user_entry->type < 3) && $entry->type != 3 && $entry->type != 4)
                <button class="btn btn-claim btn-xs" data-toggle="modal"
                        data-players-number="{{$tournament->players_number}}"
                        data-top-number="{{$tournament->top_number}}"
                        data-target="#claimModal" data-tournament-id="{{$tournament->id}}"
                        data-subtitle="{{$tournament->title.' - '.$tournament->date}}"
                        data-swiss-rank="{{ $entry->rank }}"
                        data-top-rank="{{  $entry->rank_top }}"
                        id="button-claim">
                    Claim
                </button>
            @endif
            {{--Remove button--}}
            @if (($entry->type == 3 || $entry->type == 4) && (($user && ($user->admin || $user->id == $creator))
                || ($user_entry && count($entry) && $entry->user == $user_entry->user)))
                    {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$entry->id", 'style' => 'display:inline']) !!}
                        @if ($user_entry && count($entry) && $entry->user == $user_entry->user)
                            {{--own entry--}}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove',
                                array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                        @else
                            {{--someone else's entry--}}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove',
                                array('type' => 'submit', 'class' => 'btn btn-danger btn-xs',
                                'onclick' => "return confirm('Are you sure you want to delete the claim of ".$entry->player->displayUsername()."?')")) !!}
                        @endif
                    {!! Form::close() !!}
            @elseif ($user && $entry->type > 4 && ($user->admin || $user->id == $creator || $user->id == $tournament->concluded_by))
                {!! Form::open(['method' => 'DELETE', 'url' => "/entries/anonym/$entry->id", 'style' => 'display:inline']) !!}
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs delete-anonym')) !!}
                {!! Form::close() !!}
            @endif
            </td>
        @empty
            <tr>
                <td class="text-right">#{{ $i+1 }}</td>
                <td></td>
                <td colspan="2"><em><small>unclaimed</small></em></td>
                <td colspan="2"><em><small>unclaimed</small></em></td>
                <td>
                    @if (!$user_entry || $user_entry->type < 3)
                        <button class="btn btn-claim btn-xs" data-toggle="modal"
                                data-players-number="{{$tournament->players_number}}"
                                data-top-number="{{$tournament->top_number}}"
                                data-target="#claimModal" data-tournament-id="{{$tournament->id}}"
                                data-subtitle="{{$tournament->title.' - '.$tournament->date}}"
                                data-swiss-rank="{{ $rank == 'rank' ? ($i+1) : 0}}"
                                data-top-rank="{{ $rank == 'rank_top' ? ($i+1) : 0}}"
                                id="button-claim">
                            Claim
                        </button>
                    @endif
                </td>
        @endforelse
        </tr>
    @endfor
    </tbody>
</table>