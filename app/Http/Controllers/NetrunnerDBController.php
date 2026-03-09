<?php

namespace App\Http\Controllers;

use App\CardCycle;
use App\CardIdentity;
use App\CardPack;
use Illuminate\Http\Request;
use App\User;
use App\Tournament;
use App\Http\Requests;
use App\Mwl;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class NetrunnerDBController extends Controller
{

    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    /**
     * Logs in user via OAuth
     * @param Request $request
     * @return redirects to home page
     */
    function login(Request $request)
    {
        if ($request->filled('code')) {
            try {
                $socialiteUser = Socialite::driver('netrunnerdb')->user();
                $this->storeTokens($socialiteUser);
            } catch (\Exception $e) {
                \Log::warning('NetrunnerDB OAuth callback failed: '.$e->getMessage());

                return redirect()->action('PagesController@upcoming')
                    ->withErrors(['Authentication with NetrunnerDB failed.']);
            }

            $userData = $socialiteUser->user;
            if (!is_array($userData)) {
                $userData = [];
            }

            if (!array_key_exists('id', $userData)) {
                $userData['id'] = $socialiteUser->getId();
            }

            if (!array_key_exists('username', $userData) || empty($userData['username'])) {
                $userData['username'] = $socialiteUser->getNickname() ?: $socialiteUser->getName();
            }

            if (!array_key_exists('email', $userData) || empty($userData['email'])) {
                $userData['email'] = $socialiteUser->getEmail();
            }

            if (!array_key_exists('sharing', $userData)) {
                $userData['sharing'] = 0;
            }

            if (!array_key_exists('reputation', $userData)) {
                $userData['reputation'] = 0;
            }

            if (!array_key_exists('id', $userData) || empty($userData['id'])) {
                return redirect()->action('PagesController@upcoming')
                    ->withErrors(['Authentication with NetrunnerDB returned invalid user data.']);
            }

            $auth_user = $this->findOrCreateUser($userData);
            Auth::login($auth_user, true);

            // redirect back to the original page
            $login_url = $request->cookie('login_url');
            if (is_null($login_url)) {
                return redirect()->action('PagesController@upcoming');
            }

            return redirect($login_url)->with('getdeckdata', 1);
        }

        return Socialite::driver('netrunnerdb')->redirect()
            ->withCookie(cookie('login_url', $request->headers->get('referer'), 10));
    }

    /**
     * Logs out user.
     * @return redirects to home page.
     */
    function logout(Request $request)
    {
        Auth::logout();
        $this->clearTokens();

        // redirect back, if possible
        $logout_url = $request->headers->get('referer');
        if (strpos($logout_url, 'edit') || strpos($logout_url, 'admin')) {
            return redirect()->action('PagesController@upcoming');
        } else {
            return back();
        }
    }

    /**
     * Gets deck data of logged in user from Netrunner DB.
     * Includes public decklists.
     * Also includes private decks if user is set to sharing.
     * @return array
     */
    public function getDeckData()
    {
        $result = ['publicNetrunnerDB' => ['runner' => [], 'corp' => []], 'privateNetrunnerDB' => ['runner' => [], 'corp' => []]];
        $runner_ids = CardIdentity::where('runner', 1)->get()->pluck('id')->all();
        $corp_ids = CardIdentity::where('runner', 0)->get()->pluck('id')->all();
        $public = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/private/decklists'), true);

        // error handling
        if (!is_array($public) || array_key_exists('error', $public) || !array_key_exists('data', $public)) {
            return ['error' => 'NetrunnerDB session lost'];
        }

        $this->sortDecks($public['data'], $result['publicNetrunnerDB'], $runner_ids, $corp_ids);
        // private deck data
        if (Auth::user() && Auth::user()->sharing)
        {
            $private = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/private/decks'), true);
            if (is_array($private) && array_key_exists('data', $private)) {
                $this->sortDecks($private['data'], $result['privateNetrunnerDB'], $runner_ids, $corp_ids);
            }
        }

        // update user with deck counts
        $countPublicDecks = count($result['publicNetrunnerDB']['runner']) + count($result['publicNetrunnerDB']['corp']);
        $countPrivateDecks = count($result['privateNetrunnerDB']['runner']) + count($result['privateNetrunnerDB']['corp']);
        Auth::user()->update([
            'published_decks' => $countPublicDecks,
            'private_decks' => $countPrivateDecks
        ]);
        // badges
        App('App\Http\Controllers\BadgeController')->addNDBBadges(Auth::user()->id);

        return $result;
    }

    /**
     * Check response code of URL.
     * @param $url
     * @return string
     */
    function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    /**
     * Gets deck info from NetrunnerDB.
     * @param $deckid
     * @return deck info
     */
    public function getDeckInfo($deckid) {
        $URL = 'https://netrunnerdb.com/api/2.0/public/decklist/';

        $response_code = $this->get_http_response_code($URL.$deckid);
        if ($response_code != "200") {
            return ['error' => 'wrong response code: '.$response_code, 'side' => ''];
        }

        // query deck
        $response = json_decode(file_get_contents($URL.$deckid), true);
        $runner_ids = CardIdentity::where('runner', 1)->get()->pluck('id')->all();
        $corp_ids = CardIdentity::where('runner', 0)->get()->pluck('id')->all();
        $info = $this->classifyDeck($response['data'][0], $runner_ids, $corp_ids);
        return ['id' => $deckid, 'identity' => $info['identity'], 'side' => $info['side'],
            'title' => $response['data'][0]['name'], 'type' => 1];
    }

    /**
     * Checks if deck is broken (404).
     * @param $published
     * @param $deckid
     * @return bool
     */
    public function isDeckLinkBroken($published, $deckid) {
        if ($published) {
            $URL = 'https://netrunnerdb.com/api/2.0/public/decklist/';
        } else {
            $URL = 'https://netrunnerdb.com/api/2.0/public/deck/';
        }

        $response_code = $this->get_http_response_code($URL.$deckid);

        return ($response_code != "200");
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
     * JSON API for the list of user's decks.
     * It is a proxy that the front-end calls, because OAuth tokens should be handled by the backend.
     * Currently returns NetrunnerDB decks: user's published decks and private decks if shared.
     * @param Request $request
     */
    public function getUserDecksJSON(Request $request) {
        if (Auth::user()) {
            return response()->json($this->getDeckData());
        } else {
            return response()->json(['error' => 'User is not logged in']);
        }
    }

    /**
     * Get user details from NetrunnerDB.
     * @return user details
     */
    function getUser()
    {
        $result = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/private/account/info'), true);

        if (is_array($result) && array_key_exists('data', $result) && array_key_exists(0, $result['data'])) {
            return $result['data'][0];
        }

        return [];
    }

    /**
     * Tries to find user in User DB table. Creates or updates user data in DB.
     * @param $userData
     * @return user data
     */
    function findOrCreateUser($userData)
    {
        $user = User::find($userData['id']);
        $importData = [
            'id' => $userData['id'],
            'name' => $userData['username'],
            'email' => $userData['email'],
            'sharing' => $userData['sharing'],
            'reputation' => $userData['reputation']
        ];

        // check if user is already in DB
        if (is_null($user)) {
            User::create($importData);
            $user = User::find($userData['id']);
        } else {
            $user->update($importData);
        }

        return $user;
    }

    /**
     * Store OAuth tokens in session.
     *
     * @param SocialiteUser $socialiteUser
     */
    private function storeTokens(SocialiteUser $socialiteUser)
    {
        session([
            'netrunnerdb_access_token' => $socialiteUser->token,
            'netrunnerdb_refresh_token' => $socialiteUser->refreshToken,
            'netrunnerdb_token_expires_at' => $socialiteUser->expiresIn ? time() + $socialiteUser->expiresIn : null,
        ]);
    }

    /**
     * Remove OAuth tokens from session.
     */
    private function clearTokens()
    {
        session()->forget([
            'netrunnerdb_access_token',
            'netrunnerdb_refresh_token',
            'netrunnerdb_token_expires_at',
        ]);
    }

    /**
     * Resolve redirect URI from new and legacy config keys.
     *
     * @return string
     */
    private function resolveRedirectUri()
    {
        $redirect = config('services.netrunnerdb.redirect');
        if (!empty($redirect)) {
            return $redirect;
        }

        $redirectHost = config('services.netrunnerdb.redirect_url');
        if (empty($redirectHost)) {
            return rtrim(config('app.url'), '/').'/oauth2/redirect';
        }

        if (strpos($redirectHost, 'http://') === 0 || strpos($redirectHost, 'https://') === 0) {
            return rtrim($redirectHost, '/').'/oauth2/redirect';
        }

        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return $protocol.'://'.trim($redirectHost, '/').'/oauth2/redirect';
    }

    /**
     * Refresh OAuth access token with refresh token.
     *
     * @return bool
     */
    private function refreshAccessToken()
    {
        $refreshToken = session('netrunnerdb_refresh_token');
        if (empty($refreshToken)) {
            return false;
        }

        $response = $this->httpClient->request('POST', 'https://netrunnerdb.com/oauth/v2/token', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => config('services.netrunnerdb.client_id'),
                'client_secret' => config('services.netrunnerdb.client_secret'),
                'redirect_uri' => $this->resolveRedirectUri(),
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);
        if (!is_array($payload) || !array_key_exists('access_token', $payload)) {
            return false;
        }

        session([
            'netrunnerdb_access_token' => $payload['access_token'],
            'netrunnerdb_refresh_token' => array_key_exists('refresh_token', $payload) ? $payload['refresh_token'] : $refreshToken,
            'netrunnerdb_token_expires_at' => array_key_exists('expires_in', $payload) ? time() + intval($payload['expires_in']) : null,
        ]);

        return true;
    }

    /**
     * Send request to NetrunnerDB API.
     *
     * @param string $url
     * @param string $method
     * @param string|null $payload
     * @return string
     */
    private function requestNetrunnerDB($url, $method = 'GET', $payload = null)
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        $accessToken = session('netrunnerdb_access_token');
        if (!empty($accessToken)) {
            $options['headers']['Authorization'] = 'Bearer '.$accessToken;
        }

        if (!is_null($payload)) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = $payload;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);

            if ($response->getStatusCode() === 401 && $this->refreshAccessToken()) {
                $refreshedToken = session('netrunnerdb_access_token');
                if (!empty($refreshedToken)) {
                    $options['headers']['Authorization'] = 'Bearer '.$refreshedToken;
                    $response = $this->httpClient->request($method, $url, $options);
                }
            }

            return (string) $response->getBody();
        } catch (\Exception $e) {
            \Log::warning('NetrunnerDB API request failed: '.$e->getMessage());

            return json_encode(['error' => $e->getMessage()]);
        }
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

    /**
     * Downloads MWLfrom NetrunnerDB, stores them in DB.
     * @param Request $request
     * @return redirects to admin page
     */
    function requestMWL(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $added = $this->updateMWL();
        return redirect()->action('AdminController@lister')->with('message', "$added new MWL items added.");
    }

    /**
     * Adds claim to NetrunnerDB.
     * @param $decklistID int published decklist ID
     * @param $tournamentName string tournament name
     * @param $url string tournament URL
     * @param $rank int claim rank
     * @return mixed claim ID on NetrunnerDB
     * @throws \Exception
     */
    public function addClaimToNRDB($decklistID, $tournamentName, $url, $rank, $playerNumber) {
        $response = json_decode($this->requestNetrunnerDB(
            'https://netrunnerdb.com/api/2.1/private/decklists/'.$decklistID.'/claims',
            'POST', json_encode(['name' => $tournamentName, 'url' => $url, 'rank' => $rank,
            'participants' => $playerNumber])), true);

        if (array_key_exists('status', $response) && $response['status'] === 'success') {
            return $response['data']['claim']['id'];
        } else {
            \Log::alert("Coudn't add claim to NetrunnerDB: ".$response['error']);
            throw new \Exception($response['error']);
        }
    }

    /**
     * Deletes claim from NetrunnerDB.
     * @param $claimID int ID of NetrunnerDB claim
     * @param $decklistID int ID of decklist
     * @return mixed response from NetrunnerDB
     */
    public function deleteClaimFromNRDB($claimID, $decklistID) {
        $response = json_decode($this->requestNetrunnerDB(
            'https://netrunnerdb.com/api/2.1/private/decklists/'.$decklistID.'/claims/'.$claimID, 'DELETE'), true);

        // error logging
        if (array_key_exists('error', $response)) {
            \Log::alert("Coudn't delete claim from NetrunnerDB: ".$response['error']);
        }

       return $response;
    }

    /**
     * Publishes private deck on NetrunnerDB.
     * @param $deckID int ID of private deck to be published
     * @return mixed ID of newly published decklist
     * @throws \Exception NetrunnerDB error message
     */
    public function publishDeck($deckID) {
        $response = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/private/deck/publish',
            'POST', '{"deck_id":"'.$deckID.'", "description": "*published by AlwaysBeRunning.net*"}'), true);

        if (array_key_exists('success', $response) && $response['success']) {
            return $response['data'][0]['id'];  // return ID of newly published decklist
        } else {
            if (array_key_exists('msg', $response)) {
                \Log::alert("Coudn't publish to  NetrunnerDB: ".$response['msg']);
                throw new \Exception($response['msg']);
            } else {
                \Log::alert("Coudn't publish to  NetrunnerDB: ".$response['error']);
                throw new \Exception($response['error']);
            }
        }
    }

    private function updateIdentities()
    {
        $raw = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/public/cards'), true);
        if (!is_array($raw) || !array_key_exists('data', $raw) || !is_array($raw['data'])) {
            return 0;
        }

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

    private function updateCycles()
    {
        $raw = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/public/cycles'), true);
        if (!is_array($raw) || !array_key_exists('data', $raw) || !is_array($raw['data'])) {
            return 0;
        }

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

    private function updatePacks()
    {
        $raw = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/public/packs'), true);
        if (!is_array($raw) || !array_key_exists('data', $raw) || !is_array($raw['data'])) {
            return 0;
        }

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

    private function updateMWL()
    {
        $raw = json_decode($this->requestNetrunnerDB('https://netrunnerdb.com/api/2.0/public/mwl'), true);
        if (!is_array($raw) || !array_key_exists('data', $raw) || !is_array($raw['data'])) {
            return 0;
        }

        $added = 0;
        foreach ($raw['data'] as $mwl) {
            $exists = Mwl::find($mwl['id']);
            if (is_null($exists)) {
                $added++;
                Mwl::create([
                    'id' => $mwl['id'],
                    'date' => str_replace('-', '.', $mwl['date_start']).'.',
                    'name' => $mwl['name']
                ]);
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

