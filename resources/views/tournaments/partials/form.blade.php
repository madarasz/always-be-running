<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">general</div>
            <div class="panel-body">
                <div class="form-group">
                    {!! Html::decode(Form::label('title', 'Tournament title<sup class="text-danger">*</sup>')) !!}
                    {!! Form::text('title', old('title', $tournament->title),
                         ['class' => 'form-control', 'required' => '', 'placeholder' => 'Title']) !!}
                </div>
                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                old('tournament_type_id', $tournament->tournament_type_id) || 1, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            <br/>
                            {!! Form::checkbox('decklist', 1, old('decklist', $tournament->decklist) == 1) !!}
                            {!! Form::label('decklist', 'decklist is mandatory') !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::textarea('description', old('description', $tournament->description),
                        ['rows' => 6, 'cols' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">conclusion</div>
            <div class="panel-body">
                <div class="form-group">
                    {!! Form::checkbox('concluded', 1, old('concluded', $tournament->concluded) == 1,
                        ['onclick' => "showDiv('#player-numbers','concluded')", 'id' => 'concluded']) !!}
                    {!! Form::label('concluded', 'tournament is over') !!}
                </div>
                <div class="row {{ old('concluded') == 1  || $tournament->concluded == 1 ? '' : 'hidden' }}" id="player-numbers">
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Html::decode(Form::label('players_number', 'Number of players<sup class="text-danger">*</sup>')) !!}
                            {!! Form::text('players_number', old('players_number', $tournament->players_number),
                                 ['class' => 'form-control', 'placeholder' => 'number of players']) !!}
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('top_number', 'Number of players') !!}
                            {!! Form::text('top_number', old('top_number', $tournament->top_number),
                                 ['class' => 'form-control', 'placeholder' => 'number fo players in top cut']) !!}
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
                    {!! Html::decode(Form::label('date', 'Date<sup class="text-danger">*</sup>')) !!}
                    {!! Form::text('date', old('date', $tournament->date),
                                 ['class' => 'form-control', 'required' => '', 'placeholder' => 'YYYY.MM.DD.']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('time', 'Starting time') !!}
                    {!! Form::text('time', old('time', $tournament->time), ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
                </div>
                <div class="form-group">
                    {!! Html::decode(Form::label('location_country', 'Country<sup class="text-danger">*</sup>')) !!}
                    {!! Form::select('location_country', $countries, old('location_country', $tournament->location_country),
                        ['class' => 'form-control', 'onchange' => 'showUsState()']) !!}
                </div>
                <div class="form-group {{ old('location_country') == 840 || $tournament->location_country == 840 ? '' : 'hidden'}}" id="select_state">
                    {!! Form::label('location_us_state', 'State') !!}
                    {!! Form::select('location_us_state', $us_states,
                                old('location_us_state', $tournament->location_us_state), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Html::decode(Form::label('location_city', 'City<sup class="text-danger">*</sup>')) !!}
                    {!! Form::text('location_city', old('time', $tournament->time),
                        ['class' => 'form-control', 'placeholder' => 'city', 'required' => '']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('location_store', 'Store/venue') !!}
                    {!! Form::text('location_store', old('location_store', $tournament->location_store),
                        ['class' => 'form-control', 'placeholder' => 'store/venue name']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('location_address', 'Address') !!}
                    {!! Form::text('location_address', old('location_address', $tournament->location_address),
                        ['class' => 'form-control', 'placeholder' => 'address line']) !!}
                </div>
            </div>
        </div>
    </div>

</div>
<p class="text-danger">
    <sup>*</sup> required fields
</p>
<div class="row text-center">
    {!! Form::submit($submitButton, ['class' => 'btn btn-primary']) !!}
</div>