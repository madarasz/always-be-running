@extends('layout.general')

@section('content')
    <h4 class="page-header">Organize</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <div class="bracket text-xs-center">
                <a href="/tournaments/create" class="btn btn-primary margin-tb">Create Tournament</a>
            </div>
            <div class="bracket">
                <h4>My tournament calendar</h4>
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
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
            @include('tournaments.partials.table',
                ['columns' => ['title', 'date', 'approval', 'conclusion', 'players', 'decks',
                    'action_edit', 'action_delete' ],
                'data' => $created, 'title' => 'Tournaments created by me',
                'empty_message' => 'no tournaments created yet', 'id' => 'created'])
            </div>
        </div>
    </div>
@stop

