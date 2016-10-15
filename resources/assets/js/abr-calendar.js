//contains JS for the calendar

function convertDateForCalendar(dataString) {
    return dataString.substr(5,2) + '-' + dataString.substr(8,2) + '-' + dataString.substr(0,4);
}

function nowDate() {
    var date = new Date(),
        day = date.getDate(),
        monthIndex = date.getMonth() + 1,
        result = date.getFullYear() + '.';
    if (monthIndex < 10) {
        result = result + '0';
    }
    result = result + monthIndex + '.';
    if (day < 10) {
        result = result + '0';
    }
    result = result + day + '.';
    return result;
}

function leadingZero(number) {
    if (number < 10) {
        return '0' + number;
    } else {
        return number;
    }
}

function updateTournamentCalendar(data) {
    var calendardata = {};
    $.each(data, function (index, element) {
        var entry = '<a href="/tournaments/' + element.id + '">' + element.title + '</a><small>' + element.location + '</small>';
        if (typeof calendardata[convertDateForCalendar(element.date)] == "undefined") {
            calendardata[convertDateForCalendar(element.date)] = [entry];
        } else {
            calendardata[convertDateForCalendar(element.date)].push(entry);
        }
    });

    $(function() {

        var $wrapper = $( '#custom-inner' ),
            $calendar = $( '#calendar' ),
            cal = $calendar.calendario( {
                onDayClick : function( $el, $contentEl, dateProperties ) {

                    if( $contentEl.content.length > 0 ) {
                        showEvents( $contentEl.content, dateProperties );
                    }

                },
                caldata : calendardata,
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

            var $events = $( '<div id="custom-content-reveal" class="custom-content-reveal"><h4>Tournaments for ' + dateProperties.year + '.' + leadingZero(dateProperties.month) + '.' + leadingZero(dateProperties.day) + '.</h4></div>' ),
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
}