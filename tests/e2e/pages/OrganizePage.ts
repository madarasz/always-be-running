import { BasePage } from './BasePage';

export class OrganizePage extends BasePage {
  readonly loginRequired = this.page.locator('text=Login required');
  // Use .first() — navbar and body both contain this text
  readonly loginButton = this.page.locator('text=Login via NetrunnerDB').first();
  readonly logoutButton = this.page.locator('#button-logout');

  async open() {
    await this.navigate('/organize');
  }

  async waitForLoginRequired() {
    await this.loginRequired.waitFor({ state: 'visible' });
  }

  async waitForLoginButton() {
    await this.loginButton.waitFor({ state: 'visible' });
  }

  async hasLogoutButton(): Promise<boolean> {
    return (await this.logoutButton.count()) > 0;
  }

  async waitForMyTournaments() {
    await this.page.locator('#created-title').waitFor({ state: 'visible' });
  }
}
