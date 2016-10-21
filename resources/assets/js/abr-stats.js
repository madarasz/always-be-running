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

// adds card with statistics
function addCardStat(element, card, allCount, topCount) {
    $(element).append($('<a>', {
        href: 'http://www.knowthemeta.com/Cards/' + card.title + '/'
    }).append($('<img>', {
        src: imageURL(card.title)
    }), $('<div>', {
        class: 'spotlight-title',
        text: card.title
    })), $('<div>', {
        class: 'spotlight-title',
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