<?php

namespace App\Http\Controllers;

use App\Country;
use App\Tournament;
use App\TournamentType;
use App\UsState;
use Illuminate\Http\Request;
use DB;

use App\Http\Requests;

class TournamentsController extends Controller
{
    public function store(Request $request) {
        $this->validate($request, [
            'title' => 'required',
            'date' => 'required|date_format:Y.m.d.',
            'location_city' => 'required',
            'players_number' => 'integer|between:1,1000',
            'top_number' => 'integer|between:0,1000',
            'location_country' => 'not_in:0',
        ], [
            'date_format' => 'Please enter the date using YYYY.MM.DD. format.',
            'not_in' => 'Please select a country.'
        ]);

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
            'players_number' => $request->concluded == 1 ? $request->players_number : 0,
            'top_number' => $request->concluded == 1 ? $request->top_number : 0,
            'description' => $request->description,
            'decklist' => $request->decklist == 1 ? 1 : 0,
            'creator' => 0 // TODO: creator

        ]);
        return $request->all(); // TODO: page redirect
    }

    public function index() {
        return view('home');    // TODO: page redirect
    }

    public function create()
    {
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        return view('tournaments.create', compact('tournament_types', 'countries', 'us_states'));
    }

    public function edit($id) {
        $tournament = Tournament::findOrFail($id);
        return view('tournaments.edit', compact('tournament'));  // TODO: page redirect
    }

    public function update(Request $request, Tournament $tournament) {
        $tournament->update($request->all());
    }
}
