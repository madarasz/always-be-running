<table class="table table-condensed table-striped">
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
            <td>{{ $entry->player->name }}</td>
            <td><a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->corp_deck_id }}">{{ $entry->corp_deck_title }}</a></td>
            <td><a href="{{ "https://netrunnerdb.com/en/decklist/".$entry->runner_deck_id }}">{{ $entry->runner_deck_title }}</a></td>
            @if (($user && ($user->admin || $user->id == $creator))
                || ($user_entry && count($entry) && $entry->user == $user_entry->user))
                <td class="text-right">
                    {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$entry->id"]) !!}
                        @if ($user_entry && count($entry) && $entry->user == $user_entry->user)
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