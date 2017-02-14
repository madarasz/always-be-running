{{--Draw statistics chart--}}
<div class="bracket">
    <h5>
        <i class="fa fa-bar-chart" aria-hidden="true"></i>
        Statistics
    </h5>
    <div class="loader-chart stat-load">loading</div>
    <div id="stat-chart-runner"></div>
    <div class="text-xs-center small-text p-b-1">runner IDs</div>
    <div class="loader-chart stat-load">loading</div>
    <div id="stat-chart-corp"></div>
    <div class="text-xs-center small-text">corp IDs</div>
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
                chartData = data;
                $('#button-showmatches').removeClass('disabled').prop("disabled", false);
            }
        });
    }

    // redraw charts on window resize
    $(window).resize(function(){
        drawEntryStats(chartData, 'runner', 'stat-chart-runner', playernum);
        drawEntryStats(chartData, 'corp', 'stat-chart-corp', playernum);
    });

    @if (session()->has('editmode'))
        // manual importing
        toggleEntriesEdit(true);
        window.location.hash = '#importing';
    @endif

</script>