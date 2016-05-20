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
}
