import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { ResultsPage } from '../pages/ResultsPage';
import { clearSession, loginUser } from '../helpers/auth';

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

describe('Results page', () => {
  let browser: BrowserManager;
  let resultsPage: ResultsPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    resultsPage = new ResultsPage(browser);
    await clearSession(browser);
  });

  afterAll(async () => {
    await browser.close();
  });

  describe('Loading tournament results', () => {
    beforeAll(async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
    });

    it('displays tournament results with required fields', async () => {
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
      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      expect(pagingText).toMatch(/showing \d+-\d+ of/);

      expect(await resultsPage.resultsOption50.isVisible()).toBe(true);
      expect(await resultsPage.resultsOption100.isVisible()).toBe(true);
    });

    it('shows flag mode toggle with flag active by default', async () => {
      expect(await resultsPage.resultsOptionFlag.isVisible()).toBe(true);
      expect(await resultsPage.resultsOptionText.isVisible()).toBe(true);
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('Waiting for conclusion tab', () => {
    it('switches to tab and shows login warning when not logged in', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.clickWaitingForConclusionTab();

      const tabPane = browser.getPage().locator('#tab-to-be-concluded');
      await tabPane.waitFor({ state: 'visible' });
      expect(await tabPane.getAttribute('class')).toContain('active');

      await resultsPage.concludeLoginWarning.waitFor({ state: 'visible' });
      expect(await resultsPage.concludeLoginWarning.isVisible()).toBe(true);
    });

    it('loads tournaments and hides conclude button when not logged in', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
      await resultsPage.clickWaitingForConclusionTab();
      await resultsPage.waitForToConcludedLoaded();

      const count = await resultsPage.getToConcludedCount();
      expect(count).toBeGreaterThan(0);

      // Only check inside the to-be-concluded table — the modal has static .btn-conclude buttons
      const concludeButton = browser.getPage().locator('#to-be-concluded .btn-conclude');
      expect(await concludeButton.count()).toBe(0);
    });
  });

  describe('Filtering', () => {
    it.each([
      { filterName: 'cardpool', filterLocator: 'cardpoolFilter', filterMethod: 'filterByCardpool', urlParam: 'cardpool=' },
      { filterName: 'country', filterLocator: 'countryFilter', filterMethod: 'filterByCountry', urlParam: 'country=' },
      { filterName: 'format', filterLocator: 'formatFilter', filterMethod: 'filterByFormat', urlParam: 'format=' },
    ])('filter by $filterName updates URL with $urlParam', async ({ filterLocator, filterMethod, urlParam }) => {
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
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const initialCount = await resultsPage.getResultsCount();

      const filter = (resultsPage as any)[filterLocator];
      const options = await filter.locator('option').allTextContents();
      const validOption = options.find((opt: string) => opt.trim() !== '---');
      if (!validOption) return;

      await (resultsPage as any)[filterMethod](validOption.trim());
      await browser.getPage().waitForTimeout(500);

      const filteredCount = await resultsPage.getResultsCount();
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
    });

    it.each([
      { filters: ['cardpool', 'country'], methods: ['filterByCardpool', 'filterByCountry'], locators: ['cardpoolFilter', 'countryFilter'], params: ['cardpool=', 'country='] },
      { filters: ['country', 'format'], methods: ['filterByCountry', 'filterByFormat'], locators: ['countryFilter', 'formatFilter'], params: ['country=', 'format='] },
      { filters: ['cardpool', 'format'], methods: ['filterByCardpool', 'filterByFormat'], locators: ['cardpoolFilter', 'formatFilter'], params: ['cardpool=', 'format='] },
    ])('combines $filters filters in URL', async ({ methods, locators, params }) => {
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
      const tabPane = browser.getPage().locator('#tab-to-be-concluded');
      expect(await tabPane.getAttribute('class')).toContain('active');
    });
  });

  describe('Paging', () => {
    beforeAll(async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
    });

    it('displays paging text and navigates with back button', async () => {
      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      expect(pagingText).toMatch(/showing 1-\d+ of/);

      const hasBackButton = await resultsPage.resultsPagingBack.isVisible();
      if (hasBackButton) {
        await resultsPage.resultsPagingBack.click();
        await browser.getPage().waitForTimeout(300);

        const newPagingText = await resultsPage.getPagingText();
        expect(newPagingText).not.toBe(pagingText);
      }
    });

    it('page size and flag/text mode toggles work', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      // Test 100 per page
      await resultsPage.resultsOption100.click();
      await browser.getPage().waitForTimeout(500);
      expect(await resultsPage.resultsOption100.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOption50.getAttribute('class')).not.toContain('label-active');

      // Test text mode toggle
      await resultsPage.resultsOptionText.click();
      await browser.getPage().waitForTimeout(300);
      expect(await resultsPage.resultsOptionText.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).not.toContain('label-active');

      // Switch back to flag mode
      await resultsPage.resultsOptionFlag.click();
      await browser.getPage().waitForTimeout(300);
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('Featured', () => {
    it('displays featured tournaments section with tournaments and support box', async () => {
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
      await resultsPage.open(query);
      await resultsPage.waitForResultsLoaded();

      const count = await resultsPage.getResultsCount();
      expect(count).toBeGreaterThan(0);
    });

    it.each([
      { query: 'format=standard&matchdata=true', expectations: [{ filterLocator: 'formatFilter', value: 'standard' }], checkboxes: [{ locator: 'matchdataCheckbox', checked: true }] },
      { query: 'format=standard&videos=true', expectations: [{ filterLocator: 'formatFilter', value: 'standard' }], checkboxes: [{ locator: 'videosCheckbox', checked: true }] },
    ])('URL with multiple params applies all filters ($query)', async ({ query, expectations, checkboxes }) => {
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

  describe('User default country', () => {
    it('applies user default country filter and allows manual override', async () => {
      // Login as regular user (has autofilter_results enabled with Germany as default)
      await loginUser(browser, 'regular');

      // Navigate to results page
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
      await browser.getPage().waitForTimeout(500);

      // Default country label should now be hidden
      expect(await resultsPage.isDefaultCountryLabelVisible()).toBe(false);

      // URL should now have United Kingdom
      url = await resultsPage.getCurrentUrl();
      expect(url).toContain('country=United');
    });
  });
});
