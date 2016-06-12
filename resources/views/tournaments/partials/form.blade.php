<div class="row">
    <div class="col-xs-12 col-md-8">
        {{--Conclusion--}}
        <div class="bracket">
            <label>Conclusion</label>
            <div class="form-group">
                {!! Form::checkbox('concluded', null, in_array(old('concluded', $tournament->concluded), [1, 'on'], true),
                    ['onclick' => "showDiv('#player-numbers','concluded')", 'id' => 'concluded']) !!}
                {!! Form::label('concluded', 'tournament has ended') !!}
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
                            ['class' => 'form-control', 'placeholder' => 'tournament location']) !!}

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
                        <strong>State:</strong> <span id="state"></span><br/>
                        <strong>City:</strong> <span id="city"></span><br/>
                        <strong>Store/Venue:</strong> <span id="store"></span><br/>
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

    initPage();

    function initPage() {
        showLocation();
        showUsState();
        showDiv('#player-numbers','concluded');
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
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        var markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // Clear out the old markers.
            markers.forEach(function(marker) {
                marker.setMap(null);
            });
            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    title: place.name,
                    position: place.geometry.location
                }));

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }

                avoidTooMuchZoom(bounds);

            });
            map.fitBounds(bounds);
            if (markers.length > 1) {
                // multiple locations warning
                $('#map-problem').removeClass('hidden');
            } else {
                $('#map-problem').addClass('hidden');
                refreshAddressInfo(places[0]);
            }
        });

//        service = new google.maps.places.PlacesService(map);
//        service.getDetails({placeId: 'ChIJIaFnNgzcQUcRnH7g2gqy2Xk'}, function(){});
    }

    function refreshAddressInfo(place) {
        document.getElementById('store').innerHTML = place.name;
        if (typeof place.address_components !== 'undefined') {
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
        }
    }

    function avoidTooMuchZoom(bounds) {
        var maxzoom = 0.002;
        if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
            var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + maxzoom, bounds.getNorthEast().lng() + maxzoom);
            var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - maxzoom, bounds.getNorthEast().lng() - maxzoom);
            bounds.extend(extendPoint1);
            bounds.extend(extendPoint2);
        }
    }
</script>