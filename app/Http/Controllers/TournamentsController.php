<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Entry;
use App\Tournament;
use App\TournamentType;
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
        Tournament::create($request->all());
        return redirect()->action('PagesController@organize')->with('message', 'Tournament created.');
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
        return redirect()->action('PagesController@organize')->with('message', 'Tournament updated.');
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
        if (is_null($user))
        {
            $user_entry = null;
        } else {
            $user_entry = Entry::where('tournament_id', $tournament->id)->where('user', $user->id)->first();
        }
        $decks = ['public' => ['corp' => [], 'runner' => [] ]];
        $decks_two_types = false;
        if (!is_null($user) && (is_null($user_entry) || is_null($user_entry->rank)))
        {
            $decks = app('App\Http\Controllers\NetrunnerDBController')->getDeckData();
            $decks_two_types = count($decks['public']['corp']) > 0 && count($decks['private']['corp']) > 0;
        }
        return view('tournaments.view',
            compact('tournament', 'message', 'type', 'nowdate', 'user', 'entries',
                'user_entry', 'decks', 'entries_swiss', 'entries_top', 'decks_two_types'));
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
        return back()->with('message', 'Tournament deleted.');
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

        $tournaments = $tournaments->select('id', 'title', 'location_country', 'location_state', 'tournament_type_id',
            'location_city', 'date', 'players_number', 'cardpool_id', 'concluded', 'approved', 'conflict',
            'location_store', 'location_address', 'location_place_id', 'contact')->get();

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
        }
        return response()->json($result);
    }

}
