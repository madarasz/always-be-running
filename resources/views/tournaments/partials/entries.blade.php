{{--table of entries for tournament details page--}}
<table class="table table-sm table-striped" id="{{ $id }}">
    <thead>
        <th class="text-right">rank</th>
        <th>player</th>
        <th>corp</th>
        <th>runner</th>
        <th></th>
    </thead>
    <tbody>
    @for ($i = 0; $i < count($entries); $i++)
        @forelse ($entries[$i] as $entry)
            @if (count($entries[$i])>1)
                <tr class="danger">
                    <td class="text-right"><i class="fa fa-exclamation-triangle text-danger" title="conflict"></i> #{{ $i+1 }}</td>
            @elseif ($user_entry && count($entry) && $entry[$rank] == $user_entry[$rank])
                <tr class="info">
                    <td class="text-right">#{{ $i+1 }}</td>
            @else
                <tr>
                    <td class="text-right">#{{ $i+1 }}</td>
            @endif

            @if ($entry->player)
                <td>{{ $entry->player->name }}</td>
            @elseif ($entry->import_username)
                <td class="import-user">{{ $entry->import_username }}</td>
            @else
                <td></td>
            @endif

            <td>
                <img src="/img/ids/{{ $entry->corp_deck_identity }}.png">&nbsp;
                @if ($entry->corp_deck_id)
                    <a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->corp_deck_id }}">
                        {{ $entry->corp_deck_title }}
                    </a>
                @else
                    {{ $entry->corp_deck_title }}
                @endif
            </td>
            <td>
                <img src="/img/ids/{{ $entry->runner_deck_identity }}.png">&nbsp;
                @if ($entry->runner_deck_id)
                    <a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->runner_deck_id }}">
                        {{ $entry->runner_deck_title }}
                    </a>
                @else
                    {{ $entry->runner_deck_title }}
                @endif
            </td>
            @if ($entry->runner_deck_id && (($user && ($user->admin || $user->id == $creator))
                || ($user_entry && count($entry) && $entry->user == $user_entry->user)))
                <td class="text-right">
                    {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$entry->id"]) !!}
                        @if ($user_entry && $entry->user && count($entry) && $entry->user == $user_entry->user)
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove my claim', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                        @else
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove claim', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                        @endif
                    {!! Form::close() !!}
                </td>
            @else
                <td></td>
            @endif
        @empty
            <tr>
                <td class="text-right">#{{ $i+1 }}</td>
                <td></td>
                <td><em><small>unclaimed</small></em></td>
                <td><em><small>unclaimed</small></em></td>
                <td></td>
        @endforelse
        </tr>
    @endfor
    </tbody>
</table>