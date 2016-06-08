@extends('layout.general')

@section('content')
    <h3 class="page-header">Discover upcoming tournaments</h3>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-filter" aria-hidden="true"></i> Filter</h4>
                {!! Form::open(['url' => '/tournaments']) !!}
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('tournament_type_id', 'Type') !!}
                            {!! Form::select('tournament_type_id', $tournament_types,
                                null, ['class' => 'form-control', 'onchange' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('location_country', 'Country') !!}
                            {!! Form::select('location_country', $countries, null,
                                ['class' => 'form-control', 'onchange' => '']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            {!! Form::label('location_us_state', 'State') !!}
                            {!! Form::select('location_us_state', $us_states,
                                        null, ['class' => 'form-control', 'onchange'=>'']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'players', 'cardpool'],
                'data' => $tournaments, 'title' => 'Upcoming tournaments',
                 'id' => 'discover-table', 'icon' => 'fa-list-alt'])
                <script type="text/javascript">
                    $.ajax({
                        url: '/api/tournaments?user=1276',
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            updateTournamentTable(data, ['title', 'date', 'location', 'cardpool', 'players'], 'no tournaments to show');
                        }
                    });

                    function updateTournamentTable(data, columns, emptyMessage) {
                        $('#discover-table').find('tbody').empty();
                        $.each(data, function (index, element) {
                            newrow = $('<tr>').appendTo('#discover-table > tbody');

                            // if zero rows
                            if (data.length == 0) {
                                newrow.append($('<td>', {
                                    text: emptyMessage,
                                    colspan: columns.length,
                                    'class': 'text-center'
                                }));
                                return 0;
                            }

                            // title
                            if ($.inArray('title', columns) > -1) {
                                newrow.append($('<td>').append($('<a>', {
                                    text: element.title,
                                    href: '/tournaments/' + element.id
                                })));
                            }
                            // date
                            if ($.inArray('date', columns) > -1) {
                                newrow.append($('<td>', {
                                    text: element.date
                                }));
                            }
                            // location
                            if ($.inArray('location', columns) > -1) {
                                newrow.append($('<td>', {
                                    text: element.location
                                }));
                            }
                            // cardpool
                            if ($.inArray('cardpool', columns) > -1) {
                                newrow.append($('<td>', {
                                    text: element.cardpool
                                }));
                            }
                            // approved
                            if ($.inArray('approval', columns) > -1) {
                                cell = $('<td>', {
                                    'class': 'text-center'
                                }).appendTo(newrow);

                                if (element.approved === null) {
                                    cell.append($('<span>', {
                                        text: 'pending',
                                        'class': 'label label-warning'
                                    }));
                                } else if (element.approved) {
                                    cell.append($('<span>', {
                                        text: 'approved',
                                        'class': 'label label-success'
                                    }));
                                } else {
                                    cell.append($('<i>', {
                                        'aria-hidden': true,
                                        'class': 'fa fa-thumbs-down text-danger'
                                    }), ' ', $('<span>', {
                                        text: 'rejected',
                                        'class': 'label label-danger'
                                    }));
                                }
                            }
                            // claim
                            if ($.inArray('user_claim', columns) > -1) {
                                cell = $('<td>', {
                                    'class': 'text-center'
                                }).appendTo(newrow);

                                if (element.user_claim) {
                                    cell.append($('<span>', {
                                        text: 'claimed',
                                        'class': 'label label-success'
                                    }));
                                } else if (element.concluded) {
                                    cell.append($('<i>', {
                                        'aria-hidden': true,
                                        'class': 'fa fa-clock-o text-danger'
                                    }), ' ', $('<span>', {
                                        text: 'please claim',
                                        'class': 'label label-danger'
                                    }));
                                } else {
                                    cell.append($('<span>', {
                                        text: 'registered',
                                        'class': 'label label-info'
                                    }));
                                }
                            }
                            // conclusion
                            if ($.inArray('conclusion', columns) > -1) {
                                cell = $('<td>', {
                                    'class': 'text-center'
                                }).appendTo(newrow);

                                if (element.concluded) {
                                    cell.append($('<span>', {
                                        text: 'concluded',
                                        'class': 'label label-success'
                                    }));
                                } else if (element.date <= '{{ $nowdate }}') {
                                    cell.append($('<i>', {
                                        'aria-hidden': true,
                                        'class': 'fa fa-clock-o text-danger'
                                    }), ' ', $('<span>', {
                                        text: 'due, pls update',
                                        'class': 'label label-danger'
                                    }));
                                } else {
                                    cell.append($('<span>', {
                                        text: 'not yet',
                                        'class': 'label label-info'
                                    }));
                                }
                            }
                            // players
                            if ($.inArray('players', columns) > -1) {
                                newrow.append($('<td>', {
                                    text: element.concluded ? element.players_count : element.registration_count,
                                    'class': 'text-center'
                                }));
                            }
                            // claims
                            if ($.inArray('claims', columns) > -1) {
                                cell = $('<td>', {
                                    'class': 'text-center'
                                }).appendTo(newrow);

                                if (element.claim_conflict) {
                                    cell.append($('<i>', {
                                        'title': 'conflict',
                                        'class': 'fa fa-exclamation-triangle text-danger'
                                    }), ' ');
                                }

                                cell.append(element.claim_count);

                            }
                            // action_edit
                            if ($.inArray('action_edit', columns) > -1) {
                                newrow.append($('<td>').append($('<a>', {
                                    'class': 'btn btn-primary btn-xs',
                                    href: '/tournaments/' + element.id + '/edit'
                                }).append($('<i>', {
                                    'class': 'fa fa-pencil',
                                    'aria-hidden': true
                                }), ' edit')));
                            }
                            // action_approve
                            if ($.inArray('action_approve', columns) > -1) {
                                newrow.append($('<td>').append($('<a>', {
                                    'class': 'btn btn-success btn-xs',
                                    href: '/tournaments/' + element.id + '/approve'
                                }).append($('<i>', {
                                    'class': 'fa fa-thumbs-up',
                                    'aria-hidden': true
                                }), ' approve')));
                            }
                            // action_reject
                            if ($.inArray('action_reject', columns) > -1) {
                                newrow.append($('<td>').append($('<a>', {
                                    'class': 'btn btn-danger btn-xs',
                                    href: '/tournaments/' + element.id + '/reject'
                                }).append($('<i>', {
                                    'class': 'fa fa-thumbs-down',
                                    'aria-hidden': true
                                }), ' reject')));
                            }
                            // action_restore
                            if ($.inArray('action_restore', columns) > -1) {
                                newrow.append($('<td>').append($('<a>', {
                                    'class': 'btn btn-primary btn-xs',
                                    href: '/tournaments/' + element.id + '/restore'
                                }).append($('<i>', {
                                    'class': 'fa fa-recycle',
                                    'aria-hidden': true
                                }), ' restore')));
                            }
                            // action_delete
                            if ($.inArray('action_delete', columns) > -1) {
                                newrow.append($('<td>').append($('<form>', {
                                    method: 'POST',
                                    action: '/tournaments/' + element.id
                                }).append($('<input>', {
                                    name: '_method',
                                    type: 'hidden',
                                    value: 'DELETE'
                                }), $('<input>', {
                                    name: '_token',
                                    type: 'hidden',
                                    value: '{{ csrf_token() }}'
                                }), $('<button>', {
                                    type: 'submit',
                                    'class': 'btn btn-danger btn-xs'
                                }).append($('<i>', {
                                    'class': 'fa fa-trash',
                                    'aria-hidden': true
                                }), ' delete'))));
                            }

                        }, columns, emptyMessage);
                    }
                </script>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-calendar" aria-hidden="true"></i> Upcoming calendar</h4>
                <div class="custom-calendar-wrap">
                    <div id="custom-inner" class="custom-inner">
                        <div class="custom-header clearfix">
                            <nav>
                                <span id="custom-prev" class="custom-prev"></span>
                                <span id="custom-next" class="custom-next"></span>
                            </nav>
                            <h2 id="custom-month" class="custom-month"></h2>
                            <h3 id="custom-year" class="custom-year"></h3>
                        </div>
                        <div id="calendar" class="fc-calendar-container"></div>
                    </div>
                </div>
                <script type="application/javascript">

                    $(function() {

                        var $wrapper = $( '#custom-inner' ),
                                $calendar = $( '#calendar' ),
                                cal = $calendar.calendario( {
                                    onDayClick : function( $el, $contentEl, dateProperties ) {

                                        if( $contentEl.content.length > 0 ) {
                                            showEvents( $contentEl.content, dateProperties );
                                        }

                                    },
                                    caldata : { '06-09-2016': ['<a href="">event one</a>', '<span>event two</span>']},
                                    displayWeekAbbr : true
                                } ),
                                $month = $( '#custom-month' ).html( cal.getMonthName() ),
                                $year = $( '#custom-year' ).html( cal.getYear() );

                        $( '#custom-next' ).on( 'click', function() {
                            cal.gotoNextMonth( updateMonthYear );
                        } );
                        $( '#custom-prev' ).on( 'click', function() {
                            cal.gotoPreviousMonth( updateMonthYear );
                        } );

                        function updateMonthYear() {
                            $month.html( cal.getMonthName() );
                            $year.html( cal.getYear() );
                        }

                        function showEvents( $contentEl, dateProperties ) {

                            hideEvents();

                            var $events = $( '<div id="custom-content-reveal" class="custom-content-reveal"><h4>Tournaments for ' + dateProperties.year + ' ' + dateProperties.monthname + ' ' + dateProperties.day + '</h4></div>' ),
                                    $close = $( '<span class="custom-content-close"></span>' ).on( 'click', hideEvents );

                            $events.append( $contentEl , $close ).insertAfter( $wrapper );

                            setTimeout( function() {
                                $events.css( 'top', '0%' );
                            }, 10 );

                        }
                        function hideEvents() {

                            var $events = $( '#custom-content-reveal' );
                            if( $events.length > 0 ) {

                                $events.css( 'top', '100%' );
                                $events.remove();

                            }

                        }

                    });


                </script>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h4><i class="fa fa-globe" aria-hidden="true"></i> Map</h4>
                <iframe id="map" width="100%" height="400px" frameborder="0" style="border:0"
                        src="{{ "https://www.google.com/maps/embed/v1/search?q=Europe&key=".ENV('GOOGLE_MAPS_API') }}" allowfullscreen></iframe>
            </div>
        </div>
    </div>
@stop

