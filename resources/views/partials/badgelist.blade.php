<div class="row p-b-1">
    <div class="col-xs-2 text-xs-right">
        <img src="/img/badges/{{ $badge->filename }}"/>
    </div>
    <div class="col-xs-10">
        <strong>{{ $badge->name }}</strong><br/>
        {{ $badge->description }}<br/>
        <div class="small-text">(belonging to {{ $badge->users()->count() }} user{{ $badge->users()->count() > 1 ? 's' : '' }})</div>
    </div>
</div>