<?php

namespace App\Http\Controllers;

use App\CardIdentity;
use Illuminate\Http\Request;
use App\User;
use App\Tournament;
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

    function getDeckData() {
        $raw = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/private/decks'), true);
        $result = [];
        foreach ($raw['data'] as $deck) {
            array_push($result, ['id' => $deck['id'], 'name' => $deck['name']]);
        }
        return $result;
    }

    function getUser() {
        $result = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/private/account/info'), true);
        return $result['data'][0];
    }

    function findOrCreateUser($userData) {
        $user = User::find($userData['id']);
        if (is_null($user)) {
            User::create(['id' => $userData['id'], 'name' => $userData['username'], 'sharing' => $userData['sharing']]);
            $user = User::find($userData['id']);
        } else {
            $user->update(['id' => $userData['id'], 'name' => $userData['username'], 'sharing' => $userData['sharing']]);
        }
        return $user;
    }

    function requestIdentities(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $added = $this->updateIdentities();
        return redirect()->action('AdminController@lister')->with('message', "$added new identities added.");
    }

    // not to be called from routes, no auth check, used directly by DB seeding
    function updateIdentities() {
        $raw = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/public/cards'), true);
        $added = 0;
        foreach ($raw['data'] as $card) {
            if ($card['type_code'] === 'identity') {
                $exists = CardIdentity::find($card['code']);
                if (is_null($exists)) {
                    $added++;
                    CardIdentity::create([
                        'id' => $card['code'],
                        'pack_code' => $card['pack_code'],
                        'faction_code' => $card['faction_code'],
                        'runner' => $card['side_code'] === 'runner',
                        'title' => $card['title']
                    ]);
                }
            }
        }
        return $added;
    }
}
