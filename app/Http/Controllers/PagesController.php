<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tournament;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home()
    {
        $message = session()->has('message') ? session('message') : '';
        return view('home', compact('message'));
    }

    public function my(Request $request)
    {
        $this->authorize('list_my', Tournament::class, $request->user());
        $user = $request->user()->id;
        $nowdate = date('Y.m.d.');
        $created = Tournament::where('creator', $user)->where('deleted_at', null)->get();
        $registered = [];
        $message = session()->has('message') ? session('message') : '';
        return view('my', compact('user', 'created', 'nowdate', 'registered', 'message'));
    }
}
