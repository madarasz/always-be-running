<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Country;
use App\Entry;
use App\Tournament;
use App\TournamentType;
use App\UsState;
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
        return redirect()->action('TournamentsController@organize')->with('message', 'Tournament created.');
    }

    public function index()
    {
        return view('home');
    }

    public function discover()
    {
        $tournament_types = TournamentType::pluck('type_name', 'id')->all();
        $countries = Country::orderBy('name')->pluck('name', 'id')->all();
        $us_states = UsState::orderBy('name')->pluck('name', 'id')->all();
        $message = session()->has('message') ? session('message') : '';
        $tournaments = Tournament::orderBy('date')->get();
        return view('discover', compact('message', 'tournament_types', 'countries', 'us_states', 'tournaments'));
    }

    public function results(Request $request)
    {
        $user = $request->user()->id;
        $entries = Entry::where('user', $user)->orderBy('updated_at', 'desc')->get();
        $registered = [];
        foreach ($entries as $entry)
        {
            $stuff = $entry->tournament;
            if ($stuff && $stuff->approved !== 0)
            {
                $stuff['claim'] = $entry->rank > 0;
                array_push($registered, $stuff);
            }
        }
        $nowdate = date('Y.m.d.');
        $message = session()->has('message') ? session('message') : '';
        return view('results', compact('registered', 'message', 'nowdate'));
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
        $countries = Country::orderBy('name')->pluck('name', 'id')->all();
        $us_states = UsState::orderBy('name')->pluck('name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $tournament = new Tournament();
        $tournament->location_country = 0;
        return view('tournaments.create', compact('tournament_types', 'countries', 'us_states', 'tournament', 'cardpools'));
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
        $countries = Country::orderBy('name')->pluck('name', 'id')->all();
        $us_states = UsState::orderBy('name')->pluck('name', 'id')->all();
        $cardpools = CardPack::where('usable', 1)->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'countries', 'us_states', 'cardpools'));
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
        return redirect()->action('TournamentsController@organize')->with('message', 'Tournament updated.');
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
        $country_name = $tournament->country->name;
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
        $state_name = $tournament->location_us_state == 52 || $tournament->tournament_type_id == 6 ? '' : UsState::find($tournament->location_us_state)->name;
        $decks = [];
        $decks_two_types = false;
        if (!is_null($user))
        {
            $decks = app('App\Http\Controllers\NetrunnerDBController')->getDeckData();
            $decks_two_types = count($decks['public']['corp']) > 0 && count($decks['private']['corp']) > 0;
        }
        return view('tournaments.view',
            compact('tournament', 'country_name', 'state_name', 'message', 'type', 'nowdate', 'user', 'entries',
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
     * Show organize tournamnets page.
     * @param Request $request
     * @return view
     */
    public function organize(Request $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user = $request->user()->id;
        $nowdate = date('Y.m.d.');
        $created = Tournament::where('creator', $user)->where('deleted_at', null)->orderBy('date', 'desc')->get();
        $entries = Entry::where('user', $user)->orderBy('updated_at', 'desc')->get();
        $registered = [];
        foreach ($entries as $entry)
        {
            $stuff = $entry->tournament;
            if ($stuff && $stuff->approved !== 0)
            {
                $stuff['claim'] = $entry->rank > 0;
                array_push($registered, $stuff);
            }
        }
        $message = session()->has('message') ? session('message') : '';
        return view('organize', compact('user', 'created', 'nowdate', 'registered', 'message'));
    }

}
