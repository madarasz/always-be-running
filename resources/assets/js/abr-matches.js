// contains JS for displaying match results, total mindf*ck

// prepares row in matches table
function prepareMatchRow(tbodyid, topcut, rowcClass, matchId) {
    if (topcut) {
        // top-cut
        $('#' + tbodyid).append($('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            class: 'text-xs-right',
            id: matchId + 'p1n'
        }), $('<td>', {
            id: matchId + 'p2n'
        })), $('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            id: matchId + 'p1d',
            class: 'small-text text-xs-right'
        }), $('<td>', {
            id: matchId + 'p2d',
            class: 'small-text'
        })), $('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            class: 'text-xs-right font-weight-bold',
            id: matchId + 'p1-win'
        }), $('<td>', {
            class: 'font-weight-bold',
            id: matchId + 'p2-win'
        })));
    } else {
        // swiss
        $('#' + tbodyid).append($('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            class: 'text-xs-right',
            id: matchId + 'p1n'
        }), $('<td>', {
            class: 'text-xs-center font-weight-bold',
            id: matchId + 'score'
        }), $('<td>', {
            id: matchId + 'p2n'
        })), $('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            id: matchId + 'p1c',
            class: 'small-text text-xs-right'
        }), $('<td>', {
            class: 'small-text text-xs-center',
            id: matchId + 'score1'
        }), $('<td>', {
            class: 'small-text',
            id: matchId + 'p2r'
        })), $('<tr>', {
            class: rowcClass
        }).append($('<td>', {
            class: 'small-text text-xs-right',
            id: matchId + 'p1r'
        }), $('<td>', {
            class: 'small-text text-xs-center',
            id: matchId + 'score2'
        }), $('<td>', {
            class: 'small-text',
            id: matchId + 'p2c'
        })));
    }
}

// fills player details in match table
function fillMatchPlayer(player, playernum, matchId, match) {
    if (player) {
        // player name
        if (player.user_id > 0) {   // user
            $('#' + matchId + 'p' + playernum +'n').append($('<a>', {
                text: player.user_name,
                href: '/profile/' + player.user_id
            }));
        } else {    // player name imported
            $('#' + matchId + 'p' + playernum +'n').text(player.user_import_name);
        }
        // decks
        if (match.eliminationGame) {
            // prepare elements
            var deck, id = $('<img>', {
                src: '/img/ids/' + player[match['player' + playernum].role + '_deck_identity_id'] + '.png'
            });
            if (player[match['player' + playernum].role +'_deck_url']) {
                deck = $('<a>', {
                    text: player[match['player' + playernum].role + '_deck_title'],
                    href: player[match['player' + playernum].role + '_deck_url']
                });
            } else {
                deck = $('<span>', { text: player[match['player' + playernum].role + '_deck_title'] });
            }

            // insert elements
            if (playernum == 1) {
                $('#' + matchId + 'p' + playernum + 'd').append(deck, id);
            } else {
                $('#' + matchId + 'p' + playernum + 'd').append(id, deck);
            }
        } else {
            // prepare ids
            var id1 = $('<img>', {
                src: '/img/ids/' + player.corp_deck_identity_id + '.png'
            }), id2 = $('<img>', {
                src: '/img/ids/' + player.runner_deck_identity_id + '.png'
            }), deck1, deck2;

            // prepare decks
            if (player.corp_deck_url) {
                deck1 = $('<a>', { text: player.corp_deck_title, href: player.corp_deck_url });
            } else {
                deck1 = $('<span>', { text: player.corp_deck_title });
            }
            if (player.runner_deck_url) {
                deck2 = $('<a>', { text: player.runner_deck_title, href: player.runner_deck_url });
            } else {
                deck2 = $('<span>', { text: player.runner_deck_title });
            }

            // insert elements
            if (playernum == 1) {
                $('#' + matchId + 'p' + playernum + 'c').append(deck1, id1);
                $('#' + matchId + 'p' + playernum + 'r').append(deck2, id2);
            } else {
                $('#' + matchId + 'p' + playernum + 'c').append(id1, deck1);
                $('#' + matchId + 'p' + playernum + 'r').append(id2, deck2);
            }
        }
    } else {
        // BYE
        $('#'+matchId+'p' + playernum +'r').append($('<span>', {
            text: 'BYE'
        }));
    }
}

function fillMatchRow(player1, player2, matchId, match) {
    fillMatchPlayer(player1, 1, matchId, match);
    fillMatchPlayer(player2, 2, matchId, match);

    // scores
    if (match.eliminationGame) {
        if (match.player1.winner) {
            $('#' + matchId + 'p1-win').text('WINS');
            $('#' + matchId + 'p2-win').text('LOSES');
        } else {
            $('#' + matchId + 'p1-win').text('LOSES');
            $('#' + matchId + 'p2-win').text('WINS');
        }
    } else {
        $('#' + matchId + 'score').html((match.player1.corpScore + match.player1.runnerScore) + '&nbsp;-&nbsp;' + (match.player2.corpScore + match.player2.runnerScore));
        $('#' + matchId + 'score1').html(match.player1.corpScore + '&nbsp;-&nbsp;' + match.player2.runnerScore);
        $('#' + matchId + 'score2').html(match.player1.runnerScore + '&nbsp;-&nbsp;' + match.player2.corpScore);
    }
}

function displayMatches(id) {
    $('#button-showmatches').addClass('hidden-xs-up');
    $('#loader-content').removeClass('hidden-xs-up');
    $('#content-matches').addClass('p-b-3');

    $.ajax({
        url: '/tjsons/' + id +'.json',
        dataType: "json",
        async: true,
        success: function (data) {
            if (!('rounds' in data)) {
                $('#warning-matches-top').removeClass('hidden-xs-up');
                $('#warning-matches-swiss').removeClass('hidden-xs-up');
            } else {
                // link JSON players to DB entries
                var idToIndex = [];
                for (var index = 0, len = data['players'].length; index < len; index ++) {
                    idToIndex[data['players'][index].id] = index;
                    for (var u = 0, len2 = chartData.length; u < len2; u++) {
                        if (parseInt(chartData[u].rank_swiss) == parseInt(data['players'][index].rank)) {
                            data['players'][index].entry_id = u;
                            break;
                        }
                    }
                }

                // process rounds
                var doubleElimination = { teams: [], results: [[], [], []]}, // input array for jQuery Bracket
                    eliminationLosers = [], // players who have lost once
                    eliminationDecks = [[], [], []],    // deck IDs for elimination tree
                    eliminationWinners = [];   // list of winners to get player order in matches (player with the latest win is first)

                for (index = 0, len = data['rounds'].length; index < len; index++) {
                    // prepare tbody
                    var tbodyid;

                    if (data['rounds'][index][0].eliminationGame) {
                        // top cut

                        tbodyid = 'tbody-matches-top-' + (index + 1);
                        $('#table-matches-top').append($('<thead>').append($('<th>', {
                            text: 'Top-cut bracket'
                        })), $('<tbody>', {
                            id: tbodyid
                        }));

                        doubleElimination.results[0].push([]); eliminationDecks[0].push([]);
                        doubleElimination.results[1].push([]); eliminationDecks[1].push([]);
                        doubleElimination.results[2].push([]); eliminationDecks[2].push([]);

                    } else {

                        // swiss
                        tbodyid = 'tbody-matches-swiss-' + (index + 1);
                        $('#table-matches-swiss').append($('<thead>').append($('<th>', {
                            text: 'Round ' + (index + 1)
                        })), $('<tbody>', {
                            id: tbodyid
                        }));
                    }

                    // process each match in round
                    for (u = 0, len2 = data['rounds'][index].length; u < len2; u++) {
                        var match = data['rounds'][index][u],
                            player1 = null, player2 = null,
                            matchId= 'match-'+index+'-'+u+'-',
                            rowcClass = u % 2 ? '' : 'row-colored';

                        if (match.player1.id) {
                            player1 = chartData[data['players'][idToIndex[match.player1.id]].entry_id];
                        }
                        if (match.player2.id) {
                            player2 = chartData[data['players'][idToIndex[match.player2.id]].entry_id];
                        }

                        // prepare row
                        if (match.eliminationGame) {

                            // switch players if needed to get right player order
                            if (checkPlayerOrder(player1, player2, eliminationWinners, eliminationLosers, data.cutToTop)) {
                                var playerm = match.player1; match.player1 = match.player2; match.player2 = playerm;
                                player1 = chartData[data['players'][idToIndex[match.player1.id]].entry_id];
                                player2 = chartData[data['players'][idToIndex[match.player2.id]].entry_id];
                            }

                            // get elimination data
                            prepareEliminationData(player1, player2, match, doubleElimination.teams,
                                doubleElimination.results, eliminationLosers, eliminationDecks, data.cutToTop);

                            // build top-cut results table
                            prepareMatchRow(tbodyid, true, rowcClass, matchId);

                        } else {

                            // build swiss results table
                            prepareMatchRow(tbodyid, false, rowcClass, matchId);
                        }

                        // fill row
                        fillMatchRow(player1, player2, matchId, match);
                    }

                    // adding winners, losers in a reverse order for proper player order in result array
                    for (u = data['rounds'][index].length -1 ; u >= 0; u--) {
                        var match = data['rounds'][index][u],
                            player1, player2, loserName;
                        if (match.eliminationGame) {
                            player1 = chartData[data['players'][idToIndex[match.player1.id]].entry_id];
                            player2 = chartData[data['players'][idToIndex[match.player2.id]].entry_id];
                            if (match.player1.winner) {
                                eliminationWinners.push(getPlayerName(player1));
                                loserName = getPlayerName(player2);
                            } else {
                                eliminationWinners.push(getPlayerName(player2));
                                loserName = getPlayerName(player1);
                            }
                            if (eliminationLosers.indexOf(loserName) == -1) {
                                eliminationLosers.push(loserName);
                            }
                        }
                    }

                    // empty unused rows
                    for (u = 0; u < 3; u++) {
                        if (match.eliminationGame && doubleElimination.results[u][doubleElimination.results[u].length - 1].length == 0) {
                            doubleElimination.results[u].splice(doubleElimination.results[u].length - 1, 1);
                        }
                        if (match.eliminationGame && eliminationDecks[u][eliminationDecks[u].length -1].length == 0) {
                            eliminationDecks[u].splice(eliminationDecks[u].length -1, 1);
                        }
                    }
                }
                // enable top-cut
                if (data.cutToTop && doubleElimination.results[0].length) {
                    var tree = $('#iframe-tree').contents().find('#target');
                    tree.bracket({
                        skipConsolationRound: true,
                        init: doubleElimination,
                        decorator: { edit: edit_fn, render: render_fn },
                        roundMargin: 20
                    });
                    addIdsToTree(tree, eliminationDecks);
                    matchIframeHeight();
                    $('#header-top').removeClass('hidden-xs-up');
                    //$('#table-matches-top').removeClass('hidden-xs-up');    // keep top-cut table hidden, tree is better
                }
            }
            // display missing messages if needed
            if (data.cutToTop && !doubleElimination.results[0].length) {
                $('#warning-matches-top').removeClass('hidden-xs-up');
            }
            if (!$('#table-matches-swiss > tbody').length) {
                $('#warning-matches-swiss').removeClass('hidden-xs-up');
            }

            // hide loader animation
            $('#loader-content').addClass('hidden-xs-up');
            $('#content-matches').removeClass('p-b-3').removeClass('hidden-xs-up');
        }
    });
}

// calculate elimination data
function prepareEliminationData(player1, player2, match, teams, results, eliminationLosers, eliminationDecks, topNumber) {

    // build elimination tree

    // very first bracket, build teams
    if (teams.length < topNumber / 2) {
        teams.push([getPlayerName(player1), getPlayerName(player2)]);
    }

    if (eliminationLosers.indexOf(getPlayerName(player1)) == -1 &&
        eliminationLosers.indexOf(getPlayerName(player2)) == -1) {

        // winners bracket
        addDeckInfo(match, player1, player2, eliminationDecks, 0);
        addResultInfo(match, results, 0);

    } else if  (eliminationLosers.indexOf(getPlayerName(player1)) > -1 &&
        eliminationLosers.indexOf(getPlayerName(player2)) > -1 &&
        eliminationLosers.length < topNumber) {

        // losers bracket
        addDeckInfo(match, player1, player2, eliminationDecks, 1);
        addResultInfo(match, results, 1);

    } else {

        // finals bracket
        addDeckInfo(match, player1, player2, eliminationDecks, 2);
        addResultInfo(match, results, 2);
    }
}

function getPlayerName(player) {
    return player.user_id > 0 ? player.user_name : player.user_import_name;
}

// adds item to array's last array element
function addAsLast(array, item) {
    array[array.length -1].push(item);
}

// adjust iFrame height
function matchIframeHeight() {
    var iframe = $('#iframe-tree');
    iframe.height(iframe.contents().height() + 20);
}

// adds ID info to "decks" array which has the same structure as results array for jQuery Bracket
function addDeckInfo(match, mplayer1, mplayer2, decks, index) {
    var side_player1 = match.player1.role,
        side_player2 = match.player2.role;

    addAsLast(decks[index], [mplayer1[side_player1 + '_deck_identity_id'], mplayer2[side_player2 + '_deck_identity_id']]);
}

// adds results for jQuery Bracket
function addResultInfo(match, results, index) {
    if (match.player1.winner) {
        addAsLast(results[index], [1,0]);
    } else {
        addAsLast(results[index], [0,1]);
    }
}

// returns true if player order should be switched in the match
function checkPlayerOrder(player1, player2, winners, losers, topCutSize) {

    // player1 has loss, but player 2 has none
    if (losers.indexOf(getPlayerName(player1)) > -1 && losers.indexOf(getPlayerName(player2)) == -1) {
        return true;    // switch players
    }

    // both player1 and player2 have loss
    if (losers.indexOf(getPlayerName(player1)) > -1 && losers.indexOf(getPlayerName(player2)) > -1) {
        if (topCutSize > losers.length) {
            // losers bracket, switch if player2 won more recent
            return (winners.lastIndexOf(getPlayerName(player2)) > winners.lastIndexOf(getPlayerName(player1)));
        } else {
            // finals, switch players if player1 lost earlier (coming from loser's bracket)
            return (losers.indexOf(getPlayerName(player2)) > losers.indexOf(getPlayerName(player1)));
        }
    }

    return false;
}

// adds ID images to elimination tree
function addIdsToTree(element, decks) {
    var winners = element.find('.bracket'),
        losers = element.find('.loserBracket'),
        finals = element.find('.finals');
    addIdsToSubTree(winners, decks[0]);
    addIdsToSubTree(losers, decks[1]);
    addIdsToSubTree(finals, decks[2]);
}

// adds ID images to elimination subtree: 0 - winners, 1 - losers, 2 - finals
function addIdsToSubTree(element, decks) {
    for (var roundIndex = 0, roundLen = decks.length; roundIndex < roundLen; roundIndex++) {
        for (var matchIndex = 0, matchLen = decks[roundIndex].length; matchIndex < matchLen; matchIndex++) {
            var matchElement = element.find('.round:nth-child(' + (roundIndex+1) + ')')
                .find('.match:nth-child(' + (matchIndex+1) + ')');
            matchElement.find('div > div.team:nth-child(1) > div.label > img')
                .attr('src', '/img/ids/' + decks[roundIndex][matchIndex][0] + '.png');
            matchElement.find('div > div.team:nth-child(2) > div.label > img')
                .attr('src', '/img/ids/' + decks[roundIndex][matchIndex][1] + '.png');
        }
    }
}

// custom rendering function for jQuery Bracket to insert ID image
function render_fn(container, data, score, state) {
    switch(state) {
        case "empty-bye":
            container.append("No team");
            return;
        case "empty-tbd":
            container.append("Upcoming");
            return;

        case "entry-no-score":
        case "entry-default-win":
        case "entry-complete":
            container.append('<img  /> ').append(data);
            return;
    }
}

// custom edit function for jQuery Bracket, stub, left empty because not needed
function edit_fn() {

}

// display scores in swiss table
function displayScores(id) {
    $('#button-showpoints').addClass('hidden-xs-up');

    $.ajax({
        url: '/tjsons/' + id + '.json',
        dataType: "json",
        async: true,
        success: function (data) {
            data.players.sort(orderByRank);
            $('#entries-swiss thead tr').append($('<th>', {text: 'score'}));

            for (var i = 0; i < data.players.length; i++) {
                $('#entries-swiss tbody tr:eq(' + i + ')').append(
                    '<td class="cell-points"><span class="legal-bullshit">points:&nbsp;</span><strong>'
                    + data.players[i].matchPoints + '</strong><br/>' +
                    '<span class="legal-bullshit">SoS:&nbsp;' + data.players[i].strengthOfSchedule.toFixed(3) + '<br/>' +
                    'eSoS:&nbsp;' + data.players[i].extendedStrengthOfSchedule.toFixed(3) + '</span></td>');
            }
        }
    });
}

function orderByRank(a, b) {
    if (a.rank < b.rank) {
        return -1;
    }
    return 1;
}