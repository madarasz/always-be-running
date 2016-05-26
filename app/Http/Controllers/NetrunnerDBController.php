<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use OAuth\Common\Http\Exception\TokenResponseException;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class NetrunnerDBController extends Controller
{

    protected $oauth;

    public function __construct()
    {
        $this->oauth = \OAuth2::consumer('NetrunnerDB', 'http://localhost:8000/oauth2/redirect');
    }

    function login(Request $request) {
        $code = $request->get('code');
        if ( ! is_null($code)) {
            $token = $this->oauth->requestAccessToken($code);
            $user = $this->getUser();
            if ($user > 0) {
                $auth_user = $this->findOrCreateUser($user);
                Auth::login($auth_user, true);
                return redirect()->action('PagesController@home');
            } else {
                return redirect()->action('PagesController@home')->with('message', 'You cannot login because you have no deck saved in ThronesDB. Please save a deck in ThronesDB first.');
            }
        } else {
            $url = $this->oauth->getAuthorizationUri();
            return redirect((string)$url);
        }
    }

    function logout() {
        Auth::logout();
        return redirect()->action('PagesController@home');
    }

    public function getDeckData() {
        $raw = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/private/decks'), true);
        $result = [];
        foreach ($raw['data'] as $deck) {
            array_push($result, ['id' => $deck['id'], 'name' => $deck['name']]);
        }
        return $result;
    }

    private function getUser() {
        try {
            $result = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/private/account/info'), true);
        } catch (TokenResponseException $e) {
            return -1;
        }
        return $result['data'][0];
    }

    private function findOrCreateUser($userData) {
        $user = User::find($userData['id']);
        if (is_null($user)) {
            User::create(['id' => $userData['id'], 'name' => $userData['username']]);
            $user = User::find($userData['id']);
        } else {
            $user->update(['id' => $userData['id'], 'name' => $userData['username']]);
        }
        return $user;
    }
}
