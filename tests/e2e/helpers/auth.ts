import { readFileSync, mkdirSync, statSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { BASE_URL, appUrl } from '../config';

const __filename = fileURLToPath(import.meta.url);
const __dirnameResolved = dirname(__filename);

// Storage state configuration
export const AUTH_STATE_DIR = join(__dirnameResolved, '../.auth');
export const TEST_RESULTS_DIR = join(__dirnameResolved, '../test-results');
export const REGULAR_USER_STATE = join(AUTH_STATE_DIR, 'regular.json');
export const ADMIN_USER_STATE = join(AUTH_STATE_DIR, 'admin.json');
// Set CHROME_PATH env var to use a custom browser, otherwise bundled Chromium is used
export const CHROME_PATH = process.env.CHROME_PATH || undefined;

export function getStorageStatePath(userType: 'regular' | 'admin'): string {
  return userType === 'admin' ? ADMIN_USER_STATE : REGULAR_USER_STATE;
}

/**
 * Checks if a valid storage state exists for the given user type.
 * Returns false if file doesn't exist or is older than maxAge.
 */
export function hasValidStorageState(
  userType: 'regular' | 'admin',
  maxAgeMs: number = 24 * 60 * 60 * 1000 // 24 hours default
): boolean {
  const statePath = getStorageStatePath(userType);
  try {
    const stats = statSync(statePath);
    const age = Date.now() - stats.mtimeMs;
    return age < maxAgeMs;
  } catch {
    return false;
  }
}

/**
 * Verifies that stored auth state still represents a logged-in session.
 * This catches stale cookie files that are recent but no longer authenticated.
 */
export async function hasUsableStorageState(
  userType: 'regular' | 'admin',
  maxAgeMs: number = 24 * 60 * 60 * 1000
): Promise<boolean> {
  if (!hasValidStorageState(userType, maxAgeMs)) {
    return false;
  }

  const statePath = getStorageStatePath(userType);
  const browser = new BrowserManager();

  try {
    await browser.launch({
      id: `auth-check-${userType}`,
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();

    const page = browser.getPage();
    const stateData = JSON.parse(readFileSync(statePath, 'utf-8'));

    if (Array.isArray(stateData.cookies) && stateData.cookies.length > 0) {
      await page.context().addCookies(stateData.cookies);
    }

    await page.goto(appUrl('/organize'), { waitUntil: 'domcontentloaded' });

    const createdTitle = page.locator('#created-title');
    const loginRequired = page.locator('text=Login required');
    const loginButton = page.locator('text=Login via NetrunnerDB').first();

    const loginRequiredVisible = await loginRequired.isVisible().catch(() => false);
    if (loginRequiredVisible) {
      return false;
    }

    const loginButtonVisible = await loginButton.isVisible().catch(() => false);
    if (loginButtonVisible) {
      return false;
    }

    await createdTitle.waitFor({ state: 'visible', timeout: 8000 });
    return true;
  } catch {
    return false;
  } finally {
    await closeBrowserSafely(browser);
  }
}

/**
 * Saves the current browser session to a storage state file.
 */
export async function saveSession(
  browser: BrowserManager,
  userType: 'regular' | 'admin'
): Promise<void> {
  const page = browser.getPage();
  const statePath = getStorageStatePath(userType);
  mkdirSync(AUTH_STATE_DIR, { recursive: true });
  await page.context().storageState({ path: statePath });
}

/**
 * Performs login and saves the session state.
 * Called by global setup. Includes retry logic for flaky OAuth flows.
 */
export async function loginAndSaveSession(userType: 'regular' | 'admin', maxRetries = 2): Promise<void> {
  let lastError: Error | null = null;
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    const browser = new BrowserManager();
    try {
      await browser.launch({
        id: 'auth-setup',
        action: 'launch',
        headless: true,
        executablePath: CHROME_PATH,
      });
      await browser.ensurePage();
      await loginUser(browser, userType);
      await saveSession(browser, userType);
      return; // Success
    } catch (e) {
      lastError = e as Error;
      console.error(`Login attempt ${attempt}/${maxRetries} failed for ${userType}: ${lastError.message}`);
    } finally {
      await closeBrowserSafely(browser);
    }
  }
  throw lastError!;
}

/**
 * Options for createAuthenticatedBrowser
 */
export interface AuthenticatedBrowserOptions {
  /** Callback to run after browser launch but before loading auth state/navigation */
  beforeInit?: (browser: BrowserManager) => Promise<void>;
}

/**
 * Creates a BrowserManager with pre-loaded authentication state.
 * Use this instead of manually calling loginUser() in tests.
 */
export async function createAuthenticatedBrowser(
  userType: 'regular' | 'admin' | 'none' = 'none',
  options?: AuthenticatedBrowserOptions
): Promise<BrowserManager> {
  const browser = new BrowserManager();

  await browser.launch({
    id: 'launch',
    action: 'launch',
    headless: true,
    executablePath: CHROME_PATH,
  });
  await browser.ensurePage();

  // Call beforeInit callback (e.g., for date mocking that must happen before navigation)
  if (options?.beforeInit) {
    await options.beforeInit(browser);
  }

  // Load storage state if authenticated session is requested
  if (userType !== 'none') {
    const statePath = getStorageStatePath(userType);
    if (!existsSync(statePath)) {
      throw new Error(
        `Storage state not found for ${userType} user. ` +
        `Run tests with global setup or call loginAndSaveSession('${userType}') first.`
      );
    }
    // Load cookies from storage state
    const page = browser.getPage();
    const stateData = JSON.parse(readFileSync(statePath, 'utf-8'));
    if (stateData.cookies) {
      await page.context().addCookies(stateData.cookies);
    }
    // Navigate to home page so the authenticated state is visible
    await page.goto(BASE_URL);
    await page.waitForLoadState('domcontentloaded');
  }

  return browser;
}

function parseEnvFile(path: string): Record<string, string> {
  const result: Record<string, string> = {};
  try {
    const content = readFileSync(path, 'utf-8');
    for (const line of content.split('\n')) {
      const trimmed = line.trim();
      if (!trimmed || trimmed.startsWith('#')) continue;
      const idx = trimmed.indexOf('=');
      if (idx === -1) continue;
      result[trimmed.slice(0, idx).trim()] = trimmed.slice(idx + 1).trim();
    }
  } catch {
    // file not found — credentials() will throw a descriptive error
  }
  return result;
}

function credentials(userType: 'regular' | 'admin') {
  const env = parseEnvFile(join(__dirnameResolved, '../.env'));
  const username = env[`${userType.toUpperCase()}_USERNAME`];
  const password = env[`${userType.toUpperCase()}_PASSWORD`];
  if (!username || !password) {
    throw new Error(
      `Missing ${userType} credentials. Copy tests/e2e/.env.template to tests/e2e/.env and fill in the values.`
    );
  }
  return { username, password };
}

/**
 * Logs a user in via the real NetrunnerDB OAuth browser flow.
 *
 * Flow:
 *   GET /oauth2/redirect → NRDB login page → fill credentials → submit
 *   → optional: NRDB authorization form → Allow
 *   → redirect back to the ABR app
 */
export async function loginUser(
  browser: BrowserManager,
  userType: 'regular' | 'admin'
): Promise<void> {
  const { username, password } = credentials(userType);
  const page = browser.getPage();

  // Clear any existing session so each test starts clean
  await page.context().clearCookies();

  // Trigger the OAuth redirect — the app sends us to NRDB
  await page.goto(appUrl('/oauth2/redirect'));

  // Wait until we land on netrunnerdb.com
  try {
    await page.waitForURL(/netrunnerdb\.com/, { timeout: 30000 });
  } catch (e) {
    // Capture debug info on failure
    const currentUrl = page.url();
    const pageContent = await page.content().catch(() => 'Failed to get content');
    console.error(`OAuth redirect failed. Current URL: ${currentUrl}`);
    console.error(`Page content preview: ${pageContent.substring(0, 1000)}`);
    throw e;
  }

  // Fill login form (fields: _username, _password)
  await page.fill('[name="_username"]', username);
  await page.fill('[name="_password"]', password);
  await page.click('[name="_submit"]');

  // Wait for the next page to settle after login form submit
  await page.waitForLoadState('domcontentloaded');

  // After login NRDB may show the authorization grant form (first time or re-auth).
  // If it appears, click Allow; otherwise we're redirected straight to our app.
  const allowButton = page.locator('[name="accepted"][value="Allow"]');
  const allowAppeared = await allowButton
    .waitFor({ state: 'visible', timeout: 10000 })
    .then(() => true)
    .catch(() => false);

  if (allowAppeared) {
    await allowButton.click();
  }

  // Wait until we're back on our app
  try {
    await page.waitForURL(appUrl('/**'), { timeout: 60000 });
  } catch (e) {
    const currentUrl = page.url();
    console.error(`Final redirect failed. Current URL: ${currentUrl}`);
    throw e;
  }
}

export async function clearSession(browser: BrowserManager): Promise<void> {
  const page = browser.getPage();
  await page.context().clearCookies();
}

/**
 * Safely closes a browser instance, handling cases where browser is undefined
 * or already closed. Use this in afterAll hooks to prevent timeout issues.
 */
export async function closeBrowserSafely(browser: BrowserManager | undefined): Promise<void> {
  if (!browser) return;
  try {
    await browser.close();
  } catch (e) {
    console.warn('Browser close error (ignored):', e);
  }
}

/**
 * Mocks the browser's Date to a fixed value.
 * Must be called BEFORE navigating to any page where you want the mock to apply.
 * The mock affects Date.now() and new Date() calls in the browser context.
 *
 * @param browser - The BrowserManager instance
 * @param dateString - ISO date string (e.g., '2026-02-25') or full ISO datetime
 */
export async function mockBrowserDate(browser: BrowserManager, dateString: string): Promise<void> {
  const page = browser.getPage();
  const timestamp = new Date(dateString).getTime();

  await page.addInitScript(`{
    const OriginalDate = Date;
    const mockTimestamp = ${timestamp};

    class MockDate extends OriginalDate {
      constructor(...args) {
        if (args.length === 0) {
          super(mockTimestamp);
        } else {
          super(...args);
        }
      }

      static now() {
        return mockTimestamp;
      }
    }

    // Copy static methods
    MockDate.parse = OriginalDate.parse;
    MockDate.UTC = OriginalDate.UTC;

    window.Date = MockDate;
  }`);
}

/**
 * Start Playwright tracing for a browser instance.
 * Call this at the start of a test to capture traces on failure.
 */
export async function startTracing(browser: BrowserManager, testName: string): Promise<void> {
  const page = browser.getPage();
  const context = page.context();
  mkdirSync(TEST_RESULTS_DIR, { recursive: true });
  await context.tracing.start({
    screenshots: true,
    snapshots: true,
    sources: true,
  });
}

/**
 * Stop tracing and save to file.
 * @param browser - The browser instance
 * @param testName - Name of the test (used for filename)
 * @param failed - If true, saves the trace; if false, discards it
 */
export async function stopTracing(
  browser: BrowserManager,
  testName: string,
  failed: boolean
): Promise<string | null> {
  const page = browser.getPage();
  const context = page.context();

  if (failed) {
    const safeName = testName.replace(/[^a-zA-Z0-9-_]/g, '_').substring(0, 100);
    const tracePath = join(TEST_RESULTS_DIR, `${safeName}-${Date.now()}.zip`);
    await context.tracing.stop({ path: tracePath });
    console.log(`Trace saved: ${tracePath}`);
    return tracePath;
  } else {
    // Discard trace if test passed
    await context.tracing.stop();
    return null;
  }
}

/**
 * Take a screenshot and save to test-results directory.
 */
export async function takeScreenshot(browser: BrowserManager, name: string): Promise<string> {
  const page = browser.getPage();
  mkdirSync(TEST_RESULTS_DIR, { recursive: true });
  const safeName = name.replace(/[^a-zA-Z0-9-_]/g, '_').substring(0, 100);
  const screenshotPath = join(TEST_RESULTS_DIR, `${safeName}-${Date.now()}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`Screenshot saved: ${screenshotPath}`);
  return screenshotPath;
}
