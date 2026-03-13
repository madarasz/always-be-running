# Badge Refresh Performance Improvement Plan

## Scope

This document covers performance improvement opportunities for the Admin "Refresh badges" flow:

- Route: `/admin/badges/refresh`
- Controller entrypoint: `BadgeController::refreshBadges()`
- UI trigger: Admin page "Refresh badges" button

Goal: reduce runtime and DB load, improve reliability, and avoid long blocking web requests.

## Current Flow Summary

The current implementation:

1. Runs synchronously in a single HTTP request.
2. Loads all users (`User::all()`).
3. For each user, runs multiple badge refresh functions:
   - claim badges
   - TO badges
   - NDB badges
   - video badge
   - community builder
   - sensie actor
4. Each function performs multiple queries and often writes badge changes immediately.

This creates high query volume and long request duration.

## Main Bottlenecks

## 1) Synchronous "all users" processing in a web request

- `refreshBadges()` runs everything in one request with `set_time_limit(6000)`.
- This is fragile under load and blocks the admin action until completion.

### Impact

- Timeout risk and poor UX.
- Potential pressure on PHP workers and DB during execution.

## 2) `User::all()` loads the full table into memory

- Full user records are loaded at once instead of streamed/chunked.

### Impact

- Unnecessary memory growth.
- Lower throughput on large user counts.

## 3) Repeated global lookups inside each user iteration

Many invariant datasets are fetched repeatedly per user, for example:

- badge ID sets by year/type/winlevel
- tournament ID lists for fixed criteria (approved, recurring, charity, type/year)
- card identity faction lists

### Impact

- Significant duplicate query work.
- Expensive scaling as user count grows.

## 4) Championship logic multiplies queries heavily

`addChampionshipBadges()` is called many times per user (per year/type combinations), and each call can query:

- tournament IDs
- multiple entry existence checks
- badge lookup rows

### Impact

- Likely one of the largest contributors to total runtime.

## 5) Multiple pivot writes per user

`refreshUserBadges()` is called repeatedly for each user (across several badge groups), each time:

- fetching user
- syncing additions
- detaching removals

### Impact

- Many write operations per user instead of one consolidated update.

## 6) Some query patterns are not fully index-aligned

Recent index migrations improved many paths, but the badge refresh workload still has heavy filters such as:

- `entries`: user + type + tournament_id/rank/rank_top + identity fields
- `tournaments`: creator + approved + import/type/concluded/featured
- `badges`: tournament_type_id + year + winlevel
- `badge_user`: user-focused lookups for sync/detach

### Impact

- Potential table/index scans and slower existence/count checks.

## Improvement Opportunities (Prioritized)

## Priority 1: Make refresh asynchronous (Queue-only first step)

Move the admin action from direct execution to queued job processing.

### Changes

- Convert refresh endpoint to enqueue job(s) and return immediately.
- Keep badge calculation logic unchanged in this first step (no algorithm/query optimizations yet).
- Track run status in cache (existing cache component), not in a new DB table.
- Show status/progress on Admin page via polling with a run identifier.

### Feedback model (no new DB table)

Use cache keys like `badge_refresh:{runId}` to store:

- `status`: `queued | running | done | failed`
- `started_at`
- `finished_at`
- `duration_seconds`
- `badges_before`
- `badges_after`
- `badges_added` (same metric currently displayed)
- optional: `users_total`, `users_processed`, `error`

Flow:

1. Admin clicks "Refresh badges".
2. Endpoint generates `runId`, seeds cache status, dispatches job, and returns immediately.
3. Admin page polls status endpoint with `runId`.
4. When done, UI shows final summary (`badges_added` + `duration_seconds`) equivalent to current feedback.

Notes:

- This introduces no new infrastructure component because app cache already exists and is used.
- For real async behavior, a queue worker must be running (sync queue executes inline).

### Benefits

- Removes long blocking request.
- Better operational reliability.
- Preserves current admin feedback (time taken + badges added) in async form.

## Priority 2: Chunk user processing

Replace `User::all()` with chunked iteration (`chunkById`), selecting only needed columns.

### Changes

- Process users in batches (for example, 200-1000 users/chunk).
- Use deterministic ordering and resumable progression.

### Benefits

- Lower memory footprint.
- Smoother DB and worker utilization.

## Priority 3: Consolidate per-user writes

Accumulate all badge decisions for a user, then apply a single refresh/write operation.

### Changes

- Build one combined badge map in memory per user.
- Perform one pivot diff write instead of repeated `sync/detach` per badge group.

### Benefits

- Fewer DB writes.
- Less relation overhead.

## Priority 4: Cache invariant data once per run

Preload and reuse static/reference datasets during one refresh run.

### Candidate caches

- Badge lookup map: `(tournament_type_id, year, winlevel) -> badge_id`.
- Tournament ID groups for fixed criteria.
- Card identities by faction.
- National award event winners/participants.

### Benefits

- Removes repeated queries per user.
- Large reduction in total query count.

## Priority 5: Rewrite heavy checks as set-based queries

Replace repeated `first()/count()` checks with grouped/existence queries that cover more logic in fewer calls.

### Candidate areas

- Faction and mastery badges.
- Championship and participation checks.
- Community/travel/charity style badges.

### Benefits

- Better DB efficiency, especially at higher data volume.

## Priority 6: Complete index coverage for refresh patterns

Validate with `EXPLAIN` and add targeted composite indexes where missing.

### Likely candidates

- `entries(user, type, tournament_id)`
- `entries(user, type, runner_deck_identity)`
- `entries(user, type, corp_deck_identity)`
- `tournaments(creator, approved, import)`
- `tournaments(creator, approved, tournament_type_id, concluded)`
- `badges(tournament_type_id, year, winlevel)`
- `badge_user(user_id, badge_id)` unique (plus `user_id` index if needed)

Note: final index list should be validated against real query plans before adding all candidates.

## Priority 7: Endpoint hygiene and safety

- Prefer POST for refresh trigger over GET.
- Add explicit protection against duplicate concurrent refresh jobs.
- Log run metrics and failures.

## Suggested Rollout Plan

## Phase 1 (current implementation scope for measurement)

1. Queue-based async refresh only.
2. Cache-based status/result reporting (no new DB table).
3. Keep existing badge logic and query patterns unchanged.
4. Capture baseline timing from async run (`duration_seconds`, `badges_added`).

Expected result: UX/reliability improvement while preserving behavior, and a clean baseline for later optimization phases.

## Phase 2 (performance optimization pass)

1. Chunked user iteration.
2. Consolidated per-user badge write.
3. Precompute/cached invariant data for one run.
4. Reduce repeated championship/faction/tournament list queries.
5. Add missing targeted indexes based on `EXPLAIN`.

Expected result: significant DB load reduction and faster total completion time.

## Phase 3 (structural optimization)

1. Convert selected badge rules to set-based SQL calculations.
2. Introduce incremental refresh strategy (refresh only affected users after specific events, periodic full rebuild as safety net).

Expected result: better long-term scaling and lower operational cost.

## Phase 4 (single source of truth for badge rules)

1. Introduce a dedicated badge rule engine/service that computes badge outcomes for a user from one canonical implementation.
2. Make both paths call the same engine:
   - full refresh job (batch mode)
   - event-driven incremental updates (entries/tournaments/videos/oauth/admin actions)
3. Keep one shared badge write path (diff + sync) to avoid behavior drift between code paths.
4. Add regression checks that compare:
   - legacy snapshot vs new engine output (during transition)
   - full refresh vs incremental outcomes for sampled users
5. Remove duplicated/legacy rule implementations from `BadgeController` after parity is proven.

Expected result: correctness stability, easier maintenance, and lower risk of missing badges when optimizing performance.

## Instrumentation Recommendations

Before and after each phase, capture:

- total refresh duration
- users processed
- average and P95 per-user processing time
- total queries executed
- top N slow queries
- number of badge insertions/deletions
- count of rule-output mismatches between full-refresh and incremental paths (for parity tracking in Phase 4)

This will verify real impact and prevent regressions.

## Risks and Caveats

- Refactors must preserve exact badge semantics.
- Queue retries should be idempotent.
- Ensure pivot uniqueness to avoid duplicate badge rows under concurrent writes.
- Apply indexes carefully in production windows (online DDL strategy depends on MySQL version/config).

## Validation Checklist

After each change:

1. Run badge refresh in staging on realistic data volume.
2. Compare before/after badge counts and spot-check users.
3. Confirm no duplicate `badge_user` rows.
4. Verify admin status/progress behavior and failure handling.

## Outcome

The main performance issue is not one single slow query; it is the combination of:

- synchronous full-table processing
- repeated per-user global lookups
- high query multiplicity in championship/faction logic
- repeated per-user writes

A phased approach (async + chunking + consolidation first, then caching/index/set-based optimization) provides the safest path to substantial improvements.
