import { readFileSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { BrowserManager } from 'agent-browser/dist/browser.js';

const __dirname = dirname(fileURLToPath(import.meta.url));

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
  const env = parseEnvFile(join(__dirname, '../.env'));
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
