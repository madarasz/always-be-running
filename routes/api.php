<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file contains all API routes for the application. These routes
| are loaded by the RouteServiceProvider within a group which assigns
| the "web" middleware group (for session-based auth) and "/api" prefix.
|
*/

// Photos API
Route::post('photos', 'PhotosController@storeApi');
Route::delete('photos/{id}', 'PhotosController@destroyApi');
Route::put('photos/{id}', 'PhotosController@update');

// Tournament Groups
Route::get('tournament-groups', 'TournamentGroupController@getTournamentGroups');
Route::get('tournament-groups/{id}', 'TournamentGroupController@getTournamentGroupDetails');
Route::post('tournament-groups', 'TournamentGroupController@createTournamentGroup');
Route::delete('tournament-groups/{id}', 'TournamentGroupController@deleteTournamentGroup');
Route::put('tournament-groups/{id}', 'TournamentGroupController@editTournamentGroup');
Route::post('tournament-groups/{groupId}/link/{tournamentId}', 'TournamentGroupController@linkTournamentToGroup');
Route::post('tournament-groups/{groupId}/unlink/{tournamentId}', 'TournamentGroupController@unlinkTournamentToGroup');

// Prizes
Route::get('prizes', 'PrizeController@getPrizeKits');
Route::post('prizes', 'PrizeController@createPrizeKit');
Route::delete('prizes/{id}', 'PrizeController@deletePrizeKit');
Route::put('prizes/{id}', 'PrizeController@editPrizeKit');
Route::post('prize-items', 'PrizeController@createPrizeItem');
Route::delete('prize-items/{id}', 'PrizeController@deletePrizeItem');
Route::put('prize-items/{id}', 'PrizeController@editPrizeItem');

// Unofficial Prizes
Route::get('tournaments/{id}/unofficial-prizes', 'TournamentPrizeController@index');
Route::post('unofficial-prizes/{id}', 'TournamentPrizeController@store');
Route::delete('unofficial-prizes/{id}', 'TournamentPrizeController@destroy');

// Artists
Route::get('artists', 'ArtistController@getArtists');
Route::get('artists/{id}', 'ArtistController@getArtistDetails');
Route::post('artists', 'ArtistController@createArtist');
Route::put('artists/{id}', 'ArtistController@editArtist');
Route::delete('artists/{id}', 'ArtistController@deleteArtist');
Route::post('artists/register', 'UserController@registerAsArtist');
Route::post('artists/unregister', 'UserController@unregisterAsArtist');

// Prize Collections
Route::get('prize-collections/{id}', 'PrizeCollectionController@get');
Route::put('prize-collections/{id}', 'PrizeCollectionController@update');

// Tournaments API
Route::get('tournaments/upcoming', 'TournamentsController@upcomingTournamentJSON');
Route::get('tournaments/results', 'TournamentsController@resultTournamentJSON');
Route::get('tournaments', 'TournamentsController@tournamentJSON');
Route::get('tournaments/brief', 'TournamentsController@briefTournamentJSON');

// User & Entries
Route::get('userdecks', 'NetrunnerDBController@getUserDecksJSON');
Route::get('entries', 'EntriesController@entriesJSON');
Route::post('nrtm', 'TournamentsController@NRTMEndpoint');
Route::get('useralert', 'UserController@getAlertCount');
Route::get('as/{id}', 'ASController@index');
Route::post('badgesseen/{id}', 'BadgeController@changeBadgesToSeen');

// Admin Stats
Route::get('adminstats', 'AdminController@adminStats');
Route::get('adminstats/{country}', 'AdminController@adminStatsPerCountry');

// External Data
Route::get('getdeckdata', 'NetrunnerDBController@getDeckData');
Route::get('fb/event-title', 'FBController@getEventTitle');
Route::post('fb/import', 'FBController@importViaFB');
Route::get('videos', 'VideosController@lister');
Route::get('country-mapping', 'PagesController@CountryToCodeMapping');

// KTM Proxy (https proxies for loading KnowTheMeta data)
Route::get('ktmproxy/cardpoolnames', 'KTMProxy@getCardpoolNames');
Route::get('ktmproxy/cardpool/{side}/{pack}', 'KTMProxy@getCardpoolStat');
