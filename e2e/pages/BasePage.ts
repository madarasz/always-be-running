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
}
