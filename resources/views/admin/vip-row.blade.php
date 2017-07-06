<tr>
    <td>
        <a href="/profile/{{ $vip->id }}" class="{{ $vip->linkClass() }}">
            {{ $vip->displayUsername() }}
        </a>
    </td>
    <td class="text-xs-center">{{ $vip->claims()->count() }}</td>
    <td class="text-xs-center">{{ $vip->tournamentsCreated()->count() }}</td>
    <td class="text-xs-center">{{ $vip->communityCount() }}</td>
    <td class="text-xs-right">{{ $vip->reputation }}</td>
    <td class="text-xs-center">{{ $vip->badges()->count() }}</td>
    <td>
        @if($vip->country)
            <img src="/img/flags/{{ $vip->country->flag }}"/>
            {{ $vip->country->name }}
        @endif
    </td>
    <td>{{ $vip->email }}</td>
    <td>{{ substr($vip->updated_at,0,10) }}</td>
</tr>