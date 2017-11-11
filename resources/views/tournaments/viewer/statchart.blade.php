{{--Draw statistics chart--}}
<div class="bracket">
    <h5 class="p-b-2">
        <i class="fa fa-bar-chart" aria-hidden="true"></i>
        Statistics
        <div class="pull-right">
            <div class="btn-group btn-group-sm hidden-xs-up" role="group" id="button-group-top">
                <button id="button-stats-all" type="button" class="btn btn-secondary active">all</button>
                <button id="button-stats-top" type="button" class="btn btn-secondary">top-cut</button>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button id="button-stats-id" type="button" class="btn btn-secondary active" disabled="disabled">IDs</button>
                <button id="button-stats-faction" type="button" class="btn btn-secondary" disabled="disabled">factions</button>
            </div>

        </div>

    </h5>
    {{--ID, all charts--}}
    <div id="chart-id">
        <div class="loader-chart stat-load">loading</div>
        <div id="stat-chart-runner"></div>
        <div class="text-xs-center small-text p-b-1">runner IDs</div>
        <div id="stat-chart-corp"></div>
        <div class="text-xs-center small-text">corp IDs</div>
    </div>
    {{--Faction, all charts--}}
    <div id="chart-faction" class="hidden-xs-up">
        <div id="stat-chart-runner-faction"></div>
        <div class="text-xs-center small-text p-b-1">runner factions</div>
        <div id="stat-chart-corp-faction"></div>
        <div class="text-xs-center small-text">corp factions</div>
    </div>
    {{--ID, top charts--}}
    <div id="chart-id-top" class="hidden-xs-up">
        <div class="loader-chart stat-load">loading</div>
        <div id="stat-chart-runner-top"></div>
        <div class="text-xs-center small-text p-b-1">runner IDs</div>
        <div id="stat-chart-corp-top"></div>
        <div class="text-xs-center small-text">corp IDs</div>
    </div>
    {{--Faction, top charts--}}
    <div id="chart-faction-top" class="hidden-xs-up">
        <div id="stat-chart-runner-faction-top"></div>
        <div class="text-xs-center small-text p-b-1">runner factions</div>
        <div id="stat-chart-corp-faction-top"></div>
        <div class="text-xs-center small-text">corp factions</div>
    </div>
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var chartData, chartDataTop = [],
        showIDs = true,
        showAll = true,
        playernum = parseInt('{{ $tournament->players_number }}'),
        topnum = parseInt('{{ $tournament->top_number }}');

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        $.ajax({
            url: "/api/entries?id={{ $tournament->id }}",
            dataType: "json",
            async: true,
            success: function (data) {
                $('.stat-load').addClass('hidden-xs-up');
                chartData = data;

                // make top-cut data
                if (topnum > 0) {
                    for (var i = 0; i < chartData.length; i++) {
                        if (parseInt(chartData[i].rank_top) > 0) {
                            chartDataTop.push(chartData[i]);
                        }
                    }

                    $('#button-group-top').removeClass('hidden-xs-up');
                }

                drawTournamentCharts();
                $('#button-showmatches').removeClass('disabled').prop("disabled", false);
                $('#button-stats-id').prop("disabled", false);
                $('#button-stats-faction').prop("disabled", false);
            }
        });
    }

    // stat switcher buttons
    $('#button-stats-id').click(function() {
        $('#button-stats-id').addClass('active');
        $('#button-stats-faction').removeClass('active');
        showIDs = true;
        setChartVisibility();

    });
    $('#button-stats-faction').click(function() {
        $('#button-stats-id').removeClass('active');
        $('#button-stats-faction').addClass('active');
        showIDs = false;
        setChartVisibility();
    });
    $('#button-stats-all').click(function() {
        $('#button-stats-all').addClass('active');
        $('#button-stats-top').removeClass('active');
        showAll = true;
        setChartVisibility();

    });
    $('#button-stats-top').click(function() {
        $('#button-stats-all').removeClass('active');
        $('#button-stats-top').addClass('active');
        showAll = false;
        setChartVisibility();
    });

    function setChartVisibility(showOverride) {
        // if charts are being redrawn, they need to be visible to avoid glitches
        if (showOverride) {
            $('#chart-id').removeClass('hidden-xs-up');
            $('#chart-faction').removeClass('hidden-xs-up');
            $('#chart-id-top').removeClass('hidden-xs-up');
            $('#chart-faction-top').removeClass('hidden-xs-up');
            return true;
        }

        // hide all
        $('#chart-id').addClass('hidden-xs-up');
        $('#chart-faction').addClass('hidden-xs-up');
        $('#chart-id-top').addClass('hidden-xs-up');
        $('#chart-faction-top').addClass('hidden-xs-up');

        // show only the selected
        if (showIDs && showAll) {
            $('#chart-id').removeClass('hidden-xs-up');
        } else if (!showIDs && showAll) {
            $('#chart-faction').removeClass('hidden-xs-up');
        } else if (showIDs && !showAll) {
            $('#chart-id-top').removeClass('hidden-xs-up');
        } else {
            $('#chart-faction-top').removeClass('hidden-xs-up');
        }
    }

    // redraw charts on window resize
    $(window).resize(drawTournamentCharts);

    function drawTournamentCharts() {
        setChartVisibility(true);
        drawEntryStats(chartData, 'runner', 'stat-chart-runner', playernum);
        drawEntryStats(chartData, 'corp', 'stat-chart-corp', playernum);
        drawEntryStats(chartData, 'runner', 'stat-chart-runner-faction', playernum, true);
        drawEntryStats(chartData, 'corp', 'stat-chart-corp-faction', playernum, true);
        if (topnum > 0) {
            drawEntryStats(chartDataTop, 'runner', 'stat-chart-runner-top', topnum);
            drawEntryStats(chartDataTop, 'corp', 'stat-chart-corp-top', topnum);
            drawEntryStats(chartDataTop, 'runner', 'stat-chart-runner-faction-top', topnum, true);
            drawEntryStats(chartDataTop, 'corp', 'stat-chart-corp-faction-top', topnum, true);
        }
        setChartVisibility();
    }

</script>