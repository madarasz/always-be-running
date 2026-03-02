import { BrowserManager } from 'agent-browser/dist/browser.js';
import { describe, beforeAll, afterAll, beforeEach, afterEach } from 'vitest';
import {
  createAuthenticatedBrowser,
  closeBrowserSafely,
  startTracing,
  stopTracing,
  takeScreenshot,
} from './auth';

// Page objects
import { TournamentPage } from '../pages/TournamentPage';
import { UpcomingPage } from '../pages/UpcomingPage';
import { ResultsPage } from '../pages/ResultsPage';
import { OrganizePage } from '../pages/OrganizePage';
import { ProfilePage } from '../pages/ProfilePage';
import { PersonalPage } from '../pages/PersonalPage';
import { VideosPage } from '../pages/VideosPage';
import { PrizesPage } from '../pages/PrizesPage';
import { LegalPage } from '../pages/LegalPage';
import { AdminPage } from '../pages/AdminPage';
import { TournamentDetailsPage } from '../pages/TournamentDetailsPage';

export { describe, it, expect, beforeAll, afterAll, beforeEach, afterEach } from 'vitest';

/**
 * Test context with automatic tracing and failure handling.
 *
 * Usage:
 * ```typescript
 * import { describe, it, expect, beforeAll, afterAll, beforeEach, afterEach, useTracing } from '../helpers/test-fixture';
 *
 * describe('My Tests', () => {
 *   let browser: BrowserManager;
 *   const trace = useTracing(() => browser);
 *
 *   beforeAll(async () => { browser = await createAuthenticatedBrowser('regular'); });
 *   afterAll(async () => { await closeBrowserSafely(browser); });
 *   beforeEach(trace.before);
 *   afterEach(trace.after);
 *
 *   it('test', async () => { ... });
 * });
 * ```
 */
export function useTracing(getBrowser: () => BrowserManager | undefined) {
  let currentTestName = '';

  return {
    before: async (ctx: { task: { name: string } }) => {
      currentTestName = ctx.task.name;
      const browser = getBrowser();
      if (browser) {
        await startTracing(browser, currentTestName);
      }
    },

    after: async (ctx: { task: { result?: { state?: string } } }) => {
      const browser = getBrowser();
      if (!browser) return;

      const failed = ctx.task.result?.state === 'fail';

      if (failed) {
        try {
          await takeScreenshot(browser, `${currentTestName}-failure`);
        } catch (e) {
          console.warn('Failed to take screenshot:', e);
        }
      }

      try {
        await stopTracing(browser, currentTestName, failed);
      } catch (e) {
        console.warn('Failed to stop tracing:', e);
      }
    },
  };
}

/**
 * Browser suite options
 */
export interface BrowserSuiteOptions {
  userType: 'regular' | 'admin' | 'none';
  tracing?: boolean; // default: true
  /** Callback to run after browser launch but before loading auth state/navigation.
   *  Use this for setup that must happen before the first page load (e.g., date mocking). */
  beforeInit?: (browser: BrowserManager) => Promise<void>;
}

/**
 * All available page objects
 */
export interface Pages {
  tournamentPage: TournamentPage;
  upcomingPage: UpcomingPage;
  resultsPage: ResultsPage;
  organizePage: OrganizePage;
  profilePage: ProfilePage;
  personalPage: PersonalPage;
  videosPage: VideosPage;
  prizesPage: PrizesPage;
  legalPage: LegalPage;
  adminPage: AdminPage;
  tournamentDetailsPage: TournamentDetailsPage;
}

/**
 * Context passed to test suites
 */
export interface BrowserContext {
  browser: BrowserManager;
  pages: Pages;
}

/**
 * Create a browser test suite with automatic setup/teardown and tracing.
 *
 * Usage:
 * ```typescript
 * import { createBrowserSuite, it, expect } from '../helpers/test-fixture';
 *
 * createBrowserSuite('My Tests', { userType: 'regular' }, (ctx) => {
 *   it('does something', async () => {
 *     const { tournamentPage } = ctx.pages;
 *     await tournamentPage.openCreateForm();
 *   });
 * });
 * ```
 */
export function createBrowserSuite(
  name: string,
  options: BrowserSuiteOptions,
  fn: (ctx: BrowserContext) => void
): void {
  describe(name, () => {
    let _browser: BrowserManager;
    let _pages: Pages;

    // Context with getters for lazy access (browser/pages aren't available until beforeAll)
    const ctx: BrowserContext = {
      get browser() {
        return _browser;
      },
      get pages() {
        return _pages;
      },
    };

    beforeAll(async () => {
      _browser = await createAuthenticatedBrowser(options.userType, {
        beforeInit: options.beforeInit,
      });
      _pages = {
        tournamentPage: new TournamentPage(_browser),
        upcomingPage: new UpcomingPage(_browser),
        resultsPage: new ResultsPage(_browser),
        organizePage: new OrganizePage(_browser),
        profilePage: new ProfilePage(_browser),
        personalPage: new PersonalPage(_browser),
        videosPage: new VideosPage(_browser),
        prizesPage: new PrizesPage(_browser),
        legalPage: new LegalPage(_browser),
        adminPage: new AdminPage(_browser),
        tournamentDetailsPage: new TournamentDetailsPage(_browser),
      };
    });

    afterAll(async () => {
      await closeBrowserSafely(_browser);
    });

    // Enable tracing by default
    if (options.tracing !== false) {
      const trace = useTracing(() => _browser);
      beforeEach(trace.before);
      afterEach(trace.after);
    }

    fn(ctx);
  });
}
