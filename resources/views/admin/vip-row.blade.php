<tr>
    <td>
        <a href="/profile/{{ $vip->id }}" class="{{ $vip->abr_link_class }}">
            {{ $vip->displayUsername() }}
        </a>
    </td>
    <td class="text-xs-center">{{ $vip->claims_count }}</td>
    <td class="text-xs-center">{{ $vip->tournaments_created_count }}</td>
    <td class="text-xs-center">{{ $vip->claimers_count }}</td>
    <td class="text-xs-right">{{ $vip->reputation }}</td>
    <td class="text-xs-center">{{ $vip->badges_count }}</td>
    <td>
        @if($vip->country)
            <img src="/img/flags/{{ $vip->country->flag }}"/>
            {{ $vip->country->name }}
        @endif
    </td>
    <td>{{ $vip->email }}</td>
    <td>{{ substr($vip->updated_at,0,10) }}</td>
</tr>
