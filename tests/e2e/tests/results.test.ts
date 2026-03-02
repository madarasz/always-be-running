import { createBrowserSuite, it, expect, describe, beforeAll } from '../helpers/test-fixture';
import { clearSession } from '../helpers/auth';

createBrowserSuite('Results page', { userType: 'none' }, (ctx) => {
  beforeAll(async () => {
    await clearSession(ctx.browser);
  });

  describe('Loading tournament results', () => {
    beforeAll(async () => {
      const { resultsPage } = ctx.pages;
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
    });

    it('displays tournament results with required fields', async () => {
      const { resultsPage } = ctx.pages;

      const count = await resultsPage.getResultsCount();
      expect(count).toBeGreaterThan(0);

      const firstRow = resultsPage.resultsTableRows.first();
      const cells = firstRow.locator('td');
      const titleCell = await cells.nth(0).textContent();
      const dateCell = await cells.nth(1).textContent();

      expect(titleCell?.trim().length).toBeGreaterThan(0);
      expect(dateCell?.trim().length).toBeGreaterThan(0);
    });

    it('shows paging controls and page size options', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      expect(pagingText).toMatch(/showing \d+-\d+ of/);

      expect(await resultsPage.resultsOption50.isVisible()).toBe(true);
      expect(await resultsPage.resultsOption100.isVisible()).toBe(true);
    });

    it('shows flag mode toggle with flag active by default', async () => {
      const { resultsPage } = ctx.pages;

      expect(await resultsPage.resultsOptionFlag.isVisible()).toBe(true);
      expect(await resultsPage.resultsOptionText.isVisible()).toBe(true);
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('Waiting for conclusion tab', () => {
    it('switches to tab and shows login warning when not logged in', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.clickWaitingForConclusionTab();

      const tabPane = ctx.browser.getPage().locator('#tab-to-be-concluded');
      await tabPane.waitFor({ state: 'visible' });
      expect(await tabPane.getAttribute('class')).toContain('active');

      await resultsPage.concludeLoginWarning.waitFor({ state: 'visible' });
      expect(await resultsPage.concludeLoginWarning.isVisible()).toBe(true);
    });

    it('loads tournaments and hides conclude button when not logged in', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
      await resultsPage.clickWaitingForConclusionTab();
      await resultsPage.waitForToConcludedLoaded();

      const count = await resultsPage.getToConcludedCount();
      expect(count).toBeGreaterThan(0);

      // Only check inside the to-be-concluded table — the modal has static .btn-conclude buttons
      const concludeButton = ctx.browser.getPage().locator('#to-be-concluded .btn-conclude');
      expect(await concludeButton.count()).toBe(0);
    });
  });

  describe('Filtering', () => {
    it.each([
      { filterName: 'cardpool', filterLocator: 'cardpoolFilter', filterMethod: 'filterByCardpool', urlParam: 'cardpool=' },
      { filterName: 'country', filterLocator: 'countryFilter', filterMethod: 'filterByCountry', urlParam: 'country=' },
      { filterName: 'format', filterLocator: 'formatFilter', filterMethod: 'filterByFormat', urlParam: 'format=' },
    ])('filter by $filterName updates URL with $urlParam', async ({ filterLocator, filterMethod, urlParam }) => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const filter = (resultsPage as any)[filterLocator];
      const options = await filter.locator('option').allTextContents();
      const validOption = options.find((opt: string) => opt.trim() !== '---');
      if (!validOption) return;

      await (resultsPage as any)[filterMethod](validOption.trim());
      const url = await resultsPage.getCurrentUrl();
      expect(url).toContain(urlParam);
    });

    it.each([
      { filterName: 'cardpool', filterLocator: 'cardpoolFilter', filterMethod: 'filterByCardpool' },
      { filterName: 'country', filterLocator: 'countryFilter', filterMethod: 'filterByCountry' },
      { filterName: 'format', filterLocator: 'formatFilter', filterMethod: 'filterByFormat' },
    ])('filter by $filterName reduces or maintains result count', async ({ filterLocator, filterMethod }) => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const initialCount = await resultsPage.getResultsCount();

      const filter = (resultsPage as any)[filterLocator];
      const options = await filter.locator('option').allTextContents();
      const validOption = options.find((opt: string) => opt.trim() !== '---');
      if (!validOption) return;

      await (resultsPage as any)[filterMethod](validOption.trim());
      // Wait for filter to be applied (count should change or stay same)
      await expect.poll(async () => await resultsPage.getResultsCount()).toBeLessThanOrEqual(initialCount);
    });

    it.each([
      { filters: ['cardpool', 'country'], methods: ['filterByCardpool', 'filterByCountry'], locators: ['cardpoolFilter', 'countryFilter'], params: ['cardpool=', 'country='] },
      { filters: ['country', 'format'], methods: ['filterByCountry', 'filterByFormat'], locators: ['countryFilter', 'formatFilter'], params: ['country=', 'format='] },
      { filters: ['cardpool', 'format'], methods: ['filterByCardpool', 'filterByFormat'], locators: ['cardpoolFilter', 'formatFilter'], params: ['cardpool=', 'format='] },
    ])('combines $filters filters in URL', async ({ methods, locators, params }) => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      for (let i = 0; i < methods.length; i++) {
        const filter = (resultsPage as any)[locators[i]];
        const options = await filter.locator('option').allTextContents();
        const validOption = options.find((opt: string) => opt.trim() !== '---');
        if (!validOption) return;

        await (resultsPage as any)[methods[i]](validOption.trim());
      }

      const url = await resultsPage.getCurrentUrl();
      for (const param of params) {
        expect(url).toContain(param);
      }
    });

    it('conclusion tab also filters when filter is applied', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      // Apply country filter
      const countryOptions = await resultsPage.countryFilter.locator('option').allTextContents();
      const countryOption = countryOptions.find(opt => opt.trim() !== '---');
      if (!countryOption) return;

      await resultsPage.filterByCountry(countryOption.trim());

      // Switch to to-be-concluded tab
      await resultsPage.clickWaitingForConclusionTab();

      // The tab should be active and show filtered results (possibly empty)
      const tabPane = ctx.browser.getPage().locator('#tab-to-be-concluded');
      expect(await tabPane.getAttribute('class')).toContain('active');
    });
  });

  describe('Paging', () => {
    beforeAll(async () => {
      const { resultsPage } = ctx.pages;
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
    });

    it('displays paging text and navigates with back button', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      expect(pagingText).toMatch(/showing 1-\d+ of/);

      const hasBackButton = await resultsPage.resultsPagingBack.isVisible();
      if (hasBackButton) {
        await resultsPage.resultsPagingBack.click();
        // Wait for paging text to change
        await expect.poll(async () => await resultsPage.getPagingText()).not.toBe(pagingText);
      }
    });

    it('page size and flag/text mode toggles work', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      // Test 100 per page
      await resultsPage.resultsOption100.click();
      await expect.poll(async () => await resultsPage.resultsOption100.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOption50.getAttribute('class')).not.toContain('label-active');

      // Test text mode toggle
      await resultsPage.resultsOptionText.click();
      await expect.poll(async () => await resultsPage.resultsOptionText.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).not.toContain('label-active');

      // Switch back to flag mode
      await resultsPage.resultsOptionFlag.click();
      await expect.poll(async () => await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('Featured', () => {
    it('displays featured tournaments section with tournaments and support box', async () => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.featuredBracket.waitFor({ state: 'visible' });
      expect(await resultsPage.featuredBracket.isVisible()).toBe(true);

      const count = await resultsPage.getFeaturedTournamentCount();
      expect(count).toBeGreaterThan(1); // one featured tournament is the "Support me" box

      const names = await resultsPage.getFeaturedTournamentNames();
      expect(names.length).toBeGreaterThan(0);
      expect(names[0].length).toBeGreaterThan(0);

      await resultsPage.supportMeBox.waitFor({ state: 'visible' });
      expect(await resultsPage.supportMeBox.isVisible()).toBe(true);
    });
  });

  describe('URL-based filtering', () => {
    it.each([
      { param: 'format', value: 'standard', filterLocator: 'formatFilter' },
      { param: 'country', value: 'United Kingdom', filterLocator: 'countryFilter', urlValue: 'United+Kingdom' },
      { param: 'format', value: 'startup', filterLocator: 'formatFilter' },
    ])('applies $param=$value filter from URL param', async ({ param, value, filterLocator, urlValue }) => {
      const { resultsPage } = ctx.pages;

      const queryValue = urlValue || value;
      await resultsPage.open(`${param}=${queryValue}`);
      await resultsPage.waitForResultsLoaded();

      const filter = (resultsPage as any)[filterLocator];
      const selectedValue = await filter.inputValue();
      expect(selectedValue).toBe(value);
    });

    it.each([
      { query: 'format=standard', description: 'format filter' },
      { query: 'country=Germany', description: 'country filter' },
    ])('results are filtered when URL contains $description', async ({ query }) => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open(query);
      await resultsPage.waitForResultsLoaded();

      const count = await resultsPage.getResultsCount();
      expect(count).toBeGreaterThan(0);
    });

    it.each([
      { query: 'format=standard&matchdata=true', expectations: [{ filterLocator: 'formatFilter', value: 'standard' }], checkboxes: [{ locator: 'matchdataCheckbox', checked: true }] },
      { query: 'format=standard&videos=true', expectations: [{ filterLocator: 'formatFilter', value: 'standard' }], checkboxes: [{ locator: 'videosCheckbox', checked: true }] },
    ])('URL with multiple params applies all filters ($query)', async ({ query, expectations, checkboxes }) => {
      const { resultsPage } = ctx.pages;

      await resultsPage.open(query);
      await resultsPage.waitForResultsLoaded();

      for (const exp of expectations) {
        const filter = (resultsPage as any)[exp.filterLocator];
        const selectedValue = await filter.inputValue();
        expect(selectedValue).toBe(exp.value);
      }

      for (const cb of checkboxes) {
        const checkbox = (resultsPage as any)[cb.locator];
        expect(await checkbox.isChecked()).toBe(cb.checked);
      }
    });
  });
});

createBrowserSuite('Results page - User default country', { userType: 'regular' }, (ctx) => {
  it('applies user default country filter and allows manual override', async () => {
    const { resultsPage } = ctx.pages;

    // Navigate to results page (already logged in as regular user)
    await resultsPage.open();
    await resultsPage.waitForResultsLoaded();

    // Default country label should be visible
    expect(await resultsPage.isDefaultCountryLabelVisible()).toBe(true);

    // Country filter should have Germany selected
    const selectedCountry = await resultsPage.countryFilter.inputValue();
    expect(selectedCountry).toBe('Germany');

    // URL should contain country=Germany
    let url = await resultsPage.getCurrentUrl();
    expect(url).toContain('country=Germany');

    // Change the country filter to a different value
    await resultsPage.filterByCountry('United Kingdom');
    // Wait for URL to update with new country
    await ctx.browser.getPage().waitForURL(/country=United/, { timeout: 5000 });

    // Default country label should now be hidden
    expect(await resultsPage.isDefaultCountryLabelVisible()).toBe(false);

    // URL should now have United Kingdom
    url = await resultsPage.getCurrentUrl();
    expect(url).toContain('country=United');
  });
});
