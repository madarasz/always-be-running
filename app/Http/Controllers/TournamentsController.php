<?php

namespace App\Http\Controllers;

use App\CardPack;
use App\Country;
use App\Entry;
use App\User;
use App\Tournament;
use App\TournamentType;
use App\UsState;
use Illuminate\Http\Request;

use App\Http\Requests;

class TournamentsController extends Controller
{
    public function store(Requests\TournamentRequest $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $request->sanitize_data($request->user()->id);
        Tournament::create($request->all());
        return redirect()->action('TournamentsController@my')->with('message', 'Tournament created.');
    }

    public function index()
    {
        return view('home');    // TODO: page redirect
    }

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

    public function update($id, Requests\TournamentRequest $request)
    {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());
        $request->sanitize_data();
        $tournament->update($request->all());
        return redirect()->action('TournamentsController@my')->with('message', 'Tournament updated.');
    }

    public function show($id, Request $request)
    {
        $tournament = Tournament::findorFail($id);
        $type = $tournament->tournament_type->type_name;
        $country_name = $tournament->country->name;
        $message = session()->has('message') ? session('message') : '';
        $nowdate = date('Y.m.d.');
        $user = $request->user();
//        $entries = $tournament->entries; TODO not working for some reason
        $entries = Entry::where('tournament_id', $tournament->id)->get();
        $entries_swiss = [];
        $entries_top = [];
        // TODO: refaction to function
        if ($tournament->players_number) {
            for ($i = 1; $i <= $tournament->players_number; $i++) {
                $found = false;
                foreach ($entries as $entry) {
                    if ($entry->rank == $i) {
                        array_push($entries_swiss, $entry);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    array_push($entries_swiss, []);
                }
            }
        }
        if ($tournament->top_number) {
            for ($i = 1; $i <= $tournament->top_number; $i++) {
                $found = false;
                foreach ($entries as $entry) {
                    if ($entry->rank_top == $i) {
                        array_push($entries_top, $entry);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    array_push($entries_top, []);
                }
            }
        }
        if (is_null($user))
        {
            $user_entry = null;
        } else {
            $user_entry = Entry::where('tournament_id', $tournament->id)->where('user', $user->id)->first();
        }
        $state_name = $tournament->location_us_state == 52 ? '' : UsState::findorFail($tournament->location_us_state)->name;
        $decks = [];
        $decks_two_types = false;
        if (!is_null($user)) {
            $decks = app('App\Http\Controllers\NetrunnerDBController')->getDeckData();
            $decks_two_types = count($decks['public']['corp']) > 0 && count($decks['private']['corp']) > 0;
        }
        return view('tournaments.view',
            compact('tournament', 'country_name', 'state_name', 'message', 'type', 'nowdate', 'user', 'entries',
                'user_entry', 'decks', 'entries_swiss', 'entries_top', 'decks_two_types'));
    }

    public function destroy($id, Request $request)
    {
        $tournament = Tournament::findorFail($id);
        $this->authorize('own', $tournament, $request->user());
        Tournament::destroy($id);
        return back()->with('message', 'Tournament deleted.');
    }

    public function my(Request $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user = $request->user()->id;
        $nowdate = date('Y.m.d.');
        $created = Tournament::where('creator', $user)->where('deleted_at', null)->get();
        $registered = [];
        $message = session()->has('message') ? session('message') : '';
        return view('my', compact('user', 'created', 'nowdate', 'registered', 'message'));
    }

    public function register(Request $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
    }
}
