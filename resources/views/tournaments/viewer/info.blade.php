{{--Sidebar tournament infos--}}
<div class="bracket">
    {{--Approval--}}
    @if ($tournament->approved === null)
        <div class="alert alert-warning" id="approval-needed">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
            This tournament hasn't been approved by the admins yet.
        </div>
    @elseif ($tournament->approved == 0)
        <div class="alert alert-danger" id="approval-rejected">
            <i class="fa fa-thumbs-down" aria-hidden="true"></i>
            This tournament has been rejected by an admin.
            Only the tournament creator and the admins can see this tournament.
            Please try to fix the issue or get in contact with the admins.
        </div>
    @endif
    {{--Location, date--}}
    <h5>
        @unless($tournament->tournament_type_id == 7)
            <span id="tournament-location">
                {{ $tournament->location() }}
            </span>
            <br/>
        @endunless
        <span id="tournament-date">
            @if ($tournament->date)
                @if(@$tournament->end_date)
                    {{ substr($tournament->date,0,10) }} - {{ $tournament->end_date }}
                @else
                    {{ $tournament->date }}
                @endif
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
        <p>
            <strong>Legal cardpool up to:</strong> <span id="cardpool"><em>{{ $tournament->cardpool->name }}</em></span><br/>
            <strong>Format:</strong> <span id="tournament-format">{{ $format }}</span>
            @include('partials.popover', ['direction' => 'right', 'content' =>
                        '<ul>
                            <li><strong>Standard:</strong> Most tournaments are like this. <em>Tournament Regulations</em> by FFG, the latest <em>MWL</em> and <em>FAQ</em> are in effect.</li>
                            <li><strong>Cache Refresh:</strong> 1 Core Set + 1 Deluxe Expansion + 1 Terminal Directive + current Data Cycle + second-most current Data Cycle. Latest MWL plus additional rules apply.</li>
                            <li><strong>1.1.1.1:</strong> 1 Core Set + 1 Deluxe Expansion + 1 Data Pack + 1 Card.</li>
                            <li><strong>Draft:</strong> Drafting with the official FFG draft packs.</li>
                            <li><strong>Cube Draft:</strong> Drafting with a custom draft pool.</li>
                        </ul>
                        Additional tournament rules are stated in the tournament description.'])
        </p>
    @endif
    @if($tournament->decklist == 1)
        <p><strong><u><span id="decklist-mandatory">decklist is mandatory!</span></u></strong></p>
    @else
        <p><em id="decklist-not-mandatory">decklist is not required</em></p>
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
        <div class="map-wrapper-small map-placeholder">
            {{--Map placeholder--}}
            <div class="map-placeholder-cell">
                <button id="button-show-map" class="btn btn-primary" onclick="initializeMap()" disabled>
                    <i class="fa fa-eye" aria-hidden="true"></i>
                    Show map
                </button>
            </div>
            {{--Map--}}
            <div id="map"></div>
        </div>
    @endif
</div>
