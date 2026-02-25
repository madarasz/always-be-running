import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { UpcomingPage } from '../pages/UpcomingPage';

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

describe('Upcoming page', () => {
  let browser: BrowserManager;
  let upcomingPage: UpcomingPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    upcomingPage = new UpcomingPage(browser);
  });

  afterAll(async () => {
    await browser.close();
  });

  describe('Loading upcoming tournaments table', () => {
    it('displays tournaments with all required fields populated', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const row = await upcomingPage.getFirstTournamentRow();
      const data = await upcomingPage.getTournamentRowData(row);

      // Verify each field has content (not empty)
      expect(data.title?.trim().length).toBeGreaterThan(0);
      expect(data.date?.trim().length).toBeGreaterThan(0);
      expect(data.location?.trim().length).toBeGreaterThan(0);
      expect(data.cardpool?.trim().length).toBeGreaterThan(0);
      expect(data.type?.trim().length).toBeGreaterThan(0);
      expect(data.regs?.trim()).toMatch(/^\d+$/); // regs should be a number
    });

    it('shows total tournament count', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const total = await upcomingPage.getTotalCount();
      expect(total).toBeGreaterThan(0);
    });
  });

  describe('Table controls', () => {
    it('initially shows a subset of tournaments with paging', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const total = await upcomingPage.getTotalCount();
      const showingTo = await upcomingPage.getShowingTo();

      // Should show a subset, not all tournaments (when there are more than the page size)
      if (total > showingTo) {
        expect(showingTo).toBeLessThan(total);
      }
      expect(showingTo).toBeGreaterThan(0);
    });

    it('shows country flags by default', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();
      expect(await upcomingPage.hasCountryFlag()).toBe(true);
    });

    it('switches between flag and text mode', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Should have flag mode active by default
      const flagButton = upcomingPage.flagButton;
      expect(await flagButton.getAttribute('class')).toContain('label-active');

      // Switch to text mode
      await upcomingPage.switchToTextMode();
      await browser.getPage().waitForTimeout(300);
      expect(await upcomingPage.textButton.getAttribute('class')).toContain('label-active');

      // Switch back to flag mode
      await upcomingPage.switchToFlagMode();
      await browser.getPage().waitForTimeout(300);
      expect(await flagButton.getAttribute('class')).toContain('label-active');
    });

    it('forward pager advances the view', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialFrom = await upcomingPage.getShowingFrom();
      const total = await upcomingPage.getTotalCount();
      const showingTo = await upcomingPage.getShowingTo();

      // Only test paging if there are more items than currently shown
      if (total > showingTo) {
        await upcomingPage.clickForwardPager();
        await browser.getPage().waitForTimeout(300);
        const newFrom = await upcomingPage.getShowingFrom();
        expect(newFrom).toBeGreaterThan(initialFrom);
      }
    });

    it('all pager shows all tournaments', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      await upcomingPage.clickAllPager();
      await browser.getPage().waitForTimeout(300);

      const total = await upcomingPage.getTotalCount();
      const showingTo = await upcomingPage.getShowingTo();
      expect(showingTo).toBe(total);
    });
  });

  describe('Filtering', () => {
    it.each([
      { filterType: 'type', filterMethod: 'filterByType', value: 'GNK / seasonal' },
      { filterType: 'type', filterMethod: 'filterByType', value: 'players circuit' },
      { filterType: 'country', filterMethod: 'filterByCountry', value: 'Germany' },
      { filterType: 'country', filterMethod: 'filterByCountry', value: 'United Kingdom' },
    ])('filters by $filterType = $value reduces or maintains count', async ({ filterMethod, value }) => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[filterMethod](value);
      await browser.getPage().waitForTimeout(500);
      const filteredCount = await upcomingPage.getTotalCount();
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
    });

    it.each([
      { filter1: 'type', method1: 'filterByType', value1: 'GNK / seasonal', filter2: 'country', method2: 'filterByCountry', value2: 'Germany' },
      { filter1: 'country', method1: 'filterByCountry', value1: 'United States', filter2: 'type', method2: 'filterByType', value2: 'players circuit' },
    ])('combines $filter1 + $filter2 filters', async ({ method1, value1, method2, value2 }) => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[method1](value1);
      await browser.getPage().waitForTimeout(300);
      const afterFirstFilter = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[method2](value2);
      await browser.getPage().waitForTimeout(300);
      const afterBothFilters = await upcomingPage.getTotalCount();

      expect(afterFirstFilter).toBeLessThanOrEqual(initialCount);
      expect(afterBothFilters).toBeLessThanOrEqual(afterFirstFilter);
    });

    it('shows US state filter when US is selected', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      await upcomingPage.filterByCountry('United States');
      await browser.getPage().waitForTimeout(500);
      // State filter should become visible
      const stateFilter = upcomingPage.stateFilter;
      await stateFilter.waitFor({ state: 'visible', timeout: 5000 });
      expect(await stateFilter.isVisible()).toBe(true);
    });
  });

  describe('Recurring events table', () => {
    it('displays recurring events', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const count = await upcomingPage.getRecurringEventCount();
      expect(count).toBeGreaterThan(0);
    });

    it('recurring events have all required fields populated', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const row = await upcomingPage.getFirstRecurringRow();
      const data = await upcomingPage.getRecurringRowData(row);

      expect(data.title?.trim().length).toBeGreaterThan(0);
      expect(data.location?.trim().length).toBeGreaterThan(0);
      expect(data.day?.trim()).toMatch(/Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday/);
    });
  });

  describe('Calendar', () => {
    it('displays calendar', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Calendar should be visible
      await upcomingPage.calendar.waitFor({ state: 'visible' });
      expect(await upcomingPage.calendar.isVisible()).toBe(true);
    });
  });

  describe('Map', () => {
    it('loads Google Maps with tournament markers when Show Map is clicked', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Click "Show map" button
      await upcomingPage.showMap();

      // Wait for Google Maps to load
      await upcomingPage.waitForMapLoaded();

      // Verify map is visible with content
      expect(await upcomingPage.isMapVisible()).toBe(true);

      // Verify markers are present (should have multiple tournament markers)
      const markerCount = await upcomingPage.getMapMarkerCount();
      expect(markerCount).toBeGreaterThan(0);

      // Verify we can get marker names (confirms markers have tournament data)
      const markerNames = await upcomingPage.getMapMarkerNames();
      expect(markerNames.length).toBeGreaterThan(0);
      expect(markerNames[0].length).toBeGreaterThan(0);
    });

    it('filters map markers by country', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Show the map first
      await upcomingPage.showMap();
      await upcomingPage.waitForMapLoaded();

      // Get initial marker count
      const initialMarkerCount = await upcomingPage.getMapMarkerCount();
      expect(initialMarkerCount).toBeGreaterThan(0);

      // Filter by Germany
      await upcomingPage.filterByCountry('Germany');
      await browser.getPage().waitForTimeout(1000);

      // Marker count should be reduced and all should be German
      const filteredMarkerCount = await upcomingPage.getMapMarkerCount();
      expect(filteredMarkerCount).toBeLessThan(initialMarkerCount);
      expect(filteredMarkerCount).toBeGreaterThan(0);

      // All markers should be from Germany
      const germanMarkerCount = await upcomingPage.getCountryMarkerCount('Germany');
      expect(germanMarkerCount).toBe(filteredMarkerCount);
    });
  });
});
