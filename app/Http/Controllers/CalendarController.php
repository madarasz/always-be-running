<?php

namespace App\Http\Controllers;

use App\Tournament;
use Eluceo\iCal\Component\Alarm;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Property\Event\RecurrenceRule;
use Eluceo\iCal\Component\Event;
use App\Http\Requests;

class CalendarController extends Controller
{
    protected $method = 'PUBLISH';
    protected $refresh = 'P1D';
    protected $eventStatus = 'CONFIRMED';

    /**
     * Produces an .ics calendar file with the selected event.
     * @param $id tournament ID
     * @return mixed .ics iCal calendar file
     */
    public function getEventCalendar($id) {

        $calendar = new Calendar('alwaysberunning.net-event-'.$id);
        $calendar->setMethod($this->method);
        $calendar->setPublishedTTL($this->refresh);
        $tournament = Tournament::findOrFail($id);

        $event = $this->getEvent($tournament);
        $calendar->addComponent($event);

        // construct HTTP response
        $response = \Response::make($calendar->render(), 200);
        $response->header('Content-Type', 'text/plain');
        $response->header('Content-Disposition', 'attachment;filename="'.$tournament->seoTitle().'.ics"');

        return $response;
    }

    public function getUserCalendar($id) {


    }

    /**
     * Returns iCal event object for the tournament.
     * @param $tournament Tournament object
     * @return Event iCal event object
     */
    public function getEvent($tournament) {

        $calendarEntry = $tournament->calendarEntry();
        $event = new Event();

        $start = new \DateTime('@'.strtotime($calendarEntry['start'].' '.$tournament->timezone));
        $end = new \DateTime('@'.strtotime($calendarEntry['end'].' '.$tournament->timezone));

        // timezone for non-online tournaments
        if ($tournament->tournament_type_id != 7) {
            // if not yet defined
            if (is_null($calendarEntry['timezone'])) {
                // request from google maps api
                $tzRequest = file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=".$tournament->location_lat.','.
                    $tournament->location_long.'&timestamp='.time($tournament->date).'&key='.ENV('GOOGLE_MAPS_API'));
                $timezoneString = json_decode($tzRequest,true)['timeZoneId'];
                // save to DB
                $tournament->timezone = $timezoneString;
                $tournament->save();
            }
            $timezone = new \DateTimeZone ($tournament->timezone);
            $start->setTimezone($timezone);
            $end->setTimezone($timezone);
            $event->setUseTimezone(true);
        }

        // construct event
        $event->setDtStart($start)
            ->setDtEnd($end)
            ->setSummary($calendarEntry['title'])
            ->setDescription($calendarEntry['description'])
            ->setLocation($calendarEntry['location'])
            ->setUrl($calendarEntry['url'])
            ->setUniqueId($calendarEntry['uid'])
            ->setDtStamp(new \DateTime())
            ->setStatus($this->eventStatus)
            ->setDescriptionHTML($calendarEntry['description_html']);

        // recurrence
        if ($calendarEntry['recurring']) {
            $recurrence = new RecurrenceRule();
            $recurrence->setFreq(RecurrenceRule::FREQ_WEEKLY);
            $recurrence->setInterval(1);
            $recurrence->setByDay($calendarEntry['recurring']);
            $event->setRecurrenceRule($recurrence); // deprecated but it works
        }

        // all day
        if ($calendarEntry['all_day_event']) {
            $event->setNoTime(true);
        }

        // alarm
        $alarm = new Alarm();
        $alarm->setAction(Alarm::ACTION_DISPLAY);
        $alarm->setTrigger($calendarEntry['alarm_reminder']);
        $alarm->setDescription($calendarEntry['title']);
        $event->addComponent($alarm);

        return $event;
    }
}
