<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use OAuth\Common\Http\Exception\TokenResponseException;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class ThronesController extends Controller
{
    function login(Request $request) {
        $code = $request->get('code');
        $thrones = \OAuth2::consumer('Thrones', 'http://localhost:8000/oauth2/redirect');
        if ( ! is_null($code)) {
            $token = $thrones->requestAccessToken($code);
            $user_id = $this->getUserId($thrones);
            if ($user_id > 0) {
                $auth_user = $this->findOrCreateUser($user_id);
                Auth::login($auth_user, true);
                return redirect()->action('PagesController@home');
            } else {
                return redirect()->action('PagesController@home')->with('message', 'You cannot login because you have no deck saved in ThronesDB. Please save a deck in ThronesDB first.');
            }
        } else {
            $url = $thrones->getAuthorizationUri();
            return redirect((string)$url);
        }
    }

    function logout() {
        Auth::logout();
        return redirect()->action('PagesController@home');
    }

    private function getUserId($consumer) {
        try {
            $result = json_decode($consumer->request('https://thronesdb.com/api/oauth2/decks'), true);
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
