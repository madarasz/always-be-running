<?php

namespace App\Http\Controllers;

use App\CardIdentity;
use App\Tournament;
use Illuminate\Http\Request;

use App\Http\Requests;

class AdminController extends Controller
{

    public function lister(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $nowdate = date('Y.m.d.');
        $to_approve = Tournament::where('approved', null)->where('deleted_at', null)->get();
        $deleted = Tournament::onlyTrashed()->get();
        $message = session()->has('message') ? session('message') : '';
        $count_ids = CardIdentity::count();
        $last_id = CardIdentity::orderBy('id', 'desc')->first()->title;
        return view('admin', compact('user', 'to_approve', 'deleted', 'nowdate', 'message', 'count_ids', 'last_id'));
    }

    public function approve($id, Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        return $this->approval($id, 1, 'Tournament approved.');
    }

    public function reject($id, Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        return $this->approval($id, 0, 'Tournament rejected.');
    }

    private function approval($id, $outcome, $message)
    {
        $tournament = Tournament::findorFail($id);
        $tournament->approved = $outcome;
        $tournament->save();
        return back()->with('message', $message);
    }

    public function restore($id, Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        Tournament::withTrashed()->where('id', $id)->restore();
        return back()->with('message', 'Tournament restored');
    }
}
