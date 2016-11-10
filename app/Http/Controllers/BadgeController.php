<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Badge;
use App\Http\Requests;
use App\Entry;
use App\Tournament;
use App\User;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{

    /**
     * Helper page for listing all available badges.
     * @return mixed
     */
    public function badges()
    {
        $badges = Badge::all();
        return view('badges', compact('badges'));
    }

    /**
     * Recalculates badges for all users
     */
    public function refreshBadges(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        $badgesBefore = DB::table('badge_user')->count();
        $users = User::all();

        foreach($users as $user) {
            $this->detachAutoBadges($user->id);
            $this->addClaimBadges($user->id);
            $this->refreshTOBadges($user->id);
        }

        $badgesAfter = DB::table('badge_user')->count();
        return redirect()->route('admin')->with('message', 'Badges added: '.($badgesAfter-$badgesBefore));
    }

    /**
     * Removes and readds all claim based badges
     * @param $userid
     */
    public function refreshClaimBadges($userid) {
        $this->detachAutoBadges($userid);
        $this->addClaimBadges($userid);
    }

    /**
     * Adds all claim based badges for user.
     * @param $userid
     */
    public function addClaimBadges($userid) {
        $this->addTournamentBadges($userid, 2016, 5);
        $this->addTournamentBadges($userid, 2016, 4);
        $this->addTournamentBadges($userid, 2016, 3);
        $this->addTournamentBadges($userid, 2016, 2);
    }

    /**
     * Recalculates all TO based badges for user.
     * @param $userid
     */
    public function refreshTOBadges($userid) {

    }

    /**
     * Removes all automated badges.
     * @param $userid
     */
    public function detachAutoBadges($userid) {
        $removedBadges = Badge::where('auto', 1)->pluck('id')->all();
        User::where('id', $userid)->first()->badges()->detach($removedBadges);
    }

    /**
     * Adds tournament badges to user
     * @param $userid
     * @year
     * @type
     */
    private function addTournamentBadges($userid, $year, $type) {
        $tounamentIds = Tournament::where('tournament_type_id', $type)
            ->where('date', '>', $year)->where('date', '<', ($year+1))->where('approved', 1)->pluck('id');

        // worlds winner
        $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', 1)->first();
        if ($found) {
            $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 1)->first()->id;
            $this->addBadge($userid, $badgeid);
        } elseif ($type > 2) {

            // worlds top 16
            $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', '>', 0)->first();

            if ($found) {
                $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 2)->first()->id;
                $this->addBadge($userid, $badgeid);
            } elseif ($type == 5) {
                // participation
                $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('runner_deck_id', '>', 0)->first();
                if ($found) {
                    $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 5)->first()->id;
                    $this->addBadge($userid, $badgeid);
                }
            }
        }
    }

    /**
     * Add badge to user if not already present.
     * @param $userid
     * @param $badgeid
     */
    private function addBadge($userid, $badgeid) {
        $user = User::where('id', $userid)->first();
        $found = User::where('id', $userid)->whereHas('badges', function($q) use ($badgeid) {
            $q->where('badge_id', $badgeid);
        })->first();
        if (!$found) {
            $user->badges()->attach($badgeid);
        }
    }
}
