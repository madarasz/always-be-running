import { BasePage } from './BasePage';

export class LegalPage extends BasePage {
  // Cookie consent banner (rendered on every page by cookieconsent library)
  readonly cookieBanner = this.page.locator("div[aria-label='cookieconsent']");
  readonly privacyAndCookieLink = this.page.locator("a[aria-label='learn more about cookies']");
  readonly allowCookiesButton = this.page.locator("a[aria-label='dismiss cookie message']");
  // Polite tab shown after consent is given (cc-revoke div at bottom of screen)
  readonly politeCookiePolicyTab = this.page.locator('div.cc-revoke');

  async openUpcoming() {
    await this.navigate('/');
  }

  async openPrivacy() {
    await this.navigate('/privacy');
  }
}
