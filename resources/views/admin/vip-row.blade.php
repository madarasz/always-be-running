<tr>
    <td>
        <a href="/profile/{{ $vip->id }}"{{ $vip->supporter ? 'class=supporter' : '' }}>
            {{ $vip->displayUsername() }}
        </a>
    </td>
    <td class="text-xs-center">{{ $vip->claims()->count() }}</td>
    <td class="text-xs-center">{{ $vip->tournamentsCreated()->count() }}</td>
    <td class="text-xs-right">{{ $vip->reputation }}</td>
    <td class="text-xs-center">{{ $vip->badges()->count() }}</td>
    <td>
        @if($vip->country)
            <img src="/img/flags/{{ $vip->country->flag }}"/>
            {{ $vip->country->name }}
        @endif
    </td>
    <td>{{ $vip->email }}</td>
    <td>{{ $vip->updated_at }}</td>
</tr>