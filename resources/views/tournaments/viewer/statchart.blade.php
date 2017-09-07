{{--Draw statistics chart--}}
<div class="bracket">
    <h5 class="p-b-1">
        <i class="fa fa-bar-chart" aria-hidden="true"></i>
        Statistics
        <div class="btn-group btn-group-sm pull-right" role="group">
            <button id="button-stats-id" type="button" class="btn btn-secondary active" disabled="disabled">IDs</button>
            <button id="button-stats-faction" type="button" class="btn btn-secondary" disabled="disabled">factions</button>
        </div>
    </h5>
    {{--ID charts--}}
    <div id="chart-id">
        <div class="loader-chart stat-load">loading</div>
        <div id="stat-chart-runner"></div>
        <div class="text-xs-center small-text p-b-1">runner IDs</div>
        <div id="stat-chart-corp"></div>
        <div class="text-xs-center small-text">corp IDs</div>
    </div>
    {{--Faction charts--}}
    <div id="chart-faction">
        <div id="stat-chart-runner-faction"></div>
        <div class="text-xs-center small-text p-b-1">runner factions</div>
        <div id="stat-chart-corp-faction"></div>
        <div class="text-xs-center small-text">corp factions</div>
    </div>
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var chartData, playernum = parseInt('{{ $tournament->players_number }}');
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        $.ajax({
            url: "/api/entries?id={{ $tournament->id }}",
            dataType: "json",
            async: true,
            success: function (data) {
                $('.stat-load').addClass('hidden-xs-up');
                drawEntryStats(data, 'runner', 'stat-chart-runner', playernum);
                drawEntryStats(data, 'corp', 'stat-chart-corp', playernum);
                drawEntryStats(data, 'runner', 'stat-chart-runner-faction', playernum, true);
                drawEntryStats(data, 'corp', 'stat-chart-corp-faction', playernum, true);
                chartData = data;
                $('#chart-faction').addClass('hidden-xs-up');
                $('#button-showmatches').removeClass('disabled').prop("disabled", false);
                $('#button-stats-id').prop("disabled", false);
                $('#button-stats-faction').prop("disabled", false);
            }
        });
    }

    // stat switcher
    $('#button-stats-id').click(function() {
        $('#button-stats-id').addClass('active');
        $('#button-stats-faction').removeClass('active');
        $('#chart-id').removeClass('hidden-xs-up');
        $('#chart-faction').addClass('hidden-xs-up');
    });
    $('#button-stats-faction').click(function() {
        $('#button-stats-id').removeClass('active');
        $('#button-stats-faction').addClass('active');
        $('#chart-id').addClass('hidden-xs-up');
        $('#chart-faction').removeClass('hidden-xs-up');
    });

    // redraw charts on window resize
    $(window).resize(function(){
        drawEntryStats(chartData, 'runner', 'stat-chart-runner', playernum);
        drawEntryStats(chartData, 'corp', 'stat-chart-corp', playernum);
        drawEntryStats(data, 'runner', 'stat-chart-runner-faction', playernum, true);
        drawEntryStats(data, 'corp', 'stat-chart-corp-faction', playernum, true);
    });

</script>