<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">general</div>
            <div class="panel-body">
                {{--Title--}}
                <div class="form-group">
                    {!! Html::decode(Form::label('title', 'Tournament title<sup class="text-danger">*</sup>')) !!}
                    {!! Form::text('title', old('title', $tournament->title),
                         ['class' => 'form-control', 'required' => '', 'placeholder' => 'Title']) !!}
                </div>
                <div class="row">
                    {{--Tournament type--}}
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                old('tournament_type_id', $tournament->tournament_type_id),
                                ['class' => 'form-control', 'onchange' => 'showLocation()']) !!}
                        </div>
                    </div>
                    {{--Cardpool--}}
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('cardpool_id', 'Legal cardpool up to') !!}
                            {!! Form::select('cardpool_id', $cardpools,
                                        old('cardpool_id', $tournament->cardpool_id), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                {{--Mandatory decklist--}}
                <div class="form-group">
                    {!! Form::checkbox('decklist', null, in_array(old('decklist', $tournament->decklist), [1, 'on'], true), ['id' => 'decklist']) !!}
                    {!! Form::label('decklist', 'decklist is mandatory') !!}
                </div>
                {{--Description--}}
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::textarea('description', old('description', $tournament->description),
                        ['rows' => 6, 'cols' => '', 'class' => 'form-control', 'placeholder' => 'additional information and rules, prizepool, TO contact, etc.']) !!}
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">conclusion</div>
            <div class="panel-body">
                {{--Conclusion--}}
                <div class="form-group">
                    {!! Form::checkbox('concluded', null, in_array(old('concluded', $tournament->concluded), [1, 'on'], true),
                        ['onclick' => "showDiv('#player-numbers','concluded')", 'id' => 'concluded']) !!}
                    {!! Form::label('concluded', 'tournament is over') !!}
                </div>
                <div class="row hidden" id="player-numbers">
                    {{--Player number--}}
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Html::decode(Form::label('players_number', 'Number of players<sup class="text-danger">*</sup>')) !!}
                            {!! Form::text('players_number', old('players_number', $tournament->players_number),
                                 ['class' => 'form-control', 'placeholder' => 'number of players']) !!}
                        </div>
                    </div>
                    {{--Top number--}}
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('top_number', 'Number of players in top cut') !!}
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
                {{--Date--}}
                <div class="form-group">
                    {!! Html::decode(Form::label('date', 'Date<sup class="text-danger">*</sup>')) !!}
                    {!! Form::text('date', old('date', $tournament->date),
                                 ['class' => 'form-control', 'required' => '', 'placeholder' => 'YYYY.MM.DD.']) !!}
                </div>
                {{--Starting time--}}
                <div class="form-group">
                    {!! Form::label('start_time', 'Starting time') !!}
                    {!! Form::text('start_time', old('start_time', $tournament->start_time), ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
                </div>
                <div id="select_location">
                    {{--Country--}}
                    <div class="form-group">
                        {!! Html::decode(Form::label('location_country', 'Country<sup class="text-danger">*</sup>')) !!}
                        {!! Form::select('location_country', $countries, old('location_country', $tournament->location_country),
                            ['class' => 'form-control', 'onchange' => 'showUsState(); updateMap();']) !!}
                    </div>
                    {{--US State--}}
                    <div class="form-group {{ old('location_country') == 840 || $tournament->location_country == 840 ? '' : 'hidden'}}" id="select_state">
                        {!! Form::label('location_us_state', 'State') !!}
                        {!! Form::select('location_us_state', $us_states,
                                    old('location_us_state', $tournament->location_us_state), ['class' => 'form-control', 'onchange' => 'updateMap()']) !!}
                    </div>
                    {{--City--}}
                    <div class="form-group">
                        {!! Html::decode(Form::label('location_city', 'City<sup class="text-danger">*</sup>')) !!}
                        {!! Form::text('location_city', old('time', $tournament->location_city),
                            ['class' => 'form-control', 'placeholder' => 'city', 'oninput' => 'delay(function(){ updateMap(); }, 2000)']) !!}
                    </div>
                    {{--Store/venue--}}
                    <div class="form-group">
                        {!! Form::label('location_store', 'Store/venue') !!}
                        {!! Form::text('location_store', old('location_store', $tournament->location_store),
                            ['class' => 'form-control', 'placeholder' => 'store/venue name', 'oninput' => 'delay(function(){ updateMap(); }, 2000)']) !!}
                    </div>
                    {{--Address--}}
                    <div class="form-group">
                        {!! Form::label('location_address', 'Address') !!}
                        {!! Form::text('location_address', old('location_address', $tournament->location_address),
                            ['class' => 'form-control', 'placeholder' => 'address line', 'oninput' => 'delay(function(){ updateMap(); }, 2000)']) !!}
                    </div>
                    {{--Google map--}}
                    <div class="form-group">
                        {!! Form::checkbox('display_map', null, in_array(old('display_map', $tournament->display_map), [1, 'on'], true),
                        ['onclick' => "showDiv('#map','display_map'); updateMap();", 'id' => 'display_map']) !!}
                        {!! Form::label('display_map', 'display map') !!}
                    </div>
                    {{-- TODO: do not load when not needed--}}
                    <iframe id="map" width="100%" frameborder="0" style="border:0" class="hidden"
                            src="{{ "https://www.google.com/maps/embed/v1/search?q=Europe&key=".ENV('GOOGLE_MAPS_API') }}" allowfullscreen></iframe>
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
<script type="text/javascript">

    function initPage() {
        showLocation();
        showUsState();
        showDiv('#player-numbers','concluded');
        showDiv('#map','display_map');
        updateMap();
    }

    window.addEventListener("load", initPage, false);

    function updateMap() {
        if (document.getElementById('display_map').checked) {
            var country_field = document.getElementById('location_country');
            if (country_field.selectedIndex > 0) {
                var country= country_field.options[country_field.selectedIndex].text,
                        city = document.getElementById('location_city').value,
                        store = document.getElementById('location_store').value,
                        address = document.getElementById('location_address').value,
                        state = '';
                if (country === 'United States') {
                    var state_field = document.getElementById('location_us_state');
                    if (state_field.selectedIndex < 52) {
                        state = state_field.options[state_field.selectedIndex].text;
                    }
                }
                calculateAddress(country, state, city, store, address);

                document.getElementById('map').src = "https://www.google.com/maps/embed/v1/search?q=" +
                        encodeURIComponent(calculateAddress(country, state, city, store, address)) +
                        "&key=" + '{{ ENV('GOOGLE_MAPS_API') }}';
            }
        }
    }
</script>