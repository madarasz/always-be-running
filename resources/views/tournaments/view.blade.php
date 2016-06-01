@extends('layout.general')

@section('content')
    {{--Header--}}
    <h3 class="page-header">
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="pull-right" id="control-buttons">
                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id"]) !!}
                    {{--Edit--}}
                    <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    {{--Approval --}}
                    @if ($user && $user->admin)
                        <a href="/tournaments/{{ $tournament->id }}/approve" class="btn btn-success"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</a>
                        <a href="/tournaments/{{ $tournament->id }}/reject" class="btn btn-danger"><i class="fa fa-thumbs-down" aria-hidden="true"></i> Reject</a>
                    @endif
                    {{--Delete--}}
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger')) !!}
                {!! Form::close() !!}
            </div>
        @endif
        {{ $tournament->title }}<br/>
        <small>{{ $type }} - <em>created by {{ $tournament->user->name }}</em></small>
    </h3>
    @include('partials.message')
    <div class="row">
        {{--Tournament info--}}
        <div class="col-md-4 col-xs-12">
            <h4>
                @unless($tournament->tournament_type_id == 6)
                    {{ $tournament->location_city }}, {{$tournament->location_country == 840 && $tournament->location_us_state !=52 ? "$state_name, " : ''}}{{ $country_name }} -
                @endunless
                {{ $tournament->date }}<br/>
            </h4>
            {{--Approval--}}
            @if ($tournament->approved === null)
                <div class="alert alert-warning" id="approval-needed">
                    <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    This tournament haven't been approved by the admins yet.
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
            {{--Details--}}
            <p><strong>Legal cardpool up to:</strong> <em>{{ $tournament->cardpool->name }}</em></p>
            @unless($tournament->description === '')
                <div class="panel panel-default"><div class="panel-body">{!! nl2br(e($tournament->description)) !!}</div></div>
            @endunless
            @if($tournament->decklist == 1)
                <p><strong><u>decklist is mandatory!</u></strong></p>
            @endif
            <p>
                @unless($tournament->start_time === '')
                    <strong>Starting time</strong>: {{ $tournament->start_time }} (local time)<br/>
                @endunless
                @unless($tournament->location_store === '')
                    <strong>Store/venue</strong>: {{ $tournament->location_store }}<br/>
                @endunless
                @unless($tournament->location_address === '')
                    <strong>Address</strong>: {{ $tournament->location_address }}<br/>
                @endunless
            </p>
            @if($tournament->display_map)
                <iframe id="map" width="100%" frameborder="0" style="border:0" allowfullscreen></iframe>
                <script type="text/javascript">
                    function initPage() {
                        document.getElementById('map').src = "https://www.google.com/maps/embed/v1/search?q=" +
                                encodeURIComponent(calculateAddress('{{ $tournament->country->name }}',
                                        '{{ $tournament->state->name }}', '{{ $tournament->location_city }}',
                                        '{{ $tournament->location_store }}', '{{ $tournament->location_address }}')) +
                                "&key=" + '{{ ENV('GOOGLE_MAPS_API') }}';
                    }
                    window.addEventListener("load", initPage, false);
                </script>
            @endif
        </div>
        {{--Standings and claims--}}
        <div class="col-md-8 col-xs-12">
            @if ($tournament->concluded)
                <p>
                    {{--Conflict--}}
                    @if ($tournament->conflict)
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle text-danger" title="conflict"></i>
                            This tournament has conflicting claims.<br/>
                            Claims can be removed by the tournament creator, admins or claim owners.
                        </div>
                    @endif
                    {{--Player numbers--}}
                    <strong>Number of players</strong>: {{ $tournament->players_number }}<br/>
                    @if ($tournament->top_number)
                        <strong>Top cut players</strong>: {{ $tournament->top_number }}<br/>
                    @else
                        <em>only swiss rounds, no top cut</em><br/>
                    @endif
                    {{--User claim--}}
                    @if ($user)
                        <hr/>
                        <strong>Your claim:</strong><br/><br/>
                        {{--Existing claim--}}
                        @if ($user_entry && $user_entry->rank)
                            <ul>
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
                                    <a href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->corp_deck_id }}">
                                        {{ $user_entry->corp_deck_title }}
                                    </a>
                                </li>
                                <li>
                                    Runner deck:
                                    <a  href="{{ "https://netrunnerdb.com/en/decklist/".$user_entry->runner_deck_id }}">
                                        {{ $user_entry->runner_deck_title }}
                                    </a>
                                </li>
                            </ul>
                            <div class="text-center">
                                {!! Form::open(['method' => 'DELETE', 'url' => "/entries/$user_entry->id"]) !!}
                                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Remove my claim', array('type' => 'submit', 'class' => 'btn btn-danger')) !!}
                                {!! Form::close() !!}
                            </div>
                        {{--Creating new claim--}}
                        @else
                            @include('errors.list')
                            {!! Form::open(['url' => "/tournaments/$tournament->id/claim"]) !!}
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
                                                        <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>
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
                                                <br/>
                                                <em>You don't have any published decklist on NetrunnerDB.</em>
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
                                                        <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>
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
                                                <br/>
                                                <em>You don't have any published decklist on NetrunnerDB.</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i> Claim spot
                                    </button>
                                </div>
                            {!! Form::close() !!}
                        @endif
                    @endif

                </p>
                <hr/>
                {{--Tables of tournament standings --}}
                <p>
                    @if ($tournament->top_number)
                        <h5>Top cut</h5>
                        @include('tournaments.partials.entries',
                            ['entries' => $entries_top, 'user_entry' => $user_entry, 'rank' => 'rank_top',
                            'creator' => $tournament->creator])
                        <h5>Swiss rounds</h5>
                    @endif
                    @include('tournaments.partials.entries',
                        ['entries' => $entries_swiss, 'user_entry' => $user_entry, 'rank' => 'rank',
                        'creator' => $tournament->creator])
                </p>
                <hr/>
            {{--Tournament is due--}}
            @elseif($tournament->date <= $nowdate)
                <div class="alert alert-warning">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    This tournament is due for completion.<br/>
                    The tournament creator should set it to concluded and record player number, so players make claims.
                </div>
            @endif
            {{--List of registered players--}}
            <p>
                <strong>Registered players</strong>
                @if (count($entries) > 0)
                    ({{count($entries)}})
                    <br/>
                    <ul>
                    @foreach ($entries as $entry)
                        <li>{{ $entry->player->name }}</li>
                    @endforeach
                    </ul>
                @else
                    - <em>no players yet</em>
                @endif
                <div class="text-center">
                    @if ($user)
                        @if ($user_entry)
                            @if ($user_entry->rank)
                                <span class="btn btn-danger disabled"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</span><br/>
                                <small><em>remove your claim first</em></small>
                            @else
                                <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
                            @endif
                        @else
                            <a href="{{"/tournaments/$tournament->id/register"}}" class="btn btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i> Register</a>
                        @endif
                    @endif
                </div>
            </p>
        </div>
    </div>
@stop

