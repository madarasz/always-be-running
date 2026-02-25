import { BasePage } from './BasePage';

export class ProfilePage extends BasePage {
  // Page header
  readonly pageHeader = this.page.locator('h4.page-header');

  // Tabs
  readonly infoTab = this.page.locator('#tabf-info a.nav-link');
  readonly prizeCollectionTab = this.page.locator('#tabf-collection a.nav-link');

  // Tab content
  readonly infoTabPane = this.page.locator('#tab-info');

  // User info section
  readonly userSection = this.page.locator('.bracket h5:has(i.fa-user)');
  readonly adminAlert = this.page.locator('.alert-info:has-text("This user is an admin")');
  readonly userCounts = this.page.locator('.user-counts');

  // Badges section
  readonly badgesSection = this.page.locator('.bracket h5:has-text("Badges")');

  // Claims section
  readonly claimsSection = this.page.locator('.bracket h5:has-text("Claims")');
  readonly claimsList = this.page.locator('#list-claims');

  // Created tournaments section
  readonly createdSection = this.page.locator('.bracket h5:has-text("Created tournaments")');
  readonly createdList = this.page.locator('#list-created');

  // Usernames section
  readonly usernamesSection = this.page.locator('.bracket h5:has-text("Usernames")');
  readonly netrunnerDbLabel = this.page.locator('label:has-text("NetrunnerDB")');

  // Navigation
  readonly navProfile = this.page.locator('#nav-profile');

  async open() {
    // Navigate to profile by clicking the Profile link in navbar
    await this.navProfile.click();
    await this.waitForProfileLoaded();
  }

  async openById(userId: number) {
    await this.navigate(`/profile/${userId}`);
    await this.waitForProfileLoaded();
  }

  async waitForProfileLoaded() {
    await this.pageHeader.waitFor({ state: 'visible' });
    await this.infoTabPane.waitFor({ state: 'visible' });
  }

  async getPageTitle(): Promise<string> {
    return await this.pageHeader.textContent() || '';
  }

  async isInfoTabActive(): Promise<boolean> {
    const classes = await this.infoTab.getAttribute('class');
    return classes?.includes('active') ?? false;
  }

  async hasUserSection(): Promise<boolean> {
    return await this.userSection.isVisible();
  }

  async hasBadgesSection(): Promise<boolean> {
    return await this.badgesSection.isVisible();
  }

  async hasClaimsSection(): Promise<boolean> {
    return await this.claimsSection.isVisible();
  }

  async hasCreatedSection(): Promise<boolean> {
    return await this.createdSection.isVisible();
  }

  async hasUsernamesSection(): Promise<boolean> {
    return await this.usernamesSection.isVisible();
  }

  async isAdmin(): Promise<boolean> {
    return await this.adminAlert.isVisible();
  }

  async getUserCountsText(): Promise<string> {
    return await this.userCounts.textContent() || '';
  }
}
