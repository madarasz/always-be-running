<div class="bracket">
    <h5 class="p-b-1">
        <i class="fa fa-trophy" aria-hidden="true"></i>
        Badges
        @include('partials.popover', ['direction' => 'top', 'content' =>
                'You receive badges as achiements for various activities.<br/>
                <br/>
                <a href="/badges/">full list of badges</a>'])
    </h5>
    <div class="text-xs-center">
        @forelse($user->badges as $badge)
            <div class="{{ (@$page_section == 'profile' && !$badge->pivot->seen) ? 'new-badge notif-green' : 'inline-block'}}">
                <img src="/img/badges/{{ $badge->filename }}" data-html="true"
                     data-toggle="tooltip" data-placement="top"
                     title="<strong>{{ $badge->name }}</strong><br/>{{ $badge->description }}"/>
            </div>
        @empty
            <div class="m-b-2 font-italic text-xs-center">no badges yet</div>
        @endforelse
    </div>
</div>