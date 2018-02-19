{{--Personal page - Tournaments tab content--}}
@include('tournaments.modals.claim')
<div class="row">
    <div class="col-lg-8 push-lg-4 col-xs-12">
        {{--Notification for claim--}}
        <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-toclaim" data-badge="">
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            You have tournament spots waiting to be claimed.
        </div>
        {{--Notification for broken claim--}}
        <div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-brokenclaim" data-badge="">
            <i class="fa fa-chain-broken" aria-hidden="true"></i>
            You have broken claims. Probably you deleted the decks you claimed with. Please remove claim and add new one.
        </div>
        <div class="bracket">
            @include('tournaments.partials.tabledin',
            ['columns' => ['title', 'location', 'date', 'cardpool', 'user_claim'],
            'title' => 'My tournaments', 'subtitle' => 'tournaments I registered to',
             'id' => 'my-table', 'icon' => 'fa-list-alt', 'loader' => true, 'doublerow' => true])
        </div>
    </div>
    <div class="col-lg-4 pull-lg-8 col-xs-12">
        {{--My calendar--}}
        <div class="bracket">
            <h5>
                <div class="pull-right">
                    <button class="btn btn-info" onclick="$('#info-subscribe').toggleClass('hidden-xs-up');">
                        <i class="fa fa-calendar-o"></i>
                        Subscribe
                    </button>
                    <div id="info-subscribe" class="hidden-xs-up">
                        <div class="small-text" style="padding-top: 0.2em; padding-bottom: 0.5em">
                            subscription URL
                            <span id="confirm-copy" class="hidden-xs-up">- <strong>copied</strong></span>
                        </div>
                        <div style="white-space: nowrap">
                            <button class="btn btn-primary btn-xs" style="margin-top: -5px"
                                    onclick="copySubscribe()">
                                <i class="fa fa-copy" title="copy"></i>
                            </button>
                            <input id="url-subscribe" type="text" onclick="select()"
                                   value="{{ env('APP_URL').'/calendar/user/'.$secret_id }}" size="60" readonly/>
                        </div>
                        <hr style="height: 1px"/>
                        <div class="small-text">
                            You can import these tournaments to the calendar of
                            your device. The calendar will be updated automatically.
                        </div>
                        <hr style="height: 1px"/>
                        <div class="small-text">
                            how to add to:<br/>
                            <a href="http://visihow.com/Use_webcal_url_to_add_a_calendar_to_google_calendar" target="_blank">Google Calendar</a>
                            - <a href="https://www.macobserver.com/tips/how-to/ios-add-shared-google-calendars-iphone/" target="_blank">iPhone/iPad</a>
                            - <a href="https://support.apple.com/en-gb/guide/calendar/subscribe-to-calendars-icl1022/mac" target="_blank">Mac</a>
                            - <a href="https://www.calendarwiz.com/knowledgebase/entry/71/" target="_blank">Outlook</a>
                        </div>
                    </div>
                </div>
                <i class="fa fa-calendar" aria-hidden="true"></i>
                My calendar<br/>
                <small>tournaments I registered to</small>
            </h5>
            @include('partials.calendar')
        </div>
        {{--My map--}}
        <div class="bracket">
            <h5>
                <i class="fa fa-globe" aria-hidden="true"></i>
                My map<br/>
                <small>tournaments I registered to</small>
            </h5>
            <div class="map-wrapper">
                <div id="mymap" style="height: 100%"></div>
            </div>
        </div>
    </div>
</div>