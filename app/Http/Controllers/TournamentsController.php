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
    public function store(Requests\TournamentRequest $request)
    {
        $request->sanitize_data();
        Tournament::create($request->all());
        return redirect()->action('PagesController@my')->with('message', 'Tournament created.');
    }

    public function index()
    {
        return view('home');    // TODO: page redirect
    }

    public function create()
    {
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        $tournament = new Tournament();
        $tournament->location_country = 0;
        return view('tournaments.create', compact('tournament_types', 'countries', 'us_states', 'tournament'));
    }

    public function edit($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament_types = TournamentType::lists('type_name', 'id')->all();
        $countries = Country::orderBy('name')->lists('name', 'id')->all();
        $us_states = UsState::orderBy('name')->lists('name', 'id')->all();
        return view('tournaments.edit', compact('tournament', 'id', 'tournament_types', 'countries', 'us_states'));
    }

    public function update($id, Requests\TournamentRequest $request)
    {
        $tournament = Tournament::findorFail($id);
        $request->sanitize_data();
        $tournament->update($request->all());
        return redirect()->action('PagesController@my')->with('message', 'Tournament updated.');
    }

    public function show($id)
    {
        $tournament = Tournament::findorFail($id);
        $country_name = Country::findorFail($tournament->location_country)->name;
        $message = session()->has('message') ? session('message') : '';
        if ($tournament->location_us_state == 52)
        {
            $state_name = '';
        } else
        {
            $state_name = UsState::findorFail($tournament->location_us_state)->name;
        }
        return view('tournaments.view', compact('tournament', 'country_name', 'state_name', 'message'));
    }

    public function destroy($id)
    {
        //TODO: auth!!!
        Tournament::destroy($id);
        return back()->with('message', 'Tournament deleted.');
    }
}
