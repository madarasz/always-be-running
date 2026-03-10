# Laravel 7 → 8 Migration Research

> Based on the [official upgrade guide](https://laravel.com/docs/8.x/upgrade) and [release notes](https://laravel.com/docs/8.x/releases), cross-referenced with the AlwaysBeRunning codebase.

---

## Required Breaking Changes

### 🔴 HIGH IMPACT

#### 1. Seeder & Factory Namespaces
**What changed:** Seeders and factories are now namespaced.

**Codebase impact: YES** — 8 seeder files in `database/seeds/` have no namespace, and `database/factories/ModelFactory.php` uses the legacy callback style.

Required actions:
- Rename `database/seeds/` → `database/seeders/`
- Add `namespace Database\Seeders;` to all 8 seeder files
- Update `composer.json` autoload: remove `"classmap": ["database"]` and add PSR-4 mappings:
  ```json
  "psr-4": {
      "App\\": "app/",
      "Database\\Factories\\": "database/factories/",
      "Database\\Seeders\\": "database/seeders/"
  }
  ```
- Run `composer dump-autoload`

#### 2. Model Factories Rewritten as Classes
**What changed:** Factories are now class-based, not callback-based. The old `$factory->define()` syntax no longer works.

**Codebase impact: YES** — `database/factories/ModelFactory.php` uses the old `$factory->define(App\User::class, ...)` syntax.

Options:
- **Quick path:** Install `laravel/legacy-factories` to keep the old factory — no changes to existing factory code needed
- **Clean path:** Rewrite `ModelFactory.php` as a class-based `UserFactory` (recommended if you want to modernize fully)

> [!TIP]
> Since there's only one factory (UserFactory) and it's trivial, the clean path is minimal effort and prevents carrying legacy debt forward.

#### 3. Pagination Defaults → Tailwind CSS
**What changed:** Paginator now defaults to Tailwind CSS instead of Bootstrap.

**Codebase impact: LIKELY** — The app uses Bootstrap 4. While no `->paginate()` calls were found in controllers, this should still be set defensively.

Required action — add to `AppServiceProvider::boot()`:
```php
use Illuminate\Pagination\Paginator;

Paginator::useBootstrap();
```

#### 4. Queue `retryAfter` → `backoff`, `timeoutAt` → `retryUntil`
**What changed:** Method/property renames in queued jobs, mailers, notifications, listeners.

**Codebase impact: NO** — No `retryAfter` or `timeoutAt` usage found in the codebase.

#### 5. Queue `allOnQueue()` / `allOnConnection()` Removed
**What changed:** These methods are removed from job chaining. Use `onQueue()`/`onConnection()` instead.

**Codebase impact: NO** — No usage found.

---

### 🟡 MEDIUM IMPACT

#### 6. PHP 7.3.0 Required
**What changed:** Minimum PHP version bumped from 7.2.5 to 7.3.0.

**Codebase impact: ALREADY MET** — `composer.json` already requires `"php": ">=7.3.0"`.

#### 7. Failed Jobs Table Batch Support (Optional)
**What changed:** If you want to use job batching, add a `uuid` column to `failed_jobs`.

**Codebase impact: OPTIONAL** — Only needed if job batching is desired. The app doesn't currently use heavy queue features.

#### 8. `assertExactJson` Behavior Change
**What changed:** Array key ordering now matters for `assertExactJson`.

**Codebase impact: NO** — No PHP-level test assertions use this (E2E/API tests are in TypeScript/Vitest).

---

### 🟢 LOW IMPACT

#### 9. Collection `isset` Behavior Change
**What changed:** `isset($collection[0])` returns `false` for `null` values (previously `true`).

**Codebase impact: UNLIKELY** — No code path uses `isset` on collection elements with explicit `null` values.

#### 10. `elixir()` Helper Removed
**What changed:** The deprecated `elixir()` helper is fully removed.

**Codebase impact: YES** ⚠️ — Found in `resources/views/layout/general.blade.php`:
```blade
<link rel="stylesheet" href="{{ elixir('css/all.css') }}">
<script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
```

> [!CAUTION]
> This will cause a **fatal error** in Laravel 8 since the helper is completely removed. Must replace with `mix()` or direct asset paths before upgrading. This was likely left behind from the Gulp→Mix discussion but was never addressed.

Required action — replace `elixir()` with a working asset helper. Since the build system still uses Gulp and assets are compiled to `public/`, a simple direct path or a custom helper is needed.

#### 11. `Illuminate\Support\Manager::$app` Removed
**What changed:** Use `$container` property instead.

**Codebase impact: NO** — No custom Manager subclasses found.

#### 12. `sendNow` Mail Method Removed
**What changed:** Use `send()` instead.

**Codebase impact: NO** — No `sendNow` usage found.

#### 13. Eloquent `increment`/`decrement` Now Fire Events
**What changed:** Model events (`updating`, `saving`) now fire during `increment()`/`decrement()`.

**Codebase impact: LOW** — Should audit if any model observers rely on event-triggering assumptions.

#### 14. Maintenance Mode Updates
**What changed:** Pre-rendering maintenance templates now supported. `--message` option removed from `php artisan down`.

**Codebase impact: OPTIONAL** — Add maintenance mode pre-rendering support to `public/index.php`:
```php
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}
```

#### 15. `EventServiceProvider::register()` Must Call Parent
**What changed:** If `register()` exists, must call `parent::register`.

**Codebase impact: CHECK** — Verify `EventServiceProvider` doesn't override `register()`.

#### 16. Scheduling: `cron-expression` Library Updated 2.x → 3.x
**What changed:** Internal dependency update.

**Codebase impact: NO** — Unless the app directly uses `dragonmantank/cron-expression`.

#### 17. Session Contract: New `pull` Method
**What changed:** New method added to the session contract.

**Codebase impact: NO** — Unless a custom session driver implements this contract.

---

## Routing Change (Optional but Recommended)

### Controller Namespace Prefixing
**What changed:** In Laravel 8, `RouteServiceProvider` can optionally stop auto-prefixing `App\Http\Controllers` to route declarations, enabling PHP callable syntax for routes.

**Current state:** `RouteServiceProvider.php` already has `$namespace = 'App\Http\Controllers'` and uses the old `map()` + `mapWebRoutes()` / `mapApiRoutes()` pattern.

**Recommendation:** Keep `$namespace` set for now (backward compatible, no changes needed to existing routes). Optionally modernize to the new `$this->routes()` closure-based boot method:

```php
public function boot()
{
    $this->configureRateLimiting();

    $this->routes(function () {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));

        Route::prefix('api')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    });
}
```

---

## Dependency Updates Required

| Package | Current | Target | Notes |
|---------|---------|--------|-------|
| `laravel/framework` | `^7.0` | `^8.0` | Core upgrade |
| `phpunit/phpunit` | `^8.0` | `^9.0` | Required |
| `guzzlehttp/guzzle` | (transitive) | `^7.0.1` | If explicitly required |
| `fzaninotto/faker` | `~1.4` | **Remove** | Abandoned; Laravel 8 bundles `fakerphp/faker` |
| `mockery/mockery` | `0.9.*` | `^1.4` | Update for PHP compatibility |
| `laravelcollective/html` | `^6.0` | `^6.0` | Should stay compatible |
| `laravel/socialite` | `^4.4` | `^5.0` | If upgrading to latest |
| `doctrine/dbal` | `^2.10` | `^2.10` | Compatible |

> [!IMPORTANT]
> `fzaninotto/faker` is **abandoned** and must be replaced with `fakerphp/faker`. Laravel 8 uses `fakerphp/faker` internally. Since the factory is trivial, this is straightforward.

---

## New Features / Positive Code Changes (Laravel 8)

### ✅ Available for Adoption

#### 1. `app/Models/` Directory Convention
Laravel 8 introduces `app/Models/` as the default location for Eloquent models. The MIGRATION_PLAN already targets moving models to `app/Models/` — this is the ideal time to make that move since artisan generators will now respect it.

#### 2. Migration Squashing (`schema:dump`)
With 67 migration files in `database/migrations/`, this is a great candidate for squashing:
```bash
php artisan schema:dump --prune
```
This creates a single SQL dump in `database/schema/` which runs first during `php artisan migrate`.

#### 3. Class-based Model Factories
Instead of using `laravel/legacy-factories`, rewrite to the new class-based syntax with `HasFactory` trait — provides IDE jump-to-definition, type-safe states, and relationship support.

#### 4. Improved Rate Limiting
New `RateLimiter::for()` syntax with named rate limiters, more flexible than the old `throttle:N` middleware. Can define per-user or per-IP limits:
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

#### 5. Time Testing Helpers
New `$this->travel()` and `$this->travelTo()` methods in test classes — useful if PHP unit tests are added later.

#### 6. Event Listener Improvements
Closure-based event listeners with automatic event type detection:
```php
Event::listen(function (TournamentCreated $event) {
    // Automatically resolved from type hint
});
```

#### 7. Improved Maintenance Mode
Secret-based bypass instead of IP allow-list. Pre-rendered maintenance views work without bootstrapping the framework.

#### 8. Job Batching
New `Bus::batch()` for dispatching groups of jobs with `then`/`catch`/`finally` callbacks — useful if batch operations (e.g., BCP score refreshes) are ever needed.

---

## Summary: Action Items for This Step

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 1 | Update `composer.json` dependencies | Required | Low |
| 2 | Rename `database/seeds/` → `database/seeders/`, add namespaces | Required | Low |
| 3 | Handle `ModelFactory.php` (use `laravel/legacy-factories` OR rewrite) | Required | Low |
| 4 | Update `composer.json` autoload (remove `classmap`, add PSR-4) | Required | Low |
| 5 | Add `Paginator::useBootstrap()` to `AppServiceProvider` | Required | Trivial |
| 6 | **Replace `elixir()` calls** in Blade layout | **Critical** | Low |
| 7 | Add maintenance mode pre-rendering to `public/index.php` | Recommended | Trivial |
| 8 | Modernize `RouteServiceProvider` boot method | Optional | Low |
| 9 | Replace `fzaninotto/faker` with `fakerphp/faker` | Required | Low |
| 10 | Update `mockery/mockery` to `^1.4` | Recommended | Low |
| 11 | Consider migration squashing (67 files) | Optional | Low |
| 12 | Consider moving models to `app/Models/` | Optional | Medium |
