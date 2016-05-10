@extends('layout.general')

@section('content')
    <h3 class="page-header">Create new tournament</h3>
    @if (count($errors))
        @foreach($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
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
                            <input type="text" class="form-control" id="title" name="title" placeholder="Title" required/>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="tournament_type_id">Type</label>
                                    <select name="tournament_type_id" id="tournament_type_id" class="form-control">
                                        @foreach ($tournament_types as $ttype)
                                            <option value="{{$ttype->id}}">{{ $ttype->type_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label>
                                        <br/>
                                        <input type="checkbox" value="" id="decklist" name="decklist">
                                        decklist is mandatory
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Description</label>
                            <textarea name="description" class="form-control" id="description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">conclusion</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" value="" name="concluded" id="concluded" onclick="showDiv('#player-numbers','concluded')">
                                tournament is over
                            </label>
                        </div>
                        <div class="row hidden" id="player-numbers">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="players_number">Number of players<sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" id="players_number" name="players_number" placeholder="number of players" />
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="top_number">Top cut</label>
                                    <input type="text" class="form-control" id="top_number" name="top_number" placeholder="number fo players in top cut" />
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
                            <input type="text" class="form-control" id="date" name="date" placeholder="yyyy.mm.dd." required/>
                        </div>
                        <div class="form-group">
                            <label for="time">Starting time</label>
                            <input type="text" class="form-control" id="time" name="time" placeholder="hh:mm" />
                        </div>
                        <div class="form-group">
                            <label for="location_country">Country<sup class="text-danger">*</sup></label>
                            <select name="location_country" class="form-control" id="location_country" onchange="showUsState()">
                                @foreach ($countries as $country)
                                    <option value="{{$country->id}}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group hidden" id="select_state">
                            <label for="location_us_state">State</label>
                            <select name="location_us_state" class="form-control" id="location_us_state">
                                @foreach ($us_states as $us_state)
                                    <option value="{{$us_state->id}}">{{ $us_state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location_city">City<sup class="text-danger">*</sup></label>
                            <input type="text" class="form-control" id="location_city" name="location_city" placeholder="city" required/>
                        </div>
                        <div class="form-group">
                            <label for="location_store">Store/venue</label>
                            <input type="text" class="form-control" id="location_store" name="location_store" placeholder="store/venue name" />
                        </div>
                        <div class="form-group">
                            <label for="location_address">Address</label>
                            <input type="text" class="form-control" id="location_address" name="location_address" placeholder="address line" />
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

