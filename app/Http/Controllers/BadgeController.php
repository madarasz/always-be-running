<?php

namespace App\Http\Controllers;

use App\VideoTag;
use Illuminate\Http\Request;
use App\Video;
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
        $badges_worlds_winner = Badge::where('tournament_type_id', 5)->where('winlevel', 1)->orderBy('order','desc');
        $badges_worlds_top16 = Badge::where('tournament_type_id', 5)->where('winlevel', 2)->orderBy('order','desc');
        $badges_worlds_player = Badge::where('tournament_type_id', 5)->where('winlevel', 5)->orderBy('order','desc');
        $europe_winner = Badge::where('tournament_type_id', 9)->where('winlevel', 1)->orderBy('order','desc');
        $europe_top16 = Badge::where('tournament_type_id', 9)->where('winlevel', 2)->orderBy('order','desc');
        $europe_player = Badge::where('tournament_type_id', 9)->where('winlevel', 5)->orderBy('order','desc');
        $namerica_winner = Badge::where('tournament_type_id', 10)->where('winlevel', 1)->orderBy('order','desc');
        $namerica_top16 = Badge::where('tournament_type_id', 10)->where('winlevel', 2)->orderBy('order','desc');
        $namerica_player = Badge::where('tournament_type_id', 10)->where('winlevel', 5)->orderBy('order','desc');
        $nationals_winner = Badge::where('tournament_type_id', 4)->where('winlevel', 1)->orderBy('order','desc');
        $nationals_top = Badge::where('tournament_type_id', 4)->where('winlevel', 2)->orderBy('order','desc');
        $regionals_winner = Badge::where('tournament_type_id', 3)->where('winlevel', 1)->orderBy('order','desc');
        $regionals_top = Badge::where('tournament_type_id', 3)->where('winlevel', 2)->orderBy('order','desc');
        $comm_uk_winner = Badge::where('order', '>', 8100)->where('order', '<', 8199)->where('winlevel', 1)->orderBy('order','desc');
        $comm_uk_player = Badge::where('order', '>', 8100)->where('order', '<', 8199)->where('winlevel', 2)->orderBy('order','desc');
        $comm_hun_winner = Badge::where('order', '>', 8200)->where('order', '<', 8299)->where('winlevel', 1)->orderBy('order','desc');
        $comm_hun_player = Badge::where('order', '>', 8200)->where('order', '<', 8299)->where('winlevel', 2)->orderBy('order','desc');

        return view('badges', compact([
            'badges', 'badges_worlds_winner', 'badges_worlds_top16', 'badges_worlds_player',
            'europe_winner', 'europe_top16', 'europe_player',
            'namerica_winner', 'namerica_top16', 'namerica_player',
            'nationals_winner', 'nationals_top', 'regionals_winner', 'regionals_top',
            'comm_uk_winner', 'comm_uk_player', 'comm_hun_winner', 'comm_hun_player'
        ]));
    }

    /**
     * Recalculates badges for all users, for admins.
     */
    public function refreshBadges(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        set_time_limit(600);

        $startTime = microtime(true);
        $badgesBefore = DB::table('badge_user')->count();
        $users = User::all();

        foreach($users as $user) {
            $this->addClaimBadges($user->id);
            $this->addTOBadges($user->id);
            $this->addNDBBadges($user->id);
            $this->addVideoBadge($user->id);
            $this->addCommunityBuilder($user->id);
            $this->addSensieActor($user->id);
        }

        $badgesAfter = DB::table('badge_user')->count();
        $endTime = microtime(true);
        return redirect()->route('admin')->with('message', 'Badges added: '.($badgesAfter-$badgesBefore).
            ' - time taken: '.date("i:s",$endTime-$startTime));
    }

    /**
     * Adds all claim based badges for user.
     * @param $userid
     */
    public function addClaimBadges($userid) {
        // prepare badges array
        $fromYear = 2016; $toYear = 2018;
        $badges = Badge::where('year', '>=', $fromYear)->where('year', '<=', $toYear)->pluck('id')->all();
        $badges = array_merge([13, 14, 15, 93, 94, 27, 28, 29, 30, 34, 35, 36, 49, 50, 51, 52, 53, 54, 55, 73, 74, 75, 76, 77, 78, 79, 80, 81], $badges);
        $badges = array_combine($badges, array_fill(1, count($badges), false));

        for ($year = $fromYear; $year <= $toYear; $year++) {
            $this->addChampionshipBadges($userid, $year, 5, $badges); // worlds
            $this->addChampionshipBadges($userid, $year, 4, $badges); // nationals
            $this->addChampionshipBadges($userid, $year, 3, $badges); // regionals
        }
        $this->addChampionshipBadges($userid, null, 2, $badges);    // store champion
        $this->addChampionshipBadges($userid, 2017, 9, $badges, [82]);    // 2017 european championship
        $this->addChampionshipBadges($userid, 2018, 9, $badges, [998]);    // 2018 european championship
        $this->addChampionshipBadges($userid, 2017, 10, $badges, [617]);    // 2017 north american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2018, 10, $badges, [1542]);    // 2018 north american championship, tournament_type_id is a hack
        $this->addPlayerLevelBadges($userid, $badges);
        $this->addFactionBadges($userid, $badges);
        $this->addRecurringBadge($userid, $badges);
        $this->addRoadBadge($userid, $badges);
        $this->addCOS($userid, $badges);
        $this->addTravellerPlayer($userid, $badges);
        $this->addCharity($userid, $badges); // won't be deleted
        $this->addNationalBadges($userid, $badges);

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
        $this->addFeaturedBadge($userid);

        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Adds tournament badges to badge list
     * @param $userid
     * @year
     * @type
     * @badges badge list
     * @tounamentIds
     */
    private function addChampionshipBadges($userid, $year, $type, &$badges, $tounamentIds = []) {
        if (count($tounamentIds) == 0) {
            if (is_null($year)) {
                $tounamentIds = Tournament::where('tournament_type_id', $type)->where('approved', 1)->pluck('id');
            } else {
                $tounamentIds = Tournament::where('tournament_type_id', $type)
                    ->where('date', '>', $year)->where('date', '<', ($year + 1))->where('approved', 1)->pluck('id');
            }
        }

        // winner
        $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', 1)->where('type', 3)->first();
        // maybe there was no top-cut
        if (!$found) {
            $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->whereNull('rank_top')
                ->where('rank',1)->where('type', 3)->first();
        }

        if ($found) {
            if (is_null($year)) {
                $badgeid = Badge::where('tournament_type_id', $type)->where('winlevel', 1)->first()->id;
            } else {
                $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 1)->first()->id;
            }
            $badges[$badgeid] = true;
        } elseif ($type > 2) {

            // top 16
            $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', '>', 0)->where('type', 3)->first();

            if ($found) {
                $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 2)->first()->id;
                $badges[$badgeid] = true;
            } elseif ($type == 5 || $type == 9 || $type == 10) {
                // participation
                $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('runner_deck_id', '>', 0)->where('type', 3)->first();
                if ($found) {
                    $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 5)->first()->id;
                    $badges[$badgeid] = true;
                }
            }
        }
    }

    private function addTOLevelBadges($userid, &$badges) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->count();
        if ($count >= 50) {
            $badges[94] = true; // PLATINUM T.O.
        } elseif ($count >= 20) {
            $badges[18] = true; // GOLD T.O.
        } elseif ($count >= 8) {
            $badges[17] = true;   // SILVER T.O.
        } elseif ($count >= 2) {
            $badges[16] = true;   // BRONZE T.O.
        }
    }

    private function addPlayerLevelBadges($userid, &$badges) {
        $count = Entry::where('user', $userid)->where('type', 3)->where('type', 3)->count();
        if ($count >= 50) {
            $badges[93] = true;   // PLATINUM player
        } elseif ($count >= 20) {
            $badges[15] = true;   // GOLD player
        } elseif ($count >= 8) {
            $badges[14] = true;   // SILVER player
        } elseif ($count >= 2) {
            $badges[13] = true;   // BRONZE player
        }
    }

    private function addFactionBadges($userid, &$badges) {
        $adamId = '09037';
        $apexId = '09029';
        $sunnyId = '09045';
        $mini = Entry::where('user', $userid)->where('type', 3)
            ->whereIn('runner_deck_identity', [$adamId, $apexId, $sunnyId])->where('type', 3)->first();
        if ($mini) {
            $badges[27] = true; // minority report
        }

        $shapers = CardIdentity::where('faction_code','shaper')->pluck('id');
        $crims = CardIdentity::where('faction_code','criminal')->pluck('id');
        $anarchs = CardIdentity::where('faction_code','anarch')->pluck('id');
        $shaperCount = Entry::where('user', $userid)->whereIn('runner_deck_identity', $shapers)->where('type', 3)->count();
        $crimCount = Entry::where('user', $userid)->whereIn('runner_deck_identity', $crims)->where('type', 3)->count();
        $anarchCount = Entry::where('user', $userid)->whereIn('runner_deck_identity', $anarchs)->where('type', 3)->count();
        $adamCount = Entry::where('user', $userid)->where('runner_deck_identity', $adamId)->where('type', 3)->count();
        $apexCount = Entry::where('user', $userid)->where('runner_deck_identity', $apexId)->where('type', 3)->count();
        $sunnyCount = Entry::where('user', $userid)->where('runner_deck_identity', $sunnyId)->where('type', 3)->count();

        if ($shaperCount && $crimCount && $anarchCount) {
            $badges[28] = true; // self-modifying personality
        }

        $nbn = CardIdentity::where('faction_code','nbn')->pluck('id');
        $hb = CardIdentity::where('faction_code','haas-bioroid')->pluck('id');
        $weyland = CardIdentity::where('faction_code','weyland-cons')->pluck('id');
        $jinteki = CardIdentity::where('faction_code','jinteki')->pluck('id');
        $nbnCount = Entry::where('user', $userid)->whereIn('corp_deck_identity', $nbn)->where('type', 3)->count();
        $hbCount = Entry::where('user', $userid)->whereIn('corp_deck_identity', $hb)->where('type', 3)->count();
        $weylandCount = Entry::where('user', $userid)->whereIn('corp_deck_identity', $weyland)->where('type', 3)->count();
        $jintekiCount = Entry::where('user', $userid)->whereIn('corp_deck_identity', $jinteki)->where('type', 3)->count();

        if ($nbnCount && $hbCount && $weylandCount && $jintekiCount) {
            $badges[29] = true; // diversified portfolio
        }

        // mastery badges
        $tournamentIDsTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where('top_number', '>', 0)->where('concluded', 1)->pluck('id');
        $tournamentIDsNoTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where(function($q){
                $q->whereNull('top_number')->orWhere('top_number', 0);
            })->where('concluded', 1)->pluck('id');

        //  runners
        if ($shaperCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('runner_deck_identity', $shapers)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('runner_deck_identity', $shapers)->first())) {
            $badges[53] = true;
        }
        if ($crimCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('runner_deck_identity', $crims)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('runner_deck_identity', $crims)->first())) {
            $badges[54] = true;
        }
        if ($anarchCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('runner_deck_identity', $anarchs)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('runner_deck_identity', $anarchs)->first())) {
            $badges[55] = true;
        }

        // runners, mini
        if ($adamCount > 2 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->where('runner_deck_identity', $adamId)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->where('runner_deck_identity', $adamId)->first())) {
            $badges[73] = true;
        }
        if ($apexCount > 2 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->where('runner_deck_identity', $apexId)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->where('runner_deck_identity', $apexId)->first())) {
            $badges[74] = true;
        }
        if ($sunnyCount > 2 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->where('runner_deck_identity', $sunnyId)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->where('runner_deck_identity', $sunnyId)->first())) {
            $badges[75] = true;
        }

        // corps
        if ($nbnCount > 4 &&
                (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('corp_deck_identity', $nbn)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                        ->where('rank', 1)->whereIn('corp_deck_identity', $nbn)->first())) {
            $badges[49] = true;
        }
        if ($hbCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('corp_deck_identity', $hb)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('corp_deck_identity', $hb)->first())) {
            $badges[50] = true;
        }
        if ($weylandCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('corp_deck_identity', $weyland)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('corp_deck_identity', $weyland)->first())) {
            $badges[51] = true;
        }
        if ($jintekiCount > 4 &&
            (Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsTop)
                    ->where('rank_top', 1)->whereIn('corp_deck_identity', $jinteki)->first() ||
                Entry::where('user', $userid)->where('type', 3)->whereIn('tournament_id', $tournamentIDsNoTop)
                    ->where('rank', 1)->whereIn('corp_deck_identity', $jinteki)->first())) {
            $badges[52] = true;
        }
    }

    private function addRoadBadge($userid, &$badges) {
        $stores = Tournament::where('tournament_type_id', 2)->where('approved', 1)->pluck('id');
        $regionals = Tournament::where('tournament_type_id', 3)->where('approved', 1)->pluck('id');
        $nationals = Tournament::where('tournament_type_id', 4)->where('approved', 1)->pluck('id');

        if (Entry::where('user', $userid)->whereIn('tournament_id', $stores)->where('rank', '>', 0)->where('type', 3)->first() &&
            Entry::where('user', $userid)->whereIn('tournament_id', $regionals)->where('rank', '>', 0)->where('type', 3)->first() &&
            Entry::where('user', $userid)->whereIn('tournament_id', $nationals)->where('rank', '>', 0)->where('type', 3)->first()) {
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
        $badges = [21 => false, 25 => false, 39 => false, 31 => false, 32 => false, 33 => false, 72 => false];

        if ($user->published_decks >= 20) {
            $badges[21] = true; // Hard-working publisher
        }
        if ($user->private_decks >= 150) {
            $badges[25] = true;   // Keeper of many secrets
        }
        if ($user->reputation >= 5000) {
            $badges[39] = true;   // NetrunnerDB Superstar
        } elseif ($user->reputation >= 1000) {
            $badges[31] = true;   // NetrunnerDB VIP
        } elseif ($user->reputation >= 500) {
            $badges[32] = true;  // NetrunnerDB Celeb
        } elseif ($user->reputation >= 100) {
            $badges[33] = true;   // NetrunnerDB Known
        }

        // ABR birthday badge
        if ($user->created_at->format('Y-m-d') <= (date('Y')-1).date('-m-d')) {
            $badges[72] = true;
        }

        $this->refreshUserBadges($userid, $badges);
    }

    public function addVideoBadge($userid) {
        $badges = [47 => false, 666 => false]; // array has to have at least two elements
        if (Video::where('user_id', $userid)->where('flag_removed', false)->count() >= 5) {
            $badges[47] = true;
        }
        $this->refreshUserBadges($userid, $badges);
    }

    public function addFeaturedBadge($userid) {
        $user = User::findOrFail($userid);
        if (is_null($user->badges()->where('badge_id', 63)->first()) &&
            Tournament::where('creator', $userid)->where('featured', 1)->first()) {
                $user->badges()->attach(63);
        }
    }

    private function addNRTMBadge($userid, &$badges) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->where('import', 1)->count();
        if ($count >= 3) {
            $badges[26] = true; // NRTM preacher
        }
    }

    private function addFancyTOBadge($userid, &$badges) {
        $tournaments = Tournament::where('creator', $userid)->where('approved', 1)->get();
        foreach ($tournaments as $tournament) {
            if (strlen($tournament->description) > 600 &&
                preg_match('/[^!]\[([^\]]+)\]\(([^)]+)\)/', $tournament->description) && //link
                preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $tournament->description)) { //image
                    $badges[20] = true; // Fancy T.O.
                    break;
            }
        }
    }

    private function addRecurringBadge($userid, &$badges) {
        $recurring = Tournament::where('recur_weekly', '>', 0)->where('approved', 1)->pluck('id');
        if (Entry::where('user', $userid)->whereIn('tournament_id', $recurring)->first()) {
            $badges[30] = true; // trapped in time
        }
    }

    private function addCOS($userid, &$badges) {
        $tournaments = Tournament::whereIn('tournament_type_id', [1, 6, 7, 10])->where('players_number', '>', 7)->where('approved', 1)->pluck('id');
        $entries = Entry::where('user', $userid)->whereIn('tournament_id', $tournaments)->where('type', 3)->get();
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
                ->where('approved', 1)->first() &&
            Tournament::where('creator', $userid)->where('tournament_type_id', 3)->where('concluded', 1)
                ->where('approved', 1)->first() &&
            Tournament::where('creator', $userid)->where('tournament_type_id', 4)->where('concluded', 1)
                ->where('approved', 1)->first()) {
                $badges[37] = true; // community champion
        }
    }

    private function addCharity($userid, &$badges) {
        $charities = Tournament::where('charity', 1)->where('approved', 1)->pluck('id');
        if (Entry::where('user', $userid)->whereIn('tournament_id', $charities)->where('rank', '>', 0)
            ->where('type', 3)->first()) {
                $badges[38] = true; // charity
        }
    }

    public function addCommunityBuilder($userid) {
        $badges = [48 => false, 68 => false];
        $tournamentIDs = Tournament::where('creator', $userid)->where('approved', 1)->pluck('id');
        $count = Entry::whereIn('tournament_id', $tournamentIDs)->whereIn('type', [3,4])->where('user', '!=', $userid)
            ->distinct()->count('user');

        if ($count > 29) {
            $badges[68] = true;
        } elseif ($count > 9) {
            $badges[48] = true;
        }

        $this->refreshUserBadges($userid, $badges);
    }

    public function addSensieActor($userid) {
        $badges = [64 => false, 666 => false]; // array has to have at least two elements

        if (VideoTag::where('user_id', $userid)->count() > 4) {
            $badges[64] = true;
        }

        $this->refreshUserBadges($userid, $badges);
    }

    // national community awards
    public function addNationalBadges($userid, &$badges) {
        $badge_list = [
            ['tournament_id' => 69, 'badges' => ['winner_badge_id' => 78, 'participant_badge_id' => 79]],    // UK 2016
            ['tournament_id' => 1026, 'badges' => ['winner_badge_id' => 76, 'participant_badge_id' => 77]],    // UK 2017
            ['tournament_id' => 782, 'badges' => ['winner_badge_id' => 80, 'participant_badge_id' => 81]],    // HU 2017
        ];

        foreach($badge_list as $tournament) {
            $event = Tournament::find($tournament['tournament_id']);

            if ($event) {
                // winner
                if ($event && $event->top_number > 0) {
                    $winner = Entry::where('tournament_id', $event->id)->where('rank_top', 1)->where('type', 3)->first();
                } else {
                    $winner = Entry::where('tournament_id', $event->id)->where('rank', 1)->where('type', 3)->first();
                }
                if ($winner && $winner->user == $userid) {
                    $badges[$tournament['badges']['winner_badge_id']] = true;
                } else {

                    // participants
                    $player = Entry::where('tournament_id', $event->id)->where('type', 3)->where('user', $userid)->first();
                    if ($player) {
                        $badges[$tournament['badges']['participant_badge_id']] = true;
                    }
                }
            }
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
