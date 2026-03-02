import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { LegalPage } from '../pages/LegalPage';
import { clearSession, closeBrowserSafely, CHROME_PATH } from '../helpers/auth';

describe('Legal', () => {
  let browser: BrowserManager;
  let legalPage: LegalPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    legalPage = new LegalPage(browser);
  });

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  describe('Cookie banner', () => {
    it('shows cookie banner on first visit with no consent cookie', async () => {
      // Clear all cookies so no cookieconsent_status exists
      await clearSession(browser);

      await legalPage.openUpcoming();

      await legalPage.cookieBanner.waitFor({ state: 'visible', timeout: 10000 });
      expect(await legalPage.cookieBanner.isVisible()).toBe(true);
    });

    it('Privacy and Cookie Policy link points to /privacy', async () => {
      await clearSession(browser);
      await legalPage.openUpcoming();
      await legalPage.cookieBanner.waitFor({ state: 'visible', timeout: 10000 });

      // The link opens in a new tab (target=_blank), verify its href
      const href = await legalPage.privacyAndCookieLink.getAttribute('href');
      expect(href).toBe('/privacy');
    });

    it('privacy page contains GDPR content', async () => {
      // Navigate directly — the link uses target=_blank which would open a new tab
      await legalPage.openPrivacy();
      await browser.getPage().waitForLoadState('domcontentloaded');

      const gdprText = browser.getPage().locator('text=GDPR').first();
      await gdprText.waitFor({ state: 'visible', timeout: 10000 });
      expect(await gdprText.isVisible()).toBe(true);
    });

    it('clicking Allow cookies dismisses the banner and shows polite policy tab', async () => {
      await clearSession(browser);
      await legalPage.openUpcoming();
      await legalPage.cookieBanner.waitFor({ state: 'visible', timeout: 10000 });

      // Navigate to privacy page via the learn-more link (as in the Cypress scenario)
      await legalPage.privacyAndCookieLink.click();
      await browser.getPage().waitForLoadState('domcontentloaded');

      // Banner is still present on the privacy page (no consent yet)
      await legalPage.cookieBanner.waitFor({ state: 'visible', timeout: 10000 });

      // Accept cookies
      await legalPage.allowCookiesButton.click();

      // Banner fades out (1s CSS transition) — wait for it to be hidden
      await legalPage.cookieBanner.waitFor({ state: 'hidden', timeout: 5000 });
      expect(await legalPage.cookieBanner.isVisible()).toBe(false);

      // Polite "Cookie Policy" revoke tab appears after consent
      await legalPage.politeCookiePolicyTab.waitFor({ state: 'visible', timeout: 5000 });
      expect(await legalPage.politeCookiePolicyTab.count()).toBeGreaterThan(0);
    });

    it('banner does not show on subsequent visits after consent given', async () => {
      // Previous test left the consent cookie set — banner should not appear
      await legalPage.openUpcoming();

      // Wait for page to fully load before checking banner visibility
      await browser.getPage().waitForLoadState('domcontentloaded');
      // The banner should not be visible (consent cookie is set from previous test)
      expect(await legalPage.cookieBanner.isVisible()).toBe(false);
    });
  });
});
