import { createBrowserSuite, it, expect } from '../helpers/test-fixture';
import { mockBrowserDate } from '../helpers/auth';

// Fixed date to ensure tournament on 2026-02-28 is always in the future
const MOCK_DATE = '2026-02-25';

createBrowserSuite('Personal Page', {
  userType: 'regular',
  beforeInit: async (browser) => {
    // Mock the browser date BEFORE authentication navigates to the app
    await mockBrowserDate(browser, MOCK_DATE);
  },
}, (ctx) => {
  it('Tournaments tab: calendar, map with markers, and tournaments table with going/claim/claimed', async () => {
    const { personalPage } = ctx.pages;

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
    const { personalPage } = ctx.pages;

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
    const { personalPage } = ctx.pages;

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
