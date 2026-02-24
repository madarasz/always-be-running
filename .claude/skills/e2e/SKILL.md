---
name: e2e
description: >
  Activate for any work in the e2e/ directory: creating or editing test files
  (tests/*.test.ts), page objects (pages/), helpers (helpers/), vitest config,
  or PRACTICES.md. Enforces agent-browser conventions specific to this project.
user-invocable: false
allowed-tools: Bash(cd e2e && npm test)
---

# E2E Test Conventions

Full reference: `e2e/PRACTICES.md`

## Rules — always apply these

1. **Run tests after every change**: `cd e2e && npm test`. Never leave the session with failing tests.

2. **BrowserManager import** — direct path required (no exports map in package):
   ```typescript
   import { BrowserManager } from 'agent-browser/dist/browser.js';
   ```

3. **Playwright strict mode** — throws if a locator matches more than one element:
   - Use `.first()` when the same text appears in navbar and body
   - Use `#id` selectors when `text=` would be ambiguous
   - Examples: `.locator('text=Login via NetrunnerDB').first()`, `#created-title` not `text=Tournaments created by me`

4. **Page objects**:
   - Constructor receives `BrowserManager`, never `Page`
   - Resolve `this.page = browser.getPage()` once in `BasePage` constructor
   - Locators are `readonly` class properties, not inline in methods

5. **Session isolation**: call `clearSession(browser)` (or `page.context().clearCookies()`) at the start of any test that depends on auth state.

6. **OAuth login**: use `loginUser(browser, 'regular' | 'admin')` from `helpers/auth.ts`. It runs the full browser-based NRDB OAuth flow. Credentials live in `e2e/.env` (gitignored; template at `e2e/.env.template`).

7. **External redirects**: after submitting a form that navigates to an external site, call `page.waitForLoadState('domcontentloaded')` before interacting with the next page. Use 30 s for `waitForURL` that waits for an OAuth callback.

8. **Run location**: always `cd e2e && npm test` — `e2e/` has its own Node 20 `package.json`, separate from the repo root's Node 10 Gulp setup.
