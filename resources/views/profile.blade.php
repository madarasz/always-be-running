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
            {{--User info--}}
            @include('profile.info')
            {{--Badges--}}
            @include('profile.badges')
            {{--Claims--}}
            @if ($claim_count)
                @include('profile.claims')
            @endif
            {{--Created tournaments--}}
            @if ($created_count)
                @include('profile.created')
            @endif
        </div>
        <div class="col-md-8 col-xs-12">
            {{--Usernames--}}
            @include('profile.usernames')
            {{--About--}}
            @include('profile.about')
            {{--second save button--}}
            <div class="text-xs-center">
                <a class="btn btn-info hidden-xs-up" href="#" id="button-save2"
                   onclick="document.getElementById('profile-form').submit()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </a>
            </div>
            {{--Tournament progress chart--}}
            @include('profile.tournament-chart')
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

        @endif
    </script>
@stop

