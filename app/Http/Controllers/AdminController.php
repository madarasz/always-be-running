<?php

namespace App\Http\Controllers;

use App\CardCycle;
use App\CardIdentity;
use App\CardPack;
use App\Tournament;
use Illuminate\Http\Request;

use App\Http\Requests;

class AdminController extends Controller
{

    public function lister(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $nowdate = date('Y.m.d.');
        $to_approve = Tournament::where(function($query) {
                $query->where('approved', 0)->orWhereNull('approved');
            })->where('deleted_at', null)->get();
        $deleted = Tournament::onlyTrashed()->get();
        $message = session()->has('message') ? session('message') : '';
        $cycles = CardCycle::orderBy('position', 'desc')->get();
        $packs = [];
        foreach ($cycles as $cycle) {
            array_push($packs, CardPack::where('cycle_code', $cycle->id)->orderBy('position', 'desc')->get());
        }
        $count_ids = CardIdentity::count();
        $last_id = $count_ids > 0 ? CardIdentity::orderBy('id', 'desc')->first()->title : '';
        $count_cycles = count($cycles);
        $last_cycle = $count_cycles > 1 ? $cycles[1]->name : '';
        $count_packs = CardPack::count();
        // determine last pack name, $pack[0] is 'draft'
        if ($count_packs > 1 && $count_cycles > 1) {
            if (count($packs[1])) {
                $last_pack = $packs[1][0]->name;
            } else {
                $last_pack = $packs[2][0]->name;
            }
        } else {
            $last_pack = '';
        }

        $page_section = 'admin';
        return view('admin', compact('user', 'to_approve', 'deleted', 'nowdate', 'message',
            'count_ids', 'last_id', 'count_packs', 'last_pack', 'count_cycles', 'last_cycle', 'packs', 'cycles', 'page_section'));
    }

    public function approveTournament($id, Request $request)
    {
        return $this->approval($id, 1, 'Tournament approved.', $request);
    }

    public function rejectTournament($id, Request $request)
    {
        return $this->approval($id, 0, 'Tournament rejected.', $request);
    }

    private function approval($id, $outcome, $message, $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $tournament = Tournament::findorFail($id);
        $tournament->approved = $outcome;
        $tournament->save();
        // update badges
        if ($outcome) {
            App('App\Http\Controllers\BadgeController')->addTOBadges($tournament->creator);
        } else {
            App('App\Http\Controllers\BadgeController')->refreshTOBadges($tournament->creator);
        }

        return back()->with('message', $message);
    }

    public function restoreTournament($id, Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        Tournament::withTrashed()->where('id', $id)->restore();
        return back()->with('message', 'Tournament restored');
    }

    public function enablePack($id, Request $request) {
        return $this->changePackUsage($id, 1, 'Card pack enabled.', $request);
    }

    public function disablePack($id, Request $request) {
        return $this->changePackUsage($id, 0, 'Card pack disabled.', $request);
    }

    private function changePackUsage($id, $outcome, $message, $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $pack = CardPack::findorFail($id);
        $pack->usable = $outcome;
        $pack->save();
        return back()->with('message', $message);
    }
}
