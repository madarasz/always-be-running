# AlwaysBeRunning Migration Plan: Legacy to Modern Stack

## Overview

Migrate AlwaysBeRunning from Laravel 5.2/PHP 5.5/Gulp to Laravel 11/PHP 8.2/Vite with minimal code changes, validated by E2E tests migrated from Cypress to agent-browser.

## Current State Summary

| Component | Current | Target |
|-----------|---------|--------|
| Laravel | 5.2.* (EOL 2017) | 11.x |
| PHP | >= 5.5.9 | 8.2+ |
| Build | Gulp 3.9.1 + Elixir 5.0 | Vite |
| Frontend | Vue 2.5.17, jQuery 2.2.3, Bootstrap 4-alpha | Vue 2.7 (keep), modernize later |
| E2E Tests | Cypress 7.1.0 + Cucumber | Vitest + agent-browser |
| Routes | Single `app/Http/routes.php` (130+ routes) | `routes/web.php` + `routes/api.php` |
| Models | In `app/` root (17 models) | Move to `app/Models/` |

---

## Decisions Made

1. **Laravel Migration:** MANUAL - follow official Laravel upgrade guides for each version.
2. **OAuth Priority:** CRITICAL - must work at every migration checkpoint.
3. **Test Coverage:** Add Tournament CRUD tests before migration (highest risk area).
4. **Deployment Target:** VPS / Traditional Server (nginx, PHP-FPM, MySQL).

---

## Phase 0: Docker Environment for Legacy Stack - ✅ DONE

**Goal:** Reproducible development environment that works with legacy dependencies.

### Files to Create

1. **`docker-compose.yml`**
   - `php:7.0-fpm` container (Laravel 5.2 compatible)
   - `mysql:5.7` container
   - `node:14` container (for Gulp builds)
   - `nginx:alpine` container

2. **`docker/Dockerfile.php`** - PHP 7.0 with extensions: pdo_mysql, gd, mbstring, composer 1.x

3. **`docker/nginx.conf`** - Nginx config for Laravel

4. **`docker/.env.docker`** - Docker-specific environment variables

### Validation
- [X] `docker-compose up` starts all services
- [X] `php artisan migrate` runs successfully
- [X] `gulp` builds assets — run via Docker (Node 10 + Python 2, required for `node-sass@3`):
  ```bash
  docker compose --profile build run --rm node
  ```
- [X] OAuth login with NetrunnerDB works 
  - **we need localhost redirect URL from NetrunnerDB**
- [X] Application serves at localhost:8000

---

## Phase 1: Migrate E2E Tests from Cypress to agent-browser - ✅ DONE

**Goal:** Modern test suite that validates the application before/during/after Laravel upgrade.

### Current Test Coverage (18 scenarios)
- `auth.feature` (3 scenarios): Login flows, access control
- `upcoming.feature` (6 scenarios): Tournament table, filtering, calendar, map
- `results.feature` (8 scenarios): Results display, pagination, filtering, statistics
- `legal.feature` (1 scenario): Cookie consent

### Test Framework: Vitest + agent-browser

- **Vitest**: Fast, modern test runner with native ESM support
- **agent-browser**: Headless browser automation from Vercel Labs

See **[`.claude/skills/e2e/SKILL.md`](.claude/skills/e2e/SKILL.md)** for setup notes, page object patterns, locator rules, OAuth login helper, parameterized tests, and the Cypress → agent-browser migration table.

### Tasks

1. **Install dependencies** (in `tests/` subdirectory)
   ```bash
   cd tests && npm install
   ```

2. **Directory structure**
   ```
   tests/
   ├── package.json         # Test dependencies (Vitest, agent-browser)
   ├── vitest.config.ts
   ├── e2e/
   │   ├── pages/           # BasePage, UpcomingPage, ResultsPage, OrganizePage, AdminPage, LegalPage
   │   ├── helpers/         # auth.ts
   │   ├── fixtures/        # JSON test data, SQL seed files
   │   ├── setup/           # global-setup.ts
   │   └── tests/           # *.test.ts files
   └── api/                  # Future: API schema validation tests
   ```

### Validation
- [X] All 17 scenarios pass as Vitest tests — **17/17 done**
  - Note: Know the Meta widget will be removed, no test needed
- [X] OAuth login works for regular and admin users (`auth.test.ts`: 3/3 scenarios pass)
- [X] Page objects encapsulate all page interactions (`BasePage`, `UpcomingPage`, `ResultsPage`, `LegalPage`, `OrganizePage`, `AdminPage`)
- [X] Parameterized tests cover filter combinations — using `it.each` for type/country/cardpool/format filters
- [X] Map tests verify Google Maps loads with tournament markers (replaced visual snapshots with DOM assertions)

### CI/CD: GitHub Actions - ✅ DONE

E2E tests run automatically on every push to `master`/`migration`/`migration-e2e-workflow` branches and on pull requests to `master`.

**Status:** ✅ **100% pass rate (90/90 tests)**

**What it does:**
- Spins up the full Docker stack (PHP, nginx, MySQL)
- Loads a lightweight test database (recent tournaments + all referenced users)
- Updates tournament dates for "upcoming" page tests
- Builds frontend assets
- Runs all E2E tests with Playwright/Chromium

**Setup required:**
- GitHub Secrets: E2E_REGULAR_USERNAME, E2E_REGULAR_PASSWORD, E2E_ADMIN_USERNAME, E2E_ADMIN_PASSWORD
- GitHub Secrets: NETRUNNERDB_CLIENT_ID, NETRUNNERDB_CLIENT_SECRET
- GitHub Secrets: GOOGLE_MAPS_API, GOOGLE_FRONTEND_API

**Files:**
- `.github/workflows/main.yml` — Workflow definition
- `tests/fixtures/export-test-db.sh` — Database extraction script (exports users referenced by tournaments/entries)
- `tests/fixtures/test-seed.sql` — Lightweight test database (~1.4MB, 700+ users)

---

## Phase 1b: Add Tournament CRUD Tests - ✅ DONE

**Goal:** Increase test coverage for high-risk write operations before migration.

### Implementation

Created `tests/e2e/tests/tournament-crud.test.ts` with 4 passing tests:
- **creates new tournament** - Creates a tournament with real location (Budapest Contrast Phase via Google Places)
- **edits an existing tournament** - Creates then edits a tournament via the Update button
- **deletes a tournament** - Creates then deletes a tournament via the Delete button
- **concludes a tournament with results** - Creates a past-dated tournament and marks it as concluded

**Key Features:**
- All test tournaments use `[E2E_TEST]` prefix for easy identification
- Dynamic dates (relative to today) to prevent test staleness
- Location search using Google Places Autocomplete
- Cleanup script removes all test data: `tests/e2e/fixtures/cleanup-test-data.sh`

### Implementation Notes
- Tests create their own data during execution (no seeding required)
- Dynamic dates relative to today's date (no mocking needed)
- Tournament deletion uses soft-delete (verified via redirect behavior)
- Test data cleanup: `npm run cleanup` or `./tests/e2e/fixtures/cleanup-test-data.sh`

### Validation
- [X] All new CRUD tests pass (4/4)
- [X] Tournament creation with location works
- [X] Tournament edit works
- [X] Tournament delete works (soft-delete)
- [X] Tournament conclusion works

---

## Phase 1b.2: Tournament Entries Tests - ✅ DONE

**Goal:** Test tournament entry management including claims, registration, manual imports, conflicts, and merging.

### Implementation

Created `tests/e2e/tests/tournament-entries.test.ts` with 6 passing tests:
- **claims spot with decklist** - Claims a concluded tournament spot with deck selection from NetrunnerDB
- **claims spot without decklist** - Claims using identity selection only (no deck links)
- **register and unregister** - Registration for upcoming tournaments
- **manual import and delete** - Tournament creator importing claims manually
- **claim conflict** - Detects conflicts when multiple claims at same rank with different identities
- **claim merging** - Merges user claim with imported entry when identities match

**Key Features:**
- Tests both claim workflows (with/without decks)
- Validates merge logic (same identities = merge, different = conflict)
- Page object methods added to `TournamentDetailsPage.ts`:
  - `clickClaimButton()`, `waitForDecksLoaded()`, `submitClaimWithDecks()`, `submitClaimWithoutDecks()`
  - `hasPlayerClaim()`, `removeClaim()`
  - `clickRegister()`, `clickUnregister()`
  - `openManualImportForm()`, `addManualClaim()`, `hasImportedEntry()`, `deleteImportedEntry()`
  - `hasConflictWarning()`, `getEntryCountAtRank()`

### Validation
- [X] All new entry tests pass (6/6)
- [X] Claiming with decks works
- [X] Claiming without decks works
- [X] Registration/unregistration works
- [X] Manual import works
- [X] Conflict detection works
- [X] Claim merging works

---

## Phase 1c: API Tests with Schema Validation - ✅ DONE

**Goal:** Validate API response contracts before, during, and after Laravel upgrade. Catch regressions instantly without browser overhead.

**Why now:** API contract breakages during migration cascade to UI failures. Catching them at the API layer is faster (10s vs 90s) and pinpoints the issue.

### Approach

Use **native `fetch` + Zod schemas**:
- `fetch` is built into Node 20 (no extra deps)
- Zod provides TypeScript-first schema validation (~50KB)
- Can generate OpenAPI 3.x from Zod later (via `zod-openapi`)

### Directory Structure

```
tests/
├── package.json                     # Shared test dependencies
├── vitest.config.ts                 # Vitest configuration
├── api/                             # API tests (fast, no browser)
│   ├── schemas/                     # Zod schemas
│   │   ├── common.schema.ts
│   │   ├── tournament.schema.ts
│   │   ├── video.schema.ts
│   │   ├── entry.schema.ts
│   │   └── artist.schema.ts
│   ├── helpers/
│   │   └── api-client.ts            # fetch wrapper with schema validation
│   └── tests/
│       ├── tournaments.api.test.ts
│       ├── videos.api.test.ts
│       ├── entries.api.test.ts
│       └── artists.api.test.ts
└── e2e/                             # Browser E2E tests
    ├── pages/
    ├── helpers/
    ├── fixtures/
    └── tests/
```

### Priority Endpoints

| Endpoint | Tests |
|----------|-------|
| `GET /api/videos` | Schema validation, video presence, date ordering |
| `GET /api/tournaments/upcoming` | Schema, date >= today, recurring events |
| `GET /api/tournaments/results` | Schema, concluded flag, pagination |
| `GET /api/entries?id=X` | Schema, rank ordering, error responses |
| `GET /api/artists` | Schema, endorsed ordering |

### Laravel 11 Reusability

The Zod schemas validate the **JSON wire format**, not PHP implementation:

| Phase | Action | Benefit |
|-------|--------|---------|
| **Now** | Define Zod schemas from current API | Establish contract baseline |
| **Phase 2-3** | Run `npm run test:api` after each upgrade | Catch regressions instantly |
| **Post-migration** | Generate OpenAPI from Zod | API docs for Laravel 11 |

When rewriting controllers in Laravel 11, Zod schemas serve as the spec for API Resources.

### Implementation Tasks

1. Install `zod` in `tests/`
2. Create `tests/api/schemas/` with 5 schema files (derived from PHP controllers)
3. Create `tests/api/helpers/api-client.ts` (fetch wrapper)
4. Update `tests/vitest.config.ts` with workspace projects (api vs e2e)
5. Create `tests/api/tests/` with 4 test files
6. Add npm scripts: `test:api`, `test:e2e`

### Validation
- [X] `npm run test:api` passes (all schema tests) - **26 tests passing**
- [X] API tests run fast (~7s) without browser
- [X] Schema validation catches type mismatches (proven during development)
- [X] `npm test` runs both API and E2E tests - **116 total tests**

**Full implementation details:** See `~/.claude/plans/purring-seeking-sparrow.md`

---

## Phase 1d: Performance Tests for API Endpoints and Admin Page - ✅ DONE

**Goal:** Establish performance baselines before migration to detect regressions during Laravel upgrade.

### Background

The admin page (`/admin`) is known to be slow (1-10+ seconds) due to:
- Synchronous external HTTP call to `alwaysberunning.net/ktm/metas.json`
- N+1 queries for VIP users (communityCount() method)
- Multiple separate COUNT queries

API endpoints also lack pagination and have complex relationships that may degrade during migration.

### Directory Structure

```
tests/
├── package.json              # Add test:perf scripts
├── vitest.config.ts          # Add perf project to workspace
└── perf/                     # NEW
    ├── thresholds.ts         # Centralized threshold configuration
    ├── helpers/
    │   └── perf-client.ts    # HTTP client with timing measurement
    ├── tests/
    │   ├── api-endpoints.perf.test.ts
    │   └── admin-page.perf.test.ts
    └── reports/              # Gitignored - baseline JSON files
        └── .gitkeep
```

### Performance Thresholds

| Endpoint | Warn | Fail |
|----------|------|------|
| `/api/tournaments/upcoming` | 2s | 5s |
| `/api/tournaments/results` | 2.5s | 6s |
| `/api/tournaments` | 3s | 8s |
| `/api/adminstats` | 3s | 10s |
| `/api/entries` | 2s | 5s |
| `/api/prizes` | 3s | 8s |
| `/admin` page | 5s | 15s |

### Implementation

1. **API endpoint tests** (`api-endpoints.perf.test.ts`)
   - Test public endpoints with 3 iterations each
   - Warmup request before measurement
   - Calculate mean, p75, p95 statistics

2. **Admin page test** (`admin-page.perf.test.ts`)
   - Browser-based using existing auth helper
   - Reuse `createAuthenticatedBrowser('admin')` from E2E tests
   - Measure `networkidle` timing
   - 2 iterations (expensive browser test)

### Run Commands

```bash
# Run performance tests
cd tests && npm run test:perf

# Save baseline before migration
cd tests && npm run test:perf:baseline

# Run all tests (e2e + perf)
cd tests && npm test
```

### Validation

- [X] `npm run test:perf` passes with all endpoints under fail thresholds (12/12 tests pass)
- [X] Admin page test completes (establishes baseline even if slow - ~5.6s mean)
- [X] Baseline JSON saved to `tests/perf/reports/`
- [X] Tests run in < 60 seconds total (~34s)

---

## Phase 2: Laravel 5.2 → 6.0 Upgrade (Manual)

**Goal:** First major upgrade milestone, modernizing routing and reaching PHP 7.2+.

### Upgrade Path
5.2 → 5.3 → 5.4 → 5.5 → 5.6 → 5.7 → 5.8 → 6.0

**Approach:** Follow official Laravel upgrade guides for each version. Run E2E tests after each version bump.

---

### Step 2.1: Laravel 5.2 → 5.3 - ✅ DONE
**Guide:** https://laravel.com/docs/5.3/upgrade

**PHP Requirement:** >= 5.6.4

**Key Changes:**
1. **Routes restructuring (MAJOR)**
   - Create `routes/web.php` and `routes/api.php`
   - Move routes from `app/Http/routes.php`
   - Update `app/Providers/RouteServiceProvider.php` to load new route files
   - **Note:** API routes keep `web` middleware (not `api`) because they use session-based auth

2. **User model changes**
   ```php
   use Illuminate\Notifications\Notifiable;

   class User extends Authenticatable
   {
       use Notifiable;
   }
   ```

3. **Service Provider boot() signatures (BREAKING)**
   - `EventServiceProvider::boot()` - remove `DispatcherContract $events` parameter
   - `AuthServiceProvider::boot()` - remove `GateContract $gate` parameter
   - `registerPolicies()` no longer takes a parameter

4. **Controller trait changes (BREAKING)**
   - `AuthorizesResources` trait merged into `AuthorizesRequests`
   - Remove `use AuthorizesResources` from `app/Http/Controllers/Controller.php`

5. **Query builder returns Collections**
   - `DB::table()->get()` returns `Collection` instead of array
   - Add `->all()` if array needed

6. **`lists()` renamed to `pluck()` (BREAKING)**
   - `->lists('column')` → `->pluck('column')`
   - Fixed in `UserController.php` and `AdminController.php`

7. **composer.json**
   ```json
   "php": ">=5.6.4",
   "laravel/framework": "5.3.*",
   "laravelcollective/html": "^5.3",
   "webpatser/laravel-countries": "1.5.4"
   ```
   - Pin `webpatser/laravel-countries` to 1.5.4 (dev-master now requires PHP 8.2)
   - Remove explicit `symfony/http-foundation` constraint (Laravel 5.3 needs 3.1.*)
   - Update dev dependencies: `symfony/css-selector` and `symfony/dom-crawler` to `3.1.*`

8. **Composer 2 required**
   - Packagist shutdown Composer 1 support (September 2025)
   - Run `composer self-update --2` in Docker container

9. **config/app.php updates**
   - Add `'name'` key (new in 5.3): `'name' => env('APP_NAME', 'AlwaysBeRunning'),`
   - Register `Illuminate\Notifications\NotificationServiceProvider::class` (required for Notifiable trait)
   - Add `Notification` facade alias

10. **Middleware updates (app/Http/Kernel.php)**
   - Add `SubstituteBindings` middleware to `web` middleware group
   - Add `'bindings'` alias to `api` middleware group
   - Register `'bindings'` route middleware
   - Update `'can'` middleware from `\Illuminate\Foundation\Http\Middleware\Authorize::class` to `\Illuminate\Auth\Middleware\Authorize::class`

**Files Modified:**
- `routes/web.php` - Created (68 web routes)
- `routes/api.php` - Created (32 API routes, prefix added by RouteServiceProvider)
- `app/Providers/RouteServiceProvider.php` - Rewritten for 5.3 pattern
- `app/Providers/EventServiceProvider.php` - boot() signature updated
- `app/Providers/AuthServiceProvider.php` - boot() signature updated
- `app/Http/Controllers/Controller.php` - Removed AuthorizesResources trait
- `app/Http/Controllers/PagesController.php` - Added elimination() method
- `app/Http/Controllers/UserController.php` - `lists()` → `pluck()`
- `app/Http/Controllers/AdminController.php` - `lists()` → `pluck()`
- `app/User.php` - Added Notifiable trait
- `composer.json` - Updated versions and Symfony dev dependencies to 3.1.*
- `config/app.php` - Added 'name' key, NotificationServiceProvider, Notification facade
- `app/Http/Kernel.php` - Added SubstituteBindings middleware, updated 'can' middleware class
- `app/Http/routes.php` - Renamed to `.bak`

**Validation:**
- [X] 116 routes registered (`php artisan route:list`)
- [X] API tests pass (26/26)
- [X] E2E tests pass

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.2: Laravel 5.3 → 5.4 - ✅ DONE
**Guide:** https://laravel.com/docs/5.4/upgrade

**PHP Requirement:** >= 5.6.4

**Implementation (2026-03-06):**
1. **Dependency updates**
   - `laravel/framework`: `5.3.*` → `5.4.*`
   - `laravelcollective/html`: `^5.3` → `^5.4`
   - `phpunit/phpunit`: `~4.0` → `~5.7`
   - Added `laravel/tinker` (`~1.0`) and explicitly registered `Laravel\Tinker\TinkerServiceProvider` (5.4 has no package auto-discovery).
   - Updated Symfony dev constraints to `3.2.*` and ran `composer update -W` in Docker.

2. **Markdown blocker resolved with compatibility adapter**
   - Removed `haleks/laravel-markdown` (hard blocker for 5.4).
   - Added `erusev/parsedown`.
   - Implemented app-local markdown facade adapter so existing `Markdown::convertToHtml(...)` call sites remain unchanged.

3. **Required framework/config housekeeping**
   - Removed legacy compiled class include from `bootstrap/autoload.php`.
   - Added 5.4 `markdown` config section to `config/mail.php`.
   - Renamed queue timeout keys in `config/queue.php`: `expire`/`ttr` → `retry_after`.
   - Removed deprecated `fetch` setting from `config/database.php`.

**Files Modified:**
- `composer.json`
- `composer.lock`
- `bootstrap/autoload.php`
- `config/app.php`
- `config/mail.php`
- `config/queue.php`
- `config/database.php`
- `app/Providers/MarkdownServiceProvider.php` (new)
- `app/Support/Facades/Markdown.php` (new)
- `app/Support/Markdown/MarkdownRenderer.php` (new)

**Validation:**
- [X] `composer update -W` succeeds in Docker with Laravel `5.4.*`
- [X] `php artisan` commands run (`route:list`, `tinker` available)
- [X] `php artisan view:clear` and `php artisan route:clear` run cleanly
- [X] API tests pass (`npm run test:api`) — 26/26
- [X] E2E tests pass (`npm run test:e2e`) — 91/91

**Notes:**
- Abandoned package warnings remain for legacy dependencies (e.g., `sammyk/laravel-facebook-sdk`, `laravelcollective/html`), but they are non-blocking for this step.

---

### Step 2.3: Laravel 5.4 → 5.5.42 (Security Release) - ✅ DONE
**Guide:** https://laravel.com/docs/5.5/upgrade

**PHP Requirement:** >= 7.0.0 (**already satisfied** with PHP 7.1+)

**Implementation (2026-03-07):**
1. **Dependency and Composer script updates**
   - Upgraded framework and core constraints:
     - `laravel/framework`: `5.4.*` → `5.5.42` (pinned)
     - `php`: `>=5.6.4` → `>=7.0.0`
     - `laravelcollective/html`: `^5.4` → `^5.5`
     - `phpunit/phpunit`: `~5.7` → `~6.0`
     - Added `filp/whoops` `~2.0`
   - Replaced optimize-era hooks with 5.5-style autoload discovery:
     - Removed `php artisan optimize` hooks
     - Added `post-autoload-dump` with `Illuminate\\Foundation\\ComposerScripts::postAutoloadDump` + `@php artisan package:discover`

2. **Package compatibility resolution**
   - Replaced incompatible/deprecated Facebook wrapper package usage:
     - Removed `sammyk/laravel-facebook-sdk`
     - Added `facebook/graph-sdk` and app-local adapter `App\Support\Facebook\FacebookClient`
     - Updated `FBController` to use adapter (controller behavior preserved)
   - Removed auth-critical `dev-master` usage:
     - `oriceon/oauth-5-laravel`: `dev-master` → `1.0.5`
     - Added app-local `App\Providers\OAuthServiceProvider` to replace removed `Application::share()` behavior
     - Added `config/oauth.php` compatibility mapping for package config lookup
   - Removed non-critical `dev-master`:
     - `alaouy/youtube`: `dev-master` → `^2.2.6`

3. **Framework behavior alignment + future-prep refactors**
   - Cookie serialization security change applied:
     - `App\Http\Middleware\EncryptCookies::$serialize = false`
   - Request semantics compatibility:
     - Replaced security-sensitive `$request->has(...)` check with `$request->filled(...)` where non-empty semantics are required
   - FormRequest cleanup:
     - Replaced static `Request::get(...)` with instance `$this->input(...)` in request rule builders
   - Safer write-path payload handling:
     - Reduced raw `$request->all()` usage in prize item create/update to explicit field whitelist
   - Config-backed access improvements:
     - NetrunnerDB OAuth redirect URL moved to `config/services.php`
     - FB geocode call switched to `config('services.google.backend_api')`
   - Added Laravel 5.5 command auto-loading in `app/Console/Kernel.php`

4. **Laravel 5.5.42 + Composer 2 compatibility fix**
   - Patched `vendor/laravel/framework/src/Illuminate/Foundation/PackageManifest.php` to support Composer 2 `installed.json` structure (`packages` key), so `package:discover` works on Laravel 5.5.42.

**Files Modified (Step 2.3):**
- `composer.json`
- `composer.lock`
- `config/app.php`
- `config/services.php`
- `config/oauth.php` (new)
- `app/Providers/OAuthServiceProvider.php` (new)
- `app/Support/Facebook/FacebookClient.php` (new)
- `app/Http/Controllers/FBController.php`
- `app/Http/Controllers/NetrunnerDBController.php`
- `app/Http/Controllers/PrizeController.php`
- `app/Http/Middleware/EncryptCookies.php`
- `app/Http/Requests/TournamentRequest.php`
- `app/Http/Requests/ConcludeRequest.php`
- `app/Http/Requests/NRTMRequest.php`
- `app/Console/Kernel.php`
- `vendor/laravel/framework/src/Illuminate/Foundation/PackageManifest.php`

**Validation:**
- [X] `composer update -W` succeeds with Laravel `5.5.42`
- [X] `php artisan package:discover` succeeds
- [X] cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] App reports `Laravel Framework 5.5.42`
- [X] API tests pass: `npm run test:api` (26/26)
- [X] E2E tests pass: `npm run test:e2e` (91/91)
- [X] OAuth login flow works end-to-end in E2E global setup; redirect URL contains expected `client_id`
- [X] Facebook integration path responds via API (`/api/fb/event-title`) with Graph JSON error payload (no framework/runtime crash)
- [~] Direct YouTube metadata smoke call in local env is blocked by invalid local API key (`Error 400 API key not valid`); integration wiring and videos page E2E remain functional
- [X] Tournament create/edit/delete/conclude paths pass (`tournament-crud` E2E: 4/4)

**Notes:**
- This step intentionally causes one-time logout/session invalidation due Laravel 5.5.42 cookie serialization hardening.
- No `dev-master` dependency remains on the auth-critical path.

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.4: Laravel 5.5 → 5.6 - ✅ DONE
**Guide:** https://laravel.com/docs/5.6/upgrade

**PHP Requirement:** >= 7.1.3

**Key Changes (Official 5.6 Upgrade Guide):**
1. **Dependency updates (required)**
   - Update framework/runtime constraints:
     ```json
     "laravel/framework": "5.6.*",
     "php": ">=7.1.3",
     "fideloper/proxy": "^4.0"
     ```
   - Update `phpunit/phpunit` to `^7.0`.
   - If installed, update first-party packages: BrowserKit `4.*`, Dusk `^3.0`, Passport `^6.0`, Scout `^4.0`.

2. **Logging migration (required)**
   - Add new `config/logging.php` and move logging config there.
   - Remove `log` and `log_level` keys from `config/app.php`.
   - If using `configureMonologUsing`, migrate to custom channels / taps.
   - Update type hints from `Illuminate\Log\Writer` / `Illuminate\Contracts\Logging\Log` to `Psr\Log\LoggerInterface` (or `Illuminate\Log\Logger`).

3. **Hashing migration (required)**
   - Add new `config/hashing.php` from the Laravel 5.6 skeleton.
   - Keep `bcrypt` default on PHP 7.1 baseline.

4. **Trusted proxies middleware update (required)**
   - Update `App\Http\Middleware\TrustProxies::$headers` from array style to Symfony bitmask constants (for example `Request::HEADER_X_FORWARDED_ALL`).

5. **Blade / helper encoding behavior change (breaking)**
   - Blade and `e()` now double-encode HTML entities by default.
   - If needed for legacy behavior, use `Blade::withoutDoubleEncoding()` (global) or `e($value, false)` (localized).

6. **Compatibility checks**
   - Verify no remaining `php artisan optimize` usage in scripts (removed in Step 2.3).
   - If tests assert status `200` for directly returned newly-created models from routes, update expectation to `201`.
   - If custom code implements `ValidatesWhenResolved`, rename `validate` to `validateResolved`.

**Implementation (2026-03-08):**
1. **Dependency + lockfile upgrade**
   - Updated runtime constraints in `composer.json`:
     - `php`: `>=7.0.0` → `>=7.1.3`
     - `laravel/framework`: `5.5.42` → `5.6.*`
     - added `fideloper/proxy`: `^4.0`
     - `phpunit/phpunit`: `~6.0` → `^7.0`
   - Ran `composer update -W` in Docker and upgraded framework to `Laravel 5.6.40`.

2. **Logging and hashing migration**
   - Added `config/logging.php` (channel-based config with `stack` default).
   - Removed legacy `'log'` key from `config/app.php`.
   - Added `config/hashing.php` with `bcrypt` default.
   - Added `LOG_CHANNEL=stack` to tracked env templates:
     - `.example.env`
     - `docker/.env.docker`

3. **Trusted proxies middleware update**
   - Added `app/Http/Middleware/TrustProxies.php` extending `Fideloper\Proxy\TrustProxies`.
   - Set proxy headers to `Request::HEADER_X_FORWARDED_ALL`.
   - Registered middleware globally in `app/Http/Kernel.php`.

4. **Blade encoding compatibility guard**
   - Added `Blade::withoutDoubleEncoding()` (guarded with `method_exists`) in `AppServiceProvider::boot()` to preserve legacy rendering behavior where values may already be encoded.

5. **Compatibility checks**
   - No app code uses `configureMonologUsing`, `Illuminate\Log\Writer`, or `Illuminate\Contracts\Logging\Log` type hints.
   - No custom `ValidatesWhenResolved::validate()` implementations found.
   - No active `php artisan optimize` script hooks remain.

**Files Modified (Step 2.4):**
- `composer.json`
- `composer.lock`
- `config/app.php`
- `config/logging.php` (new)
- `config/hashing.php` (new)
- `app/Http/Middleware/TrustProxies.php` (new)
- `app/Http/Kernel.php`
- `app/Providers/AppServiceProvider.php`
- `.example.env`
- `docker/.env.docker`

**Validation:**
- [X] `composer update -W` succeeds with Laravel `5.6.40`
- [X] `php artisan package:discover` succeeds
- [X] cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] `php artisan route:list` succeeds (routes register correctly)
- [X] App reports `Laravel Framework 5.6.40`
- [X] API tests pass: `npm run test:api` (26/26)
- [X] E2E tests pass: `npm run test:e2e` (91/91)

**Notes:**
- E2E required a one-time refresh of cached auth storage state (`tests/e2e/.auth/*.json`) after the framework upgrade so global setup could re-login and persist fresh OAuth sessions.
- Composer update reports legacy abandoned-package warnings (e.g. `laravelcollective/html`, `facebook/graph-sdk`), but they are non-blocking for this step.

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.5: Laravel 5.6 → 5.7 - ✅ DONE
**Guide:** https://laravel.com/docs/5.7/upgrade
**Release Notes:** https://laravel.com/docs/5.7/releases
**Email Verification (optional feature):** https://laravel.com/docs/5.7/verification

**PHP Requirement:** >= 7.1.3

**Implementation (2026-03-08):**
1. **Dependency + lockfile upgrade**
   - Updated `composer.json` framework constraint from `5.6.*` to `5.7.*`.
   - Ran `docker compose exec php composer update -W` and upgraded to `Laravel 5.7.29`.

2. **Required filesystem/assets updates**
   - Added `storage/framework/cache/data`.
   - Updated `storage/framework/cache/.gitignore` to keep `data/` tracked.
   - Added Laravel 5.7 default error SVG assets:
     - `public/svg/403.svg`
     - `public/svg/404.svg`
     - `public/svg/500.svg`
     - `public/svg/503.svg`

3. **Queue env modernization**
   - Updated queue default to support 5.7 naming with backward compatibility:
     - `config/queue.php`: `env('QUEUE_CONNECTION', env('QUEUE_DRIVER', 'sync'))`
   - Updated templates/config for `QUEUE_CONNECTION`:
     - `.example.env`
     - `docker/.env.docker`
     - `phpunit.xml`

4. **Breaking-change compatibility checks**
   - Verified no active Blade `{{ $foo or 'default' }}` syntax usage.
   - Verified no `Route::redirect` usage in active routes.
   - Verified no overrides of `authenticate`, `sendResetResponse`, `sendResetLinkResponse`.
   - Verified no custom implementations of 5.7-changed framework contracts (`Gate`, `Validator`, `Filesystem`, `ConnectionInterface`).

**Files Modified (Step 2.5):**
- `composer.json`
- `composer.lock`
- `config/queue.php`
- `.example.env`
- `docker/.env.docker`
- `phpunit.xml`
- `storage/framework/cache/.gitignore`
- `storage/framework/cache/data/.gitignore` (new)
- `public/svg/403.svg` (new)
- `public/svg/404.svg` (new)
- `public/svg/500.svg` (new)
- `public/svg/503.svg` (new)

**Validation:**
- [X] `composer update -W` succeeds with Laravel `5.7.29`
- [X] `php artisan package:discover` succeeds
- [X] cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] `php artisan route:list` succeeds (routes register correctly)
- [X] App reports `Laravel Framework 5.7.29`
- [X] API tests pass: `npm run test:api` (26/26)
- [X] E2E tests pass: `npm run test:e2e` (91/91)
- [X] OAuth login flow works in E2E global setup (fresh regular/admin sessions saved)

**Notes:**
- E2E required a one-time refresh of cached auth storage state (`tests/e2e/.auth/*.json`) after the framework upgrade so global setup could re-login and persist fresh OAuth sessions.
- Perf tests for this step were intentionally skipped per request.
- Composer update reports legacy abandoned-package warnings (for example `laravelcollective/html`, `facebook/graph-sdk`), but they are non-blocking for this step.

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.6: Laravel 5.7 → 5.8 - ✅ DONE
**Guide:** https://laravel.com/docs/5.8/upgrade
**Release Notes:** https://laravel.com/docs/5.8/releases

**PHP Requirement:** >= 7.1.3

**Implementation (2026-03-08):**
1. **Dependency + lockfile upgrade**
   - Updated `composer.json`:
     - `laravel/framework`: `5.7.*` → `5.8.*`
     - `laravelcollective/html`: `^5.5` → `^5.8`
   - Ran `docker compose exec php composer update -W` and upgraded to `Laravel 5.8.38`.
   - Composer selected 5.8-compatible transitive updates (`nesbot/carbon` 2.x, `vlucas/phpdotenv` 3.x) and removed unused legacy notification-channel packages.

2. **Runtime config/env hygiene for 5.8 immutable env behavior**
   - Replaced runtime `env()` reads in app and Blade code with `config()` / `url()` access:
     - `app/Tournament.php`
     - `app/Http/Controllers/TournamentsController.php`
     - `app/User.php`
     - `app/Http/Controllers/VideosController.php`
     - `resources/views/api.blade.php`
     - `resources/views/personal/tournaments.blade.php`
     - `resources/views/tournaments/modals/claim.blade.php`
   - Added config-backed keys used by runtime code:
     - `config/app.php`: `default_netrunnerdb_claim`
     - `config/services.php`: `twitch.client_id`

3. **5.8 compatibility refactors from identified hotspots**
   - Updated registration password rule to 5.8 default baseline:
     - `app/Http/Controllers/Auth/AuthController.php`: `min:6` → `min:8`
   - Modernized deferred provider pattern:
     - `app/Providers/OAuthServiceProvider.php`: removed `$defer = true`, now implements `Illuminate\Contracts\Support\DeferrableProvider`
   - Replaced deprecated helper usage:
     - `database/factories/ModelFactory.php`: `str_random(...)` → `Illuminate\Support\Str::random(...)`

4. **Required compatibility audits**
   - Verified no app usage of `Cache::put/add/putMany/remember` with integer TTL values (no minute→second migration needed).
   - Verified no app usage of `Cache::lock(...)` manual-release flows.
   - Verified no custom `password.reset` route in active route files.
   - Verified no published `resources/views/vendor/mail/markdown` directory.
   - Verified no Slack/Nexmo notification channel usage in app code.

**Files Modified (Step 2.6):**
- `composer.json`
- `composer.lock`
- `app/Providers/OAuthServiceProvider.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `database/factories/ModelFactory.php`
- `app/Tournament.php`
- `app/User.php`
- `app/Http/Controllers/TournamentsController.php`
- `app/Http/Controllers/VideosController.php`
- `config/app.php`
- `config/services.php`
- `resources/views/api.blade.php`
- `resources/views/personal/tournaments.blade.php`
- `resources/views/tournaments/modals/claim.blade.php`

**Validation:**
- [X] `composer update -W` succeeds with Laravel `5.8.38`
- [X] `php artisan package:discover` succeeds
- [X] cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] `php artisan route:list` succeeds (routes register correctly)
- [X] App reports `Laravel Framework 5.8.38`
- [X] API tests pass: `npm run test:api` (26/26)
- [X] E2E tests pass: `npm run test:e2e` (91/91)
- [~] Perf baseline comparison was skipped (optional for this step)

**Notes:**
- API tests include existing skip-based assertions for entries when local fixture data lacks a suitable concluded tournament; suite still passes fully.
- Composer update reports legacy abandoned-package warnings (for example `laravelcollective/html`, `facebook/graph-sdk`), but they are non-blocking for this step.

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.7: Laravel 5.8 → 6.0 (LTS)
**Guides:**
- https://laravel.com/docs/6.x/upgrade
- https://laravel.com/docs/6.x/releases
- https://laravel.com/docs/6.x/socialite
- https://www.php.net/manual/en/migration72.incompatible.php
- https://www.php.net/manual/en/migration72.deprecated.php
- https://www.php.net/manual/en/migration72.other-changes.php

**PHP Requirement:** >= 7.2.0

**Key Changes (Official 6.0 + PHP 7.2 upgrade guides):**
1. **High impact:**
   - `authorizeResource` controllers require policy `viewAny` for `index`
   - String / array helpers removed from framework (`str_*`, `array_*`)
   - PHP 7.2 warns when `count()` is used on non-countables

2. **Medium impact:**
   - Queue worker default retries changed (`queue:work` now defaults to one try)
   - `failed_jobs` table should exist
   - Eloquent string primary keys should explicitly declare `$keyType = 'string'`
   - `Input` facade removed
   - PHP 7.2 raises undefined constant usage from `E_NOTICE` to `E_WARNING`

3. **Low / contextual impact:**
   - Email verification route/method changes (only if feature used)
   - `BelongsTo::update()` behavior changed to ad-hoc update semantics
   - PHP 7.2 deprecates `create_function`, `each`, `__autoload`, one-arg `parse_str`, string `assert`, `(unset)` cast, `$php_errormsg`/`track_errors`
   - PHP 7.2 moves `mcrypt` out of core (PECL only)

**Repository impact assessment (2026-03-08, updated with PHP 7.2 audit):**
- No `authorizeResource` usage found in controllers, so `viewAny` is not a blocker in current code paths.
- No blocking `str_*` / `array_*` helper usage found in source (do **not** add `laravel/helpers` unless new usages appear).
- Carbon 2 is already present in the current lockfile baseline.
- Non-incrementing string-key models (`CardIdentity`, `CardPack`, `CardCycle`) should add explicit `$keyType = 'string'`.
- `config/queue.php` references `failed_jobs`, but no `failed_jobs` migration exists yet.
- OAuth package is legacy and vendor-patch-dependent; replace during this step.
- **Must-fix PHP 7.2 hotspot:** `count($entry)` used on model objects in `resources/views/tournaments/partials/entries.blade.php` (non-countable warning on 7.2).
- **Likely-safe but should harden:** external payload count checks in `app/Http/Controllers/FBController.php` (`count($djd['results'])`) and `app/Http/Controllers/TournamentsController.php` (`count($json['players'])`) should enforce array semantics.
- Runtime/docs are still pinned to PHP 7.1 in several places (`docker/Dockerfile.php`, `readme.md`, `CLAUDE.md`) and must be aligned to 7.2 for this step.
- First-party scans found no active usage of deprecated PHP 7.2 constructs (`create_function`, PHP `each`, `__autoload`, one-arg `parse_str`, string `assert`, `(unset)` cast, `$php_errormsg`, `track_errors`).

**Implementation (planned, revised):**
1. **Dependency + runtime upgrade (Laravel 6 + PHP 7.2 parity)**
   - Update `composer.json` to:
     ```json
     "php": ">=7.2.0",
     "laravel/framework": "^6.0"
     ```
   - Update Laravel 6-compatible package constraints and run `composer update -W`.
   - Update Docker PHP runtime from 7.1 to 7.2+ for local/CI parity.
   - Update project docs that still mention PHP 7.1 so environment setup matches runtime reality.

2. **PHP 7.2 warning hardening pass (required before full validation)**
   - Replace non-countable checks in `resources/views/tournaments/partials/entries.blade.php` (`count($entry)` → object/isset-safe checks).
   - Harden external payload checks (`FBController`, `TournamentsController`) to avoid count warnings when API payloads are missing/malformed.
   - Re-run targeted grep to confirm no first-party usage of PHP 7.2 deprecated constructs.

3. **Laravel 6 compatibility adjustments**
   - Add `$keyType = 'string'` on string-PK, non-incrementing models.
   - Add migration for `failed_jobs` table and run migrations.
   - Make queue worker retry behavior explicit (`--tries`) in deployment/ops docs/config.
   - Explicitly set Redis client in `config/database.php` (`predis` vs `phpredis`) to avoid environment drift.

4. **OAuth replacement (critical path in this step)**
   - Replace `oriceon/oauth-5-laravel` with `laravel/socialite`.
   - Implement custom NetrunnerDB Socialite provider/adapter.
   - Migrate OAuth configuration to `config/services.php` (`client_id`, `client_secret`, `redirect`).
   - Refactor `NetrunnerDBController` OAuth internals to Socialite while preserving current behavior.
   - Keep `/oauth2/redirect` compatibility during cutover so existing UI + tests remain stable.
   - Remove legacy OAuth wiring and CI vendor restore hack after Socialite flow passes tests.

5. **Optional positive refactors (requested, low/medium risk)**
   - Replace `rand()` usages used for generated numeric codes in `TournamentsController` with `random_int()`.
   - Standardize policies with conventional methods (`viewAny`, `view`, `create`, `update`, `delete`) alongside existing custom abilities.
   - Clean stale policy map entries in `AuthServiceProvider`.
   - Refactor known admin-page N+1/perf hotspots (`communityCount`/VIP stats path).

**Files expected to change (Step 2.7):**
- `composer.json`, `composer.lock`
- `docker/Dockerfile.php`
- `readme.md`, `CLAUDE.md`
- `app/Http/Controllers/NetrunnerDBController.php`
- `app/Http/Controllers/TournamentsController.php`
- `app/Http/Controllers/FBController.php`
- `routes/web.php`
- `config/services.php`, `config/app.php`, `config/database.php`, `config/queue.php`
- `config/oauth.php`, `config/oauth-5-laravel.php`, `app/Providers/OAuthServiceProvider.php` (retire)
- `app/CardIdentity.php`, `app/CardPack.php`, `app/CardCycle.php`
- `resources/views/tournaments/partials/entries.blade.php`
- `.github/workflows/main.yml`, `.gitignore`
- `database/migrations/*_create_failed_jobs_table.php` (new)

**Validation:**
- [ ] `composer update -W` succeeds with Laravel 6.x
- [ ] Runtime confirms PHP 7.2 (`php -v` in Docker/CI)
- [ ] `php artisan package:discover`, `config:clear`, `cache:clear`, `route:clear`, `view:clear` all succeed
- [ ] `php artisan route:list` succeeds
- [ ] No PHP 7.2 warning regressions in key flows (tournament entries view, FB import path, NRTM conclude path) under `E_ALL`
- [ ] OAuth login/callback works with Socialite + NetrunnerDB
- [ ] Deck sync and claim publish/delete flows still work
- [ ] API tests pass (`npm run test:api`)
- [ ] E2E tests pass (`npm run test:e2e`)

**Notes / risks:**
- Confirm NetrunnerDB OAuth `state` handling, scopes, refresh-token behavior, and redirect URI constraints before final cutover.
- Keep `/oauth2/redirect` route compatibility until E2E + smoke tests are green.
- Vendor libraries include conditional `mcrypt` and `INTL_IDNA_VARIANT_2003` fallback paths; monitor logs for deprecation noise on PHP 7.2 and only patch/bump if warnings surface in active flows.

**Validation checkpoint:** Run API and E2E tests

---

### OAuth Package Replacement (Step 2.7 Critical Path)

Replace `oriceon/oauth-5-laravel` with Laravel Socialite + custom NetrunnerDB provider while preserving behavior.

1. **Install Socialite**
   ```bash
   composer require laravel/socialite
   ```

2. **Create custom NetrunnerDB Socialite adapter**
   - Add provider/driver wiring in app-local code (NetrunnerDB is not a first-party Socialite provider).

3. **Refactor `NetrunnerDBController` OAuth flow**
   - Use Socialite redirect + callback token retrieval.
   - Preserve login return behavior (`login_url` cookie and redirect semantics).

4. **Migrate configuration**
   - Use `config/services.php` keys: `client_id`, `client_secret`, `redirect`.
   - Retire legacy OAuth config/provider glue.

5. **Stabilize CI + tests**
   - Remove vendor OAuth file restore step in CI after migration.
   - Keep `/oauth2/redirect` route compatibility until auth tests pass.

---

### Package Updates Required

| Package | Current | Target (6.0) | Action |
|---------|---------|--------------|--------|
| laravelcollective/html | ^5.8 | ^6.0 | Update |
| intervention/image | ^2.3 | ^2.7 | Update |
| oriceon/oauth-5-laravel | 1.0.5 | - | Remove (replace with Socialite) |
| laravel/socialite | - | Laravel 6-compatible | Add |
| webpatser/laravel-countries | 1.5.4 | 1.5.4 (temporary) | Keep + verify compatibility |
| doctrine/dbal | ^2.9 | ^2.10 | Update |

### Phase 2 Validation
- [ ] All routes accessible
- [ ] OAuth login/callback works with Socialite custom provider
- [ ] Deck sync and claim export/import flows still work
- [ ] `failed_jobs` migration exists and runs
- [ ] Queue worker retry strategy is explicitly configured (`--tries`)
- [ ] All CRUD operations function
- [ ] API tests pass (`npm run test:api`)
- [ ] E2E tests pass (`npm run test:e2e`)

---

## Phase 3: Laravel 6.0 → 11.0 Upgrade (Manual)

**Goal:** Complete Laravel modernization with PHP 8.2+.

### Upgrade Path
6.0 → 7.0 → 8.0 → 9.0 → 10.0 → 11.0

---

### Step 3.1: Laravel 6.0 → 7.0 (with PHP 7.3+) - ✅ DONE
**Guides:**
- https://laravel.com/docs/7.x/upgrade
- https://www.php.net/manual/en/migration73.php

**PHP Requirement:** >= 7.2.5 (Laravel 7 minimum); recommend >= 7.3.0 to unlock PHP 7.3 features

**Breaking Changes (Laravel 6 → 7):**

1. **Route Caching & `Route::apiResource` Stubs (BREAKING)**
   - The `stubs` configuration option for `Route::apiResource` was removed
   - **Impact**: If the app uses `Route::apiResource` with custom stubs, this will break
   - **Fix**: Remove stub references or use standard resource routes

2. **String Validation Rules (BREAKING)**
   - Empty strings are now converted to `null` by default via `ConvertEmptyStringsToNull` middleware
   - **Impact**: Validation rules using `nullable` may behave differently
   - **Fix**: Ensure optional fields use `nullable` modifier explicitly
   ```php
   // Laravel 7 - be explicit about nullable fields
   'publish_at' => 'nullable|date',
   'description' => 'nullable|string',
   ```

3. **Symfony 5 Components**
   - Components using Symfony 4.x may need updates
   - **Action**: Run `composer update -W` and verify no deprecation warnings

**New Features in Laravel 7 (Code Improvement Opportunities):**

1. **CORS Support (Built-in)**
   - First-party CORS package integrated (no need for `fruitcake/laravel-cors`)
   - **Action**: Create `config/cors.php` with `php artisan config:publish cors`
   - **Relevance**: Useful if API is consumed from different domains

2. **Blade Component Tag Compiler**
   - Anonymous components with `<x-component-name>` syntax
   - **Benefit**: Cleaner, more maintainable view code
   ```blade
   <!-- Old way -->
   @component('components.alert')
       Alert content
   @endcomponent

   <!-- Laravel 7+ -->
   <x-alert />
   ```
   - **Code improvement**: Refactor repetitive view logic into reusable components (buttons, modals, cards)

3. **HTTP Client (Guzzle Wrapper)**
   - `Http` facade for external API requests
   - **Benefit**: Simpler syntax for API integrations
   - **Relevance**: Could simplify NetrunnerDB API integration code
   ```php
   // Laravel 7+ style
   $response = Http::get('https://api.netrunnerdb.com/cards');
   ```

4. **Custom Eloquent Casts**
   - `Castable` interface for custom value objects
   - **Benefit**: Better type safety for model attributes

5. **Database Query Improvements**
   - Better support for foreign keys, index management in migrations
   - **Benefit**: Cleaner migration files

**PHP 7.3 Features (Positive Code Changes Available):**

1. **Trailing commas in function calls** (cleaner diffs)
   ```php
   return view('upcoming', compact(
       'message',
       'nowdate',
       'tournament_types',
   ));
   ```

2. **Flexible heredoc/nowdoc syntax**
   ```php
   $html = <<<HTML
       <div>$title</div>
       HTML;
   ```

3. **New helper functions**
   - `array_key_first($array)` / `array_key_last($array)` - replace `reset(array_keys())` / `end(array_keys())`
   - `is_countable($var)` - safe check before `count()`
   - `hrtime()` - high-resolution time for performance measurement

4. **Argon2id password hashing** (more secure)
   ```php
   $hash = password_hash($password, PASSWORD_ARGON2ID);
   ```

5. **`compact()` notices for undefined variables**
   - Review `compact()` calls to ensure all variables are defined

**Implementation:**

1. **Dependency + lockfile upgrade**
   - Update `composer.json`:
   ```json
   "php": ">=7.3.0",
   "laravel/framework": "^7.0"
   ```
   - Run `composer update -W` in Docker

2. **Runtime upgrade**
   - Update `docker/Dockerfile.php`: Change FROM to `php:7.3-fpm-alpine`
   - Update project docs that mention PHP 7.2

3. **Create CORS config**
   - Create `config/cors.php` from Laravel 7 skeleton
   - Configure allowed origins, methods, headers

4. **Validation audit**
   - Review all validation rules for `nullable` fields
   - Verify `compact()` calls have all variables defined

**Files to Modify:**
- `composer.json` - Update PHP and Laravel version constraints
- `docker/Dockerfile.php` - Update FROM to `php:7.3-fpm-alpine`
- `config/cors.php` - Create from Laravel 7 skeleton
- `config/app.php` - Review service providers and aliases
- `routes/web.php`, `routes/api.php` - Check for removed route features
- `app/Providers/*.php` - Update any deprecated service provider code

**Code Audit Results (Project-Specific):**
- ✅ No `continue;` in switch statements found
- ✅ No `define(..., true)` case-insensitive constants
- ✅ No deprecated `fgetss()`, `gzgetss()`, `mbereg_*()` functions
- ✅ `strpos()` calls use string needles (safe)
- ✅ No `FILTER_FLAG_*` deprecated constants
- ⚠️ 20+ `compact()` calls - verify all variables are defined

**Validation:**
- [X] `composer update -W` succeeds with Laravel `7.x`
- [X] Runtime confirms PHP 7.3+ (`php -v` in Docker/CI) - PHP 7.3.33
- [X] `php artisan package:discover` succeeds (manual cache creation required due to CLI output issue)
- [X] Cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] `php artisan route:list` succeeds (routes register correctly) - 116 routes
- [X] App reports `Laravel Framework 7.x` - Laravel 7.30.7
- [X] API tests pass: `npm run test:api` (26/26)
- [X] E2E tests: `npm run test:e2e` (91/91) — all tests passed after fixing PHP 7.3 compatibility issues (Exception→Throwable in Handler, compact() with undefined variables)

**Validation checkpoint:** Run API and E2E tests - API tests ✅ passed, E2E tests ✅ passed

**Notes:**
- CORS config created at `config/cors.php`
- `laravel/tinker` updated from `~1.0` to `~2.0` for Laravel 7 compatibility
- All deprecated PHP 7.3 constructs verified as not in use
- Package discovery cache created manually due to artisan CLI output buffering issue (app boots correctly)

---

### Step 3.2: Laravel 7.0 → 8.0 - ✅ DONE
**Guide:** https://laravel.com/docs/8.x/upgrade
**Release Notes:** https://laravel.com/docs/8.x/releases
**Full Research:** See [`migration_laravel_8.md`](migration_laravel_8.md) for the complete codebase-mapped analysis.

**PHP Requirement:** >= 7.3.0 (already met)

**Breaking Changes (High Impact):**
1. **Seeder & Factory Namespaces (MAJOR)**
   - Rename `database/seeds/` → `database/seeders/`
   - Add `namespace Database\Seeders;` to all 8 seeder files
   - Update `composer.json` autoload: remove `"classmap": ["database"]`, add PSR-4 mappings for `Database\Factories\` and `Database\Seeders\`

2. **Model Factories rewritten as classes (MAJOR)**
   - `database/factories/ModelFactory.php` uses legacy `$factory->define()` syntax
   - **Option A:** Install `laravel/legacy-factories` (quick, no factory code changes)
   - **Option B:** Rewrite as class-based `UserFactory` (recommended — only one trivial factory)

3. **Pagination defaults → Tailwind CSS (MAJOR)**
   - App uses Bootstrap 4 — must add `Paginator::useBootstrap()` to `AppServiceProvider::boot()`

4. **`elixir()` helper removed (CRITICAL ⚠️)**
   - Still used in `resources/views/layout/general.blade.php` — will cause fatal error
   - Must replace with direct asset paths or custom helper before upgrading

**Breaking Changes (Low/No Impact in This Codebase):**
- Queue `retryAfter` → `backoff`, `timeoutAt` → `retryUntil` — **no usage found**
- Queue `allOnQueue()`/`allOnConnection()` removed — **no usage found**
- `sendNow` mail method removed — **no usage found**
- `Manager::$app` removed — **no custom Manager subclasses**
- Collection `isset` behavior change — **unlikely impact**
- `EventServiceProvider::register()` must call parent — **verify**
- Eloquent `increment`/`decrement` now fire events — **audit model observers**

**Dependency Updates:**

| Package | Current | Target | Notes |
|---------|---------|--------|-------|
| `laravel/framework` | `^7.0` | `^8.0` | Core upgrade |
| `phpunit/phpunit` | `^8.0` | `^9.0` | Required |
| `fzaninotto/faker` | `~1.4` | **Remove** | Abandoned; replace with `fakerphp/faker` |
| `mockery/mockery` | `0.9.*` | `^1.4` | Update for compatibility |
| `laravel/socialite` | `^4.4` | `^5.0` | If upgrading to latest |

**Recommended Improvements:**
- Add maintenance mode pre-rendering to `public/index.php`
- Modernize `RouteServiceProvider` to closure-based `$this->routes()` boot method (keep `$namespace` for backward compatibility)

**New Features Available for Adoption (Optional):**
1. **`app/Models/` directory convention** — ideal time to move 17 models (already planned)
2. **Migration squashing** (`php artisan schema:dump --prune`) — 67 migration files can be consolidated
3. **Improved rate limiting** — `RateLimiter::for()` with named limiters
4. **Time testing helpers** — `$this->travel()` / `$this->travelTo()` in PHP tests
5. **Job batching** — `Bus::batch()` with `then`/`catch`/`finally` callbacks

**Implementation (2026-03-10):**
1. **Dependency + lockfile upgrade**
   - Updated `composer.json`:
     - `laravel/framework`: `^7.0` → `^8.0`
     - `laravel/socialite`: `^4.4` → `^5.0`
     - `phpunit/phpunit`: `^8.0` → `^9.0`
     - `fzaninotto/faker` removed; replaced with `fakerphp/faker: ^1.9.1`
     - `mockery/mockery`: `0.9.*` → `^1.4`
     - `symfony/css-selector` and `symfony/dom-crawler`: `^4.3` → `^5.0`
   - Updated autoload: removed `classmap: ["database"]`, added PSR-4 for `Database\Factories\` and `Database\Seeders\`
   - Ran `docker compose exec php composer update -W` → upgraded to `Laravel 8.83.29`

2. **Seeder namespace migration**
   - Created `database/seeders/` directory
   - Copied all 8 seeders from `database/seeds/` with `namespace Database\Seeders;` added
   - Seeders: `DatabaseSeeder`, `CardCycleSeeder`, `CardPackSeeder`, `CountriesSeeder`, `MWLSeeder`, `TournamentFormatsTableSeeder`, `TournamentTypeSeeder`, `UsersSeeder`

3. **Class-based factory (clean path)**
   - Created `database/factories/UserFactory.php` extending `Illuminate\Database\Eloquent\Factories\Factory`
   - Added `HasFactory` trait to `App\User`
   - Legacy `database/factories/ModelFactory.php` left in place (no longer autoloaded via classmap; PSR-4 picks up `UserFactory` only)

4. **Pagination Bootstrap fix**
   - Added `Paginator::useBootstrap()` to `AppServiceProvider::boot()` (required: default changed to Tailwind in Laravel 8)

5. **Critical: `elixir()` helper removed**
   - Replaced `elixir('css/all.css')` and `elixir('js/all.js')` in `resources/views/layout/general.blade.php` with `mix()` calls
   - Created `public/mix-manifest.json` mapping both assets to their direct paths (Gulp pipeline outputs unversioned `public/css/all.css` and `public/js/all.js`)

6. **Maintenance mode pre-rendering**
   - Added maintenance file check to `public/index.php` (before autoload)
   - Note: did NOT re-define `LARAVEL_START` — `bootstrap/autoload.php` already defines it on line 3

7. **Cleanup**
   - Removed dead `use Mockery\CountValidator\Exception` import from `PhotosController.php`

**Files Modified (Step 3.2):**
- `composer.json`
- `composer.lock`
- `app/User.php` (added `HasFactory` trait)
- `app/Providers/AppServiceProvider.php` (added `Paginator::useBootstrap()`)
- `app/Http/Controllers/PhotosController.php` (removed dead Mockery import)
- `resources/views/layout/general.blade.php` (`elixir()` → `mix()`)
- `public/index.php` (maintenance mode check)
- `public/mix-manifest.json` (new)
- `database/factories/UserFactory.php` (new — class-based factory)
- `database/seeders/DatabaseSeeder.php` (new)
- `database/seeders/CardCycleSeeder.php` (new)
- `database/seeders/CardPackSeeder.php` (new)
- `database/seeders/CountriesSeeder.php` (new)
- `database/seeders/MWLSeeder.php` (new)
- `database/seeders/TournamentFormatsTableSeeder.php` (new)
- `database/seeders/TournamentTypeSeeder.php` (new)
- `database/seeders/UsersSeeder.php` (new)

**Validation:**
- [X] `composer update -W` succeeds with Laravel `8.83.29`
- [X] `php artisan package:discover` succeeds
- [X] Cache clear commands succeed: `config:clear`, `cache:clear`, `route:clear`, `view:clear`
- [X] `php artisan route:list` succeeds (116 routes)
- [X] App reports `Laravel Framework 8.83.29`
- [X] API tests pass: 127/129 pass in full suite (2 perf timeouts due to parallel resource contention only; all 10 perf tests pass in isolation)
- [X] E2E tests pass: all E2E tests pass

**Notes:**
- The `LARAVEL_START` constant is already defined in `bootstrap/autoload.php` line 3 (legacy file present in this project). Do NOT redefine it in `public/index.php` — only add the maintenance file check there.
- `database/seeds/` directory retained (not deleted) for historical reference; it is no longer autoloaded.
- `database/factories/ModelFactory.php` retained; it is no longer autoloaded via classmap, so it causes no conflicts.
- 2 perf test timeouts in the full `npm test` run are pre-existing infrastructure-level slowness (heavy parallel load during full suite); confirmed by isolated run where all 10 pass.

**Validation checkpoint:** Run API and E2E tests — 127/129 ✅ (2 perf timeouts are load-related, not regressions)

---

### Step 3.3: Laravel 8.0 → 9.0
**Guide:** https://laravel.com/docs/9.x/upgrade

**PHP Requirement:** >= 8.0.2

**PHP 8.0.x target recommendation (official PHP docs):**
- **Target `PHP 8.0.30`** (latest/final 8.0 patch release listed on php.net releases page).
- **Important:** PHP 8.0 is already end-of-life:
  - Active support ended: **2022-11-26**
  - Security support ended: **2023-11-26**
- Practical implication: use 8.0.30 only as a short bridge to get Laravel 9 green, then move quickly to PHP 8.1/8.2.

Official references:
- https://www.php.net/releases/
- https://www.php.net/supported-versions.php

**Required Changes (Official Laravel 9 upgrade guide):**
1. **Dependencies and Composer constraints**
   - Update:
     - `laravel/framework` → `^9.0`
     - `php` → `^8.0.2`
     - `nunomaduro/collision` → `^6.1`
     - `pusher/pusher-php-server` → `^5.0` (if installed)
   - Replace debug package:
     - Remove `facade/ignition`
     - Add `spatie/laravel-ignition:^1.0`

2. **Mail stack migration (SwiftMailer removed)**
   - Laravel 9 uses Symfony Mailer.
   - Update app code and integrations that depended on SwiftMailer internals.
   - If using Mailgun/Postmark transports, add required Symfony packages:
     - `symfony/mailgun-mailer` or `symfony/postmark-mailer`
     - `symfony/http-client`

3. **Filesystem migration (Flysystem 3)**
   - Install adapter packages explicitly for non-local disks (S3/FTP/SFTP).
   - Review changed behavior:
     - write failures return `false` unless `'throw' => true`
     - reading missing files returns `null`
     - deleting missing files returns `true`
   - Ensure any custom driver `Storage::extend(...)` returns `Illuminate\Filesystem\FilesystemAdapter`.

4. **Behavior changes to audit in app code/tests**
   - `belongsToMany()->firstOrCreate / updateOrCreate / firstOrNew` matching logic changed.
   - Custom cast `set()` now runs for `null` values.
   - Validation: unvalidated array keys are excluded from validated output.
   - Validation rule rename: `password` → `current_password`.
   - HTTP client default timeout is now 30 seconds.
   - Postgres connection option rename: `schema` → `search_path`.
   - Testing: prefer `assertModelMissing` over `assertDeleted`.
   - Conditional method behavior updates: `when()` / `unless()`.

5. **App skeleton/runtime alignment**
   - If bringing forward framework skeleton changes, apply Laravel 9 language file structure updates (`lang/` publish behavior) and trusted proxy middleware updates.

Official references:
- https://laravel.com/docs/9.x/upgrade

**Positive Code Changes Enabled by Laravel 9 + modern PHP (optional):**
1. **Modernize Eloquent accessors/mutators** using `Illuminate\Database\Eloquent\Casts\Attribute` instead of legacy `getXAttribute` / `setXAttribute`.
2. **Add enum route bindings** (PHP 8.1+) for safer, typed route parameters.
3. **Use query builder full-text support** (`whereFullText`, `orWhereFullText`) where current code uses raw SQL LIKE/full-text fragments.
4. **Adopt Blade quality-of-life directives** (`@checked`, `@selected`, `@disabled`) to reduce conditional markup noise.
5. **Use controller route groups** (`Route::controller(...)->group(...)`) to simplify repetitive route definitions.
6. **Enable coverage gate in CI** via `php artisan test --coverage --min=<threshold>` once PHPUnit/Xdebug coverage setup is ready.

Official references:
- https://laravel.com/docs/9.x/releases
- https://laravel.com/docs/9.x/eloquent-mutators
- https://laravel.com/docs/9.x/routing
- https://laravel.com/docs/9.x/queries
- https://laravel.com/docs/9.x/blade
- https://laravel.com/docs/9.x/testing

**Implementation Progress (2026-03-12):**
1. **PHP 8.0 callback compatibility fix — completed**
   - Updated sort callback to return integer comparator (required for PHP 8 behavior):
     - `app/Http/Controllers/NetrunnerDBController.php`
     - `sortByDateUpdate()` now uses `$b['date_update'] <=> $a['date_update']`

2. **Error-suppression cleanup hardening — completed**
   - Removed `@unlink(...)` usage in photo upload cleanup paths and replaced with explicit safe cleanup helper:
     - `app/Http/Controllers/PhotosController.php`
     - Added `cleanupFile($path)` with `file_exists` guard + `try/catch (\Throwable)` logging

3. **Dependency blocker resolved — completed**
   - Removed abandoned `facebook/graph-sdk:^5.7` from `composer.json` (package only supports `^5.4|^7.0`).
   - Replaced SDK usage with app-local Graph API client implementation using Guzzle:
     - `app/Support/Facebook/FacebookClient.php`
     - Keeps existing `FBController` integration surface (`getGraphNode(...)`) unchanged.
   - Updated lockfile via:
     - `composer remove facebook/graph-sdk --no-interaction`
   - Re-verified PHP 8 compatibility constraints:
     - `composer why-not php 8.0.2 -t` → no blockers
     - `composer why-not php 8.0.30` → no blockers

4. **Validation run status (resolved with escalated execution)**
   - Initial sandbox-only test runs could not access localhost (`connect EPERM 127.0.0.1:8000` / `::1:8000`).
   - Re-ran with escalated networking against the Dockerized app:
     - `cd tests && npm run test:api` → **26/26 passed**
     - `cd tests && npm run test:e2e` → **91/91 passed**

5. **Runtime/CI PHP raise to 8.0.30 — completed**
   - Updated Docker PHP runtime image:
     - `docker/Dockerfile.php`: `FROM php:7.3-fpm-alpine` → `FROM php:8.0.30-fpm-alpine`
   - Updated GD extension configure flags for PHP 8-compatible syntax:
     - `--with-freetype-dir` / `--with-jpeg-dir` → `--with-freetype` / `--with-jpeg`
   - CI impact:
     - GitHub Actions builds app runtime from `docker/Dockerfile.php`, so CI now uses PHP 8.0.30 for the app container as well.

6. **Runtime bring-up and health checks — completed**
   - `docker compose build php` completed successfully with PHP `8.0.30` base image.
   - `docker compose up -d php nginx mysql` completed successfully.
   - Built frontend assets (`docker compose --profile build run --rm node`) to restore `public/mix-manifest.json` and avoid runtime 500s.
   - Verified app health before tests:
     - `GET /` → `200`
     - `GET /api/tournaments/upcoming` → `200`

7. **Laravel 9 framework/config migration — completed**
   - Upgraded Composer constraints and lockfile:
     - `laravel/framework` `^8.0` → `^9.0`
     - `php` `>=7.3.0` → `^8.0.2`
     - Added dev packages: `nunomaduro/collision:^6.1`, `spatie/laravel-ignition:^1.0`
     - Removed package: `fideloper/proxy`
   - Migrated trusted proxy middleware to framework-native class:
     - `app/Http/Middleware/TrustProxies.php` now extends `Illuminate\Http\Middleware\TrustProxies`
   - Updated maintenance middleware class for Laravel 9:
     - `CheckForMaintenanceMode` → `PreventRequestsDuringMaintenance` in `app/Http/Kernel.php`
   - Migrated config files to Laravel 9-compatible structure:
     - `config/mail.php` (mailer-based config, Symfony Mailer-compatible)
     - `config/filesystems.php` (Flysystem 3 shape + explicit `throw` behavior)
     - `config/database.php` (`schema` → `search_path` for PostgreSQL)
     - `.example.env` (`MAIL_MAILER`, `MAIL_FROM_*`, `FILESYSTEM_DISK`)

8. **Positive cleanup from Laravel 9 features — completed**
   - Replaced legacy Blade checked-attribute ternaries with `@checked(...)` in tournament form radios:
     - `resources/views/tournaments/partials/form.blade.php`

9. **Validation and delivery — completed**
   - Framework boots on Laravel 9:
     - `php artisan --version` → `Laravel Framework 9.52.21`
   - Framework sanity checks pass:
     - `php artisan config:clear`
     - `php artisan cache:clear`
     - `php artisan route:list`
   - Tests pass on upgraded branch:
     - `cd tests && npm run test:api` → **26/26 passed**
     - `cd tests && npm run test:e2e` → **91/91 passed**
     - `cd tests && npm run test:e2e -- e2e/tests/tournament-crud.test.ts` → **4/4 passed**
   - Branch/PR:
     - Branch pushed: `migration-laravel-9`
     - Commit: `e2e7828`
     - PR: https://github.com/madarasz/always-be-running/pull/152 (from `migration-laravel-9` → `migration`)

**Validation checkpoint:** ✅ Laravel 8 → 9 migration implemented and validated on branch `migration-laravel-9`

---

### Step 3.4: Laravel 9.0 → 10.0 - ✅ DONE
**Guide:** https://laravel.com/docs/10.x/upgrade

**PHP Requirement:** >= 8.1.0

**Step objective:** Upgrade framework/runtime to Laravel 10-compatible versions, apply required code breaks from official docs, then run full test suite.

**Official Laravel 10 required changes (apply in this step):**

1. **Composer / platform updates**
   - Set:
     ```json
     "php": "^8.1",
     "laravel/framework": "^10.0"
     ```
   - Update required package majors per official guide:
     - `doctrine/dbal` → `^3.0`
     - `spatie/laravel-ignition` → `^2.0`
   - Keep Docker + CI runtime on PHP 8.1.x for parity.

2. **Eloquent date casting break**
   - Laravel 10 removes support for model `$dates`.
   - Replace `$dates` with `$casts` (`'..._at' => 'datetime'`) in all affected models.
   - Known ABR files:
     - `app/Entry.php`
     - `app/Photo.php`
     - `app/Prize.php`
     - `app/PrizeElement.php`
     - `app/Tournament.php`
     - `app/TournamentGroup.php`
     - `app/Video.php`

3. **Auth service provider cleanup**
   - Remove `$this->registerPolicies();` from `AuthServiceProvider::boot()`.
   - Laravel auto-registers policies; explicit call is no longer needed.

4. **Database expression string-cast behavior**
   - Audit any `(string) DB::raw(...)` / expression string casts.
   - Use grammar-aware value access where needed (per upgrade guide).

5. **Redis / logging compatibility checks**
   - If using Predis, ensure `predis/predis:^2.0`.
   - Verify custom Monolog integrations against Monolog 3 expectations.

**ABR-specific preflight checks for PHP 8.1 deprecations (recommended before/with framework bump):**

1. **Dependency compatibility**
   - `docker compose run --rm php composer why-not php 8.1.0`
   - `docker compose run --rm php composer why-not php 8.1.33`

2. **Null-to-string hardening hotspots**
   - Normalize potentially-null inputs before `strlen`, `trim`, `strpos`.
   - Candidate files:
     - `app/Http/Requests/TournamentRequest.php`
     - `app/Http/Controllers/VideosController.php`
     - `app/Tournament.php`
     - `app/Http/Controllers/TournamentsController.php`
     - `app/Http/Controllers/BadgeController.php`
     - `app/Http/Controllers/EntriesController.php`
   - Preferred patterns:
     - `strlen((string) $value)`, `trim((string) $value)`, `strpos((string) $value, '...')`
     - or `$value = $value ?? ''` before string functions.

**Positive code changes worth doing during this step (optional but high-value):**

1. **Migrate legacy middleware alias naming**
   - Rename `Kernel::$routeMiddleware` to `Kernel::$middlewareAliases` (modern convention; behavior-preserving).

2. **Continue Blade cleanup**
   - Replace legacy conditional attribute ternaries with native directives (`@checked`, `@selected`, etc.).

3. **Introduce enums for magic numeric domains**
   - Start with tournament type IDs and format IDs in validation + view logic.
   - Keep scope small: read-only mapping first, then request/controller usage.

4. **Add strict return/param types to touched methods**
   - Especially policies, request helpers, and small service methods modified in this step.

**Implementation (2026-03-12):**

1. **Composer/runtime upgrade to Laravel 10 + PHP 8.1 parity**
   - Updated `composer.json` constraints:
     - `php`: `^8.1`
     - `laravel/framework`: `^10.0`
     - `doctrine/dbal`: `^3.0`
     - `spatie/laravel-ignition`: `^2.0`
     - `nunomaduro/collision`: `^7.0`
     - `laravel/tinker`: `^2.8`
   - Added Composer platform pin for deterministic PHP 8.1-compatible resolution:
     - `config.platform.php = 8.1.0`
   - Regenerated lockfile via `composer update -W`.
   - Updated Docker PHP runtime:
     - `docker/Dockerfile.php`: `php:8.0.30-fpm-alpine` → `php:8.1-fpm-alpine`
   - Rebuilt/restarted Docker services; verified container runtime:
     - `php -v` → `PHP 8.1.34`

2. **Laravel 10 required code breaks**
   - Replaced model `$dates` with `$casts` datetime mappings:
     - `app/Entry.php`
     - `app/Photo.php`
     - `app/Prize.php`
     - `app/PrizeElement.php`
     - `app/Tournament.php`
     - `app/TournamentGroup.php`
     - `app/Video.php`
   - Removed explicit policy registration call:
     - `app/Providers/AuthServiceProvider.php`: removed `$this->registerPolicies();`

3. **DB expression and logging compatibility audit**
   - Audited app code for `(string) DB::raw(...)` style usage; no occurrences found.
   - Confirmed no custom Monolog integration code in app layer that requires Monolog 3 adaptation.

4. **PHP 8.1 null-to-string hardening**
   - Applied explicit string normalization/casts in known hotspots:
     - `app/Http/Requests/TournamentRequest.php`
     - `app/Http/Controllers/VideosController.php`
     - `app/Tournament.php`
     - `app/Http/Controllers/TournamentsController.php`
     - `app/Http/Controllers/BadgeController.php`
     - `app/Http/Controllers/EntriesController.php`
   - Preflight checks:
     - `composer why-not php 8.1.0` → no blockers
     - `composer why-not php 8.1.33` → no blockers

**Validation (2026-03-12 final rerun after session fix):**
- [X] `php artisan --version` → `Laravel Framework 10.50.2`
- [X] `php artisan config:clear && php artisan cache:clear && php artisan route:list`
- [X] `cd tests && npm run test:api` → **26/26 passed**
- [X] `cd tests && npm run test:e2e` → **91/91 passed**
  - Full regression result: **11/11 test files passed**, **91/91 tests passed**
  - Previously failing test now passes:
    - `e2e/tests/tournament-entries.test.ts`:
      - `Claim with decks > claims spot on concluded tournament with decklist and then removes it`
  - Root cause from latest trace artifact:
    - `/api/userdecks` returned `{"error":"NetrunnerDB session lost"}`, so claim modal kept `#submit-claim` disabled by design.
  - Fix applied:
    - `app/Http/Controllers/NetrunnerDBController.php`
      - Store NRDB OAuth tokens **after** `Auth::login(...)` in callback flow, so tokens persist across session migration.
    - `tests/e2e/helpers/auth.ts`
      - Harden cached auth validation to call `/api/userdecks`; if it returns `error`, force fresh OAuth login in global setup.
  - Verification:
    - Targeted test rerun passed:
      - `cd tests && npm run test:e2e -- e2e/tests/tournament-entries.test.ts -t "claims spot on concluded tournament with decklist and then removes it"` → **1/1 passed**
    - Full suite rerun passed:
      - `cd tests && npm run test:e2e` → **91/91 passed**

**Validation checkpoint (must pass before Step 3.5):**

1. `php artisan --version` reports Laravel 10.x
2. `php artisan config:clear && php artisan cache:clear && php artisan route:list`
3. `cd tests && npm run test:api`
4. `cd tests && npm run test:e2e`
5. Smoke test OAuth login + tournament create/edit/delete flow

**References:**
- Laravel 10 upgrade guide: https://laravel.com/docs/10.x/upgrade
- Laravel 10 release notes (support policy + features): https://laravel.com/docs/10.x/releases
- PHP 8.1 migration notes: https://www.php.net/manual/en/migration81.php

---

### Step 3.5: Laravel 10.0 → 11.0 ✅ DONE
**Guide:** https://laravel.com/docs/11.x/upgrade

**PHP Requirement:** >= 8.2.0

**Repo-specific applicability + blockers (checked 2026-03-12):**

1. **Hard requirements before bumping Laravel**
   - PHP runtime must move to 8.2+ (Laravel 11 requirement).
     - Update Docker base image in `docker/Dockerfile.php` (`php:8.1-fpm-alpine` -> `php:8.2-fpm-alpine`).
     - Update Composer constraints in `composer.json`:
       - `"php": "^8.1"` -> `"php": "^8.2"`
       - `"config.platform.php": "8.1.0"` -> `"8.2.x"`
   - Bump framework:
     - `"laravel/framework": "^10.0"` -> `"^11.0"`

2. **Composer blockers found in current codebase**
   - `laravelcollective/html v6.4.1` blocks Laravel 11 (requires `illuminate/*` up to `^10.0`).
     - This is a **real blocker** and high-impact in ABR because Blade heavily uses `Form::` / `Html::` helpers across tournament/admin/profile views.
     - Plan this as a dedicated sub-step: migrate helper usage to native Blade + HTML forms (or another maintained package), then remove provider/aliases from `config/app.php`.
   - `nunomaduro/collision v7.x` conflicts with Laravel 11.
     - Update dev dependency to Laravel 11-compatible major during Composer update.

3. **Laravel 11 change() / DBAL impact in this repo**
   - `doctrine/dbal` is currently required in `composer.json` and can be removed for Laravel 11.
   - Repo has one migration using `->change()`:
     - `database/migrations/2019_12_16_151441_modify_prize_elements_table.php`
   - Keep all needed modifiers explicitly when changing columns (already mostly done here with `unsigned()` and `nullable()`), then verify with fresh migrate/rollback runs after removing DBAL.

4. **What is already compatible / low-risk here**
   - Routes are already split (`routes/web.php`, `routes/api.php`), so Laravel 11 streamlined structure is optional and can be deferred.
   - No custom implementations of `Authenticatable` / `UserProvider` were found.
   - No custom `RateLimiter::for(...)` definitions were found; only middleware throttle strings (`throttle:60,1`) in `app/Http/Kernel.php`.
   - No direct Sanctum/Passport/Cashier/Telescope usage in `composer.json`.

5. **Positive post-upgrade changes that fit this repo**
   - Add `APP_PREVIOUS_KEYS` support in deployment envs for safer key rotation.
   - Add `#[SensitiveParameter]` on methods handling OAuth tokens/secrets in `NetrunnerDBController`.
   - Consider per-second throttling only if/when custom named limiters are introduced.
   - Keep Laravel 10 app structure initially; optionally adopt Laravel 11 skeleton cleanup after green baseline.

**Validation (2026-03-12):**
- [X] `php artisan --version` -> `Laravel Framework 11.48.0`
- [X] `php artisan config:clear && php artisan cache:clear && php artisan route:list` (route list generated, 123 lines)
- [X] `docker compose exec -T php php artisan migrate --no-interaction` -> `Nothing to migrate.`
  - Non-destructive migration check used (no `migrate:fresh`/schema reset).
- [X] `cd tests && npm run test:api` -> **26/26 passed** (4/4 files)
- [X] `cd tests && npm run test:e2e` -> **91/91 passed** (11/11 files)

---

### Step 3.6: Laravel 11.0 → 12.0 ✅ DONE
**Guide:** https://laravel.com/docs/12.x/upgrade

**PHP Requirement:** >= 8.2.0 (already satisfied in this repo)

**Repo-specific applicability + actions (checked 2026-03-12):**

1. **Core dependency bump (required)**
   - Update framework constraint:
     - `"laravel/framework": "^11.0"` -> `"^12.0"`
   - Keep PHP at `^8.2` or newer.
   - Run `composer update -W` and resolve any transitive conflicts (for example dev tools like Collision/Ignition if Composer reports blockers).

2. **Carbon 3 requirement (verify)**
   - Laravel 12 requires Carbon 3.
   - Verify lockfile resolves `nesbot/carbon` to v3 during the framework bump.

3. **Filesystem behavior change (applicability: low, but explicit decision needed)**
   - Laravel 12 changes the default `local` disk root when not explicitly configured.
   - ABR already defines `local` explicitly in `config/filesystems.php` as `storage_path('app')`, so behavior will stay stable unless we intentionally change it.
   - Optional hardening step after upgrade: add/use a dedicated private disk rooted at `storage/app/private` for sensitive uploads.

4. **UUID v7 change in `HasUuids` (not currently applicable)**
   - No `HasUuids` / `HasVersion4Uuids` usage found in app models.
   - No UUID migration columns (`uuid`, `foreignUuid`, `uuidMorphs`) found.
   - No action required now; if UUID traits are introduced later, choose v7 vs v4 explicitly.

5. **Image validation SVG behavior (not currently applicable)**
   - No `image` validation rules or `File::image(...)` usage found in the app layer.
   - No migration action required; if image validation rules are added later, allow SVG explicitly only when needed.

6. **Concurrency + route naming changes (currently low risk)**
   - No `Concurrency::run(...)` usage found.
   - Named routes are sparse and no duplicate route names were found in `routes/web.php` and `routes/api.php`.
   - No required code change, but keep route-name uniqueness check in validation.

7. **Positive code changes to schedule with this upgrade**
   - Add `#[SensitiveParameter]` to controller methods handling OAuth tokens/secrets (notably in `app/Http/Controllers/NetrunnerDBController.php`) so sensitive values are redacted from stack traces/logs.
   - Optionally introduce an explicit `private` filesystem disk and migrate sensitive writes there.

**Validation checkpoint (must pass to close Step 3.6):**

1. `php artisan --version` reports Laravel 12.x
2. `php artisan config:clear && php artisan cache:clear && php artisan route:list`
3. `composer show nesbot/carbon` reports Carbon 3.x
4. `cd tests && npm run test:api`
5. `cd tests && npm run test:e2e`
6. Smoke test OAuth login + tournament create/edit/delete + claim with deck flow

**References:**
- Laravel 12 upgrade guide: https://laravel.com/docs/12.x/upgrade
- Laravel 12 release notes: https://laravel.com/docs/12.x/releases

**Implementation log (started 2026-03-12):**
- [X] Added Laravel 12 repo-specific migration step after Step 3.5.
- [X] Bumped `laravel/framework` to `^12.0` and resolved Composer dependency graph.
- [X] Verified Carbon 3 in lockfile (`nesbot/carbon 3.11.3`).
- [X] Validation run completed on Laravel 12:
  - `php artisan --version` -> `Laravel Framework 12.54.1`
  - `php artisan config:clear && php artisan cache:clear && php artisan route:list` -> OK (119 routes)
  - `cd tests && npm run test:api` -> **26/26 passed**
  - `cd tests && npm run test:e2e` -> **91/91 passed**
- [X] Applied first Laravel 12 hardening improvement: `#[SensitiveParameter]` on OAuth token storage method input in `NetrunnerDBController`.
- [ ] Optional follow-up: add explicit `private` filesystem disk (`storage/app/private`) and migrate sensitive writes.

---

### Package Updates Through Phase 3 (Laravel 10 -> 11)
| Package | Current (Step 3.4) | Step 3.5 Target | Notes |
|---------|---------------------|-----------------|-------|
| laravel/framework | ^10.0 | ^11.0 | Core framework bump |
| php (runtime + composer platform) | ^8.1 / 8.1.0 | ^8.2 / 8.2.x | Required by Laravel 11 |
| laravelcollective/html | ^6.0 | Remove / replace | Blocks Laravel 11 via `illuminate/*` constraints |
| doctrine/dbal | ^3.0 | Remove | Not required for Laravel 11 |

### Phase 3 Validation
- [X] Application boots without errors (Laravel 11.48.0)
- [X] PHP 8.2+ runtime working (current runtime: PHP 8.4.12 CLI, composer platform pinned to 8.2.0)
- [X] Admin panel functions (covered by E2E auth/admin flows)
- [X] API tests pass (`npm run test:api` - schema contracts preserved, 26/26)
- [X] E2E tests pass (`npm run test:e2e`, 91/91)

---

## Phase 4: Gulp/Elixir → Vite Migration

**Goal:** Modern asset pipeline with hot module replacement.

### Tasks

1. **Remove old build tools**
   ```bash
   npm remove gulp laravel-elixir gulp-rimraf gulp-shell
   ```

2. **Install Vite**
   ```bash
   npm install --save-dev vite laravel-vite-plugin @vitejs/plugin-vue2
   ```

3. **Create `vite.config.js`**
   ```javascript
   import { defineConfig } from 'vite';
   import laravel from 'laravel-vite-plugin';
   import vue2 from '@vitejs/plugin-vue2';

   export default defineConfig({
     plugins: [
       laravel({
         input: ['resources/css/app.css', 'resources/js/app.js'],
         refresh: true,
       }),
       vue2(),
     ],
   });
   ```

4. **Update Blade templates**
   ```blade
   {{-- Before --}}
   <link href="{{ elixir('css/all.css') }}" rel="stylesheet">
   <script src="{{ elixir('js/all.js') }}"></script>

   {{-- After --}}
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   ```

5. **Convert concatenated scripts to ES modules**
   - Create `resources/js/app.js` as entry point
   - Import individual modules: `import './abr-main.js'`

### Files to Modify
- `resources/views/layout/general.blade.php` - Update asset references
- `gulpfile.js` → Delete after migration
- Create `vite.config.js`
- Create `resources/js/app.js` (entry point)

### Validation
- [ ] `npm run dev` starts Vite dev server
- [ ] `npm run build` produces production assets
- [ ] All styles render correctly
- [ ] JavaScript functionality works
- [ ] API tests pass (`npm run test:api`)
- [ ] E2E tests pass (`npm run test:e2e`)

---

## Phase 5 (Future): Vue 2 → Vue 3

**Deferred** - Keep Vue 2.7 (compatibility build) during initial migration. Vue 3 upgrade is separate project for code quality phase.

---

## Critical Files Summary

| File | Phase | Action |
|------|-------|--------|
| `composer.json` | 2, 3 | Update Laravel, PHP, packages |
| `app/Http/routes.php` | 2 | Migrate to `routes/web.php` |
| `app/Http/Controllers/NetrunnerDBController.php` | 2 | Replace OAuth package |
| `app/*.php` (models) | 2 | Move to `app/Models/` |
| `gulpfile.js` | 4 | Replace with `vite.config.js` |
| `resources/views/layout/general.blade.php` | 4 | Update asset references |
| `tests/e2e/` | 1 | E2E tests (Vitest + agent-browser) |
| `tests/api/schemas/` | 1c | Zod schemas for API contracts |
| `tests/api/tests/` | 1c | API schema validation tests |
| `tests/perf/` | 1d | Performance baseline tests |
| `tests/perf/thresholds.ts` | 1d | Endpoint threshold configuration |

---

## Laravel Version Upgrade Summary

| Step | Version | PHP | Key Breaking Changes |
|------|---------|-----|----------------------|
| 2.1 | 5.2→5.3 | 5.6.4 | Routes restructure, Collections |
| 2.2 | 5.3→5.4 | 5.6.4 | Route::controller removed |
| 2.3 | 5.4→5.5 | **7.0** | Package auto-discovery |
| 2.4 | 5.5→5.6 | 7.1.3 | Logging + hashing config, TrustProxies headers, Blade/e() encoding |
| 2.5 | 5.6→5.7 | 7.1.3 | Blade `or` removed, cache `storage/framework/cache/data`, add `public/svg` error assets, `Route::redirect` 302 default, queue key rename (`QUEUE_CONNECTION`) |
| 2.6 | 5.7→5.8 | 7.1.3 | Cache TTL in seconds, env parsing changes, Markdown mail path + notification channel extraction |
| 2.7 | 5.8→6.0 | **7.2** | `authorizeResource`→`viewAny` (if used), helper removal, queue retry default (`--tries`) + `failed_jobs`, OAuth migration to Socialite |
| 3.1 | 6.0→7.0 | **7.3** | CORS config, Symfony 5, PHP 7.3 features (trailing commas, heredoc flex, `array_key_first/last`, `is_countable`, Argon2id) |
| 3.2 | 7.0→8.0 | **7.3** | Seeder/Factory namespaces, Paginator Bootstrap, elixir()→mix(), fakerphp/faker |
| 3.3 | 8.0→9.0 | **8.0** | Flysystem 3, Symfony Mailer |
| 3.4 | 9.0→10.0 | **8.1** | Dependency updates |
| 3.5 | 10.0→11.0 | **8.2** | Remove doctrine/dbal, streamlined config |

**Bold** = PHP version upgrade required (update Docker image)

---

## Risk Mitigation

1. **Version Control Strategy**
   - Create branch per phase: `phase-0-docker`, `phase-1-e2e`, etc.
   - Tag before each phase: `pre-phase-2`, `pre-phase-3`
   - Keep `master` untouched until phase completion

2. **Database Safety**
   - `mysqldump` before each Laravel upgrade phase
   - Test migrations on copy of production data

3. **Parallel Testing**
   - Keep Cypress tests until agent-browser achieves parity
   - Run both test suites during Phase 1 transition

4. **OAuth Fallback**
   - Keep old OAuth code commented until new implementation verified

---

## Verification Strategy

After each phase, verify with:
1. **API tests** - `npm run test:api` (schema validation, ~10s)
2. **E2E tests** - `npm run test:e2e` (browser tests, 90 tests: auth, pages, CRUD, entries)
3. **Performance tests** - `npm run test:perf` (baseline comparison, ~60s)
4. **Manual smoke test** - Login, create tournament, view results
5. **Admin functions** - Card sync, user management work
