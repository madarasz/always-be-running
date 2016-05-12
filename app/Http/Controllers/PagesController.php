<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function about()
    {
        $tournament_types = DB::table('tournament_types')->get();
        return view('about', compact('tournament_types'));
    }

    public function my()
    {
        $user = 0;  // TODO
        $nowdate = date('Y.m.d.');
        $created = DB::table('tournaments')->where('creator', $user)->where('deleted_at', null)->get();
        $registered = [];
        $message = session()->has('message') ? session('message') : '';
        return view('my', compact('user', 'created', 'nowdate', 'registered', 'message'));
    }
}
