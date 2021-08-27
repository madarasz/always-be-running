{{--List of registered players--}}
<h6>Players going {{ $regcount > 0 ? '('.$regcount.')' : '' }}</h6>
@if ($regcount)
    <ul id="registered-players">
        @foreach ($registered as $player)
            <li><a href="/profile/{{ $player->id }}" class="{{ $player->linkClass() }}">{{ $player->displayUsername() }}</a></li>
        @endforeach
    </ul>
@else
    <p><em id="no-registered-players">no players yet</em></p>
@endif
{{--Register/unregister--}}
@if (!$tournament->concluded)
    <div class="text-xs-center">
        @if ($user)
            @if ($user_entry)
                @if ($user_entry->rank)
                    <span class="btn btn-danger disabled" id="unregister-disabled"><i class="fa fa-minus-circle" aria-hidden="true"></i> Not going</span><br/>
                    <small><em>remove your claim first</em></small>
                @else
                    <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Not going</a>
                @endif
            @else
                <a href="{{"/tournaments/$tournament->id/register"}}" class="btn btn-primary" id="register"><i class="fa fa-plus-circle" aria-hidden="true"></i> Going</a>
            @endif
        @else
            <div class="text-xs-center p-b-1" id="suggest-login2">
                <a href="/oauth2/redirect">Login via NetrunnerDB</a> to register for this tournament.
            </div>
        @endif
    </div>
@elseif ($user_entry && !$user_entry->rank)
    <div class="text-xs-center">
        <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Not going</a>
    </div>
@endif