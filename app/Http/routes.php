<?php

Route::get('/', 'PagesController@home');

Route::get('about', 'PagesController@about');

Route::get('my', 'PagesController@my');
Route::get('admin', 'AdminController@lister');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approve');
Route::get('tournaments/{id}/reject', 'AdminController@reject');