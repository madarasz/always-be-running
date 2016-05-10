<?php

Route::get('/', 'PagesController@home');

Route::get('about', 'PagesController@about');

Route::get('create', 'PagesController@create');

Route::post('tournaments', 'TournamentsController@create');

Route::get('tournaments/{tournament}/edit', 'TournamentsController@edit');
Route::patch('tournaments/{tournament}', 'TournamentsController@update');
