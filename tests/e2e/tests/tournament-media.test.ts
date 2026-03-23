import { createBrowserSuite, it, expect, beforeAll, afterAll } from '../helpers/test-fixture';
import { E2E_TEST_PREFIX } from '../pages/TournamentPage';

const TEST_IMAGE_PATH = new URL('../fixtures/testimage.jpeg', import.meta.url).pathname;
const YOUTUBE_VIDEO_URL = 'https://www.youtube.com/watch?v=nEpTYwpTopQ';
const YOUTUBE_VIDEO_ID = 'nEpTYwpTopQ';

function getFutureDate(daysFromNow: number): string {
  const date = new Date();
  date.setDate(date.getDate() + daysFromNow);
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}.${m}.${d}.`;
}

createBrowserSuite('Tournament media upload', { userType: 'regular' }, (ctx) => {
  let tournamentId: number;

  beforeAll(async () => {
    const { tournamentPage, tournamentDetailsPage } = ctx.pages;
    await tournamentPage.openCreateForm();
    await tournamentPage.fillTournamentDetails({
      title: `${E2E_TEST_PREFIX} Media Upload Test ${Date.now()}`,
      date: getFutureDate(30),
      online: true,
    });
    await tournamentPage.submitForm();
    await tournamentDetailsPage.waitForPageLoaded();
    tournamentId = tournamentPage.getIdFromUrl()!;
  });

  afterAll(async () => {
    const { tournamentPage, tournamentDetailsPage } = ctx.pages;
    await tournamentDetailsPage.open(String(tournamentId));
    await tournamentDetailsPage.waitForPageLoaded();
    await tournamentPage.deleteTournament();
  });

  it('uploads a photo to the tournament photos section', async () => {
    const { tournamentDetailsPage } = ctx.pages;
    const page = ctx.browser.getPage();

    await tournamentDetailsPage.open(String(tournamentId));
    await tournamentDetailsPage.waitForPageLoaded();

    // Open the photo upload section
    await tournamentDetailsPage.addPhotosButton.click();
    await tournamentDetailsPage.addPhotoSection.waitFor({ state: 'visible' });

    // Upload the test image and submit
    await page.locator('#photo').setInputFiles(TEST_IMAGE_PATH);
    await Promise.all([
      page.waitForURL(/\/tournaments\//),
      page.locator('#button-add-photo').click(),
    ]);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify photo appears in the gallery
    const galleryItem = page.locator('.gallery-item').first();
    await galleryItem.waitFor({ state: 'visible' });
    expect(await galleryItem.isVisible()).toBe(true);

    // Clean up: delete the photo just added
    page.once('dialog', (dialog) => dialog.accept());
    await page.locator('.gallery-item .btn-danger').first().click();
    await page.waitForLoadState('domcontentloaded');
  });

  it('adds a YouTube video to the tournament videos section', async () => {
    const { tournamentDetailsPage } = ctx.pages;
    const page = ctx.browser.getPage();

    await tournamentDetailsPage.open(String(tournamentId));
    await tournamentDetailsPage.waitForPageLoaded();

    // Open the video add section
    await tournamentDetailsPage.addVideosButton.click();
    await tournamentDetailsPage.addVideoSection.waitFor({ state: 'visible' });

    // Fill in the YouTube URL (YouTube radio is selected by default)
    await page.locator('input[name="video_id"]').fill(YOUTUBE_VIDEO_URL);

    // Submit - server makes YouTube API call before redirecting
    await Promise.all([
      page.waitForURL(/\/tournaments\//, { timeout: 30000 }),
      page.locator('#button-add-video').click(),
    ]);
    await tournamentDetailsPage.waitForPageLoaded();

    // Verify video row appears in the table
    const videoRow = page.locator(`#video-${YOUTUBE_VIDEO_ID}`);
    await videoRow.waitFor({ state: 'visible' });
    expect(await videoRow.isVisible()).toBe(true);

    // Clean up: delete the video just added
    page.once('dialog', (dialog) => dialog.accept());
    await videoRow.locator('.delete-video').click();
    await page.waitForLoadState('domcontentloaded');
  });
});
