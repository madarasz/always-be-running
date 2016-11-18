<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Badge;
use App\Http\Requests;
use App\Entry;
use App\Tournament;
use App\User;
use App\CardIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
     * Recalculates badges for all users, for admins.
     */
    public function refreshBadges(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        $badgesBefore = DB::table('badge_user')->count();
        $users = User::all();

        foreach($users as $user) {
            $this->addClaimBadges($user->id);
            $this->addTOBadges($user->id);
            $this->addNDBBadges($user->id);
        }

        $badgesAfter = DB::table('badge_user')->count();
        return redirect()->route('admin')->with('message', 'Badges added: '.($badgesAfter-$badgesBefore));
    }

    /**
     * Adds all claim based badges for user.
     * @param $userid
     */
    public function addClaimBadges($userid) {
        // prepare badges array
        $badges = Badge::where('year', 2016)->pluck('id')->all();
        $badges = array_merge([13, 14, 15, 27, 28, 29, 30, 34, 35, 36], $badges);
        $badges = array_combine($badges, array_fill(1, count($badges), false));

        $this->addChampionshipBadges($userid, 2016, 5, $badges);
        $this->addChampionshipBadges($userid, 2016, 4, $badges);
        $this->addChampionshipBadges($userid, 2016, 3, $badges);
        $this->addChampionshipBadges($userid, 2016, 2, $badges);
        $this->addPlayerLevelBadges($userid, $badges);
        $this->addFactionBadges($userid, $badges);
        $this->addRecurringBadge($userid, $badges);
        $this->addRoadBadge($userid, $badges);
        $this->addCOS($userid, $badges);
        $this->addTravellerPlayer($userid, $badges);

        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Adds, removes badges related to tournament organizing.
     * @param $userid
     */
    public function addTOBadges($userid) {
        $badges = [16 => false, 17 => false, 18 => false, 26 => false, 20 => false, 37 => false];

        $this->addTOLevelBadges($userid, $badges);
        $this->addNRTMBadge($userid, $badges);
        $this->addFancyTOBadge($userid, $badges);
        $this->addTOChampion($userid, $badges);

        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Adds tournament badges to badge list
     * @param $userid
     * @year
     * @type
     * @badges badge list
     */
    private function addChampionshipBadges($userid, $year, $type, &$badges) {
        $tounamentIds = Tournament::where('tournament_type_id', $type)
            ->where('date', '>', $year)->where('date', '<', ($year+1))->where('approved', 1)->whereNull('deleted_at')->pluck('id');

        // worlds winner
        $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', 1)->first();
        if ($found) {
            $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 1)->first()->id;
            $badges[$badgeid] = true;
        } elseif ($type > 2) {

            // worlds top 16
            $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', '>', 0)->first();

            if ($found) {
                $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 2)->first()->id;
                $badges[$badgeid] = true;
            } elseif ($type == 5) {
                // participation
                $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('runner_deck_id', '>', 0)->first();
                if ($found) {
                    $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 5)->first()->id;
                    $badges[$badgeid] = true;
                }
            }
        }
    }

    private function addTOLevelBadges($userid, &$badges) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->whereNull('deleted_at')->count();
        if ($count >= 20) {
            $badges[18] = true; // GOLD T.O.
        } elseif ($count >= 8) {
            $badges[17] = true;   // SILVER T.O.
        } elseif ($count >= 2) {
            $badges[16] = true;   // BRONZE T.O.
        }
    }

    private function addPlayerLevelBadges($userid, &$badges) {
        $count = Entry::where('user', $userid)->where('runner_deck_id', '>', 0)->count();
        if ($count >= 20) {
            $badges[15] = true;   // GOLD player
        } elseif ($count >= 8) {
            $badges[14] = true;   // SILVER player
        } elseif ($count >= 2) {
            $badges[13] = true;   // BRONZE player
        }
    }

    private function addFactionBadges($userid, &$badges) {
        $mini = Entry::where('user', $userid)->whereIn('runner_deck_identity', ['09029', '09045', '09037'])->first();
        if ($mini) {
            $badges[27] = true; // minority report
        }

        $shapers = CardIdentity::where('faction_code','shaper')->pluck('id');
        $crims = CardIdentity::where('faction_code','criminal')->pluck('id');
        $anarchs = CardIdentity::where('faction_code','anarch')->pluck('id');

        if (Entry::where('user', $userid)->whereIn('runner_deck_identity', $shapers)->first() &&
            Entry::where('user', $userid)->whereIn('runner_deck_identity', $crims)->first() &&
            Entry::where('user', $userid)->whereIn('runner_deck_identity', $anarchs)->first()) {
            $badges[28] = true; // self-modifying personality
        }

        $nbn = CardIdentity::where('faction_code','nbn')->pluck('id');
        $hb = CardIdentity::where('faction_code','haas-bioroid')->pluck('id');
        $weyland = CardIdentity::where('faction_code','weyland-cons')->pluck('id');
        $jinteki = CardIdentity::where('faction_code','jinteki')->pluck('id');

        if (Entry::where('user', $userid)->whereIn('corp_deck_identity', $nbn)->first() &&
            Entry::where('user', $userid)->whereIn('corp_deck_identity', $hb)->first() &&
            Entry::where('user', $userid)->whereIn('corp_deck_identity', $weyland)->first() &&
            Entry::where('user', $userid)->whereIn('corp_deck_identity', $jinteki)->first()) {
            $badges[29] = true; // diversified portfolio
        }
    }

    private function addRoadBadge($userid, &$badges) {
        $stores = Tournament::where('tournament_type_id', 2)->where('approved', 1)->whereNull('deleted_at')->pluck('id');
        $regionals = Tournament::where('tournament_type_id', 3)->where('approved', 1)->whereNull('deleted_at')->pluck('id');
        $nationals = Tournament::where('tournament_type_id', 4)->where('approved', 1)->whereNull('deleted_at')->pluck('id');

        if (Entry::where('user', $userid)->whereIn('tournament_id', $stores)->where('rank', '>', 0)->first() &&
            Entry::where('user', $userid)->whereIn('tournament_id', $regionals)->where('rank', '>', 0)->first() &&
            Entry::where('user', $userid)->whereIn('tournament_id', $nationals)->where('rank', '>', 0)->first()) {
            $badges[34] = true; // road to worlds
        }

    }

    /**
     * Adds NetrunnerDB related badges to user.
     * These badges are never removed.
     * @param $userid
     */
    public function addNDBBadges($userid) {
        $user = User::where('id', $userid)->first();
        $badges = [];

        if ($user->published_decks >= 20) {
            array_push($badges, 21); // Hard-working publisher
        }
        if ($user->private_decks >= 150) {
            array_push($badges, 25);   // Keeper of many secrets
        }
        if ($user->reputation >= 1000) {
            array_push($badges, 31);   // NetrunnerDB VIP
        } elseif ($user->reputation >= 500) {
            array_push($badges, 32);   // NetrunnerDB Celeb
        } elseif ($user->reputation >= 100) {
            array_push($badges, 33);   // NetrunnerDB Known
        }

        $user->badges()->sync($badges, false);
    }

    private function addNRTMBadge($userid, &$badges) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->where('import', 1)->whereNull('deleted_at')->count();
        if ($count >= 3) {
            $badges[26] = true; // NRTM preacher
        }
    }

    private function addFancyTOBadge($userid, &$badges) {
        $tournaments = Tournament::where('creator', $userid)->where('approved', 1)->whereNull('deleted_at')->get();
        foreach ($tournaments as $tournament) {
            if (strlen($tournament->description) > 600 &&
                preg_match('/[^!]\[([^\]]+)\]\(([^)]+)\)/', $tournament->description) && //link
                preg_match('/!\[([^\]]+)\]\(([^)]+)\)/', $tournament->description)) { //image
                    $badges[20] = true; // Fancy T.O.
                    break;
            }
        }
    }

    private function addRecurringBadge($userid, &$badges) {
        $recurring = Tournament::where('recur_weekly', '>', 0)->where('approved', 1)->whereNull('deleted_at')->pluck('id');
        if (Entry::where('user', $userid)->whereIn('tournament_id', $recurring)->first()) {
            $badges[30] = true; // trapped in time
        }
    }

    private function addCOS($userid, &$badges) {
        $tournaments = Tournament::whereIn('tournament_type_id', [1, 6, 7])->where('players_number', '>', 7)->where('approved', 1)->whereNull('deleted_at')->pluck('id');
        $entries = Entry::where('user', $userid)->whereIn('tournament_id', $tournaments)->get();
        foreach ($entries as $entry) {
            if ($entry->rank() == 1) {
                $badges[35] = true; // champion of sorts
                break;
            }
        }
    }

    private function addTravellerPlayer($userid, &$badges) {
        $country_count = DB::table('entries')->join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
            ->selectRaw('*, count(*)')->where('entries.user', $userid)->where('tournaments.tournament_type_id', '!=', 7)
            ->where('tournaments.approved', 1)->whereNull('tournaments.deleted_at')->groupBy('tournaments.location_country')
            ->where('entries.rank', '>', 0)->get();
        if (count($country_count) >= 3) {
            $badges[36] = true; // travelling player
        }
    }

    private function addTOChampion($userid, &$badges) {
        if (Tournament::where('creator', $userid)->where('tournament_type_id', 2)->where('concluded', 1)
                ->where('approved', 1)->whereNull('deleted_at')->first() &&
            Tournament::where('creator', $userid)->where('tournament_type_id', 3)->where('concluded', 1)
                ->where('approved', 1)->whereNull('deleted_at')->first() &&
            Tournament::where('creator', $userid)->where('tournament_type_id', 4)->where('concluded', 1)
                ->where('approved', 1)->whereNull('deleted_at')->first()) {
                $badges[37] = true; // community champion
        }
    }

    /**
     * Set 'seen' flag to true for all badges of user.
     * @param $userid
     */
    public function changeBadgesToSeen($userid) {
        if (Auth::user() && Auth::user()->id == $userid) {
            DB::table('badge_user')->where('user_id', $userid)->update(['seen' => 1]);
        }
    }

    /**
     * Updates badges of users
     * @param $userid
     * @param $badges array keys with false value are removed, keys with true value are added
     */
    private function refreshUserBadges($userid, $badges) {
        $user = User::where('id', $userid)->first();
        $to_add = []; $to_remove = [];
        foreach ($badges as $badgeid => $value) {
            if ($value) {
                array_push($to_add, $badgeid);
            } else {
                array_push($to_remove, $badgeid);
            }
        }

        $user->badges()->sync($to_add, false);
        $user->badges()->detach($to_remove);
    }
}
