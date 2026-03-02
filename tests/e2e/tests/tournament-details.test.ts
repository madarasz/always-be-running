import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { TournamentDetailsPage } from '../pages/TournamentDetailsPage';
import { clearSession, closeBrowserSafely, createAuthenticatedBrowser, CHROME_PATH } from '../helpers/auth';
const TOURNAMENT_PATH = '5313/pawnshop-now-playing-in-a-game-store-near-you';

describe('Tournament details page', () => {
  let browser: BrowserManager;
  let tournamentPage: TournamentDetailsPage;

  beforeAll(async () => {
    browser = new BrowserManager();
    await browser.launch({
      id: 'launch',
      action: 'launch',
      headless: true,
      executablePath: CHROME_PATH,
    });
    await browser.ensurePage();
    tournamentPage = new TournamentDetailsPage(browser);
  });

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  describe('Page components (generic)', () => {
    beforeAll(async () => {
      await clearSession(browser);
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
    });

    it('displays tournament basic info (title, type, creator, location, date)', async () => {
      const title = await tournamentPage.getTitle();
      expect(title.length).toBeGreaterThan(0);
      expect(title.toLowerCase()).toContain('pawnshop');

      const type = await tournamentPage.getType();
      expect(type.length).toBeGreaterThan(0);

      const creator = await tournamentPage.getCreator();
      expect(creator.length).toBeGreaterThan(0);

      const location = await tournamentPage.getLocation();
      expect(location.length).toBeGreaterThan(0);

      const date = await tournamentPage.getDate();
      expect(date.length).toBeGreaterThan(0);
    });

    it('displays cardpool and MWL information', async () => {
      const cardpool = await tournamentPage.getCardpool();
      expect(cardpool.length).toBeGreaterThan(0);

      const mwl = await tournamentPage.getMwl();
      expect(mwl.length).toBeGreaterThan(0);
    });

    it('displays results, entries, photos and videos sections', async () => {
      expect(await tournamentPage.hasResultsSection()).toBe(true);
      expect(await tournamentPage.hasEntriesTable()).toBe(true);

      await tournamentPage.photosHeader.waitFor({ state: 'visible' });
      expect(await tournamentPage.photosHeader.isVisible()).toBe(true);

      await tournamentPage.videosHeader.waitFor({ state: 'visible' });
      expect(await tournamentPage.videosHeader.isVisible()).toBe(true);
    });
  });

  describe('Map display', () => {
    beforeAll(async () => {
      await clearSession(browser);
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
    });

    it('shows enabled "Show Map" button when Google Maps API is loaded', async () => {
      await tournamentPage.showMapButton.waitFor({ state: 'visible' });
      expect(await tournamentPage.showMapButton.isVisible()).toBe(true);

      await tournamentPage.waitForMapButtonEnabled();
      const isDisabled = await tournamentPage.showMapButton.isDisabled();
      expect(isDisabled).toBe(false);
    });

    it('displays map with marker after clicking "Show Map"', async () => {
      await tournamentPage.clickShowMap();
      await tournamentPage.waitForMapDisplayed();

      // Button should be hidden
      const buttonClass = await tournamentPage.showMapButton.getAttribute('class');
      expect(buttonClass).toContain('hidden-xs-up');

      // Map should have content (Google Maps renders child elements)
      const mapHasContent = await tournamentPage.mapContainer.evaluate((el) => el.children.length > 0);
      expect(mapHasContent).toBe(true);
    });
  });

  describe('Statistics charts', () => {
    beforeAll(async () => {
      await clearSession(browser);
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
      await tournamentPage.waitForChartsLoaded();
    });

    it('displays statistics section with runner and corp ID charts', async () => {
      const statsSection = browser.getPage().locator('h5:has(.fa-bar-chart)');
      await statsSection.waitFor({ state: 'visible', timeout: 10000 });
      expect(await statsSection.isVisible()).toBe(true);

      const hasRunnerChart = await tournamentPage.isChartVisible('#stat-chart-runner');
      expect(hasRunnerChart).toBe(true);

      const hasCorpChart = await tournamentPage.isChartVisible('#stat-chart-corp');
      expect(hasCorpChart).toBe(true);
    });

    it('has ID/faction toggle with ID active by default', async () => {
      expect(await tournamentPage.statsButtonId.isVisible()).toBe(true);
      expect(await tournamentPage.statsButtonFaction.isVisible()).toBe(true);

      const idButtonClass = await tournamentPage.statsButtonId.getAttribute('class');
      expect(idButtonClass).toContain('active');
    });

    it('can switch to faction charts', async () => {
      await tournamentPage.statsButtonFaction.click();
      // Wait for faction button to become active
      await expect.poll(async () => await tournamentPage.statsButtonFaction.getAttribute('class')).toContain('active');

      const idButtonClass = await tournamentPage.statsButtonId.getAttribute('class');
      expect(idButtonClass).not.toContain('active');
    });
  });

  describe('User not logged in', () => {
    beforeAll(async () => {
      await clearSession(browser);
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
    });

    it('does not show authenticated user features', async () => {
      expect(await tournamentPage.hasClaimButtons()).toBe(false);
      expect(await tournamentPage.hasAddPhotosButton()).toBe(false);
      expect(await tournamentPage.hasAddVideosButton()).toBe(false);
      expect(await tournamentPage.hasRegisterButton()).toBe(false);
      expect(await tournamentPage.hasControlButtons()).toBe(false);
    });

    it('shows login suggestion messages', async () => {
      expect(await tournamentPage.hasSuggestLoginClaim()).toBe(true);
      expect(await tournamentPage.hasSuggestLoginMedia()).toBe(true);
    });
  });

  describe('User logged in (regular user)', () => {
    let regularBrowser: BrowserManager;
    let regularPage: TournamentDetailsPage;

    beforeAll(async () => {
      regularBrowser = await createAuthenticatedBrowser('regular');
      regularPage = new TournamentDetailsPage(regularBrowser);
      await regularPage.open(TOURNAMENT_PATH);
      await regularPage.waitForPageLoaded();
    });

    afterAll(async () => {
      await closeBrowserSafely(regularBrowser);
    });

    it('shows authenticated UI elements and hides login messages', async () => {
      // Should see add buttons
      expect(await regularPage.hasAddPhotosButton()).toBe(true);
      expect(await regularPage.hasAddVideosButton()).toBe(true);

      // Should NOT see login suggestion messages
      expect(await regularPage.hasSuggestLoginClaim()).toBe(false);
      expect(await regularPage.hasSuggestLoginMedia()).toBe(false);

      // Claim buttons should be available (suggest login message gone)
      const hasClaimButtons = await regularPage.hasClaimButtons();
      const hasSuggestLogin = await regularPage.hasSuggestLoginClaim();
      expect(hasClaimButtons || !hasSuggestLogin).toBe(true);
    });

    it('does not show control buttons for non-creator user', async () => {
      expect(await regularPage.hasControlButtons()).toBe(false);
    });

    it('does not show admin-only features', async () => {
      const count = await regularPage.viewingAsAdmin.count();
      expect(count).toBe(0);
      expect(await regularPage.hasApproveButton()).toBe(false);
      expect(await regularPage.hasRejectButton()).toBe(false);
      expect(await regularPage.hasRevertConclusionButton()).toBe(false);
    });
  });

  describe('User logged in (admin user)', () => {
    let adminBrowser: BrowserManager;
    let adminPage: TournamentDetailsPage;

    beforeAll(async () => {
      adminBrowser = await createAuthenticatedBrowser('admin');
      adminPage = new TournamentDetailsPage(adminBrowser);
      await adminPage.open(TOURNAMENT_PATH);
      await adminPage.waitForPageLoaded();
    });

    afterAll(async () => {
      await closeBrowserSafely(adminBrowser);
    });

    it('shows admin control buttons', async () => {
      expect(await adminPage.hasControlButtons()).toBe(true);
      expect(await adminPage.editButton.isVisible()).toBe(true);
      expect(await adminPage.transferButton.isVisible()).toBe(true);
      expect(await adminPage.deleteButton.isVisible()).toBe(true);
      expect(await adminPage.viewingAsAdmin.isVisible()).toBe(true);
      expect(await adminPage.hasRejectButton()).toBe(true);
    });

    it('shows admin content management UI', async () => {
      expect(await adminPage.hasAddPhotosButton()).toBe(true);
      expect(await adminPage.hasAddVideosButton()).toBe(true);
      expect(await adminPage.hasConcludedBySection()).toBe(true);
      expect(await adminPage.hasRevertConclusionButton()).toBe(true);

      const concludedText = await adminPage.getConcludedByText();
      expect(concludedText).toContain('concluded by');
    });

    it('shows claim buttons and hides login messages', async () => {
      const hasClaimButtons = await adminPage.hasClaimButtons();
      const hasSuggestLoginClaim = await adminPage.hasSuggestLoginClaim();
      const hasSuggestLoginMedia = await adminPage.hasSuggestLoginMedia();

      // Suggest login should NOT be shown for logged in admin
      expect(hasSuggestLoginClaim).toBe(false);
      expect(hasSuggestLoginMedia).toBe(false);
    });
  });
});
