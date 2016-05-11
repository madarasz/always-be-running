@extends('layout.general')

@section('content')
    <h3 class="page-header">Create new tournament</h3>
    @if (count($errors))
        <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif
    <form method="post" type="POST" action="/tournaments">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">general</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="title">Tournament title<sup class="text-danger">*</sup></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Title" required
                                   value="{{ old('title') }}"/>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="tournament_type_id">Type</label>
                                    <select name="tournament_type_id" id="tournament_type_id" class="form-control">
                                        @foreach ($tournament_types as $ttype)
                                            @if (old('tournament_type_id') == $ttype->id)
                                                <option value="{{$ttype->id}}" selected>{{ $ttype->type_name }}</option>
                                            @else
                                                <option value="{{$ttype->id}}">{{ $ttype->type_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label>
                                        <br/>
                                        @if (old('decklist') === '')
                                            <input type="checkbox" value="" id="decklist" name="decklist" checked/>
                                        @else
                                            <input type="checkbox" value="" id="decklist" name="decklist"/>
                                        @endif
                                        decklist is mandatory
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Description</label>
                            <textarea name="description" class="form-control" id="description">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">conclusion</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>
                                @if (old('concluded') === '')
                                    <input type="checkbox" value="" name="concluded" id="concluded"
                                           onclick="showDiv('#player-numbers','concluded')" checked>
                                @else
                                    <input type="checkbox" value="" name="concluded" id="concluded"
                                           onclick="showDiv('#player-numbers','concluded')">
                                @endif
                                tournament is over
                            </label>
                        </div>
                        @if (old('concluded') === '')
                            <div class="row" id="player-numbers">
                        @else
                            <div class="row hidden" id="player-numbers">
                        @endif
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="players_number">Number of players<sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" id="players_number" name="players_number"
                                           placeholder="number of players" value="{{ old('players_number') }}"/>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="top_number">Top cut</label>
                                    <input type="text" class="form-control" id="top_number" name="top_number"
                                           placeholder="number fo players in top cut" value="{{ old('top_number') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">date, time, location</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="date">Date<sup class="text-danger">*</sup></label>
                            <input type="text" class="form-control" id="date" name="date" placeholder="yyyy.mm.dd."
                                   required value="{{ old('date') }}"/>
                        </div>
                        <div class="form-group">
                            <label for="time">Starting time</label>
                            <input type="text" class="form-control" id="time" name="time" placeholder="hh:mm"
                                   value="{{ old('time') }}"/>
                        </div>
                        <div class="form-group">
                            <label for="location_country">Country<sup class="text-danger">*</sup></label>
                            <select name="location_country" class="form-control" id="location_country" onchange="showUsState()">
                                @foreach ($countries as $country)
                                    @if (old('location_country') == $country->id)
                                        <option value="{{$country->id}}" selected>{{ $country->name }}</option>
                                    @else
                                        <option value="{{$country->id}}">{{ $country->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @if (old('location_country') == 840)
                            <div class="form-group" id="select_state">
                        @else
                            <div class="form-group hidden" id="select_state">
                        @endif
                            <label for="location_us_state">State</label>
                            <select name="location_us_state" class="form-control" id="location_us_state">
                                @foreach ($us_states as $us_state)
                                    @if (old('location_us_state') == @$us_state->id)
                                        <option value="{{$us_state->id}}" selected>{{ $us_state->name }}</option>
                                    @else
                                        <option value="{{$us_state->id}}">{{ $us_state->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location_city">City<sup class="text-danger">*</sup></label>
                            <input type="text" class="form-control" id="location_city" name="location_city"
                                   placeholder="city" required value="{{ old('location_city') }}"/>
                        </div>
                        <div class="form-group">
                            <label for="location_store">Store/venue</label>
                            <input type="text" class="form-control" id="location_store" name="location_store"
                                   placeholder="store/venue name" value="{{ old('location_store') }}"/>
                        </div>
                        <div class="form-group">
                            <label for="location_address">Address</label>
                            <input type="text" class="form-control" id="location_address" name="location_address"
                                   placeholder="address line" value="{{ old('location_address') }}"/>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <p class="text-danger">
            <sup>*</sup> required fields
        </p>
        <div class="row text-center">
            <button type="submit" class="btn btn-primary">Create tournament</button>
        </div>
    </form>


@stop

