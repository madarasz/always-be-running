<?php
use Illuminate\Http\Request;


Route::get('/', 'PagesController@home');

Route::get('my', 'TournamentsController@my');
Route::get('admin', 'AdminController@lister');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approve');
Route::get('tournaments/{id}/reject', 'AdminController@reject');
Route::get('tournaments/{id}/restore', 'AdminController@restore');

Route::get('tournaments/{id}/register', 'EntriesController@register');
Route::get('tournaments/{id}/unregister', 'EntriesController@unregister');

Route::get('/oauth2/redirect', 'ThronesController@login');
Route::get('/logout', 'ThronesController@logout');


Route::get('/try', 'ThronesController@getDeckData');