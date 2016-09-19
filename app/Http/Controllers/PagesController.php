<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tournament;
use App\TournamentType;
//use App\Entry;
use App\CardPack;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home()
    {
        $message = session()->has('message') ? session('message') : '';
        return view('home', compact('message'));
    }

    public function upcoming()
    {
        $nowdate = date('Y.m.d.', time() - 86400); // actually yesterday, to be on the safe side
        $tournaments = Tournament::where('date', '>=', $nowdate)->where('approved', 1)->whereNull('deleted_at');
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $countries = $tournaments->pluck('location_country')->unique()->all();
        $states = $tournaments->pluck('location_state')->unique()->all();
        if(($states_key = array_search('', $states)) !== false) {
            unset($states[$states_key]);
        }
        $countries = array_values($countries);
        $states = array_values($states);
        $message = session()->has('message') ? session('message') : '';
        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $countries = [-1 => '---'] + $countries;
        $states = [-1 => '---'] + $states;
        return view('upcoming', compact('message', 'nowdate', 'tournament_types', 'countries', 'states'));
    }

    public function results(Request $request)
    {
//        $entries = Entry::where('user', $user)->orderBy('updated_at', 'desc')->get();
//        $registered = [];
//        foreach ($entries as $entry)
//        {
//            $stuff = $entry->tournament;
//            if ($stuff && $stuff->approved !== 0)
//            {
//                $stuff['claim'] = $entry->rank > 0;
//                array_push($registered, $stuff);
//            }
//        }
        $nowdate = date('Y.m.d.', time() + 86400); // actually tomorrow, to be on the safe side
        $tournaments = Tournament::where('date', '<=', $nowdate)->where('approved', 1)->where('concluded',1)->whereNull('deleted_at');
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $countries = $tournaments->pluck('location_country')->unique()->all();
        $tournament_cardpools = CardPack::whereIn('id', $tournaments->pluck('cardpool_id')->unique()->all())->pluck('name', 'id')->all();;
        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $countries = [-1 => '---'] + $countries;
        $tournament_cardpools = [-1 => '---'] + $tournament_cardpools;
        $message = session()->has('message') ? session('message') : '';
        return view('results', compact('registered', 'message', 'nowdate', 'tournament_types', 'countries', 'tournament_cardpools'));
    }

    /**
     * Show organize tournamnets page.
     * @param Request $request
     * @return view
     */
    public function organize(Request $request)
    {
        if (is_null($request->user())) {
            return view('loginreq');
        }
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user = $request->user()->id;
        $message = session()->has('message') ? session('message') : '';
        return view('organize', compact('user', 'message'));
    }

    public function personal(Request $request)
    {
        if (is_null($request->user())) {
            return view('loginreq');
        }
        $message = session()->has('message') ? session('message') : '';
        $user = $request->user()->id;
        $username = $request->user()->name;
        return view('personal', compact('message', 'user', 'username'));
    }
}
