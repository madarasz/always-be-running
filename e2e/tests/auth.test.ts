import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { OrganizePage } from '../pages/OrganizePage';
import { AdminPage } from '../pages/AdminPage';
import { loginUser, clearSession } from '../helpers/auth';

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

describe('Authentication', () => {
  let browser: BrowserManager;
  let organizePage: OrganizePage;
  let adminPage: AdminPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    organizePage = new OrganizePage(browser);
    adminPage = new AdminPage(browser);
  });

  afterAll(async () => {
    await browser.close();
  });

  it('Logged out: shows login prompt and blocks access', async () => {
    await clearSession(browser);

    await organizePage.open();
    await organizePage.waitForLoginRequired();
    await organizePage.waitForLoginButton();
    expect(await organizePage.hasLogoutButton()).toBe(false);

    await adminPage.open();
    await adminPage.waitForAccessDenied();
  });

  it('Logging in with regular user: can access organize, cannot access admin', async () => {
    await loginUser(browser, 'regular');

    await organizePage.open();
    await organizePage.waitForMyTournaments();

    await adminPage.open();
    await adminPage.waitForAccessDenied();
  });

  it('Logging in with admin user: can access organize and admin', async () => {
    await loginUser(browser, 'admin');

    await organizePage.open();
    await organizePage.waitForMyTournaments();

    await adminPage.open();
    await adminPage.waitForAdminContent();
  });
});
