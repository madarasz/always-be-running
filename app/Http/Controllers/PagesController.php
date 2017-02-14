<?php

namespace App\Http\Controllers;

use App\Entry;
use App\User;
use App\Badge;
use App\CardIdentity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Tournament;
use App\TournamentType;
use App\CardPack;

use App\Http\Requests;

class PagesController extends Controller
{

    public function upcoming(Request $request)
    {
        $nowdate = date('Y.m.d.', time() - 86400); // actually yesterday, to be on the safe side
        $tournaments = Tournament::where(function($q) use ($nowdate) {
                $q->where('date', '>=', $nowdate)->orWhereNotNull('recur_weekly');
            })->where('approved', 1);
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $countries = $tournaments->where('location_country', '!=', '')->orderBy('location_country')->pluck('location_country')->unique()->all();
        $states = $tournaments->orderBy('location_state')->pluck('location_state')->unique()->all();
        if(($states_key = array_search('', $states)) !== false) {
            unset($states[$states_key]);
        }
        $countries = array_values($countries);
        $states = array_values($states);
        $message = session()->has('message') ? session('message') : '';

        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $countries = [-1 => '---'] + $countries;
        $states = [-1 => '---'] + $states;
        $page_section = 'upcoming';

        // user's default filter
        if ($request->user() && $request->user()->autofilter_upcoming && $request->user()->country_id) {
            $default_country = $request->user()->country->name;
            $default_country_id = array_search($default_country, $countries, true);
        }

        return view('upcoming', compact('message', 'nowdate', 'tournament_types', 'countries', 'states', 'page_section',
            'default_country', 'default_country_id'));
    }

    public function results(Request $request, $cardpool = "", $type = "", $country = "", $videos = "")
    {
        $nowdate = date('Y.m.d.', time() + 86400); // actually tomorrow, to be on the safe side
        $tournaments = Tournament::where('date', '<=', $nowdate)->where('approved', 1)->where('concluded',1);
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $tournament_cardpools = CardPack::whereIn('id', $tournaments->pluck('cardpool_id')->unique()->all())->where('id', '!=', 'unknown')
            ->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $countries = $tournaments->where('location_country', '!=', '')->orderBy('location_country')
            ->pluck('location_country')->unique()->all();

        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $countries = [-1 => '---'] + $countries;
        $tournament_cardpools = [-1 => '---'] + $tournament_cardpools;
        $message = session()->has('message') ? session('message') : '';
        $page_section = 'results';

        // user's default filter
        if ($request->user() && $request->user()->autofilter_results && $request->user()->country_id) {
            $default_country = $request->user()->country->name;
            $default_country_id = array_search($default_country, $countries, true);
        }

        return view('results', compact('registered', 'message', 'nowdate', 'tournament_types', 'countries',
            'tournament_cardpools', 'page_section', 'default_country', 'default_country_id',
            'cardpool', 'type', 'country', 'videos'));
    }

    /**
     * Show organize tournamnets page.
     * @param Request $request
     * @return view
     */
    public function organize(Request $request)
    {
        if (is_null($request->user())) {
            return view('loginreq');
        }
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user = $request->user()->id;
        $message = session()->has('message') ? session('message') : '';
        $page_section = 'organize';
        return view('organize', compact('user', 'message', 'page_section'));
    }

    public function personal(Request $request)
    {
        if (is_null($request->user())) {
            return view('loginreq');
        }
        $message = session()->has('message') ? session('message') : '';
        $user = $request->user();
        $created_count = Tournament::where('creator', $user->id)->count();
        $claim_count = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->count();
        $username = $request->user()->name;
        $page_section = 'personal';
        return view('personal', compact('message', 'user', 'username', 'page_section', 'created_count', 'claim_count'));
    }

    public function about()
    {
        $helpers = Badge::where('id', 24)->first()->users()->get();
        return view('about', ['helpers' => $helpers]);
    }

    public function faq()
    {
        return view('faq');
    }

    public function markdown()
    {
        return view('markdown');
    }

    public function profile(Request $request, $id)
    {
        // own profile
        if (Auth::check()) {
            $request_id = Auth::user()->id;
            if ($id == $request_id) {
                $page_section = 'profile';
                $countries = \Countries::orderBy('name')->get();
                $factions = CardIdentity::where('pack_code', '!=', 'draft')->groupBy('faction_code')->get();
            }
        }

        $user = User::findOrFail($id);

        $deleted_tournaments = Tournament::withTrashed()->whereNotNull('deleted_at')->pluck('id')->all();
        $message = session()->has('message') ? session('message') : '';
        $created_count = Tournament::where('creator', $user->id)->where('approved', 1)->count();
        $claim_count = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->count();
        $claims = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')
            ->whereNotIn('tournament_id', $deleted_tournaments)->get();
        $created = Tournament::where('creator', $user->id)->where('approved', 1)->get();
        $username = $user->name;
        return view('profile', compact('user', 'claims', 'created', 'created_count', 'claim_count',
            'username', 'page_section', 'message', 'countries', 'factions'));
    }

    public function updateProfile(Request $request) {
        // edit only for self
        if ($request->id != Auth::user()->id) {
            abort(403);
        }

        // checkbox fix
        $request->merge(['autofilter_upcoming' => $request->autofilter_upcoming === 'on']);
        $request->merge(['autofilter_results' => $request->autofilter_results === 'on']);

        $user = User::findorFail($request->id);
        $user->update($request->all());
        return redirect()->route('profile.show', $request->id)
            ->with('message', 'Profile updated.');
    }

    /**
     * API endpoint for user notification badges.
     * @return json
     */
    public function getAlertCount() {
        if (!Auth::user()) {
            abort(403);
        }

        $userid = Auth::user()->id;
        $toclaim = Tournament::where('concluded', 1)->pluck('id');
        $nowdate = date('Y.m.d.', time());
        $weeklaterdate = date('Y.m.d.', time() + 86400 * 7);
        $toconclude = Tournament::where('creator', $userid)->where('tournament_type_id', '!=', 8)
            ->where('concluded', 0)->where('date', '<', $nowdate)->count();
        $tocomplete = Tournament::where('creator', $userid)->where('incomplete', 1)->count();
        $tocardpool = Tournament::where('creator', $userid)->whereNotNull('date')->where('cardpool_id', 'unknown')
            ->where('date', '<', $weeklaterdate)->count();
        $toclaimalert = Entry::where('user', $userid)->whereIn('tournament_id', $toclaim)->whereNull('rank')->count();
        $brokenclaim = Entry::where('user', $userid)->where('rank', '>', 0)->where(function($q){
                $q->where('broken_runner', '=', 1)->orWhere('broken_corp', '=', 1);
            })->count();
        $result = [
            'personalAlerts' => [
                'total' => $toclaimalert + $brokenclaim,
                'toClaimAlert' => $toclaimalert,
                'brokenClaimAlert' => $brokenclaim
            ],
            'organizeAlert' => [
                'total' => $tocomplete + $toconclude + $tocardpool,
                'concludeAlert' => $toconclude,
                'incompleteAlert' => $tocomplete,
                'unknownCardpoolAlert' => $tocardpool
            ],
            'profileAlerts' => Auth::user()->badges()->wherePivot('seen', 0)->count()
        ];

        if (Auth::user()->admin) {
            $pending = Tournament::whereNull('approved')->where('incomplete', 0)->count();
            $conflict = Tournament::where('conflict', 1)->count();
            $result['adminAlerts'] = [
                'total' => $pending + $conflict,
                'pendingAlerts' => $pending,
                'conflictAlerts' => $conflict
            ];
        }

        return response()->json($result);
    }

    public function supportMe()
    {
        $badges = Badge::where('order', '>', 9000)->orderBy('order')->get();
        return view('supportme', compact('badges'));
    }

    public function thankYou()
    {
        return view('thankyou');
    }
}
