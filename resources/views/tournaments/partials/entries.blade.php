<table class="table table-condensed table-striped">
    <thead>
    <th class="text-right">rank</th>
    <th>player</th>
    <th>corp</th>
    <th>runner</th>
    </thead>
    <tbody>
    @for ($i = 0; $i < count($entries); $i++)
        @if ($user_entry && count($entries[$i]) && $entries[$i]->rank == $user_entry[$rank])
            <tr class="info">
        @else
            <tr>
                @endif
                <td class="text-right">#{{ $i+1 }}</td>
                @if (count($entries[$i]))
                    <td>{{ $entries[$i]->player->name }}</td>
                    <td><a href="{{ "https://netrunnerdb.com/en/decklist/".$entries[$i]->corp_deck_id }}">{{ $entries[$i]->corp_deck_title }}</a></td>
                    <td><a href="{{ "https://netrunnerdb.com/en/decklist/".$entries[$i]->runner_deck_id }}">{{ $entries[$i]->runner_deck_title }}</a></td>
                @else
                    <td></td>
                    <td><em>unclaimed</em></td>
                    <td><em>unclaimed</em></td>
                @endif
            </tr>
            @endfor
    </tbody>
</table>