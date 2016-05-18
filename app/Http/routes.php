<?php
use Illuminate\Http\Request;


Route::get('/', 'PagesController@home');

Route::get('my', 'PagesController@my');
Route::get('admin', 'AdminController@lister');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approve');
Route::get('tournaments/{id}/reject', 'AdminController@reject');
Route::get('tournaments/{id}/restore', 'AdminController@restore');

Route::get('/oauth2/redirect', 'ThronesController@login');
Route::get('/logout', 'ThronesController@logout');
