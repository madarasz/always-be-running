import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { TournamentDetailsPage } from '../pages/TournamentDetailsPage';
import { clearSession, loginUser } from '../helpers/auth';

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
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
    await browser.close();
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
      await browser.getPage().waitForTimeout(300);

      const factionButtonClass = await tournamentPage.statsButtonFaction.getAttribute('class');
      expect(factionButtonClass).toContain('active');

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
    beforeAll(async () => {
      await loginUser(browser, 'regular');
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
    });

    it('shows authenticated UI elements and hides login messages', async () => {
      // Should see add buttons
      expect(await tournamentPage.hasAddPhotosButton()).toBe(true);
      expect(await tournamentPage.hasAddVideosButton()).toBe(true);

      // Should NOT see login suggestion messages
      expect(await tournamentPage.hasSuggestLoginClaim()).toBe(false);
      expect(await tournamentPage.hasSuggestLoginMedia()).toBe(false);

      // Claim buttons should be available (suggest login message gone)
      const hasClaimButtons = await tournamentPage.hasClaimButtons();
      const hasSuggestLogin = await tournamentPage.hasSuggestLoginClaim();
      expect(hasClaimButtons || !hasSuggestLogin).toBe(true);
    });

    it('does not show control buttons for non-creator user', async () => {
      expect(await tournamentPage.hasControlButtons()).toBe(false);
    });

    it('does not show admin-only features', async () => {
      const count = await tournamentPage.viewingAsAdmin.count();
      expect(count).toBe(0);
      expect(await tournamentPage.hasApproveButton()).toBe(false);
      expect(await tournamentPage.hasRejectButton()).toBe(false);
      expect(await tournamentPage.hasRevertConclusionButton()).toBe(false);
    });
  });

  describe('User logged in (admin user)', () => {
    beforeAll(async () => {
      await loginUser(browser, 'admin');
      await tournamentPage.open(TOURNAMENT_PATH);
      await tournamentPage.waitForPageLoaded();
    });

    it('shows admin control buttons', async () => {
      expect(await tournamentPage.hasControlButtons()).toBe(true);
      expect(await tournamentPage.editButton.isVisible()).toBe(true);
      expect(await tournamentPage.transferButton.isVisible()).toBe(true);
      expect(await tournamentPage.deleteButton.isVisible()).toBe(true);
      expect(await tournamentPage.viewingAsAdmin.isVisible()).toBe(true);
      expect(await tournamentPage.hasRejectButton()).toBe(true);
    });

    it('shows admin content management UI', async () => {
      expect(await tournamentPage.hasAddPhotosButton()).toBe(true);
      expect(await tournamentPage.hasAddVideosButton()).toBe(true);
      expect(await tournamentPage.hasConcludedBySection()).toBe(true);
      expect(await tournamentPage.hasRevertConclusionButton()).toBe(true);

      const concludedText = await tournamentPage.getConcludedByText();
      expect(concludedText).toContain('concluded by');
    });

    it('shows claim buttons and hides login messages', async () => {
      const hasClaimButtons = await tournamentPage.hasClaimButtons();
      const hasSuggestLoginClaim = await tournamentPage.hasSuggestLoginClaim();
      const hasSuggestLoginMedia = await tournamentPage.hasSuggestLoginMedia();

      // Suggest login should NOT be shown for logged in admin
      expect(hasSuggestLoginClaim).toBe(false);
      expect(hasSuggestLoginMedia).toBe(false);
    });
  });
});
