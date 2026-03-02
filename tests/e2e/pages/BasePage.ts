import { BrowserManager } from 'agent-browser/dist/browser.js';

const BASE_URL = 'http://localhost:8000';

export class BasePage {
  protected page: ReturnType<BrowserManager['getPage']>;

  constructor(protected browser: BrowserManager) {
    this.page = browser.getPage();
  }

  protected async navigate(path: string, options?: { waitUntil?: string }) {
    await this.page.goto(`${BASE_URL}${path}`, options as any);
  }

  /**
   * Get the current page URL.
   */
  getUrl(): string {
    return this.page.url();
  }

  /**
   * Dismiss the cookie consent banner if visible.
   * The banner can interfere with dropdowns and autocomplete.
   */
  async dismissCookieBanner(): Promise<void> {
    try {
      const allowButton = this.page.locator('button:has-text("Allow cookies")');
      // Give banner time to render, but don't fail if it doesn't appear
      await allowButton.waitFor({ state: 'visible', timeout: 2000 });
      await allowButton.click();
      await allowButton.waitFor({ state: 'hidden', timeout: 3000 });
    } catch {
      // Banner not present or already dismissed - continue
    }
  }
}
