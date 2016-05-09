<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', function () {
//    $people = ['a', 'b', 'c'];
//    return view('welcome', compact('people'));
//});

Route::get('/', 'PagesController@home');

Route::get('about', 'PagesController@about');

Route::get('create', 'PagesController@create');
