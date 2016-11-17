<div class="row p-b-1">
    <div class="col-xs-2 text-xs-right">
        <img src="/img/badges/{{ $badge->filename }}"/>
    </div>
    <div class="col-xs-10">
        <strong>{{ $badge->name }}</strong><br/>
        {{ $badge->description }}<br/>
        <div class="small-text">
            (belonging to {{ $badge->users()->count() }} user{{ $badge->users()->count() > 1 ? 's' : '' }})
            @if (Auth::user() && Auth::user()->admin && $badge->users()->count())
                <br/>
                <strong>admin user info:</strong>
                @foreach($badge->users()->get() as $badgeuser)
                    <a href="/profile/{{ $badgeuser->id }}">{{ $badgeuser->name }}</a>,
                @endforeach
            @endif
        </div>
    </div>
</div>
