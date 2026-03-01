import { BasePage } from './BasePage';

export class PersonalPage extends BasePage {
  // Page header
  readonly pageHeader = this.page.locator('h4.page-header');

  // Tabs
  readonly tournamentsTab = this.page.locator('[data-toggle="tab"][href="#tab-tournaments"]');
  readonly photosTab = this.page.locator('[data-toggle="tab"][href="#tab-photos"]');
  readonly videosTab = this.page.locator('[data-toggle="tab"][href="#tab-videos"]');

  // Tab panes
  readonly tournamentsPane = this.page.locator('#tab-tournaments');
  readonly photosPane = this.page.locator('#tab-photos');
  readonly videosPane = this.page.locator('#tab-videos');

  // Tournaments tab elements
  readonly myTournamentsSection = this.page.locator('.bracket h5:has-text("My tournaments")');
  readonly tournamentsTable = this.page.locator('#my-table');
  readonly calendarSection = this.page.locator('.bracket h5:has-text("My calendar")');
  readonly calendar = this.page.locator('#calendar');
  readonly mapSection = this.page.locator('.bracket h5:has-text("My map")');
  readonly mapContainer = this.page.locator('#mymap');

  // Photos tab elements
  readonly photosSection = this.page.locator('.bracket h5:has-text("My photos")');
  readonly photoLinks = this.page.locator('#tab-photos a[href*="/photo/"]');

  // Videos tab elements
  readonly videosSection = this.page.locator('.bracket h5:has-text("My videos")');
  readonly videoRows = this.page.locator('#tab-videos .row');

  async open() {
    await this.navigate('/personal');
    await this.waitForPageLoaded();
  }

  async waitForPageLoaded() {
    await this.pageHeader.waitFor({ state: 'visible' });
  }

  async getPageTitle(): Promise<string> {
    return await this.pageHeader.textContent() || '';
  }

  // Tournaments tab methods
  async isTournamentsTabActive(): Promise<boolean> {
    const classes = await this.tournamentsTab.getAttribute('class');
    return classes?.includes('active') ?? false;
  }

  async clickTournamentsTab() {
    await this.tournamentsTab.click();
    await this.tournamentsPane.waitFor({ state: 'visible' });
  }

  async hasTournamentsTable(): Promise<boolean> {
    return await this.tournamentsTable.isVisible();
  }

  async hasCalendar(): Promise<boolean> {
    return await this.calendar.isVisible();
  }

  async hasMap(): Promise<boolean> {
    return await this.mapContainer.isVisible();
  }

  async hasMapMarkers(): Promise<boolean> {
    // Google Maps markers are buttons with tournament info in the description
    const markers = this.page.locator('#my-map button[aria-label*="tournaments"]');
    const count = await markers.count();
    return count > 0;
  }

  async waitForTableData(): Promise<void> {
    // Wait for at least one row to appear in the table
    await this.tournamentsTable.locator('tbody tr').first().waitFor({ state: 'visible', timeout: 10000 });
  }

  async hasGoingStatus(): Promise<boolean> {
    await this.waitForTableData();
    // Look for the "going" label anywhere in the table
    const goingLabel = this.tournamentsTable.locator('span.label-info:has-text("going")');
    const count = await goingLabel.count();
    return count > 0;
  }

  async hasClaimButton(): Promise<boolean> {
    await this.waitForTableData();
    // Look for the claim button in the table
    const claimButton = this.tournamentsTable.locator('button.btn-claim');
    const count = await claimButton.count();
    return count > 0;
  }

  async hasClaimedStatus(): Promise<boolean> {
    await this.waitForTableData();
    // Look for the "claimed" label anywhere in the table
    const claimedLabel = this.tournamentsTable.locator('span.label-success:has-text("claimed")');
    const count = await claimedLabel.count();
    return count > 0;
  }

  // Photos tab methods
  async clickPhotosTab() {
    await this.photosTab.click();
    await this.photosPane.waitFor({ state: 'visible' });
  }

  async getPhotoCount(): Promise<number> {
    return await this.photoLinks.count();
  }

  async hasPhotos(): Promise<boolean> {
    const count = await this.getPhotoCount();
    return count > 0;
  }

  // Videos tab methods
  async clickVideosTab() {
    await this.videosTab.click();
    await this.videosPane.waitFor({ state: 'visible' });
  }

  async hasVideos(): Promise<boolean> {
    // Check for video entries - look for video title text or links
    const videoTitles = this.videosPane.locator('.row');
    const count = await videoTitles.count();
    return count > 0;
  }

  async getVideosSectionTitle(): Promise<string> {
    return await this.videosSection.textContent() || '';
  }
}
