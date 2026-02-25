import { BasePage } from './BasePage';

export class ResultsPage extends BasePage {
  // Results table
  readonly resultsTableRows = this.page.locator('#results tbody tr');
  readonly resultsRowTitleCells = this.page.locator('#results tbody tr td:nth-child(2)');

  // To-be-concluded table
  readonly toConcludedTableRows = this.page.locator('#to-be-concluded tbody tr');
  readonly toConcludedRowTitleCells = this.page.locator('#to-be-concluded tbody tr td:nth-child(2)');

  // Tabs
  readonly tournamentResultsTab = this.page.locator('#t-results a');
  readonly waitingForConclusionTab = this.page.locator('#t-to-be-concluded a');

  // Filters (wait for enabled state before use)
  readonly cardpoolFilter = this.page.locator('#cardpool');
  readonly typeFilter = this.page.locator('#tournament_type_id');
  readonly countryFilter = this.page.locator('#location_country');
  readonly formatFilter = this.page.locator('#format');
  readonly matchdataCheckbox = this.page.locator('#matchdata');
  readonly videosCheckbox = this.page.locator('#videos');

  // Paging controls
  readonly resultsPagingBack = this.page.locator('#results-paging-back');
  readonly resultsPagingForward = this.page.locator('#results-paging-forward');
  readonly resultsPagingSection = this.page.locator('#results-paging');
  readonly resultsOption50 = this.page.locator('#results-option-50');
  readonly resultsOption100 = this.page.locator('#results-option-100');
  readonly resultsOptionFlag = this.page.locator('#results-option-flag');
  readonly resultsOptionText = this.page.locator('#results-option-text');

  // To-be-concluded controls
  readonly concludeLoginWarning = this.page.locator('#warning-conclude');
  readonly concludeButton = this.page.locator('.btn-conclude').first();

  // Featured
  readonly featuredBoxes = this.page.locator('.featured-box');

  // Stats
  readonly statsChartRunner = this.page.locator('#stat-chart-runner');

  async open(query?: string) {
    await this.navigate(query ? `/results?${query}` : '/results');
  }

  async waitForResultsLoaded() {
    // Wait until the cardpool filter becomes enabled (proxy for resultsLoaded = true)
    await this.cardpoolFilter.waitFor({ state: 'visible', timeout: 10000 });
    await this.page.waitForFunction(
      () => {
        const sel = document.querySelector('#cardpool') as HTMLSelectElement | null;
        return sel && !sel.disabled;
      },
      { timeout: 15000 }
    );
    // Also wait for at least one row to appear
    await this.resultsTableRows.first().waitFor({ state: 'visible', timeout: 15000 });
  }

  async waitForToConcludedLoaded() {
    await this.toConcludedTableRows.first().waitFor({ state: 'visible', timeout: 10000 });
  }

  async getResultsCount(): Promise<number> {
    return await this.resultsRowTitleCells.count();
  }

  async getToConcludedCount(): Promise<number> {
    return await this.toConcludedRowTitleCells.count();
  }

  async clickWaitingForConclusionTab() {
    await this.waitingForConclusionTab.click();
    await this.page.waitForTimeout(500);
  }

  async clickTournamentResultsTab() {
    await this.tournamentResultsTab.click();
    await this.page.waitForTimeout(300);
  }

  async filterByCardpool(value: string) {
    await this.cardpoolFilter.selectOption(value);
    await this.page.waitForTimeout(500);
  }

  async filterByCountry(value: string) {
    await this.countryFilter.selectOption(value);
    await this.page.waitForTimeout(500);
  }

  async filterByFormat(value: string) {
    await this.formatFilter.selectOption(value);
    await this.page.waitForTimeout(500);
  }

  async getCurrentUrl(): Promise<string> {
    return this.page.url();
  }

  async getPagingText(): Promise<string> {
    return (await this.resultsPagingSection.textContent() || '').trim();
  }
}
