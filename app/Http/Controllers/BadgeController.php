<?php

namespace App\Http\Controllers;

use App\Jobs\RefreshBadgesJob;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class BadgeController extends Controller
{
    public const REFRESH_STATUS_CACHE_PREFIX = 'badge_refresh:';
    public const REFRESH_STATUS_TTL_HOURS = 6;
    public const REFRESH_ACTIVE_RUN_CACHE_KEY = 'badge_refresh:active_run';

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
        $badges_worlds_staff = Badge::where('tournament_type_id', 5)->where('winlevel', 9)->orderBy('order','desc');
        $europe_winner = Badge::where('tournament_type_id', 9)->where('winlevel', 1)->orderBy('order','desc');
        $europe_top16 = Badge::where('tournament_type_id', 9)->where('winlevel', 2)->orderBy('order','desc');
        $europe_player = Badge::where('tournament_type_id', 9)->where('winlevel', 5)->orderBy('order','desc');
        $namerica_winner = Badge::where('tournament_type_id', 10)->where('winlevel', 1)->orderBy('order','desc');
        $namerica_top16 = Badge::where('tournament_type_id', 10)->where('winlevel', 2)->orderBy('order','desc');
        $namerica_player = Badge::where('tournament_type_id', 10)->where('winlevel', 5)->orderBy('order','desc');
        $apac_winner = Badge::where('tournament_type_id', 11)->where('winlevel', 1)->orderBy('order','desc');
        $apac_top16 = Badge::where('tournament_type_id', 11)->where('winlevel', 2)->orderBy('order','desc');
        $apac_player = Badge::where('tournament_type_id', 11)->where('winlevel', 5)->orderBy('order','desc');
        $nationals_winner = Badge::where('tournament_type_id', 4)->where('winlevel', 1)->orderBy('order','desc');
        $nationals_top = Badge::where('tournament_type_id', 4)->where('winlevel', 2)->orderBy('order','desc');
        $regionals_winner = Badge::where('tournament_type_id', 3)->where('winlevel', 1)->orderBy('order','desc');
        $regionals_top = Badge::where('tournament_type_id', 3)->where('winlevel', 2)->orderBy('order','desc');
        $comm_uk_winner = Badge::where('order', '>', 8100)->where('order', '<', 8199)->where('winlevel', 1)->orderBy('order','desc');
        $comm_uk_player = Badge::where('order', '>', 8100)->where('order', '<', 8199)->where('winlevel', 2)->orderBy('order','desc');
        $comm_hun_winner = Badge::where('order', '>', 8200)->where('order', '<', 8299)->where('winlevel', 1)->orderBy('order','desc');
        $comm_hun_player = Badge::where('order', '>', 8200)->where('order', '<', 8299)->where('winlevel', 2)->orderBy('order','desc');
        $other_community = Badge::where('order', '>', 8300)->where('order', '<', 8399)->orderBy('order','desc');

        return view('badges', compact([
            'badges', 'badges_worlds_winner', 'badges_worlds_top16', 'badges_worlds_player', 'badges_worlds_staff',
            'europe_winner', 'europe_top16', 'europe_player',
            'namerica_winner', 'namerica_top16', 'namerica_player',
            'apac_winner', 'apac_top16', 'apac_player',
            'nationals_winner', 'nationals_top', 'regionals_winner', 'regionals_top',
            'comm_uk_winner', 'comm_uk_player', 'comm_hun_winner', 'comm_hun_player', 'other_community'
        ]));
    }

    /**
     * Recalculates badges for all users, for admins.
     */
    public function refreshBadges(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        $activeRunId = Cache::get(self::REFRESH_ACTIVE_RUN_CACHE_KEY);
        if ($activeRunId) {
            $activeStatus = Cache::get(self::refreshStatusCacheKey($activeRunId));
            if ($activeStatus && in_array($activeStatus['status'] ?? '', ['queued', 'running'])) {
                return redirect()->route('admin', ['badge_refresh_run' => $activeRunId])
                    ->with('message', 'Badge refresh is already running. Run ID: '.$activeRunId);
            }
        }

        $runId = (string) Str::uuid();
        $status = [
            'run_id' => $runId,
            'status' => 'queued',
            'queued_at' => now()->toDateTimeString(),
            'started_at' => null,
            'finished_at' => null,
            'duration_seconds' => null,
            'badges_before' => null,
            'badges_after' => null,
            'badges_added' => null,
            'users_total' => null,
            'users_processed' => 0,
            'error' => null,
        ];

        Cache::put(
            self::refreshStatusCacheKey($runId),
            $status,
            now()->addHours(self::REFRESH_STATUS_TTL_HOURS)
        );
        Cache::put(self::REFRESH_ACTIVE_RUN_CACHE_KEY, $runId, now()->addHours(self::REFRESH_STATUS_TTL_HOURS));

        if (Config::get('queue.default') === 'sync') {
            RefreshBadgesJob::dispatchAfterResponse($runId);
        } else {
            RefreshBadgesJob::dispatch($runId);
        }

        return redirect()->route('admin', ['badge_refresh_run' => $runId])
            ->with('message', 'Badge refresh queued. Run ID: '.$runId);
    }

    public function refreshBadgesStatus($runId, Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $status = Cache::get(self::refreshStatusCacheKey($runId));
        if (!$status) {
            return response()->json(['message' => 'Badge refresh run not found'], 404);
        }

        return response()->json($status);
    }

    public static function refreshStatusCacheKey(string $runId): string
    {
        return self::REFRESH_STATUS_CACHE_PREFIX.$runId;
    }

    public static function releaseActiveRefreshRun(string $runId): void
    {
        $activeRunId = Cache::get(self::REFRESH_ACTIVE_RUN_CACHE_KEY);
        if ($activeRunId === $runId) {
            Cache::forget(self::REFRESH_ACTIVE_RUN_CACHE_KEY);
        }
    }

    /**
     * Adds all claim based badges for user.
     * @param $userid
     */
    public function addClaimBadges($userid) {
        // prepare badges array
        $fromYear = 2016; $toYear = 2022;
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
        $this->addChampionshipBadges($userid, 2019, 9, $badges, [2005]);    // 2019 european championship
        $this->addChampionshipBadges($userid, 2017, 10, $badges, [617]);    // 2017 north american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2018, 10, $badges, [1542]);    // 2018 north american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2020, 9, $badges, [2810]);    // 2020 european+african championship
        $this->addChampionshipBadges($userid, 2020, 10, $badges, [2811]);    // 2020 american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2020, 11, $badges, [2809]);    // 2020 asia-pacific championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2021, 9, $badges, [3014]);    // 2021 european+african championship
        $this->addChampionshipBadges($userid, 2021, 10, $badges, [3015]);    // 2021 american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2021, 11, $badges, [3013]);    // 2021 asia-pacific championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2022, 9, $badges, [3342]);    // 2022 european+african championship
        $this->addChampionshipBadges($userid, 2022, 10, $badges, [3341]);    // 2022 american championship, tournament_type_id is a hack
        $this->addChampionshipBadges($userid, 2022, 11, $badges, [3340]);    // 2022 asia-pacific championship, tournament_type_id is a hack
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
        $badges = [16 => false, 17 => false, 18 => false, 26 => false, 20 => false, 37 => false, 111 => false];

        $this->addTOLevelBadges($userid, $badges);
        $this->addNRTMBadge($userid, $badges);
        $this->addCobraiBadge($userid, $badges);
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
                    ->where('date', '>', $year)->where('date', '<', ($year + 1).'.03')->where('approved', 1)->pluck('id'); // adding '.03' to year to compensate for late 2019 nationals
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
                $badgeid = Badge::where('tournament_type_id', $type)->where('winlevel', 1)->first();
            } else {
                $badgeid = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 1)->first();
            }
            if ($badgeid) {
                $badges[$badgeid->id] = true;
            }
        } elseif ($type > 2) {

            // top cut
            $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank_top', '>', 0)->where('type', 3)->first();
            if ($year == 2019 && $type ==9) {
                // Euro 2019 "day 2", not "top-cut"
                $found = Entry::where('user', $userid)->whereIn('tournament_id', $tounamentIds)->where('rank', '<', 14)->where('type', 3)->first();
            }

            if ($found) {
                $found = Badge::where('tournament_type_id', $type)->where('year', $year)->where('winlevel', 2)->first();
                if ($found) {
                    $badgeid = $found->id;
                    $badges[$badgeid] = true;
                }
            } elseif ($type == 5 || $type == 9 || $type == 10 || $type == 11) {
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
        if ((!is_null($user->created_at)) && ($user->created_at->format('Y-m-d') <= (date('Y')-1).date('-m-d'))) {
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

    private function addCobraiBadge($userid, &$badges) {
        $count = Tournament::where('creator', $userid)->where('approved', 1)->where('import', 4)->count();
        if ($count >= 3) {
            $badges[111] = true; // Snek Majesty
        }
    }

    private function addFancyTOBadge($userid, &$badges) {
        $tournaments = Tournament::where('creator', $userid)->where('approved', 1)->get();
        foreach ($tournaments as $tournament) {
            if (strlen((string) $tournament->description) > 600 &&
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
            ->selectRaw('*, count(*)')->where('entries.user', $userid)->where('tournaments.online', 0)
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
            ['tournament_id' => 1823, 'badges' => ['winner_badge_id' => 98, 'participant_badge_id' => 99]],    // HU 2018
            ['tournament_id' => 3330, 'badges' => ['winner_badge_id' => 158, 'participant_badge_id' => 159]],    // German Nat Team 2022
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
     * Build reusable datasets for full badge refresh runs.
     */
    public function buildFullRefreshContext(): array
    {
        $fromYear = 2016;
        $toYear = 2022;

        $claimBaseBadgeIds = Badge::where('year', '>=', $fromYear)
            ->where('year', '<=', $toYear)
            ->pluck('id')
            ->all();
        $claimBaseBadgeIds = array_values(array_unique(array_merge([
            13, 14, 15, 93, 94, 27, 28, 29, 30, 34, 35, 36,
            49, 50, 51, 52, 53, 54, 55, 73, 74, 75, 76, 77, 78, 79, 80, 81
        ], $claimBaseBadgeIds)));

        $badgeLookup = [];
        $badgeLookupAnyYear = [];
        foreach (Badge::select('id', 'tournament_type_id', 'year', 'winlevel')->orderBy('id')->get() as $badge) {
            $yearKey = is_null($badge->year) ? 'null' : (string) $badge->year;
            $badgeLookup[$badge->tournament_type_id][$yearKey][$badge->winlevel] = $badge->id;
            if (!isset($badgeLookupAnyYear[$badge->tournament_type_id][$badge->winlevel])) {
                $badgeLookupAnyYear[$badge->tournament_type_id][$badge->winlevel] = $badge->id;
            }
        }

        $championshipConfigs = [];
        for ($year = $fromYear; $year <= $toYear; $year++) {
            foreach ([5, 4, 3] as $type) {
                $championshipConfigs[] = ['key' => $type.'_'.$year, 'type' => $type, 'year' => $year, 'ids' => []];
            }
        }
        $championshipConfigs[] = ['key' => '2_null', 'type' => 2, 'year' => null, 'ids' => []];
        $championshipConfigs[] = ['key' => '9_2017', 'type' => 9, 'year' => 2017, 'ids' => [82]];
        $championshipConfigs[] = ['key' => '9_2018', 'type' => 9, 'year' => 2018, 'ids' => [998]];
        $championshipConfigs[] = ['key' => '9_2019', 'type' => 9, 'year' => 2019, 'ids' => [2005]];
        $championshipConfigs[] = ['key' => '10_2017', 'type' => 10, 'year' => 2017, 'ids' => [617]];
        $championshipConfigs[] = ['key' => '10_2018', 'type' => 10, 'year' => 2018, 'ids' => [1542]];
        $championshipConfigs[] = ['key' => '9_2020', 'type' => 9, 'year' => 2020, 'ids' => [2810]];
        $championshipConfigs[] = ['key' => '10_2020', 'type' => 10, 'year' => 2020, 'ids' => [2811]];
        $championshipConfigs[] = ['key' => '11_2020', 'type' => 11, 'year' => 2020, 'ids' => [2809]];
        $championshipConfigs[] = ['key' => '9_2021', 'type' => 9, 'year' => 2021, 'ids' => [3014]];
        $championshipConfigs[] = ['key' => '10_2021', 'type' => 10, 'year' => 2021, 'ids' => [3015]];
        $championshipConfigs[] = ['key' => '11_2021', 'type' => 11, 'year' => 2021, 'ids' => [3013]];
        $championshipConfigs[] = ['key' => '9_2022', 'type' => 9, 'year' => 2022, 'ids' => [3342]];
        $championshipConfigs[] = ['key' => '10_2022', 'type' => 10, 'year' => 2022, 'ids' => [3341]];
        $championshipConfigs[] = ['key' => '11_2022', 'type' => 11, 'year' => 2022, 'ids' => [3340]];

        $championshipSets = [];
        foreach ($championshipConfigs as $config) {
            if (count($config['ids'])) {
                $ids = $config['ids'];
            } elseif (is_null($config['year'])) {
                $ids = Tournament::where('tournament_type_id', $config['type'])
                    ->where('approved', 1)
                    ->pluck('id')
                    ->all();
            } else {
                $ids = Tournament::where('tournament_type_id', $config['type'])
                    ->where('date', '>', $config['year'])
                    ->where('date', '<', ($config['year'] + 1).'.03')
                    ->where('approved', 1)
                    ->pluck('id')
                    ->all();
            }
            $championshipSets[$config['key']] = $this->buildIdSet($ids);
        }

        $identitiesByFaction = [
            'shaper' => $this->buildIdSet(CardIdentity::where('faction_code', 'shaper')->pluck('id')->all()),
            'criminal' => $this->buildIdSet(CardIdentity::where('faction_code', 'criminal')->pluck('id')->all()),
            'anarch' => $this->buildIdSet(CardIdentity::where('faction_code', 'anarch')->pluck('id')->all()),
            'nbn' => $this->buildIdSet(CardIdentity::where('faction_code', 'nbn')->pluck('id')->all()),
            'hb' => $this->buildIdSet(CardIdentity::where('faction_code', 'haas-bioroid')->pluck('id')->all()),
            'weyland' => $this->buildIdSet(CardIdentity::where('faction_code', 'weyland-cons')->pluck('id')->all()),
            'jinteki' => $this->buildIdSet(CardIdentity::where('faction_code', 'jinteki')->pluck('id')->all()),
            'adam' => [$this->setKey('09037') => true],
            'apex' => [$this->setKey('09029') => true],
            'sunny' => [$this->setKey('09045') => true],
        ];

        $tournamentIdsTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where('top_number', '>', 0)->where('concluded', 1)->pluck('id')->all();
        $tournamentIdsNoTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where(function($q) {
                $q->whereNull('top_number')->orWhere('top_number', 0);
            })->where('concluded', 1)->pluck('id')->all();

        $nationalBadges = [
            ['tournament_id' => 69, 'winner_badge_id' => 78, 'participant_badge_id' => 79],
            ['tournament_id' => 1026, 'winner_badge_id' => 76, 'participant_badge_id' => 77],
            ['tournament_id' => 782, 'winner_badge_id' => 80, 'participant_badge_id' => 81],
            ['tournament_id' => 1823, 'winner_badge_id' => 98, 'participant_badge_id' => 99],
            ['tournament_id' => 3330, 'winner_badge_id' => 158, 'participant_badge_id' => 159],
        ];
        $nationalInfo = [];
        foreach ($nationalBadges as $cfg) {
            $event = Tournament::find($cfg['tournament_id']);
            if (!$event) {
                continue;
            }

            if ($event->top_number > 0) {
                $winner = Entry::where('tournament_id', $event->id)->where('rank_top', 1)->where('type', 3)->first();
            } else {
                $winner = Entry::where('tournament_id', $event->id)->where('rank', 1)->where('type', 3)->first();
            }

            $participants = Entry::where('tournament_id', $event->id)
                ->where('type', 3)
                ->where('user', '>', 0)
                ->distinct()
                ->pluck('user')
                ->all();

            $nationalInfo[] = [
                'winner_user' => $winner ? $winner->user : null,
                'participants' => $this->buildIdSet($participants),
                'winner_badge_id' => $cfg['winner_badge_id'],
                'participant_badge_id' => $cfg['participant_badge_id'],
            ];
        }

        $recurringIds = Tournament::where('recur_weekly', '>', 0)->where('approved', 1)->pluck('id')->all();

        return [
            'from_year' => $fromYear,
            'to_year' => $toYear,
            'claim_base_badge_ids' => $claimBaseBadgeIds,
            'badge_lookup' => $badgeLookup,
            'badge_lookup_any_year' => $badgeLookupAnyYear,
            'championship_configs' => $championshipConfigs,
            'championship_sets' => $championshipSets,
            'identities_by_faction' => $identitiesByFaction,
            'tournament_top_set' => $this->buildIdSet($tournamentIdsTop),
            'tournament_no_top_set' => $this->buildIdSet($tournamentIdsNoTop),
            'recurring_ids' => $recurringIds,
            'recurring_set' => $this->buildIdSet($recurringIds),
            'charity_set' => $this->buildIdSet(Tournament::where('charity', 1)->where('approved', 1)->pluck('id')->all()),
            'road_stores_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 2)->where('approved', 1)->pluck('id')->all()),
            'road_regionals_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 3)->where('approved', 1)->pluck('id')->all()),
            'road_nationals_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 4)->where('approved', 1)->pluck('id')->all()),
            'cos_set' => $this->buildIdSet(Tournament::whereIn('tournament_type_id', [1, 6, 7, 10])->where('players_number', '>', 7)->where('approved', 1)->pluck('id')->all()),
            'national_info' => $nationalInfo,
        ];
    }

    /**
     * Optimized full refresh path: one read context + one write sync per user.
     */
    public function refreshUserBadgesOptimized(User $user, array $context): void
    {
        $userId = (int) $user->id;
        $badges = [];

        foreach ($context['claim_base_badge_ids'] as $badgeId) {
            $badges[$badgeId] = false;
        }
        foreach ([16, 17, 18, 26, 20, 37, 111, 21, 25, 39, 31, 32, 33, 72, 47, 48, 68, 64] as $badgeId) {
            $badges[$badgeId] = false;
        }

        $entries = Entry::where('user', $userId)->where('type', 3)->get([
            'tournament_id', 'rank', 'rank_top', 'runner_deck_id', 'runner_deck_identity', 'corp_deck_identity'
        ]);

        foreach ($context['championship_configs'] as $config) {
            $set = $context['championship_sets'][$config['key']] ?? [];
            if (empty($set)) {
                continue;
            }

            $winner = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                return intval($entry->rank_top) === 1 || (is_null($entry->rank_top) && intval($entry->rank) === 1);
            });

            if ($winner) {
                $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 1);
                if ($badgeId) {
                    $badges[$badgeId] = true;
                }
                continue;
            }

            if ($config['type'] <= 2) {
                continue;
            }

            $topCut = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                return intval($entry->rank_top) > 0;
            });
            if (!$topCut && $config['year'] == 2019 && $config['type'] == 9) {
                $topCut = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                    return intval($entry->rank) < 14;
                });
            }

            if ($topCut) {
                $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 2);
                if ($badgeId) {
                    $badges[$badgeId] = true;
                }
            } elseif (in_array($config['type'], [5, 9, 10, 11])) {
                $participated = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                    return intval($entry->runner_deck_id) > 0;
                });
                if ($participated) {
                    $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 5);
                    if ($badgeId) {
                        $badges[$badgeId] = true;
                    }
                }
            }
        }

        $entryCount = $entries->count();
        if ($entryCount >= 50) {
            $badges[93] = true;
        } elseif ($entryCount >= 20) {
            $badges[15] = true;
        } elseif ($entryCount >= 8) {
            $badges[14] = true;
        } elseif ($entryCount >= 2) {
            $badges[13] = true;
        }

        $factions = $context['identities_by_faction'];
        $runnerCounts = ['shaper' => 0, 'criminal' => 0, 'anarch' => 0, 'adam' => 0, 'apex' => 0, 'sunny' => 0];
        $corpCounts = ['nbn' => 0, 'hb' => 0, 'weyland' => 0, 'jinteki' => 0];
        $runnerWinFlags = ['shaper' => false, 'criminal' => false, 'anarch' => false, 'adam' => false, 'apex' => false, 'sunny' => false];
        $corpWinFlags = ['nbn' => false, 'hb' => false, 'weyland' => false, 'jinteki' => false];

        foreach ($entries as $entry) {
            $runnerIdentity = (string) $entry->runner_deck_identity;
            $corpIdentity = (string) $entry->corp_deck_identity;
            $tournamentId = (int) $entry->tournament_id;
            $wonTop = isset($context['tournament_top_set'][$this->setKey($tournamentId)]) && intval($entry->rank_top) === 1;
            $wonNoTop = isset($context['tournament_no_top_set'][$this->setKey($tournamentId)]) && intval($entry->rank) === 1;
            $won = $wonTop || $wonNoTop;

            foreach ($runnerCounts as $key => $count) {
                if (isset($factions[$key][$this->setKey($runnerIdentity)])) {
                    $runnerCounts[$key]++;
                    if ($won) {
                        $runnerWinFlags[$key] = true;
                    }
                }
            }
            foreach ($corpCounts as $key => $count) {
                if (isset($factions[$key][$this->setKey($corpIdentity)])) {
                    $corpCounts[$key]++;
                    if ($won) {
                        $corpWinFlags[$key] = true;
                    }
                }
            }
        }

        if ($runnerCounts['adam'] > 0 || $runnerCounts['apex'] > 0 || $runnerCounts['sunny'] > 0) {
            $badges[27] = true;
        }
        if ($runnerCounts['shaper'] > 0 && $runnerCounts['criminal'] > 0 && $runnerCounts['anarch'] > 0) {
            $badges[28] = true;
        }
        if ($corpCounts['nbn'] > 0 && $corpCounts['hb'] > 0 && $corpCounts['weyland'] > 0 && $corpCounts['jinteki'] > 0) {
            $badges[29] = true;
        }

        if ($runnerCounts['shaper'] > 4 && $runnerWinFlags['shaper']) { $badges[53] = true; }
        if ($runnerCounts['criminal'] > 4 && $runnerWinFlags['criminal']) { $badges[54] = true; }
        if ($runnerCounts['anarch'] > 4 && $runnerWinFlags['anarch']) { $badges[55] = true; }
        if ($runnerCounts['adam'] > 2 && $runnerWinFlags['adam']) { $badges[73] = true; }
        if ($runnerCounts['apex'] > 2 && $runnerWinFlags['apex']) { $badges[74] = true; }
        if ($runnerCounts['sunny'] > 2 && $runnerWinFlags['sunny']) { $badges[75] = true; }
        if ($corpCounts['nbn'] > 4 && $corpWinFlags['nbn']) { $badges[49] = true; }
        if ($corpCounts['hb'] > 4 && $corpWinFlags['hb']) { $badges[50] = true; }
        if ($corpCounts['weyland'] > 4 && $corpWinFlags['weyland']) { $badges[51] = true; }
        if ($corpCounts['jinteki'] > 4 && $corpWinFlags['jinteki']) { $badges[52] = true; }

        if (
            $this->anyEntryMatchesTournamentSet($entries, $context['road_stores_set'], function ($entry) { return intval($entry->rank) > 0; }) &&
            $this->anyEntryMatchesTournamentSet($entries, $context['road_regionals_set'], function ($entry) { return intval($entry->rank) > 0; }) &&
            $this->anyEntryMatchesTournamentSet($entries, $context['road_nationals_set'], function ($entry) { return intval($entry->rank) > 0; })
        ) {
            $badges[34] = true;
        }
        $hasRecurringEntry = !empty($context['recurring_ids']) &&
            Entry::where('user', $userId)->whereIn('tournament_id', $context['recurring_ids'])->exists();
        if ($hasRecurringEntry) {
            $badges[30] = true;
        }
        if ($this->anyEntryMatchesTournamentSet($entries, $context['charity_set'], function ($entry) { return intval($entry->rank) > 0; })) {
            $badges[38] = true;
        }
        if ($this->anyEntryMatchesTournamentSet($entries, $context['cos_set'], function ($entry) {
            return intval($entry->rank_top) === 1 || intval($entry->rank) === 1;
        })) {
            $badges[35] = true;
        }

        $countryCount = DB::table('entries')->join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
            ->where('entries.user', $userId)
            ->where('tournaments.online', 0)
            ->where('tournaments.approved', 1)
            ->whereNull('tournaments.deleted_at')
            ->where('entries.rank', '>', 0)
            ->distinct('tournaments.location_country')
            ->count('tournaments.location_country');
        if ($countryCount >= 3) {
            $badges[36] = true;
        }

        foreach ($context['national_info'] as $nationalInfo) {
            if ($nationalInfo['winner_user'] === $userId) {
                $badges[$nationalInfo['winner_badge_id']] = true;
            } elseif (isset($nationalInfo['participants'][$this->setKey($userId)])) {
                $badges[$nationalInfo['participant_badge_id']] = true;
            }
        }

        $createdApprovedTournaments = Tournament::where('creator', $userId)
            ->where('approved', 1)
            ->get(['id', 'import', 'description', 'featured', 'tournament_type_id', 'concluded']);

        $createdCount = $createdApprovedTournaments->count();
        if ($createdCount >= 50) {
            $badges[94] = true;
        } elseif ($createdCount >= 20) {
            $badges[18] = true;
        } elseif ($createdCount >= 8) {
            $badges[17] = true;
        } elseif ($createdCount >= 2) {
            $badges[16] = true;
        }

        if ($createdApprovedTournaments->where('import', 1)->count() >= 3) {
            $badges[26] = true;
        }
        if ($createdApprovedTournaments->where('import', 4)->count() >= 3) {
            $badges[111] = true;
        }
        foreach ($createdApprovedTournaments as $tournament) {
            if (strlen((string) $tournament->description) > 600 &&
                preg_match('/[^!]\[([^\]]+)\]\(([^)]+)\)/', $tournament->description) &&
                preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $tournament->description)) {
                $badges[20] = true;
                break;
            }
        }

        $concludedByType = $createdApprovedTournaments
            ->where('concluded', 1)
            ->groupBy('tournament_type_id')
            ->map(function ($items) { return $items->count() > 0; });
        if (($concludedByType[2] ?? false) && ($concludedByType[3] ?? false) && ($concludedByType[4] ?? false)) {
            $badges[37] = true;
        }

        $createdTournamentIds = $createdApprovedTournaments->pluck('id')->all();
        if (count($createdTournamentIds)) {
            $communityCount = Entry::whereIn('tournament_id', $createdTournamentIds)
                ->whereIn('type', [3, 4])
                ->where('user', '!=', $userId)
                ->distinct()
                ->count('user');
            if ($communityCount > 29) {
                $badges[68] = true;
            } elseif ($communityCount > 9) {
                $badges[48] = true;
            }
        }

        if ($user->published_decks >= 20) { $badges[21] = true; }
        if ($user->private_decks >= 150) { $badges[25] = true; }
        if ($user->reputation >= 5000) {
            $badges[39] = true;
        } elseif ($user->reputation >= 1000) {
            $badges[31] = true;
        } elseif ($user->reputation >= 500) {
            $badges[32] = true;
        } elseif ($user->reputation >= 100) {
            $badges[33] = true;
        }
        if ((!is_null($user->created_at)) && ($user->created_at->format('Y-m-d') <= (date('Y') - 1).date('-m-d'))) {
            $badges[72] = true;
        }

        if (Video::where('user_id', $userId)->where('flag_removed', false)->count() >= 5) {
            $badges[47] = true;
        }
        if (VideoTag::where('user_id', $userId)->count() > 4) {
            $badges[64] = true;
        }

        $this->refreshUserBadges($userId, $badges);

        $hasFeaturedTournament = Tournament::where('creator', $userId)->where('featured', 1)->exists();
        if ($hasFeaturedTournament) {
            $alreadyHasFeatured = DB::table('badge_user')
                ->where('user_id', $userId)
                ->where('badge_id', 63)
                ->exists();
            if (!$alreadyHasFeatured) {
                DB::table('badge_user')->insert(['user_id' => $userId, 'badge_id' => 63, 'seen' => 0]);
            }
        }
    }

    private function buildIdSet(array $ids): array
    {
        $set = [];
        foreach ($ids as $id) {
            $set[$this->setKey($id)] = true;
        }
        return $set;
    }

    private function setKey($id): string
    {
        return 'k:'.(string) $id;
    }

    private function badgeIdFromLookup(array $context, int $type, $year, int $winlevel): ?int
    {
        $yearKey = is_null($year) ? 'null' : (string) $year;
        if (isset($context['badge_lookup'][$type][$yearKey][$winlevel])) {
            return intval($context['badge_lookup'][$type][$yearKey][$winlevel]);
        }

        // Preserve legacy behavior for "yearless" lookups, which used first() without year filter.
        if (is_null($year) && isset($context['badge_lookup_any_year'][$type][$winlevel])) {
            return intval($context['badge_lookup_any_year'][$type][$winlevel]);
        }

        return null;
    }

    private function anyEntryMatchesTournamentSet($entries, array $tournamentSet, ?callable $predicate = null): bool
    {
        foreach ($entries as $entry) {
            if (!isset($tournamentSet[$this->setKey($entry->tournament_id)])) {
                continue;
            }
            if (is_null($predicate) || $predicate($entry)) {
                return true;
            }
        }
        return false;
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
