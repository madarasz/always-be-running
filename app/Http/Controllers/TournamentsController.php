<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Entry;
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

        return view('tournaments.create', compact('tournament_types', 'tournament', 'cardpools'));
    }

    /**
     * Form for tournament edit.
     * @param $id tournament id
     * @param Request $request
     * @return view
     */
    public function edit($id, Request $request)
    {
        $tournament = Tournament::findOrFail($id);
        $this->authorize('own', $tournament, $request->user());
        $tournament_types = TournamentType::pluck('type_name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'cardpools'));
    }

    /**
     * Updates tournament.
     * @param $id tournament id
     * @param Requests\TournamentRequest $request
     * @return redirects
     */
    public function update($id, Requests\TournamentRequest $request)
    {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());
        $request->sanitize_data();
        $tournament->update($request->all());

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament updated.');
    }

    /**
     * Shows tournament information.
     * @param $id tournament id
     * @param Request $request
     * @return view
     */
    public function show($id, Request $request)
    {
        $tournament = Tournament::findorFail($id);
        // rejected tournaments can only be seen by creator and admins
        if ($tournament->approved === 0 &&
            (!$request->user() || $request->user()->admin == 0 && $request->user()->id != $tournament->creator))
        {
            abort(403);
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
                'user_entry', 'entries_swiss', 'entries_top', 'regcount'));
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
        Tournament::destroy($id);

        if (strpos($request->headers->get('referer'), 'tournaments') !== false) {
            $user = $request->user()->id;
            return view('organize', ['user' => $user])->with('message', 'Tournament deleted.');    // deleted from tournament details page
        } else {
            return back()->with('message', 'Tournament deleted.');  // deleted from Organize or Admin page
        }
    }

    /**
     * JSON API for listing tournaments.
     * @param Request $request GET parameters:
     *      approved, concluded, start, end, type, country, state, deleted, creator, user
     * @return mixed JSON result
     */
    public function tournamentJSON(Request $request) {
        // initial query
        $tournaments = Tournament::orderBy('date')
            ->with(array('tournament_type' => function($query){
                $query->select('id', 'type_name');
            }, 'cardpool' => function($query){
                $query->select('id', 'name');
            }));

        // filtering
        if (!is_null($request->input('approved'))) {
            $tournaments = $tournaments->where('approved',$request->input('approved'));
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
        if ($request->input('country')) {
            $tournaments = $tournaments->where('location_country', $request->input('country'));
        }
        if ($request->input('state')) {
            $tournaments = $tournaments->where('location_state', $request->input('state'));
        }
        if ($request->input('creator')) {
            $tournaments = $tournaments->where('creator', $request->input('creator'));
        }
        if ($request->input('deleted')) {
            $tournaments = $tournaments->whereNotNull('deleted_at');
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
                'cardpool' => $tournament->cardpool['name'],
                'location' => $location,
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
                'claim_conflict' => $tournament->conflict == 1
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
     * @param Request $request
     * @return mixed
     */
    public function concludeNRTM($id, Request $request) {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());

        $tournament->update(['concluded' => true]);

        $this->processNRTMjson($request, $tournament);

        // redirecting to show newly created tournament
        return redirect()->route('tournaments.show', $tournament->id)
            ->with('message', 'Tournament concluded by NRTM import.');
    }

    /**
     * updates tournament with the anonymous claims from the NRTM json
     * @param $request
     * @param $tournament
     */
    private function processNRTMjson(&$request, &$tournament) {

        if ($request->hasFile('jsonresults') && $request->file('jsonresults')->isValid()) {
            // store file
            $request->file('jsonresults')->move('tjsons', $tournament->id.'.json');

            // process file
            $json = json_decode(file_get_contents('tjsons/'.$tournament->id.'.json'), true);
            $tournament->concluded = true;
            $tournament->import = 1;
            $tournament->top_number = $json['cutToTop']; // number of players in top cut
            $tournament->players_number = count($json['players']); // number of players
            foreach($json['players'] as $swiss) {

                // get identities
                $corp = CardIdentity::where('title', 'LIKE', '%'.$swiss['corpIdentity'].'%')->first();
                $runner = CardIdentity::where('title', 'LIKE', '%'.$swiss['runnerIdentity'].'%')->first();
                $existing = Entry::where('tournament_id', $tournament->id)->where('rank', $swiss['rank'])->first();

                // create claims with IDs, skipping this if there is a user claim on the same rank with same IDs
                if (!is_null($corp) && !is_null($runner) && // identities are found
                    (is_null($existing) || strcmp($runner->id, $existing->runner_deck_identity) != 0 ||
                        strcmp($corp->id, $existing->corp_deck_identity) != 0)) { // no entry or conflicting entry

                    // checking top cut
                    $ranktop = 0;
                    foreach($json['eliminationPlayers'] as $topcut) {
                        if ($topcut['id'] == $swiss['id']) {
                            $ranktop = $topcut['rank'];
                            break;
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

            $tournament->save();

            // create conflict if needed
            $tournament->updateConflict();
        }
    }
}
