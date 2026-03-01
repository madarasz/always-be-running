import { BrowserManager } from 'agent-browser/dist/browser.js';
import { BasePage } from './BasePage';

export class PrizesPage extends BasePage {
  constructor(browser: BrowserManager) {
    super(browser);
  }

  async open() {
    await this.navigate('/prizes');
  }

  get officialTab() {
    return this.page.locator('#tabf-official a');
  }

  get otherArtTab() {
    return this.page.locator('#tabf-other a');
  }

  get officialTabContent() {
    return this.page.locator('#tab-official');
  }

  get otherArtTabContent() {
    return this.page.locator('#tab-other');
  }

  get filterDropdown() {
    return this.page.locator('#tab-official .custom-select');
  }

  get prizeKitBrackets() {
    return this.page.locator('#tab-official .bracket');
  }

  get artistSections() {
    return this.page.locator('#tab-other .bracket');
  }

  async waitForPrizesLoaded() {
    // Wait for filter dropdown to have more than just the default option
    // This indicates Vue has loaded the prizes data
    await this.page.waitForFunction(
      () => document.querySelectorAll('#tab-official .custom-select option').length > 1,
      { timeout: 10000 }
    );
  }

  async waitForArtistsLoaded() {
    // Wait for at least one artist section to appear
    await this.page.waitForFunction(
      () => document.querySelectorAll('#tab-other .bracket').length > 0,
      { timeout: 10000 }
    );
  }

  async clickOfficialTab() {
    await this.officialTab.click();
    await this.page.waitForTimeout(300);
  }

  async clickOtherArtTab() {
    await this.otherArtTab.click();
    await this.page.waitForTimeout(300);
  }

  async getVisibleKitCount() {
    // Count brackets that don't have hidden-xs-up class
    const brackets = this.page.locator('#tab-official .bracket:not(.hidden-xs-up)');
    return await brackets.count();
  }

  async getKitTitles() {
    const titles = this.page.locator('#tab-official .bracket h5');
    return await titles.allTextContents();
  }

  async selectFilterOption(value: string) {
    await this.filterDropdown.selectOption(value);
    await this.page.waitForTimeout(300);
  }

  async getFilterOptions() {
    const options = this.filterDropdown.locator('option');
    return await options.allTextContents();
  }

  async getArtistNames() {
    const artistLinks = this.page.locator('#tab-other .bracket h5 a');
    return await artistLinks.allTextContents();
  }

  async getArtItemCount() {
    // Count art items in Other art tab (each item has class checkered-md)
    const items = this.page.locator('#tab-other .bracket .checkered-md');
    return await items.count();
  }
}
