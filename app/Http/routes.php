<?php

Route::get('/', 'PagesController@home');

Route::get('discover', 'PagesController@discover');
Route::get('results', 'PagesController@results');
Route::get('organize', 'PagesController@organize');
Route::get('personal', 'PagesController@personal');
Route::get('admin', 'AdminController@lister');
Route::get('admin/identities/update', 'NetrunnerDBController@requestIdentities');
Route::get('admin/cycles/update', 'NetrunnerDBController@requestCycles');
Route::get('admin/packs/update', 'NetrunnerDBController@requestPacks');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approveTournament');
Route::get('tournaments/{id}/reject', 'AdminController@rejectTournament');
Route::get('tournaments/{id}/restore', 'AdminController@restoreTournament');
Route::get('packs/{id}/enable', 'AdminController@enablePack');
Route::get('packs/{id}/disable', 'AdminController@disablePack');

Route::get('tournaments/{id}/register', 'EntriesController@register');
Route::get('tournaments/{id}/unregister', 'EntriesController@unregister');
Route::post('tournaments/{id}/claim', 'EntriesController@claim');
Route::delete('entries/{id}', 'EntriesController@unclaim');

Route::get('/oauth2/redirect', 'NetrunnerDBController@login');
Route::get('/logout', 'NetrunnerDBController@logout');

Route::get('/api/tournaments', 'TournamentsController@tournamentJSON');