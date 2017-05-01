@extends('layout.general')

@section('content')
    {!! Form::open(['url' => '/profile/'.$user->id, 'id' => 'profile-form']) !!}
    <h4 class="page-header p-b-1">
        {{--Edit button--}}
        @if (Auth::check() && Auth::user()->id == $user->id)
            <div class="pull-right">
                <a class="btn btn-primary" href="#" onclick="profileSwitchEdit()" id="button-edit">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                </a>
                <a class="btn btn-secondary hidden-xs-up" href="#" onclick="profileSwitchView()" id="button-cancel">
                    <i class="fa fa-times" aria-hidden="true"></i> Cancel
                </a>
                <a class="btn btn-info hidden-xs-up" href="#" id="button-save"
                   onclick="document.getElementById('profile-form').submit()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </a>
            </div>
        @endif
        Profile - <span {{ $user->supporter ? 'class=supporter' : '' }}>{{ $user->displayUsername() }}</span>
    </h4>
    @include('partials.message')
    @include('errors.list')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--Supporter strip--}}
            @if ($user->supporter)
                <div class="alert alert-warning">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    This user <a href="/support-me" class="supporter">supports</a> AlwaysBeRunning.net
                </div>
            @endif
            {{--Badge notification--}}
            <div class="alert alert-success view-indicator notif-green notif-badge-page hidden-xs-up" id="notif-profile" data-badge="">
                You have new badges.
            </div>
            {{--User info--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    User
                </h5>
                <div class="text-xs-center p-b-1">
                    <h6 {{ $user->supporter ? 'class=supporter' : '' }}>{{ $user->displayUsername() }}</h6>
                    <div class="user-counts">
                        {{ $created_count }} tournament{{ $created_count > 1 ? 's' : '' }} created<br/>
                        {{ $claim_count }} tournament claim{{ $claim_count > 1 ? 's' : '' }}<br/>
                        {{ $user->published_decks }} published deck{{ $user->published_decks > 1 ? 's' : '' }}
                        @if ($user->private_decks)
                            <br/>
                            {{ $user->private_decks }} private deck{{ $user->private_decks > 1 ? 's' : '' }}
                        @endif
                        @if ($user->reputation)
                            <br/>
                            {{ $user->reputation }} reputation on NetrunnerDB
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                            'You receive reputation on NetrunnerDB for the following:<br/>
                            +5 point for each favorite on your decklist<br/>
                            +1 point for each like on your decklist<br/>
                            +1 point for each like on your card review'])
                        @endif
                        {{--Admin info--}}
                        @if (Auth::user() && Auth::user()->admin)
                            <br/>
                            <strong>admin info - email:</strong> {{ $user->email }}
                            <br/>
                            <strong>admin info - first login:</strong> {{ $user->created_at }}
                            <br/>
                            <strong>admin info - last login:</strong> {{ $user->updated_at }}
                        @endif
                    </div>
                </div>
            </div>
            {{--Badges--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-trophy" aria-hidden="true"></i>
                    Badges
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            'You receive badges as achiements for various activities.<br/>
                            <br/>
                            <a href="/badges/">full list of badges</a>'])
                </h5>
                <div class="text-xs-center">
                    @forelse($user->badges as $badge)
                        <div class="{{ (@$page_section == 'profile' && !$badge->pivot->seen) ? 'new-badge notif-green' : 'inline-block'}}">
                            <img src="/img/badges/{{ $badge->filename }}" data-html="true"
                                 data-toggle="tooltip" data-placement="top"
                                 title="<strong>{{ $badge->name }}</strong><br/>{{ $badge->description }}"/>
                        </div>
                    @empty
                        <div class="m-b-2 font-italic text-xs-center">no badges yet</div>
                    @endforelse
                </div>
            </div>
            {{--Claims--}}
            @if ($claim_count)
                <div class="bracket">
                    <h5>
                        <i class="fa fa-list-ol" aria-hidden="true"></i>
                        Claims ({{$claim_count}})
                    </h5>
                    <ul>
                        @foreach($claims as $claim)
                            @if ($claim->tournament->tournament_type_id > 1 && $claim->tournament->tournament_type_id < 6)
                                <li style="list-style: none">
                            @else
                                <li>
                            @endif
                                @include('tournaments.partials.list-type', ['tournament' => $claim->tournament, 'class' => 'no-li'])
                                <strong>#{{ $claim->rank() }} / {{ $claim->tournament->players_number }}</strong>
                                @if ($claim->type == 3)
                                    <a href="{{ $claim->runner_deck_url() }}" title="{{ $claim->runner_deck_title }}"><img src="/img/ids/{{ $claim->runner_deck_identity }}.png"></a>&nbsp;<a href="{{ $claim->corp_deck_url() }}" title="{{ $claim->corp_deck_title }}"><img src="/img/ids/{{ $claim->corp_deck_identity }}.png"></a>
                                @else
                                    <img src="/img/ids/{{ $claim->runner_deck_identity }}.png">&nbsp;<img src="/img/ids/{{ $claim->corp_deck_identity }}.png">
                                @endif
                                <a href="{{ $claim->tournament->seoUrl() }}">
                                    {{ $claim->tournament->title }}
                                </a>
                                <span class="legal-bullshit">({{ $claim->tournament->date }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{--Created tournaments--}}
            @if ($created_count)
                <div class="bracket">
                    <h5>
                        <i class="fa fa-list-alt" aria-hidden="true"></i>
                        Created tournaments ({{$created_count}})
                    </h5>
                    <ul>
                        @foreach($created as $tournament)
                            <li>
                                @include('tournaments.partials.list-type', ['tournament' => $tournament, 'class' => 'no-li'])
                                <a href="{{ $tournament->seoUrl() }}">
                                    {{ $tournament->title }}
                                </a><br/>
                                <div class="small-text">
                                    {{ $tournament->tournament_type()->first()->type_name }} -
                                    @if($tournament->tournament_type_id != 7)
                                        {{ $tournament->location_country }}, {{$tournament->location_country === 'United States' ? $tournament->location_state.', ' : ''}}{{ $tournament->location_city }}
                                    @else
                                        online
                                    @endif
                                    @if ($tournament->date)
                                        ({{ $tournament->date }})
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-md-8 col-xs-12">
            {{--Usernames--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                    Usernames
                </h5>
                {{--NetrunnerDB--}}
                <div class="form-group row">
                    <label class="col-xs-3 col-form-label">NetrunnerDB:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label">
                            <a href="https://netrunnerdb.com/en/profile/{{ $user->id }}">{{ $user->name }}</a>
                        </div>
                    </div>
                </div>
                {{--Preferred--}}
                <div class="form-group row">
                    <label for="username_preferred" class="col-xs-3 col-form-label">displayed username:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_preferred }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_preferred" name="username_preferred"
                               placeholder="leave empty to use NetrunnerDB" value="{{ $user->username_preferred }}">
                    </div>
                </div>
                <hr/>
                <div class="legal-bullshit text-xs-center">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    WARNING: we cannot ensure the authenticity of these usernames
                </div>
                <div class="form-group row">
                    <label for="username_real" class="col-xs-3 col-form-label">real name:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_real }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_real"
                               name="username_real" value="{{ $user->username_real }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_jinteki" class="col-xs-3 col-form-label">Jinteki.net:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_jinteki }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_jinteki"
                               name="username_jinteki" value="{{ $user->username_jinteki }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_stimhack" class="col-xs-3 col-form-label">Stimhack forum:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="https://forum.stimhack.com/users/{{ $user->username_stimhack }}">{{ $user->username_stimhack }}</a>
                        </div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_stimhack"
                               name="username_stimhack" value="{{ $user->username_stimhack }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_twitter" class="col-xs-3 col-form-label">Twitter:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="https://twitter.com/{{ $user->username_twitter }}">
                                {{ $user->username_twitter ? '@'.$user->username_twitter : '' }}
                            </a>
                        </div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_twitter"
                               name="username_twitter" placeholder="username without @" value="{{ $user->username_twitter }}">
                    </div>
                </div>
            </div>
            {{--About--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-book" aria-hidden="true"></i>
                    About
                </h5>
                {{--Country--}}
                <div class="form-group row">
                    <label for="username_real" class="col-xs-3 col-form-label">country:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            @if ($user->country)
                                <img src="/img/flags/{{ $user->country->flag }}"/>
                                {{ $user->country->name }}
                            @else
                                --- not set ---
                            @endif
                        </div>
                        @if (@$countries)
                            <select class="form-control profile-field hidden-xs-up" id="country_id"
                                   name="country_id">
                                <option value="0">--- not set ---</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ $user->country_id == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div>
                                {!! Form::checkbox('autofilter_upcoming', null,
                                    in_array($user->autofilter_upcoming, [1, '1', 'on'], true),
                                    ['id' => 'autofilter_upcoming', 'class' => 'profile-field hidden-xs-up']) !!}
                                <label for="autofilter_upcoming" class="small-text profile-field {{ $user->autofilter_upcoming ? '' :'hidden-xs-up' }}">
                                    <em>use as default filter for Upcoming tournaments</em>
                                </label>
                            </div>
                            <div>
                                {!! Form::checkbox('autofilter_results', null,
                                    in_array($user->autofilter_results, [1, '1', 'on'], true),
                                    ['id' => 'autofilter_results', 'class' => 'profile-field hidden-xs-up']) !!}
                                <label for="autofilter_results" class="small-text profile-field {{ $user->autofilter_results ? '' :'hidden-xs-up' }}">
                                    <em>use as default filter for tournament Results</em>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                {{--Favorite faction--}}
                <div class="form-group row">
                    <label for="favorite_faction" class="col-xs-3 col-form-label">favorite faction:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <span id="faction_logo" class="icon"></span>
                            <span id="faction_text"></span>
                        </div>
                        @if (@$factions)
                            <select class="form-control profile-field hidden-xs-up" id="favorite_faction"
                                    name="favorite_faction">
                                <option value="">--- not set ---</option>
                                @foreach($factions as $faction)
                                    <option value="{{ $faction->faction_code }}" {{ $user->favorite_faction === $faction->faction_code ? 'selected' : ''}}></option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
                {{--Website--}}
                <div class="form-group row">
                    <label for="website" class="col-xs-3 col-form-label">website:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="{{ $user->website }}">{{ $user->website }}</a>
                        </div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="website"
                               name="website" value="{{ $user->website }}" placeholder="http://...">
                    </div>
                </div>
                {{--About--}}
                <div class="form-group row">
                    <label for="about" class="col-xs-3 col-form-label">about me:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text markdown-content">
                            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $user->about)) !!}
                        </div>
                        {!! Form::textarea('about', $user->about, ['rows' => 6, 'cols' => '', 'class' => 'form-control profile-field hidden-xs-up']) !!}
                        <div class="pull-right profile-field hidden-xs-up">
                            <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a><br/>
                                    <br/>
                                    How to make your tournament look cool?<br/>
                                    <a href="/markdown" target="_blank">example formatted description</a>'])
                        </div>
                    </div>
                </div>
            </div>
            {{--second save button--}}
            <div class="text-xs-center">
                <a class="btn btn-info hidden-xs-up" href="#" id="button-save2"
                   onclick="document.getElementById('profile-form').submit()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </a>
            </div>
            {{--Tournament progress chart--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    Tournament claims
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            'This chart shows your tournament rankings over time.
                            The higher the bubble is the closer you were to the first place.
                            Bubble size reflects number of players on the tournament.'])
                </h5>
                @if ($claim_count > 2)
                    <div id="chart-claim"></div>
                @else
                    <div class="text-xs-center small-text m-t-1 m-b-1">
                        Claim in at least 3 tournaments to make the chart appear.
                    </div>
                @endif
            </div>
        </div>
    </div>
    {!! Form::close() !!}

    {{--Flaticon legal--}}
    @include('partials.legal-icons')

    @if ($claim_count > 2)
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    @endif

    <script type="text/javascript">
        function profileSwitchEdit() {
            $('.profile-text').addClass('hidden-xs-up');
            $('.profile-field').removeClass('hidden-xs-up');
            $('#button-save').removeClass('hidden-xs-up');
            $('#button-save2').removeClass('hidden-xs-up');
            $('#button-cancel').removeClass('hidden-xs-up');
            $('#button-edit').addClass('hidden-xs-up');
        }
        function profileSwitchView() {
            $('.profile-text').removeClass('hidden-xs-up');
            $('.profile-field').addClass('hidden-xs-up');
            $('#button-save').addClass('hidden-xs-up');
            $('#button-save2').addClass('hidden-xs-up');
            $('#button-cancel').addClass('hidden-xs-up');
            $('#button-edit').removeClass('hidden-xs-up');
        }

        // favorite faction
        @if (@$factions)
            $('#favorite_faction option').each(function(i, obj) {
                if (i > 0) {
                    obj.text = factionCodeToFactionTitle(obj.value);
                }
            });
        @endif

        document.getElementById('faction_text').textContent = factionCodeToFactionTitle('{{ $user->favorite_faction }}');
        $('#faction_logo').addClass('icon-' + '{{ $user->favorite_faction }}');

        @if ($claim_count > 2)

            // tournament claims chart
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawClaimChart);
            var chart, chartOptions, chartDataTable;

            function drawClaimChart() {

                chartDataTable = new google.visualization.DataTable();

                chartDataTable.addColumn('date', 'date');
                chartDataTable.addColumn('number', 'rank');
                chartDataTable.addColumn({ type: 'string', role: 'style' });
                chartDataTable.addColumn({ type: 'string', role: 'tooltip', 'p': {'html': true} });

                // polinomial constants for size calculation
                var poly1 = -0.00084, poly2 = 0.2789, poly3 = 2.8224;
//                var poly1 = -0.0013, poly2 = 0.4071, poly3 = 1.8287;

                @foreach($claims->reverse() as $claim)

                    <?php
                        $tooltip = '<div style="padding: 0.5em"><strong>'.addslashes($claim->tournament->title).
                            '</strong><br/>claim: #'.$claim->rank().'/'.$claim->tournament->players_number.
                            '&nbsp;<img src="/img/ids/'.$claim->runner_deck_identity.'.png">&nbsp;<img src="/img/ids/'.$claim->corp_deck_identity.'.png"></div>';
                    ?>

                    chartDataTable.addRow([
                        new Date('{{ $claim->tournament->date }}'),
                        {{ ($claim->rank() - $claim->tournament->players_number) / (-$claim->tournament->players_number+1)}},
                        'point { fill-color: ' + tournamentTypeToColor({{$claim->tournament->tournament_type_id}}) +
                            '; size: ' + Math.round(poly1 * {{ $claim->tournament->players_number }} * {{ $claim->tournament->players_number }} + poly2 * {{ $claim->tournament->players_number }} + poly3) +
                            {{--'; size: ' + Math.round(0.1033 * {{ $claim->tournament->players_number }} + 4.1736) +--}}
                            {{--'; size: ' + Math.round(4.52779 * Math.log(3.01709 * {{ $claim->tournament->players_number }} )) +--}}
                            '; stroke-color: #fff }',
                        '{!! $tooltip !!}'
                    ]);

                @endforeach

                chartOptions = {
                    legend: 'none',
                    tooltip: { isHtml: true },
                    series: { 0: { lineWidth: 0, pointSize: 5 } },
                    vAxis: { viewWindow: { min: -0.2, max: 1.2 }, ticks: [{ v: 1, f: 'first'}, {v: 0, f: 'last'}]  }
                };


                chart = new google.visualization.LineChart(document.getElementById('chart-claim'));
                chart.draw(chartDataTable, chartOptions);
            }

            function tournamentTypeToColor(type) {
                switch (type) {
                    case 2: return '#3a8922'; // store
                    case 3: return '#722086'; // regional
                    case 4: return '#24598a'; // national
                    case 5: return '#892222'; // worlds
                    case 9: return '#8a5e25'; // continental
                    default: return 'grey';
                }
            }

        @endif
    </script>
@stop

