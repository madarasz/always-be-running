<?php

namespace App\Jobs;

use App\Http\Controllers\BadgeController;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefreshBadgesJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public string $runId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $runId)
    {
        $this->runId = $runId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = BadgeController::refreshStatusCacheKey($this->runId);
        $startTime = microtime(true);
        $queuedStatus = Cache::get($cacheKey, []);
        $queuedAt = $queuedStatus['queued_at'] ?? null;
        $startedAt = now()->toDateTimeString();

        try {
            set_time_limit(6000);

            $badgesBefore = DB::table('badge_user')->count();
            $usersTotal = User::count();
            $usersProcessed = 0;
            $refreshContext = app(BadgeController::class)->buildFullRefreshContext();

            Cache::put($cacheKey, [
                'run_id' => $this->runId,
                'status' => 'running',
                'queued_at' => $queuedAt,
                'started_at' => $startedAt,
                'finished_at' => null,
                'duration_seconds' => null,
                'badges_before' => $badgesBefore,
                'badges_after' => null,
                'badges_added' => null,
                'users_total' => $usersTotal,
                'users_processed' => 0,
                'error' => null,
            ], now()->addHours(BadgeController::REFRESH_STATUS_TTL_HOURS));

            $badgeController = app(BadgeController::class);
            User::select(['id', 'published_decks', 'private_decks', 'reputation', 'created_at'])
                ->orderBy('id')
                ->chunkById(250, function ($users) use (
                    $badgeController,
                    $refreshContext,
                    &$usersProcessed,
                    $usersTotal,
                    $cacheKey,
                    $queuedAt,
                    $startedAt
                ) {
                    foreach ($users as $user) {
                        $badgeController->refreshUserBadgesOptimized($user, $refreshContext);
                        $usersProcessed++;
                    }

                    $status = Cache::get($cacheKey, []);
                    $status['status'] = 'running';
                    $status['queued_at'] = $queuedAt;
                    $status['started_at'] = $startedAt;
                    $status['users_processed'] = $usersProcessed;
                    $status['users_total'] = $usersTotal;
                    Cache::put($cacheKey, $status, now()->addHours(BadgeController::REFRESH_STATUS_TTL_HOURS));
                });

            $badgesAfter = DB::table('badge_user')->count();
            $durationSeconds = round(microtime(true) - $startTime, 3);

            Cache::put($cacheKey, [
                'run_id' => $this->runId,
                'status' => 'done',
                'queued_at' => $queuedAt,
                'started_at' => $startedAt,
                'finished_at' => now()->toDateTimeString(),
                'duration_seconds' => $durationSeconds,
                'badges_before' => $badgesBefore,
                'badges_after' => $badgesAfter,
                'badges_added' => $badgesAfter - $badgesBefore,
                'users_total' => $usersTotal,
                'users_processed' => $usersProcessed,
                'error' => null,
            ], now()->addHours(BadgeController::REFRESH_STATUS_TTL_HOURS));
            BadgeController::releaseActiveRefreshRun($this->runId);
        } catch (Throwable $exception) {
            $status = Cache::get($cacheKey, []);
            $status['status'] = 'failed';
            $status['finished_at'] = now()->toDateTimeString();
            $status['duration_seconds'] = round(microtime(true) - $startTime, 3);
            $status['error'] = $exception->getMessage();
            Cache::put($cacheKey, $status, now()->addHours(BadgeController::REFRESH_STATUS_TTL_HOURS));
            BadgeController::releaseActiveRefreshRun($this->runId);

            throw $exception;
        }
    }
}
