# E2E CI Progress Log

## Goal
Get E2E tests running on GitHub Actions with at least 90% pass rate.

## Current Status
**Date**: 2026-02-28
**Branch**: `migration-e2e-workflow` (split from `migration`)
**Status**: 🔴 Failing - API endpoint returns error page

## Test Files (9 total)
- auth.test.ts
- legal.test.ts
- prizes.test.ts
- profile.test.ts
- upcoming.test.ts
- videos.test.ts
- results.test.ts
- tournament-details.test.ts
- personal.test.ts

---

## Iteration Log

### Iteration 1 - 2026-02-28

**Problem**: Workflow fails at "Clear Laravel caches" step
- `php artisan tinker --execute` doesn't exist in Laravel 5.2
- Error: `The "--execute" option does not exist.`

**Root Cause**: Uncommitted local changes in `.github/workflows/main.yml` that use inline PHP instead of tinker.

**Action**: Pushed fix using inline PHP script instead of tinker.

**Result**: ✅ Tinker issue fixed, but revealed new OAuth issue.

---

### Iteration 2 - 2026-02-28

**Problem**: OAuth redirect endpoint returns HTTP 500
- Error: `Call to a member function getAuthorizationUri() on null`
- `OAuth2::consumer('NetrunnerDB', ...)` returns `null`

**Root Cause**:
- Custom `NetrunnerDB.php` OAuth service file is tracked in git (in vendor/lusitanian/oauth/...)
- But `composer install` overwrites the vendor directory, removing the custom file
- The OAuth service factory can't find 'NetrunnerDB' service

**Action**: Added workflow step to restore the custom OAuth service file after composer install.

**Result**: ✅ OAuth service restored successfully. Tests now reach the test execution phase.

---

### Iteration 3 - 2026-02-28

**Problem**: Tests reach execution but fail (17 passed, 42 failed, 21 skipped)
- Main failures in `upcoming.test.ts` - timeout waiting for `#discover-table tbody tr`
- The upcoming page shows only future tournaments
- Test seed data has tournaments dated 2017-2025
- Current date (2026-02-28) means all seed tournaments are in the past

**Root Cause**: No date mocking in tests - upcoming page shows no data because seed tournaments are all past.

**Action**: Initial attempt - browser date mocking. Didn't work because filtering is server-side.

**Result**: ❌ Still failing - date filtering happens in PHP, not JavaScript.

---

### Iteration 4 - 2026-02-28

**Problem**: Browser date mocking doesn't help - tournament filtering is server-side (PHP)

**Root Cause**: The "upcoming" tournaments are filtered in PHP using the server's date, not the browser's date.

**Action**: Added SQL UPDATE step in workflow to set some tournament dates to future dates (+7, +30, +60 days from current date).

**Result**: ✅ SQL update worked (61 upcoming tournaments). But tests still fail.

---

### Iteration 5 - 2026-02-28

**Discovery**: The `/api/tournaments/upcoming` API endpoint returns a **Symfony error page**, NOT JSON data!

**Evidence from CI logs**:
```
Testing API endpoint for upcoming tournaments ===
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="noindex,nofollow" />
        <style>
            /* Copyright (c) 2010, Yahoo! Inc...
```

**This explains why all upcoming/results tests fail** - the table never loads because the AJAX call fails.

**Previous Failed Runs**:
- `22526933427` (2026-02-28) - API returns error page
- `22520421592` (2026-02-28) - 17/42 (same - API issue)
- `22515725746` (2026-02-28) - 17/42 (same)
- `22515493565` (2026-02-28) - OAuth null pointer
- `22492042013` (2026-02-27) - tinker --execute failure

---

## Current Evaluation Summary

### Test Results: 17 passed, 42 failed, 21 skipped (out of 80)
- **Pass rate**: ~29% (17/59 non-skipped)
- **Target**: 90%

### Working Tests (4 test files pass)
- `legal.test.ts` - 5 tests (cookie banner, privacy page)
- `videos.test.ts` - 2 tests (video list, switching)
- `personal.test.ts` - 2 tests (photos, videos tabs)
- `auth.test.ts` - 4 tests (login, access control)
- `prizes.test.ts` - 3 tests (prize kits)
- `profile.test.ts` - 1 test (profile page)

### Failing Tests (5 test files fail)
- `upcoming.test.ts` - ALL tests fail (table never loads - API broken)
- `results.test.ts` - Most tests fail (same API issue)
- `tournament-details.test.ts` - Some tests fail
- Plus various auth-dependent tests skipped

### Root Cause Analysis
**The `/api/tournaments/upcoming` endpoint returns an error page instead of JSON.**

This is a **Laravel/PHP error**, not a test issue. The API endpoint needs to be investigated.

### Next Steps (To reach 90%)
1. **Debug the API error** - Check Laravel logs, TournamentsController
2. **Fix the API** - Resolve whatever exception is being thrown
3. **Re-run tests** - Once API works, the 42 failing tests should pass

---

## Notes
- Local Docker stack works (abr-nginx:8000, abr-php, abr-mysql:3307)
- E2E tests use separate Node 20 environment in `e2e/` folder
- Tests use Playwright with system Chrome
