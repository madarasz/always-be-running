import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { BrowserManager } from 'agent-browser/dist/browser.js';
import { PersonalPage } from '../pages/PersonalPage';
import { createAuthenticatedBrowser, closeBrowserSafely, mockBrowserDate } from '../helpers/auth';

// Fixed date to ensure tournament on 2026-02-28 is always in the future
const MOCK_DATE = '2026-02-25';

describe('Personal Page', () => {
  let browser: BrowserManager;
  let personalPage: PersonalPage;

  beforeAll(async () => {
    browser = await createAuthenticatedBrowser('regular');
    // Mock the browser date so tournament on 2026-02-28 is always in the future
    await mockBrowserDate(browser, MOCK_DATE);
    personalPage = new PersonalPage(browser);
  });

  afterAll(async () => {
    await closeBrowserSafely(browser);
  });

  it('Tournaments tab: calendar, map with markers, and tournaments table with going/claim/claimed', async () => {
    await personalPage.open();

    // Verify page header contains "Personal"
    const pageTitle = await personalPage.getPageTitle();
    expect(pageTitle).toContain('Personal');

    // Tournaments tab should be active by default
    expect(await personalPage.isTournamentsTabActive()).toBe(true);

    // Verify My tournaments table is visible
    expect(await personalPage.hasTournamentsTable()).toBe(true);

    // Verify calendar is visible
    expect(await personalPage.hasCalendar()).toBe(true);

    // Verify map is visible
    expect(await personalPage.hasMap()).toBe(true);

    // Verify tournaments table has going, claim, and claimed values
    expect(await personalPage.hasGoingStatus()).toBe(true);
    expect(await personalPage.hasClaimButton()).toBe(true);
    expect(await personalPage.hasClaimedStatus()).toBe(true);
  });

  it('Photos tab: photos are visible', async () => {
    await personalPage.open();

    // Navigate to Photos tab
    await personalPage.clickPhotosTab();

    // Verify photos are visible
    expect(await personalPage.hasPhotos()).toBe(true);

    // Verify at least some photos exist
    const photoCount = await personalPage.getPhotoCount();
    expect(photoCount).toBeGreaterThan(0);
  });

  it('Videos tab: at least one video is listed', async () => {
    await personalPage.open();

    // Navigate to Videos tab
    await personalPage.clickVideosTab();

    // Verify videos section is visible and has at least one video
    expect(await personalPage.hasVideos()).toBe(true);

    // Verify the videos section title shows a count
    const sectionTitle = await personalPage.getVideosSectionTitle();
    expect(sectionTitle).toContain('My videos');
  });
});
