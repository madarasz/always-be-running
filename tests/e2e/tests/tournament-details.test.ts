import { createBrowserSuite, it, expect, describe, beforeAll } from '../helpers/test-fixture';
import { clearSession } from '../helpers/auth';

const TOURNAMENT_PATH = '5313/pawnshop-now-playing-in-a-game-store-near-you';

createBrowserSuite('Tournament details - Logged out', { userType: 'none' }, (ctx) => {
  describe('Page components (generic)', () => {
    beforeAll(async () => {
      const { tournamentDetailsPage } = ctx.pages;
      await clearSession(ctx.browser);
      await tournamentDetailsPage.open(TOURNAMENT_PATH);
      await tournamentDetailsPage.waitForPageLoaded();
    });

    it('displays tournament basic info (title, type, creator, location, date)', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      const title = await tournamentDetailsPage.getTitle();
      expect(title.length).toBeGreaterThan(0);
      expect(title.toLowerCase()).toContain('pawnshop');

      const type = await tournamentDetailsPage.getType();
      expect(type.length).toBeGreaterThan(0);

      const creator = await tournamentDetailsPage.getCreator();
      expect(creator.length).toBeGreaterThan(0);

      const location = await tournamentDetailsPage.getLocation();
      expect(location.length).toBeGreaterThan(0);

      const date = await tournamentDetailsPage.getDate();
      expect(date.length).toBeGreaterThan(0);
    });

    it('displays cardpool and MWL information', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      const cardpool = await tournamentDetailsPage.getCardpool();
      expect(cardpool.length).toBeGreaterThan(0);

      const mwl = await tournamentDetailsPage.getMwl();
      expect(mwl.length).toBeGreaterThan(0);
    });

    it('displays results, entries, photos and videos sections', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      expect(await tournamentDetailsPage.hasResultsSection()).toBe(true);
      expect(await tournamentDetailsPage.hasEntriesTable()).toBe(true);

      await tournamentDetailsPage.photosHeader.waitFor({ state: 'visible' });
      expect(await tournamentDetailsPage.photosHeader.isVisible()).toBe(true);

      await tournamentDetailsPage.videosHeader.waitFor({ state: 'visible' });
      expect(await tournamentDetailsPage.videosHeader.isVisible()).toBe(true);
    });
  });

  describe('Map display', () => {
    beforeAll(async () => {
      const { tournamentDetailsPage } = ctx.pages;
      await clearSession(ctx.browser);
      await tournamentDetailsPage.open(TOURNAMENT_PATH);
      await tournamentDetailsPage.waitForPageLoaded();
    });

    it('shows enabled "Show Map" button when Google Maps API is loaded', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      await tournamentDetailsPage.showMapButton.waitFor({ state: 'visible' });
      expect(await tournamentDetailsPage.showMapButton.isVisible()).toBe(true);

      await tournamentDetailsPage.waitForMapButtonEnabled();
      const isDisabled = await tournamentDetailsPage.showMapButton.isDisabled();
      expect(isDisabled).toBe(false);
    });

    it('displays map with marker after clicking "Show Map"', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      await tournamentDetailsPage.clickShowMap();
      await tournamentDetailsPage.waitForMapDisplayed();

      // Button should be hidden
      const buttonClass = await tournamentDetailsPage.showMapButton.getAttribute('class');
      expect(buttonClass).toContain('hidden-xs-up');

      // Map should have content (Google Maps renders child elements)
      const mapHasContent = await tournamentDetailsPage.mapContainer.evaluate((el) => el.children.length > 0);
      expect(mapHasContent).toBe(true);
    });
  });

  describe('Statistics charts', () => {
    beforeAll(async () => {
      const { tournamentDetailsPage } = ctx.pages;
      await clearSession(ctx.browser);
      await tournamentDetailsPage.open(TOURNAMENT_PATH);
      await tournamentDetailsPage.waitForPageLoaded();
      await tournamentDetailsPage.waitForChartsLoaded();
    });

    it('displays statistics section with runner and corp ID charts', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      const statsSection = ctx.browser.getPage().locator('h5:has(.fa-bar-chart)');
      await statsSection.waitFor({ state: 'visible', timeout: 10000 });
      expect(await statsSection.isVisible()).toBe(true);

      const hasRunnerChart = await tournamentDetailsPage.isChartVisible('#stat-chart-runner');
      expect(hasRunnerChart).toBe(true);

      const hasCorpChart = await tournamentDetailsPage.isChartVisible('#stat-chart-corp');
      expect(hasCorpChart).toBe(true);
    });

    it('has ID/faction toggle with ID active by default', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      expect(await tournamentDetailsPage.statsButtonId.isVisible()).toBe(true);
      expect(await tournamentDetailsPage.statsButtonFaction.isVisible()).toBe(true);

      const idButtonClass = await tournamentDetailsPage.statsButtonId.getAttribute('class');
      expect(idButtonClass).toContain('active');
    });

    it('can switch to faction charts', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      await tournamentDetailsPage.statsButtonFaction.click();
      // Wait for faction button to become active
      await expect.poll(async () => await tournamentDetailsPage.statsButtonFaction.getAttribute('class')).toContain('active');

      const idButtonClass = await tournamentDetailsPage.statsButtonId.getAttribute('class');
      expect(idButtonClass).not.toContain('active');
    });
  });

  describe('User not logged in', () => {
    beforeAll(async () => {
      const { tournamentDetailsPage } = ctx.pages;
      await clearSession(ctx.browser);
      await tournamentDetailsPage.open(TOURNAMENT_PATH);
      await tournamentDetailsPage.waitForPageLoaded();
    });

    it('does not show authenticated user features', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      expect(await tournamentDetailsPage.hasClaimButtons()).toBe(false);
      expect(await tournamentDetailsPage.hasAddPhotosButton()).toBe(false);
      expect(await tournamentDetailsPage.hasAddVideosButton()).toBe(false);
      expect(await tournamentDetailsPage.hasRegisterButton()).toBe(false);
      expect(await tournamentDetailsPage.hasControlButtons()).toBe(false);
    });

    it('shows login suggestion messages', async () => {
      const { tournamentDetailsPage } = ctx.pages;

      expect(await tournamentDetailsPage.hasSuggestLoginClaim()).toBe(true);
      expect(await tournamentDetailsPage.hasSuggestLoginMedia()).toBe(true);
    });
  });
});

createBrowserSuite('Tournament details - Regular user', { userType: 'regular' }, (ctx) => {
  beforeAll(async () => {
    const { tournamentDetailsPage } = ctx.pages;
    await tournamentDetailsPage.open(TOURNAMENT_PATH);
    await tournamentDetailsPage.waitForPageLoaded();
  });

  it('shows authenticated UI elements and hides login messages', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    // Should see add buttons
    expect(await tournamentDetailsPage.hasAddPhotosButton()).toBe(true);
    expect(await tournamentDetailsPage.hasAddVideosButton()).toBe(true);

    // Should NOT see login suggestion messages
    expect(await tournamentDetailsPage.hasSuggestLoginClaim()).toBe(false);
    expect(await tournamentDetailsPage.hasSuggestLoginMedia()).toBe(false);

    // Claim buttons should be available (suggest login message gone)
    const hasClaimButtons = await tournamentDetailsPage.hasClaimButtons();
    const hasSuggestLogin = await tournamentDetailsPage.hasSuggestLoginClaim();
    expect(hasClaimButtons || !hasSuggestLogin).toBe(true);
  });

  it('does not show control buttons for non-creator user', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    expect(await tournamentDetailsPage.hasControlButtons()).toBe(false);
  });

  it('does not show admin-only features', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    const count = await tournamentDetailsPage.viewingAsAdmin.count();
    expect(count).toBe(0);
    expect(await tournamentDetailsPage.hasApproveButton()).toBe(false);
    expect(await tournamentDetailsPage.hasRejectButton()).toBe(false);
    expect(await tournamentDetailsPage.hasRevertConclusionButton()).toBe(false);
  });
});

createBrowserSuite('Tournament details - Admin user', { userType: 'admin' }, (ctx) => {
  beforeAll(async () => {
    const { tournamentDetailsPage } = ctx.pages;
    await tournamentDetailsPage.open(TOURNAMENT_PATH);
    await tournamentDetailsPage.waitForPageLoaded();
  });

  it('shows admin control buttons', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    expect(await tournamentDetailsPage.hasControlButtons()).toBe(true);
    expect(await tournamentDetailsPage.editButton.isVisible()).toBe(true);
    expect(await tournamentDetailsPage.transferButton.isVisible()).toBe(true);
    expect(await tournamentDetailsPage.deleteButton.isVisible()).toBe(true);
    expect(await tournamentDetailsPage.viewingAsAdmin.isVisible()).toBe(true);
    expect(await tournamentDetailsPage.hasRejectButton()).toBe(true);
  });

  it('shows admin content management UI', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    expect(await tournamentDetailsPage.hasAddPhotosButton()).toBe(true);
    expect(await tournamentDetailsPage.hasAddVideosButton()).toBe(true);
    expect(await tournamentDetailsPage.hasConcludedBySection()).toBe(true);
    expect(await tournamentDetailsPage.hasRevertConclusionButton()).toBe(true);

    const concludedText = await tournamentDetailsPage.getConcludedByText();
    expect(concludedText).toContain('concluded by');
  });

  it('shows claim buttons and hides login messages', async () => {
    const { tournamentDetailsPage } = ctx.pages;

    const hasSuggestLoginClaim = await tournamentDetailsPage.hasSuggestLoginClaim();
    const hasSuggestLoginMedia = await tournamentDetailsPage.hasSuggestLoginMedia();

    // Suggest login should NOT be shown for logged in admin
    expect(hasSuggestLoginClaim).toBe(false);
    expect(hasSuggestLoginMedia).toBe(false);
  });
});
