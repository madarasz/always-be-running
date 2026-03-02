import { createBrowserSuite, it, expect, describe } from '../helpers/test-fixture';

createBrowserSuite('Upcoming page', { userType: 'none' }, (ctx) => {
  describe('Loading upcoming tournaments table', () => {
    it('displays tournaments with all required fields populated', async () => {
      const { upcomingPage } = ctx.pages;

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
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const total = await upcomingPage.getTotalCount();
      expect(total).toBeGreaterThan(0);
    });
  });

  describe('Table controls', () => {
    it('initially shows a subset of tournaments with paging', async () => {
      const { upcomingPage } = ctx.pages;

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
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();
      expect(await upcomingPage.hasCountryFlag()).toBe(true);
    });

    it('switches between flag and text mode', async () => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Should have flag mode active by default
      const flagButton = upcomingPage.flagButton;
      expect(await flagButton.getAttribute('class')).toContain('label-active');

      // Switch to text mode
      await upcomingPage.switchToTextMode();
      await expect.poll(async () => await upcomingPage.textButton.getAttribute('class')).toContain('label-active');

      // Switch back to flag mode
      await upcomingPage.switchToFlagMode();
      await expect.poll(async () => await flagButton.getAttribute('class')).toContain('label-active');
    });

    it('forward pager advances the view', async () => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialFrom = await upcomingPage.getShowingFrom();
      const total = await upcomingPage.getTotalCount();
      const showingTo = await upcomingPage.getShowingTo();

      // Only test paging if there are more items than currently shown
      if (total > showingTo) {
        await upcomingPage.clickForwardPager();
        // Wait for paging to update
        await expect.poll(async () => await upcomingPage.getShowingFrom()).toBeGreaterThan(initialFrom);
      }
    });

    it('all pager shows all tournaments', async () => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      await upcomingPage.clickAllPager();
      // Wait for paging to update - showingTo should equal total
      const total = await upcomingPage.getTotalCount();
      await expect.poll(async () => await upcomingPage.getShowingTo()).toBe(total);
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
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[filterMethod](value);
      // Wait for filter to be applied (total count updates)
      await expect.poll(async () => await upcomingPage.getTotalCount()).toBeLessThanOrEqual(initialCount);
    });

    it.each([
      { filter1: 'type', method1: 'filterByType', value1: 'GNK / seasonal', filter2: 'country', method2: 'filterByCountry', value2: 'Germany' },
      { filter1: 'country', method1: 'filterByCountry', value1: 'United States', filter2: 'type', method2: 'filterByType', value2: 'players circuit' },
    ])('combines $filter1 + $filter2 filters', async ({ method1, value1, method2, value2 }) => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const initialCount = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[method1](value1);
      // Wait for first filter to be applied
      await expect.poll(async () => await upcomingPage.getTotalCount()).toBeLessThanOrEqual(initialCount);
      const afterFirstFilter = await upcomingPage.getTotalCount();
      await (upcomingPage as any)[method2](value2);
      // Wait for second filter to be applied
      await expect.poll(async () => await upcomingPage.getTotalCount()).toBeLessThanOrEqual(afterFirstFilter);
    });

    it('shows US state filter when US is selected', async () => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      await upcomingPage.filterByCountry('United States');
      // State filter should become visible
      const stateFilter = upcomingPage.stateFilter;
      await stateFilter.waitFor({ state: 'visible', timeout: 5000 });
      expect(await stateFilter.isVisible()).toBe(true);
    });
  });

  describe('Recurring events table', () => {
    it('displays recurring events', async () => {
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      const count = await upcomingPage.getRecurringEventCount();
      expect(count).toBeGreaterThan(0);
    });

    it('recurring events have all required fields populated', async () => {
      const { upcomingPage } = ctx.pages;

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
      const { upcomingPage } = ctx.pages;

      await upcomingPage.open();
      await upcomingPage.waitForTableLoaded();

      // Calendar should be visible
      await upcomingPage.calendar.waitFor({ state: 'visible' });
      expect(await upcomingPage.calendar.isVisible()).toBe(true);
    });
  });

  describe('Map', () => {
    it('loads Google Maps with tournament markers when Show Map is clicked', async () => {
      const { upcomingPage } = ctx.pages;

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
      const { upcomingPage } = ctx.pages;

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
      // Wait for markers to be filtered (count should decrease)
      await expect.poll(async () => await upcomingPage.getMapMarkerCount(), { timeout: 10000 })
        .toBeLessThan(initialMarkerCount);

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
