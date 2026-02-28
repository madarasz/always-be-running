# E2E CI Progress Log

## Goal
Get E2E tests running on GitHub Actions with at least 90% pass rate.

## Current Status
**Date**: 2026-02-28
**Status**: 🔴 Failing - Workflow setup issues

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

**Action**: Added `mockBrowserDate(browser, '2025-01-01')` to upcoming tests so seed data appears as "upcoming".

**Previous Failed Runs**:
- `22515725746` (2026-02-28) - 17 passed, 42 failed (29% pass rate)
- `22515493565` (2026-02-28) - OAuth null pointer
- `22492042013` (2026-02-27) - tinker --execute failure

---

## Notes
- Local Docker stack works (abr-nginx:8000, abr-php, abr-mysql:3307)
- E2E tests use separate Node 20 environment in `e2e/` folder
- Tests use Playwright with system Chrome
