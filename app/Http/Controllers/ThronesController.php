<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use OAuth\Common\Http\Exception\TokenResponseException;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class ThronesController extends Controller
{

    protected $oauth;

    public function __construct()
    {
        $this->oauth = \OAuth2::consumer('Thrones', 'http://localhost:8000/oauth2/redirect');
    }

    function login(Request $request) {
        $code = $request->get('code');
//        $oauth = \OAuth2::consumer('Thrones', 'http://localhost:8000/oauth2/redirect');
        if ( ! is_null($code)) {
            $token = $this->oauth->requestAccessToken($code);
            $user_id = $this->getUserId();
            if ($user_id > 0) {
                $auth_user = $this->findOrCreateUser($user_id);
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
        $raw = json_decode($this->oauth->request('https://thronesdb.com/api/oauth2/decks'), true);
        $result = [];
        foreach ($raw as $deck) {
            array_push($result, ['id' => $deck['id'], 'name' => $deck['name']]);
        }
        return $result;
    }

    private function getUserId() {
        try {
            $result = json_decode($this->oauth->request('https://thronesdb.com/api/oauth2/decks'), true);
        } catch (TokenResponseException $e) {
            return -1;
        }
        return $result[0]['user_id'];
    }

    private function findOrCreateUser($id) {
        $user = User::find($id);
        if (is_null($user)) {
            User::create(['id' => $id]);
            $user = User::find($id);
        }
        return $user;
    }
}
