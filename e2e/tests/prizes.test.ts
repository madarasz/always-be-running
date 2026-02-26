import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { PrizesPage } from '../pages/PrizesPage';
import { closeBrowserSafely, CHROME_PATH } from '../helpers/auth';

describe('Prizes page', () => {
  let browser: BrowserManager;
  let prizesPage: PrizesPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    prizesPage = new PrizesPage(browser);
  });

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  it('displays information on the Official kits tab', async () => {
    await prizesPage.open();
    await prizesPage.waitForPrizesLoaded();

    // Verify kit titles are displayed
    const titles = await prizesPage.getKitTitles();
    expect(titles.length).toBeGreaterThan(0);

    // Verify filter dropdown has options
    const filterOptions = await prizesPage.getFilterOptions();
    expect(filterOptions.length).toBeGreaterThan(1);
  });

  it('filters prize kits on the Official kits tab', async () => {
    await prizesPage.open();
    await prizesPage.waitForPrizesLoaded();

    // Get initial filter options
    const filterOptions = await prizesPage.getFilterOptions();
    expect(filterOptions.length).toBeGreaterThan(1);

    // Select a specific kit (second option, first is "--- all ---")
    const specificKit = filterOptions[1];
    await prizesPage.selectFilterOption(specificKit);

    // Verify filtering changed the display
    const titlesAfterFilter = await prizesPage.getKitTitles();
    expect(titlesAfterFilter.some(t => t.includes(specificKit.trim()))).toBe(true);
  });

  it('displays information on the Other art tab', async () => {
    await prizesPage.open();
    await prizesPage.waitForPrizesLoaded();

    // Click Other art tab
    await prizesPage.clickOtherArtTab();
    await prizesPage.waitForArtistsLoaded();

    // Verify artist names are displayed
    const artistNames = await prizesPage.getArtistNames();
    expect(artistNames.length).toBeGreaterThan(0);

    // Verify art items exist
    const artItemCount = await prizesPage.getArtItemCount();
    expect(artItemCount).toBeGreaterThan(0);
  });
});
