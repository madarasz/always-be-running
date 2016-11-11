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
        $badges = Badge::orderBy('auto', 'desc')->orderBy('tournament_type_id', 'desc')->orderBy('order','asc')->get();
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
            $this->detachAutoBadges($user->id, true);
            $this->detachAutoBadges($user->id, false);
            $this->addClaimBadges($user->id);
            $this->addTOBadges($user->id);
            $this->addDeckBadges($user->id);
        }

        $badgesAfter = DB::table('badge_user')->count();
        return redirect()->route('admin')->with('message', 'Badges added: '.($badgesAfter-$badgesBefore));
    }

    /**
     * Removes and readds all claim based badges
     * @param $userid
     */
    public function refreshClaimBadges($userid) {
        $this->detachAutoBadges($userid, true);
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
        $this->addPlayerLevelBadges($userid);
    }

    /**
     * Recalculates all TO based badges for user.
     * @param $userid
     */
    public function refreshTOBadges($userid) {
        $this->detachAutoBadges($userid, false);
        $this->addTOBadges($userid);
    }

    public function addTOBadges($userid) {
        $this->addTOLevelBadges($userid);
        $this->addNRTMBadge($userid);
        $this->addFancyTOBadge($userid);
    }

    /**
     * Removes all automated badges.
     * @param $userid
     */
    public function detachAutoBadges($userid, $claims) {
        if ($claims) {
            $removedBadges = Badge::where('auto', 1)->whereNotNull('tournament_type_id')->pluck('id')->all();
        } else {
            $removedBadges = Badge::where('auto', 1)->whereNull('tournament_type_id')->pluck('id')->all();
        }
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

    private function addTOLevelBadges($userid) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->count();
        if ($count >= 20) {
            $this->addBadge($userid, 18); // GOLD T.O.
        } elseif ($count >= 8) {
            $this->addBadge($userid, 17);   // SILVER T.O.
        } elseif ($count >= 2) {
            $this->addBadge($userid, 16);   // BRONZE T.O.
        }
    }

    private function addPlayerLevelBadges($userid) {
        $count = Entry::where('user', $userid)->whereNotNull('runner_deck_id')->count();
        if ($count >= 25) {
            $this->addBadge($userid, 15);   // GOLD player
        } elseif ($count >= 10) {
            $this->addBadge($userid, 14);   // SILVER player
        } elseif ($count >= 3) {
            $this->addBadge($userid, 13);   // BRONZE player
        }
    }

    private function addDeckBadges($userid) {
        $user = User::where('id', $userid)->first();
        if ($user->published_decks >= 20) {
            $this->addBadge($userid, 21);   // Hard-working publisher
        }
        if ($user->private_decks >= 150) {
            $this->addBadge($userid, 25);   // Keeper of many secrets
        }
    }

    private function addNRTMBadge($userid) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->where('import', 1)->count();
        if ($count >= 3) {
            $this->addBadge($userid, 26); // NRTM preacher
        }
    }

    private function addFancyTOBadge($userid) {
        $tournaments = Tournament::where('creator', $userid)->where('approved', 1)->get();
        foreach ($tournaments as $tournament) {
            if (strlen($tournament->description) > 1000 &&
                preg_match('/[^!]\[([^\]]+)\]\(([^)]+)\)/', $tournament->description) && //link
                preg_match('/!\[([^\]]+)\]\(([^)]+)\)/', $tournament->description)) { //image
                $this->addBadge($userid, 20);
                break;
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
