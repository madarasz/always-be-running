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
  readonly resultsOption500 = this.page.locator('#results-option-500');
  readonly resultsOptionFlag = this.page.locator('#results-option-flag');
  readonly resultsOptionText = this.page.locator('#results-option-text');

  // To-be-concluded controls
  readonly concludeLoginWarning = this.page.locator('#warning-conclude');
  readonly concludeButton = this.page.locator('.btn-conclude').first();

  // Featured section
  readonly featuredBracket = this.page.locator('#bracket-featured');
  readonly featuredTournaments = this.page.locator('#bracket-featured a[href*="/tournaments/"]');
  readonly supportMeBox = this.page.locator('.support-bg');

  // Default country label (shown when user has autofilter enabled)
  readonly defaultCountryLabel = this.page.locator('#label-default-country');

  // Stats
  readonly statsChartRunner = this.page.locator('#stat-chart-runner');

  async open(query?: string) {
    await this.navigate(query ? `/results?${query}` : '/results');
  }

  /**
   * Alias for waitForResultsLoaded - waits for the results table to load.
   */
  async waitForTable() {
    await this.waitForResultsLoaded();
  }

  async waitForResultsLoaded() {
    // Wait for results table to have data
    await this.resultsTableRows.first().waitFor({ state: 'visible', timeout: 15000 });
  }

  async waitForToConcludedLoaded() {
    await this.toConcludedTableRows.first().waitFor({ state: 'visible', timeout: 10000 });
  }

  /**
   * Wait for more tournaments to appear in the results table.
   * @param previousCount The count before the action that should trigger more rows
   */
  async waitForTournamentNumberToChange(previousCount: number) {
    // Wait for the next row to appear (indicating count increased)
    await this.resultsTableRows.nth(previousCount).waitFor({ state: 'visible', timeout: 5000 }).catch(() => {});
  }

  async getResultsCount(): Promise<number> {
    return await this.resultsRowTitleCells.count();
  }

  async getToConcludedCount(): Promise<number> {
    return await this.toConcludedRowTitleCells.count();
  }

  async clickWaitingForConclusionTab() {
    await this.waitingForConclusionTab.click();
    // Wait for tab content to be visible
    await this.page.locator('#tab-to-be-concluded').waitFor({ state: 'visible' });
  }

  async clickTournamentResultsTab() {
    await this.tournamentResultsTab.click();
    // Wait for tab content to be visible
    await this.page.locator('#tab-results').waitFor({ state: 'visible' });
  }

  async filterByCardpool(value: string) {
    await this.cardpoolFilter.selectOption(value);
    // Wait for URL to update with cardpool parameter
    await this.page.waitForURL(/cardpool=/, { timeout: 5000 });
  }

  async filterByCountry(value: string) {
    await this.countryFilter.selectOption(value);
    // Wait for URL to update with country parameter
    await this.page.waitForURL(/country=/, { timeout: 5000 });
  }

  async filterByFormat(value: string) {
    await this.formatFilter.selectOption(value);
    // Wait for URL to update with format parameter
    await this.page.waitForURL(/format=/, { timeout: 5000 });
  }

  async clearCountryFilter() {
    await this.countryFilter.selectOption('---');
    // Wait for URL to no longer contain a country filter (or show country=---)
    await this.page.waitForURL((url) => !url.searchParams.has('country') || url.searchParams.get('country') === '---', { timeout: 5000 }).catch(() => {});
  }

  async getCurrentUrl(): Promise<string> {
    return this.page.url();
  }

  async getPagingText(): Promise<string> {
    return (await this.resultsPagingSection.textContent() || '').trim();
  }

  async getFeaturedTournamentCount(): Promise<number> {
    return await this.featuredTournaments.count();
  }

  async getFeaturedTournamentNames(): Promise<string[]> {
    const names: string[] = [];
    const count = await this.featuredTournaments.count();
    for (let i = 0; i < count; i++) {
      const text = await this.featuredTournaments.nth(i).textContent();
      if (text) names.push(text.trim());
    }
    return names;
  }

  async isDefaultCountryLabelVisible(): Promise<boolean> {
    // The label exists but may be hidden via hidden-xs-up class
    const label = this.defaultCountryLabel;
    const exists = await label.count() > 0;
    if (!exists) return false;
    const className = await label.getAttribute('class') || '';
    return !className.includes('hidden-xs-up');
  }

  /**
   * Select 500 results per page option.
   */
  async select500PerPage() {
    const currentCount = await this.resultsTableRows.count();
    await this.resultsOption500.click();
    await this.waitForTournamentNumberToChange(currentCount);
  }

  /**
   * Check if a tournament with the given title is visible in the results table.
   * @param title Partial or full tournament title to search for
   */
  async hasTournamentInTable(title: string): Promise<boolean> {
    // Look for a row containing the title in the results table
    const matchingRow = this.resultsTableRows.filter({ hasText: title });
    return (await matchingRow.count()) > 0;
  }
}
