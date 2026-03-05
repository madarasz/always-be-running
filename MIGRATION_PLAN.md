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

### Step 2.2: Laravel 5.3 → 5.4
**Guide:** https://laravel.com/docs/5.4/upgrade

**PHP Requirement:** >= 5.6.4

**Key Changes:**
1. **Route helper changes**
   - `Route::controller()` removed - convert to explicit routes

2. **Middleware changes**
   - Review custom middleware signatures

3. **Tinker extracted**
   ```bash
   composer require laravel/tinker
   ```

4. **composer.json**
   ```json
   "laravel/framework": "5.4.*"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.3: Laravel 5.4 → 5.5
**Guide:** https://laravel.com/docs/5.5/upgrade

**PHP Requirement:** >= 7.0.0 (UPDATE DOCKER!)

**Key Changes:**
1. **Package auto-discovery** - update service providers

2. **Exception rendering**
   - `render()` method signature changed

3. **Mail changes**
   - `Mailable` class changes

4. **composer.json**
   ```json
   "laravel/framework": "5.5.*",
   "php": ">=7.0.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.4: Laravel 5.5 → 5.6
**Guide:** https://laravel.com/docs/5.6/upgrade

**PHP Requirement:** >= 7.1.3

**Key Changes:**
1. **Logging configuration**
   - New `config/logging.php` file
   - Update logging calls

2. **Blade changes**
   - `@parent` directive changes

3. **composer.json**
   ```json
   "laravel/framework": "5.6.*",
   "php": ">=7.1.3"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.5: Laravel 5.6 → 5.7
**Guide:** https://laravel.com/docs/5.7/upgrade

**PHP Requirement:** >= 7.1.3

**Key Changes:**
1. **Pagination**
   - Default pagination views updated

2. **Resources directory**
   - `resources/assets` → `resources` (js, sass moved up)

3. **composer.json**
   ```json
   "laravel/framework": "5.7.*"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.6: Laravel 5.7 → 5.8
**Guide:** https://laravel.com/docs/5.8/upgrade

**PHP Requirement:** >= 7.1.3

**Key Changes:**
1. **Environment file changes**
   - `putenv()` calls cached

2. **Cache TTL in seconds**
   - Cache duration now in seconds (was minutes)

3. **composer.json**
   ```json
   "laravel/framework": "5.8.*"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 2.7: Laravel 5.8 → 6.0 (LTS)
**Guide:** https://laravel.com/docs/6.x/upgrade

**PHP Requirement:** >= 7.2.0

**Key Changes:**
1. **Authorization**
   - Policy changes

2. **Carbon 2.0**
   - Date handling changes

3. **Eloquent**
   - `BelongsTo::update()` changes

4. **Remove Helpers package** (if using)
   ```bash
   composer require laravel/helpers
   ```

5. **composer.json**
   ```json
   "laravel/framework": "^6.0",
   "php": ">=7.2.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### OAuth Package Replacement (During Phase 2)

Replace `oriceon/oauth-5-laravel` with Laravel Socialite + custom provider:

1. **Install Socialite**
   ```bash
   composer require laravel/socialite
   ```

2. **Create custom NetrunnerDB provider**
   - File: `app/Socialite/NetrunnerDBProvider.php`

3. **Update NetrunnerDBController.php**

4. **Update config/services.php**

---

### Package Updates Required
| Package | Current | Target (6.0) | Action |
|---------|---------|--------------|--------|
| laravelcollective/html | ^5.0 | ^6.0 | Update |
| intervention/image | ^2.3 | ^2.7 | Update |
| oriceon/oauth-5-laravel | dev-master | - | Replace with Socialite |
| webpatser/laravel-countries | dev-master | - | Find alternative or fork |
| doctrine/dbal | ^2.9 | ^2.10 | Update |

### Phase 2 Validation
- [ ] All routes accessible
- [ ] OAuth login works with new Socialite provider
- [ ] All CRUD operations function
- [ ] API tests pass (`npm run test:api`)
- [ ] E2E tests pass (`npm run test:e2e`)

---

## Phase 3: Laravel 6.0 → 11.0 Upgrade (Manual)

**Goal:** Complete Laravel modernization with PHP 8.2+.

### Upgrade Path
6.0 → 7.0 → 8.0 → 9.0 → 10.0 → 11.0

---

### Step 3.1: Laravel 6.0 → 7.0
**Guide:** https://laravel.com/docs/7.x/upgrade

**PHP Requirement:** >= 7.2.5

**Key Changes:**
1. **Symfony 5 components**

2. **CORS configuration**
   - New `config/cors.php` file

3. **Factory changes**
   - Class-based factories introduced (optional)

4. **composer.json**
   ```json
   "laravel/framework": "^7.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 3.2: Laravel 7.0 → 8.0
**Guide:** https://laravel.com/docs/8.x/upgrade

**PHP Requirement:** >= 7.3.0

**Key Changes:**
1. **Models directory (MAJOR)**
   - Move models from `app/` to `app/Models/`
   - Update all namespace references

2. **Factory classes**
   - Convert to class-based factories (recommended)

3. **Pagination views**
   - Tailwind CSS default (keep Bootstrap with config)

4. **Route caching**
   - Closure routes cannot be cached

5. **composer.json**
   ```json
   "laravel/framework": "^8.0",
   "php": ">=7.3.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 3.3: Laravel 8.0 → 9.0
**Guide:** https://laravel.com/docs/9.x/upgrade

**PHP Requirement:** >= 8.0.2

**Key Changes:**
1. **PHP 8.0 minimum (UPDATE DOCKER!)**

2. **Flysystem 3.x**
   - Filesystem adapter changes

3. **Symfony Mailer**
   - SwiftMailer → Symfony Mailer

4. **Custom casts**
   - Cast class changes

5. **composer.json**
   ```json
   "laravel/framework": "^9.0",
   "php": ">=8.0.2"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 3.4: Laravel 9.0 → 10.0
**Guide:** https://laravel.com/docs/10.x/upgrade

**PHP Requirement:** >= 8.1.0

**Key Changes:**
1. **PHP 8.1 minimum**
   - Use enums, readonly properties where beneficial

2. **Minimum dependency versions**
   - Various package updates required

3. **composer.json**
   ```json
   "laravel/framework": "^10.0",
   "php": ">=8.1.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Step 3.5: Laravel 10.0 → 11.0
**Guide:** https://laravel.com/docs/11.x/upgrade

**PHP Requirement:** >= 8.2.0

**Key Changes:**
1. **PHP 8.2 minimum (UPDATE DOCKER!)**

2. **Streamlined application structure**
   - Many config files optional
   - Slimmer `bootstrap/app.php`

3. **Remove doctrine/dbal**
   - Use native Schema column modifiers
   ```php
   // Must specify all modifiers when changing columns
   $table->integer('votes')->unsigned()->default(1)->change();
   ```

4. **Per-second rate limiting**
   - Rate limiter syntax changes

5. **composer.json**
   ```json
   "laravel/framework": "^11.0",
   "php": ">=8.2.0"
   ```

**Validation checkpoint:** Run API and E2E tests

---

### Package Updates Through Phase 3
| Package | 6.0 | 11.0 | Notes |
|---------|-----|------|-------|
| laravelcollective/html | ^6.0 | ^6.4 or migrate to Blade | Consider native Blade components |
| intervention/image | ^2.7 | ^3.0 | API changes in v3 |
| doctrine/dbal | ^2.10 | Remove | Use native Schema methods |

### Phase 3 Validation
- [ ] Application boots without errors
- [ ] PHP 8.2 features working
- [ ] Admin panel functions
- [ ] API tests pass (`npm run test:api` - schema contracts preserved)
- [ ] E2E tests pass (`npm run test:e2e`)

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
| 2.4 | 5.5→5.6 | 7.1.3 | Logging config |
| 2.5 | 5.6→5.7 | 7.1.3 | Resources directory |
| 2.6 | 5.7→5.8 | 7.1.3 | Cache TTL in seconds |
| 2.7 | 5.8→6.0 | **7.2** | Carbon 2, policy changes |
| 3.1 | 6.0→7.0 | 7.2.5 | CORS config, Symfony 5 |
| 3.2 | 7.0→8.0 | **7.3** | Models to app/Models/ |
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
