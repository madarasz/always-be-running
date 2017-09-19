@extends('layout.general')

@section('content')
    <h4 class="page-header">API documentation</h4>
    <div class="row">
        <div class="col-md-10 col-xs-12 offset-md-1">
            <div class="bracket">
                <h5 class="p-b-2">Terms of usage</h5>
                <p>
                    You may freely use my API endpoints for your site, app, project, etc.
                </p>
                <p>
                    In return, please add a backlink to <a href="https://alwaysberunning.net">https://alwaysberunning.net</a>.
                    You can use <a href="https://alwaysberunning.net/ms-icon-310x310.png">this ABR logo</a> if needed.
                </p>
                <hr/>
                <h5 class="p-b-2">Endpoints</h5>
                <p>
                    <strong>Upcoming tournaments and recurring events</strong>:
                    <a href="<?php env('APP_URL')?>/api/tournaments/upcoming">https://alwaysberunning.net/api/tournaments/upcoming</a>
                    <br/>
                    <em>Only approved events are displayed. Tournaments with past dates are filtered out.</em>
                    <blockquote class="help-markdown m-b-3">
                        {<br/>
                        &nbsp;&nbsp;"tournaments": [<em>event objects</em>]<br/>
                        &nbsp;&nbsp;"recurring_events": [<em>event objects</em>]<br/>
                        }
                    </blockquote>
                </p>
                <p>
                    <strong>Concluded tournamets</strong> (tournaments with results):
                    <a href="<?php env('APP_URL')?>/api/tournaments/results">https://alwaysberunning.net/api/tournaments/results</a>
                    <br/>
                    <em>Only approved and concluded tournaments are displayed.</em><br/>
                    Supports <strong>limit</strong> and <strong>offset</strong> parameters, example:
                    <a href="<?php env('APP_URL')?>/api/tournaments/results?offset=20&limit=10">https://alwaysberunning.net/api/tournaments/results?offset=20&limit=10</a>
                    <blockquote class="help-markdown m-b-3">
                        [<br/>
                        &nbsp;&nbsp;<em>event objects</em><br/>
                        ]
                    </blockquote>
                </p>
                <p>
                    <strong>Tournament entries</strong>:
                    https://alwaysberunning.net/api/entries?id=<em>[tournament ID]</em>
                    - <a href="<?php env('APP_URL')?>/api/entries?id=353">example</a>
                    <br/>
                    <em>Returns claims and imported entries for a single tournament.</em>
                    <blockquote class="help-markdown m-b-3">
                        [<br/>
                        &nbsp;&nbsp;<em>entry objects</em><br/>
                        ]
                    </blockquote>
                </p>
                <hr/>
                <h5 class="p-b-2">Returned objects</h5>
                <p>
                    <strong>Event objects</strong><br/>
                    <em>Events may be tournaments or recurring events.</em>
                </p>
                <table class="table table-sm table-striped abr-table">
                    <tr>
                        <th>property</th>
                        <th>type</th>
                        <th>description</th>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>general properties</em></td>
                    </tr>
                    <tr>
                        <td>id</td>
                        <td>int</td>
                        <td>event ID</td>
                    </tr>
                    <tr>
                        <td>title</td>
                        <td>string</td>
                        <td>event title</td>
                    </tr>
                    <tr>
                        <td>contact</td>
                        <td>string</td>
                        <td>event contact field</td>
                    </tr>
                    <tr>
                        <td>approved</td>
                        <td>int (0/1/-1)</td>
                        <td>if event was approved by an admin (waiting for approval/approved/rejected)</td>
                    </tr>
                    <tr>
                        <td>registration_count</td>
                        <td>int</td>
                        <td>number of users registered for the event</td>
                    </tr>
                    <tr>
                        <td>photos</td>
                        <td>int</td>
                        <td>number photos uploaded for the event</td>
                    </tr>
                    <tr>
                        <td>url</td>
                        <td>string</td>
                        <td>URL for the event</td>
                    </tr>
                    <tr>
                        <td>link_facebook</td>
                        <td>string</td>
                        <td>URL for related Facebook group / event</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>event creator related properties</em></td>
                    </tr>
                    <tr>
                        <td>creator_id</td>
                        <td>int</td>
                        <td>NetrunnerDB user ID</td>
                    </tr>
                    <tr>
                        <td>creator_name</td>
                        <td>string</td>
                        <td>NetrunnerDB user name - may be overridden by <em>'displayed user name'</em> setting in ABR profile</td>
                    </tr>
                    <tr>
                        <td>creator_supporter</td>
                        <td>int</td>
                        <td>
                            ABR supporter status:<br/>
                            - 0: not a supporter<br/>
                            - 1: one-time supporter<br/>
                            - 2: Patreon bioroid supporter<br/>
                            - 3: Patreon sysop supporter<br/>
                            - 4: Patreon executive supporter
                        </td>
                    </tr>
                    <tr>
                        <td>creator_class</td>
                        <td>string</td>
                        <td>
                            ABR user class (used for CSS):<br/>
                            - "": normal user<br/>
                            - "supporter": supporter<br/>
                            - "admin": admin (overrides supporter)
                        </td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>location related properties</em></td>
                    </tr>
                    <tr>
                        <td>location</td>
                        <td>string</td>
                        <td>location string, format: <em>country, [US state,] city</em></td>
                    </tr>
                    <tr>
                        <td>location_lat</td>
                        <td>float</td>
                        <td>location latitude</td>
                    </tr>
                    <tr>
                        <td>location_lng</td>
                        <td>float</td>
                        <td>location longitude</td>
                    </tr>
                    <tr>
                        <td>location_country</td>
                        <td>string</td>
                        <td>location country (in English)</td>
                    </tr>
                    <tr>
                        <td>location_state</td>
                        <td>string</td>
                        <td>location state (for US)</td>
                    </tr>
                    <tr>
                        <td>address</td>
                        <td>string</td>
                        <td>address string</td>
                    </tr>
                    <tr>
                        <td>store</td>
                        <td>string</td>
                        <td>store / venue name</td>
                    </tr>
                    <tr>
                        <td>place_id</td>
                        <td>string</td>
                        <td>place ID provided by Google Maps API</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>tournament related properties</em></td>
                    </tr>
                    <tr>
                        <td>cardpool</td>
                        <td>string</td>
                        <td>tournament legal cardpool up to</td>
                    </tr>
                    <tr>
                        <td>date</td>
                        <td>string</td>
                        <td>event date, YYYY.mm.dd. format</td>
                    </tr>
                    <tr>
                        <td>type</td>
                        <td>string</td>
                        <td>tournament type (GNK, store championship, regional championship, etc.)</td>
                    </tr>
                    <tr>
                        <td>format</td>
                        <td>string</td>
                        <td>tournament format (standard, cache refresh, draft etc.)</td>
                    </tr>
                    <tr>
                        <td>concluded</td>
                        <td>boolean</td>
                        <td>tournament conclusion</td>
                    </tr>
                    <tr>
                        <td>charity</td>
                        <td>boolean</td>
                        <td>if tournament is charity</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>concluded tournament properties</em></td>
                    </tr>
                    <tr>
                        <td>players_count</td>
                        <td>int</td>
                        <td>number of players in tournament</td>
                    </tr>
                    <tr>
                        <td>top_count</td>
                        <td>int</td>
                        <td>number of players in tournament top-cut</td>
                    </tr>
                    <tr>
                        <td>claim_count</td>
                        <td>int</td>
                        <td>number of claims (with decklist) in tournament</td>
                    </tr>
                    <tr>
                        <td>claim_conflict</td>
                        <td>boolean</td>
                        <td>if tournament has conflicting claims</td>
                    </tr>
                    <tr>
                        <td>matchdata</td>
                        <td>boolean</td>
                        <td>if tournament has match data (results were impored by NRTM app)</td>
                    </tr>
                    <tr>
                        <td>video</td>
                        <td>int</td>
                        <td>number of videos in tournament</td>
                    </tr>
                    <tr>
                        <td>winner_runner_identity</td>
                        <td>string</td>
                        <td>card ID of winner runner identity (get card IDs from NetrunnerDB API)</td>
                    </tr>
                    <tr>
                        <td>winner_corp_identity</td>
                        <td>string</td>
                        <td>card ID of winner corporation identity (get card IDs from NetrunnerDB API)</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>recurring event properties</em></td>
                    </tr>
                    <tr>
                        <td>recurring_day</td>
                        <td>string</td>
                        <td>name of day (in English)</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>multiple day event properties</em></td>
                    </tr>
                    <tr>
                        <td>end_date</td>
                        <td>string</td>
                        <td>event end date, YYYY.mm.dd. format</td>
                    </tr>
                </table>
                <p class="p-t-2">
                    <strong>Entry objects</strong><br/>
                    <em>player entries of a single tournament</em>
                </p>
                <table class="table table-sm table-striped abr-table">
                    <tr>
                        <th>property</th>
                        <th>type</th>
                        <th>description</th>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>player related properties</em></td>
                    </tr>
                    <tr>
                        <td>user_id</td>
                        <td>int</td>
                        <td>NetrunnerDB user ID (0 if imported entry)</td>
                    </tr>
                    <tr>
                        <td>user_name</td>
                        <td>string</td>
                        <td>NetrunnerDB user name - may be overridden by <em>'displayed user name'</em> setting in
                            ABR profile (null if imported entry)</td>
                    </tr>
                    <tr>
                        <td>user_import_name</td>
                        <td>string</td>
                        <td>user name coming from importing results</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>rank properties</em></td>
                    </tr>
                    <tr>
                        <td>rank_swiss</td>
                        <td>int</td>
                        <td>rank after swiss rounds</td>
                    </tr>
                    <tr>
                        <td>rank_top</td>
                        <td>int</td>
                        <td>final rank after top-cut (null if didn't reach top-cut, 0 if there was no top-cut)</td>
                    </tr>
                    <tr class="row-worlds">
                        <td colspan="3" class="text-xs-center"><em>deck related properties</em></td>
                    </tr>
                    <tr>
                        <td>runner_deck_title</td>
                        <td>string</td>
                        <td>title of runner deck</td>
                    </tr>
                    <tr>
                        <td>runner_deck_identity_id</td>
                        <td>string</td>
                        <td>card ID of runner identity (card ID coming from NetrunnerDB API)</td>
                    </tr>
                    <tr>
                        <td>runner_deck_url</td>
                        <td>string</td>
                        <td>URL for runner deck on NetrunnerDB</td>
                    </tr>
                    <tr>
                        <td>runner_deck_identity_title</td>
                        <td>string</td>
                        <td>name of runner identity</td>
                    </tr>
                    <tr>
                        <td>runner_deck_identity_faction</td>
                        <td>string</td>
                        <td>faction of runner identity</td>
                    </tr>
                    <tr>
                        <td>corp_deck_title</td>
                        <td>string</td>
                        <td>title of corporation deck</td>
                    </tr>
                    <tr>
                        <td>corp_deck_identity_id</td>
                        <td>string</td>
                        <td>card ID of corporation identity (card ID coming from NetrunnerDB API)</td>
                    </tr>
                    <tr>
                        <td>corp_deck_url</td>
                        <td>string</td>
                        <td>URL for corporation deck on NetrunnerDB</td>
                    </tr>
                    <tr>
                        <td>corp_deck_identity_title</td>
                        <td>string</td>
                        <td>name of corporatoin identity</td>
                    </tr>
                    <tr>
                        <td>corp_deck_identity_faction</td>
                        <td>string</td>
                        <td>faction of corporation identity</td>
                    </tr>
                </table>
                <hr/>
                <h5 class="p-b-2">For more information</h5>
                <p>
                    You can contact me via: alwaysberunning (at) gmail.com
                </p>
            </div>
        </div>
    </div>
@stop

