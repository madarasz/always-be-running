@extends('layout.general')

@section('content')
    <h3 class="page-header">Create new tournament</h3>
    <div class="row">
        <div class="col-md-6 col-xs-12 col-md-offset-3">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="title">Tournament title</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="Title" />
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" class="form-control">
                        @foreach ($tournament_types as $ttype)
                            <option value="{{$ttype->id}}">{{ $ttype->type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="text" class="form-control" id="date" name="date" placeholder="yyyy.mm.dd." />
                        </div>
                    </div>
                    <div class="col-xs-5 col-xs-offset-2">
                        <div class="form-group">
                            <label for="date">Starting time</label>
                            <input type="text" class="form-control" id="time" name="time" placeholder="hh:mm" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select name="country" class="form-control" id="country">
                                @foreach ($countries as $country)
                                    <option value="{{$country->id}}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-5 col-xs-offset-2">
                        <div class="form-group">
                            <label for="state">State</label>
                            <select name="state" class="form-control" id="state">
                                @foreach ($us_states as $us_state)
                                    <option value="{{$us_state->id}}">{{ $us_state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="city" />
                        </div>
                    </div>
                    <div class="col-xs-5 col-xs-offset-2">
                        <div class="form-group">
                            <label for="store">Store/venue</label>
                            <input type="text" class="form-control" id="store" name="store" placeholder="store name" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="address line" />
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" value="" id="decklist">
                        decklist is mandatory
                    </label>
                </div>
                <div class="form-group">
                    <label for="address">Description</label>
                    <textarea name="description" class="form-control" id="description"></textarea>
                </div>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="form-group">
                            <label for="players_number">Number of players</label>
                            <input type="text" class="form-control" id="players_number" name="players_number" placeholder="number of players" />
                        </div>
                    </div>
                    <div class="col-xs-5 col-xs-offset-2">
                        <div class="form-group">
                            <label for="top">Top cut</label>
                            <input type="text" class="form-control" id="top" name="top" placeholder="number fo players" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

