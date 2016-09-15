<?php

namespace App\Http\Controllers;

use App\CardCycle;
use App\CardIdentity;
use App\CardPack;
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
        $this->oauth = \OAuth2::consumer('NetrunnerDB', '/oauth2/redirect');
    }

    /**
     * Logs in user via OAuth
     * @param Request $request
     * @return redirects to home page
     */
    function login(Request $request)
    {
        $code = $request->get('code');
        if (!is_null($code))
        {
            $token = $this->oauth->requestAccessToken($code);
            $user = $this->getUser();
            if ($user > 0)
            {
                $auth_user = $this->findOrCreateUser($user);
                Auth::login($auth_user, true);
                return redirect()->action('PagesController@home');
            }
        } else
        {
            $url = $this->oauth->getAuthorizationUri();
            return redirect((string)$url);
        }
    }

    /**
     * Logs out user.
     * @return redirects to home page.
     */
    function logout()
    {
        Auth::logout();
        return redirect()->action('PagesController@home');
    }

    /**
     * Gets deck data of logged in user from Netrunner DB.
     * Includes public decklists.
     * Also includes private decks if user is set to sharing.
     * @return array
     */
    function getDeckData()
    {
        $result = ['public' => ['runner' => [], 'corp' => []], 'private' => ['runner' => [], 'corp' => []]];
        $runner_ids = CardIdentity::where('runner', 1)->get()->pluck('id')->all();
        $corp_ids = CardIdentity::where('runner', 0)->get()->pluck('id')->all();
        $public = json_decode($this->oauth->requestWrapper('https://netrunnerdb.com/api/2.0/private/decklists'), true);
        $this->sortDecks($public['data'], $result['public'], $runner_ids, $corp_ids);
        // TODO: enable if performance is ok
//        if (Auth::user()->sharing)
//        {
//            $private = json_decode($this->oauth->request('https://netrunnerdb.com/api/2.0/private/decks'), true);
//            $this->sortDecks($private['data'], $result['private'], $runner_ids, $corp_ids);
//        }
        return $result;
    }

    private function sortDecks(&$deckSource, &$target, &$runner_ids, &$corp_ids)
    {
        foreach ($deckSource as $deck)
        {
            $info = $this->classifyDeck($deck, $runner_ids, $corp_ids);
            $data = ['id' => $deck['id'], 'name' => $deck['name'],
                'identity' => $info['identity'], 'date_update' => $deck['date_update']];
            if ($info['side'] === 'runner' || is_null($info['side']))  // TODO: include null
            {
                array_push($target['runner'], $data);
            }
            if ($info['side'] === 'corp' || is_null($info['side'])) // TODO: include null
            {
                array_push($target['corp'], $data);
            }
        }
        usort($target['runner'], array($this, 'sortByDateUpdate'));
        usort($target['corp'], array($this, 'sortByDateUpdate'));
    }

    /**
     * Get user details from NetrunnerDB.
     * @return user details
     */
    function getUser()
    {
        $result = json_decode($this->oauth->requestWrapper('https://netrunnerdb.com/api/2.0/private/account/info'), true);
        return $result['data'][0];
    }

    /**
     * Tries to find user in User DB table. Creates or updates user data in DB.
     * @param $userData
     * @return user data
     */
    function findOrCreateUser($userData)
    {
        $user = User::find($userData['id']);
        if (is_null($user)) {
            User::create(['id' => $userData['id'], 'name' => $userData['username'], 'sharing' => $userData['sharing']]);
            $user = User::find($userData['id']);
        } else {
            $user->update(['id' => $userData['id'], 'name' => $userData['username'], 'sharing' => $userData['sharing']]);
        }
        return $user;
    }

    /**
     * Downloads cards which are identities from NetrunnerDB, stores them in DB.
     * @param Request $request
     * @return redirects to admin page
     */
    function requestIdentities(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $added = $this->updateIdentities();
        return redirect()->action('AdminController@lister')->with('message', "$added new identities added.");
    }

    /**
     * Downloads card cycles from NetrunnerDB, stores them in DB.
     * @param Request $request
     * @return redirects to admin page
     */
    function requestCycles(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $added = $this->updateCycles();
        return redirect()->action('AdminController@lister')->with('message', "$added new card cycles added.");
    }

    /**
     * Downloads card cycles from NetrunnerDB, stores them in DB.
     * @param Request $request
     * @return redirects to admin page
     */
    function requestPacks(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $added = $this->updatePacks();
        return redirect()->action('AdminController@lister')->with('message', "$added new card packs added.");
    }

    // not to be called from routes, no auth check, used directly by DB seeding
    private function updateIdentities()
    {
        $raw = json_decode($this->oauth->requestWrapper('https://netrunnerdb.com/api/2.0/public/cards'), true);
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

    // not to be called from routes, no auth check, used directly by DB seeding
    private function updateCycles()
    {
        $raw = json_decode($this->oauth->requestWrapper('https://netrunnerdb.com/api/2.0/public/cycles'), true);
        $added = 0;
        foreach ($raw['data'] as $cycle) {
            $exists = CardCycle::find($cycle['code']);
            if (is_null($exists)) {
                $added++;
                CardCycle::create([
                    'id' => $cycle['code'],
                    'name' => $cycle['name'],
                    'position' => $cycle['position']
                ]);
            }
        }
        return $added;
    }

    // not to be called from routes, no auth check, used directly by DB seeding
    private function updatePacks()
    {
        $raw = json_decode($this->oauth->requestWrapper('https://netrunnerdb.com/api/2.0/public/packs'), true);
        $added = 0;
        $nowdate = date('Y-m-d');
        foreach ($raw['data'] as $pack) {
            $exists = CardPack::find($pack['code']);
            if (is_null($exists)) {
                $added++;
                CardPack::create($this->packToArray($pack, $nowdate));
            } else {
                $exists->update($this->packToArray($pack, $nowdate));
            }
        }
        return $added;
    }

    private function packToArray($pack, $nowdate)
    {
        $cycle_position = CardCycle::find($pack['cycle_code'])->position;
        return [
            'id' => $pack['code'],
            'cycle_code' => $pack['cycle_code'],
            'position' => $pack['position'],
            'name' => $pack['name'],
            'date_release' => $pack['date_release'],
            'usable' => !is_null($pack['date_release']) && $pack['date_release'] <= $nowdate,
            'cycle_position' => $cycle_position
        ];
    }

    private function sortByDateUpdate($a, $b)
    {
        return $a['date_update'] < $b['date_update'];
    }

    private function classifyDeck($deck, $runner_ids, $corp_ids)
    {
        foreach ($deck['cards'] as $key => $card)
        {
            if (in_array($key, $runner_ids))
            {
                return ['side' => 'runner', 'identity' => $key];
            } elseif (in_array($key, $corp_ids))
            {
                return ['side' => 'corp', 'identity' => $key];
            }
        }
        return ['side' => null, 'identity' => null];
    }
}

