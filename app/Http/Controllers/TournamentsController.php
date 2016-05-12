<?php

namespace App\Http\Controllers;

use App\Country;
use App\Tournament;
use App\TournamentType;
use App\UsState;
use DB;

use App\Http\Requests;

class TournamentsController extends Controller
{
    public function store(Requests\TournamentRequest $request) {
        DB::table('tournaments')->insert([
            'tournament_type_id' => $request->tournament_type_id,
            'location_country' => $request->location_country,
            'location_us_state' => $request->location_us_state,
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'location_city' => $request->location_city,
            'location_store' => $request->location_store,
            'location_address' => $request->location_address,
            'concluded' => $request->concluded == 1 ? 1 : 0,
            'players_number' => $request->concluded == 1 ? $request->players_number : '',
            'top_number' => $request->concluded == 1 ? $request->top_number : '',
            'description' => $request->description,
            'decklist' => $request->decklist == 1 ? 1 : 0,
            'creator' => 0 // TODO: creator
        ]);
        return redirect()->action('PagesController@my')->with('message', 'Tournament created.');
    }

    public function index() {
        return view('home');    // TODO: page redirect
    }

    public function create()
    {
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        $tournament = new Tournament();
        $tournament->location_country = 0 ;
        return view('tournaments.create', compact('tournament_types', 'countries', 'us_states', 'tournament'));
    }

    public function edit($id) {
        $tournament = Tournament::findOrFail($id);
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'countries', 'us_states'));
    }

    public function update($id, Requests\TournamentRequest $request) {
        $tournament = Tournament::findorFail($id);
        $tournament->update($request->all());
    }

    public function show($id) {
        $tournament = Tournament::findorFail($id);
        $country_name = Country::findorFail($tournament->location_country)->name;
        $message = session()->has('message') ? session('message') : '';
        if ($tournament->location_us_state == 52) {
            $state_name = '';
        } else {
            $state_name = UsState::findorFail($tournament->location_us_state)->name;
        }
        return view('tournaments.view', compact('tournament', 'country_name', 'state_name', 'message'));
    }

    public function destroy($id) {
        //TODO: auth!!!
        Tournament::destroy($id);
        return redirect()->action('PagesController@my')->with('message', 'Tournament deleted.');
    }
}
