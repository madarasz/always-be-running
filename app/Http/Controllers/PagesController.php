<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home()
    {
        $people = ['a', 'b', 'c'];
        return view('welcome', compact('people'));
    }

    public function about()
    {
        $tournament_types = DB::table('tournament_types')->get();
        return view('about', compact('tournament_types'));
    }
}
