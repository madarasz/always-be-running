{{--New/edit tournament form--}}
<div class="row" id="form-tournament">
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
                                <li><strong>community tournament:</strong> a tournament not supported by FFG/NISEI or its prize pack</li>
                                <li><strong>online event:</strong> You are playing via Jinteki.net/OCTGN. No location is required.</li>
                                <li><strong>non-tournament:</strong> This is not a tournament. Just play. <em>Weekly recurrence</em> is an option.</li>
                                <li><strong>continental championship:</strong> North American / European championship</li>
                                <li><strong>team:</strong> more than one players can claim a single rank, conflicts are relaxed</li>
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
                                    old('cardpool_id', 
                                        $tournament->cardpool_id == "unknown" ? array_keys(array_slice($cardpools, 1, 1, TRUE))[0] : $tournament->cardpool_id ),
                                        ['class' => 'form-control']) !!}
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
                            <li><strong>Standard:</strong> Most tournaments are like this. <em>Tournament Regulations</em> by NISEI and latest <em>Banlist, FAQ</em> are in effect. No additional rules!</li>
                            <li><strong>Startup:</strong> Cardpool is <em>Sytem Gateway</em> + most recent <em>System Update</em> set + most recent NISEI cycle.</li>
                            <li><strong>Snapshot:</strong> This format is a “snapshot” of the meta at Magnum Opus 2018, the culmination of FFG Organized Play.</li>
                            <li><strong>Eternal:</strong> Eternal is not affected by rotation and has a much less stringent ban list. The largest and most complex format.</li>
                            <li><strong>Cube Draft:</strong> Drafting with a custom draft pool (not the official FFG draft packs). Please give more information about the draft pool in the description.</li>
                            <li><strong>Other:</strong> Use this if you have additional rules on deck building, legal cardpool, etc. Please explain in the description.</li>
                        </ul>
                        Check out <a href="https://nisei.net/players/supported-formats/" target="_blank" rel="nofollow">NISEI supported formats</a> for more information.
                        If your tournament rules differs from the format you selected, explain the differences in the
                        description.'])
                    {!! Form::select('tournament_format_id', $tournament_formats,
                        old('tournament_format_id', $tournament->tournament_format_id), ['class' => 'form-control']) !!}
                    </div>
                </div>
                {{--MWL--}}
                <div class="col-md-6 col-xs-12">
                    <div class="form-group hide-nonrequired">
                    {!! Form::label('mwl_id', 'MWL') !!}
                    @include('partials.popover', ['direction' => 'right', 'content' =>
                            '<strong>Most Wanted List</strong> used on the tournament. 
                            Defines <strong>retricted</strong> and <strong>removed</strong> cards in cardpool.'])
                    {!! Form::select('mwl_id', $mwls, old('mwl_id', $tournament->mwl_id),
                        ['class' => 'form-control']) !!}
                    </div>
                    
                </div>
            </div>
            <div class="row">
                {{--Mandatory decklist--}}
                <div class="col-md-6 col-xs-12">
                    {{--MWL, Mandatory decklist--}}
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
        {{--Prizes--}}
        <div class="bracket hide-nonrequired">
            {{--Official prize kit--}}
            <div class="row form-group">
                {!! Form::label('prize_id', 'Official prize kit:', ['class' => 'col-md-3 col-xs-12 col-form-label']) !!}
                <div class="col-md-9 col-xs-12">
                    <div class="loader-chart" v-if="prizes.length == 0">&nbsp;</div>
                    <select name="prize_id" v-model="prizeId" class="form-control" v-if="prizes.length > 0">
                        <option value="0">--- none ---</option>
                        <option v-for="prize in prizes" :value="prize.id">@{{ prize.year+' '+prize.title }}</option>
                    </select>
                </div>
                <div class="col-md-3 col-xs-12" v-if="selectedPrizeIndex > -1">
                    <img v-if="prizes[selectedPrizeIndex].photos.length > 0"
                         :src="prizes[selectedPrizeIndex].photos[0].url" style="width: 100%"/>
                </div>
                <div class="col-md-9 col-xs-12 legal-bullshit" v-if="selectedPrizeIndex > -1" v-html="prizeSummary"></div>
            </div>
            {{-- Unofficial alt arts --}}
            <div class="row form-group m-t-1">
                <label for="alt_prize_selector" class="col-md-3 col-xs-12 col-form-label">
                    Unofficial prizes:
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            'You can add prize items from the artists registered on ABR. These are usually fan made alternate arts or items.<br/>
                            <a href="https://alwaysberunning.net/prizes#tab-other" target="_blank">Check out these items</a>'])
                </label>
                <div class="col-md-5 col-xs-8">
                    <div class="loader-chart" v-if="!unofficialPrizesLoaded">&nbsp;</div>
                    <div class="input-group">
                        <div class="input-group-prepend" style="display: flex">
                            <img class="v-autocomplete-preview" :src="focusedUnofficialPrize.urlThumb" v-if="focusedUnofficialPrize != null">
                        </div>
                        <template v-if="unofficialPrizesLoaded">
                            <v-autocomplete :items="unofficialPrizesSelection" v-model="focusedUnofficialPrize"
                                :input-attrs="{ class: focusedUnofficialPrize != null ? 'border-left-flat v-autocomplete-input' : 'v-autocomplete-input'}"
                                :component-item='selectionTemplate' :get-label="getPrizeLabel" placeholder="type title or artist"
                                @update-items="prizeInputChange" :min-len="2" :auto-select-one-item="false"/>
                        </template>
                    </div>
                </div>
                <div class="col-md-4 col-xs-4">
                    <div class="input-group">
                        <input maxlength="15" type="text" placeholder="quantity" class="form-control" v-model="unofficialQuantity"/>
                        <div class="input-group-append">
                            <button type="button" class="btn border-left-flat" :class="focusedUnofficialPrize != null ? 'btn-primary' : 'btn-secondary'"
                                    style="border-left: 0" :disabled="focusedUnofficialPrize == null" @click="addUnofficialPrize">
                                <i aria-hidden="true" class="fa fa-plus-circle font-1-3" id="button-add-unofficial"></i>
                            </button> 
                        </div>
                    </div>
                </div>
            </div>
            {{-- Added unofficialPrizes --}}
            <table v-if="addedUnofficialPrizes.length > 0" style="margin: 0 auto">
                <tr v-for="(prize, index) in addedUnofficialPrizes">
                    <td class="text-xs-right">
                        @{{ prize.quantity }}<span v-if="prize.quantity.length">@{{ isNaN(prize.quantity) ? ':' : 'x'}}</span>
                    </td>
                    <td nowrap>
                        <img class="v-autocomplete-preview" :src="prize.urlThumb" v-if="prize.urlThumb.length">
                        <strong>@{{ prize.title }}</strong> by <em>@{{ prize.artist }}</em>
                    </td>
                    <td>
                        <i aria-hidden="true" class="fa fa-times-circle text-danger font-1-3 btn" @click="removeUnofficial(index)"></i>
                    </td>
                </tr>
            </table>
            <div class="text-xs-center legal-bullshit" v-if="addedUnofficialPrizes.length == 0">
                no unofficial items added
            </div>
            <hr/>
            {{--Additional prizes--}}
            <div class="form-group hide-nonrequired m-t-1">
                {!! Form::label('prize_additional', 'Additional prizes') !!}
                <div class="pull-right">
                    <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                </div>
                {!! Form::textarea('prize_additional', old('prize_additional', $tournament->prize_additional),
                    ['rows' => 4, 'cols' => '', 'class' => 'form-control', 'placeholder' => 'anything in addition to the selected prizes, how prizes will be distributed']) !!}
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
                        {!! Form::select('top_number', ['0' => '- no elimination rounds -', '3' => 'top 3', '4' => 'top 4', '8' => 'top 8', '16' => 'top 16'],
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
                                 ['class' => 'form-control', 'required' => '', 'placeholder' => 'YYYY.MM.DD.', 'onchange' => 'mwlAdjust()']) !!}
                    <div class="input-group-addon" id="datepicker-icon">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            {{--Registration time--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('reg_time', 'Registration starts') !!}
                {!! Form::text('reg_time', old('reg_time', $tournament->reg_time), ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
            </div>
            {{--Starting time--}}
            <div class="form-group hide-nonrequired">
                {!! Form::label('start_time', 'Tournaments starts') !!}
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
                                {{ old('end_date', $tournament->end_date) ? 'checked' : '' }}/>
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
                                {{ old('recur_weekly', $tournament->recur_weekly) ? 'checked' : 'disabled' }}/>
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
        src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_FRONTEND_API')}}&libraries=places&language=en&callback=initializeMap">
</script>
<script type="text/javascript">

    var map, marker,
        old_place_id = '{{old('location_place_id', $tournament->location_place_id)}}',
        mwl_dates = [
            @foreach($mwl_dates as $mwl)
                '{{ $mwl }}',
            @endforeach
    ];

    conclusionCheck();
    changeTournamentType();
    hideNonRequired();

    // disable "-----" in format selection
    $("#tournament_format_id > [value='9999']").prop('disabled', true)

    // adjust MWL based on selected date
    function mwlAdjust() {
        var selectedDate = document.getElementById("date").value;
        var mwl_index;
        for (mwl_index = 0; mwl_index < mwl_dates.length; mwl_index++) {
            if (mwl_dates[mwl_index].localeCompare(selectedDate) < 1) {
                break;
            }
        }
        var mwlSelector = $('select[name=mwl_id]');
        var value = mwlSelector.find('option:eq('+mwl_index+')').val();
        mwlSelector.val(value).change();
    }

    function initDatePicker() {
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

        $('#reg_time').timepicker({ 'scrollDefault': '9:30AM', 'timeFormat': 'H:i', 'show2400': true });
        $('#start_time').timepicker({ 'scrollDefault': '10:00AM', 'timeFormat': 'H:i', 'show2400': true });

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
    }

    function initializeMap() {

        initDatePicker();

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

    // template for filtered unofficial items
    Vue.use(VAutocomplete.default);
    var itemTemplate = Vue.component('itemTemplate', {
        template: '<div><strong>@{{ item.title }}</strong> by <em>@{{ item.artist }}</em></div>',
        props: {
            item: { required: true },
        }
    });

    var formTournament= new Vue({
        el: '#form-tournament',
        data: {
            prizes: [], // all official prize kits
            unofficialPrizes: [], // all unofficial prize items
            unofficialPrizesSelection: null, // list of filtered unofficial items
            focusedUnofficialPrize: null, // currently selected unofficial item
            unofficialQuantity: '',
            addedUnofficialPrizes: [],
            prizeId: '{{ old('prize_id', $tournament->prize_id) }}' || '0',
            selectionTemplate: itemTemplate,
            unofficialPrizesLoaded: false,
            tournamentId: {{ is_null($tournament->id) ? $temp_id : $tournament->id }}
        },
        mounted: function () {
            this.loadPrizes();
        },
        computed: {
            selectedPrizeIndex: function() {
                if (this.prizes.length > 0 && this.prizeId > 0) {
                    for (var i = 0; i < this.prizes.length; i++) {
                        if (this.prizes[i].id == this.prizeId) {
                            return i;
                        }
                    }
                }
                return -1;
            },
            prizeSummary: function() {
                if (this.selectedPrizeIndex > -1) {
                    var selectedPrizeKit = this.prizes[this.selectedPrizeIndex], summary = '';
                    for (var u = 0; u < selectedPrizeKit.elements.length; u++) {
                        if (u == 0 || selectedPrizeKit.elements[u-1].quantityString != selectedPrizeKit.elements[u].quantityString) {
                            summary += '<em>'+selectedPrizeKit.elements[u].quantityString+':</em> ';
                        }
                        summary += '<strong>'+selectedPrizeKit.elements[u].title+'</strong> ';
                        summary += selectedPrizeKit.elements[u].type+', ';
                    }
                    return summary.substring(0, summary.length - 2);
                } else {
                    return '';
                }
            }
        },
        methods: {
            // load all prizes
            loadPrizes: function () {
                // load prize kits
                axios.get('/api/prizes').then(function (response) {
                    formTournament.prizes = response.data;
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the prize kits.', '', {timeOut: 2000});
                });
                // load unofficial arts
                axios.get('/api/artists').then(function (response) {
                    formTournament.unofficialPrizes = response.data.map(
                        x => { return x.items.map(
                            y => { return {
                                id: y.id, 
                                title: y.title, 
                                artist: x.displayArtistName,
                                urlThumb: y.photos.length > 0 ? y.photos[0].urlThumb : ""
                            }}
                        )}
                    ).flat();
                    formTournament.unofficialPrizesLoaded = true;

                    // {{-- if editing load already added unofficial prizes --}}
                    @if (!is_null($tournament->id))       
                        formTournament.unofficialPrizesLoaded = false;
                        formTournament.loadAddedUnofficialPrizes();
                    @endif

                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the unofficial prizes.', '', {timeOut: 2000});
                });
            },
            getPrizeLabel(item) {
                if (item != null && item.hasOwnProperty('title') && item.hasOwnProperty('artist')) {
                    return item.title+' by '+item.artist;
                }
                return "";
            },
            prizeInputChange(text) {
                this.unofficialPrizesSelection = this.unofficialPrizes.filter(
                    x => { return x.title.toUpperCase().includes(text.toUpperCase()) || 
                                x.artist.toUpperCase().includes(text.toUpperCase()); }
                );
            },
            addUnofficialPrize() {
                this.focusedUnofficialPrize.quantity = this.unofficialQuantity;
                var payload = {
                    prize_element_id: this.focusedUnofficialPrize.id, 
                    quantity: this.focusedUnofficialPrize.quantity
                }
                axios.post('/api/unofficial-prizes/'+this.tournamentId, payload).then(function (response) {
                    formTournament.focusedUnofficialPrize.tournamentPrizeId = response.data.id;
                    formTournament.addedUnofficialPrizes.push(JSON.parse(JSON.stringify(formTournament.focusedUnofficialPrize)));
                    formTournament.focusedUnofficialPrize = null;
                    formTournament.unofficialQuantity = "";
                    toastr.info('Unofficial prize added', {timeOut: 500});
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while adding the unofficial prize', '', {timeOut: 2000});
                });
            },
            removeUnofficial(index) {
                axios.delete('/api/unofficial-prizes/'+this.addedUnofficialPrizes[index].tournamentPrizeId).then(function (response) {
                    formTournament.addedUnofficialPrizes.splice(index, 1);
                    toastr.info('Unofficial prize removed', {timeOut: 500});
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while removing the unofficial prize', '', {timeOut: 2000});
                });
            },
            loadAddedUnofficialPrizes() {
                axios.get('/api/tournaments/'+this.tournamentId+'/unofficial-prizes').then(function (response) {
                    formTournament.addedUnofficialPrizes = response.data.map(
                        x => {
                            prize =  formTournament.unofficialPrizes.find(y => { return y.id == x.prize_element_id; });
                            return {
                                artist: prize.artist,
                                id: prize.id,
                                quantity: x.quantity,
                                title: prize.title,
                                tournamentPrizeId: x.id,
                                urlThumb: prize.urlThumb
                            };
                        }
                    );
                    formTournament.unofficialPrizesLoaded = true;
                }, function (response) {
                    // error handling
                    toastr.error('Something went wrong while loading the unofficial prizes for the tournament', '', {timeOut: 2000});
                });
            }
        }
    });

</script>