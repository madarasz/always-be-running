// contains JS for displaying match results

// prepares row in matches table
function pepareMatchRow(tbodyid, topcut, rowcClass, matchId) {
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
        if (player.user_id > 0) {
            $('#' + matchId + 'p' + playernum +'n').append($('<a>', {
                text: player.user_name,
                href: '/profile/' + player.user_id
            }));
        } else {
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
                // process players
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
                var doubleElimination = { teams: [], results: [[], [], []]}, eliminationPlayers = [], eliminationLosers = [];
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
                        doubleElimination.results[0].push([]);
                        doubleElimination.results[1].push([]);
                        doubleElimination.results[2].push([]);
                    } else {
                        // swiss
                        tbodyid = 'tbody-matches-swiss-' + (index + 1);
                        $('#table-matches-swiss').append($('<thead>').append($('<th>', {
                            text: 'Round ' + (index + 1)
                        })), $('<tbody>', {
                            id: tbodyid
                        }));
                    }

                    // process round
                    for (u = 0, len2 = data['rounds'][index].length; u < len2; u++) {
                        var match = data['rounds'][index][u],
                            player1 = null, player2 = null,
                            matchId= 'match-'+index+'-'+u+'-',
                            rowcClass = u % 2 ? '' : 'row-colored';
                        if (match.player1.id) {
                            player1 = chartData[data['players'][idToIndex[match.player1.id]].entry_id];
                        }
                        if (match.player2.id) {
                            player2 = chartData[data['players'][idToIndex[match.player2.id]].entry_id]
                        }
                        // prepare row
                        if (match.eliminationGame) {
                            // build elimination tree
                            if (doubleElimination.teams.length < data.cutToTop / 2) {
                                // very first bracket
                                doubleElimination.teams.push([getPlayerName(player1), getPlayerName(player2)]);
                                if (match.player1.winner) {
                                    doubleElimination.results[0][doubleElimination.results[0].length-1].push([1, 0]);
                                    eliminationLosers.push(getPlayerName(player2));
                                } else {
                                    doubleElimination.results[0][doubleElimination.results[0].length-1].push([0, 1]);
                                    eliminationLosers.push(getPlayerName(player1));
                                }
                                eliminationPlayers.push(getPlayerName(player1), getPlayerName(player2));
                            } else {
                                if (eliminationLosers.indexOf(getPlayerName(player1)) == -1 &&
                                    eliminationLosers.indexOf(getPlayerName(player2)) == -1) {
                                        // winner's bracket
                                        if (match.player1.winner) {
                                            doubleElimination.results[0][doubleElimination.results[0].length-1].push([1, 0]);
                                            eliminationLosers.push(getPlayerName(player2));
                                        } else {
                                            doubleElimination.results[0][doubleElimination.results[0].length-1].push([0, 1]);
                                            eliminationLosers.push(getPlayerName(player1));
                                        }
                                } else {
                                    if (eliminationLosers.indexOf(getPlayerName(player1)) > -1 &&
                                        eliminationLosers.indexOf(getPlayerName(player2)) > -1 &&
                                        eliminationLosers.length < data.cutToTop) {
                                        // loser's bracket
                                        if (match.player1.winner) {
                                            doubleElimination.results[1][doubleElimination.results[1].length-1].push([1, 0]);
                                            if (eliminationLosers.indexOf(getPlayerName(player2)) == -1) {
                                                eliminationLosers.push(getPlayerName(player2));
                                            }
                                        } else {
                                            doubleElimination.results[1][doubleElimination.results[1].length-1].push([0, 1]);
                                            if (eliminationLosers.indexOf(getPlayerName(player1)) == -1) {
                                                eliminationLosers.push(getPlayerName(player1));
                                            }
                                        }
                                    } else {
                                        // finals
                                        if (match.player1.winner) {
                                            doubleElimination.results[2][doubleElimination.results[2].length-1].push([1, 0]);
                                        } else {
                                            doubleElimination.results[2][doubleElimination.results[2].length-1].push([0, 1]);
                                        }
                                    }

                                }
                            }
                            pepareMatchRow(tbodyid, true, rowcClass, matchId);
                        } else {
                            pepareMatchRow(tbodyid, false, rowcClass, matchId);
                        }

                        // fill row
                        fillMatchRow(player1, player2, matchId, match);
                    }

                    for (u = 0; u < 3; u++) {
                        if (match.eliminationGame && doubleElimination.results[u][doubleElimination.results[u].length - 1].length == 0) {
                            doubleElimination.results[u].splice(doubleElimination.results[u].length - 1, 1);
                        }
                    }
                }
                // enable top-cut
                if (data.cutToTop) {
                    $('#iframe-tree').contents().find('#target').bracket({
                        skipConsolationRound: true,
                        init: doubleElimination,
                        roundMargin: 20
                    });
                    matchIframeHeight();
                    $('#header-top').removeClass('hidden-xs-up');
                    $('#table-matches-top').removeClass('hidden-xs-up');
                }
            }
            // hide loader animation
            $('#loader-content').addClass('hidden-xs-up');
            $('#content-matches').removeClass('p-b-3').removeClass('hidden-xs-up');
        }
    });
}

function getPlayerName(player) {
    return player.user_id > 0 ? player.user_name : player.user_import_name;
}

function matchIframeHeight() {
    $('#iframe-tree').height($('#iframe-tree').contents().height());
}