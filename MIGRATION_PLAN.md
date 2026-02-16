# AlwaysBeRunning Migration Plan: Legacy to Modern Stack

## Overview

Migrate AlwaysBeRunning from Laravel 5.2/PHP 5.5/Gulp to Laravel 11/PHP 8.2/Vite with minimal code changes, validated by E2E tests migrated from Cypress to Playwright.

## Current State Summary

| Component | Current | Target |
|-----------|---------|--------|
| Laravel | 5.2.* (EOL 2017) | 11.x |
| PHP | >= 5.5.9 | 8.2+ |
| Build | Gulp 3.9.1 + Elixir 5.0 | Vite |
| Frontend | Vue 2.5.17, jQuery 2.2.3, Bootstrap 4-alpha | Vue 2.7 (keep), modernize later |
| E2E Tests | Cypress 7.1.0 + Cucumber | Playwright + playwright-bdd |
| Routes | Single `app/Http/routes.php` (130+ routes) | `routes/web.php` + `routes/api.php` |
| Models | In `app/` root (17 models) | Move to `app/Models/` |

---

## Decisions Made

1. **Laravel Migration:** MANUAL - follow official Laravel upgrade guides for each version.
2. **OAuth Priority:** CRITICAL - must work at every migration checkpoint.
3. **Test Coverage:** Add Tournament CRUD tests before migration (highest risk area).
4. **Deployment Target:** VPS / Traditional Server (nginx, PHP-FPM, MySQL).

---

## Phase 0: Docker Environment for Legacy Stack

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
- [X] `gulp` builds assets
- [X] OAuth login with NetrunnerDB works 
  - **we need localhost redirect URL from NetrunnerDB**
- [X] Application serves at localhost:8000

---

## Phase 1: Migrate E2E Tests from Cypress to Playwright

**Goal:** Modern test suite that validates the application before/during/after Laravel upgrade.

### Current Test Coverage (18 scenarios)
- `auth.feature` (3 scenarios): Login flows, access control
- `upcoming.feature` (5 scenarios): Tournament table, filtering, calendar, map
- `results.feature` (9 scenarios): Results display, pagination, filtering, statistics
- `legal.feature` (1 scenario): Cookie consent

### Tasks

1. **Install Playwright + playwright-bdd**
   ```bash
   npm init playwright@latest
   npm install playwright-bdd
   ```

2. **Create `playwright.config.ts`**
   ```typescript
   import { defineConfig } from '@playwright/test';
   import { defineBddConfig } from 'playwright-bdd';

   const testDir = defineBddConfig({
     features: 'e2e/features/*.feature',
     steps: 'e2e/steps/**/*.ts',
   });

   export default defineConfig({
     testDir,
     use: { baseURL: 'http://localhost:8000' },
   });
   ```

3. **Copy feature files** (unchanged)
   - `cypress/integration/*.feature` → `e2e/features/*.feature`

4. **Convert step definitions** (syntax changes required)

   | Cypress Pattern | Playwright Pattern |
   |-----------------|-------------------|
   | `cy.visit(url)` | `await page.goto(url)` |
   | `cy.get(sel)` | `page.locator(sel)` |
   | `cy.intercept()` | `await page.route()` |
   | `.should('have.length', n)` | `await expect(locator).toHaveCount(n)` |

5. **Convert API mocking**
   ```typescript
   // Before (Cypress)
   cy.intercept('GET', '/api/tournaments', { fixture: 'upcoming.json' });

   // After (Playwright)
   await page.route('/api/tournaments', route =>
     route.fulfill({ json: require('../fixtures/upcoming.json') })
   );
   ```

6. **Convert OAuth helper** (`cypress/integration/common/auth.js` → `e2e/helpers/auth.ts`)

### Files to Create
- `playwright.config.ts`
- `e2e/features/*.feature` (4 files, copied)
- `e2e/steps/common/navigation.ts`
- `e2e/steps/common/elements.ts`
- `e2e/steps/common/forms.ts`
- `e2e/steps/common/auth.ts`
- `e2e/steps/common/map.ts`
- `e2e/steps/upcoming/upcoming.ts`
- `e2e/steps/results/results.ts`
- `e2e/steps/legal/legal.ts`
- `e2e/fixtures/*.json` (9 files, copied)

### Validation
- [ ] All 18 scenarios pass
- [ ] OAuth login works for regular and admin users
- [ ] API mocking works correctly
- [ ] Visual snapshots match (if retained)

---

## Phase 1b: Add Tournament CRUD Tests

**Goal:** Increase test coverage for high-risk write operations before migration.

### New Scenarios to Add

Create `e2e/features/tournament-crud.feature`:

```gherkin
Feature: Tournament Management

    Background:
        Given I login with "regular" user

    Scenario: Create a new tournament
        When I open the "Organize" page
        And I click on "Create tournament" button
        And I fill in tournament details:
            | field       | value                    |
            | title       | Test Tournament          |
            | date        | 2025-06-01               |
            | type        | GNK / seasonal           |
            | format      | standard                 |
        And I submit the tournament form
        Then I see text "Tournament created"
        And I see tournament "Test Tournament" in my tournaments

    Scenario: Edit an existing tournament
        Given I have a tournament "My Test Event"
        When I open tournament edit page for "My Test Event"
        And I change the title to "Updated Event Name"
        And I save changes
        Then I see text "Tournament updated"

    Scenario: Delete a tournament
        Given I have a tournament "Tournament to Delete"
        When I delete tournament "Tournament to Delete"
        And I confirm the deletion
        Then I don't see "Tournament to Delete" in my tournaments

    Scenario: Conclude a tournament with results
        Given I have an ongoing tournament "Finished Tournament"
        When I open tournament "Finished Tournament"
        And I click "Conclude" button
        And I enter top cut results
        And I submit conclusion
        Then tournament appears in Results page
```

### Implementation Notes
- Requires database seeding or direct API setup for test data
- May need to mock date/time for consistent test data
- Tournament deletion uses soft-delete, verify in database

### Validation
- [ ] All new CRUD scenarios pass
- [ ] Tournament appears in Results after conclusion
- [ ] Soft-delete works correctly

---

## Phase 2: Laravel 5.2 → 6.0 Upgrade (Manual)

**Goal:** First major upgrade milestone, modernizing routing and reaching PHP 7.2+.

### Upgrade Path
5.2 → 5.3 → 5.4 → 5.5 → 5.6 → 5.7 → 5.8 → 6.0

**Approach:** Follow official Laravel upgrade guides for each version. Run Playwright tests after each version bump.

---

### Step 2.1: Laravel 5.2 → 5.3
**Guide:** https://laravel.com/docs/5.3/upgrade

**PHP Requirement:** >= 5.6.4

**Key Changes:**
1. **Routes restructuring (MAJOR)**
   - Create `routes/web.php` and `routes/api.php`
   - Move routes from `app/Http/routes.php`
   - Update `app/Providers/RouteServiceProvider.php` to load new route files

2. **User model changes**
   ```php
   use Illuminate\Notifications\Notifiable;

   class User extends Authenticatable
   {
       use Notifiable;
   }
   ```

3. **Query builder returns Collections**
   - `DB::table()->get()` returns `Collection` instead of array
   - Add `->all()` if array needed

4. **composer.json**
   ```json
   "laravel/framework": "5.3.*"
   ```

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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
- [ ] Playwright tests pass (all 22 scenarios)

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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

**Validation checkpoint:** Run Playwright tests

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
- [ ] All API endpoints respond
- [ ] Playwright tests pass (all 22 scenarios)

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
- [ ] Playwright tests pass

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
| `cypress/` | 1 | Migrate to `e2e/` (Playwright) |

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
   - Create branch per phase: `phase-0-docker`, `phase-1-playwright`, etc.
   - Tag before each phase: `pre-phase-2`, `pre-phase-3`
   - Keep `master` untouched until phase completion

2. **Database Safety**
   - `mysqldump` before each Laravel upgrade phase
   - Test migrations on copy of production data

3. **Parallel Testing**
   - Keep Cypress tests until Playwright achieves parity
   - Run both test suites during Phase 1 transition

4. **OAuth Fallback**
   - Keep old OAuth code commented until new implementation verified

---

## Verification Strategy

After each phase, verify with:
1. **Playwright E2E tests** - All scenarios pass (18 existing + 4 new CRUD)
2. **Manual smoke test** - Login, create tournament, view results
3. **API check** - `/api/tournaments`, `/api/entries` return correct data
4. **Admin functions** - Card sync, user management work
