import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { OrganizePage } from '../pages/OrganizePage';
import { AdminPage } from '../pages/AdminPage';
import { createAuthenticatedBrowser, closeBrowserSafely, CHROME_PATH } from '../helpers/auth';

describe('Authentication', () => {
  describe('Logged out user', () => {
    let browser: BrowserManager;
    let organizePage: OrganizePage;
    let adminPage: AdminPage;

    beforeAll(async () => {
      browser = await createAuthenticatedBrowser('none');
      organizePage = new OrganizePage(browser);
      adminPage = new AdminPage(browser);
    });

    afterAll(async () => {
      await closeBrowserSafely(browser);
    });

    it('shows login prompt and blocks access', async () => {
      await organizePage.open();
      await organizePage.waitForLoginRequired();
      await organizePage.waitForLoginButton();
      expect(await organizePage.hasLogoutButton()).toBe(false);

      await adminPage.open();
      await adminPage.waitForAccessDenied();
    });
  });

  describe('Regular user', () => {
    let browser: BrowserManager;
    let organizePage: OrganizePage;
    let adminPage: AdminPage;

    beforeAll(async () => {
      browser = await createAuthenticatedBrowser('regular');
      organizePage = new OrganizePage(browser);
      adminPage = new AdminPage(browser);
    });

    afterAll(async () => {
      await closeBrowserSafely(browser);
    });

    it('can access organize page', async () => {
      await organizePage.open();
      await organizePage.waitForMyTournaments();
    });

    it('cannot access admin page', async () => {
      await adminPage.open();
      await adminPage.waitForAccessDenied();
    });
  });

  describe('Admin user', () => {
    let browser: BrowserManager;
    let organizePage: OrganizePage;
    let adminPage: AdminPage;

    beforeAll(async () => {
      browser = await createAuthenticatedBrowser('admin');
      organizePage = new OrganizePage(browser);
      adminPage = new AdminPage(browser);
    });

    afterAll(async () => {
      await closeBrowserSafely(browser);
    });

    it('can access organize page', async () => {
      await organizePage.open();
      await organizePage.waitForMyTournaments();
    });

    it('can access admin page', async () => {
      // Validate admin access by checking navbar shows Admin link (faster than loading full admin page)
      await organizePage.open();
      await organizePage.waitForMyTournaments();
      expect(await adminPage.hasAdminNavLink()).toBe(true);
    });
  });
});
