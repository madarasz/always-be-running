{{--Sidebar tournament infos--}}
<div class="bracket">
    {{--Approval--}}
    @if ($tournament->approved === null)
        <div class="alert alert-warning" id="approval-needed">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            This tournament hasn't been approved by the admins yet.
            You can already share it, though it's not appearing in any tournament lists.
        </div>
    @elseif ($tournament->approved == 0)
        <div class="alert alert-danger" id="approval-rejected">
            <i class="fa fa-thumbs-down" aria-hidden="true"></i>
            This tournament has been rejected by an admin.
            Only the tournament creator and the admins can see this tournament.
            Please try to fix the issue.
        </div>
    @endif
    {{--Location, date--}}
    <h5>
        @unless($tournament->tournament_type_id == 7)
            <span id="tournament-location">
                            {{ $tournament->location_country }}, {{$tournament->location_country === 'United States' ? $tournament->location_state.', ' : ''}}{{ $tournament->location_city }}
                        </span>
            <br/>
        @endunless
        <span id="tournament-date">
                        @if ($tournament->date)
                {{ $tournament->date }}
            @else
                <br/>
                <em>recurring: {{ $tournament->recurDay() }}</em>
            @endif
                    </span>
    </h5>
    {{--Details--}}
    @if ($tournament->link_facebook)
        <p><strong><a href="{{ $tournament->link_facebook }}" rel="nofollow">
                    Facebook {{ strpos($tournament->link_facebook, 'event') ? 'event' : 'group' }}
                </a></strong></p>
    @endif
    @if ($tournament->date)
        <p><strong>Legal cardpool up to:</strong> <span id="cardpool"><em>{{ $tournament->cardpool->name }}</em></span></p>
    @endif
    @if($tournament->decklist == 1)
        <p><strong><u><span id="decklist-mandatory">decklist is mandatory!</span></u></strong></p>
    @endif
    <p>
        @unless($tournament->start_time === '')
            <strong>Starting time</strong>: <span id="start-time">{{ $tournament->start_time }}</span> (local time)<br/>
        @endunless
        @unless($tournament->location_store === '')
            <strong>Store/venue</strong>: <span id="store">{{ $tournament->location_store }}</span><br/>
        @endunless
        @unless($tournament->location_address === '')
            <strong>Address</strong>: <span id="address">{{ $tournament->location_address }}</span><br/>
        @endunless
        @unless($tournament->contact === '')
            <strong>Contact</strong>: <span id="contact">{{ $tournament->contact }}</span><br/>
        @endunless
    </p>
    {{--Google map--}}
    @if($tournament->tournament_type_id != 7)
        <div class="map-wrapper-small">
            <div id="map"></div>
        </div>
    @endif
</div>
