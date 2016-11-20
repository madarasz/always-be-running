<?php

namespace App\Http\Controllers;

use App\Entry;
use App\User;
use App\Badge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Tournament;
use App\TournamentType;
use App\CardPack;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home(Request $request)
    {
        $yesterday = date('Y.m.d.', time() - 86400);
        $tomorrow = date('Y.m.d.', time() + 86400);
        $message = session()->has('message') ? session('message') : '';
        $user = $request->user();
        if ($user) {
            $created_count = Tournament::where('creator', $user->id)->count();
            $claim_count = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->count();
        }
        return view('home', compact('message', 'user', 'created_count', 'claim_count', 'yesterday', 'tomorrow'));
    }

    public function upcoming()
    {
        $nowdate = date('Y.m.d.', time() - 86400); // actually yesterday, to be on the safe side
        $tournaments = Tournament::where(function($q) use ($nowdate) {
                $q->where('date', '>=', $nowdate)->orWhereNotNull('recur_weekly');
            })->where('approved', 1)->whereNull('deleted_at');
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $countries = $tournaments->where('location_country', '!=', '')->orderBy('location_country')->pluck('location_country')->unique()->all();
        $states = $tournaments->pluck('location_state')->unique()->all();
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
        return view('upcoming', compact('message', 'nowdate', 'tournament_types', 'countries', 'states', 'page_section'));
    }

    public function results(Request $request)
    {
        $nowdate = date('Y.m.d.', time() + 86400); // actually tomorrow, to be on the safe side
        $tournaments = Tournament::where('date', '<=', $nowdate)->where('approved', 1)->where('concluded',1)->whereNull('deleted_at');
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $tournament_cardpools = CardPack::whereIn('id', $tournaments->pluck('cardpool_id')->unique()->all())->pluck('name', 'id')->all();;
        $countries = $tournaments->where('location_country', '!=', '')->pluck('location_country')->unique()->all();
        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $countries = [-1 => '---'] + $countries;
        $tournament_cardpools = [-1 => '---'] + $tournament_cardpools;
        $message = session()->has('message') ? session('message') : '';
        $page_section = 'results';
        return view('results', compact('registered', 'message', 'nowdate', 'tournament_types', 'countries', 'tournament_cardpools', 'page_section'));
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
            }
        }

        $user = User::where('id', $id)->first();

        // non existing user
        if (is_null($user)) {
            abort(404);
        }

        $message = session()->has('message') ? session('message') : '';
        $created_count = Tournament::where('creator', $user->id)->where('approved', 1)->whereNull('deleted_at')->count();
        $claim_count = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->count();
        $claims = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->get();
        $created = Tournament::where('creator', $user->id)->where('approved', 1)->whereNull('deleted_at')->get();
        $username = $user->name;
        return view('profile', compact('user', 'claims', 'created', 'created_count', 'claim_count',
            'username', 'page_section', 'message'));
    }

    public function updateProfile(Request $request) {
        // edit only for self
        if ($request->id != Auth::user()->id) {
            abort(403);
        }
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
        $toclaim = Tournament::where('concluded', 1)->whereNull('deleted_at')->pluck('id');
        $nowdate = date('Y.m.d.', time());

        $result = [
            'personalAlerts' => Entry::where('user', $userid)->whereIn('tournament_id', $toclaim)
                ->whereNull('rank')->count(),
            'organizeAlert' =>Tournament::where('creator', $userid)->where('concluded', 0)->where('date', '<', $nowdate)
                ->whereNull('deleted_at')->count(),
            'profileAlerts' => Auth::user()->badges()->wherePivot('seen', 0)->count()
        ];

        if (Auth::user()->admin) {
            $pending = Tournament::whereNull('approved')->whereNull('deleted_at')->count();
            $conflict = Tournament::where('conflict', 1)->whereNull('deleted_at')->count();
            $result['adminAlerts'] = [
                'total' => $pending + $conflict,
                'pendingAlerts' => $pending,
                'conflictAlerts' => $conflict
            ];
        }

        return response()->json($result);
    }
}
