{{--References--}}
<div class="bracket">
    <h5>
        <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
        References
        {{--QR code--}}
        <div class="text-xs-center p-t-1 p-b-1 markdown-content">
            <a href="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}&size=500x500">
                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode('https://alwaysberunning.net/'.$tournament->seoUrl()) }}" />
            </a>
            <div class="legal-bullshit">
                provided by <a href="http://goqr.me/" rel="nofollow">goQR.me</a>
                @include('partials.popover', ['direction' => 'right', 'content' =>
                            'Ideal for printing. It links to this tournament page. Click QR code for bigger resolution.'])
            </div>
        </div>
        {{--Calendar--}}
        <div class="text-xs-center">
            <div title="Add to Calendar" class="addeventatc">
                Add to Calendar
                <span class="start">{{ @$calendarEntry['start'] }}</span>
                <span class="end">{{ @$calendarEntry['end'] }}</span>
                <span class="timezone" id="calendar-timezone"></span>
                <span class="title">{{ @$calendarEntry['title'] }}</span>
                <span class="description">{{ @$calendarEntry['description'] }}</span>
                <span class="location">{{ @$calendarEntry['location'] }}</span>
                @if (strlen(@$calendarEntry['facebook_event']) > 0)
                <span class="facebook_event">{{ @$calendarEntry['facebook_event'] }}</span>
                @endif
                <span class="all_day_event">{{ @$calendarEntry['all_day_event'] }}</span>
                <span class="date_format">{{ @$calendarEntry['date_format'] }}</span>
                <span class="alarm_reminder">{{ @$calendarEntry['alarm_reminder'] }}</span>
                <span class="recurring">{{ @$calendarEntry['recurring'] }}</span>
                <span class="uid">{{ @$calendarEntry['uid'] }}</span>
                <span class="status">{{ @$calendarEntry['status'] }}</span>
                <span class="method">{{ @$calendarEntry['method'] }}</span>
            </div>
        </div>
    </h5>
</div>
{{--Getting timezone from location--}}
@if ($tournament->tournament_type_id != 7)
    <script type="text/javascript">

        $.ajax({
            url: "https://maps.googleapis.com/maps/api/timezone/json?location=" + {{ $tournament->location_lat }} + ',' +
                '{{ $tournament->location_long }}' + '&timestamp=' + '{{ time($tournament->date) }}' + '&key=' + '{{ENV('GOOGLE_MAPS_API')}}',
            dataType: "json",
            async: true,
            success: function (data) {
                $('#calendar-timezone').text(data.timeZoneId);
                addeventatc.refresh();
            }
        });
    </script>
@endif