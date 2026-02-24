# E2E Test Practices

Patterns and conventions for the `e2e/` Vitest + agent-browser test suite.

## Setup Notes

- **Separate `e2e/package.json`**: Root package.json uses Node 10/npm 6 for Gulp builds. E2E tests live in `e2e/` with their own `package.json` (Node 20 / modern npm). Run tests from inside `e2e/` with `npm test`.
- **BrowserManager import**: `import { BrowserManager } from 'agent-browser/dist/browser.js'` (no exports map in the package, so the direct path import is required)
- **System Chrome**: `executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'` — no need to install Playwright browsers separately
- **Credentials**: Copy `e2e/.env.template` to `e2e/.env` and fill in NetrunnerDB usernames/passwords. The `.env` file is gitignored.

## Locator Rules

- **Use `.first()`** when a text locator matches multiple elements (e.g. navbar and body both contain "Login via NetrunnerDB")
- **Prefer `#id` selectors** over `text=` when the text appears in multiple places on the page (e.g. `#created-title` instead of `text=Tournaments created by me` which also matches a checkbox label)
- Playwright strict mode throws if a locator resolves to more than one element — always confirm uniqueness

## Page Object Pattern

Pass `BrowserManager`, not `Page`, into page objects. Do not import or type against `playwright-core` directly — `agent-browser` wraps it.

Resolve `page` once in `BasePage`'s constructor so all subclasses access `this.page` without repeating the call:

```typescript
// e2e/pages/BasePage.ts
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
// e2e/helpers/mockApi.ts
export async function mockResultsApi(browser: BrowserManager) {
  await browser.execute('network route "**/api/tournaments/results" --body',
    JSON.stringify(resultsFixture));
}
```

## Visual Regression Testing

```typescript
// e2e/helpers/visualTest.ts
import { compare } from 'resemblejs';
import { BrowserManager } from 'agent-browser/dist/browser.js';

export async function matchScreenshot(
  browser: BrowserManager,
  name: string,
  options: { threshold?: number; fullPage?: boolean } = {}
) {
  const { threshold = 0.03, fullPage = false } = options;
  const page = browser.getPage();
  const actualPath = `e2e/screenshots/actual/${name}.png`;
  const baselinePath = `e2e/screenshots/baseline/${name}.png`;

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
