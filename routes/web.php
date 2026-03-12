<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file contains all web routes for the application. These routes
| are loaded by the RouteServiceProvider within a group which contains
| the "web" middleware group.
|
*/

// Static pages
Route::get('/', 'PagesController@upcoming');
Route::get('results', 'PagesController@results');
Route::get('organize', 'PagesController@organize')->name('organize');
Route::get('prizes', 'PagesController@prizes');
Route::get('personal', 'PagesController@personal');
Route::get('profile/{id}', 'UserController@profile')->name('profile.show');
Route::post('profile/{id}', 'UserController@updateProfile');
Route::get('about', 'PagesController@about');
Route::get('faq', 'PagesController@faq');
Route::get('markdown', 'PagesController@markdown');
Route::get('badges', 'BadgeController@badges');
Route::get('support-me', 'PagesController@supportMe');
Route::get('thank-you', 'PagesController@thankYou');
Route::get('apidoc', 'PagesController@api');
Route::get('birthday', 'PagesController@birthdayFirst');
Route::get('privacy', 'PagesController@privacy');

// Admin
Route::get('admin', 'AdminController@lister')->name('admin');
Route::get('admin/identities/update', 'NetrunnerDBController@requestIdentities');
Route::get('admin/cycles/update', 'NetrunnerDBController@requestCycles');
Route::get('admin/packs/update', 'NetrunnerDBController@requestPacks');
Route::get('admin/mwl/update', 'NetrunnerDBController@requestMWL');
Route::post('admin/badges/refresh', 'BadgeController@refreshBadges');
Route::get('admin/badges/refresh/status/{runId}', 'BadgeController@refreshBadgesStatus');
Route::get('admin/entries/refresh', 'AdminController@setEntryTypes');
Route::get('admin/decks/broken', 'AdminController@detectBrokenDecks');
Route::get('admin/videos/broken', 'VideosController@scanForRemovedVideos');

// Tournaments
Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approveTournament');
Route::get('tournaments/{id}/reject', 'AdminController@rejectTournament');
Route::get('tournaments/{id}/restore', 'AdminController@restoreTournament');
Route::get('packs/{id}/enable', 'AdminController@enablePack');
Route::get('packs/{id}/disable', 'AdminController@disablePack');

Route::patch('tournaments/{id}/transfer', 'TournamentsController@transfer');
Route::get('tournaments/{id}/register', 'EntriesController@register');
Route::get('tournaments/{id}/unregister', 'EntriesController@unregister');
Route::post('tournaments/{id}/claim', 'EntriesController@claim');
Route::post('tournaments/{id}/claim-no-deck', 'EntriesController@claimWithoutDecks');
Route::delete('tournaments/{id}/purge', 'TournamentsController@purge');
Route::delete('entries/{id}', 'EntriesController@unclaim');
Route::delete('entries/anonym/{id}', 'EntriesController@deleteAnonym');
Route::post('entries/anonym', 'EntriesController@addAnonym');
Route::delete('tournaments/{id}/clearanonym', 'TournamentsController@clearAnonym');
Route::post('tournaments/{id}/conclude/manual', 'TournamentsController@concludeManual');
Route::post('tournaments/{id}/conclude/revert', 'TournamentsController@revertConclusion');
Route::post('tournaments/{id}/relax/{relax}', 'TournamentsController@relaxTournament');
Route::post('tournaments/{id}/conclude/nrtm', 'TournamentsController@concludeNRTM');
Route::get('tournaments/{id}/toggle-featured', 'AdminController@toggleFeatured');
Route::get('tournaments/{id}/{slug}', 'TournamentsController@show')->name('tournaments.show.slug');

// Calendar
Route::get('calendar/event/{id}', 'CalendarController@getEventCalendar');
Route::get('calendar/user/{secret_id}', 'CalendarController@getUserCalendar');

// Videos (web form submissions)
Route::post('videos', 'VideosController@store');
Route::delete('videos/{id}', 'VideosController@destroy');
Route::post('videos/{id}/tag', 'VideosController@storeTag');
Route::get('videotags/delete/{id}', 'VideosController@destroyTag');
Route::get('videos', 'VideosController@page');

// Photos (web form submissions)
Route::post('photos', 'PhotosController@store');
Route::get('photos/{id}/approve', 'PhotosController@approve');
Route::get('photos/{id}/approve-all', 'PhotosController@approveAll');
Route::get('photos/{id}/rotate/{dir}', 'PhotosController@rotate');
Route::delete('photos/{id}', 'PhotosController@destroy');

// OAuth
Route::get('/oauth2/redirect', 'NetrunnerDBController@login');
Route::get('/logout', 'NetrunnerDBController@logout');

// Elimination bracket (iframe)
Route::get('/elimination', 'PagesController@elimination');
