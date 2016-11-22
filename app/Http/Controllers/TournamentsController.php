<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Entry;
use App\User;
use App\Tournament;
use App\TournamentType;
use App\CardIdentity;
use App\Http\Requests;
use Illuminate\Http\Request;

class TournamentsController extends Controller
{
    /**
     * Saves new tournament.
     * @param Requests\TournamentRequest $request
     * @return redirects
     */
    public function store(Requests\TournamentRequest $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $request->sanitize_data($request->user()->id);
        $tournament = Tournament::create($request->all());

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament created.');
    }

    /**
     * Form for tournament creation.
     * @param Request $request
     * @return view
     */
    public function create(Request $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $tournament_types = TournamentType::pluck('type_name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $tournament = new Tournament();

        // make cardpool default to cardpool after 'not yet known'
        if (count($cardpools)>1) {
            $tournament->cardpool_id = key(array_slice($cardpools,1,1));
        }

        $page_section = 'organize';
        return view('tournaments.create', compact('tournament_types', 'tournament', 'cardpools', 'page_section'));
    }

    /**
     * Form for tournament edit.
     * @param $id tournament id
     * @param Request $request
     * @return view
     */
    public function edit($id, Request $request)
    {
        $tournament = Tournament::withTrashed()->findOrFail($id);
        $this->authorize('own', $tournament, $request->user());
        $tournament_types = TournamentType::pluck('type_name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $page_section = 'organize';
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'cardpools', 'page_section'));
    }

    /**
     * Updates tournament.
     * @param $id tournament id
     * @param Requests\TournamentRequest $request
     * @return redirects
     */
    public function update($id, Requests\TournamentRequest $request)
    {
        $tournament = Tournament::withTrashed()->findorFail($id);
        $this->authorize('own', $tournament, $request->user());
        $request->sanitize_data();
        $tournament->update(['incomplete' => 0]); // clear incomplete flag if validation was ok
        $tournament->update($request->all());

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament updated.');
    }

    /**
     * Transfers tournament.
     * @param $id tournament id
     * @param Request $request
     * @return redirects
     */
    public function transfer($id, Request $request)
    {
        $tournament = Tournament::findorFail($id);
        $oldowner = $tournament->creator;
        $this->authorize('own', $tournament, $request->user());
        $tournament->update($request->all());

        // update badges
        if ($tournament->approved) {
            App('App\Http\Controllers\BadgeController')->addTOBadges($tournament->creator);
            App('App\Http\Controllers\BadgeController')->addTOBadges($oldowner);
        }

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament transferred.');
    }

    /**
     * Shows tournament information.
     * @param $id tournament id
     * @param Request $request
     * @return view
     */
    public function show($id, Request $request)
    {
        $tournament = Tournament::withTrashed()->findorFail($id);
        // rejected or soft deleted tournaments can only be seen by creator and admins
        if (($tournament->approved === 0 || $tournament->deleted_at) &&
            (!$request->user() || $request->user()->admin == 0 && $request->user()->id != $tournament->creator))
        {
            abort(403);
        }

        // all usernames for transferring
        if ($request->user() && ($request->user()->admin || $request->user()->id == $tournament->creator)) {
            $all_users = User::orderBy('name')->pluck('name', 'id');
        }

        $type = $tournament->tournament_type->type_name;
        $message = session()->has('message') ? session('message') : '';
        $nowdate = date('Y.m.d.');
        $user = $request->user();
        $entries = $tournament->entries;
        $regcount = $tournament->registration_number();

        // tournament entries
        $entries_swiss = [];
        $entries_top = [];
        if ($tournament->players_number)
        {
            $this->pushEntries($tournament->players_number, $entries, $entries_swiss, 'rank');
        }
        if ($tournament->top_number)
        {
            $this->pushEntries($tournament->top_number, $entries, $entries_top, 'rank_top');
        }

        // user's entry
        if (is_null($user))
        {
            $user_entry = null;
        } else {
            $user_entry = Entry::where('tournament_id', $tournament->id)->where('user', $user->id)->first();
        }

        return view('tournaments.view',
            compact('tournament', 'message', 'type', 'nowdate', 'user', 'entries',
                'user_entry', 'entries_swiss', 'entries_top', 'regcount', 'all_users'));
    }

    /**
     * Creates array for tournament entry table
     * @param $row_number number of entries
     * @param $entries entries array
     * @param $target target array for entry rows
     * @param $rank rank string to consider on entry object (rank / rank_top)
     */
    private function pushEntries($row_number, &$entries, &$target, $rank)
    {
        for ($i = 1; $i <= $row_number; $i++) {
            $current = [];
            foreach ($entries as $entry) {
                if ($entry[$rank] == $i) {
                    array_push($current, $entry);   // also works with conflicts
                }
            }
            array_push($target, $current);
        }
    }

    /**
     * Soft deletes tournament.
     * @param $id tournament id
     * @param Request $request
     * @return redirects
     */
    public function destroy($id, Request $request)
    {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());
        $user = $request->user()->id;

        Tournament::destroy($id);
        if (strpos($request->headers->get('referer'), 'tournaments') !== false && $tournament->creator == $user) {
            return view('organize', ['user' => $user, 'page_section' => 'organize'])->with('message', 'Tournament deleted.');    // deleted from tournament details page
        } else {
            return back()->with('message', 'Tournament deleted.');  // deleted from Organize or Admin page
        }
    }

    /**
     * Deletes tournament and its entries forever.
     * Required authorization: you are the creator, or you are admin and tournament is incomplete or you are Necro
     * @param $id
     * @param Request $request
     */
    public function purge($id, Request $request) {
        $tournament = Tournament::withTrashed()->findorFail($id);
        $this->authorize('purge', $tournament, $request->user());

        // hard delete tournament, delete its entries
        $tournament->entries()->delete();
        $tournament->forceDelete();

        return back()->with('message', 'Tournament hard deleted.');
    }

    /**
     * JSON API for listing tournaments.
     * @param Request $request GET parameters:
     *      approved, concluded, start, end, type, country, state, deleted, creator, user
     * @return mixed JSON result
     */
    public function tournamentJSON(Request $request) {
        // order by
        if ($request->input('concluded')) {
            $ordering = 'desc';
        } else {
            $ordering = 'asc';
        }
        // initial query
        $tournaments = Tournament::orderBy('date', $ordering)
            ->with(array('tournament_type' => function($query){
                $query->select('id', 'type_name');
            }, 'cardpool' => function($query){
                $query->select('id', 'name');
            }));

        // filtering
        if ($request->input('incomplete')) {
            $tournaments = $tournaments->where('incomplete', '1');
        } else {
            $tournaments = $tournaments->where('incomplete', '0');
        }
        if (!is_null($request->input('approved'))) {
            if ($request->input('approved') != 'null') {
                $tournaments = $tournaments->where('approved', $request->input('approved'));
            } else {
                $tournaments = $tournaments->whereNull('approved');
            }
        }
        if ($request->input('start')) {
            $tournaments = $tournaments->where('date', '>=', $request->input('start'));
        }
        if ($request->input('end')) {
            $tournaments = $tournaments->where('date', '<=', $request->input('end'));
        }
        if (!is_null($request->input('concluded'))) {
            $tournaments = $tournaments->where('concluded', $request->input('concluded'));
        }
        if ($request->input('type')) {
            $tournaments = $tournaments->where('tournament_type_id', $request->input('type'));
        }
        if (!is_null($request->input('recur'))) {
            if ($request->input('recur')) {
                $tournaments = $tournaments->whereNotNull('recur_weekly')->orderBy('recur_weekly');
            } else {
                $tournaments = $tournaments->whereNull('recur_weekly');
            }
        }
        if ($request->input('country')) {
            $tournaments = $tournaments->where('location_country', $request->input('country'));
        }
        if ($request->input('cardpool')) {
            $tournaments = $tournaments->where('cardpool_id', $request->input('cardpool'));
        }
        if ($request->input('state')) {
            $tournaments = $tournaments->where('location_state', $request->input('state'));
        }
        if ($request->input('creator')) {
            $tournaments = $tournaments->where('creator', $request->input('creator'));
        }
        if (!is_null($request->input('conflict'))) {
            $tournaments = $tournaments->where('conflict', $request->input('conflict'));
        }
        if ($request->input('deleted')) {
            $tournaments = $tournaments->onlyTrashed();
        }
        if ($request->input('foruser')) {
            $tournaments = $tournaments->whereIn('id', function($query) use ($request) {
                $query->select('tournament_id')->from(with(new Entry)->getTable())->where('user', $request->input('foruser'));
            });
        }

        $tournaments = $tournaments->select()->get();

        // modify and flatten result
        $result = [];
        foreach($tournaments as $tournament) {
            // location
            if ($tournament->tournament_type_id == 7) {
                $location = 'online';
            } else if ($tournament->location_country === 'United States') {
                $location = $tournament->location_country.', '.$tournament->location_state.', '.$tournament->location_city;
            } else {
                $location = $tournament->location_country.', '.$tournament->location_city;
            }

            array_push($result, [
                'id' => $tournament->id,
                'title' => $tournament->title,
                'type' => $tournament->tournament_type['type_name'],
                'date' => $tournament->date,
                'creator_id' => $tournament->creator,
                'creator_name' => $tournament->user()->first()->name,
                'created_at' => $tournament->created_at->format('Y.m.d. H:i:s'),
                'cardpool' => $tournament->cardpool['name'],
                'location' => $location,
                'location_lat' => $tournament->location_lat,
                'location_lng' => $tournament->location_long,
                'address' => $tournament->location_address,
                'store' => $tournament->location_store,
                'contact' => $tournament->contact,
                'place_id' => $tournament->location_place_id,
                'concluded' => $tournament->concluded == 1,
                'approved' => $tournament->approved,
                'players_count' => $tournament->players_number,
                'top_count' => $tournament->top_number,
                'registration_count' => $tournament->registration_number(),
                'claim_count' => $tournament->claim_number(),
                'claim_conflict' => $tournament->conflict == 1,
                'recurring_day' => $tournament->recur_weekly ? $tournament->recurDay() : null,
                'charity' => $tournament->charity == 1
            ]);

            // user specific claim
            if ($request->input('user') || $request->input('foruser')) {
                $userId = $request->input('user') ? $request->input('user') : $request->input('foruser');
                $entry = Entry::where('tournament_id', $tournament->id)->where('user', $userId)
                    ->whereNotNull('rank')->first();
                $result[count($result)-1]['user_claim'] = !is_null($entry);
            }

            // winner IDs
            if ($tournament->concluded) {
                if ($tournament->top_number) {
                    $winner = Entry::where('tournament_id', $tournament->id)->where('rank_top', 1)->first(); // with top cut
                } else {
                    $winner = Entry::where('tournament_id', $tournament->id)->where('rank', 1)->first(); // without top cut
                }
                if (!is_null($winner)) {
                    $result[count($result)-1]['winner_runner_identity'] = $winner['runner_deck_identity'];
                    $result[count($result)-1]['winner_corp_identity'] = $winner['corp_deck_identity'];
                }
            }
        }
        return response()->json($result);
    }

    /**
     * Deletes all anonym claims (from NRTM import) from a tournament
     * @param $id tournament ID
     * @param Request $request
     * @return mixed
     */
    public function clearAnonym($id, Request $request) {

        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());

        // delete claims
        Entry::where('tournament_id', $tournament->id)->whereNull('runner_deck_id')->whereNotNull('import_username')->delete();
        $tournament->update(['import' => 0]);

        // clear conflicts if exists and solved
        $tournament->updateConflict();

        return redirect()->back()->with('message', 'You have cleared all claims by NRTM import.');

    }

    /**
     * Receives player number and top cut player number from the
     * conclude tournament manually form on the conclude modal.
     * @param $id tournament ID
     * @param Requests\ConcludeRequest $request
     * @return mixed
     */
    public function concludeManual($id, Requests\ConcludeRequest $request) {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());

        $tournament->update(array_merge($request->all(), ['concluded' => true]));

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament concluded.');
    }

    /**
     * Receives NRTM json file from the conclude tournament modal
     * @param $id tournament ID
     * @param NRTMRequest $request
     * @return mixed
     */
    public function concludeNRTM($id, Requests\NRTMRequest $request) {
        // check if existing tournament or create via importing
        if ($id == -1) {
            // create via importing
            $this->authorize('logged_in', Tournament::class, $request->user());
            $tournament = Tournament::create([
                'creator' => $request->user()->id,
                'tournament_type_id' => 1,
                'cardpool_id' => 'unknown',
                'concluded' => 1,
                'incomplete' => 1
            ]);
        } else {
            // conclude existing tournament
            $tournament = Tournament::findorFail($id);
            $this->authorize('own', $tournament, $request->user());
        }

        $errors = [];

        // conclusion code
        if ($request->input('conclusion_code')) {
            // move from temp
            rename('tjsons/nrtm/import_'.$request->input('conclusion_code').'.json', 'tjsons/'.$tournament->id.'.json');
            // process file
            $json = json_decode(file_get_contents('tjsons/' . $tournament->id . '.json'), true);
            $this->processNRTMjson($json, $tournament, $errors);
        } elseif ($request->hasFile('jsonresults') && $request->file('jsonresults')->isValid()) { // NRTM JSON
                // store file
                $request->file('jsonresults')->move('tjsons', $tournament->id . '.json');
                // process file
                $json = json_decode(file_get_contents('tjsons/' . $tournament->id . '.json'), true);
                $this->processNRTMjson($json, $tournament, $errors);
        } elseif ($request->hasFile('csvresults') && $request->file('csvresults')->isValid()) { // CSV file
                // store file
                $request->file('csvresults')->move('tjsons', $tournament->id . '.csv');
                // process file
                $handle = fopen('tjsons/' . $tournament->id . '.csv', 'r');
                $csv = []; $topcut = 0;
                while ($row = fgetcsv($handle, 0, ';')) {
                    array_push($csv, $row);
                    if (intval($row[2]) > $topcut) {
                        $topcut = intval($row[2]);
                    }
                }
                $this->processCSV($csv, $tournament, $topcut, $errors);
        } else {
                array_push($errors, 'There was a problem with uploading the file.');
        }

        $tournament->update(['concluded' => true]);

        if ($id == -1) {    // new tournament via import
            // there was a failure, do not accept
            if (count($errors)) {
                $tournament->entries()->delete();
                $tournament->forceDelete();
                array_unshift($errors, 'Problem(s) occured during import. Please fix these issues first:');
                return redirect()->route('organize', $tournament->id)
                    ->withErrors($errors);
            } else {
                return redirect()->route('tournaments.edit', $tournament->id);
            }

        } elseif (count($errors)) { // redirecting to show errors
            return redirect()->route('tournaments.show', $tournament->id)
                ->withErrors($errors);
        } else {    // redirecting to show newly concluded tournament
            return redirect()->route('tournaments.show', $tournament->id)
                ->with('message', 'Tournament concluded by import.');
        }
    }

    /**
     * Endpoint for NRTM results upload. Stores JSON in a temporal file, provides conclusion code
     * @param Request $request
     * @return response conclusion code
     */
    public function NRTMEndpoint(Request $request) {
        if ($request->hasFile('jsonresults') && $request->file('jsonresults')->isValid()) {
            // generate code
            $code = rand(100000, 999999);
            while (file_exists('tjsons/nrtm/import_'.$code.'.json')) {
                $code = rand(100000, 999999);
            }
            // store file
            $request->file('jsonresults')->move('tjsons/nrtm', 'import_'.$code.'.json');
            return response()->json(['code' => $code]);
        } else {
            return response()->json(['error' => 'File upload failed.']);
        }
    }

    /**
     * updates tournament with the anonymous claims from the NRTM json
     * @param $json NRTM JSON
     * @param $tournament tournament object
     */
    private function processNRTMjson($json, &$tournament, &$errors) {

        if (array_key_exists('players', $json)) {

            // error checking
            if (!array_key_exists('corpIdentity', $json['players'][0]) ||
                (!array_key_exists('runnerIdentity', $json['players'][0]))) {
                    array_push($errors, 'JSON is missing identities. Please update your NRTM app.');
                    return false;
            }

            $tournament->concluded = true;
            $tournament->import = 1;
            $tournament->top_number = $json['cutToTop']; // number of players in top cut
            $tournament->players_number = count($json['players']); // number of players

            foreach ($json['players'] as $swiss) {

                // get identities
                $corp = CardIdentity::where('title', 'LIKE', '%' . $swiss['corpIdentity'] . '%')->first();
                $runner = CardIdentity::where('title', 'LIKE', '%' . $swiss['runnerIdentity'] . '%')->first();
                $existing = Entry::where('tournament_id', $tournament->id)->where('rank', $swiss['rank'])->first();

                // error handling
                if (is_null($corp)) {
                    array_push($errors, 'Cannot find corporation identity "' . $swiss['corpIdentity'] . '". Be sure to download faction names in NRTM. See F.A.Q.');
                }
                if (is_null($runner)) {
                    array_push($errors, 'Cannot find runner identity "' . $swiss['runnerIdentity'] . '". Be sure to download faction names in NRTM. See F.A.Q.');
                }

                // create claims with IDs, skipping this if there is a user claim on the same rank with same IDs
                if ($this->assessNewClaimNeed($corp, $runner, $existing)) { // no entry or conflicting entry

                    // checking top cut
                    $ranktop = 0;
                    if (array_key_exists('eliminationPlayers', $json)) {
                        foreach ($json['eliminationPlayers'] as $topcut) {
                            if ($topcut['id'] == $swiss['id']) {
                                $ranktop = $topcut['rank'];
                                break;
                            }
                        }
                    }

                    // saving new claim
                    Entry::create([
                        'approved' => 1,
                        'tournament_id' => $tournament->id,
                        'rank' => $swiss['rank'],
                        'rank_top' => $ranktop,
                        'corp_deck_identity' => $corp->id,
                        'corp_deck_title' => $swiss['corpIdentity'],
                        'runner_deck_identity' => $runner->id,
                        'runner_deck_title' => $swiss['runnerIdentity'],
                        'import_username' => $swiss['name']
                    ]);
                }
            }

            // update tournament metadata with JSON if missing
            if (!strlen($tournament->title) && array_key_exists('name', $json)) {
                $tournament->title = $json['name'];
            }

            $tournament->save();

            // create conflict if needed
            $tournament->updateConflict();

        } else {
            array_push($errors, 'There was an error with the uploaded file. Please update your NRTM app.');
        }
    }

    /**
     * Updates tournament anonym claims based on CSV results.
     * @param $csv array
     * @param $tournament
     * @param $topcut number of players in top-cut
     * @param $errors
     * @return boolean
     */
    private function processCSV($csv, &$tournament, $topcut, &$errors) {
        $tournament->concluded = true;
        $tournament->import = 1;
        $tournament->top_number = $topcut; // number of players in top cut
        $tournament->players_number = count($csv); // number of players

        foreach($csv as $row) {
            // error handling
            if (count($row) < 5) {
                array_push($errors, 'Cannot process: '.implode($row, ';').
                    ' - expected format is: name;swiss-rank;topcut-rank;runnerID;corpID' );
                return false;
            }
            // get identities
            $corp = CardIdentity::where('title', 'LIKE', '%' . $row[4] . '%')->first();
            $runner = CardIdentity::where('title', 'LIKE', '%' . $row[3] . '%')->first();
            $existing = Entry::where('tournament_id', $tournament->id)->where('rank', $row[1])->first();

            // error handling
            if (is_null($corp)) {
                array_push($errors, 'Cannot find corporation identity "' . $row[4] . '". Be sure to use correct faction names. See F.A.Q.');
            }
            if (is_null($runner)) {
                array_push($errors, 'Cannot find runner identity "' . $row[3] . '". Be sure to use correct faction names. See F.A.Q.');
            }

            // create new claim if needed
            if ($this->assessNewClaimNeed($corp, $runner, $existing)) {

                // saving new claim
                Entry::create([
                    'approved' => 1,
                    'tournament_id' => $tournament->id,
                    'rank' => $row[1],
                    'rank_top' => $row[2],
                    'corp_deck_identity' => $corp->id,
                    'corp_deck_title' => $row[4],
                    'runner_deck_identity' => $runner->id,
                    'runner_deck_title' => $row[3],
                    'import_username' => $row[0]
                ]);

            }
        }

        $tournament->save();

        // create conflict if needed
        $tournament->updateConflict();
    }

    private function assessNewClaimNeed($corp, $runner, $existing) {
        return !is_null($corp) && !is_null($runner) && // identities are found
            (is_null($existing) || strcmp($runner->id, $existing->runner_deck_identity) != 0 ||
                strcmp($corp->id, $existing->corp_deck_identity) != 0);
    }
}

