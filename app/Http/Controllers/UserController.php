<?php

namespace App\Http\Controllers;

use App\CardIdentity;
use App\Entry;
use App\Photo;
use App\Tournament;
use App\User;
use App\Video;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Displays profile page
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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
        $claim_count = Entry::where('user', $user->id)->whereIn('type', [3, 4])->count();
        $claims = Entry::select(\DB::raw('entries.*'))->join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
            ->where('user', $user->id)->whereIn('type', [3, 4])->whereNotIn('tournament_id', $deleted_tournaments)
            ->orderBy('tournaments.date', 'desc')->get();
        $claims_by_size = Entry::select(\DB::raw('entries.*'))->join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
        ->where('user', $user->id)->whereIn('type', [3, 4])->whereNotIn('tournament_id', $deleted_tournaments)
        ->orderBy('tournaments.players_number', 'desc')->get();
        $created = Tournament::where('creator', $user->id)->where('approved', 1)->orderBy('tournaments.date', 'desc')->get();
        $username = $user->name;
        return view('profile', compact('user', 'claims', 'claims_by_size', 'created', 'created_count', 'claim_count',
            'username', 'page_section', 'message', 'countries', 'factions'));
    }

    /**
     * Updates profile page.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request) {
        // edit only for self
        if ($request->id != Auth::user()->id) {
            abort(403);
        }

        $user = User::findorFail($request->id);
        $user->update($request->all());

        return response()->json('Profile updated.');
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
        $unavailableVideos = Video::where('user_id', $userid)->where('flag_removed', true)->count();
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
                'total' => $toclaimalert + $brokenclaim + $unavailableVideos,
                'toClaimAlert' => $toclaimalert,
                'brokenClaimAlert' => $brokenclaim,
                'unavailableVideos' => $unavailableVideos
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
            $photo = Photo::whereNull('approved')->count();
            $result['adminAlerts'] = [
                'total' => $pending + $conflict + $photo,
                'pendingTournament' => $pending,
                'conflictTournament' => $conflict,
                'pendingPhoto' => $photo
            ];
        }

        return response()->json($result);
    }
}
