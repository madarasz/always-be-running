{{--New/edit tournament form--}}
<div class="row">
    <div class="col-xs-12 col-md-8">
        {{--General--}}
        <div class="bracket">
            {{--Title--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('title', 'Tournament title') !!}
                @include('partials.popover', ['direction' => 'right', 'content' =>
                            'You can leave this field empty. If you do, it will be constructed as
                            "<em>store name OR city</em> - <em>tournament type</em>".'])
                {!! Form::text('title', old('title', $tournament->title),
                     ['class' => 'form-control', 'placeholder' => 'Title', 'maxlength' => 50]) !!}
            </div>
            <div class="row">
                {{--Tournament type--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Html::decode(Form::label('tournament_type_id', 'Type<sup class="text-danger" id="req-date">*</sup>')) !!}
                        @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Confused about the official Netrunner tournament types?
                            Read the <a href="https://www.fantasyflightgames.com/en/more/android-netrunner-organized-play/" target="_blank">FFG\'s Organized Play</a> page.
                            The prize kit is a good way to tell which type you are hosting.
                            <br/><br/>
                            Additional tournament types:
                            <ul>
                                <li><strong>non-FFG tournament:</strong> a tournament not supported by FFG or its prize pack / casual</li>
                                <li><strong>online event:</strong> You are playing via Jinteki.net/OCTGN. No location is required.</li>
                                <li><strong>non-tournament:</strong> This is not a tournament. Just play. <em>Weekly recurrence</em> is an option.</li>
                                <li><strong>continental championship:</strong> North American / European championship</li>
                            </ul>'])
                        {!! Form::select('tournament_type_id', $tournament_types,
                            old('tournament_type_id', $tournament->tournament_type_id),
                            ['class' => 'form-control', 'onchange' => 'changeTournamentType()']) !!}
                    </div>
                </div>
                {{--Cardpool--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Html::decode(Form::label('cardpool_id', 'Legal cardpool up to<sup class="text-danger" id="req-date">*</sup>')) !!}
                        <div style="position: relative;">
                            <div id="overlay-cardpool" class="overlay hidden-xs-up" style="top: 0; bottom: 0">
                                <div>recurring event has no cardpool</div>
                            </div>
                        {!! Form::select('cardpool_id', $cardpools,
                                    old('cardpool_id', $tournament->cardpool_id), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{--Format--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group hide-nonrequired">
                    {!! Form::label('tournament_format_id', 'Format') !!}
                    @include('partials.popover', ['direction' => 'right', 'content' =>
                        '<ul>
                            <li><strong>Standard:</strong> Most tournaments are like this. <em>Tournament Regulations</em> by FFG and latest <em>MWL, FAQ</em> are in effect.</li>
                            <li><strong>Cache Refresh:</strong> 1 Core Set + 1 Deluxe Expansion + 1 Terminal Directive + current Data Cycle + second-most current Data Cycle. Latest MWL plus additional rules apply.</li>
                            <li><strong>1.1.1.1:</strong> 1 Core Set + 1 Deluxe Expansion + 1 Data Pack + 1 Card. Please state in the description if MWL applies.</li>
                            <li><strong>Draft:</strong> Drafting with the official FFG draft packs.</li>
                            <li><strong>Cube Draft:</strong> Drafting with a custom draft pool. Please give more information about the draft pool in the description.</li>
                        </ul>
                        If your tournament rules differs from the format you selected, explain the differences in the
                        description.'])
                    {!! Form::select('tournament_format_id', $tournament_formats,
                        old('tournament_format_id', $tournament->tournament_format_id), ['class' => 'form-control']) !!}
                    </div>
                </div>
                {{--Mandatory decklist--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group hide-nonrequired m-t-2">
                        {!! Form::checkbox('decklist', null, in_array(old('decklist', $tournament->decklist), [1, '1', 'on'], true), ['id' => 'decklist']) !!}
                        {!! Form::label('decklist', 'decklist is mandatory') !!}
                    </div>
                </div>
            </div>
            {{--Contact--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('contact', 'Contact') !!}
                {!! Form::text('contact', old('contact', $tournament->contact),
                     ['class' => 'form-control', 'placeholder' => 'T.O. phone number or email']) !!}
            </div>
            {{--FB link--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('link_facebook', 'Facebook event/group') !!}
                {!! Form::text('link_facebook', old('link_facebook', $tournament->link_facebook),
                     ['class' => 'form-control', 'placeholder' => 'URL of Facebook event or group']) !!}
            </div>
            {{--Description--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('description', 'Description') !!}
                <div class="pull-right">
                    <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a><br/>
                            <br/>
                            How to make your tournament look cool?<br/>
                            <a href="/markdown" target="_blank">example formatted description</a>'])
                </div>
                {!! Form::textarea('description', old('description', $tournament->description),
                    ['rows' => 6, 'cols' => '', 'class' => 'form-control', 'placeholder' => 'additional information and rules, prizepool, etc.']) !!}
            </div>
        </div>
        {{--Conclusion--}}
        <div class="bracket hide-nonrequired">
            {{--Overlay--}}
            <div id="overlay-conclusion" class="overlay hidden-xs-up">
                <div>'non-tournament' events have no conclusion</div>
            </div>
            <h5>
                Conclusion
                <small class="form-group text-xs-center">
                    -
                    {!! Form::checkbox('concluded', null, in_array(old('concluded', $tournament->concluded), [1, '1', 'on'], true),
                        ['onclick' => 'conclusionCheck()', 'id' => 'concluded']) !!}
                    {!! Form::label('concluded', 'tournament has already ended') !!}
                </small>
            </h5>
            {{--Imported values warning--}}
            @if ($tournament->import && $tournament->import != 3)
                <div class="alert alert-warning view-indicator" id="warning-imported-values">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    These values were set by importing results. Only change if necessary.
                </div>
            @endif
            <div class="row">
                {{--Player number--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Html::decode(Form::label('players_number', 'Number of players<sup class="text-danger hidden-xs-up req-conclusion">*</sup>')) !!}
                        {!! Form::text('players_number', old('players_number', $tournament->players_number),
                             ['class' => 'form-control', 'placeholder' => 'number of players', 'disabled' => '']) !!}
                    </div>
                </div>
                {{--Top cut number--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        {!! Form::label('top_number', 'Number of players in top cut') !!}
                        {!! Form::select('top_number', ['0' => '- no elimination rounds -', '4' => 'top 4', '8' => 'top 8', '16' => 'top 16'],
                            old('top_number', $tournament->top_number),
                            ['class' => 'form-control', 'disabled' => '']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        {{--Date and time--}}
        <div class="bracket">
            {{--Date--}}
            <div class="form-group">
                {!! Html::decode(Form::label('date', 'Date<sup class="text-danger" id="req-date">*</sup>')) !!}
                <div class="input-group">
                    {!! Form::text('date', old('date', $tournament->date),
                                 ['class' => 'form-control', 'required' => '', 'placeholder' => 'YYYY.MM.DD.']) !!}
                    <div class="input-group-addon" id="datepicker-icon">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            {{--Starting time--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('start_time', 'Starting time') !!}
                {!! Form::text('start_time', old('start_time', $tournament->start_time), ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
            </div>
            {{--Weekly reoccurance--}}
            <fieldset class="form-group hide-nonrequired">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="end-date form-check-input" name="end_date_selector" id="end-date-single" value="single"
                                {{ !$tournament->end_date && !$tournament->recur_weekly ? 'checked' : '' }}/>
                        <span>single day event</span>
                    </label>
                <div class="form-check">
                    <label class="form-check-label" style="width: 100%">
                        <input type="radio" class="end-date form-check-input" name="end_date_selector" id="end-date-multiple" value="multiple"
                                {{ $tournament->end_date ? 'checked' : '' }}/>
                        multiple day event, end date:
                        <div class="input-group">
                            {!! Form::text('end_date', old('end_date', $tournament->end_date),
                                     ['class' => 'form-control', 'required' => '', 'placeholder' => 'YYYY.MM.DD.', 'id' => 'end_date', 'disabled' => '']) !!}
                            <div class="input-group-addon" id="datepicker-icon-end">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label" style="width: 100%">
                        <input type="radio" class="end-date form-check-input" name="end_date_selector" id="end-date-recur" value="recurring"
                                {{ $tournament->recur_weekly ? 'checked' : 'disabled' }}/>
                        weekly recurrence
                        @include('partials.popover', ['direction' => 'right', 'content' =>
                                    'Select if the event recurs weekly. This is ideal for weekly get-togethers.
                                    This option is only available for the <strong>non-tournament event</strong> type.<br/>
                                    <br/>
                                    Recurring events are listed separately.'])
                        <div style="position: relative">
                            <div id="overlay-weekly" class="overlay" style="top: 0; bottom: 0">
                                <div>only for 'non-tournament' type</div>
                            </div>
                            {!! Form::select('recur_weekly', ['1' => 'Monday', '2' => 'Tuesday',
                                '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'],
                                old('recur_weekly', $tournament->recur_weekly), ['class' => 'form-control', 'id' => 'recur_weekly']) !!}
                        </div>
                    </label>
                </div>
            </fieldset>
        </div>
        {{--Location--}}
        <div class="bracket">
            {{--Overlay--}}
            <div id="overlay-location" class="overlay hidden-xs-up">
                <div>'online' events have no location</div>
            </div>
            {{--Location input--}}
            <div class="form-group">
                {!! Html::decode(Form::label('location_search', 'Location<sup class="text-danger hidden-xs-up req-conclusion">*</sup>')) !!}
                @include('partials.popover', ['direction' => 'top', 'content' =>
                            'Use the input field to search for the location of the tournament.
                            Providing the city is enough, but locating the store or address helps players.'])
                {!! Form::text('location_search', null,
                    ['class' => 'form-control', 'placeholder' => 'search city, address or store name']) !!}
                {{--Google map--}}
                <div class="map-wrapper-small">
                    <div id="map"></div>
                </div>
            </div>
            {{--Location info--}}
            <div class="form-group">
                <strong>Country:<sup class="text-danger req-location">*</sup></strong> <span id="country"></span><br/>
                <strong>State (US):</strong> <span id="state"></span><br/>
                <strong>City:<sup class="text-danger req-location">*</sup></strong> <span id="city"></span><br/>
                <strong>Store/Venue:</strong> <span id="store"></span><br/>
                <strong>Address:</strong> <span id="address"></span><br/>
                {!! Form::hidden('location_country', old('location_country', $tournament->location_country), ['id' => 'location_country']) !!}
                {!! Form::hidden('location_state', old('location_state', $tournament->location_state), ['id' => 'location_state']) !!}
                {!! Form::hidden('location_city', old('location_city', $tournament->location_city), ['id' => 'location_city']) !!}
                {!! Form::hidden('location_store', old('location_store', $tournament->location_store), ['id' => 'location_store']) !!}
                {!! Form::hidden('location_address', old('location_address', $tournament->location_address), ['id' => 'location_address']) !!}
                {!! Form::hidden('location_place_id', old('location_place_id', $tournament->location_place_id), ['id' => 'location_place_id']) !!}
                {!! Form::hidden('location_lat', old('location_lat', $tournament->location_lat), ['id' => 'location_lat']) !!}
                {!! Form::hidden('location_long', old('location_long', $tournament->location_long), ['id' => 'location_long']) !!}
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-xs-12">
        <p class="text-xs-center">
            <span class="text-danger"><sup>*</sup> required fields</span> -
            {!! Form::checkbox('hide-non', null,
                in_array(old('hide-non', $tournament->incomplete), [1, '1', 'on'], true) && in_array(old('hide-non', $tournament->concluded), [1, '1', 'on'], true),
                ['id' => 'hide-non', 'onchange' => 'hideNonRequired()']) !!}
            {!! Form::label('hide-non', 'hide non-required fields') !!}
        </p>
        <p class="text-xs-center">
            {{--create button--}}
            {!! Form::submit($submitButton, ['class' => 'btn btn-primary']) !!}
            {{--cancel button--}}
            <a href="#" onclick="window.history.back()" class="btn btn-secondary m-l-2" id="button-cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
                cancel
            </a>
            <br/>
        </p>
    </div>
</div>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&libraries=places&language=en&callback=initializeMap">
</script>
<script type="text/javascript">

    var map, marker,
        old_place_id = '{{old('location_place_id', $tournament->location_place_id)}}';

    conclusionCheck();
    changeTournamentType();
    hideNonRequired();

    $('#date').datepicker({
        autoclose: true,
        format: 'yyyy.mm.dd.',
        orientation: 'bottom',
        todayHighlight: true,
        weekStart: 1 //TODO: custom
    });
    $('#end_date').datepicker({
        autoclose: true,
        format: 'yyyy.mm.dd.',
        orientation: 'bottom',
        todayHighlight: true,
        weekStart: 1 //TODO: custom
    });

    // clicking icon should also show datepicker
    $('#datepicker-icon').click(function(){
        $('#date').trigger('focus.datepicker.data-api');
    });
    $('#datepicker-icon-end').click(function(){
        document.getElementById('end-date-multiple').checked = true;
        $('#end_date').trigger('focus.datepicker.data-api');
    });
    $('#end_date').click(function(){
        document.getElementById('end-date-multiple').checked = true;
    });
    $('#recur_weekly').click(function(){
        document.getElementById('end-date-recur').checked = true;
        recurCheck();
    });
    $('.end-date').click(function() {
        recurCheck();
    });

    function initializeMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 1,
            center: {lat: 40.157053, lng: 19.329297},
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false
        });

        var input = document.getElementById('location_search');
        var autocomplete = new google.maps.places.Autocomplete(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        autocomplete.bindTo('bounds', map);

        marker = new google.maps.Marker({
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
            renderPlace(place, marker, map);

        });

        if (old_place_id.length > 0) {
            var service = new google.maps.places.PlacesService(map);
            service.getDetails({placeId: old_place_id}, function(place, status){
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    renderPlace(place, marker, map)
                }
            });
        }
    }

</script>