import { createBrowserSuite, it, expect } from '../helpers/test-fixture';

createBrowserSuite('Prizes page', { userType: 'none' }, (ctx) => {
  it('displays information on the Official kits tab', async () => {
    const { prizesPage } = ctx.pages;

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
    const { prizesPage } = ctx.pages;

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
    const { prizesPage } = ctx.pages;

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
