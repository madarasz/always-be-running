<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Entry;
use App\TournamentFormat;
use App\User;
use App\Tournament;
use App\TournamentType;
use App\CardIdentity;
use App\Video;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // if concluded, set concluded by, concluded at
        if ($tournament->concluded) {
            $tournament->update(array_merge($request->all(), [
                'concluded_by' => $request->user()->id,
                'concluded_at' => date('Y-m-d H:i:s')
            ]));
        }

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
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
        $tournament_types = TournamentType::orderBy('order')->pluck('type_name', 'id')->all();
        $tournament_formats = TournamentFormat::orderBy('order')->pluck('format_name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $tournament = new Tournament();

        // make cardpool default to 'not yet known'
        $tournament->cardpool_id = 'unknown';

        $page_section = 'organize';
        return view('tournaments.create', compact('tournament_types', 'tournament', 'cardpools', 'page_section',
            'tournament_formats'));
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
        $tournament_types = TournamentType::orderBy('order')->pluck('type_name', 'id')->all();
        $tournament_formats = TournamentFormat::orderBy('order')->pluck('format_name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $page_section = 'organize';
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'cardpools', 'page_section',
            'tournament_formats'));
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
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
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
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
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

        // all usernames for transferring, IDs for adding entries
        if ($request->user()) {
            $all_users = User::orderBy('name')->pluck('name', 'id');
        }

        $runnerIDs = $this->categorizeIDs(CardIdentity::where('runner', 1)
            ->orderBy('faction_code')->orderBy('title')->groupBy('title')
            ->get(['pack_code', 'faction_code', 'title', DB::raw('MAX(id) as id')]));
        $corpIDs = $this->categorizeIDs(CardIdentity::where('runner', 0)
            ->orderBy('faction_code')->orderBy('title')->groupBy('title')
            ->get(['pack_code', 'faction_code', 'title', DB::raw('MAX(id) as id')]));
        $type = $tournament->tournament_type->type_name;
        $format = $tournament->tournament_format->format_name;
        $message = session()->has('message') ? session('message') : '';
        $nowdate = date('Y.m.d.');
        $user = $request->user();
        $entries = $tournament->entries;
        $registeredIds = Entry::where('tournament_id', $id)->where('user', '>', 0)->pluck('user');
        $registered = User::whereIn('id', $registeredIds)->get()->sortBy('displayUsernameLower');
        $regcount = $tournament->registrationCount;

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
            compact('tournament', 'message', 'type', 'format', 'nowdate', 'user', 'entries', 'runnerIDs', 'corpIDs',
                'user_entry', 'entries_swiss', 'entries_top', 'regcount', 'all_users', 'registered'));
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
     * Returns JSON data on upcoming tournaments and recurring events.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcomingTournamentJSON(Request $request) {
        $startTime = microtime(true);

        $yesterday = date('Y.m.d.', time() - 86400); // to be on the safe side
        $tournaments = Tournament::where('date', '>=', $yesterday)->where('concluded', 0)
            ->where(function($query){
                $query->where('approved', 1)->orWhereNull('approved');
            })->with(['photosCount', 'videosCount', 'registrationCount', 'claimCount', 'winner'])
            ->orderBy('date', 'asc')->get();
        $recurring = Tournament::whereNotNull('recur_weekly')->where(function($query){
                $query->where('approved', 1)->orWhereNull('approved');
            })->with(['photosCount', 'videosCount', 'registrationCount', 'claimCount', 'winner'])
            ->orderBy('recur_weekly')->get();

        $endtime = microtime(true);

        $result = [
            'tournaments' => $this->tournametDataFormat($tournaments),
            'recurring_events' => $this->tournametDataFormat($recurring),
            'rendered_in' => $endtime - $startTime
        ];

        return response()->json($result);
    }

    public function resultTournamentJSON(Request $request) {
        $startTime = microtime(true);

        $tournaments = Tournament::where('concluded', 1)->where(function($query){
            $query->where('approved', 1)->orWhereNull('approved');
        })->where('incomplete', 0)->orderBy('date', 'desc');

        $this->applyLimitOffset($request, $tournaments);

        $tournaments = $tournaments
            ->with(['photosCount', 'videosCount', 'registrationCount', 'claimCount', 'winner'])->get();

        $result = $this->tournametDataFormat($tournaments);

        $endtime = microtime(true);
        if (count($result)) {
            $result[count($result) - 1]['rendered_in'] = $endtime-$startTime;
        }

        return response()->json($result);
    }

    private function applyLimitOffset(Request $request, &$tournaments) {
        // applying limit
        if ($request->input('limit')) {
            $tournaments = $tournaments->take(intval($request->input('limit')));
        }

        // applying offset
        if ($request->input('offset')) {
            $tournaments = $tournaments->skip(intval($request->input('offset')));
            // offset without limit bugfix
            if (!$request->input('limit')) {
                $tournaments = $tournaments->take(PHP_INT_MAX);
            }
        }
    }

    /**
     * Formats tournament data.
     * @param $data array event data to be represented as JSON
     * @param null $forUser user ID if claim status, broken deck links to be added for user
     * @return array
     */
    private function tournametDataFormat($data, $forUser = null) {
        $appUrl = env('APP_URL');
        $result = [];
        $tournament_types = TournamentType::get()->pluck('type_name', 'id');
        $tournament_formats = TournamentFormat::get()->pluck('format_name', 'id');
        $cardpool_names = CardPack::get()->pluck('name', 'id');

        foreach($data as $tournament) {

            $user = $tournament->user;

            $event_data = [
                'id' => $tournament->id,
                'title' => $tournament->title,
                'creator_id' => $tournament->creator,
                'creator_name' => $user->displayUsername(),
                'creator_supporter' => $user->supporter,
                'creator_class' => $user->linkClass(),
                'created_at' => $tournament->created_at->format('Y.m.d. H:i:s'),
                'location' => $tournament->location(),
                'location_lat' => $tournament->location_lat,
                'location_lng' => $tournament->location_long,
                'location_country' => $tournament->location_country,
                'location_state' => $tournament->location_state,
                'address' => $tournament->location_address,
                'store' => $tournament->location_store,
                'place_id' => $tournament->location_place_id,
                'contact' => $tournament->contact,
                'approved' => $tournament->approved,
                'registration_count' => $tournament->registrationCount, // ~ +0.1s
                'photos' => $tournament->photosCount, // ~ +0.1s
                'url' => $appUrl.$tournament->seoUrl(),
                'link_facebook' => $tournament->link_facebook
            ];

            // if not recurring / recurring
            if (!$tournament->recur_weekly) {
                $event_data['cardpool'] = $cardpool_names[$tournament->cardpool_id];
                $event_data['date'] = $tournament->date;
                $event_data['type'] = $tournament_types[$tournament->tournament_type_id];
                $event_data['format'] = $tournament_formats[$tournament->tournament_format_id];
                $event_data['concluded'] = $tournament->concluded == 1;
                $event_data['charity'] = $tournament->charity == 1;
            } else {
                $event_data['recurring_day'] = $tournament->recurDay();
            }

            // if multiple days
            if ($tournament->end_date) {
                $event_data['end_date'] = $tournament->end_date;
            }

            // if concluded
            if ($tournament->concluded) {
                $event_data['players_count'] = $tournament->players_number;
                $event_data['top_count'] = $tournament->top_number;
                $event_data['claim_count'] = $tournament->claimCount;
                $event_data['claim_conflict'] = $tournament->conflict == 1;
                $event_data['matchdata'] = $tournament->import == 1 || $tournament->import == 4;
                $event_data['videos'] = $tournament->videosCount; // ~ +0.1s

                // winner IDs
                $winner = $tournament->winner;
                if (!is_null($winner)) {
                    $event_data['winner_runner_identity'] = $winner['runner_deck_identity'];
                    $event_data['winner_corp_identity'] = $winner['corp_deck_identity'];
                }
            }

            // for specific user, add broken flag
            if ($forUser) {
                $entry = Entry::where('tournament_id', $tournament->id)->where('user', $forUser)
                    ->whereNotNull('rank')->first();
                $event_data['user_claim'] = !is_null($entry);
                // check for broken claims
                if (!is_null($entry)) {
                    $event_data['user_claim_broken'] = $entry->broken_runner || $entry->broken_corp;
                }
            }

            array_push($result, $event_data);
        }

        return $result;
    }

    /**
     * JSON API for listing tournaments.
     * @param Request $request GET parameters:
     *      approved, concluded, start, end, type, country, state, deleted, creator, user, etc.
     * @return mixed JSON result
     */
    public function tournamentJSON(Request $request) {
        $startTime = microtime(true);

        // order by
        if ($request->input('concluded') || $request->input('desc')) {
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
            if ($request->input('concluded')) {
                $tournaments = $tournaments->where('tournament_type_id', '!=', 8);   // non-tournaments are left out for concluded ones
            }
        }
        if ($request->input('hide-non')) {
            $tournaments = $tournaments->where('tournament_type_id', '!=', 8);   // non-tournaments are left out for concluded ones
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
            if ($request->include_online) { // include online
                $tournaments = $tournaments->where(function ($q) use ($request) {
                    $q->where('location_country', $request->input('country'))->orWhere('tournament_type_id', 7);
                });
            } else { // just country filter
                $tournaments = $tournaments->where('location_country', $request->input('country'));
            }


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
        if (!is_null($request->input('videos'))) {  // just =1, =0 not supported
            $videoIDs = Video::where('flag_removed', false)->pluck('tournament_id')->all();
            $tournaments = $tournaments->whereIn('id', $videoIDs);
        }
        if ($request->input('deleted')) {
            $tournaments = $tournaments->onlyTrashed();
        }
        if ($request->input('foruser')) {
            $tournaments = $tournaments->whereIn('id', function($query) use ($request) {
                $query->select('tournament_id')->from(with(new Entry)->getTable())->where('user', $request->input('foruser'));
            });
        }

        $tournaments = $tournaments
            ->with(['photosCount', 'videosCount', 'registrationCount', 'claimCount', 'winner'])->get();

        // flatten result
        $userId = null;
        if ($request->input('user') || $request->input('foruser')) {
            $userId = $request->input('user') ? $request->input('user') : $request->input('foruser');
        }
        $result = $this->tournametDataFormat($tournaments, $userId);

        // insert time measurement on last element
        if (count($result)) {
            $result[count($result) - 1]['rendered_in'] = microtime(true) - $startTime;
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

        $tournament = Tournament::withTrashed()->findorFail($id);
        $this->authorize('conclude', $tournament, $request->user());

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
        $this->authorize('logged_in', $tournament, $request->user());

        $tournament->update(array_merge($request->all(), [
            'concluded' => true,
            'featured' => 0,
            'concluded_by' => $request->user()->id,
            'concluded_at' => date('Y-m-d H:i:s')
        ]));

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
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
            $this->authorize('logged_in', $tournament, $request->user());
        }

        $errors = [];

        // conclusion code
        if ($request->input('conclusion_code')) {
            // move from temp
            rename('tjsons/nrtm/import_'.$request->input('conclusion_code').'.json', 'tjsons/'.$tournament->id.'.json');
            // process file
            $json = json_decode(file_get_contents('tjsons/' . $tournament->id . '.json'), true);
            $this->processNRTMjson($json, $tournament, $errors, $request->user()->id);
        } elseif ($request->hasFile('jsonresults') && $request->file('jsonresults')->isValid()) { // NRTM JSON
                // store file
                $request->file('jsonresults')->move('tjsons', $tournament->id . '.json');
                // process file
                $json = json_decode(file_get_contents('tjsons/' . $tournament->id . '.json'), true);
                $this->processNRTMjson($json, $tournament, $errors, $request->user()->id);
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
                $this->processCSV($csv, $tournament, $topcut, $errors, $request->user()->id);
        } else {
                array_push($errors, 'There was a problem with uploading the file.');
        }

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
            return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                ->with('message', 'Tournament concluded by import.');
        }
    }

    /**
     * Resets tournament to an unconcluded state.
     * @param $id
     * @param Request $request
     */
    public function revertConclusion($id, Request $request) {
        $tournament = Tournament::findorFail($id);
        $this->authorize('conclude', $tournament, $request->user());

        $tournament->update([
            'concluded' => false,
            'concluded_by' => null,
            'concluded_at' => null,
            'import' => 0,
            'top_number' => null,
            'players_number' => null
        ]);

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', 'Tournament conclusion reverted.');
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

            // check for tournamentID
            $json = json_decode(file_get_contents('tjsons/nrtm/import_'.$code. '.json'), true);
            if (array_key_exists('abrTournamentId', $json)) {
                $tournamentId = intval($json['abrTournamentId']);
                $tournament = Tournament::find($tournamentId);

                if (!is_null($tournament)) { // tournament found
                    // move from temp
                    rename('tjsons/nrtm/import_'.$code.'.json', 'tjsons/'.$tournamentId.'.json');
                    // process file
                    $errors = [];
                    $this->processNRTMjson($json, $tournament, $errors, null);
                    if (count($errors) == 0) {
                        return response()->json([
                            'url' => $tournament->seoUrl(),
                            'code' => 42,
                            'status' => 'Results uploaded successfully.'
                        ]);
                    } else {
                        return response()->json([
                            'error' => $errors
                        ]);
                    }
                } else {
                    // error handling, tournament was not found
                    return response()->json([
                        'code' => $code,
                        'error' => 'Tournament with ID '.$tournamentId.' not found.'
                    ]);
                }
            } else {
                // give back conclusion code
                return response()->json([
                    'code' => $code,
                    'status' => 'JSON successfully stored.'
                ]);
            }
        } else {
            return response()->json(['error' => 'File upload failed.']);
        }
    }

    /**
     * updates tournament with the anonymous claims from the NRTM json
     * @param $json NRTM JSON
     * @param $tournament tournament object
     */
    private function processNRTMjson($json, &$tournament, &$errors, $user) {

        if (array_key_exists('players', $json)) {

            // error checking
            if (!array_key_exists('corpIdentity', $json['players'][0]) &&
                (!array_key_exists('runnerIdentity', $json['players'][0]))) {
                    array_push($errors, 'JSON is missing identities. NRTM app needs updating or identities are not set.');
                    return false;
            }
            foreach ($json['players'] as $swiss) {
                if (!array_key_exists('corpIdentity', $swiss)) {
                    array_push($errors, "Player '" . $swiss['name'] . "' has no corp identity.");
                    return false;
                }
                if (!array_key_exists('runnerIdentity', $swiss)) {
                    array_push($errors, "Player '" . $swiss['name'] . "' has no runner identity.");
                    return false;
                }
            }

            $tournament->concluded = true;
            $tournament->import = 1;
            $tournament->featured = 0;
            $tournament->top_number = $json['cutToTop']; // number of players in top cut
            $tournament->players_number = count($json['players']); // number of players
            $tournament->concluded_at = date('Y-m-d H:i:s');
            $tournament->concluded_by = $user;

            if (array_key_exists('uploadedFrom', $json) && $json['uploadedFrom'] == 'Cobra') {
                // imported by Cobr.ai
                $tournament->import = 4;
            } else {
                // imported by NRTM
                $tournament->import = 1;
            }

            foreach ($json['players'] as $swiss) {

                // get identities, newer versions first
                $corp = CardIdentity::where('title', 'LIKE', '%' . $swiss['corpIdentity'] . '%')->orderBy('id', 'desc')->first();
                $runner = CardIdentity::where('title', 'LIKE', '%' . $swiss['runnerIdentity'] . '%')->orderBy('id', 'desc')->first();
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
                        'type' => 10 + $tournament->import, // 11 if NRTM, 14 if Cobr.ai
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
    private function processCSV($csv, &$tournament, $topcut, &$errors, $user) {
        $tournament->concluded = true;
        $tournament->concluded_at = date('Y-m-d H:i:s');
        $tournament->concluded_by = $user;
        $tournament->import = 2;
        $tournament->featured = 0;
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
                    'type' => 12,
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

    /**
     * Puts IDs in a 2 dimensional array based on their faction.
     * Special filtering for mini-factions and draft ID which are moved to the end of the array.
     * Future proof if new factions created.
     * @param $identities
     * @return array
     */
    public function categorizeIDs($identities) {

        // preprocessing: mini-factions, draft
        foreach($identities as $id) {
            if (in_array($id->faction_code, ['adam', 'apex', 'sunny-lebeau'])) {
                $id->faction_code = 'mini-factions';
            }
            if ($id->pack_code === 'draft') {
                $id->faction_code = 'draft';
            }
        }

        // sorting into
        $result = [];
        foreach($identities as $id) {
            if (!array_key_exists($id->faction_code, $result)) {
                $result[$id->faction_code] = [];
            }
            $result[$id->faction_code][$id->id] = $id->title;
        }

        // postprocessing: mini-factions, draft to the end
        if (array_key_exists('mini-factions', $result)) {
            $mini = $result['mini-factions'];
            unset($result['mini-factions']);
            $result['mini-factions'] = $mini;
        }
        $draft = $result['draft'];
        unset($result['draft']);
        $result['draft'] = $draft;

        return $result;
    }
}

