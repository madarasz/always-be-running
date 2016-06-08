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
                @include('tournaments.partials.table',
                ['columns' => ['title', 'location', 'date', 'players', 'cardpool' ],
                'data' => $tournaments, 'title' => 'Upcoming tournaments',
                'empty_message' => 'no tournaments to show', 'id' => 'discover-table', 'icon' => 'fa-list-alt'])
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

