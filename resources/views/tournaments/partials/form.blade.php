<div class="row">
    <div class="col-xs-12 col-md-8">
        {{--Conclusion--}}
        <div class="bracket">
            <label>Conclusion</label>
            <div class="form-group">
                {!! Form::checkbox('concluded', null, in_array(old('concluded', $tournament->concluded), [1, 'on'], true),
                    ['onclick' => 'conclusionCheck()', 'id' => 'concluded']) !!}
                {!! Form::label('concluded', 'tournament has ended') !!}
            </div>
            <div class="row" id="player-numbers">
                {{--Player number--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Html::decode(Form::label('players_number', 'Number of players<sup class="text-danger">*</sup>')) !!}
                        {!! Form::text('players_number', old('players_number', $tournament->players_number),
                             ['class' => 'form-control', 'placeholder' => 'number of players', 'disabled' => '']) !!}
                    </div>
                </div>
                {{--Top number--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Form::label('top_number', 'Number of players in top cut') !!}
                        {!! Form::text('top_number', old('top_number', $tournament->top_number),
                             ['class' => 'form-control', 'placeholder' => 'number fo players in top cut', 'disabled' => '']) !!}
                    </div>
                </div>
            </div>
        </div>
        {{--General--}}
        <div class="bracket">
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
    <div class="col-xs-12 col-md-4">
        <div class="bracket">
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


                    <div class="form-group">
                        {!! Html::decode(Form::label('location_address', 'Location<sup class="text-danger">*</sup>')) !!}
                        {!! Form::text('location_address', old('time', $tournament->location_city),
                            ['class' => 'form-control', 'placeholder' => 'city, address or store name']) !!}

                        {{--Google map--}}
                        <div class="map-wrapper-small">
                            <div id="map"></div>
                        </div>
                        {{--Map problem--}}
                        <div id="map-problem" class="text-danger hidden">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            multiple locations
                        </div>
                    </div>
                    <div class="form-group">
                        <strong>Country:</strong> <span id="country"></span><br/>
                        <strong>State (US):</strong> <span id="state"></span><br/>
                        <strong>City:</strong> <span id="city"></span><br/>
                        <strong>Store/Venue:</strong> <span id="store"></span><br/>
                        <strong>Address:</strong> <span id="address"></span><br/>
                    </div>
                </div>
            {{--</div>--}}
        </div>
    </div>

</div>
<p class="text-danger">
    <sup>*</sup> required fields
</p>
<div class="row text-center">
    {!! Form::submit($submitButton, ['class' => 'btn btn-primary']) !!}
</div>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&libraries=places&callback=initializeMap">
</script>
<script type="text/javascript">

    var map;

    conclusionCheck();

    function conclusionCheck() {
        if (document.getElementById('concluded').checked) {
            document.getElementById('players_number').removeAttribute('disabled');
            document.getElementById('top_number').removeAttribute('disabled');
        } else {
            document.getElementById('players_number').setAttribute('disabled','');
            document.getElementById('top_number').setAttribute('disabled','');
        }
    }

    function initializeMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 1,
            center: {lat: 40.157053, lng: 19.329297},
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false
        });

        var input = document.getElementById('location_address');
        var autocomplete = new google.maps.places.Autocomplete(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        autocomplete.bindTo('bounds', map);

        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        autocomplete.addListener('place_changed', function() {
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                console.log("Autocomplete's returned place contains no geometry");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(15);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            refreshAddressInfo(place);
        });

    }

    function refreshAddressInfo(place) {
        if (typeof place.types !== 'undefined' &&
                ($.inArray('establishment', place.types) > -1 || ($.inArray('store', place.types) > -1))) {
            document.getElementById('store').innerHTML = place.name;
            document.getElementById('address').innerHTML = place.formatted_address;
        } else {
            document.getElementById('store').innerHTML = '';
            document.getElementById('address').innerHTML = '';
        }
        if (typeof place.address_components !== 'undefined') {
            document.getElementById('country').innerHTML = '';
            document.getElementById('city').innerHTML = '';
            place.address_components.forEach(function (comp) {
                if (comp.types[0] === 'country') {
                    document.getElementById('country').innerHTML = comp.long_name;
                }
                if (comp.types[0] === 'locality') {
                    document.getElementById('city').innerHTML = comp.long_name;
                }
                if (comp.types[0] === 'administrative_area_level_1') {
                    document.getElementById('state').innerHTML = comp.long_name;
                }
            });
            if (document.getElementById('country').innerHTML !== 'United States') {
                document.getElementById('state').innerHTML = '';
            }
        }
    }

</script>