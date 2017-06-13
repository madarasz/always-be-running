<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class FBController extends Controller
{
    /**
     * Gets FB event data from event ID or URL
     * @param $id
     * @return array|mixed
     */
    private function getEventDetails($id) {

        // if not numerical ID
        if (!ctype_digit($id)) {
            // extract ID from URL
            if (preg_match('/.facebook.com\/events\/(([0-9])+)/', $id, $matches)) {
                $id = $matches[1];
            } else {
                // error handling
                return ['error' => 'Format is incorrect.'];
            }
        }

        $fb = \App::make('SammyK\LaravelFacebookSdk\LaravelFacebookSdk');
        $fb->setDefaultAccessToken($fb->getApp()->getAccessToken());

        try {
            $eventData = json_decode($fb->get('/' . $id)->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return $eventData;
    }

    /**
     * Returns FB event name from event URL or event ID.
     * @param Request $request 'event' paramenter is the input
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventTitle(Request $request) {

        $data = $this->getEventDetails($request->event);

        // error handling
        if (array_key_exists('error', $data)) {
            return response()->json($data, 404);
        }

        return response()->json(['title' => $data['name']]);
    }

    /**
     * Guesses the tournament type from tournament title
     * @param $title
     * @return int|string
     */
    private function guessTournamentTypeFromTitle($title) {
        $typeStrings = [
            1 => ['gnk', 'seasonal'],
            2 => ['store', ' sc'],
            3 => ['regional'],
            4 => ['national', 'nats'],
            5 => ['world'],
            7 => ['online']
        ];

        $title = strtolower($title);

        foreach ($typeStrings as $key => $strings) {
            foreach ($strings as $string) {
                if (strpos($title, $string)) {
                    return $key;
                }
            }
        }

        return 1;
    }

    /**
     * Imports and saves tournament from FB event ID or URL
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importViaFB(Request $request) {
        // authorize
        if (!Auth::user()) {
            abort(403);
        }

        // call to facebook
        $data = $this->getEventDetails($request->event);

        // error handling
        if (array_key_exists('error', $data)) {
            return redirect()->route('organize')->withErrors(['There was an error during importing event from Facebook.']);
        }

        // extracting data
        //dd($data);
        $title = substr($data['name'], 0, 50);
        $date = str_replace('-','.',substr($data['start_time'], 0, 10)).'.';
        $start_time = substr($data['start_time'], 11, 5);
        $description = $data['description'];
        $tournament_type_id = $this->guessTournamentTypeFromTitle($title);
        if (ctype_digit($request->event)) {
            $link_facebook = 'https://www.facebook.com/events/'.$request->event;
        } else {
            $link_facebook = $request->event;
        }

        // extracting location data
        $location_store = '';
        $location_country = '';
        $location_state = '';
        $location_city = '';
        $location_lat = null;
        $location_long = null;
        $street = '';
        if (array_key_exists('place', $data)) {
            if (array_key_exists('location', $data['place'])) {
                $location_country = $data['place']['location']['country'];
                if (array_key_exists('state', $data['place']['location'])) {
                    $location_state = $data['place']['location']['state'];
                }
                if (array_key_exists('city', $data['place']['location'])) {
                    $location_city = $data['place']['location']['city'];
                }
                if (array_key_exists('latitude', $data['place']['location'])) {
                    $location_lat = $data['place']['location']['latitude'];
                    $location_long = $data['place']['location']['longitude'];
                }
                if (array_key_exists('street', $data['place']['location'])) {
                    $street = $data['place']['location']['street'];
                }
            }
            $location_store = $data['place']['name'];
        }

        // call google maps API
        $location_address = '';
        $location_place_id = '';
        $du = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?language=en&key=".env('GOOGLE_MAPS_API').
            "&address=".urlencode($location_country.", ".$location_state.", ".$location_city.", ".$street.", ".$location_store));
        $djd = json_decode($du,true);

        //dd($djd);

        if ($djd['status'] == 'OK' && count($djd['results'])) {
            if (array_key_exists('formatted_address', $djd['results'][0])) {
                $location_address = $djd['results'][0]['formatted_address'];
            }
            if (array_key_exists('place_id', $djd['results'][0])) {
                $location_place_id = $djd['results'][0]['place_id'];
            }
            if (array_key_exists('geometry', $djd['results'][0]) &&
                array_key_exists('location', $djd['results'][0]['geometry'])) {
                $location_lat = $djd['results'][0]['geometry']['location']['lat'];
                $location_long = $djd['results'][0]['geometry']['location']['lng'];
            }
        }

        // dd($title, $creator, $tournament_type_id, $date, $start_time, $location_country, $location_state, $location_city,
        //    $location_store, $location_long, $location_lat, $location_address, $location_place_id, $description);

        // create tournament based on FB data
        $tournament = Tournament::create([
            'creator' => $request->user()->id,
            'title' => $title,
            'date' => $date,
            'start_time' => $start_time,
            'location_country' => $location_country,
            'location_state' => $location_state,
            'location_city' => $location_city,
            'location_store' => $location_store,
            'location_address' => $location_address,
            'location_place_id' => $location_place_id,
            'description' => $description,
            'tournament_type_id' => $tournament_type_id,
            'cardpool_id' => 'unknown',
            'location_lat' => $location_lat,
            'location_long' => $location_long,
            'link_facebook' => $link_facebook,
            'concluded' => 0,
            'incomplete' => 1
        ]);

        return redirect()->route('tournaments.edit', $tournament->id);

    }
}
