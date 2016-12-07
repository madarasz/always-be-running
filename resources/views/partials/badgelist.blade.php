<div class="row p-b-1">
    <div class="col-xs-2 text-xs-right">
        <img src="/img/badges/{{ $badge->filename }}"/>
    </div>
    <div class="col-xs-10">
        <strong>{{ $badge->name }}</strong><br/>
        {{ $badge->description }}<br/>
        <div class="small-text">
            <?php $bcount = $badge->users()->count() ?>
            belonging to {{ $bcount }} user{{ $bcount > 1 ? 's' : '' }}{{ $bcount ? ':' : '' }}
            @if ($bcount)
                @foreach($badge->users()->get() as $key=>$badgeuser)
                    <a href="/profile/{{ $badgeuser->id }}">{{ $badgeuser->name }}</a>{{ $key != $bcount-1 ? ',' : ''}}
                @endforeach
            @endif
        </div>
    </div>
</div>
