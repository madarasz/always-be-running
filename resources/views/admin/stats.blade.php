<div class="tab-pane" id="tab-stats" role="tabpanel">
    <h5 class="p-t-2">Weekly stats</h5>
    <div id="chart1"></div>
    <div class="legal-bullshit">Reminder: the last week in graph is not a whole week.</div>
    <h5 class="p-t-2">General stats</h5>
    <div class="">
        <strong>Total number of users:</strong> <span id="stat-total-users"></span><br/>
        <strong>Total number of approved tournaments:</strong> <span id="stat-total-tournaments"></span><br/>
        <strong>Total number of claims:</strong> <span id="stat-total-entries"></span>
    </div>
    <h5 class="p-t-2">Tournaments per countries</h5>
    <div id="chart2"></div>
    <h5 class="p-t-2">
        Country stats
        <select id="selector-country-stats" onchange="getCountryStats()">
        </select>
    </h5>
    <table style="width: 100%">
        <tr>
            <td class="text-xs-center">
                <h6>tournaments</h6>
                <div id="chart3"></div>
            </td>
            <td class="text-xs-center">
                <h6>entries</h6>
                <div id="chart4"></div>
            </td>
        </tr>
    </table>
</div>