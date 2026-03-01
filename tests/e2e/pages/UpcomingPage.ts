import { BasePage } from './BasePage';

export class UpcomingPage extends BasePage {
  // Filter elements
  readonly typeFilter = this.page.locator('#tournament_type_id');
  readonly countryFilter = this.page.locator('#location_country');
  readonly stateFilter = this.page.locator('#location_state');
  readonly includeOnlineCheckbox = this.page.locator('#include-online');

  // Upcoming tournaments table
  readonly upcomingTable = this.page.locator('#discover-table');
  readonly upcomingTableBody = this.page.locator('#discover-table tbody');
  readonly upcomingTableRows = this.page.locator('#discover-table tbody tr');
  readonly upcomingTotalCount = this.page.locator('#discover-table-number-total');

  // Paging controls
  readonly pagerForward = this.page.locator('#discover-table-controls-forward');
  readonly pagerBack = this.page.locator('#discover-table-controls-back');
  readonly pagerOptions = this.page.locator('#discover-table-options');
  readonly pagerAll = this.page.locator('#discover-table-options .control-paging').filter({ hasText: 'all' });
  readonly flagButton = this.page.locator('#discover-table-options .control-flag');
  readonly textButton = this.page.locator('#discover-table-options .control-text');
  readonly showingFrom = this.page.locator('#discover-table-number-from');
  readonly showingTo = this.page.locator('#discover-table-number-to');

  // Calendar elements
  readonly calendar = this.page.locator('.fc-calendar');
  readonly calendarDays = this.page.locator('.fc-calendar .fc-content');
  readonly calendarReveal = this.page.locator('#custom-content-reveal');

  // Map elements
  readonly showMapButton = this.page.locator('#button-show-map');
  readonly map = this.page.locator('#map');

  // Recurring events table
  readonly recurringTable = this.page.locator('#recur-table');
  readonly recurringTableRows = this.page.locator('#recur-table tbody tr');

  async open() {
    await this.navigate('/');
  }

  async waitForTableLoaded() {
    // Wait for the table loader to disappear and data to appear
    await this.upcomingTableRows.first().waitFor({ state: 'visible', timeout: 10000 });
  }

  async getUpcomingTournamentCount(): Promise<number> {
    return await this.upcomingTableRows.count();
  }

  async getVisibleUpcomingCount(): Promise<number> {
    return await this.upcomingTableRows.filter({ has: this.page.locator(':visible') }).count();
  }

  async getTotalCount(): Promise<number> {
    const text = await this.upcomingTotalCount.textContent() || '0';
    return parseInt(text);
  }

  async getShowingFrom(): Promise<number> {
    const text = await this.showingFrom.textContent() || '0';
    return parseInt(text);
  }

  async getShowingTo(): Promise<number> {
    const text = await this.showingTo.textContent() || '0';
    return parseInt(text);
  }

  async getVisibleRowCount(): Promise<number> {
    // Count visible rows (hidden-xs-up class hides elements)
    const rows = await this.upcomingTableRows.all();
    let visibleCount = 0;
    for (const row of rows) {
      const className = await row.getAttribute('class') || '';
      if (!className.includes('hidden-xs-up')) {
        visibleCount++;
      }
    }
    return visibleCount;
  }

  async filterByType(type: string) {
    await this.typeFilter.selectOption(type);
  }

  async filterByCountry(country: string) {
    await this.countryFilter.selectOption(country);
  }

  async clearCountryFilter() {
    // Click first to ensure focus, then select the "---" (all countries) option
    await this.countryFilter.click();
    await this.countryFilter.selectOption('---');
    await this.page.waitForTimeout(500);
  }

  async filterByState(state: string) {
    await this.stateFilter.selectOption(state);
  }

  async toggleIncludeOnline() {
    await this.includeOnlineCheckbox.click();
  }

  async clickForwardPager() {
    await this.pagerForward.click();
  }

  async clickAllPager() {
    await this.pagerAll.click();
  }

  async switchToTextMode() {
    await this.textButton.click();
  }

  async switchToFlagMode() {
    await this.flagButton.click();
  }

  async getFirstTournamentRow() {
    return this.upcomingTableRows.first();
  }

  async getTournamentRowData(row: ReturnType<typeof this.upcomingTableRows.first>) {
    const cells = row.locator('td');
    return {
      title: await cells.nth(0).textContent(),
      date: await cells.nth(1).textContent(),
      location: await cells.nth(2).textContent(),
      cardpool: await cells.nth(3).textContent(),
      type: await cells.nth(4).textContent(),
      regs: await cells.nth(5).textContent(),
    };
  }

  async hasCountryFlag(): Promise<boolean> {
    const flags = this.upcomingTableRows.first().locator('img[src*="/flags/"]');
    return (await flags.count()) > 0;
  }

  async hasCountryText(text: string): Promise<boolean> {
    const content = await this.upcomingTableRows.first().textContent();
    return content?.includes(text) || false;
  }

  async getCalendarMarkedDaysCount(): Promise<number> {
    return await this.calendarDays.count();
  }

  async clickCalendarDay(dayNumber: number) {
    await this.page.locator('.fc-calendar .fc-date').filter({ hasText: String(dayNumber) }).click();
  }

  async getCalendarRevealContent(): Promise<string> {
    return await this.calendarReveal.textContent() || '';
  }

  async showMap() {
    await this.showMapButton.click();
  }

  async isMapVisible(): Promise<boolean> {
    // Check if the map div has content (Google Maps loaded)
    const mapContent = await this.map.innerHTML();
    return mapContent.length > 0;
  }

  async waitForMapLoaded(): Promise<void> {
    // Wait for Google Maps to fully load (gm-style class indicates Google Maps content)
    await this.page.waitForFunction(() => {
      const map = document.querySelector('#map');
      return map && map.innerHTML.includes('gm-style');
    }, { timeout: 15000 });
  }

  async getMapMarkerCount(): Promise<number> {
    // Access the global map.markers array directly via JavaScript
    // The markers array is populated by codeAddress() in abr-map.js
    return await this.page.evaluate(() => {
      const mapObj = (window as unknown as { map?: { markers?: unknown[] } }).map;
      return mapObj?.markers?.length ?? 0;
    });
  }

  async getMapMarkerNames(): Promise<string[]> {
    // Get marker titles from the global map.markers array
    // Each marker's title contains HTML with tournament name in <strong> tags
    return await this.page.evaluate(() => {
      const mapObj = (window as unknown as { map?: { markers?: { getTitle?: () => string }[] } }).map;
      const markers = mapObj?.markers ?? [];
      const names: string[] = [];
      for (const marker of markers) {
        const title = marker.getTitle?.() ?? '';
        const match = title.match(/<strong>([^<]+)<\/strong>/);
        if (match) {
          names.push(match[1]);
        }
      }
      return names;
    });
  }

  async getCountryMarkerCount(country: string): Promise<number> {
    // Count markers whose title contains the specified country
    return await this.page.evaluate((countryName) => {
      const mapObj = (window as unknown as { map?: { markers?: { getTitle?: () => string }[] } }).map;
      const markers = mapObj?.markers ?? [];
      let count = 0;
      for (const marker of markers) {
        const title = marker.getTitle?.() ?? '';
        if (title.includes(countryName)) {
          count++;
        }
      }
      return count;
    }, country);
  }

  async getRecurringEventCount(): Promise<number> {
    return await this.recurringTableRows.count();
  }

  async getFirstRecurringRow() {
    return this.recurringTableRows.first();
  }

  async getRecurringRowData(row: ReturnType<typeof this.recurringTableRows.first>) {
    const cells = row.locator('td');
    return {
      title: await cells.nth(0).textContent(),
      location: await cells.nth(1).textContent(),
      day: await cells.nth(2).textContent(),
    };
  }

  /**
   * Check if a tournament with the given title is visible in the upcoming table.
   * @param title Partial or full tournament title to search for
   */
  async hasTournamentInTable(title: string): Promise<boolean> {
    // Look for a row containing the title
    const matchingRow = this.upcomingTableRows.filter({ hasText: title });
    return (await matchingRow.count()) > 0;
  }
}
