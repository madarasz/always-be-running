{{--Supporter strip--}}
@if ($user->supporter)
    <div class="alert alert-warning">
        <i class="fa fa-star" aria-hidden="true"></i>
        This user <a href="/support-me" class="supporter">supports</a> AlwaysBeRunning.net
    </div>
@endif
{{--Admin strip--}}
@if ($user->admin)
    <div class="alert alert-info">
        <i class="fa fa-certificate" aria-hidden="true"></i>
        This user is an admin.
    </div>
@endif
{{--Badge notification--}}
<div class="alert alert-success view-indicator notif-green notif-badge-page hidden-xs-up" id="notif-profile" data-badge="">
    You have new badges.
</div>
{{--User info--}}
<div class="bracket">
    <h5 class="p-b-1">
        <i class="fa fa-user" aria-hidden="true"></i>
        User
    </h5>
    <div class="text-xs-center p-b-1">
        <h6 class="{{ $user->linkClass() }}" v-cloak>@{{ displayUserName }}</h6>
        <div class="user-counts">
            {{ $created_count }} tournament{{ $created_count > 1 ? 's' : '' }} created<br/>
            {{ $claim_count }} tournament claim{{ $claim_count > 1 ? 's' : '' }}<br/>
            {{ $user->published_decks }} published deck{{ $user->published_decks > 1 ? 's' : '' }}
            @if ($user->private_decks)
                <br/>
                {{ $user->private_decks }} private deck{{ $user->private_decks > 1 ? 's' : '' }}
            @endif
            @if ($user->reputation)
                <br/>
                {{ $user->reputation }} reputation on NetrunnerDB
                @include('partials.popover', ['direction' => 'top', 'content' =>
                'You receive reputation on NetrunnerDB for the following:<br/>
                +5 point for each favorite on your decklist<br/>
                +1 point for each like on your decklist<br/>
                +1 point for each like on your card review'])
            @endif
            {{--Admin info--}}
            @if (Auth::user() && Auth::user()->admin)
                <br/>
                <strong>admin info - email:</strong> {{ $user->email }}
                <br/>
                <strong>admin info - first login:</strong> {{ $user->created_at }}
                <br/>
                <strong>admin info - last login:</strong> {{ $user->updated_at }}
            @endif
        </div>
    </div>
</div>