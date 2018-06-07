<?php

namespace App\Http\Controllers;

use App\Country;
use App\Entry;
use App\Photo;
use App\TournamentFormat;
use App\User;
use App\Badge;
use App\CardIdentity;
use App\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Tournament;
use App\TournamentType;
use App\CardPack;
use Illuminate\Support\Facades\DB;

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
        $featured = Tournament::where('featured', '>', 0)->where('concluded', 0)->where('approved', 1)
            ->where('date', '>=', $nowdate)->orderBy('date', 'asc')->get();
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
            'default_country', 'default_country_id', 'featured'));
    }

    public function results(Request $request)
    {
        // GET parameters for filtering
        $cardpool = $request->input('cardpool');
        $type = $request->input('type');
        $country = $request->input('country');
        $format = $request->input('format');
        $videos = $request->input('videos');
        $matchdata = $request->input('matchdata');

        $nowdate = date('Y.m.d.');
        $tournaments = Tournament::where('approved', 1)->where('concluded',1);
        $tournament_types = TournamentType::whereIn('id', $tournaments->pluck('tournament_type_id')->unique()->all())->pluck('type_name', 'id')->all();
        $tournament_cardpools = CardPack::whereIn('id', $tournaments->pluck('cardpool_id')->unique()->all())->where('id', '!=', 'unknown')
            ->orderBy('cycle_position', 'desc')->orderBy('position', 'desc')->pluck('name', 'id')->all();
        $countries = $tournaments->where('location_country', '!=', '')->orderBy('location_country')
            ->pluck('location_country')->unique()->all();
        $tournament_formats = TournamentFormat::whereIn('id', $tournaments->pluck('tournament_format_id')->unique()->all())->pluck('format_name', 'id')->all();
        $featured = Tournament::where('featured', '>', 0)->where('concluded', 1)->where('approved', 1)
            ->with('winner')->orderBy('date', 'desc')->get();

        // adding empty filters
        $tournament_types = [-1 => '---'] + $tournament_types;
        $tournament_formats = [-1 => '---'] + $tournament_formats;
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
            'tournament_cardpools', 'tournament_formats', 'page_section', 'default_country', 'default_country_id',
            'cardpool', 'type', 'country', 'format', 'videos', 'matchdata', 'featured', 'nowdate'));
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
        $admin = $request->user()->admin == 1;
        $countries = Country::orderBy('name')->pluck('name', 'name');
        $usedCountries = Tournament::where('location_country', '!=', '')->orderBy('location_country')
            ->groupBy('location_country')->pluck('location_country', 'location_country');

        $message = session()->has('message') ? session('message') : '';
        $page_section = 'organize';
        return view('organize', compact('user', 'message', 'page_section', 'countries', 'usedCountries', 'admin'));
    }

    public function personal(Request $request)
    {
        if (is_null($request->user())) {
            return view('loginreq');
        }
        $message = session()->has('message') ? session('message') : '';
        $user = $request->user();
        $secret_id = $user->getSecretId();
        $created_count = Tournament::where('creator', $user->id)->count();
        $claim_count = Entry::where('user', $user->id)->whereNotNull('runner_deck_id')->count();
        $photo_count = Photo::where('user_id', $user->id)->count();
        $photo_tournament_ids = Photo::where('user_id', $user->id)->pluck('tournament_id');
        $photo_tournaments = Tournament::whereIn('id', $photo_tournament_ids)->orderBy('date', 'desc')->get();
        $video_count = Video::where('user_id', $user->id)->count();
        $video_tournament_ids = Video::where('user_id', $user->id)->pluck('tournament_id');
        $video_tournaments = Tournament::whereIn('id', $video_tournament_ids)->orderBy('date', 'desc')->get();
        $username = $request->user()->name;
        $page_section = 'personal';
        $runnerIDs = app('App\Http\Controllers\TournamentsController')->categorizeIDs(CardIdentity::where('runner', 1)
            ->orderBy('faction_code')->orderBy('title')->get());
        $corpIDs = app('App\Http\Controllers\TournamentsController')->categorizeIDs(CardIdentity::where('runner', 0)
            ->orderBy('faction_code')->orderBy('title')->get());
        return view('personal', compact('message', 'user', 'username', 'page_section', 'created_count', 'claim_count',
            'runnerIDs', 'corpIDs', 'photo_count', 'photo_tournaments', 'video_count', 'video_tournaments', 'secret_id'));
    }

    public function prizes(Request $request)
    {
        $user_id = is_null($request->user()) ? 0 : $request->user()->id;
        return view('prizes', ['page_section' => 'prizes', 'user_id' => $user_id]);
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

    public function privacy(Request $request)
    {
        $user = $request->user();
        return view('privacy', compact('user'));
    }

    public function markdown()
    {
        return view('markdown');
    }

    public function supportMe()
    {
        $badges = Badge::where('order', '>', 9000)->orderBy('order')->get();
        $supporters = User::where('supporter', '>', 0)->orderBy('supporter', 'desc')->get();
        $scount = count($supporters);
        return view('supportme', compact('badges', 'supporters', 'scount'));
    }

    public function thankYou()
    {
        return view('thankyou');
    }

    public function api()
    {
        $types = TournamentType::get()->pluck('type_name', 'id');
        $formats = TournamentFormat::get()->pluck('format_name', 'id');
        return view('api', compact('types', 'formats'));

    }
    public function birthdayFirst()
    {
        $tournaments = Tournament::where('approved', 1)->where('concluded', 1)->count();
        $weekly = Tournament::where('approved', 1)->whereNull('date')->count();
        $countries = Tournament::where('approved', 1)->pluck('location_country')->unique()->count();
        $users = User::count();
        $supporters = User::where('supporter', '>', 0)->get();
        $claims = Entry::whereIn('type', [3, 4])->count();
        $decks = Entry::where('type', 3)->count() * 2;
        $videos = Video::where('flag_removed', false)->count();
        $photos = Photo::count();
        $badges = Badge::count();
        return view('birthday', compact('tournaments', 'weekly', 'countries', 'users', 'claims', 'decks',
            'videos', 'photos', 'badges', 'supporters'));
    }

    /**
     * Lists available countries in common name => flag JSON object format
     * Adds list of countries where DB is not matching with Google Maps country names as '_mapping_problems'
     * @return \Illuminate\Http\JsonResponse
     */
    public function CountryToCodeMapping() {
        $results = Country::orderBy('name')->pluck('flag', 'name');
        $googleMappingProblems = Tournament::select('location_country')->distinct()
            ->leftJoin('countries', 'tournaments.location_country', '=', 'countries.name')
            ->where('location_country', '!=', '')->whereNull('countries.name')->pluck('location_country');
        $results['_mapping_problems'] = $googleMappingProblems;
        return response()->json($results);
    }
}
