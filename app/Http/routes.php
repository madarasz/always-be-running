<?php

Route::get('/', 'PagesController@home');

Route::get('my', 'TournamentsController@my');
Route::get('admin', 'AdminController@lister');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approve');
Route::get('tournaments/{id}/reject', 'AdminController@reject');
Route::get('tournaments/{id}/restore', 'AdminController@restore');

Route::get('tournaments/{id}/register', 'EntriesController@register');
Route::get('tournaments/{id}/unregister', 'EntriesController@unregister');
Route::post('tournaments/{id}/claim', 'EntriesController@claim');
Route::get('tournaments/{id}/unclaim', 'EntriesController@unclaim');

Route::get('/oauth2/redirect', 'NetrunnerDBController@login');
Route::get('/logout', 'NetrunnerDBController@logout');
