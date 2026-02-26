import { readFileSync, mkdirSync, statSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { BrowserManager } from 'agent-browser/dist/browser.js';

const __filename = fileURLToPath(import.meta.url);
const __dirnameResolved = dirname(__filename);

// Storage state configuration
export const AUTH_STATE_DIR = join(__dirnameResolved, '../.auth');
export const REGULAR_USER_STATE = join(AUTH_STATE_DIR, 'regular.json');
export const ADMIN_USER_STATE = join(AUTH_STATE_DIR, 'admin.json');
export const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

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
 * Called by global setup.
 */
export async function loginAndSaveSession(userType: 'regular' | 'admin'): Promise<void> {
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
  } finally {
    await browser.close();
  }
}

/**
 * Creates a BrowserManager with pre-loaded authentication state.
 * Use this instead of manually calling loginUser() in tests.
 */
export async function createAuthenticatedBrowser(
  userType: 'regular' | 'admin' | 'none' = 'none'
): Promise<BrowserManager> {
  const browser = new BrowserManager();

  await browser.launch({
    id: 'launch',
    action: 'launch',
    headless: true,
    executablePath: CHROME_PATH,
  });
  await browser.ensurePage();

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
    await page.goto('http://localhost:8000');
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
      `Missing ${userType} credentials. Copy e2e/.env.template to e2e/.env and fill in the values.`
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
 *   → redirect back to http://localhost:8000
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
  await page.goto('http://localhost:8000/oauth2/redirect');

  // Wait until we land on netrunnerdb.com
  await page.waitForURL(/netrunnerdb\.com/, { timeout: 30000 });

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
  await page.waitForURL('http://localhost:8000/**', { timeout: 60000 });
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
