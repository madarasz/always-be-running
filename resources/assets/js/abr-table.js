//Contains JS for the dynamic tables

function updateTournamentTable(elementID, columns, emptyMessage, csrftoken, data) {

    var nowdate = nowDate(),
        weeklater = new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0,10).replace(/-/g, ".")+'.',
        paging = document.getElementById(elementID.substr(1) + '-controls').dataset;

    // if zero rows
    if (data.length == 0) {
        $(elementID + ' > tbody').append($('<tr>')).append($('<td>', {
            text: emptyMessage,
            colspan: columns.length,
            'class': 'text-xs-center small-text'
        }));

        // remove loader animation
        $(elementID+'-loader').addClass('hidden-xs-up');

        return 0;
    }

    $.each(data, function (index, element) {
        // row class: highlight worlds, hide paged
        var rowclass = '';
        if (element.type === 'worlds championship' || element.type === 'continental championship') {
            rowclass = 'row-worlds';
        }
        if (paging.maxrows && index >= paging.maxrows) {
            rowclass = 'hidden-xs-up ' + rowclass;
        }

        var newrow = $('<tr>', {
            class: rowclass,
            id: elementID.substr(1) + '-row-' + (index+1)
        }).appendTo(elementID + ' > tbody');

        // title
        if ($.inArray('title', columns) > -1) {
            var cell = $('<td>', { class: 'tournament-title' });

            // charity
            if (element.charity) {
                cell.append($('<i>', {
                    'title': 'charity',
                    'class': 'fa fa-heart text-danger'
                }), ' ');
            }

            // emblems for tournament type and format
            tournamentEmblem(cell, element.type, element.format);

            cell.append($('<a>', {
                text: element.title,
                href: element.url,
                class: element.approved ? '' : ' font-italic'
            })).appendTo(newrow);

            var tInfo = $('<span>', { class: 'text-nowrap' });
            cell.append(tInfo);

            // patreon Sysop goal
            if (parseInt(element.creator_supporter) > 2) {
                tInfo.append(' ', $('<img>', {
                    'class': 'img-patron-o'
                }))
            }

            // match data
            if (element.matchdata) {
                tInfo.append(' ', $('<i>', {
                    'title': 'match data',
                    'class': 'fa fa-handshake-o'
                }));
            }
            // photos
            if (element.photos) {
                tInfo.append(' ');
                if (element.photos > 1) {
                    tInfo.append(element.photos);
                } else {

                }
                tInfo.append($('<i>', {
                    'title': 'photo',
                    'class': 'fa fa-camera'
                }));
            }
            // videos
            if (element.videos) {
                tInfo.append(' ');
                if (element.videos > 1) {
                    tInfo.append(element.videos);
                } else {

                }
                tInfo.append($('<i>', {
                    'title': 'video',
                    'class': 'fa fa-video-camera'
                }));
            }
        }
        // date
        if ($.inArray('date', columns) > -1) {
            var cell = $('<td>').appendTo(newrow);
            cell.append($('<span>', {
                text: element.date ? element.date.substring(0, 5) : element.recurring_day,
                class: 'line-breaker'
            }));
            var endpart = $('<span>', {
                text: element.date ? element.date.substring(5) : '',
                class: 'line-breaker'
            }).appendTo(cell);
            if (element.end_date) {
                $('<i>', {
                    class: 'fa fa-plus-circle icon-upper',
                    title: 'multiple day event'
                }).appendTo(endpart);
            }
        }
        // location, small devices see only country
        if ($.inArray('location', columns) > -1) {
            newrow.append($('<td>').append($('<span>', {
                text: element.location,
                class: 'hidden-sm-down'
            }), $('<span>', {
                text: element.location_country,
                class: 'hidden-md-up'
            })));
        }
        // recurring day
        if ($.inArray('recurday', columns) > -1) {
            newrow.append($('<td>', {
                text: element.recurring_day
            }));
        }
        // cardpool
        if ($.inArray('cardpool', columns) > -1) {
            var warning = false;
            if (element.cardpool === '- not yet known -') {
                // time to update
                if ($.inArray('action_edit', columns) > -1 && element.date < weeklater) {
                    warning = true;
                }
                element.cardpool = element.cardpool.replace(/ /g,'&nbsp;');
            }
            newrow.append(cell = $('<td>', {
                html: element.cardpool,
                style: warning ? 'color: red' : ''
            }));
        }
        // creator
        if ($.inArray('creator', columns) > -1) {
            newrow.append($('<td>').append($('<a>', {
                text: element.creator_name,
                href: '/profile/' + element.creator_id,
                class: element.creator_class
            })));
        }
        // type
        if ($.inArray('type', columns) > -1) {
            newrow.append($('<td>', {
                class: 'hidden-md-down'
            }).append($('<em>', {
                text: element.type
            })));
        }
        // approved
        if ($.inArray('approval', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.approved === null) {
                cell.append($('<span>', {
                    text: 'pending',
                    'class': 'hidden-md-down label label-warning'
                }), $('<i>', {
                    class: 'hidden-lg-up fa fa-question text-warning'
                }));
            } else if (element.approved) {
                cell.append($('<span>', {
                    text: 'approved',
                    'class': 'hidden-md-down label label-success'
                }), $('<i>', {
                    class: 'hidden-lg-up fa fa-thumbs-up text-success'
                }));
            } else {
                cell.append($('<i>', {
                    'aria-hidden': true,
                    'class': 'fa fa-thumbs-down text-danger'
                }), ' ', $('<span>', {
                    text: 'rejected',
                    'class': 'label label-danger hidden-md-down'
                }));
            }
        }
        // claim
        if ($.inArray('user_claim', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.user_claim) {   // claimed
                if (element.user_claim_broken) {    // broken claim
                    cell.append($('<span>', {
                        'class': 'label label-danger'
                    }).append($('<i>', {
                        class: 'fa fa-chain-broken',
                        title: 'broken link'
                    }), '&nbsp;broken claim'));
                } else {    // claim ok
                    cell.append($('<span>', {
                        text: 'claimed',
                        'class': 'label label-success'
                    }));
                }
            } else if (element.concluded) { // concluded, should claim
                cell.append($('<button>', {
                    'class': 'btn btn-claim btn-xs',
                    'data-toggle': 'modal',
                    'data-target': '#claimModal',
                    'data-tournament-id': element.id,
                    'data-subtitle': element.title + ' - ' + element.date,
                    'data-players-number': element.players_count,
                    'data-top-number': element.top_count
                }).append($('<i>', {
                    'class': 'fa fa-list-ol',
                    'aria-hidden': true
                }), ' claim'), $('<br>'), $('<span>', {
                    class: 'small-text'
                }).append('or&nbsp;', $('<a>', {
                    text: 'unregister',
                    href: '/tournaments/' + element.id + '/unregister'
                })));
            } else {    // registered
                cell.append($('<span>', {
                    text: 'registered',
                    'class': 'label label-info'
                }));
            }
        }
        // conclusion
        if ($.inArray('conclusion', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center'
            }).appendTo(newrow);

            if (element.type !== 'non-tournament event' && element.date) { // if not a non-tournament
                if (element.concluded) {
                    cell.append($('<span>', {
                        text: 'concluded',
                        'class': 'label label-success'
                    }));
                } else if (element.date <= nowdate) {
                    cell.append($('<button>', {
                        'class': 'btn btn-conclude btn-xs',
                        'data-toggle': 'modal',
                        'data-target': '#concludeModal',
                        'data-tournament-id': element.id,
                        'data-subtitle': element.title + ' - ' + element.date
                    }).append($('<i>', {
                        'class': 'fa fa-check',
                        'aria-hidden': true
                    }), ' conclude'));
                } else {
                    cell.append($('<span>', {
                        text: 'not yet',
                        'class': 'label label-info'
                    }));
                }
            } else {
                cell.append($('<span>', {
                    text: '-',
                    'class': ''
                }));
            }
        }
        // winner
        if ($.inArray('winner', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center cell-winner'
            }).appendTo(newrow);
            if (element.winner_runner_identity) {
                cell.append($('<img>', {
                    src: '/img/ids/'+element.winner_runner_identity+'.png'
                }))
            }
            if (element.winner_corp_identity) {
                cell.append('&nbsp;').append($('<img>', {
                    src: '/img/ids/'+element.winner_corp_identity+'.png'
                }))
            }
        }
        // players
        if ($.inArray('players', columns) > -1) {
            newrow.append($('<td>', {
                text: element.concluded ? element.players_count : element.registration_count,
                'class': 'text-xs-center hidden-xs-down'
            }));
        }
        // claims
        if ($.inArray('claims', columns) > -1) {
            cell = $('<td>', {
                'class': 'text-xs-center hidden-xs-down'
            }).appendTo(newrow);

            if (element.claim_conflict) {
                cell.append($('<i>', {
                    'title': 'conflict',
                    'class': 'fa fa-exclamation-triangle text-danger'
                }), ' ');
            }

            cell.append(element.claim_count);

        }
        // created at
        if ($.inArray('created_at', columns) > -1) {
            newrow.append($('<td>', {
                text: element.created_at,
                class: 'text-xs-center font-italic'
            }));
        }
        // action_edit
        if ($.inArray('action_edit', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<a>', {
                'class': 'btn btn-primary btn-xs',
                href: '/tournaments/' + element.id + '/edit'
            }).append($('<i>', {
                'class': 'fa fa-pencil',
                'aria-hidden': true
            }), $('<span>', {
                text: ' update',
                class: 'hidden-md-down'
            }))));
        }
        // action_approve
        if ($.inArray('action_approve', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<a>', {
                'class': 'btn btn-success btn-xs',
                href: '/tournaments/' + element.id + '/approve'
            }).append($('<i>', {
                'class': 'fa fa-thumbs-up',
                'aria-hidden': true
            }), $('<span>', {
                text: ' approve',
                class: 'hidden-md-down'
            }))));
        }
        // action_reject
        if ($.inArray('action_reject', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<a>', {
                'class': 'btn btn-danger btn-xs',
                href: '/tournaments/' + element.id + '/reject'
            }).append($('<i>', {
                'class': 'fa fa-thumbs-down',
                'aria-hidden': true
            }), $('<span>', {
                text: ' reject',
                class: 'hidden-md-down'
            }))));
        }
        // action_restore
        if ($.inArray('action_restore', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<a>', {
                'class': 'btn btn-primary btn-xs',
                href: '/tournaments/' + element.id + '/restore'
            }).append($('<i>', {
                'class': 'fa fa-recycle',
                'aria-hidden': true
            }), $('<span>', {
                text: ' restore',
                class: 'hidden-md-down'
            }))));
        }
        // action_delete
        if ($.inArray('action_delete', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<form>', {
                method: 'POST',
                action: '/tournaments/' + element.id
            }).append($('<input>', {
                name: '_method',
                type: 'hidden',
                value: 'DELETE'
            }), $('<input>', {
                name: '_token',
                type: 'hidden',
                value: csrftoken
            }), $('<button>', {
                type: 'submit',
                'class': 'btn btn-danger btn-xs',
                'onclick': "return confirm('Are you sure you want to delete this tournament?')"
            }).append($('<i>', {
                'class': 'fa fa-trash',
                'aria-hidden': true
            }), $('<span>', {
                text: ' delete',
                class: 'hidden-md-down'
            })))));
        }
        // action_purge
        if ($.inArray('action_purge', columns) > -1) {
            newrow.append($('<td>', {class: 'action'}).append($('<form>', {
                method: 'POST',
                action: '/tournaments/' + element.id + '/purge'
            }).append($('<input>', {
                name: '_method',
                type: 'hidden',
                value: 'DELETE'
            }), $('<input>', {
                name: '_token',
                type: 'hidden',
                value: csrftoken
            }), $('<button>', {
                type: 'submit',
                'class': 'btn btn-danger btn-xs',
                'onclick': "return confirm('Are you sure you want to remove this tournament? This cannot be undone. All related information and claims will be lost.')"
            }).append($('<i>', {
                'class': 'fa fa-times',
                'aria-hidden': true
            }), $('<span>', {
                text: ' remove',
                class: 'hidden-md-down'
            })))));
        }

    }, columns, emptyMessage);

    // remove loader animation
    $(elementID+'-loader').addClass('hidden-xs-up');

    // paging
    updatePaging(elementID.substr(1));
    // read last used pager setting from cookie, apply if found
    if (document.getElementById(elementID.substr(1)+'-options')) {
        var savedOption = getCookie('pager-' + elementID.substr(1) + '-option');
        if (savedOption != "") {
            changePageOptions(elementID.substr(1), savedOption);
        }
    }
}

// update paging data
function updatePaging(elementId) {
    var paging = document.getElementById(elementId + '-controls').dataset,
        tablelength = $('[id^=' + elementId + '-row-]').length;
    paging.currentpage = 1;

    if (paging.maxrowsoriginal && paging.maxrowsoriginal <= tablelength) {
        paging.totalrows = tablelength;
        $('#' + elementId + '-controls').removeClass('hidden-xs-up');
        $('#' + elementId + '-options').removeClass('hidden-xs-up');
        document.getElementById(elementId + '-number-total').innerHTML = tablelength;
    } else {
        $('#' + elementId + '-controls').addClass('hidden-xs-up');
        $('#' + elementId + '-options').addClass('hidden-xs-up');
    }

    updatePageControls(elementId);
}

// clicking paging arrows for tournament table, direction==true forward, direction==false backward,
// direction==null just refresh row visibility
function doTournamentPaging(elementId, direction) {
    var paging = document.getElementById(elementId + '-controls').dataset;

    if (direction === true) {
        paging.currentpage++;
    } else if (direction === false) {
        paging.currentpage--;
    }

    // hide all rows
    $('[id^='+elementId+'-row-').addClass('hidden-xs-up');

    updatePageControls(elementId);

    // show new rows
    for (var i = (paging.currentpage-1) * paging.maxrows + 1; i <= paging.torows; i++) {
        $('#'+elementId+'-row-'+i).removeClass('hidden-xs-up');
    }
}

// update paging text and controls
function updatePageControls(elementId) {
    var paging = document.getElementById(elementId + '-controls').dataset;

    document.getElementById(elementId + '-number-from').innerHTML = (paging.currentpage-1) * paging.maxrows + 1;
    if ((paging.currentpage) * paging.maxrows < paging.totalrows) {
        paging.torows = (paging.currentpage) * paging.maxrows;
        $('#'+elementId+'-controls-forward').removeClass('hidden-xs-up');
    } else {
        paging.torows = paging.totalrows;
        $('#'+elementId+'-controls-forward').addClass('hidden-xs-up');
    }
    document.getElementById(elementId + '-number-to').innerHTML = paging.torows;
    if (paging.currentpage == 1) {
        $('#'+elementId+'-controls-back').addClass('hidden-xs-up');
    } else {
        $('#'+elementId+'-controls-back').removeClass('hidden-xs-up');
    }
}

function changePageOptions(elementId, rowsPerPage) {
    var paging = document.getElementById(elementId + '-controls').dataset,
        options = document.getElementById(elementId + '-options').dataset;

    if (options.selected != rowsPerPage) {

        // set values
        options.selected = rowsPerPage;
        paging.maxrows = parseInt(rowsPerPage);
        if (isNaN(paging.maxrows)) {
            paging.maxrows = 99999;
        }
        paging.currentpage = 1;
        setCookie('pager-'+elementId+'-option', rowsPerPage, 30);

        // set button states
        $('#' + elementId + '-options span').each(function () {
            if ($(this).text().trim() == rowsPerPage) {
                $(this).addClass('label-active').removeClass('label-inactive');
            } else {
                $(this).addClass('label-inactive').removeClass('label-active');
            }
        });

        // update row visibility
        doTournamentPaging(elementId, null);
    }
}
