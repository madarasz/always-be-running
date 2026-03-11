# CI E2E Tests Troubleshooting Log

**Problem Statement:** E2E tests pass locally but fail on GitHub Actions CI. From CI screenshots, CSS and JavaScript assets are not loading, resulting in blank/unstyled pages.

**Tools**: Use gh CLI tool to get information from Github.

---

## Iteration 1: Initial Analysis

### Problem Observed
- E2E tests pass locally with `docker compose --profile build run --rm node`
- CI fails with missing CSS/JS in test result screenshots
- CI workflow runs: `docker compose run --rm node` (without `--profile build`)

### Root Cause Identified
The `node` service in `docker-compose.yml` has `profiles: [build]` defined:

```yaml
node:
  command: sh -c "npm install && gulp"
  profiles:
    - build
```

Without the `--profile build` flag, Docker Compose does not activate the `node` service, so the gulp build never runs on CI.

### Solution Implemented
**File:** `.github/workflows/main.yml`

Changed line 95 from:
```yaml
run: docker compose run --rm node
```

To:
```yaml
run: docker compose --profile build run --rm node
```

### Result
**Status:** ❌ FAILED

New error appeared:
```
=== mix-manifest.json contents ===
{
    "/css/all.css": "/build/css/all-d21da57507.css",
    "/js/all.js": "/build/js/all-2f8f7d4d52.js"
}
Expected CSS: public/build/css/all-d21da57507.css
Expected JS: public/build/js/all-2f8f7d4d52.js
ERROR: CSS file not found!
```

The `mix-manifest.json` file exists (committed to git) but points to hashed files that don't exist because the build output wasn't persisting.

---

## Iteration 2: Build Artifact Management

### Problem Observed
- `public/mix-manifest.json` was tracked in git with specific hashes (e.g., `all-d21da57507.css`)
- `public/build/` directory (containing actual hashed files) is gitignored
- The gulp build runs but the built files in `public/build/` were not persisting
- Result: Manifest pointed to non-existent files, causing HTTP 500 errors

### Root Cause Identified
1. `public/mix-manifest.json` being tracked in git causes a mismatch:
   - The manifest has hashes from a previous local build
   - The actual `public/build/` files are gitignored and not available on CI
2. When Laravel's `mix()` helper reads the manifest, it returns paths to files that don't exist

### Solution Implemented
**Files:** `.github/workflows/main.yml`, `.gitignore`

Changes:
1. Removed `public/mix-manifest.json` from git tracking:
   ```bash
   git rm --cached public/mix-manifest.json
   ```

2. Added to `.gitignore`:
   ```
   public/mix-manifest.json
   ```

3. Updated CI build step to show debug output:
   ```yaml
   - name: Build frontend assets
     run: |
       docker compose --profile build run --rm node
       echo "=== Build completed ==="
       echo "Contents of public/:"
       ls -la public/
       echo "Contents of public/build/:"
       ls -laR public/build/ 2>/dev/null || echo "public/build/ does not exist!"
       echo "mix-manifest.json:"
       cat public/mix-manifest.json 2>/dev/null || echo "mix-manifest.json does not exist!"
   ```

### Result
**Status:** ❌ FAILED - HTTP 500 on "Verify app is running" step

New symptom: Application returns HTTP 500 error immediately after build step.

---

## Iteration 3: Docker Image Build and Permissions

### Problem Observed
- HTTP 500 error when accessing the application after asset build
- PHP FPM logs show: `"GET /index.php" 500`
- Suspected causes:
  1. Node Docker image not being built (has `profiles: [build]`)
  2. File permissions issue - files created by root in node container may not be readable by PHP container (www-data user)

### Root Cause Identified
1. The `docker compose build` step doesn't include `--profile build`, so the `node` image may not be built
2. Files created inside the `node` container are owned by root, potentially unreadable by the `php` container's www-data user

### Solution Implemented
**File:** `.github/workflows/main.yml`

Changes:

1. **Added explicit Node image build step:**
   ```yaml
   - name: Build Node image for gulp
     run: docker compose --profile build build node
   ```

2. **Added permissions fix step after asset build:**
   ```yaml
   # Fix permissions for files created by node container (root) so php container can read them
   - name: Fix asset permissions
     run: |
       docker compose exec -T php chown -R www-data:www-data public/build public/mix-manifest.json 2>/dev/null || echo "Permission fix skipped"
       docker compose exec -T php chmod -R 755 public/build public/mix-manifest.json 2>/dev/null || echo "Permission fix skipped"
   ```

3. **Enhanced error logging for debugging:**
   ```yaml
   - name: Verify app is running
     run: |
       HTTP_CODE=$(curl -s --max-time 30 -o /dev/null -w "%{http_code}" http://localhost:8000/)
       if [ "$HTTP_CODE" != "200" ]; then
         echo "Homepage returned HTTP $HTTP_CODE"
         echo "=== PHP FPM logs ==="
         docker compose logs php --tail=50
         echo "=== Nginx logs ==="
         docker compose logs nginx --tail=30
         echo "=== Laravel storage logs ==="
         docker compose exec -T php cat storage/logs/laravel.log 2>/dev/null | tail -50 || echo "No Laravel logs"
         exit 1
       fi
   ```

### Result
**Status:** ❌ FAILED - Build output not persisting

**CI Run:** [Tests #22926067960](https://github.com/madarasz/always-be-running/actions/runs/22926067960)

**Build step output:**
```
Contents of public/build/:
public/build/ does not exist!
mix-manifest.json:
mix-manifest.json does not exist!
```

**Key Finding:** The gulp build inside the `node` container is NOT creating any output files. The `public/build/` directory and `mix-manifest.json` are not being generated, even though the build step reports success.

**"Verify app is running" output:**
```
Homepage returned HTTP 500
=== Laravel storage logs ===
#52 /var/www/html/public/index.php(68): Illuminate\Foundation\Http\Kernel->handle(...)
#53 {main}
```

The HTTP 500 error is caused by the missing assets - Laravel's `mix()` helper fails when the manifest file doesn't exist.

---

## Iteration 4: Gulp Build Not Running in Container

### Problem Observed (from CI Run #22926067960)
- Build step "Build frontend assets" completes successfully
- Build step "Fix asset permissions" reports "Permission fix skipped" (files don't exist)
- BUT: `public/build/ does not exist!` and `mix-manifest.json does not exist!`
- The gulp build is NOT producing any output inside the container

### Root Cause Analysis
The `docker compose --profile build run --rm node` command runs the node service, but:
1. The `node` service uses `node:10-alpine` with npm install
2. `node-sass@3.x` requires Python 2 and build tools (present in Dockerfile.node)
3. The volume mount `- .:/var/www/html` should persist files to the runner's filesystem
4. **Likely issue:** The gulp build may be failing silently, or the volume mount isn't working correctly in the GitHub Actions environment

### Potential Fixes
1. **Check if gulp is actually running** - Add more verbose output to the build step
2. **Run gulp with verbose logging** - Change command to show build progress
3. **Alternative: Build in PHP container** - Install Node.js in PHP container and run gulp there
4. **Check Docker volume mount** - Verify files are being written to the mounted volume

---

## Iteration 5: Correct Root Cause - Default Gulp Task Regression

### Problem Observed (from CI Run #22939952856)
- CI still fails with missing `public/build/` and missing `public/mix-manifest.json`
- Logs show gulp runs only:
  - `Starting 'version'...`
  - `Starting 'mix-manifest'...`
- Logs do **not** show:
  - `Starting 'sass'...`
  - `Starting 'scripts'...`
  - `Starting 'styles'...`

### Root Cause Identified
The regression was introduced in commit `a876a68`:

1. Added custom task:
   - `gulp.task('mix-manifest', ['version'], ...)`
2. Overrode default task:
   - `gulp.task('default', ['version', 'mix-manifest'])`

This causes `gulp` to run only versioning + manifest conversion, and skip asset compilation tasks on fresh CI workspaces.

### Important Correction
Earlier assumption about Docker Compose profiles was incomplete:
- `docker compose run <service>` can run a profiled service when explicitly targeted.
- So the main failure here is not profile activation; it is build-task ordering/coverage.

### Solution Implemented
**Files:** `.github/workflows/main.yml`, `gulpfile.js`

1. Updated CI build step to run a deterministic ordered pipeline explicitly:
   ```yaml
   docker compose --profile build run --rm node sh -lc "
     set -e
     npm install
     gulp prepare-dirs
     gulp sass
     gulp scripts
     gulp styles
     gulp version
     gulp mix-manifest
   "
   ```

2. Added hard checks immediately after build:
   - `public/build/css` must exist
   - `public/build/js` must exist
   - `public/mix-manifest.json` must exist

3. Made `mix-manifest` a pure conversion task (removed implicit `version` dependency) so task order is explicit.

### Result
**Status:** 🔄 In progress (changes prepared locally; CI verification pending next run)

---

## Summary of Changes Made

| File | Changes |
|------|---------|
| `.github/workflows/main.yml` | 1. Added `--profile build` flag to docker compose run<br>2. Added explicit `docker compose --profile build build node` step<br>3. Added debug output to build step to show build results<br>4. Added permissions fix step<br>5. Enhanced error logging with PHP, nginx, and Laravel logs |
| `.github/workflows/main.yml` | 6. Replaced implicit `gulp` call with explicit ordered gulp pipeline (`prepare-dirs -> sass -> scripts -> styles -> version -> mix-manifest`)<br>7. Added fail-fast checks for `public/build/css`, `public/build/js`, and `public/mix-manifest.json` |
| `.gitignore` | Added `public/mix-manifest.json` to prevent committing build artifacts |
| `public/mix-manifest.json` | Removed from git tracking (deleted via `git rm --cached`) |
| `gulpfile.js` | 1. Added custom `mix-manifest` conversion task<br>2. Made `mix-manifest` task conversion-only (no implicit `version` dependency)<br>3. Added explicit ordered build tasks for reliable CI execution |
| `docs/ci-e2e-troubleshooting-log.md` | Created this troubleshooting log documenting all iterations |

---

## Key Learnings

1. **Docker Compose Profiles:** `docker compose up` honors profiles; `docker compose run <service>` can still run an explicitly targeted profiled service.

2. **Build Artifacts in Git:** `mix-manifest.json` should NOT be committed when it references dynamically-generated files. The manifest and the build output must be generated together in the same build context.

3. **Docker Volume Permissions:** Files created in one container (node) may have different ownership than what another container (php) expects. Permission fixes may be needed after cross-container file operations.

4. **Debug Output:** Adding verbose logging at each step helps identify exactly where the build process fails.

5. **Legacy Elixir/Gulp Pipelines Need Explicit Ordering in CI:** Relying on implicit `default` behavior is fragile after task overrides; explicit ordered task execution is safer.

---

## Next Steps for Further Troubleshooting

If CI still fails, investigate:

1. **Check CI run logs** for:
   - Whether gulp build actually completes successfully
   - Whether `public/build/` directory is created
   - Whether `public/mix-manifest.json` is generated with correct paths
   - Laravel error logs for specific exception messages

2. **Potential additional fixes:**
   - Verify the new explicit ordered pipeline produces `public/build/*` and `public/mix-manifest.json`
   - If CI still fails, capture full `npm install` + gulp output (not only failed-step logs)
   - Consider running node build in one reusable container session to reduce variability

3. **Alternative approach:** If Docker volume mounts continue to cause issues, consider:
   - Building assets inside the PHP container instead
   - Using a multi-stage Docker build
   - Pre-building assets and committing them (not recommended for long-term)

---

## Reference Files

- **Gulpfile:** `gulpfile.js` - Defines `version` and `mix-manifest` tasks
- **Docker Compose:** `docker-compose.yml` - Node service with gulp build command
- **Node Dockerfile:** `docker/Dockerfile.node` - Node 10 Alpine with build tools
- **Layout Template:** `resources/views/layout/general.blade.php` - Uses `mix()` helper
- **Migration Plan:** `MIGRATION_PLAN.md` - Documents Laravel 8 migration with elixir→mix changes

---

*Last updated: 2026-03-11*
*Author: Claude Code*

## CI Run History

| Run ID | Commit | Status | Duration | Notes |
|--------|--------|--------|----------|-------|
| 22939952856 | 01def6b | ❌ Failed | 4m16s | Regression confirmed: gulp ran `version` + `mix-manifest` only; no `sass/scripts/styles`; no build artifacts generated |
| 22926067960 | 12a1c92 | ❌ Failed | 4m10s | Build output not persisting - `public/build/` and `mix-manifest.json` don't exist after build step |
| 22925852569 | 382c927 | ❌ Failed | 3m41s | HTTP 500 - mix-manifest.json existed but pointed to non-existent files |
| 22925421571 | 0af6475 | ❌ Failed | 4m23s | Missing --profile build flag |
| 22924054719 | (earlier) | ❌ Failed | 12m36s | Initial failure - missing CSS/JS assets |
