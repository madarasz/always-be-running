<?php

Route::get('/', 'PagesController@home');

Route::get('about', 'PagesController@about');

Route::get('my', 'PagesController@my');

Route::resource('tournaments', 'TournamentsController');