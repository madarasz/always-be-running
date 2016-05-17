<?php
use Illuminate\Http\Request;


Route::get('/', 'PagesController@home');

Route::get('about', 'PagesController@about');

Route::get('my', 'PagesController@my');
Route::get('admin', 'AdminController@lister');

Route::resource('tournaments', 'TournamentsController');
Route::get('tournaments/{id}/approve', 'AdminController@approve');
Route::get('tournaments/{id}/reject', 'AdminController@reject');
Route::get('tournaments/{id}/restore', 'AdminController@restore');

Route::get('/oauth2/redirect', function(Request $request) {
    $code = $request->get('code');
    $thrones = \OAuth2::consumer('Thrones', 'http://localhost:8000/oauth2/redirect');
    if ( ! is_null($code)) {
        $token = $thrones->requestAccessToken($code);
        // Send a request with it
        $result = json_decode($thrones->request('https://thronesdb.com/api/oauth2/decks'), true);
        dd($result);

    } else {
        // get fb authorization
        $url = $thrones->getAuthorizationUri();

        // return to facebook login url
        return redirect((string)$url);
    }
});
