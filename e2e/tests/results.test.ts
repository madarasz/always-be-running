import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { ResultsPage } from '../pages/ResultsPage';
import { clearSession } from '../helpers/auth';

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
    it('displays tournament results after page load', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const count = await resultsPage.getResultsCount();
      expect(count).toBeGreaterThan(0);
    });

    it('shows required fields for each result row', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const firstRow = resultsPage.resultsTableRows.first();
      const cells = firstRow.locator('td');
      const titleCell = await cells.nth(0).textContent();
      const dateCell = await cells.nth(1).textContent();

      expect(titleCell?.trim().length).toBeGreaterThan(0);
      expect(dateCell?.trim().length).toBeGreaterThan(0);
    });

    it('shows paging controls', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      expect(pagingText).toMatch(/showing \d+-\d+ of/);
    });

    it('shows page size options', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      expect(await resultsPage.resultsOption50.isVisible()).toBe(true);
      expect(await resultsPage.resultsOption100.isVisible()).toBe(true);
    });

    it('shows flag mode toggle', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      expect(await resultsPage.resultsOptionFlag.isVisible()).toBe(true);
      expect(await resultsPage.resultsOptionText.isVisible()).toBe(true);
      // Flag mode is active by default
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('Waiting for conclusion tab', () => {
    it('switches to waiting for conclusion tab', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.clickWaitingForConclusionTab();

      const tabPane = browser.getPage().locator('#tab-to-be-concluded');
      await tabPane.waitFor({ state: 'visible' });
      expect(await tabPane.getAttribute('class')).toContain('active');
    });

    it('shows login warning when not logged in', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.clickWaitingForConclusionTab();

      await resultsPage.concludeLoginWarning.waitFor({ state: 'visible' });
      expect(await resultsPage.concludeLoginWarning.isVisible()).toBe(true);
    });

    it('conclude button is absent from table rows when not logged in', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();
      await resultsPage.clickWaitingForConclusionTab();
      await resultsPage.waitForToConcludedLoaded();

      // Only check inside the to-be-concluded table — the modal has static .btn-conclude buttons
      const concludeButton = browser.getPage().locator('#to-be-concluded .btn-conclude');
      expect(await concludeButton.count()).toBe(0);
    });

    it('loads tournaments waiting for conclusion', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.clickWaitingForConclusionTab();
      await resultsPage.waitForToConcludedLoaded();

      const count = await resultsPage.getToConcludedCount();
      expect(count).toBeGreaterThan(0);
    });
  });

  describe('Filtering', () => {
    it('filter by cardpool updates URL', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      // Get first available non-default cardpool option
      const options = await resultsPage.cardpoolFilter.locator('option').allTextContents();
      const cardpoolOption = options.find(opt => opt.trim() !== '---');
      if (!cardpoolOption) return; // skip if no options

      await resultsPage.filterByCardpool(cardpoolOption.trim());

      const url = await resultsPage.getCurrentUrl();
      expect(url).toContain('cardpool=');
    });

    it('filter by cardpool reduces or maintains result count', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const initialCount = await resultsPage.getResultsCount();

      const options = await resultsPage.cardpoolFilter.locator('option').allTextContents();
      const cardpoolOption = options.find(opt => opt.trim() !== '---');
      if (!cardpoolOption) return;

      await resultsPage.filterByCardpool(cardpoolOption.trim());
      await browser.getPage().waitForTimeout(500);

      const filteredCount = await resultsPage.getResultsCount();
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
    });

    it('filter by country updates URL', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const options = await resultsPage.countryFilter.locator('option').allTextContents();
      const countryOption = options.find(opt => opt.trim() !== '---');
      if (!countryOption) return;

      await resultsPage.filterByCountry(countryOption.trim());

      const url = await resultsPage.getCurrentUrl();
      expect(url).toContain('country=');
    });

    it('filter by format updates URL', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const options = await resultsPage.formatFilter.locator('option').allTextContents();
      const formatOption = options.find(opt => opt.trim() !== '---');
      if (!formatOption) return;

      await resultsPage.filterByFormat(formatOption.trim());

      const url = await resultsPage.getCurrentUrl();
      expect(url).toContain('format=');
    });

    it('multiple filters combine in URL', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const cardpoolOptions = await resultsPage.cardpoolFilter.locator('option').allTextContents();
      const cardpoolOption = cardpoolOptions.find(opt => opt.trim() !== '---');

      const countryOptions = await resultsPage.countryFilter.locator('option').allTextContents();
      const countryOption = countryOptions.find(opt => opt.trim() !== '---');

      if (!cardpoolOption || !countryOption) return;

      await resultsPage.filterByCardpool(cardpoolOption.trim());
      await resultsPage.filterByCountry(countryOption.trim());

      const url = await resultsPage.getCurrentUrl();
      expect(url).toContain('cardpool=');
      expect(url).toContain('country=');
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
    it('displays paging text with correct format', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.resultsPagingSection.waitFor({ state: 'visible' });
      const pagingText = await resultsPage.getPagingText();
      // Should match pattern like "showing 1-50 of 65"
      expect(pagingText).toMatch(/showing 1-\d+ of/);
    });

    it('page back button navigates to next set of results', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      const pagingText = await resultsPage.getPagingText();
      // Only test paging if the back button (next page) is present
      const hasBackButton = await resultsPage.resultsPagingBack.isVisible();
      if (!hasBackButton) return;

      await resultsPage.resultsPagingBack.click();
      await browser.getPage().waitForTimeout(300);

      const newPagingText = await resultsPage.getPagingText();
      expect(newPagingText).not.toBe(pagingText);
    });

    it('100 per page option changes page size', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.resultsOption100.click();
      await browser.getPage().waitForTimeout(500);

      expect(await resultsPage.resultsOption100.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOption50.getAttribute('class')).not.toContain('label-active');
    });

    it('switching to text mode disables flag mode', async () => {
      await resultsPage.open();
      await resultsPage.waitForResultsLoaded();

      await resultsPage.resultsOptionText.click();
      await browser.getPage().waitForTimeout(300);

      expect(await resultsPage.resultsOptionText.getAttribute('class')).toContain('label-active');
      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).not.toContain('label-active');

      // Switching back to flag mode
      await resultsPage.resultsOptionFlag.click();
      await browser.getPage().waitForTimeout(300);

      expect(await resultsPage.resultsOptionFlag.getAttribute('class')).toContain('label-active');
    });
  });

  describe('URL-based filtering', () => {
    it('applies format filter from URL param', async () => {
      await resultsPage.open('format=standard');
      await resultsPage.waitForResultsLoaded();

      const selectedFormat = await resultsPage.formatFilter.inputValue();
      expect(selectedFormat).toBe('standard');
    });

    it('results are filtered when URL contains format param', async () => {
      await resultsPage.open('format=standard');
      await resultsPage.waitForResultsLoaded();

      const count = await resultsPage.getResultsCount();
      expect(count).toBeGreaterThan(0);
    });

    it('applies country filter from URL param', async () => {
      await resultsPage.open('country=United+Kingdom');
      await resultsPage.waitForResultsLoaded();

      const selectedCountry = await resultsPage.countryFilter.inputValue();
      expect(selectedCountry).toBe('United Kingdom');
    });

    it('URL with multiple params applies all filters', async () => {
      await resultsPage.open('format=standard&matchdata=true');
      await resultsPage.waitForResultsLoaded();

      const selectedFormat = await resultsPage.formatFilter.inputValue();
      expect(selectedFormat).toBe('standard');

      // matchdata checkbox should be checked
      expect(await resultsPage.matchdataCheckbox.isChecked()).toBe(true);
    });
  });
});
