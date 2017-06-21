{{--List of registered players--}}
<h6>Registered players {{ $regcount > 0 ? '('.$regcount.')' : '' }}</h6>
@if ($regcount)
    <ul id="registered-players">
        @foreach ($entries as $entry)
            @if ($entry->player)
                <li><a href="/profile/{{ $entry->player->id }}" class="{{ $entry->player->linkClass() }}">{{ $entry->player->displayUsername() }}</a></li>
            @endif
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
                    <span class="btn btn-danger disabled" id="unregister-disabled"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</span><br/>
                    <small><em>remove your claim first</em></small>
                @else
                    <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
                @endif
            @else
                <a href="{{"/tournaments/$tournament->id/register"}}" class="btn btn-primary" id="register"><i class="fa fa-plus-circle" aria-hidden="true"></i> Register</a>
            @endif
        @else
            <div class="text-xs-center p-b-1" id="suggest-login2">
                <a href="/oauth2/redirect">Login via NetrunnerDB</a> to register for this tournament.
            </div>
        @endif
    </div>
@elseif ($user_entry && !$user_entry->rank)
    <div class="text-xs-center">
        <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger" id="unregister"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
    </div>
@endif