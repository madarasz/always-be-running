import { BrowserManager } from 'agent-browser/dist/browser.js';
import { BasePage } from './BasePage';

export class VideosPage extends BasePage {
  constructor(browser: BrowserManager) {
    super(browser);
  }

  async open() {
    await this.navigate('/videos');
  }

  get tournamentsTable() {
    return this.page.locator('#table-tournaments');
  }

  get videosTable() {
    return this.page.locator('#table-videos');
  }

  get videoCountLabel() {
    return this.page.locator('#label-all-videos');
  }

  get videoListCountLabel() {
    return this.page.locator('#label-videos-number');
  }

  get videoPlayer() {
    return this.page.locator('#section-video-player iframe');
  }

  get helperSelectText() {
    return this.page.locator('#helper-select');
  }

  async waitForTournamentsLoaded() {
    await this.tournamentsTable.locator('tbody tr').first().waitFor({ timeout: 10000 });
  }

  async getTournamentCount() {
    return await this.tournamentsTable.locator('tbody tr').count();
  }

  async getTotalVideoCount() {
    const text = await this.videoCountLabel.textContent();
    return parseInt(text || '0', 10);
  }

  async getTournamentRow(index: number) {
    return this.tournamentsTable.locator('tbody tr').nth(index);
  }

  async getTournamentTitle(index: number) {
    const row = await this.getTournamentRow(index);
    const title = await row.locator('.featured-title').textContent();
    return title?.trim() || '';
  }

  async clickTournament(index: number) {
    const row = await this.getTournamentRow(index);
    await row.click();
    // Wait for videos table to be populated
    await this.videosTable.locator('tbody tr').first().waitFor({ state: 'attached', timeout: 5000 });
  }

  async getVideosCount() {
    return await this.videosTable.locator('tbody tr').count();
  }

  async getVideoRow(index: number) {
    return this.videosTable.locator('tbody tr').nth(index);
  }

  async getVideoTitle(index: number) {
    const row = await this.getVideoRow(index);
    const link = row.locator('td:nth-child(2) b a');
    return await link.textContent();
  }

  async clickVideo(index: number) {
    const row = await this.getVideoRow(index);
    const link = row.locator('td:nth-child(2) b a');
    await link.click();
    // Wait for video player section to become visible (hidden-xs-up class removed)
    await this.page.waitForFunction(
      () => {
        const section = document.querySelector('#section-watch-video');
        return section && !section.classList.contains('hidden-xs-up');
      },
      { timeout: 5000 }
    );
    // Wait for iframe to be created
    await this.videoPlayer.waitFor({ state: 'attached', timeout: 5000 });
  }

  async isVideoPlayerVisible() {
    const watchSection = this.page.locator('#section-watch-video');
    const hasHiddenClass = await watchSection.getAttribute('class');
    return !hasHiddenClass?.includes('hidden-xs-up');
  }

  async getVideoPlayerSrc() {
    const iframe = this.videoPlayer;
    await iframe.waitFor({ state: 'attached', timeout: 5000 });
    return await iframe.getAttribute('src');
  }

  async isTournamentSelected(index: number) {
    const row = await this.getTournamentRow(index);
    const cell = row.locator('td').first();
    const classList = await cell.getAttribute('class');
    return classList?.includes('row-selected') || false;
  }
}
