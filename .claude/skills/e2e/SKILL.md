---
name: e2e
description: >
  Activate for any work in the tests/e2e/ directory: creating or editing test files
  (tests/*.test.ts), page objects (pages/), helpers (helpers/), or vitest config.
  Enforces agent-browser conventions specific to this project.
user-invocable: false
allowed-tools: Bash(cd tests && npm test)
---

# E2E Test Conventions

## Setup Notes

- **Separate `tests/package.json`**: Root package.json uses Node 10/npm 6 for Gulp builds. Tests live in `tests/` with their own `package.json` (Node 20 / modern npm). Run tests from inside `tests/` with `npm test`.
- **BrowserManager import**: `import { BrowserManager } from 'agent-browser/dist/browser.js'` (no exports map in the package, so the direct path import is required)
- **System Chrome**: `executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'` — no need to install Playwright browsers separately
- **Credentials**: Copy `tests/e2e/.env.template` to `tests/e2e/.env` and fill in NetrunnerDB usernames/passwords. The `.env` file is gitignored.

## Rules — always apply these

1. **Run tests after every change**: `cd tests && npm test`. Never leave the session with failing tests.

2. **Playwright strict mode** — throws if a locator matches more than one element:
   - Use `.first()` when the same text appears in navbar and body
   - Use `#id` selectors when `text=` would be ambiguous
   - Examples: `.locator('text=Login via NetrunnerDB').first()`, `#created-title` not `text=Tournaments created by me`

3. **Each test navigates fresh** — don't use `beforeAll` to navigate once. Each `it()` should call `page.open()` to ensure clean state and prevent test pollution.

4. **Don't expect specific data values** — test data changes. Use flexible assertions:
   - `expect(count).toBeGreaterThan(0)` not `expect(count).toBe(10)`
   - `expect(filteredCount).toBeLessThanOrEqual(initialCount)`
   - `expect(data.title?.trim().length).toBeGreaterThan(0)`

5. **Session isolation**: call `clearSession(browser)` (or `page.context().clearCookies()`) at the start of any test that depends on auth state.

6. **External redirects**: after submitting a form that navigates to an external site, call `page.waitForLoadState('domcontentloaded')` before interacting with the next page. Use 30 s for `waitForURL` that waits for an OAuth callback.

7. **Add small waits after JS actions**: Use `waitForTimeout(300-500)` after clicks that trigger JavaScript state changes (paging, filtering, toggles).

8. **Use Chrome DevTools MCP to inspect DOM**: Before writing locators, use `take_snapshot` or `evaluate_script` to understand actual element IDs, classes, and structure. Page controls often use `<span onclick>` not anchor tags.

## Page Object Pattern

Pass `BrowserManager`, not `Page`, into page objects. Do not import or type against `playwright-core` directly — `agent-browser` wraps it.

Resolve `page` once in `BasePage`'s constructor so all subclasses access `this.page` without repeating the call:

```typescript
// tests/e2e/pages/BasePage.ts
import { BrowserManager } from 'agent-browser/dist/browser.js';

export class BasePage {
  protected page: ReturnType<BrowserManager['getPage']>;
  constructor(protected browser: BrowserManager) {
    this.page = browser.getPage();
  }
  protected async navigate(path: string, options?: { waitUntil?: string }) {
    await this.page.goto(`http://localhost:8000${path}`, options as any);
  }
}
```

Define locators as `readonly` class properties, not inline in methods — makes selectors easy to find and update:

```typescript
export class OrganizePage extends BasePage {
  readonly loginRequired = this.page.locator('text=Login required');
  // .first() because navbar and body both contain this text
  readonly loginButton = this.page.locator('text=Login via NetrunnerDB').first();
  readonly logoutButton = this.page.locator('#button-logout');
}
```

## OAuth Login Helper

The `loginUser(browser, 'regular' | 'admin')` helper in `e2e/helpers/auth.ts` performs the real browser OAuth flow:

1. Clears all browser cookies (`page.context().clearCookies()`) — isolates each test
2. Navigates to `/oauth2/redirect` — the app redirects to NetrunnerDB
3. Waits for the NRDB domain, fills `_username` / `_password`, submits
4. Calls `waitForLoadState('domcontentloaded')` after submit to let NRDB process the login
5. If the OAuth "Allow" form appears (first auth or re-auth), clicks it (10 s window)
6. Waits up to 30 s for redirect back to `http://localhost:8000`

Call `clearSession(browser)` at the start of logged-out tests to ensure no leftover cookies.

## Parameterized Tests

Use `it.each` instead of BDD Scenario Outlines:

```typescript
it.each([
  { filter: 'cardpool', value: 'System Gateway', expected: 15 },
  { filter: 'cardpool', value: 'Uprising', expected: 8 },
  { filter: 'type', value: 'GNK', expected: 20 },
])('filters by $filter = $value shows $expected results',
  async ({ filter, value, expected }) => {
    await resultsPage.applyFilter(filter, value);
    expect(await resultsPage.getTournamentCount()).toBe(expected);
  }
);
```

## API Mocking

```typescript
// tests/e2e/helpers/mockApi.ts
export async function mockResultsApi(browser: BrowserManager) {
  await browser.execute('network route "**/api/tournaments/results" --body',
    JSON.stringify(resultsFixture));
}
```

## Visual Regression Testing

```typescript
// tests/e2e/helpers/visualTest.ts
import { compare } from 'resemblejs';
import { BrowserManager } from 'agent-browser/dist/browser.js';

export async function matchScreenshot(
  browser: BrowserManager,
  name: string,
  options: { threshold?: number; fullPage?: boolean } = {}
) {
  const { threshold = 0.03, fullPage = false } = options;
  const page = browser.getPage();
  const actualPath = `tests/e2e/screenshots/actual/${name}.png`;
  const baselinePath = `tests/e2e/screenshots/baseline/${name}.png`;

  await page.screenshot({ path: actualPath, fullPage });
  // Compare with baseline using resemblejs
}
```

Usage:

```typescript
it('displays Google map with markers', async () => {
  await upcomingPage.showMap();
  await matchScreenshot(browser, 'map-worldwide');
});
```

## Cypress → agent-browser Migration

| Cypress | Playwright / agent-browser |
|---------|---------------------------|
| `cy.visit(url)` | `page.goto(url)` |
| `cy.get(sel)` | `page.locator(sel)` |
| `cy.intercept()` | `page.route()` |
| `cy.wait('@alias')` | `page.waitForResponse()` |
| `.should('be.visible')` | `locator.waitFor({ state: 'visible' })` |
| `.should('have.length', n)` | `expect(await locator.count()).toBe(n)` |
| `cy.clock()` | Manual date mocking in test setup |
| `cy.clearCookies()` | `page.context().clearCookies()` |
| `cy.readFile('.env')` | `readFileSync` in helpers |
