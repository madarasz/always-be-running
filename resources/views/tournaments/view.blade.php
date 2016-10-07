@extends('layout.general')

@section('content')
    @include('tournaments.partials.modal')
    {{--Header--}}
    <h4 class="page-header">
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="pull-right" id="control-buttons">
                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id"]) !!}
                    {{--Edit--}}
                    <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary" id="edit-button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    {{--Approval --}}
                    @if ($user && $user->admin)
                        <a href="/tournaments/{{ $tournament->id }}/approve" class="btn btn-success" id="approve-button"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</a>
                        <a href="/tournaments/{{ $tournament->id }}/reject" class="btn btn-danger" id="reject-button"><i class="fa fa-thumbs-down" aria-hidden="true"></i> Reject</a>
                    @endif
                    {{--Delete--}}
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger', 'id' => 'delete-button')) !!}
                {!! Form::close() !!}
            </div>
        @endif
        <span id="tournament-title">{{ $tournament->title }}</span><br/>
        <small>
            <span id="tournament-type">{{ $type }}</span> -
            <em>
                created by
                <span id="tournament-creator">{{ $tournament->user->name }}</span>
            </em>
        </small>
    </h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--Tournament info--}}
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
                        <span id="tournament-city">
                            {{$tournament->location_country === 'United States' ? $tournament->location_state : ''}} {{ $tournament->location_country }}, {{ $tournament->location_city }}
                        </span>
                        <br/>
                    @endunless
                    <span id="tournament-date">{{ $tournament->date }}</span>
                </h5>
                {{--Details--}}
                <p><strong>Legal cardpool up to:</strong> <span id="cardpool"><em>{{ $tournament->cardpool->name }}</em></span></p>
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
                <div class="map-wrapper-small">
                    <div id="map"></div>
                </div>
            </div>
            {{--Statistics--}}
            @if ($tournament->concluded)
            <div class="bracket">
                <h5>
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    Statistics
                </h5>
                @include('partials.tobedeveloped')
            </div>
            @endif
        </div>
        {{--Standings and claims--}}
        <div class="col-md-8 col-xs-12">
            {{--Tournament description--}}
            @unless($tournament->description === '')
                <div class="bracket">
                    {{--<h5>Description</h5>--}}
                    <div id="description">
                        {!! nl2br(e($tournament->description)) !!}
                    </div>
                </div>
            @endunless
            <div class="bracket">
            @if ($tournament->concluded)
                    <h5>
                        <i class="fa fa-list-ol" aria-hidden="true"></i>
                        Results
                    </h5>
                    {{--Conflict--}}
                    @if ($tournament->conflict)
                        <div class="alert alert-danger" id="conflict-warning">
                            <i class="fa fa-exclamation-triangle text-danger" title="conflict"></i>
                            This tournament has conflicting claims.<br/>
                            Claims can be removed by the tournament creator, admins or claim owners.
                        </div>
                    @endif
                    {{--Player numbers--}}
                    <div id="player-numbers">
                        <strong>Number of players</strong>: {{ $tournament->players_number }}<br/>
                        @if ($tournament->top_number)
                            <span id="top-player-numbers">
                                <strong>Top cut players</strong>: {{ $tournament->top_number }}
                            </span><br/>
                        @else
                            <em>only swiss rounds, no top cut</em><br/>
                        @endif
                    </div>
                    {{--User claim--}}
                    @if ($user)
                        <hr/>
                        <h6>Your claim</h6>
                        {{--Existing claim--}}
                        @if ($user_entry && $user_entry->runner_deck_id)
                            <ul id="player-claim">
                                @if ($tournament->top_number)
                                    <li>Top cut rank:
                                        @if ($user_entry->rank_top)
                                            <strong>#{{ $user_entry->rank_top}}</strong>
                                        @else
                                            <em>none</em>
                                        @endif
                                    </li>
                                @endif
                                <li>Swiss rounds rank: <strong>#{{ $user_entry->rank }}</strong></li>
                                <li>
                                    Corporation deck:
                                    <img src="/img/ids/{{ $user_entry->corp_deck_identity }}.png">&nbsp;<a href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->corp_deck_id }}">
                                        {{ $user_entry->corp_deck_title }}
                                    </a>
                                </li>
                                <li>
                                    Runner deck:
                                    <img src="/img/ids/{{ $user_entry->runner_deck_identity }}.png">&nbsp;<a  href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->runner_deck_id }}">
                                        {{ $user_entry->runner_deck_title }}
                                    </a>
                                </li>
                            </ul>
                            <div class="text-xs-center">
                                {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$user_entry->id"]) !!}
                                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove my claim',
                                    array('type' => 'submit', 'class' => 'btn btn-danger', 'id' => 'remove-claim')) !!}
                                {!! Form::close() !!}
                            </div>
                        {{--Creating new claim--}}
                        @else
                            @include('errors.list')
                            {!! Form::open(['url' => "/tournaments/$tournament->id/claim", 'id' => 'create-claim']) !!}
                                {!! Form::hidden('top_number', $tournament->top_number) !!}
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('rank', 'rank after swiss rounds') !!}
                                            {!! Form::select('rank', array_combine(range(1, $tournament->players_number),
                                                range(1, $tournament->players_number)), old('rank'), ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    @if ($tournament->top_number)
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('rank_top', 'rank after top cut') !!}
                                                {!! Form::select('rank_top',
                                                    array_combine(
                                                        array_merge([0],range(1, $tournament->top_number)),
                                                        array_merge(['below top cut'], range(1, $tournament->top_number))),
                                                    old('rank_top'), ['class' => 'form-control']) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                {{--Dropdown selectors for decks--}}
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('corp_deck', 'corporation deck') !!}
                                            @if (count($decks['public']['corp'])>0)
                                                <select class="form-control" id="corp_deck" name="corp_deck">
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--<optgroup label="Public decklists">--}}
                                                    {{--@endif--}}
                                                    @foreach ($decks['public']['corp'] as $deck)
                                                        <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }}, "identity": "{{ $deck['identity'] }}" }'>{{ $deck['name'] }}</option>
                                                    @endforeach
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--</optgroup>--}}
                                                        {{--<optgroup label="Shared private decklists">--}}
                                                    {{--@endif--}}
                                                    {{--@foreach ($decks['private']['corp'] as $deck)--}}
                                                        {{--<option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>--}}
                                                    {{--@endforeach--}}
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--</optgroup>--}}
                                                    {{--@endif--}}
                                                </select>
                                            @else
                                                <div class="alert alert-danger" id="no-corp-deck">
                                                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                                    You don't have any published decklist on NetrunnerDB.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('runner_deck', 'runner deck') !!}
                                            @if (count($decks['public']['runner'])>0)
                                                <select class="form-control" id="runner_deck" name="runner_deck">
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--<optgroup label="Public decklists">--}}
                                                    {{--@endif--}}
                                                    @foreach ($decks['public']['runner'] as $deck)
                                                        <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }}, "identity": "{{ $deck['identity'] }}" }'>{{ $deck['name'] }}</option>
                                                    @endforeach
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--</optgroup>--}}
                                                        {{--<optgroup label="Shared private decklists">--}}
                                                    {{--@endif--}}
                                                    {{--@foreach ($decks['private']['runner'] as $deck)--}}
                                                        {{--<option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>--}}
                                                    {{--@endforeach--}}
                                                    {{--@if ($decks_two_types)--}}
                                                        {{--</optgroup>--}}
                                                    {{--@endif--}}
                                                </select>
                                            @else
                                                <div class="alert alert-danger" id="no-runner-deck">
                                                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                                    You don't have any published decklist on NetrunnerDB.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs-center">
                                    <button type="submit" class="btn btn-success" id="submit-claim">
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i> Claim spot
                                    </button>
                                </div>
                            {!! Form::close() !!}
                        @endif
                    @endif
                <hr/>
                {{--Import NRTM, Clear anonym claims--}}
                @if ($user && ($user->admin || $user->id == $tournament->creator))
                    <div class="text-xs-center">
                        {{--Import NRTM--}}
                        <button class="btn btn-conclude btn-xs" data-toggle="modal" data-hide-manual="true"
                                data-target="#concludeModal" data-tournament-id="{{$tournament->id}}"
                                data-subtitle="{{$tournament->title.' - '.$tournament->date}}">
                            <i class="fa fa-check" aria-hidden="true"></i> Import NRTM results
                        </button>
                        {{--Clear NRTM--}}
                        {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id/clearanonym"]) !!}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove all claims by NRTM import', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                        {!! Form::close() !!}
                    </div>
                @endif
                {{--Tables of tournament standings --}}
                @if ($tournament->top_number)
                    <h6>Top cut</h6>
                    @include('tournaments.partials.entries',
                        ['entries' => $entries_top, 'user_entry' => $user_entry, 'rank' => 'rank_top',
                        'creator' => $tournament->creator, 'id' => 'entries-top'])
                    <hr/>
                @endif
                <h6>Swiss rounds</h6>
                @include('tournaments.partials.entries',
                    ['entries' => $entries_swiss, 'user_entry' => $user_entry, 'rank' => 'rank',
                    'creator' => $tournament->creator, 'id' => 'entries-swiss'])
            {{--Tournament is due--}}
            @elseif($tournament->date <= $nowdate)
                <h5>
                    <i class="fa fa-list-ol" aria-hidden="true"></i>
                    Results
                </h5>
                <div class="alert alert-warning" id="due-warning">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    This tournament is due for completion.<br/>
                    The tournament creator should set it to 'concluded', so players make claims.
                </div>
                {{--Conclude modal, button--}}
                <div class="text-xs-center">
                    <button class="btn btn-conclude" data-toggle="modal" data-target="#concludeModal"
                            data-tournament-id="{{$tournament->id}}"
                            data-subtitle="{{$tournament->title.' - '.$tournament->date}}">
                        <i class="fa fa-check" aria-hidden="true"></i> Conclude
                    </button>
                </div>
            @endif
            {{--List of registered players--}}
            <hr/>
            <h6>Registered players {{ $regcount > 0 ? '('.$regcount.')' : '' }}</h6>
            @if (count($entries) > 0)
                <ul id="registered-players">
                @foreach ($entries as $entry)
                    @if ($entry->player)
                        <li>{{ $entry->player->name }}</li>
                    @endif
                @endforeach
                </ul>
            @else
                <p><em id="no-registered-players">no players yet</em></p>
            @endif
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
                @endif
            </div>
            </div>
            {{--Comments--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-comments-o" aria-hidden="true"></i>
                    Comments
                </h5>
                @include('partials.tobedeveloped')
            </div>
        </div>
    </div>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&libraries=places&callback=initializeMap">
    </script>
    <script type="text/javascript">
        var map, marker;

        function initializeMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297},
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                streetViewControl: false,
                mapTypeControl: false
            });

            marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });

            var service = new google.maps.places.PlacesService(map);
            service.getDetails({placeId: '{{ $tournament->location_place_id }}'}, function(place, status){
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    renderPlace(place, marker, map)
                }
            });
        }
    </script>
@stop

