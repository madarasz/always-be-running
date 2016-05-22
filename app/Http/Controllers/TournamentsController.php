<?php

namespace App\Http\Controllers;

use App\Country;
use App\Entry;
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
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        $tournament = new Tournament();
        $tournament->location_country = 0;
        return view('tournaments.create', compact('tournament_types', 'countries', 'us_states', 'tournament'));
    }

    public function edit($id, Request $request)
    {
        $tournament = Tournament::findOrFail($id);
        $this->authorize('own', $tournament, $request->user());
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'countries', 'us_states'));
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
//        $entries = $tournament->entries; TODO
        $entries = Entry::where('tournament_id', $tournament->id)->get();
        $user_entry = Entry::where('tournament_id', $tournament->id)->where('user', $user->id)->first();
        $state_name = $tournament->location_us_state == 52 ? '' : UsState::findorFail($tournament->location_us_state)->name;
        $decks = [];
        if (!is_null($user)) {
            $decks = app('App\Http\Controllers\ThronesController')->getDeckData();
        }
        return view('tournaments.view',
            compact('tournament', 'country_name', 'state_name', 'message', 'type', 'nowdate', 'user', 'entries',
                'user_entry', 'decks'));
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
