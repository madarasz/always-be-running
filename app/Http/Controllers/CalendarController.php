<?php

namespace App\Http\Controllers;

use App\Entry;
use App\Tournament;
use App\User;
use Eluceo\iCal\Component\Alarm;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Property\Event\RecurrenceRule;
use Eluceo\iCal\Component\Event;
use App\Http\Requests;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    protected $method = 'PUBLISH';
    protected $refresh = 'P12H';
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

    public function getUserCalendar($secret_id) {
        $startTime = microtime(true);

        $user = User::where('secret_id', $secret_id)->first();
        if (is_null($user)) {
            return response()->json(['error' => 'user not found']);
        }

        $calendar = new Calendar('alwaysberunning.net-user-'.$user->id);
        $calendar->setMethod($this->method);
        $calendar->setPublishedTTL($this->refresh);

        $tournamentIds = Entry::where('user', $user->id)->pluck('tournament_id')->toArray();
        $tournaments = Tournament::whereIn('id', $tournamentIds)->get();
        foreach($tournaments as $tournament) {
            $calendar->addComponent($this->getEvent($tournament));
        }

        // some logging
        Log::info('Calendar rendered for user '
            .$user->displayUsername().'('.$user->id.') in: '.(microtime(true)-$startTime));

        return $calendar;
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
        if (!$tournament->online) {
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
