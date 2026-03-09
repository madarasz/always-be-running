import { BasePage } from './BasePage';

export class OrganizePage extends BasePage {
  readonly loginRequired = this.page.locator('text=Login required');
  // Use .first() — navbar and body both contain this text
  readonly loginButton = this.page.locator('text=Login via NetrunnerDB').first();
  readonly logoutButton = this.page.locator('#button-logout');
  readonly myTournamentsTitle = this.page.locator('#created-title');
  readonly createTournamentLink = this.page.locator('a[href="/tournaments/create"]').first();

  async open() {
    await this.navigate('/organize');
  }

  async waitForLoginRequired() {
    await this.loginRequired.waitFor({ state: 'visible', timeout: 10000 });
  }

  async waitForLoginButton() {
    await this.loginButton.waitFor({ state: 'visible', timeout: 10000 });
  }

  async hasLogoutButton(): Promise<boolean> {
    return (await this.logoutButton.count()) > 0;
  }

  async waitForMyTournaments(timeoutMs: number = 15000) {
    await this.page.waitForLoadState('domcontentloaded', { timeout: timeoutMs });

    const loginRequiredVisible = await this.loginRequired.isVisible().catch(() => false);
    if (loginRequiredVisible) {
      throw new Error(`Expected authenticated organize page, but login is required at ${this.getUrl()}`);
    }

    const loginButtonVisible = await this.loginButton.isVisible().catch(() => false);
    if (loginButtonVisible) {
      throw new Error(`Expected authenticated organize page, but login button is visible at ${this.getUrl()}`);
    }

    await this.myTournamentsTitle.waitFor({ state: 'visible', timeout: timeoutMs });
  }

  /**
   * Wait for the tournaments table to finish loading (loader disappears).
   */
  async waitForTournamentsLoaded() {
    const loader = this.page.locator('#created-loader');
    await loader.waitFor({ state: 'hidden', timeout: 10000 });
  }

  /**
   * Check if a tournament with given title exists in the organize page list.
   * Call waitForTournamentsLoaded() before this method.
   */
  async hasTournamentInList(title: string): Promise<boolean> {
    const cell = this.page.locator(`td:has-text("${title}")`);
    return (await cell.count()) > 0;
  }
}
