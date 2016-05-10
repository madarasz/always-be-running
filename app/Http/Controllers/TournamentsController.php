<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\Request;
use DB;

use App\Http\Requests;

class TournamentsController extends Controller
{
    public function create(Request $request) {
        $this->validate($request, [
            'title' => 'required',
            'date' => 'required|date_format:Y.m.d.',
            'location_city' => 'required',
            'players_number' => 'integer|between:1,1000',
            'top_number' => 'integer|between:0,1000',
            'location_country' => 'not_in:0',
        ], [
            'date_format' => 'Please enter the date using YYYY.mm.dd. format.',
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
            'concluded' => $request->concluded === '' ? 1 : 0,
            'players_number' => $request->concluded === '' ? $request->has('players_number') : 0,
            'top_number' => $request->concluded === '' ? $request->has('top_number') : 0,
            'description' => $request->description,
            'decklist' => $request->decklist === '' ? 1 : 0,
            'creator' => 0 // TODO: creator

        ]);
        return $request->all(); // TODO: page redirect
    }

    public function edit(Tournament $tournament) {
        return view('meh', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament) {
        $tournament->update($request->all());
    }
}
