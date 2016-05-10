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

    public function create()
    {
        $tournament_types = DB::table('tournament_types')->get();
        $countries = DB::table('countries')->orderBy('name')->get();
        $us_states = DB::table('us_states')->orderBy('name')->get();
        return view('create', compact('tournament_types', 'countries', 'us_states'));
    }
}
