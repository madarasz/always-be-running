<?php

namespace App\Http\Controllers;

use App\Jobs\RefreshBadgesJob;
use Illuminate\Http\Request;
use App\Badge;
use App\Tournament;
use App\User;
use App\Support\BadgeRulesEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Throwable;

class BadgeController extends Controller
{
    public const REFRESH_STATUS_CACHE_PREFIX = 'badge_refresh:';
    public const REFRESH_STATUS_TTL_HOURS = 6;
    public const REFRESH_ACTIVE_RUN_CACHE_KEY = 'badge_refresh:active_run';
    private ?array $cachedBadgeRulesContext = null;

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

        $runId = (string) Str::uuid();
        $ttl = now()->addHours(self::REFRESH_STATUS_TTL_HOURS);
        $claimed = Cache::add(self::REFRESH_ACTIVE_RUN_CACHE_KEY, $runId, $ttl);
        if (!$claimed) {
            $activeRunId = Cache::get(self::REFRESH_ACTIVE_RUN_CACHE_KEY);
            if ($activeRunId) {
                return redirect()->route('admin', ['badge_refresh_run' => $activeRunId])
                    ->with('message', 'Badge refresh is already running. Run ID: '.$activeRunId);
            }

            return redirect()->route('admin')
                ->with('message', 'Badge refresh is already running.');
        }

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
            $ttl
        );

        try {
            if (Config::get('queue.default') === 'sync') {
                RefreshBadgesJob::dispatchAfterResponse($runId);
            } else {
                RefreshBadgesJob::dispatch($runId);
            }
        } catch (Throwable $exception) {
            $status['status'] = 'failed';
            $status['finished_at'] = now()->toDateTimeString();
            $status['error'] = $exception->getMessage();
            Cache::put(self::refreshStatusCacheKey($runId), $status, $ttl);
            self::releaseActiveRefreshRun($runId);
            throw $exception;
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
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'claim');
        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Adds, removes badges related to tournament organizing.
     * @param $userid
     */
    public function addTOBadges($userid) {
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'to');
        $this->addFeaturedBadge($userid);
        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Adds NetrunnerDB related badges to user.
     * These badges are never removed.
     * @param $userid
     */
    public function addNDBBadges($userid) {
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'ndb');
        $this->refreshUserBadges($userid, $badges);
    }

    public function addVideoBadge($userid) {
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'video');
        $this->refreshUserBadges($userid, $badges);
    }

    public function addFeaturedBadge($userid) {
        $user = User::findOrFail($userid);
        if (is_null($user->badges()->where('badge_id', 63)->first()) &&
            Tournament::where('creator', $userid)->where('featured', 1)->first()) {
                $user->badges()->attach(63);
        }
    }

    public function addCommunityBuilder($userid) {
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'community');
        $this->refreshUserBadges($userid, $badges);
    }

    public function addSensieActor($userid) {
        $user = User::findOrFail($userid);
        $badges = $this->badgeRulesEngine()->computeScope($user, $this->badgeRulesContext(), 'sensie');
        $this->refreshUserBadges($userid, $badges);
    }

    /**
     * Build reusable datasets for full badge refresh runs.
     */
    public function buildFullRefreshContext(): array
    {
        return $this->badgeRulesEngine()->buildContext();
    }

    /**
     * Optimized full refresh path: one read context + one write sync per user.
     */
    public function refreshUserBadgesOptimized(User $user, array $context): void
    {
        $userId = (int) $user->id;
        $result = $this->badgeRulesEngine()->computeAll($user, $context);
        $badges = $result['badges'];
        $this->refreshUserBadges($userId, $badges);
        if ($result['has_featured_tournament']) {
            DB::table('badge_user')->insertOrIgnore([
                'user_id' => $userId,
                'badge_id' => 63,
                'seen' => 0,
            ]);
        }
    }

    private function badgeRulesEngine(): BadgeRulesEngine
    {
        return app(BadgeRulesEngine::class);
    }

    private function badgeRulesContext(): array
    {
        if (is_null($this->cachedBadgeRulesContext)) {
            $this->cachedBadgeRulesContext = $this->badgeRulesEngine()->buildContext();
        }
        return $this->cachedBadgeRulesContext;
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
