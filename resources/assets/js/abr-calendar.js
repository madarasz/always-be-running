//contains JS for the calendar

// converts to MM-DD-YY format from YYYY.MM.DD
function convertDateForCalendar(dataString) {
    return dataString.substr(5,2) + '-' + dataString.substr(8,2) + '-' + dataString.substr(0,4);
}
// concerts to MM-DD-YY from Date object
function convertDateObjectForCalendar(date) {
    return ('0'+(date.getMonth()+1)).slice(-2)+
        '-'+('0'+date.getDate()).slice(-2)+
        '-'+date.getFullYear();
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

function drawCalendar(calendardata) {
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

function updateTournamentCalendar(data) {
    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $.each(data, function (index, element) {
        if (element.date) {
            var entry = '<a href="/tournaments/' + element.id + '">' + element.title + '</a><small>' + element.location + '</small>';
            if (!element.end_date) {
                // single day event
                addEntryToCalendar(element, entry);
            } else {
                // multiple day event
                for (var day = new Date(convertDateForCalendar(element.date));
                     day <= new Date(convertDateForCalendar(element.end_date));
                     day.setDate(day.getDate() + 1)) {
                        element.date = day.getFullYear() + '.' + ('0' + (day.getMonth()+1)).slice(-2) + '.' + ('0' + day.getDate()).slice(-2) + '.';
                        addEntryToCalendar(element, entry);
                }
            }
        } else {
            // get first day
            var dateCounter = new Date();
            while (element.recurring_day !== days[dateCounter.getDay()]) {
                dateCounter.setDate(dateCounter.getDate() + 1);
            }
            // add to calendar for 52 weeks
            for (var i = 0; i < 52; i++) {
                var entry = '<div class="recurring-block"><a href="/tournaments/' + element.id + '">' + element.title +
                    '</a> <i class="fa fa-repeat recurring" aria-hidden="true"></i> <small>' + element.location + '</small></div>';
                if (typeof calendardata[convertDateObjectForCalendar(dateCounter)] == "undefined") {
                    calendardata[convertDateObjectForCalendar(dateCounter)] = [entry];
                } else {
                    calendardata[convertDateObjectForCalendar(dateCounter)].push(entry);
                }
                dateCounter.setDate(dateCounter.getDate() + 7);
            }
        }
    });


}

function addEntryToCalendar(element, entry) {
    if (typeof calendardata[convertDateForCalendar(element.date)] == "undefined") {
        calendardata[convertDateForCalendar(element.date)] = [entry];
    } else {
        calendardata[convertDateForCalendar(element.date)].push(entry);
    }
}