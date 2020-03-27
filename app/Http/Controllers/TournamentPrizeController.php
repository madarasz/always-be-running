<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\TournamentPrize;
use App\Tournament;

class TournamentPrizeController extends Controller
{
    /**
     * Display a listing of unofficial prizes for tournament.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $prizes = TournamentPrize::where('tournament_id', $id)->get();
        return response()->json($prizes);
    }

    /**
     * Store a newly created unofficial prize for a tournament.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $tournament = Tournament::find($id);
        $this->auth($request, $tournament);

        $prize = TournamentPrize::create([
            'tournament_id' => $id,
            'prize_element_id' => $request->input('prize_element_id'),
            'quantity' => $request->input('quantity')
        ]);
        return response()->json($prize);
    }

    /**
     * Remove the specified unofficial prize for a tournament.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $prize = TournamentPrize::findOrFail($id);
        $tournament = Tournament::find($prize->tournament_id);
        $this->auth($request, $tournament);

        TournamentPrize::destroy($id);
        return response()->json('Unofficial prize deleted from tournament');
    }

    // authorize action
    private function auth(Request $request, $tournament) {
        if (is_null($tournament)) {
            // tournament is being created
            $this->authorize('logged_in', Tournament::class, $request->user());
        } else {
            // tournament is being edited
            $this->authorize('own', $tournament, $request->user());
        }
    }
}
