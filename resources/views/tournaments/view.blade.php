@extends('layout.general')

@section('content')
    <h3 class="page-header">
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="pull-right">
                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id"]) !!}
                    <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger')) !!}
                {!! Form::close() !!}
            </div>
        @endif
        {{ $tournament->title }}<br/>
        <small>{{ $type }} - <em>created by {{ $tournament->creator }}</em></small>
    </h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <h4>
                {{ $tournament->location_city }}, {{$tournament->location_country == 840 && $tournament->location_us_state !=52 ? "$state_name, " : ''}}{{ $country_name }}
             - {{ $tournament->date }}<br/>

            </h4>
            @unless($tournament->description === '')
                <p>{!! nl2br(e($tournament->description)) !!}</p>
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

        </div>
        <div class="col-md-8 col-xs-12">
            @if ($tournament->concluded)
                <p>
                    <strong>Number of players</strong>: {{ $tournament->players_number }}<br/>
                    @if ($tournament->top_number)
                        <strong>Top cut players:</strong> {{ $tournament->top_number }}<br/>
                    @endif
                    {{--User claim--}}
                    @if ($user)
                        <hr/>
                        <strong>Your claim:</strong><br/><br/>
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
                                <li>Corporation deck: <a>{{ $user_entry->corp_deck_title }}</a></li>
                                <li>Runner deck: <a>{{ $user_entry->runner_deck_title }}</a></li>
                            </ul>
                            <div class="text-center">
                                <a href="{{ "/tournaments/$tournament->id/unclaim" }}" class="btn btn-danger">
                                    <i class="fa fa-trash" aria-hidden="true"></i> Remove claim
                                </a>
                            </div>
                        @else
                            {!! Form::open(['url' => "/tournaments/$tournament->id/claim"]) !!}
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('rank', 'rank after swiss rounds') !!}
                                            <select class="form-control" id="rank" name="rank">
                                                @foreach (range(1, $tournament->players_number) as $rank)
                                                    <option value="{{ $rank }}">{{ $rank }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @if ($tournament->top_number)
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('rank_top', 'rank after top cut') !!}
                                                <select class="form-control" id="rank_top" name="rank_top">
                                                    <option value="">below top cut</option>
                                                    @foreach (range(1, $tournament->top_number) as $rank)
                                                        <option value="{{ $rank }}">{{ $rank }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    {!! Form::label('corp_deck', 'corporation deck') !!}
                                    <select class="form-control" id="corp_deck" name="corp_deck">
                                        @foreach ($decks as $deck)
                                            <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('runner_deck', 'runner deck') !!}
                                    <select class="form-control" id="runner_deck" name="runner_deck">
                                        @foreach ($decks as $deck)
                                            <option value='{ "title": "{{ addslashes($deck['name']) }}", "id": {{ $deck['id'] }} }'>{{ $deck['name'] }}</option>
                                        @endforeach
                                    </select>
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
                {{--Tables of tournament ranks --}}
                <p>
                    @if ($tournament->top_number)
                        <h5>Top cut</h5>
                        <table class="table table-condensed table-striped">
                            <thead>
                                <th class="text-right">rank</th>
                                <th>player</th>
                                <th>corp</th>
                                <th>runner</th>
                            </thead>
                            <tbody>
                            @for ($i = 0; $i < count($entries_top); $i++)
                                @if ($user_entry && count($entries_top[$i]) && $entries_top[$i]->rank_top == $user_entry->rank_top)
                                    <tr class="info">
                                @else
                                    <tr>
                                @endif
                                    <td class="text-right">#{{ $i+1 }}</td>
                                    @if (count($entries_top[$i]))
                                        <td>{{ $entries_top[$i]->user }}</td>
                                        <td><a>{{ $entries_top[$i]->corp_deck_title }}</a></td>
                                        <td><a>{{ $entries_top[$i]->runner_deck_title }}</a></td>
                                    @else
                                        <td></td>
                                        <td><em>unclaimed</em></td>
                                        <td><em>unclaimed</em></td>
                                    @endif
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                        <h5>Swiss rounds</h5>
                    @endif
                    <table class="table table-condensed table-striped">
                        <thead>
                            <th class="text-right">rank</th>
                            <th>player</th>
                            <th>corp</th>
                            <th>runner</th>
                        </thead>
                        <tbody>
                        @for ($i = 0; $i < count($entries_swiss); $i++)
                            @if ($user_entry && count($entries_swiss[$i]) && $entries_swiss[$i]->rank == $user_entry->rank)
                                    <tr class="info">
                                @else
                                    <tr>
                                @endif
                                    <td class="text-right">#{{ $i+1 }}</td>
                                    @if (count($entries_swiss[$i]))
                                        <td>{{ $entries_swiss[$i]->user }}</td>
                                        <td><a>{{ $entries_swiss[$i]->corp_deck_title }}</a></td>
                                        <td><a>{{ $entries_swiss[$i]->runner_deck_title }}</a></td>
                                    @else
                                        <td></td>
                                        <td><em>unclaimed</em></td>
                                        <td><em>unclaimed</em></td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </p>
            @endif
            <hr/>
            <p>
                <strong>Registered players</strong>
                @if (count($entries) > 0)
                    ({{count($entries)}})
                    <br/>
                    <ul>
                    @foreach ($entries as $entry)
                        <li>{{ $entry->player->id }}</li>
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

