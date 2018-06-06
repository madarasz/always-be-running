<?php

Route::get('/', 'PagesController@upcoming');
Route::get('results', 'PagesController@results');
Route::get('organize', 'PagesController@organize')->name('organize');
Route::get('prizes', 'PagesController@prizes');
Route::get('personal', 'PagesController@personal');
Route::get('profile/{id}', 'PagesController@profile')->name('profile.show');
Route::post('profile/{id}', 'PagesController@updateProfile');
Route::get('about', 'PagesController@about');
Route::get('faq', 'PagesController@faq');
Route::get('markdown', 'PagesController@markdown');
Route::get('badges', 'BadgeController@badges');
Route::get('support-me', 'PagesController@supportMe');
Route::get('thank-you', 'PagesController@thankYou');
Route::get('apidoc', 'PagesController@api');
Route::get('birthday', 'PagesController@birthdayFirst');
Route::get('privacy', 'PagesController@privacy');

Route::get('admin', 'AdminController@lister')->name('admin');
Route::get('admin/identities/update', 'NetrunnerDBController@requestIdentities');
Route::get('admin/cycles/update', 'NetrunnerDBController@requestCycles');
Route::get('admin/packs/update', 'NetrunnerDBController@requestPacks');
Route::get('admin/badges/refresh', 'BadgeController@refreshBadges');
Route::get('admin/entries/refresh', 'AdminController@setEntryTypes');
Route::get('admin/decks/broken', 'AdminController@detectBrokenDecks');
Route::get('admin/videos/broken', 'VideosController@scanForRemovedVideos');

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

Route::get('calendar/event/{id}', 'CalendarController@getEventCalendar');
Route::get('calendar/user/{secret_id}', 'CalendarController@getUserCalendar');

Route::post('videos', 'VideosController@store');
Route::delete('videos/{id}', 'VideosController@destroy');
Route::post('videos/{id}/tag', 'VideosController@storeTag');
Route::get('videotags/delete/{id}', 'VideosController@destroyTag');
Route::get('videos', 'VideosController@page');

Route::post('photos', 'PhotosController@store');
Route::get('photos/{id}/approve', 'PhotosController@approve');
Route::get('photos/{id}/approve-all', 'PhotosController@approveAll');
Route::get('photos/{id}/rotate/{dir}', 'PhotosController@rotate');
Route::delete('photos/{id}', 'PhotosController@destroy');
Route::post('api/photos', 'PhotosController@storeApi');
Route::delete('api/photos/{id}', 'PhotosController@destroyApi');

Route::get('/api/tournament-groups', 'TournamentGroupController@getTournamentGroups');
Route::get('/api/tournament-groups/{id}', 'TournamentGroupController@getTournamentGroupDetails');
Route::post('/api/tournament-groups', 'TournamentGroupController@createTournamentGroup');
Route::delete('/api/tournament-groups/{id}', 'TournamentGroupController@deleteTournamentGroup');
Route::put('/api/tournament-groups/{id}', 'TournamentGroupController@editTournamentGroup');
Route::post('/api/tournament-groups/{groupId}/link/{tournamentId}', 'TournamentGroupController@linkTournamentToGroup');
Route::post('/api/tournament-groups/{groupId}/unlink/{tournamentId}', 'TournamentGroupController@unlinkTournamentToGroup');

Route::get('/api/prizes', 'PrizeController@getPrizeKits');
Route::post('/api/prizes', 'PrizeController@createPrizeKit');
Route::delete('/api/prizes/{id}', 'PrizeController@deletePrizeKit');
Route::put('/api/prizes/{id}', 'PrizeController@editPrizeKit');
Route::post('/api/prize-items', 'PrizeController@createPrizeItem');
Route::delete('/api/prize-items/{id}', 'PrizeController@deletePrizeItem');
Route::put('/api/prize-items/{id}', 'PrizeController@editPrizeItem');

Route::get('/api/prize-collections/{id}', 'PrizeCollectionController@get');
Route::put('/api/prize-collections/{id}', 'PrizeCollectionController@update');

Route::get('/oauth2/redirect', 'NetrunnerDBController@login');
Route::get('/logout', 'NetrunnerDBController@logout');

Route::get('/api/tournaments/upcoming', 'TournamentsController@upcomingTournamentJSON');
Route::get('/api/tournaments/results', 'TournamentsController@resultTournamentJSON');
Route::get('/api/tournaments', 'TournamentsController@tournamentJSON'); // for internal use
Route::get('/api/tournaments/brief', 'TournamentsController@briefTournamentJSON'); // for internal use, list tournaments for groups

Route::get('/api/userdecks', 'NetrunnerDBController@getUserDecksJSON');
Route::get('/api/entries', 'EntriesController@entriesJSON');
Route::post('/api/nrtm', 'TournamentsController@NRTMEndpoint');
Route::get('/api/useralert', 'PagesController@getAlertCount');
Route::get('/api/as/{id}', 'ASController@index');
Route::post('/api/badgesseen/{id}', 'BadgeController@changeBadgesToSeen');
Route::get('/api/adminstats', 'AdminController@adminStats');
Route::get('/api/adminstats/{country}', 'AdminController@adminStatsPerCountry');
Route::get('/api/getdeckdata', 'NetrunnerDBController@getDeckData');
Route::get('/api/fb/event-title', 'FBController@getEventTitle');
Route::post('/api/fb/import', 'FBController@importViaFB');
Route::get('/api/videos', 'VideosController@lister');
Route::get('/api/country-mapping', 'PagesController@CountryToCodeMapping');

// iframe for double elimination
Route::get('/elimination', function () { return view('layout.bracket'); });
// https proxies for loading KnowTheMeta data
Route::get('/api/ktmproxy/cardpoolnames', 'KTMProxy@getCardpoolNames');
Route::get('/api/ktmproxy/cardpool/{side}/{pack}', 'KTMProxy@getCardpoolStat');
