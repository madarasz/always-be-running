import { BasePage } from './BasePage';

export class AdminPage extends BasePage {
  readonly accessDenied = this.page.locator('text=Access denied');
  readonly adminNavLink = this.page.locator('nav a[href*="/admin"]');

  async open() {
    // May return 403 — use domcontentloaded to avoid timeout on error responses
    await this.navigate('/admin', { waitUntil: 'domcontentloaded' });
  }

  async waitForAccessDenied() {
    await this.accessDenied.waitFor({ state: 'visible' });
  }

  async waitForAdminContent() {
    await this.page.locator('text=Administration').waitFor({ state: 'visible' });
  }

  async hasAdminNavLink(): Promise<boolean> {
    return await this.adminNavLink.isVisible();
  }
}
