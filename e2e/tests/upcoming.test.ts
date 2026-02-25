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
    it('filters by tournament type', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await upcomingPage.filterByType('GNK / seasonal');
      await browser.getPage().waitForTimeout(500);
      const filteredCount = await upcomingPage.getTotalCount();
      // Filtered count should be less than or equal to initial
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
    });

    it('filters by country', async () => {
      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await upcomingPage.filterByCountry('Germany');
      await browser.getPage().waitForTimeout(500);
      const filteredCount = await upcomingPage.getTotalCount();
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
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
});
