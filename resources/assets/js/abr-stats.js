//for generating statistics and Know the Meta integration

// gets the available data pack names from KtM
function getKTMDataPacks(callback) {
    $.ajax({
        url: "http://www.knowthemeta.com/JSON/Cardpacks",
        dataType: "json",
        async: true,
        success: function (data) {
            var result = [];
            for (var i = 0, len = data.length; i < len; i++) {
                if (data[i].datapacks.length) {
                    result = result.concat(data[i].datapacks);
                } else {
                    result.push(data[i].title);
                }
            }
            callback(result);
        }
    });
}

// update the popular IDs box on the homepage
function updatePopularIds(packname) {
    // update pack name
    $('#hot-packname').text(packname);
    // get runner
    $.ajax({
        url: "http://www.knowthemeta.com/JSON/Tournament/runner/" + packname,
        dataType: "json",
        async: true,
        success: function (data) {
            data.ids.sort(tournamentShorters.byAllStanding);
            addCardStat('#hot-id-runner', data.ids[0], data.allStandingCount, data.topStandingCount);
            // get corp
            $.ajax({
                url: "http://www.knowthemeta.com/JSON/Tournament/corp/" + packname,
                dataType: "json",
                async: true,
                success: function (data) {
                    data.ids.sort(tournamentShorters.byAllStanding);
                    addCardStat('#hot-id-corp', data.ids[0], data.allStandingCount, data.topStandingCount);
                }
            });
        }
    });
}

// for sorting tournament drilldown data from KtM
var tournamentShorters = {
    byTopStanding : function (a,b) {
        return (b.topStandingCount - a.topStandingCount);
    },
    byAllStanding : function (a,b) {
        return (b.allStandingCount - a.allStandingCount);
    },
    byAllDeck : function (a,b) {
        return (b.allDeckCount - a.allDeckCount);
    },
    byInTopDeck: function (a,b) {
        return (b.intopdecks - a.intopdecks);
    },
    byTopAllFractionStanding: function(a,b) {
        return (b.topStandingCount / b.allStandingCount - a.topStandingCount / a.allStandingCount)
    }
};

var idShorter = function (a,b) {
    return (b[1] - a[1]);
};

// adds card with statistics
function addCardStat(element, card, allCount, topCount) {
    $(element).append($('<a>', {
        href: 'http://www.knowthemeta.com/Cards/' + card.title + '/'
    }).append($('<img>', {
        src: imageURL(card.title)
    }), $('<div>', {
        class: 'small-text',
        text: card.title
    })), $('<div>', {
        class: 'small-text',
        text: 'all: ' + percentageToString(card.allStandingCount / allCount) +
        ' - top: ' + percentageToString(card.topStandingCount / topCount)
    }));
    $(element).removeClass('loader');
}

// generates image URL for KtM
function imageURL(title) {
    return "http://www.knowthemeta.com/static/img/cards/netrunner-" +
        title.toLowerCase().replace(new RegExp(" ", 'g'), "-").replace(new RegExp("[^a-z0-9.-]", 'g'), "") + ".png";
}

// pie charts on IDs on tournament detail page
function drawEntryStats(data, side, element, playersNum) {
    var stat_results = [['ID', 'number of decks', 'faction']];
    for (var i = 0, len = data.length; i < len; i++) {
        var found = false;
        for (var u = 1, len2 = stat_results.length; u < len2; u++) {
            if (data[i][side+'_deck_identity_title'] === stat_results[u][0]) {
                stat_results[u][1]++;
                found = true;
                break;
            }
        }
        if (!found) {
            stat_results.push([data[i][side+'_deck_identity_title'], 1, data[i][side+'_deck_identity_faction']]);
        }

    }

    stat_results.sort(idShorter);

    // adding unknown IDs
    if (data.length < playersNum ) {
        stat_results.push(['unknown', playersNum - data.length, 'unknown']);
    }

    var slices = [];
    for (var u = 1, len2 = stat_results.length; u < len2; u++) {
        slices.push({color: factionCodeToColor(stat_results[u][2])});
    }

    var chartdata = google.visualization.arrayToDataTable(stat_results);

    var options = {
        chartArea: {left:0,top:0,width:'100%',height:'100%'},
        slices: slices
    };

    var chart = new google.visualization.PieChart(document.getElementById(element));

    chart.draw(chartdata, options);
}