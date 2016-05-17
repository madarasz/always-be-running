<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\Request;
use DB;

use App\Http\Requests;

class AdminController extends Controller
{

    public function lister()
    {
        $user = 0;  // TODO
        $nowdate = date('Y.m.d.');
        $created = Tournament::where('approved', null)->where('deleted_at', null)->get();
        $deleted = Tournament::onlyTrashed()->get();
        $registered = [];
        $message = session()->has('message') ? session('message') : '';
        return view('admin', compact('user', 'created', 'deleted', 'nowdate', 'registered', 'message'));
    }

    public function approve($id)
    {
        $this->approval($id, 1, 'Tournament approved.');
    }

    public function reject($id)
    {
        $this->approval($id, 0, 'Tournament rejected.');
    }

    private function approval($id, $outcome, $message)
    {
        $tournament = Tournament::findorFail($id);
        $tournament->approved = $outcome;
        $tournament->save();
        redirect()->action('AdminController@lister')->with('message', $message);
    }

    public function restore($id)
    {
        Tournament::withTrashed()->where('id', $id)->restore();
        redirect()->action('AdminController@lister')->with('message', 'Tournament restored');
    }
}
