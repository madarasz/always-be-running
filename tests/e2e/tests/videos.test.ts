import { createBrowserSuite, it, expect, beforeEach } from '../helpers/test-fixture';
import { setupApiMock } from '../helpers/mocks';
import videosFixture from '../fixtures/videos.json';

createBrowserSuite('Videos page', { userType: 'none' }, (ctx) => {
  beforeEach(async () => {
    const page = ctx.browser.getPage();
    await setupApiMock(page, '**/api/videos', videosFixture);
  });

  it('loads tournament list with videos and titles', async () => {
    const { videosPage } = ctx.pages;

    await videosPage.open();
    await videosPage.waitForTournamentsLoaded();

    const tournamentCount = await videosPage.getTournamentCount();
    expect(tournamentCount).toBeGreaterThan(0);

    const displayedCount = await videosPage.getTotalVideoCount();
    expect(displayedCount).toBeGreaterThan(0);

    const firstTitle = await videosPage.getTournamentTitle(0);
    expect(firstTitle.trim().length).toBeGreaterThan(0);
  });

  it('switches tournaments and plays videos', async () => {
    const { videosPage } = ctx.pages;

    await videosPage.open();
    await videosPage.waitForTournamentsLoaded();

    // Click first tournament
    await videosPage.clickTournament(0);
    const firstTournamentVideosCount = await videosPage.getVideosCount();
    expect(firstTournamentVideosCount).toBeGreaterThan(0);

    // Verify first tournament is selected
    expect(await videosPage.isTournamentSelected(0)).toBe(true);
    expect(await videosPage.isTournamentSelected(1)).toBe(false);

    // Click second tournament
    await videosPage.clickTournament(1);
    const secondTournamentVideosCount = await videosPage.getVideosCount();
    expect(secondTournamentVideosCount).toBeGreaterThan(0);

    // Verify second tournament is now selected
    expect(await videosPage.isTournamentSelected(0)).toBe(false);
    expect(await videosPage.isTournamentSelected(1)).toBe(true);

    // Verify video titles are populated
    const firstVideoTitle = await videosPage.getVideoTitle(0);
    expect(firstVideoTitle?.trim().length).toBeGreaterThan(0);

    // Video player should not be visible initially
    expect(await videosPage.isVideoPlayerVisible()).toBe(false);

    // Click first video
    await videosPage.clickVideo(0);

    // Video player should now be visible with YouTube embed
    expect(await videosPage.isVideoPlayerVisible()).toBe(true);
    const playerSrc = await videosPage.getVideoPlayerSrc();
    expect(playerSrc).toContain('youtube.com/embed');
  });
});
